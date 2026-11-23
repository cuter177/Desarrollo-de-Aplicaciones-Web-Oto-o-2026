import { apiGet } from '../api.js';
import { iniciarPagina } from '../app.js';
import { aplicarConfig, crearTarjetaProyecto, enfoqueDe, iconoServicio } from '../public.js';

const hero = document.querySelector('#servicio-hero');
const titulo = document.querySelector('#servicio-titulo');
const descripcion = document.querySelector('#servicio-descripcion');
const acciones = document.querySelector('#servicio-acciones');
const contenido = document.querySelector('#servicio-contenido');
const beneficios = document.querySelector('#servicio-beneficios');
const panelBeneficios = document.querySelector('#panel-beneficios');
const casos = document.querySelector('#servicio-casos');
const icono = document.querySelector('#servicio-icono');
const nombreTecnico = document.querySelector('#servicio-nombre-tecnico');
const alcance = document.querySelector('#servicio-alcance');
const alcanceLabel = document.querySelector('#servicio-alcance-label');
const tecnologias = document.querySelector('#servicio-tecnologias');
const noEncontrado = document.querySelector('#servicio-no-encontrado');
const cta = document.querySelector('#servicio-cta');

const params = new URLSearchParams(window.location.search);
const slug = params.get('slug');

function mostrarNoEncontrado() {
  titulo.textContent = 'Solución no encontrada';
  descripcion.textContent = '';
  hero.querySelector('.hero__eyebrow').textContent = 'Soluciones';
  acciones.hidden = true;
  contenido.hidden = true;
  cta.hidden = true;
  noEncontrado.hidden = false;
  document.title = 'Solución no encontrada — DevStudio';
}

function llenarLista(contenedor, textos) {
  contenedor.replaceChildren();

  textos.forEach((texto) => {
    const item = document.createElement('li');
    item.textContent = texto;
    contenedor.append(item);
  });
}

function renderBeneficios(servicio) {
  const enfoque = enfoqueDe(servicio);
  const guardados = Array.isArray(servicio.beneficios) ? servicio.beneficios : [];
  const visibles = enfoque?.beneficios ?? guardados;

  llenarLista(beneficios, visibles);
  panelBeneficios.hidden = visibles.length === 0;

  const tecnicos = enfoque ? guardados : [];
  llenarLista(alcance, tecnicos);
  alcance.hidden = tecnicos.length === 0;
  alcanceLabel.hidden = tecnicos.length === 0;
}

function renderTecnologias(lista) {
  tecnologias.replaceChildren();

  if (!Array.isArray(lista) || lista.length === 0) {
    const item = document.createElement('li');
    item.textContent = 'El detalle de herramientas se comparte en la conversación.';
    tecnologias.append(item);
    return;
  }

  lista.forEach((herramienta) => {
    const item = document.createElement('li');
    item.className = 'tech';
    item.textContent = herramienta.nombre;
    tecnologias.append(item);
  });
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

    const enfoque = enfoqueDe(servicio);
    titulo.textContent = enfoque?.titulo ?? servicio.nombre;
    descripcion.textContent = enfoque?.texto ?? servicio.descripcion;
    document.title = `${enfoque?.titulo ?? servicio.nombre} — DevStudio`;
    nombreTecnico.textContent = servicio.nombre;
    icono.innerHTML = iconoServicio(servicio.icono);

    renderBeneficios(servicio);
    renderCasos(data.casos);

    try {
      renderTecnologias(await apiGet('/tecnologias'));
    } catch {
      renderTecnologias([]);
    }

    acciones.hidden = false;
    contenido.hidden = false;
    cta.hidden = false;
    noEncontrado.hidden = true;
  } catch (error) {
    if (error.status === 404) {
      mostrarNoEncontrado();
      return;
    }

    titulo.textContent = 'No se pudo cargar la solución';
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
