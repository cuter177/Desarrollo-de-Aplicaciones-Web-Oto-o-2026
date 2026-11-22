import { apiGet } from '../api.js';
import { iniciarPagina } from '../app.js';
import { aplicarConfig } from '../public.js';

async function cargarEstadisticas() {
  const servicios = document.querySelector('[data-stat="servicios"]');
  const proyectos = document.querySelector('[data-stat="proyectos"]');

  try {
    const [serviciosData, proyectosData] = await Promise.all([
      apiGet('/servicios'),
      apiGet('/portafolio'),
    ]);

    servicios.textContent = String(serviciosData.length);
    proyectos.textContent = String(proyectosData.length);
  } catch {
    servicios.textContent = '—';
    proyectos.textContent = '—';
  }
}

async function init() {
  await iniciarPagina();
  aplicarConfig();
  cargarEstadisticas();
}

init();
