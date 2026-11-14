import { cerrarSesion } from './auth.js';

export function actualizarHeader(usuario) {
  document.querySelectorAll('[data-auth]').forEach((element) => {
    const scope = element.dataset.auth;
    let visible = false;

    if (scope === 'guest') {
      visible = !usuario;
    } else if (scope === 'session') {
      visible = Boolean(usuario);
    } else {
      visible = usuario?.rol === scope;
    }

    element.hidden = !visible;
  });

  document.querySelectorAll('[data-user-name]').forEach((element) => {
    element.textContent = usuario?.nombre ?? '';
  });

  document.querySelectorAll('[data-user-email]').forEach((element) => {
    element.textContent = usuario?.email ?? '';
  });

  document.querySelectorAll('[data-user-role]').forEach((element) => {
    element.textContent = usuario?.rol ?? '';
  });

  document.querySelectorAll('[data-action="logout"]').forEach((button) => {
    if (button.dataset.bound === '1') {
      return;
    }

    button.dataset.bound = '1';
    button.addEventListener('click', async () => {
      button.disabled = true;

      try {
        await cerrarSesion();
      } catch {
        // La sesión pudo expirar; de todos modos se vuelve al inicio.
      } finally {
        window.location.href = '/index.html';
      }
    });
  });
}

export function mostrarAlerta(elemento, tipo, mensaje) {
  if (!elemento) {
    return;
  }

  elemento.className = `alert alert--${tipo}`;
  elemento.textContent = mensaje;
  elemento.hidden = false;
}

export function ocultarAlerta(elemento) {
  if (!elemento) {
    return;
  }

  elemento.hidden = true;
  elemento.textContent = '';
}

export function setBotonCargando(boton, cargando, texto = 'Procesando…') {
  if (!boton) {
    return;
  }

  if (cargando) {
    boton.dataset.label = boton.textContent;
    boton.textContent = texto;
    boton.disabled = true;
    return;
  }

  boton.textContent = boton.dataset.label || boton.textContent;
  boton.disabled = false;
}
