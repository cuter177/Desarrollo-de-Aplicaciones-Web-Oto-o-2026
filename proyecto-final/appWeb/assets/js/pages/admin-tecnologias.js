import { apiDelete, apiGet, apiPatch, apiPost } from '../api.js';
import { iniciarPagina } from '../app.js';
import { protegerPagina } from '../auth.js';
import { aplicarConfig } from '../public.js';
import { escapar, mensajeError, mostrarAlerta, ocultarAlerta, setBotonCargando } from '../ui.js';

const alerta = document.querySelector('#admin-alert');
const lista = document.querySelector('#tecnologias-lista');
const form = document.querySelector('#tecnologia-form');
const idInput = document.querySelector('#tecnologia-id');
const nombreInput = document.querySelector('#tecnologia-nombre');
const categoriaInput = document.querySelector('#tecnologia-categoria');
const iconoInput = document.querySelector('#tecnologia-icono');
const activoInput = document.querySelector('#tecnologia-activo');
const guardarBoton = document.querySelector('#tecnologia-guardar');
const cancelarBoton = document.querySelector('#tecnologia-cancelar');
const titulo = document.querySelector('#tecnologia-form-title');

let tecnologias = [];

function render() {
  if (tecnologias.length === 0) {
    lista.innerHTML = '<div class="empty-state">Todavía no hay tecnologías.</div>';
    return;
  }

  lista.innerHTML = `
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th scope="col">Tecnología</th>
            <th scope="col">Categoría</th>
            <th scope="col">Estado</th>
            <th scope="col"><span class="u-visually-hidden">Acciones</span></th>
          </tr>
        </thead>
        <tbody>
          ${tecnologias.map((tecnologia) => `
            <tr>
              <td><strong>${escapar(tecnologia.nombre)}</strong></td>
              <td>${escapar(tecnologia.categoria || '—')}</td>
              <td>${tecnologia.activo ? '<span class="badge badge--success">Activa</span>' : '<span class="badge badge--warning">Inactiva</span>'}</td>
              <td class="table__actions">
                <button class="btn btn--ghost" type="button" data-editar="${tecnologia.id}">Editar</button>
                ${tecnologia.activo ? `<button class="btn btn--ghost" type="button" data-desactivar="${tecnologia.id}">Desactivar</button>` : ''}
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
  activoInput.checked = true;
  titulo.textContent = 'Nueva tecnología';
  guardarBoton.textContent = 'Crear tecnología';
  cancelarBoton.hidden = true;
}

function editar(id) {
  const tecnologia = tecnologias.find((item) => item.id === id);

  if (!tecnologia) {
    return;
  }

  idInput.value = String(tecnologia.id);
  nombreInput.value = tecnologia.nombre;
  categoriaInput.value = tecnologia.categoria ?? '';
  iconoInput.value = tecnologia.icono ?? '';
  activoInput.checked = tecnologia.activo;
  titulo.textContent = 'Editar tecnología';
  guardarBoton.textContent = 'Guardar cambios';
  cancelarBoton.hidden = false;
  form.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

async function cargar() {
  tecnologias = await apiGet('/admin/tecnologias');
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
    lista.innerHTML = '<div class="empty-state">No se pudieron cargar las tecnologías.</div>';
    mostrarAlerta(alerta, 'error', mensajeError(error));
  }

  cancelarBoton.addEventListener('click', limpiar);
  lista.addEventListener('click', async (event) => {
    const editarBoton = event.target.closest('[data-editar]');
    const desactivarBoton = event.target.closest('[data-desactivar]');

    if (editarBoton) {
      editar(Number(editarBoton.dataset.editar));
    }

    if (desactivarBoton && window.confirm('La tecnología dejará de mostrarse en el sitio público.')) {
      try {
        await apiDelete(`/admin/tecnologias/${desactivarBoton.dataset.desactivar}`);
        mostrarAlerta(alerta, 'success', 'Tecnología desactivada.');
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
      categoria: categoriaInput.value.trim(),
      icono: iconoInput.value.trim(),
      activo: activoInput.checked,
    };
    setBotonCargando(guardarBoton, true, id ? 'Guardando…' : 'Creando…');

    try {
      if (id > 0) {
        await apiPatch(`/admin/tecnologias/${id}`, payload);
        mostrarAlerta(alerta, 'success', 'Tecnología actualizada.');
      } else {
        await apiPost('/admin/tecnologias', payload);
        mostrarAlerta(alerta, 'success', 'Tecnología creada.');
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
