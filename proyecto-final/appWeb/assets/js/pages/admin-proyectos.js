import { apiGet, apiPatch, apiPost } from '../api.js';
import { iniciarPagina } from '../app.js';
import { protegerPagina } from '../auth.js';
import { aplicarConfig } from '../public.js';
import { mostrarAlerta, ocultarAlerta, setBotonCargando } from '../ui.js';

const alerta = document.querySelector('#admin-alert');
const lista = document.querySelector('#proyectos-lista');
const form = document.querySelector('#proyecto-form');
const idInput = document.querySelector('#proyecto-id');
const clienteSelect = document.querySelector('#proyecto-cliente');
const nombreInput = document.querySelector('#proyecto-nombre');
const descripcionInput = document.querySelector('#proyecto-descripcion');
const retoInput = document.querySelector('#proyecto-reto');
const solucionInput = document.querySelector('#proyecto-solucion');
const resultadosInput = document.querySelector('#proyecto-resultados');
const estadoSelect = document.querySelector('#proyecto-estado');
const urlInput = document.querySelector('#proyecto-url');
const inicioInput = document.querySelector('#proyecto-inicio');
const finInput = document.querySelector('#proyecto-fin');
const publicadoInput = document.querySelector('#proyecto-publicado');
const destacadoInput = document.querySelector('#proyecto-destacado');
const mostrarClienteInput = document.querySelector('#proyecto-mostrar-cliente');
const guardarBoton = document.querySelector('#proyecto-guardar');
const cancelarBoton = document.querySelector('#proyecto-cancelar');
const tituloFormulario = document.querySelector('#proyecto-form-title');

const etiquetasEstado = {
  planificado: 'Planificado',
  en_progreso: 'En progreso',
  en_revision: 'En revisión',
  entregado: 'Entregado',
  pausado: 'Pausado',
  cancelado: 'Cancelado',
};

let proyectos = [];

function escapar(texto) {
  return String(texto ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}

function fecha(valor) {
  return valor ? String(valor).slice(0, 10) : '—';
}

async function cargarClientes() {
  const clientes = await apiGet('/admin/clientes');

  clienteSelect.innerHTML = '<option value="">Selecciona un cliente</option>' + clientes
    .map((cliente) => `<option value="${cliente.id}">${escapar(cliente.empresa)} — ${escapar(cliente.contacto)}</option>`)
    .join('');
}

async function cargarProyectos() {
  lista.innerHTML = '<div class="empty-state">Cargando proyectos…</div>';

  try {
    proyectos = await apiGet('/admin/proyectos');
    renderLista();
  } catch (error) {
    lista.innerHTML = '<div class="empty-state">No se pudieron cargar los proyectos.</div>';
    mostrarAlerta(alerta, 'error', error.message);
  }
}

function renderLista() {
  if (proyectos.length === 0) {
    lista.innerHTML = '<div class="empty-state">Todavía no hay proyectos.</div>';
    return;
  }

  lista.innerHTML = `
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th scope="col">Proyecto</th>
            <th scope="col">Estado</th>
            <th scope="col">Portafolio</th>
            <th scope="col"><span class="u-visually-hidden">Acciones</span></th>
          </tr>
        </thead>
        <tbody>
          ${proyectos.map((proyecto) => `
            <tr>
              <td>
                <strong>${escapar(proyecto.nombre)}</strong>
                <span class="table__meta">${escapar(proyecto.cliente_empresa)}</span>
              </td>
              <td><span class="badge status status--${escapar(proyecto.estado)}">${escapar(etiquetasEstado[proyecto.estado] ?? proyecto.estado)}</span></td>
              <td>
                ${proyecto.publicado ? '<span class="badge badge--success">Publicado</span>' : '<span class="badge badge--warning">Borrador</span>'}
                ${proyecto.destacado ? '<span class="badge badge--info">Destacado</span>' : ''}
              </td>
              <td class="table__actions">
                <button class="btn btn--ghost" type="button" data-editar="${proyecto.id}">Editar</button>
              </td>
            </tr>
          `).join('')}
        </tbody>
      </table>
    </div>
  `;
}

function limpiarFormulario() {
  form.reset();
  idInput.value = '';
  clienteSelect.value = '';
  estadoSelect.value = 'planificado';
  tituloFormulario.textContent = 'Nuevo proyecto';
  guardarBoton.textContent = 'Crear proyecto';
  cancelarBoton.hidden = true;
}

function editarProyecto(id) {
  const proyecto = proyectos.find((item) => item.id === id);

  if (!proyecto) {
    return;
  }

  idInput.value = String(proyecto.id);
  clienteSelect.value = String(proyecto.cliente_id);
  nombreInput.value = proyecto.nombre;
  descripcionInput.value = proyecto.descripcion;
  retoInput.value = proyecto.reto ?? '';
  solucionInput.value = proyecto.solucion ?? '';
  resultadosInput.value = proyecto.resultados ?? '';
  estadoSelect.value = proyecto.estado;
  urlInput.value = proyecto.url_demo ?? '';
  inicioInput.value = proyecto.fecha_inicio ? proyecto.fecha_inicio.slice(0, 10) : '';
  finInput.value = proyecto.fecha_fin ? proyecto.fecha_fin.slice(0, 10) : '';
  publicadoInput.checked = proyecto.publicado;
  destacadoInput.checked = proyecto.destacado;
  mostrarClienteInput.checked = proyecto.mostrar_cliente;

  tituloFormulario.textContent = 'Editar proyecto';
  guardarBoton.textContent = 'Guardar cambios';
  cancelarBoton.hidden = false;
  form.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

async function guardarProyecto(event) {
  event.preventDefault();
  ocultarAlerta(alerta);

  if (!form.reportValidity()) {
    return;
  }

  const id = Number(idInput.value) || 0;
  const payload = {
    cliente_id: Number(clienteSelect.value),
    nombre: nombreInput.value.trim(),
    descripcion: descripcionInput.value.trim(),
    reto: retoInput.value.trim(),
    solucion: solucionInput.value.trim(),
    resultados: resultadosInput.value.trim(),
    estado: estadoSelect.value,
    url_demo: urlInput.value.trim(),
    fecha_inicio: inicioInput.value,
    fecha_fin: finInput.value,
    publicado: publicadoInput.checked,
    destacado: destacadoInput.checked,
    mostrar_cliente: mostrarClienteInput.checked,
  };

  setBotonCargando(guardarBoton, true, id ? 'Guardando…' : 'Creando…');

  try {
    if (id > 0) {
      await apiPatch(`/admin/proyectos/${id}`, payload);
      mostrarAlerta(alerta, 'success', 'Proyecto actualizado.');
    } else {
      await apiPost('/admin/proyectos', payload);
      mostrarAlerta(alerta, 'success', 'Proyecto creado.');
    }

    limpiarFormulario();
    await cargarProyectos();
  } catch (error) {
    mostrarAlerta(alerta, 'error', error.message);
    setBotonCargando(guardarBoton, false);
  }
}

async function init() {
  const usuario = await iniciarPagina();
  aplicarConfig();

  if (!protegerPagina(usuario, ['admin'])) {
    return;
  }

  try {
    await cargarClientes();
    await cargarProyectos();
  } catch (error) {
    mostrarAlerta(alerta, 'error', error.message);
  }

  form?.addEventListener('submit', guardarProyecto);

  lista.addEventListener('click', (event) => {
    const boton = event.target.closest('[data-editar]');

    if (boton) {
      editarProyecto(Number(boton.dataset.editar));
    }
  });

  cancelarBoton?.addEventListener('click', limpiarFormulario);
}

init();
