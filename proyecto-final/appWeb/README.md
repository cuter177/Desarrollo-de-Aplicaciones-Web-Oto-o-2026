# appWeb — Front estático

Interfaz del proyecto: sitio público, portal de cliente y panel administrativo. Se sirve con Nginx y consume la API PHP a través de `/api`.

## Estructura

```text
appWeb/
├── index.html                 # Página de inicio (placeholder)
├── pages/                     # Páginas internas del sitio
├── assets/
│   ├── css/                   # Estilos Mobile First
│   ├── js/                    # Módulos de JavaScript
│   ├── img/                   # Imágenes y logotipos
│   └── fonts/                 # Tipografías locales
├── docs/                      # Arquitectura, páginas, estilos y accesibilidad
├── CHANGELOG.md
└── README.md
```

## Estado actual

`0.1.0` — Andamiaje. Solo existe la página de inicio con estilos base y un archivo JS vacío. No hay navegación, formularios ni llamadas a la API implementadas.

## Desarrollo

El front se sirve desde el contenedor `web`:

```bash
# Desde la raíz de proyecto-final/
docker compose up -d web
```

Abrir <http://localhost:8080>.

Como los archivos se montan como volumen de solo lectura, los cambios en HTML, CSS y JS se reflejan al recargar el navegador.

## Integración con la API

- Base: `/api/v1` (mismo origen).
- Las peticiones incluyen cookies: `fetch(url, { credentials: 'same-origin' })`.
- Las mutaciones envían `X-CSRF-Token`.
- El token se obtiene de `GET /api/v1/auth/me` después del login.
- El contrato completo está en [`../backEnd/docs/03-api.md`](../backEnd/docs/03-api.md).

## Convenciones

- **Mobile First:** estilos base para móvil y `min-width` para tablet/escritorio.
- **Sin framework:** HTML, CSS y JavaScript nativos.
- **Módulos ES:** un archivo por responsabilidad (`api.js`, `auth.js`, `ui.js`, etc.).
- **Seguridad:** usar `textContent` para datos del usuario; nunca `innerHTML` sin sanitizar.
- **Accesibilidad:** HTML semántico, etiquetas asociadas, foco visible y navegación por teclado.
- **Nombres:** clases con BEM (`card__title`, `form--compact`).

## Documentación

| Documento | Ruta |
|---|---|
| Arquitectura del front | [`docs/01-arquitectura-front.md`](docs/01-arquitectura-front.md) |
| Páginas y componentes | [`docs/02-paginas-y-componentes.md`](docs/02-paginas-y-componentes.md) |
| Guía de estilos | [`docs/03-guia-estilos.md`](docs/03-guia-estilos.md) |
| Accesibilidad | [`docs/04-accesibilidad.md`](docs/04-accesibilidad.md) |
| Changelog | [`CHANGELOG.md`](CHANGELOG.md) |

## Páginas previstas

- Públicas: inicio, servicios, detalle de servicio, portafolio, detalle de caso, nosotros, contacto, cotización, login y registro.
- Cliente: resumen, proyectos, detalle de proyecto, solicitudes, detalle de solicitud y perfil.
- Admin: dashboard, usuarios, clientes, servicios, proyectos, tecnologías, solicitudes y mensajes.

El detalle está en [`docs/02-paginas-y-componentes.md`](docs/02-paginas-y-componentes.md).
