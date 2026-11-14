import { iniciarPagina } from '../app.js';
import { protegerPagina } from '../auth.js';

async function init() {
  const usuario = await iniciarPagina();

  if (!protegerPagina(usuario, ['cliente', 'admin'])) {
    return;
  }

  document.querySelectorAll('[data-portal-nombre]').forEach((element) => {
    element.textContent = usuario.nombre;
  });

  document.querySelectorAll('[data-portal-empresa]').forEach((element) => {
    element.textContent = usuario.cliente_id
      ? `Cliente #${usuario.cliente_id}`
      : 'Perfil de administrador';
  });
}

init();
