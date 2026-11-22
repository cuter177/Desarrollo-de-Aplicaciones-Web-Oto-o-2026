import { apiGet } from '../api.js';
import { iniciarPagina } from '../app.js';
import { protegerPagina } from '../auth.js';
import { aplicarConfig, etiquetaEstado, formatearFecha } from '../public.js';
import { escapar, mostrarAlerta } from '../ui.js';

const alerta = document.querySelector('#portal-alert');
const detalle = document.querySelector('#proyecto-detalle');

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
    detalle.innerHTML = '<div class="empty-state">Falta el identificador del proyecto.</div>';
    return;
  }

  try {
    const proyecto = await apiGet(`/mi/proyectos/${id}`);
    document.title = `${proyecto.nombre} — DevStudio`;
    const demo = proyecto.url_demo
      ? `<a href="${escapar(proyecto.url_demo)}" rel="noopener noreferrer" target="_blank">Abrir demo</a>`
      : '—';
    const tecnologias = (proyecto.tecnologias ?? []).length
      ? (proyecto.tecnologias ?? []).map((item) => `<span class="badge badge--info">${escapar(item)}</span>`).join(' ')
      : '—';

    detalle.innerHTML = `
      <header class="page__header">
        <p class="hero__eyebrow">Proyecto</p>
        <h1 class="page__title">${escapar(proyecto.nombre)}</h1>
        <p>${escapar(proyecto.descripcion)}</p>
      </header>
      <section class="panel">
        <dl class="data-list">
          ${fila('Estado', `<span class="badge status status--${escapar(proyecto.estado)}">${escapar(etiquetaEstado(proyecto.estado))}</span>`)}
          ${fila('Inicio', escapar(formatearFecha(proyecto.fecha_inicio, { day: 'numeric' })))}
          ${fila('Entrega', escapar(formatearFecha(proyecto.fecha_fin, { day: 'numeric' })))}
          ${fila('Tecnologías', tecnologias)}
          ${fila('Demo', demo)}
        </dl>
      </section>
      ${proyecto.reto ? `<section class="panel"><h2 class="panel__title">Reto</h2><p>${escapar(proyecto.reto)}</p></section>` : ''}
      ${proyecto.solucion ? `<section class="panel"><h2 class="panel__title">Solución</h2><p>${escapar(proyecto.solucion)}</p></section>` : ''}
      ${proyecto.resultados ? `<section class="panel"><h2 class="panel__title">Resultados</h2><p>${escapar(proyecto.resultados)}</p></section>` : ''}
    `;
  } catch (error) {
    detalle.innerHTML = '<div class="empty-state">No se encontró el proyecto o no pertenece a tu cuenta.</div>';
    mostrarAlerta(alerta, 'error', error.message);
  }
}

init();
