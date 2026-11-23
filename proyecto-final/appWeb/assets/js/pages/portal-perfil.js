import { apiGet, apiPatch } from '../api.js';
import { iniciarPagina } from '../app.js';
import { cambiarPassword, protegerPagina } from '../auth.js';
import { aplicarConfig, pintarIdentidad } from '../public.js';
import { inicialesDe, mensajeError, mostrarAlerta, ocultarAlerta, setBotonCargando } from '../ui.js';

const alerta = document.querySelector('#portal-alert');
const form = document.querySelector('#perfil-form');
const passwordForm = document.querySelector('#password-form');
const nombreInput = document.querySelector('#perfil-nombre');
const emailInput = document.querySelector('#perfil-email');
const empresaInput = document.querySelector('#perfil-empresa');
const telefonoInput = document.querySelector('#perfil-telefono');
const sitioInput = document.querySelector('#perfil-sitio');
const industriaInput = document.querySelector('#perfil-industria');
const cargoInput = document.querySelector('#perfil-cargo');
const fraseInput = document.querySelector('#perfil-frase');
const ciudadInput = document.querySelector('#perfil-ciudad');
const guardarBoton = document.querySelector('#perfil-guardar');
const passwordBoton = document.querySelector('#password-guardar');

function mostrarTarjeta() {
  pintarIdentidad({
    iniciales: inicialesDe(nombreInput.value),
    nombre: nombreInput.value.trim(),
    cargo: cargoInput.value.trim(),
    empresa: empresaInput.value.trim(),
    frase: fraseInput.value.trim(),
    ciudad: ciudadInput.value.trim(),
    telefono: telefonoInput.value.trim(),
    sitio: sitioInput.value.trim(),
    industria: industriaInput.value.trim(),
  });
}

async function init() {
  const usuario = await iniciarPagina();
  aplicarConfig();

  if (!protegerPagina(usuario, ['cliente'])) {
    return;
  }

  try {
    const perfil = await apiGet('/mi/perfil');
    nombreInput.value = perfil.usuario.nombre ?? '';
    emailInput.value = perfil.usuario.email ?? '';
    empresaInput.value = perfil.cliente.empresa ?? '';
    telefonoInput.value = perfil.cliente.telefono ?? '';
    sitioInput.value = perfil.cliente.sitio_web ?? '';
    industriaInput.value = perfil.cliente.industria ?? '';
    cargoInput.value = perfil.cliente.cargo ?? '';
    fraseInput.value = perfil.cliente.frase ?? '';
    ciudadInput.value = perfil.cliente.ciudad ?? '';
    mostrarTarjeta();
  } catch (error) {
    mostrarAlerta(alerta, 'error', mensajeError(error));
  }

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    ocultarAlerta(alerta);

    if (!form.reportValidity()) {
      return;
    }

    setBotonCargando(guardarBoton, true, 'Guardando…');

    try {
      const perfil = await apiPatch('/mi/perfil', {
        nombre: nombreInput.value.trim(),
        empresa: empresaInput.value.trim(),
        telefono: telefonoInput.value.trim(),
        sitio_web: sitioInput.value.trim(),
        industria: industriaInput.value.trim(),
        cargo: cargoInput.value.trim(),
        frase: fraseInput.value.trim(),
        ciudad: ciudadInput.value.trim(),
      });
      mostrarTarjeta();
      document.querySelectorAll('[data-user-name]').forEach((element) => {
        element.textContent = perfil.usuario.nombre;
      });
      mostrarAlerta(alerta, 'success', 'Perfil actualizado.');
    } catch (error) {
      mostrarAlerta(alerta, 'error', mensajeError(error));
    } finally {
      setBotonCargando(guardarBoton, false);
    }
  });

  passwordForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    ocultarAlerta(alerta);

    if (!passwordForm.reportValidity()) {
      return;
    }

    setBotonCargando(passwordBoton, true, 'Actualizando…');

    try {
      await cambiarPassword({
        password_actual: document.querySelector('#password-actual').value,
        password_nueva: document.querySelector('#password-nueva').value,
        password_confirmacion: document.querySelector('#password-confirmacion').value,
      });
      passwordForm.reset();
      mostrarAlerta(alerta, 'success', 'Contraseña actualizada.');
    } catch (error) {
      mostrarAlerta(alerta, 'error', mensajeError(error));
    } finally {
      setBotonCargando(passwordBoton, false);
    }
  });
}

init();
