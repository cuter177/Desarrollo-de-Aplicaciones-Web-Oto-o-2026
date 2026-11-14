import { obtenerSesion } from './auth.js';
import { actualizarHeader } from './ui.js';

export async function iniciarPagina() {
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
