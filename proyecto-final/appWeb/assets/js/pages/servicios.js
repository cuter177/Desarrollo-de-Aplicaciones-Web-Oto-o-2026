import { apiGet } from '../api.js';
import { iniciarPagina } from '../app.js';
import { aplicarConfig, iconoServicio } from '../public.js';

const lista = document.querySelector('#servicios-lista');

function crearTarjeta(servicio) {
  const articulo = document.createElement('article');
  articulo.className = 'service-card';

  const icono = document.createElement('div');
  icono.className = 'service-card__icon';
  icono.innerHTML = iconoServicio(servicio.icono);

  const titulo = document.createElement('h3');
  titulo.className = 'service-card__title';
  titulo.textContent = servicio.nombre;

  const descripcion = document.createElement('p');
  descripcion.className = 'service-card__body';
  descripcion.textContent = servicio.descripcion;

  const enlace = document.createElement('a');
  enlace.className = 'service-card__link';
  enlace.href = `/pages/servicio-detalle.html?slug=${encodeURIComponent(servicio.slug)}`;
  enlace.textContent = 'Conocer el servicio →';

  articulo.append(icono, titulo, descripcion, enlace);

  return articulo;
}

function mostrarError(mensaje) {
  lista.replaceChildren();

  const caja = document.createElement('div');
  caja.className = 'empty-state';

  const texto = document.createElement('p');
  texto.textContent = mensaje;

  const boton = document.createElement('button');
  boton.className = 'btn btn--secondary';
  boton.type = 'button';
  boton.textContent = 'Reintentar';
  boton.addEventListener('click', cargar);

  caja.append(texto, boton);
  lista.append(caja);
}

async function cargar() {
  lista.innerHTML = '<div class="empty-state">Cargando servicios…</div>';

  try {
    const servicios = await apiGet('/servicios');

    if (!Array.isArray(servicios) || servicios.length === 0) {
      lista.innerHTML = '<div class="empty-state">Todavía no hay servicios publicados.</div>';
      return;
    }

    lista.replaceChildren(...servicios.map(crearTarjeta));
  } catch {
    mostrarError('No se pudieron cargar los servicios. Revisa tu conexión e inténtalo de nuevo.');
  }
}

async function init() {
  await iniciarPagina();
  aplicarConfig();
  cargar();
}

init();
