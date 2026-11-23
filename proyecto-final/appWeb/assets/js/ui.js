import { cerrarSesion } from './auth.js';

export function iniciarTema() {
  const boton = document.querySelector('[data-theme-toggle]');

  if (!boton || boton.dataset.bound === '1') {
    return;
  }

  boton.dataset.bound = '1';

  const pintar = (tema) => {
    const noche = tema === 'noche';
    document.documentElement.dataset.theme = tema;
    boton.setAttribute('aria-pressed', noche ? 'true' : 'false');
    const label = boton.querySelector('[data-theme-label]');

    if (label) {
      label.textContent = noche ? 'Cambiar a modo día' : 'Cambiar a modo noche';
    }
  };

  pintar(document.documentElement.dataset.theme === 'noche' ? 'noche' : 'dia');

  boton.addEventListener('click', () => {
    const siguiente = document.documentElement.dataset.theme === 'noche' ? 'dia' : 'noche';

    try {
      localStorage.setItem('ds-tema', siguiente);
    } catch {
      // Si el navegador bloquea el almacenamiento, el cambio vale solo en esta visita.
    }

    pintar(siguiente);
  });
}

export function iniciarCuenta() {
  const boton = document.querySelector('.account__button');
  const menu = document.getElementById('menu-cuenta');

  if (!boton || !menu || boton.dataset.bound === '1') {
    return;
  }

  boton.dataset.bound = '1';

  const cerrar = () => {
    boton.setAttribute('aria-expanded', 'false');
    menu.hidden = true;
  };

  boton.addEventListener('click', (event) => {
    event.stopPropagation();
    const abierto = boton.getAttribute('aria-expanded') === 'true';
    boton.setAttribute('aria-expanded', abierto ? 'false' : 'true');
    menu.hidden = abierto;
  });

  document.addEventListener('click', (event) => {
    if (!menu.hidden && !menu.contains(event.target)) {
      cerrar();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && !menu.hidden) {
      cerrar();
      boton.focus();
    }
  });
}

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

  const iniciales = inicialesDe(usuario?.nombre ?? '');

  try {
    if (usuario) {
      localStorage.setItem('ds-sesion', '1');
      document.documentElement.dataset.sesion = '1';

      if (iniciales) {
        localStorage.setItem('ds-iniciales', iniciales);
      }
    } else {
      localStorage.removeItem('ds-sesion');
      localStorage.removeItem('ds-iniciales');
      delete document.documentElement.dataset.sesion;
    }
  } catch {
    // Sin almacenamiento la sesión sigue viéndose al terminar la consulta.
  }

  aplicarFoto(usuario?.foto ?? null);

  if (iniciales) {
    document.documentElement.style.setProperty('--user-initials', `"${iniciales}"`);
  } else {
    document.documentElement.style.removeProperty('--user-initials');
  }

  document.querySelectorAll('[data-user-name]').forEach((element) => {
    element.textContent = usuario?.nombre ?? '';
  });

  document.querySelectorAll('.account__button').forEach((button) => {
    button.setAttribute('aria-label', usuario?.nombre ? `Menú de cuenta de ${usuario.nombre}` : 'Menú de cuenta');
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
        try {
          localStorage.removeItem('ds-sesion');
          localStorage.removeItem('ds-iniciales');
          localStorage.removeItem('ds-foto');
          localStorage.removeItem('ds-foto-src');
        } catch {
          // La siguiente página vuelve a consultar la sesión.
        }

        delete document.documentElement.dataset.sesion;
        delete document.documentElement.dataset.foto;
        document.documentElement.style.removeProperty('--user-initials');
        document.documentElement.style.removeProperty('--user-foto');
        window.location.href = '/index.html';
      }
    });
  });
}

function esMiniatura(valor) {
  return typeof valor === 'string'
    && valor.startsWith('data:image/jpeg;base64,')
    && valor.length > 40
    && valor.length < 100000;
}

function limpiarFoto() {
  try {
    localStorage.removeItem('ds-foto');
    localStorage.removeItem('ds-foto-src');
  } catch {
    // La siguiente página vuelve a consultar la sesión.
  }

  delete document.documentElement.dataset.foto;
  document.documentElement.style.removeProperty('--user-foto');
}

function pintarMiniatura(src, marca) {
  try {
    const misma = localStorage.getItem('ds-foto') === marca
      && localStorage.getItem('ds-foto-src') === src
      && document.documentElement.dataset.foto === '1';
    localStorage.setItem('ds-foto', marca);
    localStorage.setItem('ds-foto-src', src);

    if (misma) {
      return;
    }
  } catch {
    // Sin almacenamiento la foto se pinta solo en esta página.
  }

  document.documentElement.dataset.foto = '1';
  document.documentElement.style.setProperty('--user-foto', `url("${src}")`);
}

function miniaturizar(blob) {
  return new Promise((resolve) => {
    const imagen = new Image();
    const objeto = URL.createObjectURL(blob);

    imagen.onload = () => {
      const lado = 96;
      const lienzo = document.createElement('canvas');
      lienzo.width = lado;
      lienzo.height = lado;
      const contexto = lienzo.getContext('2d');
      const escala = Math.max(lado / imagen.width, lado / imagen.height);
      const ancho = imagen.width * escala;
      const alto = imagen.height * escala;
      contexto.drawImage(imagen, (lado - ancho) / 2, (lado - alto) / 2, ancho, alto);
      URL.revokeObjectURL(objeto);
      resolve(lienzo.toDataURL('image/jpeg', 0.82));
    };

    imagen.onerror = () => {
      URL.revokeObjectURL(objeto);
      resolve('');
    };

    imagen.src = objeto;
  });
}

export function urlFoto(version) {
  const marca = version ? String(version) : '';

  try {
    const src = localStorage.getItem('ds-foto-src') || '';

    if (localStorage.getItem('ds-foto') === marca && esMiniatura(src)) {
      return src;
    }
  } catch {
    // Se usa la dirección de la cuenta si el navegador no guarda la copia.
  }

  return marca ? `/api/v1/mi/foto?v=${encodeURIComponent(marca)}` : '';
}

export async function fijarFotoLocal(archivo, version) {
  const marca = version ? String(version) : '';

  if (!/^[0-9]{10,13}$/.test(marca)) {
    limpiarFoto();
    return;
  }

  const src = await miniaturizar(archivo);

  if (!esMiniatura(src)) {
    return;
  }

  pintarMiniatura(src, marca);
}

export function aplicarFoto(version) {
  const marca = version ? String(version) : '';

  if (!/^[0-9]{10,13}$/.test(marca)) {
    limpiarFoto();
    return;
  }

  try {
    const src = localStorage.getItem('ds-foto-src') || '';

    if (localStorage.getItem('ds-foto') === marca && esMiniatura(src)) {
      pintarMiniatura(src, marca);
      return;
    }
  } catch {
    // Se intenta leer la foto desde la cuenta.
  }

  fetch(`/api/v1/mi/foto?v=${encodeURIComponent(marca)}`, { credentials: 'same-origin' })
    .then((respuesta) => (respuesta.ok ? respuesta.blob() : null))
    .then((blob) => (blob ? miniaturizar(blob) : ''))
    .then((src) => {
      if (esMiniatura(src)) {
        pintarMiniatura(src, marca);
      }
    })
    .catch(() => {
      // La inicial sigue visible si la foto no alcanza a cargarse.
    });
}

export function inicialesDe(nombre) {
  const partes = String(nombre ?? '').trim().split(/\s+/).filter(Boolean);

  return partes
    .slice(0, 2)
    .map((parte) => parte.slice(0, 1))
    .join('')
    .toLocaleUpperCase('es-MX');
}

export function escapar(texto) {
  return String(texto ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}

export function mensajeError(error) {
  const detalles = error?.details && typeof error.details === 'object'
    ? Object.values(error.details).filter((item) => typeof item === 'string')
    : [];

  return detalles[0] || error?.message || 'No se pudo completar la operación.';
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
