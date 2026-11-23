import { apiGet } from './api.js';

let configPromise = null;

export function obtenerConfig() {
  if (!configPromise) {
    configPromise = apiGet('/config').catch((error) => {
      configPromise = null;
      throw error;
    });
  }

  return configPromise;
}

/**
 * Rellena los elementos con data-config, data-config-link y data-social.
 */
export async function aplicarConfig(root = document) {
  let config;

  try {
    config = await obtenerConfig();
  } catch {
    return null;
  }

  root.querySelectorAll('[data-config]').forEach((elemento) => {
    const valor = config[elemento.dataset.config];

    if (typeof valor === 'string' && valor !== '') {
      elemento.textContent = valor;
    }
  });

  root.querySelectorAll('[data-config-link]').forEach((elemento) => {
    const clave = elemento.dataset.configLink;
    const valor = config[clave];

    if (typeof valor !== 'string' || valor === '') {
      elemento.hidden = true;
      return;
    }

    elemento.hidden = false;

    if (clave === 'email') {
      elemento.href = `mailto:${valor}`;
    } else if (clave === 'whatsapp') {
      elemento.href = `https://wa.me/${valor.replace(/\D/g, '')}`;
      elemento.target = '_blank';
      elemento.rel = 'noopener noreferrer';
    } else if (clave === 'telefono') {
      elemento.href = `tel:${valor.replace(/[^\d+]/g, '')}`;
    } else {
      elemento.href = valor;
    }
  });

  root.querySelectorAll('[data-social]').forEach((elemento) => {
    const valor = config.redes?.[elemento.dataset.social];

    if (typeof valor === 'string' && valor !== '') {
      elemento.href = valor;
      elemento.hidden = false;
    } else {
      elemento.hidden = true;
    }
  });

  root.querySelectorAll('[data-social-list]').forEach((grupo) => {
    grupo.hidden = grupo.querySelectorAll('[data-social]:not([hidden])').length === 0;
  });

  return config;
}

export function formatearFecha(valor, opciones = {}) {
  if (!valor) {
    return 'Sin fecha';
  }

  const texto = String(valor).slice(0, 10);
  const fecha = new Date(`${texto}T00:00:00`);

  if (Number.isNaN(fecha.getTime())) {
    return String(valor);
  }

  return new Intl.DateTimeFormat('es-MX', {
    month: 'long',
    year: 'numeric',
    ...opciones,
  }).format(fecha);
}

export function etiquetaEstado(estado) {
  const etiquetas = {
    planificado: 'Planificado',
    en_progreso: 'En progreso',
    en_revision: 'En revisión',
    entregado: 'Entregado',
    pausado: 'Pausado',
    cancelado: 'Cancelado',
    nueva: 'Nueva',
    cotizada: 'Cotizada',
    aceptada: 'Aceptada',
    rechazada: 'Rechazada',
    nuevo: 'Nuevo',
    atendido: 'Atendido',
    archivado: 'Archivado',
  };

  return etiquetas[estado] ?? estado;
}

export function crearBadge(texto, clase = 'badge--info') {
  const badge = document.createElement('span');
  badge.className = `badge ${clase}`;
  badge.textContent = texto;

  return badge;
}

export function crearListaTecnologias(tecnologias, limite = 0) {
  const lista = document.createElement('ul');
  lista.className = 'tech-list';

  const visibles = limite > 0 ? tecnologias.slice(0, limite) : tecnologias;

  visibles.forEach((tecnologia) => {
    const item = document.createElement('li');
    item.className = 'tech';
    item.textContent = tecnologia;
    lista.append(item);
  });

  return lista;
}

/**
 * Crea una tarjeta de proyecto publicada para el portafolio.
 */
export function crearTarjetaProyecto(proyecto) {
  const articulo = document.createElement('article');
  articulo.className = 'case-card';

  const media = document.createElement('div');
  media.className = 'case-card__media';
  media.setAttribute('aria-hidden', 'true');

  const iniciales = document.createElement('span');
  iniciales.className = 'case-card__initials';
  iniciales.textContent = proyecto.nombre
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((palabra) => palabra[0])
    .join('')
    .toUpperCase();
  media.append(iniciales);

  const cuerpo = document.createElement('div');
  cuerpo.className = 'case-card__body';

  const meta = document.createElement('p');
  meta.className = 'case-card__meta';
  meta.textContent = proyecto.sector ?? 'Caso de éxito';

  const titulo = document.createElement('h3');
  titulo.className = 'case-card__title';

  const enlace = document.createElement('a');
  enlace.href = `/pages/caso-detalle.html?slug=${encodeURIComponent(proyecto.slug)}`;
  enlace.textContent = proyecto.nombre;
  titulo.append(enlace);

  const resumen = document.createElement('p');
  resumen.className = 'case-card__text';
  resumen.textContent = proyecto.resultados || proyecto.resumen;

  cuerpo.append(meta, titulo, resumen);

  const pie = document.createElement('a');
  pie.className = 'case-card__link';
  pie.href = enlace.href;
  pie.textContent = 'Ver el resultado';
  cuerpo.append(pie);

  articulo.append(media, cuerpo);

  return articulo;
}

export function mostrarNoEncontrado(contenedor, mensaje, enlaceTexto, enlaceHref) {
  contenedor.replaceChildren();

  const caja = document.createElement('div');
  caja.className = 'empty-state';

  const texto = document.createElement('p');
  texto.textContent = mensaje;

  const enlace = document.createElement('a');
  enlace.className = 'btn btn--secondary';
  enlace.href = enlaceHref;
  enlace.textContent = enlaceTexto;

  caja.append(texto, enlace);
  contenedor.append(caja);
}

export const enfoqueServicio = {
  'desarrollo-web': {
    titulo: 'Que te encuentren',
    texto: 'Una página clara para que alguien de Puebla sepa qué ofreces, dónde estás y cómo contactarte.',
    beneficios: [
      'Se entiende qué vendes en el primer vistazo',
      'Te escriben o te llaman sin buscarte en redes',
      'Se ve bien en el teléfono, que es donde te buscan',
      'Puedes cambiar lo que ofreces sin empezar de cero',
    ],
  },
  dashboards: {
    titulo: 'Que la operación no dependa de mensajes',
    texto: 'Ventas, citas y pendientes en un solo lugar, para decidir sin armar reportes a mano ni preguntar en el chat.',
    beneficios: [
      'Ves cómo va el negocio sin pedir un reporte',
      'El equipo deja de preguntar lo mismo por WhatsApp',
      'Comparas periodos o sucursales con los mismos números',
      'Decides con lo que pasó, no con lo que se recuerda',
    ],
  },
  'apps-moviles': {
    titulo: 'Que atiendas desde el teléfono',
    texto: 'Tus clientes piden, consultan o regresan desde el celular, sin llamarte cada vez.',
    beneficios: [
      'Agendan o piden desde el celular',
      'Reciben un recordatorio sin que alguien llame',
      'Atiendes fuera del local con la misma información',
      'El teléfono deja de ser la única agenda',
    ],
  },
  consultoria: {
    titulo: 'Que sepas qué conviene hacer primero',
    texto: 'Ordenamos el problema y te decimos el siguiente paso, sin que tengas que elegir herramientas.',
    beneficios: [
      'Sales con un orden claro de qué hacer primero',
      'No tienes que elegir herramientas tú solo',
      'El plan cabe en cómo trabajas hoy',
      'Sabes qué puedes dejar para después',
    ],
  },
};

export function enfoqueDe(servicio) {
  if (!servicio || typeof servicio.slug !== 'string') {
    return null;
  }

  return enfoqueServicio[servicio.slug] ?? null;
}

export function iconoServicio(icono) {
  const iconos = {
    code: '<path d="m8 9-3 3 3 3M16 9l3 3-3 3M13 6l-2 12"/>',
    chart: '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
    mobile: '<rect x="7" y="2" width="10" height="20" rx="2"/><path d="M11 18h2"/>',
    compass: '<circle cx="12" cy="12" r="9"/><path d="m15 9-2 4-4 2 2-4z"/>',
  };

  return `<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
    stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
    aria-hidden="true">${iconos[icono] ?? iconos.code}</svg>`;
}
