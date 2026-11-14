import { iniciarPagina } from '../app.js';
import { redirigirPorRol, registrar } from '../auth.js';
import { mostrarAlerta, ocultarAlerta, setBotonCargando } from '../ui.js';

const form = document.querySelector('#registro-form');
const alerta = document.querySelector('#registro-alert');
const boton = form?.querySelector('button[type="submit"]');

async function init() {
  const usuario = await iniciarPagina();

  if (usuario) {
    redirigirPorRol(usuario);
    return;
  }

  form?.addEventListener('submit', async (event) => {
    event.preventDefault();
    ocultarAlerta(alerta);

    if (!form.reportValidity()) {
      return;
    }

    if (form.password.value !== form.password_confirmacion.value) {
      mostrarAlerta(alerta, 'error', 'Las contraseñas no coinciden.');
      return;
    }

    setBotonCargando(boton, true, 'Creando cuenta…');

    try {
      await registrar({
        nombre: form.nombre.value.trim(),
        email: form.email.value.trim(),
        empresa: form.empresa.value.trim(),
        telefono: form.telefono.value.trim(),
        password: form.password.value,
        password_confirmacion: form.password_confirmacion.value,
        acepta_terminos: form.acepta_terminos.checked,
      });

      window.location.href = '/pages/login.html?registrado=1';
    } catch (error) {
      mostrarAlerta(alerta, 'error', error.message);
      setBotonCargando(boton, false);
    }
  });
}

init();
