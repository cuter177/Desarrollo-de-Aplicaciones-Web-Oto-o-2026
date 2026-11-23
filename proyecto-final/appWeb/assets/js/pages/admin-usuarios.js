import { apiGet, apiPatch, apiPost } from '../api.js';
import { iniciarPagina } from '../app.js';
import { protegerPagina } from '../auth.js';
import { aplicarConfig } from '../public.js';
import { escapar, mensajeError, mostrarAlerta, ocultarAlerta, setBotonCargando } from '../ui.js';

const alerta = document.querySelector('#admin-alert');
const lista = document.querySelector('#usuarios-lista');
const form = document.querySelector('#usuario-form');
const idInput = document.querySelector('#usuario-id');
const nombreInput = document.querySelector('#usuario-nombre');
const emailInput = document.querySelector('#usuario-email');
const passwordInput = document.querySelector('#usuario-password');
const passwordCampo = document.querySelector('#usuario-password-campo');
const rolSelect = document.querySelector('#usuario-rol');
const activoInput = document.querySelector('#usuario-activo');
const empresaCampo = document.querySelector('#usuario-empresa-campos');
const empresaInput = document.querySelector('#usuario-empresa');
const telefonoInput = document.querySelector('#usuario-telefono');
const guardarBoton = document.querySelector('#usuario-guardar');
const cancelarBoton = document.querySelector('#usuario-cancelar');
const titulo = document.querySelector('#usuario-form-title');
const buscarInput = document.querySelector('#usuario-buscar');

let usuarios = [];

function render() {
  if (usuarios.length === 0) {
    lista.innerHTML = '<div class="empty-state">No hay usuarios con ese filtro.</div>';
    return;
  }

  lista.innerHTML = `
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th scope="col">Usuario</th>
            <th scope="col">Rol</th>
            <th scope="col">Estado</th>
            <th scope="col"><span class="u-visually-hidden">Acciones</span></th>
          </tr>
        </thead>
        <tbody>
          ${usuarios.map((usuario) => `
            <tr>
              <td>
                <strong>${escapar(usuario.nombre)}</strong>
                <span class="table__meta">${escapar(usuario.email)}${usuario.cliente_empresa ? ` · ${escapar(usuario.cliente_empresa)}` : ''}</span>
              </td>
              <td>${escapar(usuario.rol)}</td>
              <td>${usuario.activo ? '<span class="badge badge--success">Activo</span>' : '<span class="badge badge--warning">Inactivo</span>'}</td>
              <td class="table__actions">
                <button class="btn btn--ghost" type="button" data-editar="${usuario.id}">Editar</button>
              </td>
            </tr>
          `).join('')}
        </tbody>
      </table>
    </div>
  `;
}

function alternarEmpresa() {
  const creando = idInput.value === '';
  const esCliente = rolSelect.value === 'cliente';
  empresaCampo.hidden = !(creando && esCliente);
  passwordCampo.hidden = !creando;
  passwordInput.required = creando;
}

function limpiar() {
  form.reset();
  idInput.value = '';
  activoInput.checked = true;
  rolSelect.value = 'cliente';
  titulo.textContent = 'Nuevo usuario';
  guardarBoton.textContent = 'Crear usuario';
  cancelarBoton.hidden = true;
  alternarEmpresa();
}

function editar(id) {
  const usuario = usuarios.find((item) => item.id === id);

  if (!usuario) {
    return;
  }

  idInput.value = String(usuario.id);
  nombreInput.value = usuario.nombre;
  emailInput.value = usuario.email;
  rolSelect.value = usuario.rol;
  activoInput.checked = usuario.activo;
  passwordInput.value = '';
  titulo.textContent = 'Editar usuario';
  guardarBoton.textContent = 'Guardar cambios';
  cancelarBoton.hidden = false;
  alternarEmpresa();
  form.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

async function cargar() {
  const q = buscarInput.value.trim();
  const ruta = q ? `/admin/usuarios?q=${encodeURIComponent(q)}` : '/admin/usuarios';
  usuarios = await apiGet(ruta);
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
    lista.innerHTML = '<div class="empty-state">No se pudieron cargar los usuarios.</div>';
    mostrarAlerta(alerta, 'error', mensajeError(error));
  }

  rolSelect.addEventListener('change', alternarEmpresa);
  buscarInput.addEventListener('input', () => {
    cargar().catch((error) => mostrarAlerta(alerta, 'error', mensajeError(error)));
  });
  cancelarBoton.addEventListener('click', limpiar);
  lista.addEventListener('click', (event) => {
    const boton = event.target.closest('[data-editar]');

    if (boton) {
      editar(Number(boton.dataset.editar));
    }
  });

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    ocultarAlerta(alerta);

    if (!form.reportValidity()) {
      return;
    }

    const id = Number(idInput.value) || 0;
    setBotonCargando(guardarBoton, true, id ? 'Guardando…' : 'Creando…');

    try {
      if (id > 0) {
        await apiPatch(`/admin/usuarios/${id}`, {
          nombre: nombreInput.value.trim(),
          email: emailInput.value.trim(),
          rol: rolSelect.value,
          activo: activoInput.checked,
        });
        mostrarAlerta(alerta, 'success', 'Usuario actualizado.');
      } else {
        const payload = {
          nombre: nombreInput.value.trim(),
          email: emailInput.value.trim(),
          password: passwordInput.value,
          rol: rolSelect.value,
          activo: activoInput.checked,
        };

        if (rolSelect.value === 'cliente' && empresaInput.value.trim() !== '') {
          payload.empresa = empresaInput.value.trim();
          payload.telefono = telefonoInput.value.trim();
        }

        await apiPost('/admin/usuarios', payload);
        mostrarAlerta(alerta, 'success', 'Usuario creado.');
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
