import { apiGet } from '../api.js';
import { iniciarPagina } from '../app.js';
import { aplicarConfig, crearTarjetaProyecto } from '../public.js';

const lista = document.querySelector('#portafolio-lista');
const filtros = document.querySelector('#portafolio-filtros');

let proyectos = [];
let tecnologiaActiva = '';

function renderProyectos() {
  const visibles = tecnologiaActiva === ''
    ? proyectos
    : proyectos.filter((proyecto) => proyecto.tecnologias.includes(tecnologiaActiva));

  lista.replaceChildren();

  if (visibles.length === 0) {
    const vacio = document.createElement('div');
    vacio.className = 'empty-state';
    vacio.textContent = 'No hay casos publicados con esa herramienta.';
    lista.append(vacio);
    return;
  }

  lista.append(...visibles.map(crearTarjetaProyecto));
}

function renderFiltros() {
  filtros.replaceChildren();

  const tecnologias = [...new Set(
    proyectos.flatMap((proyecto) => proyecto.tecnologias)
  )].sort((a, b) => a.localeCompare(b, 'es'));

  const opciones = ['', ...tecnologias];

  opciones.forEach((tecnologia) => {
    const boton = document.createElement('button');
    boton.type = 'button';
    boton.className = 'filter-chip';
    boton.textContent = tecnologia === '' ? 'Todos' : tecnologia;
    boton.dataset.tecnologia = tecnologia;
    boton.setAttribute('aria-pressed', String(tecnologia === tecnologiaActiva));

    if (tecnologia === tecnologiaActiva) {
      boton.classList.add('is-active');
    }

    boton.addEventListener('click', () => {
      tecnologiaActiva = tecnologia;
      renderFiltros();
      renderProyectos();
    });

    filtros.append(boton);
  });
}

async function cargar() {
  lista.innerHTML = '<div class="empty-state">Cargando casos…</div>';

  try {
    const data = await apiGet('/portafolio');
    proyectos = Array.isArray(data) ? data : [];

    if (proyectos.length === 0) {
      lista.innerHTML = '<div class="empty-state">Todavía no hay casos publicados.</div>';
      filtros.replaceChildren();
      return;
    }

    renderFiltros();
    renderProyectos();
  } catch {
    lista.replaceChildren();

    const caja = document.createElement('div');
    caja.className = 'empty-state';

    const texto = document.createElement('p');
    texto.textContent = 'No se pudo cargar el portafolio. Revisa tu conexión e inténtalo de nuevo.';

    const boton = document.createElement('button');
    boton.className = 'btn btn--secondary';
    boton.type = 'button';
    boton.textContent = 'Reintentar';
    boton.addEventListener('click', cargar);

    caja.append(texto, boton);
    lista.append(caja);
  }
}

async function init() {
  await iniciarPagina();
  aplicarConfig();
  cargar();
}

init();
