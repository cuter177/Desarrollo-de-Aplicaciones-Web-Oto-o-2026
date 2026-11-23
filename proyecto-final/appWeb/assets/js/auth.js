import { apiGet, apiPatch, apiPost, setCsrfToken } from './api.js';

export async function obtenerSesion() {
  try {
    const data = await apiGet('/auth/me');
    setCsrfToken(data.csrf_token);
    return data.usuario;
  } catch (error) {
    if (error.status === 401) {
      return null;
    }

    throw error;
  }
}

export async function obtenerCsrf() {
  const data = await apiGet('/auth/csrf');
  setCsrfToken(data.csrf_token);
  return data.csrf_token;
}

export async function iniciarSesion(email, password) {
  await obtenerCsrf();
  const data = await apiPost('/auth/login', { email, password });
  setCsrfToken(data.csrf_token);
  return data.usuario;
}

export async function registrar(datos) {
  await obtenerCsrf();
  return apiPost('/auth/registro', datos);
}

export async function cerrarSesion() {
  try {
    await apiPost('/auth/logout', {});
  } finally {
    setCsrfToken(null);
  }
}

export async function cambiarPassword(datos) {
  return apiPatch('/auth/password', datos);
}

export function rutaPorRol(usuario) {
  return usuario?.rol === 'admin'
    ? '/pages/admin/dashboard.html'
    : '/pages/portal/index.html';
}

export function redirigirPorRol(usuario) {
  window.location.href = rutaPorRol(usuario);
}

export function protegerPagina(usuario, roles) {
  if (!usuario) {
    const redirect = encodeURIComponent(
      `${window.location.pathname}${window.location.search}`
    );
    window.location.href = `/pages/login.html?redirect=${redirect}`;
    return false;
  }

  if (!roles.includes(usuario.rol)) {
    window.location.href = rutaPorRol(usuario);
    return false;
  }

  return true;
}
