import { apiPost } from '../api.js';
import { iniciarPagina } from '../app.js';
import { obtenerCsrf } from '../auth.js';
import { aplicarConfig } from '../public.js';
import { mostrarAlerta, ocultarAlerta, setBotonCargando } from '../ui.js';

const form = document.querySelector('#contacto-form');
const alerta = document.querySelector('#contacto-alerta');
const boton = form.querySelector('button[type="submit"]');

function limpiarErrores() {
  form.querySelectorAll('[data-error-for]').forEach((error) => {
    error.hidden = true;
    error.textContent = '';
  });

  form.querySelectorAll('[aria-invalid="true"]').forEach((campo) => {
    campo.removeAttribute('aria-invalid');
  });
}

function mostrarErrores(detalles) {
  Object.entries(detalles ?? {}).forEach(([campo, mensaje]) => {
    const error = form.querySelector(`[data-error-for="${campo}"]`);
    const control = form.elements.namedItem(campo);

    if (error) {
      error.textContent = String(mensaje);
      error.hidden = false;
    }

    if (control instanceof HTMLElement) {
      control.setAttribute('aria-invalid', 'true');
    }
  });
}

function datosFormulario() {
  return {
    nombre: form.elements.namedItem('nombre').value.trim(),
    email: form.elements.namedItem('email').value.trim(),
    asunto: form.elements.namedItem('asunto').value.trim(),
    mensaje: form.elements.namedItem('mensaje').value.trim(),
    acepta_privacidad: form.elements.namedItem('acepta_privacidad').checked,
    website: form.elements.namedItem('website').value,
  };
}

form.addEventListener('submit', async (event) => {
  event.preventDefault();
  limpiarErrores();
  ocultarAlerta(alerta);

  if (!form.reportValidity()) {
    return;
  }

  setBotonCargando(boton, true, 'Enviando…');

  try {
    await obtenerCsrf();
    const data = await apiPost('/contacto', datosFormulario());

    form.reset();
    mostrarAlerta(
      alerta,
      'success',
      data.mensaje ?? 'Mensaje recibido. Te contactaremos pronto.'
    );
    alerta.focus();
  } catch (error) {
    if (error.status === 422) {
      mostrarErrores(error.details);
      mostrarAlerta(alerta, 'error', 'Revisa los campos marcados e inténtalo de nuevo.');
    } else if (error.status === 429) {
      mostrarAlerta(alerta, 'warning', error.message);
    } else {
      mostrarAlerta(alerta, 'error', error.message);
    }

    alerta.focus();
  } finally {
    setBotonCargando(boton, false);
  }
});

async function init() {
  await iniciarPagina();
  aplicarConfig();
}

init();
