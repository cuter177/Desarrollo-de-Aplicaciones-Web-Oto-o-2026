const API_BASE = '/api/v1';

let csrfToken = null;

export function setCsrfToken(token) {
  csrfToken = typeof token === 'string' && token !== '' ? token : null;
}

export function getCsrfToken() {
  return csrfToken;
}

async function request(path, { method = 'GET', body, formData, headers = {} } = {}) {
  const options = {
    method,
    credentials: 'same-origin',
    headers: {
      Accept: 'application/json',
      ...headers,
    },
  };

  if (formData instanceof FormData) {
    options.body = formData;
  } else if (body !== undefined) {
    options.headers['Content-Type'] = 'application/json';
    options.body = JSON.stringify(body);
  }

  if (!['GET', 'HEAD'].includes(method) && csrfToken) {
    options.headers['X-CSRF-Token'] = csrfToken;
  }

  let response;

  try {
    response = await fetch(`${API_BASE}${path}`, options);
  } catch {
    const error = new Error('No se pudo conectar con el servidor.');
    error.code = 'SIN_CONEXION';
    error.status = 0;
    error.details = {};
    throw error;
  }

  const payload = await response.json().catch(() => ({}));

  if (!response.ok) {
    const error = new Error(
      payload?.error?.message ?? 'No se pudo completar la operación.'
    );
    error.code = payload?.error?.code ?? 'ERROR';
    error.status = response.status;
    error.details = payload?.error?.details ?? {};
    throw error;
  }

  return payload.data;
}

export function apiGet(path) {
  return request(path);
}

export function apiPost(path, body = {}) {
  return request(path, { method: 'POST', body });
}

export function apiPostArchivo(path, archivo, campo = 'foto') {
  const datos = new FormData();
  datos.append(campo, archivo);
  return request(path, { method: 'POST', formData: datos });
}

export function apiPatch(path, body = {}) {
  return request(path, { method: 'PATCH', body });
}

export function apiDelete(path) {
  return request(path, { method: 'DELETE' });
}
