import { apiGet, apiPatch, apiPost } from '../api.js';
import { iniciarPagina } from '../app.js';
import { protegerPagina } from '../auth.js';
import { mostrarAlerta, ocultarAlerta, setBotonCargando } from '../ui.js';

const alerta = document.querySelector('#admin-alert');
const lista = document.querySelector('#solicitudes-lista');
const detallePanel = document.querySelector('#solicitud-detalle');
const detalleContenido = document.querySelector('#detalle-contenido');
const detalleTitulo = document.querySelector('#detalle-title');
const filtroEstado = document.querySelector('#filtro-estado');
const filtroPlan = document.querySelector('#filtro-plan');
const filtroQ = document.querySelector('#filtro-q');
const filtroAplicar = document.querySelector('#filtro-aplicar');
const detalleCerrar = document.querySelector('#detalle-cerrar');

const estados = ['nueva', 'en_revision', 'cotizada', 'aceptada', 'rechazada'];
const etiquetasEstado = {
  nueva: 'Nueva',
  en_revision: 'En revisión',
  cotizada: 'Cotizada',
  aceptada: 'Aceptada',
  rechazada: 'Rechazada',
};

let planes = [];
let clientes = [];
let servicios = [];
let solicitudActual = null;
let solicitudesCache = [];

function escapar(texto) {
  return String(texto ?? '')
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

function fecha(valor) {
  if (!valor) {
    return '—';
  }

  const texto = String(valor).includes('T')
    ? String(valor)
    : `${String(valor).replace(' ', 'T')}Z`;
  const fechaObj = new Date(texto);

  if (Number.isNaN(fechaObj.getTime())) {
    return String(valor);
  }

  return new Intl.DateTimeFormat('es-MX', { dateStyle: 'medium' }).format(fechaObj);
}

function nombreServicio(slug) {
  return servicios.find((servicio) => servicio.slug === slug)?.nombre ?? slug;
}

function badgeEstado(estado) {
  return `<span class="badge status status--${escapar(estado)}">${escapar(etiquetasEstado[estado] ?? estado)}</span>`;
}

async function cargarCatalogos() {
  const [planesData, clientesData, serviciosData] = await Promise.all([
    apiGet('/planes'),
    apiGet('/admin/clientes'),
    apiGet('/servicios'),
  ]);

  planes = planesData;
  clientes = clientesData;
  servicios = serviciosData;

  filtroPlan.innerHTML = '<option value="">Todos</option>' + planes
    .map((plan) => `<option value="${plan.id}">${escapar(plan.nombre)}</option>`)
    .join('');
}

function parametrosFiltro() {
  const params = new URLSearchParams();

  if (filtroEstado.value) {
    params.set('estado', filtroEstado.value);
  }

  if (filtroPlan.value) {
    params.set('plan_id', filtroPlan.value);
  }

  if (filtroQ.value.trim()) {
    params.set('q', filtroQ.value.trim());
  }

  return params;
}

async function cargarSolicitudes() {
  const params = parametrosFiltro();
  const query = params.toString();

  lista.innerHTML = '<div class="empty-state">Cargando solicitudes…</div>';

  try {
    const data = await apiGet(`/admin/solicitudes${query ? `?${query}` : ''}`);
    solicitudesCache = data;
    renderLista(data);
  } catch (error) {
    lista.innerHTML = '<div class="empty-state">No se pudieron cargar las solicitudes.</div>';
    mostrarAlerta(alerta, 'error', error.message);
  }
}

function renderLista(solicitudes) {
  if (solicitudes.length === 0) {
    lista.innerHTML = '<div class="empty-state">No hay solicitudes con estos filtros.</div>';
    return;
  }

  lista.innerHTML = solicitudes.map((solicitud) => `
    <button class="lead${solicitudActual?.id === solicitud.id ? ' is-active' : ''}" type="button" data-id="${solicitud.id}">
      <span class="lead__main">
        <span class="lead__title">${escapar(solicitud.nombre_contacto)}</span>
        <span class="lead__company">${escapar(solicitud.empresa ?? 'Sin empresa')}</span>
        <span class="lead__meta">${escapar(solicitud.servicio_nombre ?? 'Sin servicio')} · ${fecha(solicitud.creado_en)}</span>
      </span>
      <span class="lead__side">
        ${badgeEstado(solicitud.estado)}
        <span class="lead__plan">${escapar(solicitud.plan_nombre ?? 'Sin plan')}</span>
        <span class="lead__money">${solicitud.presupuesto_estimado !== null ? dinero(solicitud.presupuesto_estimado) : 'Sin presupuesto'}</span>
      </span>
    </button>
  `).join('');
}

function opcionesEstado(seleccionado) {
  return estados.map((estado) => `
    <option value="${estado}"${estado === seleccionado ? ' selected' : ''}>${escapar(etiquetasEstado[estado])}</option>
  `).join('');
}

function opcionesPlan(seleccionado) {
  return '<option value="">Sin plan</option>' + planes.map((plan) => `
    <option value="${plan.id}"${plan.id === seleccionado ? ' selected' : ''}>${escapar(plan.nombre)}</option>
  `).join('');
}

function opcionesCliente(seleccionado) {
  return '<option value="">Sin cliente vinculado</option>' + clientes.map((cliente) => `
    <option value="${cliente.id}"${cliente.id === seleccionado ? ' selected' : ''}>${escapar(cliente.empresa)} — ${escapar(cliente.contacto)}</option>
  `).join('');
}

function bloqueProyecto(solicitud) {
  if (solicitud.proyecto_id) {
    return `
      <div class="convert-box">
        <h3 class="panel__title">Proyecto creado</h3>
        <p class="muted">Esta solicitud ya está vinculada al proyecto #${solicitud.proyecto_id}.</p>
        <a class="btn btn--secondary" href="/pages/admin/proyectos.html">Ver proyectos</a>
      </div>
    `;
  }

  return `
    <div class="convert-box">
      <h3 class="panel__title">Convertir en proyecto</h3>
      <p class="form__hint">
        Requiere un cliente vinculado. Si aún no existe, selecciónalo arriba y guarda los cambios.
      </p>
      <div class="form__field">
        <label class="form__label" for="convertir-nombre">Nombre del proyecto</label>
        <input class="form__input" id="convertir-nombre" type="text" maxlength="150" value="${escapar(`Proyecto para ${solicitud.empresa ?? solicitud.cliente_empresa ?? 'cliente'}`)}">
      </div>
      <button class="btn" type="button" id="convertir-solicitud">Crear proyecto</button>
    </div>
  `;
}

function renderDetalle(solicitud) {
  solicitudActual = solicitud;
  detalleTitulo.textContent = `${solicitud.nombre_contacto} · ${solicitud.empresa ?? 'Sin empresa'}`;

  const serviciosTexto = solicitud.servicios_interes.length > 0
    ? solicitud.servicios_interes.map((slug) => escapar(nombreServicio(slug))).join(', ')
    : 'Sin servicios seleccionados';

  detalleContenido.innerHTML = `
    <div class="detail-grid">
      <div class="detail-grid__item">
        <span class="detail-label">Contacto</span>
        <span class="detail-value">${escapar(solicitud.nombre_contacto)}</span>
      </div>
      <div class="detail-grid__item">
        <span class="detail-label">Correo</span>
        <span class="detail-value">${escapar(solicitud.email)}</span>
      </div>
      <div class="detail-grid__item">
        <span class="detail-label">Empresa</span>
        <span class="detail-value">${escapar(solicitud.empresa ?? '—')}</span>
      </div>
      <div class="detail-grid__item">
        <span class="detail-label">Giro</span>
        <span class="detail-value">${escapar(solicitud.giro ?? '—')}</span>
      </div>
      <div class="detail-grid__item">
        <span class="detail-label">Teléfono</span>
        <span class="detail-value">${escapar(solicitud.telefono ?? '—')}</span>
      </div>
      <div class="detail-grid__item">
        <span class="detail-label">Origen</span>
        <span class="detail-value">${escapar(solicitud.origen)}</span>
      </div>
      <div class="detail-grid__item">
        <span class="detail-label">Creada</span>
        <span class="detail-value">${fecha(solicitud.creado_en)}</span>
      </div>
      <div class="detail-grid__item">
        <span class="detail-label">Presupuesto</span>
        <span class="detail-value">${solicitud.presupuesto_estimado !== null ? dinero(solicitud.presupuesto_estimado) : '—'}</span>
      </div>
      <div class="detail-grid__item">
        <span class="detail-label">Meta de crecimiento</span>
        <span class="detail-value">${solicitud.meta_crecimiento_pct !== null ? `${solicitud.meta_crecimiento_pct} %` : '—'}</span>
      </div>
      <div class="detail-grid__item">
        <span class="detail-label">Ahorro semanal</span>
        <span class="detail-value">${solicitud.ahorro_horas_semana !== null ? `${solicitud.ahorro_horas_semana} h` : '—'}</span>
      </div>
      <div class="detail-grid__item">
        <span class="detail-label">Plazo deseado</span>
        <span class="detail-value">${escapar(solicitud.plazo_deseado ?? '—')}</span>
      </div>
      <div class="detail-grid__item">
        <span class="detail-label">Etapa de decisión</span>
        <span class="detail-value">${escapar(solicitud.etapa_decision ?? '—')}</span>
      </div>
    </div>

    <div class="detail-block">
      <h3 class="panel__title">Diagnóstico</h3>
      <p><strong>Servicios:</strong> ${serviciosTexto}</p>
      <p><strong>Necesidad:</strong> ${escapar(solicitud.necesidades ?? solicitud.descripcion)}</p>
      <p><strong>Procesos a eficientar:</strong> ${escapar(solicitud.procesos_automatizar ?? '—')}</p>
      <p><strong>Plan recomendado:</strong> ${escapar(solicitud.plan_nombre ?? 'Sin plan')} ${solicitud.puntaje !== null ? `(puntaje ${solicitud.puntaje})` : ''}</p>
      <p class="muted">${escapar(solicitud.recomendacion ?? '')}</p>
    </div>

    <div class="detail-block">
      <h3 class="panel__title">Gestión</h3>
      <div class="form-row">
        <div class="form__field">
          <label class="form__label" for="detalle-estado">Estado</label>
          <select class="form__select" id="detalle-estado">${opcionesEstado(solicitud.estado)}</select>
        </div>
        <div class="form__field">
          <label class="form__label" for="detalle-plan">Plan propuesto</label>
          <select class="form__select" id="detalle-plan">${opcionesPlan(solicitud.plan_id)}</select>
        </div>
      </div>
      <div class="form__field">
        <label class="form__label" for="detalle-cliente">Cliente vinculado</label>
        <select class="form__select" id="detalle-cliente">${opcionesCliente(solicitud.cliente_id)}</select>
      </div>
      <div class="form__field">
        <label class="form__label" for="detalle-notas">Notas internas</label>
        <textarea class="form__textarea" id="detalle-notas" maxlength="5000">${escapar(solicitud.notas_admin ?? '')}</textarea>
      </div>
      <button class="btn" type="button" id="guardar-solicitud">Guardar cambios</button>
    </div>

    ${bloqueProyecto(solicitud)}
  `;

  detallePanel.hidden = false;
  document.querySelector('#guardar-solicitud')?.addEventListener('click', guardarSolicitud);
  document.querySelector('#convertir-solicitud')?.addEventListener('click', convertirSolicitud);

  renderLista(solicitudesCache);
}

async function abrirDetalle(id) {
  ocultarAlerta(alerta);

  try {
    const solicitud = await apiGet(`/admin/solicitudes/${id}`);
    renderDetalle(solicitud);
    detallePanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
  } catch (error) {
    mostrarAlerta(alerta, 'error', error.message);
  }
}

async function guardarSolicitud() {
  if (!solicitudActual) {
    return;
  }

  const boton = document.querySelector('#guardar-solicitud');
  const planId = document.querySelector('#detalle-plan').value;
  const clienteId = document.querySelector('#detalle-cliente').value;

  setBotonCargando(boton, true, 'Guardando…');

  try {
    const actualizada = await apiPatch(`/admin/solicitudes/${solicitudActual.id}`, {
      estado: document.querySelector('#detalle-estado').value,
      plan_id: planId ? Number(planId) : null,
      cliente_id: clienteId ? Number(clienteId) : null,
      notas_admin: document.querySelector('#detalle-notas').value.trim(),
    });

    mostrarAlerta(alerta, 'success', 'Solicitud actualizada.');
    await cargarSolicitudes();
    renderDetalle(actualizada);
  } catch (error) {
    mostrarAlerta(alerta, 'error', error.message);
    setBotonCargando(boton, false);
  }
}

async function convertirSolicitud() {
  if (!solicitudActual) {
    return;
  }

  const boton = document.querySelector('#convertir-solicitud');
  const nombre = document.querySelector('#convertir-nombre')?.value.trim() ?? '';

  setBotonCargando(boton, true, 'Creando proyecto…');

  try {
    const data = await apiPost(`/admin/solicitudes/${solicitudActual.id}/convertir`, {
      nombre,
      estado: 'planificado',
    });

    mostrarAlerta(alerta, 'success', `Proyecto creado: ${data.proyecto.nombre}.`);
    await cargarSolicitudes();
    await abrirDetalle(solicitudActual.id);
  } catch (error) {
    mostrarAlerta(alerta, 'error', error.message);
    setBotonCargando(boton, false);
  }
}

async function init() {
  const usuario = await iniciarPagina();

  if (!protegerPagina(usuario, ['admin'])) {
    return;
  }

  try {
    await cargarCatalogos();
  } catch (error) {
    mostrarAlerta(alerta, 'error', error.message);
  }

  await cargarSolicitudes();

  lista.addEventListener('click', (event) => {
    const tarjeta = event.target.closest('[data-id]');

    if (tarjeta) {
      abrirDetalle(Number(tarjeta.dataset.id));
    }
  });

  filtroAplicar?.addEventListener('click', cargarSolicitudes);
  filtroQ?.addEventListener('keydown', (event) => {
    if (event.key === 'Enter') {
      event.preventDefault();
      cargarSolicitudes();
    }
  });

  detalleCerrar?.addEventListener('click', () => {
    solicitudActual = null;
    detallePanel.hidden = true;
    renderLista(solicitudesCache);
  });
}

init();
