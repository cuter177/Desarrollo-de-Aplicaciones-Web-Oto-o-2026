# appWeb — Front estático

Interfaz del proyecto: sitio público, portal de cliente y panel administrativo. Se sirve con Nginx y consume la API PHP a través de `/api`.

## Estructura

```text
appWeb/
├── index.html                 # Inicio con navegación dinámica
├── pages/
│   ├── login.html             # Inicio de sesión
│   ├── registro.html          # Registro de cliente
│   ├── portal/index.html      # Portal del cliente (protegido)
│   └── admin/dashboard.html   # Dashboard administrativo (protegido)
├── assets/
│   ├── css/                   # main.css (base) y components.css (UI)
│   ├── js/                    # api.js, auth.js, ui.js, app.js y pages/
│   ├── img/                   # Imágenes y logotipos
│   └── fonts/                 # Tipografías locales
├── docs/                      # Arquitectura, páginas, estilos y accesibilidad
├── CHANGELOG.md
└── README.md
```

## Estado actual

`0.2.0` — Autenticación conectada a la API:

- Login, registro de cliente y cierre de sesión.
- Restauración de sesión con `GET /auth/me` y header dinámico por rol.
- Portal de cliente y dashboard administrativo protegidos en el front y en el backend.
- Manejo de CSRF, errores de validación y estados de carga.

El sitio público completo y los módulos de negocio siguen pendientes.

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
- Antes de una mutación se obtiene el token con `GET /auth/csrf`; después del login se usa el `csrf_token` devuelto.
- Las mutaciones envían `X-CSRF-Token` y el backend responde `403 CSRF_INVALIDO` si falta o no coincide.
- Al recargar, `GET /auth/me` restaura la sesión y el token; si responde `401`, el header vuelve al estado de visitante.
- Los errores de la API se muestran con `textContent` en el bloque `role="alert"` de cada formulario.
- El contrato completo está en [`../backEnd/docs/03-api.md`](../backEnd/docs/03-api.md).

## Rutas del front

| Página | Ruta | Acceso |
|---|---|---|
| Inicio | `/index.html` | Público |
| Login | `/pages/login.html` | Público; redirige si hay sesión |
| Registro | `/pages/registro.html` | Público; redirige si hay sesión |
| Portal | `/pages/portal/index.html` | `cliente` y `admin` |
| Dashboard | `/pages/admin/dashboard.html` | Solo `admin` |

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
