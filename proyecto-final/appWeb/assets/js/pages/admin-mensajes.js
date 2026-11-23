import { apiGet, apiPatch } from '../api.js';
import { iniciarPagina } from '../app.js';
import { protegerPagina } from '../auth.js';
import { aplicarConfig } from '../public.js';
import { mostrarAlerta, ocultarAlerta, setBotonCargando } from '../ui.js';

const alerta = document.querySelector('#admin-alert');
const lista = document.querySelector('#mensajes-lista');
const detallePanel = document.querySelector('#mensaje-detalle');
const detalleContenido = document.querySelector('#detalle-contenido');
const detalleTitulo = document.querySelector('#detalle-title');
const detalleCerrar = document.querySelector('#detalle-cerrar');
const filtroEstado = document.querySelector('#filtro-estado');
const filtroQ = document.querySelector('#filtro-q');
const filtroAplicar = document.querySelector('#filtro-aplicar');

const etiquetasEstado = {
  nuevo: 'Nuevo',
  en_revision: 'En revisión',
  atendido: 'Atendido',
  archivado: 'Archivado',
};

let mensajeActual = null;

function fecha(valor) {
  if (!valor) {
    return '—';
  }

  const texto = String(valor).includes('T')
    ? String(valor)
    : `${String(valor).replace(' ', 'T')}Z`;
  const fechaObj = new Date(texto);

  if (Number.isNaN(fechaObj.getTime())) {
    return String(valor);
  }

  return new Intl.DateTimeFormat('es-MX', { dateStyle: 'medium' }).format(fechaObj);
}

function badgeEstado(estado) {
  const badge = document.createElement('span');
  badge.className = `badge status status--${estado}`;
  badge.textContent = etiquetasEstado[estado] ?? estado;

  return badge;
}

function filaDetalle(etiqueta, valor) {
  const item = document.createElement('div');
  item.className = 'detail-grid__item';

  const label = document.createElement('span');
  label.className = 'detail-label';
  label.textContent = etiqueta;

  const value = document.createElement('span');
  value.className = 'detail-value';

  if (valor instanceof Node) {
    value.append(valor);
  } else {
    value.textContent = valor;
  }

  item.append(label, value);

  return item;
}

function renderLista(mensajes) {
  lista.replaceChildren();

  if (!Array.isArray(mensajes) || mensajes.length === 0) {
    const vacio = document.createElement('div');
    vacio.className = 'empty-state';
    vacio.textContent = 'No hay mensajes con esos filtros.';
    lista.append(vacio);
    return;
  }

  mensajes.forEach((mensaje) => {
    const boton = document.createElement('button');
    boton.type = 'button';
    boton.className = 'lead';
    boton.dataset.id = String(mensaje.id);

    if (mensajeActual?.id === mensaje.id) {
      boton.classList.add('is-active');
    }

    const principal = document.createElement('span');
    principal.className = 'lead__main';

    const titulo = document.createElement('span');
    titulo.className = 'lead__title';
    titulo.textContent = mensaje.nombre;

    const asunto = document.createElement('span');
    asunto.className = 'lead__company';
    asunto.textContent = mensaje.asunto;

    const correo = document.createElement('span');
    correo.className = 'lead__meta';
    correo.textContent = mensaje.email;

    principal.append(titulo, asunto, correo);

    const lateral = document.createElement('span');
    lateral.className = 'lead__side';

    const estado = badgeEstado(mensaje.estado);

    const creado = document.createElement('span');
    creado.className = 'lead__meta';
    creado.textContent = fecha(mensaje.creado_en);

    lateral.append(estado, creado);
    boton.append(principal, lateral);

    boton.addEventListener('click', () => abrirDetalle(mensaje.id));

    lista.append(boton);
  });
}

function renderDetalle(mensaje) {
  mensajeActual = mensaje;
  detalleTitulo.textContent = mensaje.asunto;
  detalleContenido.replaceChildren();

  const correo = document.createElement('a');
  correo.href = `mailto:${mensaje.email}`;
  correo.textContent = mensaje.email;

  const grid = document.createElement('div');
  grid.className = 'detail-grid';
  grid.append(
    filaDetalle('Nombre', mensaje.nombre),
    filaDetalle('Correo', correo),
    filaDetalle('Estado', badgeEstado(mensaje.estado)),
    filaDetalle('Recibido', fecha(mensaje.creado_en)),
    filaDetalle('Atendido', fecha(mensaje.atendido_en))
  );

  const bloque = document.createElement('div');
  bloque.className = 'detail-block';

  const label = document.createElement('p');
  label.className = 'detail-label';
  label.textContent = 'Mensaje';

  const texto = document.createElement('p');
  texto.className = 'case-prose';
  texto.textContent = mensaje.mensaje;

  bloque.append(label, texto);

  const gestion = document.createElement('div');
  gestion.className = 'convert-box';

  const form = document.createElement('form');
  form.className = 'form';
  form.id = 'mensaje-form';

  const campo = document.createElement('div');
  campo.className = 'form__field';

  const labelEstado = document.createElement('label');
  labelEstado.className = 'form__label';
  labelEstado.htmlFor = 'mensaje-estado';
  labelEstado.textContent = 'Estado del mensaje';

  const select = document.createElement('select');
  select.className = 'form__select';
  select.id = 'mensaje-estado';

  Object.entries(etiquetasEstado).forEach(([valor, etiqueta]) => {
    const opcion = document.createElement('option');
    opcion.value = valor;
    opcion.textContent = etiqueta;
    opcion.selected = valor === mensaje.estado;
    select.append(opcion);
  });

  campo.append(labelEstado, select);

  const acciones = document.createElement('div');
  acciones.className = 'form__actions';

  const guardar = document.createElement('button');
  guardar.className = 'btn';
  guardar.type = 'submit';
  guardar.textContent = 'Guardar estado';

  acciones.append(guardar);
  form.append(campo, acciones);
  gestion.append(form);

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    ocultarAlerta(alerta);
    setBotonCargando(guardar, true, 'Guardando…');

    try {
      const data = await apiPatch(`/admin/mensajes/${mensaje.id}`, { estado: select.value });
      mostrarAlerta(alerta, 'success', 'Estado actualizado.');
      await cargarMensajes();
      renderDetalle(data.mensaje);
    } catch (error) {
      mostrarAlerta(alerta, 'error', error.message);
    } finally {
      setBotonCargando(guardar, false);
    }
  });

  detalleContenido.append(grid, bloque, gestion);
  detallePanel.hidden = false;
  detallePanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

async function abrirDetalle(id) {
  try {
    const mensaje = await apiGet(`/admin/mensajes/${id}`);
    renderDetalle(mensaje);
    await cargarMensajes();
  } catch (error) {
    mostrarAlerta(alerta, 'error', error.message);
  }
}

async function cargarMensajes() {
  const params = new URLSearchParams();

  if (filtroEstado.value) {
    params.set('estado', filtroEstado.value);
  }

  if (filtroQ.value.trim()) {
    params.set('q', filtroQ.value.trim());
  }

  const query = params.toString();
  lista.innerHTML = '<div class="empty-state">Cargando mensajes…</div>';

  try {
    const data = await apiGet(`/admin/mensajes${query ? `?${query}` : ''}`);
    renderLista(data);
  } catch (error) {
    lista.innerHTML = '<div class="empty-state">No se pudieron cargar los mensajes.</div>';
    mostrarAlerta(alerta, 'error', error.message);
  }
}

filtroAplicar.addEventListener('click', cargarMensajes);

filtroQ.addEventListener('keydown', (event) => {
  if (event.key === 'Enter') {
    event.preventDefault();
    cargarMensajes();
  }
});

detalleCerrar.addEventListener('click', () => {
  detallePanel.hidden = true;
  mensajeActual = null;
  cargarMensajes();
});

async function init() {
  const usuario = await iniciarPagina();
  aplicarConfig();

  if (!protegerPagina(usuario, ['admin'])) {
    return;
  }

  cargarMensajes();
}

init();
