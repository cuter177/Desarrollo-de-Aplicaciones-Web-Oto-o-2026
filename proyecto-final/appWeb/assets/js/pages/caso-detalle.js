import { apiGet } from '../api.js';
import { iniciarPagina } from '../app.js';
import { aplicarConfig, formatearFecha, etiquetaEstado } from '../public.js';

const hero = document.querySelector('#caso-hero');
const sector = document.querySelector('#caso-sector');
const titulo = document.querySelector('#caso-titulo');
const meta = document.querySelector('#caso-meta');
const contenido = document.querySelector('#caso-contenido');
const reto = document.querySelector('#caso-reto');
const seccionSolucion = document.querySelector('#seccion-solucion');
const solucion = document.querySelector('#caso-solucion');
const seccionResultados = document.querySelector('#seccion-resultados');
const resultados = document.querySelector('#caso-resultados');
const tecnologias = document.querySelector('#caso-tecnologias');
const demo = document.querySelector('#caso-demo');
const noEncontrado = document.querySelector('#caso-no-encontrado');
const cta = document.querySelector('#caso-cta');

const params = new URLSearchParams(window.location.search);
const slug = params.get('slug');

function agregarMeta(texto) {
  if (!texto) {
    return;
  }

  const item = document.createElement('span');
  item.textContent = texto;
  meta.append(item);
}

function mostrarNoEncontrado() {
  titulo.textContent = 'Caso no encontrado';
  sector.textContent = 'Portafolio';
  meta.replaceChildren();
  contenido.hidden = true;
  cta.hidden = true;
  noEncontrado.hidden = false;
  document.title = 'Caso no encontrado — [Nombre de la empresa]';
}

function renderTecnologias(lista) {
  tecnologias.replaceChildren();

  if (!Array.isArray(lista) || lista.length === 0) {
    const item = document.createElement('li');
    item.className = 'tech';
    item.textContent = 'Por definir';
    tecnologias.append(item);
    return;
  }

  lista.forEach((nombre) => {
    const item = document.createElement('li');
    item.className = 'tech';
    item.textContent = nombre;
    tecnologias.append(item);
  });
}

async function cargar() {
  if (!slug) {
    mostrarNoEncontrado();
    return;
  }

  try {
    const proyecto = await apiGet(`/portafolio/${encodeURIComponent(slug)}`);

    document.title = `${proyecto.nombre} — [Nombre de la empresa]`;
    sector.textContent = proyecto.sector ?? 'Caso de éxito';
    titulo.textContent = proyecto.nombre;

    agregarMeta(proyecto.cliente ? `Cliente: ${proyecto.cliente}` : null);
    agregarMeta(proyecto.sector ? `Sector: ${proyecto.sector}` : null);
    agregarMeta(proyecto.fecha_fin || proyecto.fecha_inicio
      ? formatearFecha(proyecto.fecha_fin || proyecto.fecha_inicio)
      : null);
    agregarMeta(`Estado: ${etiquetaEstado(proyecto.estado)}`);

    reto.textContent = proyecto.reto ?? proyecto.resumen;
    seccionSolucion.hidden = !proyecto.solucion;
    solucion.textContent = proyecto.solucion ?? '';
    seccionResultados.hidden = !proyecto.resultados;
    resultados.textContent = proyecto.resultados ?? '';

    renderTecnologias(proyecto.tecnologias);

    if (proyecto.url_demo) {
      demo.href = proyecto.url_demo;
      demo.hidden = false;
    } else {
      demo.hidden = true;
    }

    contenido.hidden = false;
    cta.hidden = false;
    noEncontrado.hidden = true;
  } catch (error) {
    if (error.status === 404) {
      mostrarNoEncontrado();
      return;
    }

    titulo.textContent = 'No se pudo cargar el caso';
    meta.replaceChildren();
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
