import { obtenerSesion } from './auth.js';
import { actualizarHeader, iniciarCuenta, iniciarMenuPanel, iniciarTema } from './ui.js';

export async function iniciarPagina() {
  iniciarTema();
  iniciarCuenta();
  iniciarMenuPanel();

  try {
    const usuario = await obtenerSesion();
    actualizarHeader(usuario);
    return usuario;
  } catch (error) {
    console.error('No se pudo inicializar la sesión:', error);
    actualizarHeader(null);
    return null;
  }
}
