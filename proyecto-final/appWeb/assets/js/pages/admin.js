import { apiGet } from '../api.js';
import { iniciarPagina } from '../app.js';
import { protegerPagina } from '../auth.js';
import { aplicarConfig } from '../public.js';
import { mostrarAlerta } from '../ui.js';

const alerta = document.querySelector('#admin-alert');
const recientes = document.querySelector('#solicitudes-recientes');

const etiquetasEstado = {
  nueva: 'Nueva',
  en_revision: 'En revisión',
  cotizada: 'Cotizada',
  aceptada: 'Aceptada',
  rechazada: 'Rechazada',
};

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

function valorMetrica(key, valor) {
  if (key === 'pipeline_valor') {
    return dinero(valor);
  }

  if (key === 'tasa_conversion' || key === 'promedio_crecimiento') {
    return `${Number(valor).toFixed(1)} %`;
  }

  return String(valor ?? 0);
}

function renderRecientes(solicitudes) {
  if (!recientes) {
    return;
  }

  if (!solicitudes || solicitudes.length === 0) {
    recientes.innerHTML = '<div class="empty-state">No hay solicitudes todavía.</div>';
    return;
  }

  recientes.innerHTML = solicitudes.map((solicitud) => `
    <a class="lead" href="/pages/admin/solicitudes.html">
      <span class="lead__main">
        <span class="lead__title">${escapar(solicitud.nombre_contacto)}</span>
        <span class="lead__company">${escapar(solicitud.empresa ?? 'Sin empresa')}</span>
        <span class="lead__meta">${escapar(solicitud.plan_nombre ?? 'Sin plan')} · ${fecha(solicitud.creado_en)}</span>
      </span>
      <span class="lead__side">
        <span class="badge status status--${escapar(solicitud.estado)}">${escapar(etiquetasEstado[solicitud.estado] ?? solicitud.estado)}</span>
        <span class="lead__money">${solicitud.presupuesto_estimado !== null ? dinero(solicitud.presupuesto_estimado) : 'Sin presupuesto'}</span>
      </span>
    </a>
  `).join('');
}

async function init() {
  const usuario = await iniciarPagina();
  aplicarConfig();

  if (!protegerPagina(usuario, ['admin'])) {
    return;
  }

  try {
    const data = await apiGet('/admin/dashboard');

    document.querySelectorAll('[data-metric]').forEach((element) => {
      const key = element.dataset.metric;
      element.textContent = valorMetrica(key, data.metricas?.[key] ?? 0);
    });

    renderRecientes(data.solicitudes_recientes ?? []);
  } catch (error) {
    if (error.status === 401) {
      window.location.href = '/pages/login.html';
      return;
    }

    mostrarAlerta(alerta, 'error', error.message);
  }
}

init();
