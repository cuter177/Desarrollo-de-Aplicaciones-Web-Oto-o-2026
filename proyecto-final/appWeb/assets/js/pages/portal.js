import { apiGet } from '../api.js';
import { iniciarPagina } from '../app.js';
import { protegerPagina } from '../auth.js';
import { aplicarConfig, etiquetaEstado, formatearFecha, pintarIdentidad } from '../public.js';
import { escapar, inicialesDe, mostrarAlerta } from '../ui.js';

const alerta = document.querySelector('#portal-alert');
const actividad = document.querySelector('#portal-actividad');

function badge(estado) {
  return `<span class="badge status status--${escapar(estado)}">${escapar(etiquetaEstado(estado))}</span>`;
}

function renderActividad(items) {
  if (!actividad) {
    return;
  }

  if (!items.length) {
    actividad.innerHTML = '<div class="empty-state">Todavía no hay proyectos ni solicitudes.</div>';
    return;
  }

  actividad.innerHTML = `
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th scope="col">Actividad</th>
            <th scope="col">Estado</th>
            <th scope="col">Fecha</th>
          </tr>
        </thead>
        <tbody>
          ${items.map((item) => {
            const href = item.tipo === 'proyecto'
              ? `/pages/portal/proyecto-detalle.html?id=${item.id}`
              : `/pages/portal/solicitud-detalle.html?id=${item.id}`;
            const tipo = item.tipo === 'proyecto' ? 'Proyecto' : 'Solicitud';

            return `
              <tr>
                <td>
                  <a href="${href}"><strong>${escapar(item.titulo)}</strong></a>
                  <span class="table__meta">${tipo}</span>
                </td>
                <td>${badge(item.estado)}</td>
                <td>${escapar(formatearFecha(item.fecha, { day: 'numeric' }))}</td>
              </tr>
            `;
          }).join('')}
        </tbody>
      </table>
    </div>
  `;
}

async function init() {
  const usuario = await iniciarPagina();
  aplicarConfig();

  if (!protegerPagina(usuario, ['cliente'])) {
    return;
  }

  pintarIdentidad({
    iniciales: inicialesDe(usuario.nombre),
    nombre: usuario.nombre,
    foto: usuario.foto,
  });

  try {
    const resumen = await apiGet('/mi/resumen');
    const cliente = resumen.cliente ?? {};

    pintarIdentidad({
      iniciales: inicialesDe(usuario.nombre),
      nombre: usuario.nombre,
      foto: usuario.foto,
      cargo: cliente.cargo,
      empresa: cliente.empresa,
      frase: cliente.frase,
      ciudad: cliente.ciudad,
      telefono: cliente.telefono,
      sitio: cliente.sitio_web,
      industria: cliente.industria,
    });
    document.querySelector('[data-metric="proyectos_activos"]').textContent = String(resumen.proyectos_activos ?? 0);
    document.querySelector('[data-metric="solicitudes_abiertas"]').textContent = String(resumen.solicitudes_abiertas ?? 0);
    document.querySelector('[data-metric="proyectos_total"]').textContent = String(resumen.proyectos_total ?? 0);
    renderActividad(resumen.actividad ?? []);
  } catch (error) {
    mostrarAlerta(alerta, 'error', error.message);
    if (actividad) {
      actividad.innerHTML = '<div class="empty-state">No se pudo cargar la actividad.</div>';
    }
  }
}

init();
