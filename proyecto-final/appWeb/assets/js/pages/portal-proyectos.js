import { apiGet } from '../api.js';
import { iniciarPagina } from '../app.js';
import { protegerPagina } from '../auth.js';
import { aplicarConfig, etiquetaEstado, formatearFecha, htmlAvance } from '../public.js';
import { escapar, mostrarAlerta } from '../ui.js';

const alerta = document.querySelector('#portal-alert');
const lista = document.querySelector('#proyectos-lista');

async function init() {
  const usuario = await iniciarPagina();
  aplicarConfig();

  if (!protegerPagina(usuario, ['cliente'])) {
    return;
  }

  try {
    const proyectos = await apiGet('/mi/proyectos');

    if (proyectos.length === 0) {
      lista.innerHTML = '<div class="empty-state">Todavía no tienes proyectos asignados.</div>';
      return;
    }

    lista.innerHTML = `
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th scope="col">Proyecto</th>
              <th scope="col">Estado</th>
              <th scope="col">Avance</th>
              <th scope="col">Inicio</th>
              <th scope="col">Entrega</th>
            </tr>
          </thead>
          <tbody>
            ${proyectos.map((proyecto) => `
              <tr>
                <td>
                  <a href="/pages/portal/proyecto-detalle.html?id=${proyecto.id}"><strong>${escapar(proyecto.nombre)}</strong></a>
                  <span class="table__meta">${escapar((proyecto.tecnologias ?? []).join(', ') || 'Sin tecnologías')}</span>
                </td>
                <td><span class="badge status status--${escapar(proyecto.estado)}">${escapar(etiquetaEstado(proyecto.estado))}</span></td>
                <td>${htmlAvance(proyecto.progreso)}</td>
                <td>${escapar(formatearFecha(proyecto.fecha_inicio, { day: 'numeric' }))}</td>
                <td>${escapar(formatearFecha(proyecto.fecha_fin, { day: 'numeric' }))}</td>
              </tr>
            `).join('')}
          </tbody>
        </table>
      </div>
    `;
  } catch (error) {
    lista.innerHTML = '<div class="empty-state">No se pudieron cargar los proyectos.</div>';
    mostrarAlerta(alerta, 'error', error.message);
  }
}

init();
