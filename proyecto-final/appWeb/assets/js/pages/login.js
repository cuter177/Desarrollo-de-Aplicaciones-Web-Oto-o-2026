import { iniciarPagina } from '../app.js';
import { iniciarSesion, redirigirPorRol } from '../auth.js';
import { aplicarConfig } from '../public.js';
import { mostrarAlerta, ocultarAlerta, setBotonCargando } from '../ui.js';

const form = document.querySelector('#login-form');
const alerta = document.querySelector('#login-alert');
const boton = form?.querySelector('button[type="submit"]');
const params = new URLSearchParams(window.location.search);

async function init() {
  const usuario = await iniciarPagina();
  aplicarConfig();

  if (usuario) {
    redirigirPorRol(usuario);
    return;
  }

  if (params.get('registrado') === '1') {
    mostrarAlerta(alerta, 'success', 'Cuenta creada. Ya puedes iniciar sesión.');
  }

  form?.addEventListener('submit', async (event) => {
    event.preventDefault();
    ocultarAlerta(alerta);

    if (!form.reportValidity()) {
      return;
    }

    setBotonCargando(boton, true, 'Entrando…');

    try {
      const usuario = await iniciarSesion(
        form.email.value.trim(),
        form.password.value
      );

      const redirect = params.get('redirect');

      if (redirect && redirect.startsWith('/') && !redirect.startsWith('//')) {
        window.location.href = redirect;
        return;
      }

      redirigirPorRol(usuario);
    } catch (error) {
      mostrarAlerta(alerta, 'error', error.message);
      setBotonCargando(boton, false);
    }
  });
}

init();
