import { apiDelete, apiGet, apiPatch, apiPost } from '../api.js';
import { iniciarPagina } from '../app.js';
import { protegerPagina } from '../auth.js';
import { aplicarConfig } from '../public.js';
import { escapar, mensajeError, mostrarAlerta, ocultarAlerta, setBotonCargando } from '../ui.js';

const alerta = document.querySelector('#admin-alert');
const lista = document.querySelector('#servicios-lista');
const form = document.querySelector('#servicio-form');
const idInput = document.querySelector('#servicio-id');
const nombreInput = document.querySelector('#servicio-nombre');
const slugInput = document.querySelector('#servicio-slug');
const descripcionInput = document.querySelector('#servicio-descripcion');
const beneficiosInput = document.querySelector('#servicio-beneficios');
const iconoInput = document.querySelector('#servicio-icono');
const ordenInput = document.querySelector('#servicio-orden');
const activoInput = document.querySelector('#servicio-activo');
const guardarBoton = document.querySelector('#servicio-guardar');
const cancelarBoton = document.querySelector('#servicio-cancelar');
const titulo = document.querySelector('#servicio-form-title');

let servicios = [];

function render() {
  if (servicios.length === 0) {
    lista.innerHTML = '<div class="empty-state">Todavía no hay servicios.</div>';
    return;
  }

  lista.innerHTML = `
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th scope="col">Servicio</th>
            <th scope="col">Estado</th>
            <th scope="col"><span class="u-visually-hidden">Acciones</span></th>
          </tr>
        </thead>
        <tbody>
          ${servicios.map((servicio) => `
            <tr>
              <td>
                <strong>${escapar(servicio.nombre)}</strong>
                <span class="table__meta">/${escapar(servicio.slug)} · orden ${escapar(servicio.orden)}</span>
              </td>
              <td>${servicio.activo ? '<span class="badge badge--success">Activo</span>' : '<span class="badge badge--warning">Inactivo</span>'}</td>
              <td class="table__actions">
                <button class="btn btn--ghost" type="button" data-editar="${servicio.id}">Editar</button>
                ${servicio.activo ? `<button class="btn btn--ghost" type="button" data-desactivar="${servicio.id}">Desactivar</button>` : ''}
              </td>
            </tr>
          `).join('')}
        </tbody>
      </table>
    </div>
  `;
}

function limpiar() {
  form.reset();
  idInput.value = '';
  ordenInput.value = '0';
  activoInput.checked = true;
  titulo.textContent = 'Nuevo servicio';
  guardarBoton.textContent = 'Crear servicio';
  cancelarBoton.hidden = true;
}

function editar(id) {
  const servicio = servicios.find((item) => item.id === id);

  if (!servicio) {
    return;
  }

  idInput.value = String(servicio.id);
  nombreInput.value = servicio.nombre;
  slugInput.value = servicio.slug;
  descripcionInput.value = servicio.descripcion;
  beneficiosInput.value = (servicio.beneficios ?? []).join('\n');
  iconoInput.value = servicio.icono ?? '';
  ordenInput.value = String(servicio.orden ?? 0);
  activoInput.checked = servicio.activo;
  titulo.textContent = 'Editar servicio';
  guardarBoton.textContent = 'Guardar cambios';
  cancelarBoton.hidden = false;
  form.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

async function cargar() {
  servicios = await apiGet('/admin/servicios');
  render();
}

async function init() {
  const usuario = await iniciarPagina();
  aplicarConfig();

  if (!protegerPagina(usuario, ['admin'])) {
    return;
  }

  limpiar();

  try {
    await cargar();
  } catch (error) {
    lista.innerHTML = '<div class="empty-state">No se pudieron cargar los servicios.</div>';
    mostrarAlerta(alerta, 'error', mensajeError(error));
  }

  cancelarBoton.addEventListener('click', limpiar);
  lista.addEventListener('click', async (event) => {
    const editarBoton = event.target.closest('[data-editar]');
    const desactivarBoton = event.target.closest('[data-desactivar]');

    if (editarBoton) {
      editar(Number(editarBoton.dataset.editar));
    }

    if (desactivarBoton && window.confirm('El servicio dejará de mostrarse en el sitio público.')) {
      try {
        await apiDelete(`/admin/servicios/${desactivarBoton.dataset.desactivar}`);
        mostrarAlerta(alerta, 'success', 'Servicio desactivado.');
        await cargar();
      } catch (error) {
        mostrarAlerta(alerta, 'error', mensajeError(error));
      }
    }
  });

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    ocultarAlerta(alerta);

    if (!form.reportValidity()) {
      return;
    }

    const id = Number(idInput.value) || 0;
    const payload = {
      nombre: nombreInput.value.trim(),
      slug: slugInput.value.trim(),
      descripcion: descripcionInput.value.trim(),
      beneficios: beneficiosInput.value,
      icono: iconoInput.value.trim(),
      orden: Number(ordenInput.value) || 0,
      activo: activoInput.checked,
    };
    setBotonCargando(guardarBoton, true, id ? 'Guardando…' : 'Creando…');

    try {
      if (id > 0) {
        await apiPatch(`/admin/servicios/${id}`, payload);
        mostrarAlerta(alerta, 'success', 'Servicio actualizado.');
      } else {
        await apiPost('/admin/servicios', payload);
        mostrarAlerta(alerta, 'success', 'Servicio creado.');
      }

      setBotonCargando(guardarBoton, false);
      limpiar();
      await cargar();
    } catch (error) {
      mostrarAlerta(alerta, 'error', mensajeError(error));
      setBotonCargando(guardarBoton, false);
    }
  });
}

init();
