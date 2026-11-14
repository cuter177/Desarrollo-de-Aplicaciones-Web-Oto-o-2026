import { apiGet, apiPost } from '../api.js';
import { iniciarPagina } from '../app.js';
import { obtenerCsrf } from '../auth.js';
import { mostrarAlerta, ocultarAlerta, setBotonCargando } from '../ui.js';

const form = document.querySelector('#diagnostico-form');
const alerta = document.querySelector('#diagnostico-alert');
const planesLista = document.querySelector('#planes-lista');
const serviciosLista = document.querySelector('#servicios-lista');
const resultado = document.querySelector('#resultado');
const barra = document.querySelector('[data-wizard-bar]');
const pasos = [...form.querySelectorAll('[data-step]')];
const etiquetas = [...document.querySelectorAll('[data-step-label]')];
const botonAnterior = form.querySelector('[data-wizard-prev]');
const botonSiguiente = form.querySelector('[data-wizard-next]');
const botonEnviar = form.querySelector('[data-wizard-submit]');
const botonReiniciar = form.querySelector('[data-wizard-restart]');

let pasoActual = 1;
let planes = [];

function escapar(texto) {
  return String(texto)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}

function dinero(valor) {
  return new Intl.NumberFormat('es-MX', {
    style: 'currency',
    currency: 'MXN',
    maximumFractionDigits: 0,
  }).format(Number(valor) || 0);
}

function mostrarPaso(paso) {
  pasoActual = paso;

  pasos.forEach((panel) => {
    panel.hidden = Number(panel.dataset.step) !== paso;
  });

  etiquetas.forEach((etiqueta) => {
    const numero = Number(etiqueta.dataset.stepLabel);
    etiqueta.classList.toggle('is-active', numero === paso);
    etiqueta.classList.toggle('is-done', numero < paso);
  });

  const porcentaje = ((paso - 1) / (pasos.length - 1)) * 100;
  barra.style.width = `${porcentaje}%`;

  botonAnterior.hidden = paso <= 1 || paso >= 4;
  botonSiguiente.hidden = paso >= 3;
  botonEnviar.hidden = paso !== 3;
  botonReiniciar.hidden = paso !== 4;
}

function validarPaso(paso) {
  const panel = form.querySelector(`[data-step="${paso}"]`);

  if (!panel) {
    return true;
  }

  const campos = [...panel.querySelectorAll('input, select, textarea')];

  for (const campo of campos) {
    if (!campo.checkValidity()) {
      campo.reportValidity();
      return false;
    }
  }

  if (paso === 2 && form.querySelectorAll('input[name="servicios_interes"]:checked').length === 0) {
    mostrarAlerta(alerta, 'error', 'Selecciona al menos un servicio.');
    return false;
  }

  return true;
}

async function cargarPlanes() {
  try {
    planes = await apiGet('/planes');
    planesLista.innerHTML = planes.map((plan) => `
      <article class="plan${plan.destacado ? ' plan--featured' : ''}">
        ${plan.destacado ? '<span class="badge badge--success">Más elegido</span>' : ''}
        <h3 class="plan__title">${escapar(plan.nombre)}</h3>
        <p class="plan__subtitle">${escapar(plan.subtitulo ?? '')}</p>
        <p class="plan__price">
          <span>Desde</span>
          ${dinero(plan.precio_desde)}
          ${plan.precio_hasta ? `<small>hasta ${dinero(plan.precio_hasta)}</small>` : ''}
        </p>
        <p class="plan__description">${escapar(plan.descripcion)}</p>
        <ul class="plan__features">
          ${plan.caracteristicas.map((item) => `<li>${escapar(item)}</li>`).join('')}
        </ul>
        ${plan.dirigido_a ? `<p class="plan__audience">${escapar(plan.dirigido_a)}</p>` : ''}
        <a class="btn btn--secondary btn--block" href="#diagnostico">Elegir este plan</a>
      </article>
    `).join('');
  } catch (error) {
    planesLista.innerHTML = '<div class="empty-state">No se pudieron cargar los planes.</div>';
    mostrarAlerta(alerta, 'error', error.message);
  }
}

async function cargarServicios() {
  try {
    const servicios = await apiGet('/servicios');

    serviciosLista.innerHTML = servicios.map((servicio) => `
      <label class="service-option">
        <input type="checkbox" name="servicios_interes" value="${escapar(servicio.slug)}">
        <span>
          <strong>${escapar(servicio.nombre)}</strong>
          <small>${escapar(servicio.descripcion)}</small>
        </span>
      </label>
    `).join('');
  } catch (error) {
    serviciosLista.innerHTML = '<div class="empty-state">No se pudieron cargar los servicios.</div>';
    mostrarAlerta(alerta, 'error', error.message);
  }
}

function renderResultado(data) {
  const plan = data.plan;

  resultado.innerHTML = `
    <div class="result">
      <p class="hero__eyebrow">Plan recomendado</p>
      <h3 class="result__title">${escapar(plan.nombre)}</h3>
      <p class="result__subtitle">${escapar(plan.subtitulo ?? '')}</p>
      <p class="result__price">Desde ${dinero(plan.precio_desde)}</p>
      <p class="result__text">${escapar(data.recomendacion)}</p>
      <ul class="plan__features">
        ${plan.caracteristicas.map((item) => `<li>${escapar(item)}</li>`).join('')}
      </ul>
      <div class="result__impact">
        <div>
          <span class="result__impact-label">Crecimiento esperado</span>
          <strong data-resultado-crecimiento>—</strong>
        </div>
        <div>
          <span class="result__impact-label">Ahorro semanal</span>
          <strong data-resultado-horas>—</strong>
        </div>
        <div>
          <span class="result__impact-label">Solicitud</span>
          <strong>#${Number(data.solicitud_id)}</strong>
        </div>
      </div>
      <p class="form__hint">
        Guardamos tu diagnóstico. Un asesor puede contactarte para afinar el
        alcance y el presupuesto.
      </p>
      <div class="hero__actions">
        <a class="btn" href="/pages/registro.html">Crear cuenta de cliente</a>
        <a class="btn btn--secondary" href="#planes">Volver a los planes</a>
      </div>
    </div>
  `;

  const crecimiento = form.querySelector('#meta_crecimiento_pct')?.value ?? '0';
  const horas = form.querySelector('#ahorro_horas_semana')?.value ?? '0';
  resultado.querySelector('[data-resultado-crecimiento]').textContent = `${crecimiento} %`;
  resultado.querySelector('[data-resultado-horas]').textContent = `${horas} h`;
}

function datosFormulario() {
  const servicios = [...form.querySelectorAll('input[name="servicios_interes"]:checked')]
    .map((input) => input.value);

  return {
    nombre_contacto: form.nombre_contacto.value.trim(),
    email: form.email.value.trim(),
    empresa: form.empresa.value.trim(),
    giro: form.giro.value.trim(),
    telefono: form.telefono.value.trim(),
    necesidades: form.necesidades.value.trim(),
    servicios_interes: servicios,
    procesos_automatizar: form.procesos_automatizar.value.trim(),
    meta_crecimiento_pct: Number(form.meta_crecimiento_pct.value),
    ahorro_horas_semana: Number(form.ahorro_horas_semana.value),
    presupuesto_estimado: Number(form.presupuesto_estimado.value),
    plazo_deseado: form.plazo_deseado.value,
    etapa_decision: form.etapa_decision.value,
  };
}

async function init() {
  await iniciarPagina();
  mostrarPaso(1);

  await Promise.all([cargarPlanes(), cargarServicios()]);

  botonSiguiente?.addEventListener('click', () => {
    ocultarAlerta(alerta);

    if (!validarPaso(pasoActual)) {
      return;
    }

    mostrarPaso(Math.min(3, pasoActual + 1));
  });

  botonAnterior?.addEventListener('click', () => {
    ocultarAlerta(alerta);
    mostrarPaso(Math.max(1, pasoActual - 1));
  });

  form?.addEventListener('submit', async (event) => {
    event.preventDefault();
    ocultarAlerta(alerta);

    if (!validarPaso(3)) {
      return;
    }

    setBotonCargando(botonEnviar, true, 'Calculando plan…');

    try {
      await obtenerCsrf();
      const data = await apiPost('/diagnostico', datosFormulario());
      renderResultado(data);
      mostrarPaso(4);
      resultado.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } catch (error) {
      mostrarAlerta(alerta, 'error', error.message);
      setBotonCargando(botonEnviar, false);
    }
  });
}

init();
