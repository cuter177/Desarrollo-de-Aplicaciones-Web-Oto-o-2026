import { apiGet, apiPatch, apiPost } from '../api.js';
import { iniciarPagina } from '../app.js';
import { protegerPagina } from '../auth.js';
import { aplicarConfig } from '../public.js';
import { escapar, mensajeError, mostrarAlerta, ocultarAlerta, setBotonCargando } from '../ui.js';

const alerta = document.querySelector('#admin-alert');
const lista = document.querySelector('#clientes-lista');
const detalle = document.querySelector('#cliente-detalle');
const form = document.querySelector('#cliente-form');
const idInput = document.querySelector('#cliente-id');
const usuarioCampo = document.querySelector('#cliente-usuario-campo');
const usuarioSelect = document.querySelector('#cliente-usuario');
const empresaInput = document.querySelector('#cliente-empresa');
const telefonoInput = document.querySelector('#cliente-telefono');
const sitioInput = document.querySelector('#cliente-sitio');
const industriaInput = document.querySelector('#cliente-industria');
const guardarBoton = document.querySelector('#cliente-guardar');
const cancelarBoton = document.querySelector('#cliente-cancelar');
const titulo = document.querySelector('#cliente-form-title');

let clientes = [];

function render() {
  if (clientes.length === 0) {
    lista.innerHTML = '<div class="empty-state">Todavía no hay clientes.</div>';
    return;
  }

  lista.innerHTML = `
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th scope="col">Empresa</th>
            <th scope="col">Contacto</th>
            <th scope="col"><span class="u-visually-hidden">Acciones</span></th>
          </tr>
        </thead>
        <tbody>
          ${clientes.map((cliente) => `
            <tr>
              <td>
                <strong>${escapar(cliente.empresa)}</strong>
                <span class="table__meta">${escapar(cliente.industria || 'Sin industria')}</span>
              </td>
              <td>
                ${escapar(cliente.contacto)}
                <span class="table__meta">${escapar(cliente.email)}</span>
              </td>
              <td class="table__actions">
                <button class="btn btn--ghost" type="button" data-ver="${cliente.id}">Ver</button>
                <button class="btn btn--ghost" type="button" data-editar="${cliente.id}">Editar</button>
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
  usuarioCampo.hidden = false;
  usuarioSelect.required = true;
  titulo.textContent = 'Vincular cliente';
  guardarBoton.textContent = 'Crear cliente';
  cancelarBoton.hidden = true;
}

async function cargarUsuariosLibres() {
  const usuarios = await apiGet('/admin/usuarios?rol=cliente');
  const libres = usuarios.filter((usuario) => usuario.cliente_id === null && usuario.activo);
  usuarioSelect.innerHTML = '<option value="">Selecciona un usuario</option>' + libres
    .map((usuario) => `<option value="${usuario.id}">${escapar(usuario.nombre)} — ${escapar(usuario.email)}</option>`)
    .join('');
}

async function cargar() {
  clientes = await apiGet('/admin/clientes');
  render();
}

function editar(id) {
  const cliente = clientes.find((item) => item.id === id);

  if (!cliente) {
    return;
  }

  idInput.value = String(cliente.id);
  empresaInput.value = cliente.empresa;
  telefonoInput.value = cliente.telefono ?? '';
  sitioInput.value = cliente.sitio_web ?? '';
  industriaInput.value = cliente.industria ?? '';
  usuarioCampo.hidden = true;
  usuarioSelect.required = false;
  titulo.textContent = 'Editar cliente';
  guardarBoton.textContent = 'Guardar cambios';
  cancelarBoton.hidden = false;
  form.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

async function ver(id) {
  detalle.innerHTML = '<div class="empty-state">Cargando detalle…</div>';

  try {
    const cliente = await apiGet(`/admin/clientes/${id}`);
    const proyectos = (cliente.proyectos ?? []).map((proyecto) => `<li>${escapar(proyecto.nombre)} · ${escapar(proyecto.estado)}</li>`).join('');
    const solicitudes = (cliente.solicitudes ?? []).map((solicitud) => `<li>#${solicitud.id} · ${escapar(solicitud.estado)}</li>`).join('');
    detalle.innerHTML = `
      <h2 class="panel__title">${escapar(cliente.empresa)}</h2>
      <p>${escapar(cliente.contacto)} · ${escapar(cliente.email)}</p>
      <h3>Proyectos</h3>
      ${proyectos ? `<ul>${proyectos}</ul>` : '<p>Sin proyectos.</p>'}
      <h3>Solicitudes</h3>
      ${solicitudes ? `<ul>${solicitudes}</ul>` : '<p>Sin solicitudes.</p>'}
    `;
  } catch (error) {
    detalle.innerHTML = '<div class="empty-state">No se pudo cargar el detalle.</div>';
    mostrarAlerta(alerta, 'error', mensajeError(error));
  }
}

async function init() {
  const usuario = await iniciarPagina();
  aplicarConfig();

  if (!protegerPagina(usuario, ['admin'])) {
    return;
  }

  limpiar();

  try {
    await Promise.all([cargar(), cargarUsuariosLibres()]);
  } catch (error) {
    lista.innerHTML = '<div class="empty-state">No se pudieron cargar los clientes.</div>';
    mostrarAlerta(alerta, 'error', mensajeError(error));
  }

  cancelarBoton.addEventListener('click', limpiar);
  lista.addEventListener('click', (event) => {
    const verBoton = event.target.closest('[data-ver]');
    const editarBoton = event.target.closest('[data-editar]');

    if (verBoton) {
      ver(Number(verBoton.dataset.ver));
    }

    if (editarBoton) {
      editar(Number(editarBoton.dataset.editar));
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
      empresa: empresaInput.value.trim(),
      telefono: telefonoInput.value.trim(),
      sitio_web: sitioInput.value.trim(),
      industria: industriaInput.value.trim(),
    };
    setBotonCargando(guardarBoton, true, id ? 'Guardando…' : 'Creando…');

    try {
      if (id > 0) {
        await apiPatch(`/admin/clientes/${id}`, payload);
        mostrarAlerta(alerta, 'success', 'Cliente actualizado.');
      } else {
        await apiPost('/admin/clientes', {
          ...payload,
          usuario_id: Number(usuarioSelect.value),
        });
        mostrarAlerta(alerta, 'success', 'Cliente vinculado.');
      }

      setBotonCargando(guardarBoton, false);
      limpiar();
      await Promise.all([cargar(), cargarUsuariosLibres()]);
    } catch (error) {
      mostrarAlerta(alerta, 'error', mensajeError(error));
      setBotonCargando(guardarBoton, false);
    }
  });
}

init();
