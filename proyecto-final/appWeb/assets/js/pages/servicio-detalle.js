import { apiGet } from '../api.js';
import { iniciarPagina } from '../app.js';
import { aplicarConfig, crearTarjetaProyecto } from '../public.js';

const hero = document.querySelector('#servicio-hero');
const titulo = document.querySelector('#servicio-titulo');
const descripcion = document.querySelector('#servicio-descripcion');
const acciones = document.querySelector('#servicio-acciones');
const contenido = document.querySelector('#servicio-contenido');
const beneficios = document.querySelector('#servicio-beneficios');
const panelBeneficios = document.querySelector('#panel-beneficios');
const casos = document.querySelector('#servicio-casos');
const noEncontrado = document.querySelector('#servicio-no-encontrado');
const cta = document.querySelector('#servicio-cta');

const params = new URLSearchParams(window.location.search);
const slug = params.get('slug');

function mostrarNoEncontrado() {
  titulo.textContent = 'Servicio no encontrado';
  descripcion.textContent = '';
  hero.querySelector('.hero__eyebrow').textContent = 'Servicios';
  acciones.hidden = true;
  contenido.hidden = true;
  cta.hidden = true;
  noEncontrado.hidden = false;
  document.title = 'Servicio no encontrado — [Nombre de la empresa]';
}

function renderBeneficios(lista) {
  beneficios.replaceChildren();

  lista.forEach((beneficio) => {
    const item = document.createElement('li');
    item.textContent = beneficio;
    beneficios.append(item);
  });

  panelBeneficios.hidden = lista.length === 0;
}

function renderCasos(lista) {
  casos.replaceChildren();

  if (!Array.isArray(lista) || lista.length === 0) {
    const vacio = document.createElement('div');
    vacio.className = 'empty-state';
    vacio.textContent = 'Todavía no hay casos publicados para mostrar.';
    casos.append(vacio);
    return;
  }

  casos.append(...lista.map(crearTarjetaProyecto));
}

async function cargar() {
  if (!slug) {
    mostrarNoEncontrado();
    return;
  }

  try {
    const data = await apiGet(`/servicios/${encodeURIComponent(slug)}`);
    const servicio = data.servicio;

    titulo.textContent = servicio.nombre;
    descripcion.textContent = servicio.descripcion;
    document.title = `${servicio.nombre} — [Nombre de la empresa]`;

    renderBeneficios(Array.isArray(servicio.beneficios) ? servicio.beneficios : []);
    renderCasos(data.casos);

    acciones.hidden = false;
    contenido.hidden = false;
    cta.hidden = false;
    noEncontrado.hidden = true;
  } catch (error) {
    if (error.status === 404) {
      mostrarNoEncontrado();
      return;
    }

    titulo.textContent = 'No se pudo cargar el servicio';
    descripcion.textContent = 'Revisa tu conexión e inténtalo de nuevo.';
    acciones.hidden = true;
    contenido.hidden = true;
    cta.hidden = true;
  }
}

async function init() {
  await iniciarPagina();
  aplicarConfig();
  cargar();
}

init();
