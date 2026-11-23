import { apiGet, apiPatch, apiPost } from '../api.js';
import { iniciarPagina } from '../app.js';
import { protegerPagina } from '../auth.js';
import { aplicarConfig, etiquetaEstado, formatearMomento } from '../public.js';
import { escapar, mensajeError, mostrarAlerta, ocultarAlerta, setBotonCargando } from '../ui.js';

const alerta = document.querySelector('#portal-alert');
const tituloMes = document.querySelector('#mes-titulo');
const dias = document.querySelector('#calendario-dias');
const tituloDia = document.querySelector('#dia-titulo');
const listaDia = document.querySelector('#dia-reuniones');
const formulario = document.querySelector('#reunion-form');
const proyectoSelect = document.querySelector('#reunion-proyecto');
const botonGuardar = formulario?.querySelector('button[type="submit"]');

const MESES = [
  'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
  'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre',
];

const hoy = claveMexico(new Date());
let anio = Number(hoy.slice(0, 4));
let mes = Number(hoy.slice(5, 7));
let seleccionado = hoy;
let reuniones = [];

function instanteMexico(year, month, day, hora = 12, minuto = 0) {
  return new Date(Date.UTC(year, month - 1, day, hora + 6, minuto));
}

function claveMexico(fecha) {
  return new Intl.DateTimeFormat('en-CA', {
    timeZone: 'America/Mexico_City',
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  }).format(fecha);
}

function clavePartes(clave) {
  const [year, month, day] = clave.split('-').map(Number);
  return { year, month, day };
}

function construirMes() {
  const primero = instanteMexico(anio, mes, 1);
  const desplazamiento = (primero.getUTCDay() + 6) % 7;
  const inicio = new Date(primero);
  inicio.setUTCDate(primero.getUTCDate() - desplazamiento);
  return Array.from({ length: 42 }, (_, indice) => {
    const fecha = new Date(inicio);
    fecha.setUTCDate(inicio.getUTCDate() + indice);
    return fecha;
  });
}

function puedeCancelar(reunion) {
  return ['solicitada', 'confirmada'].includes(reunion.estado)
    && new Date(reunion.inicio).getTime() > Date.now();
}

function pintarDia() {
  const { day, month } = clavePartes(seleccionado);
  tituloDia.textContent = `${day} de ${MESES[month - 1]}`;
  const delDia = reuniones.filter((reunion) => claveMexico(new Date(reunion.inicio)) === seleccionado);

  if (delDia.length === 0) {
    listaDia.innerHTML = '<p class="progress__empty">Este día no tiene reuniones.</p>';
    return;
  }

  listaDia.innerHTML = `
    <ul class="meeting-list">
      ${delDia.map((reunion) => `
        <li>
          <article>
            <h3>${escapar(reunion.titulo)}</h3>
            <p>${escapar(formatearMomento(reunion.inicio))} – ${escapar(formatearMomento(reunion.fin))}</p>
            <p>${escapar(reunion.proyecto_nombre)}</p>
            <p><span class="badge status status--${escapar(reunion.estado)}">${escapar(etiquetaEstado(reunion.estado))}</span></p>
            ${reunion.respuesta ? `<p><strong>Respuesta.</strong> ${escapar(reunion.respuesta)}</p>` : ''}
            ${reunion.puntos ? `<p><strong>Puntos a tratar.</strong> ${escapar(reunion.puntos)}</p>` : ''}
            ${puedeCancelar(reunion) ? `<button class="btn btn--ghost" type="button" data-cancelar="${reunion.id}">Cancelar reunión</button>` : ''}
          </article>
        </li>
      `).join('')}
    </ul>
  `;
}

function pintarMes() {
  tituloMes.textContent = `${MESES[mes - 1]} ${anio}`;
  const celdas = construirMes();
  const conteo = new Map();

  reuniones.forEach((reunion) => {
    const clave = claveMexico(new Date(reunion.inicio));
    conteo.set(clave, (conteo.get(clave) ?? 0) + 1);
  });

  dias.innerHTML = celdas.map((fecha) => {
    const clave = claveMexico(fecha);
    const { day, month } = clavePartes(clave);
    const cantidad = conteo.get(clave) ?? 0;
    const clases = ['calendar__day'];

    if (month !== mes) {
      clases.push('is-outside');
    }

    if (clave === seleccionado) {
      clases.push('is-selected');
    }

    const etiqueta = cantidad === 1
      ? `${day} de ${MESES[month - 1]}, 1 reunión`
      : `${day} de ${MESES[month - 1]}, ${cantidad} reuniones`;

    return `
      <button class="${clases.join(' ')}" type="button" data-dia="${clave}" aria-pressed="${clave === seleccionado ? 'true' : 'false'}" aria-label="${etiqueta}">
        <span>${day}</span>
        ${cantidad > 0 ? `<span class="calendar__count">${cantidad}</span>` : ''}
      </button>
    `;
  }).join('');
  pintarDia();
}

async function cargarMes() {
  const celdas = construirMes();
  const desde = desdeClave(claveMexico(celdas[0]));
  const hasta = new Date(desdeClave(claveMexico(celdas[41])).getTime() + 24 * 60 * 60 * 1000);
  const params = new URLSearchParams({
    desde: desde.toISOString(),
    hasta: hasta.toISOString(),
  });
  reuniones = await apiGet(`/mi/reuniones?${params.toString()}`);
  pintarMes();
}

function desdeClave(clave) {
  const { year, month, day } = clavePartes(clave);
  return instanteMexico(year, month, day, 0, 0);
}

function cambiarMes(delta) {
  mes += delta;

  if (mes < 1) {
    mes = 12;
    anio -= 1;
  }

  if (mes > 12) {
    mes = 1;
    anio += 1;
  }

  seleccionado = `${anio}-${String(mes).padStart(2, '0')}-01`;
}

async function init() {
  const usuario = await iniciarPagina();
  aplicarConfig();

  if (!protegerPagina(usuario, ['cliente'])) {
    return;
  }

  document.querySelector('#mes-anterior')?.addEventListener('click', async () => {
    cambiarMes(-1);
    await cargarMes().catch(mostrarError);
  });
  document.querySelector('#mes-siguiente')?.addEventListener('click', async () => {
    cambiarMes(1);
    await cargarMes().catch(mostrarError);
  });

  dias.addEventListener('click', (event) => {
    const boton = event.target.closest('[data-dia]');

    if (!boton) {
      return;
    }

    seleccionado = boton.dataset.dia;
    const { month, year } = clavePartes(seleccionado);

    if (month !== mes || year !== anio) {
      mes = month;
      anio = year;
      cargarMes().catch(mostrarError);
      return;
    }

    pintarMes();
  });

  listaDia.addEventListener('click', async (event) => {
    const boton = event.target.closest('[data-cancelar]');

    if (!boton) {
      return;
    }

    boton.disabled = true;

    try {
      await apiPatch(`/mi/reuniones/${boton.dataset.cancelar}`, {});
      ocultarAlerta(alerta);
      await cargarMes();
    } catch (error) {
      mostrarError(error);
      boton.disabled = false;
    }
  });

  formulario.addEventListener('submit', async (event) => {
    event.preventDefault();
    ocultarAlerta(alerta);
    const datos = new FormData(formulario);
    setBotonCargando(botonGuardar, true, 'Agendando…');

    try {
      const reunion = await apiPost('/mi/reuniones', {
        proyecto_id: Number(datos.get('proyecto_id')),
        titulo: String(datos.get('titulo') ?? '').trim(),
        inicio: String(datos.get('inicio') ?? ''),
        fin: String(datos.get('fin') ?? ''),
      });
      formulario.reset();
      const clave = claveMexico(new Date(reunion.inicio));
      const partes = clavePartes(clave);
      anio = partes.year;
      mes = partes.month;
      seleccionado = clave;
      await cargarMes();
    } catch (error) {
      mostrarError(error);
    } finally {
      setBotonCargando(botonGuardar, false);
    }
  });

  try {
    const proyectos = await apiGet('/mi/proyectos');
    proyectoSelect.innerHTML = proyectos.length
      ? proyectos.map((proyecto) => `<option value="${proyecto.id}">${escapar(proyecto.nombre)}</option>`).join('')
      : '<option value="">Sin proyectos</option>';
    proyectoSelect.disabled = proyectos.length === 0;
    await cargarMes();
  } catch (error) {
    mostrarError(error);
  }
}

function mostrarError(error) {
  mostrarAlerta(alerta, 'error', mensajeError(error));
}

init();
