import { apiGet, apiPost } from '../api.js';
import { iniciarPagina } from '../app.js';
import { protegerPagina } from '../auth.js';
import { aplicarConfig, etiquetaEstado, formatearFecha } from '../public.js';
import { escapar, mensajeError, mostrarAlerta, ocultarAlerta, setBotonCargando } from '../ui.js';

const alerta = document.querySelector('#portal-alert');
const lista = document.querySelector('#solicitudes-lista');
const form = document.querySelector('#solicitud-form');
const servicioSelect = document.querySelector('#solicitud-servicio');
const descripcionInput = document.querySelector('#solicitud-descripcion');
const guardarBoton = document.querySelector('#solicitud-guardar');

function render(solicitudes) {
  if (solicitudes.length === 0) {
    lista.innerHTML = '<div class="empty-state">Todavía no tienes solicitudes.</div>';
    return;
  }

  lista.innerHTML = `
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th scope="col">Solicitud</th>
            <th scope="col">Estado</th>
            <th scope="col">Fecha</th>
          </tr>
        </thead>
        <tbody>
          ${solicitudes.map((solicitud) => `
            <tr>
              <td>
                <a href="/pages/portal/solicitud-detalle.html?id=${solicitud.id}"><strong>${escapar(solicitud.servicio_nombre || solicitud.plan_nombre || 'Solicitud')}</strong></a>
                <span class="table__meta">${escapar(solicitud.descripcion).slice(0, 120)}</span>
              </td>
              <td><span class="badge status status--${escapar(solicitud.estado)}">${escapar(etiquetaEstado(solicitud.estado))}</span></td>
              <td>${escapar(formatearFecha(solicitud.creado_en, { day: 'numeric' }))}</td>
            </tr>
          `).join('')}
        </tbody>
      </table>
    </div>
  `;
}

async function cargar() {
  const solicitudes = await apiGet('/mi/solicitudes');
  render(solicitudes);
}

async function init() {
  const usuario = await iniciarPagina();
  aplicarConfig();

  if (!protegerPagina(usuario, ['cliente'])) {
    return;
  }

  try {
    const servicios = await apiGet('/servicios');
    servicioSelect.innerHTML = '<option value="">Sin servicio específico</option>' + servicios
      .map((servicio) => `<option value="${servicio.id}">${escapar(servicio.nombre)}</option>`)
      .join('');
    await cargar();
  } catch (error) {
    lista.innerHTML = '<div class="empty-state">No se pudieron cargar las solicitudes.</div>';
    mostrarAlerta(alerta, 'error', mensajeError(error));
  }

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    ocultarAlerta(alerta);

    if (!form.reportValidity()) {
      return;
    }

    const payload = { descripcion: descripcionInput.value.trim() };

    if (servicioSelect.value) {
      payload.servicio_id = Number(servicioSelect.value);
    }

    setBotonCargando(guardarBoton, true, 'Enviando…');

    try {
      await apiPost('/mi/solicitudes', payload);
      form.reset();
      mostrarAlerta(alerta, 'success', 'Solicitud registrada. Puedes consultar su estado en el historial.');
      await cargar();
    } catch (error) {
      mostrarAlerta(alerta, 'error', mensajeError(error));
    } finally {
      setBotonCargando(guardarBoton, false);
    }
  });
}

init();
