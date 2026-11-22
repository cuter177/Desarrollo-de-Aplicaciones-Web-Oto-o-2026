import { apiGet } from '../api.js';
import { iniciarPagina } from '../app.js';
import { protegerPagina } from '../auth.js';
import { aplicarConfig, etiquetaEstado, formatearFecha } from '../public.js';
import { escapar, mostrarAlerta } from '../ui.js';

const alerta = document.querySelector('#portal-alert');
const detalle = document.querySelector('#solicitud-detalle');

function fila(etiqueta, valor) {
  return `
    <div class="data-list__row">
      <dt class="data-list__label">${etiqueta}</dt>
      <dd class="data-list__value">${valor}</dd>
    </div>
  `;
}

async function init() {
  const usuario = await iniciarPagina();
  aplicarConfig();

  if (!protegerPagina(usuario, ['cliente'])) {
    return;
  }

  const id = Number(new URLSearchParams(window.location.search).get('id'));

  if (!id) {
    detalle.innerHTML = '<div class="empty-state">Falta el identificador de la solicitud.</div>';
    return;
  }

  try {
    const solicitud = await apiGet(`/mi/solicitudes/${id}`);
    document.title = `Solicitud #${solicitud.id} — DevStudio`;
    const servicios = (solicitud.servicios_interes ?? []).length
      ? solicitud.servicios_interes.map((item) => escapar(item)).join(', ')
      : '—';

    detalle.innerHTML = `
      <header class="page__header">
        <p class="hero__eyebrow">Solicitud</p>
        <h1 class="page__title">${escapar(solicitud.servicio_nombre || solicitud.plan_nombre || 'Seguimiento')}</h1>
      </header>
      <section class="panel">
        <dl class="data-list">
          ${fila('Estado', `<span class="badge status status--${escapar(solicitud.estado)}">${escapar(etiquetaEstado(solicitud.estado))}</span>`)}
          ${fila('Fecha', escapar(formatearFecha(solicitud.creado_en, { day: 'numeric' })))}
          ${fila('Servicio', escapar(solicitud.servicio_nombre || '—'))}
          ${fila('Plan recomendado', escapar(solicitud.plan_nombre || '—'))}
          ${fila('Servicios de interés', servicios)}
        </dl>
      </section>
      <section class="panel">
        <h2 class="panel__title">Descripción</h2>
        <p>${escapar(solicitud.descripcion)}</p>
      </section>
      <section class="panel">
        <h2 class="panel__title">Recomendación</h2>
        <p>${escapar(solicitud.recomendacion || 'El equipo todavía no publica una recomendación.')}</p>
      </section>
    `;
  } catch (error) {
    detalle.innerHTML = '<div class="empty-state">No se encontró la solicitud o no pertenece a tu cuenta.</div>';
    mostrarAlerta(alerta, 'error', error.message);
  }
}

init();
