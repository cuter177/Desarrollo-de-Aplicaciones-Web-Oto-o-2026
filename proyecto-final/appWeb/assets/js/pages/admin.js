import { apiGet } from '../api.js';
import { iniciarPagina } from '../app.js';
import { protegerPagina } from '../auth.js';
import { mostrarAlerta } from '../ui.js';

const alerta = document.querySelector('#admin-alert');

async function init() {
  const usuario = await iniciarPagina();

  if (!protegerPagina(usuario, ['admin'])) {
    return;
  }

  try {
    const data = await apiGet('/admin/dashboard');

    document.querySelectorAll('[data-metric]').forEach((element) => {
      const key = element.dataset.metric;
      element.textContent = data.metricas?.[key] ?? '0';
    });
  } catch (error) {
    if (error.status === 401) {
      window.location.href = '/pages/login.html';
      return;
    }

    mostrarAlerta(alerta, 'error', error.message);
  }
}

init();
