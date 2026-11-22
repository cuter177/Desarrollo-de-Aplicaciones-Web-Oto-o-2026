# appWeb — Front estático

Interfaz del proyecto: sitio público, portal de cliente y panel administrativo. Se sirve con Nginx y consume la API PHP a través de `/api`.

## Estructura

```text
appWeb/
├── index.html                 # Inicio, propuesta de valor y acceso al diagnóstico
├── 404.html                   # Página no encontrada
├── pages/
│   ├── servicios.html         # Catálogo de servicios
│   ├── servicio-detalle.html  # Detalle de servicio por slug
│   ├── portafolio.html        # Casos publicados con filtros
│   ├── caso-detalle.html      # Detalle de caso por slug
│   ├── nosotros.html          # Proceso, equipo y propuesta
│   ├── contacto.html          # Formulario y datos de contacto
│   ├── cotizacion.html        # Planes y wizard de diagnóstico
│   ├── login.html             # Inicio de sesión
│   ├── registro.html          # Registro de cliente
│   ├── portal/index.html      # Portal del cliente (protegido)
│   └── admin/
│       ├── dashboard.html     # Métricas y solicitudes recientes
│       ├── solicitudes.html   # Bandeja y detalle de solicitudes
│       ├── proyectos.html     # Alta y edición de proyectos
│       └── mensajes.html      # Bandeja de mensajes de contacto
├── assets/
│   ├── css/                   # main.css (base) y components.css (UI)
│   ├── js/                    # api.js, auth.js, ui.js, app.js, public.js y pages/
│   ├── img/                   # Imágenes y logotipos
│   └── fonts/                 # Tipografías locales
├── docs/                      # Arquitectura, páginas, estilos y accesibilidad
├── CHANGELOG.md
└── README.md
```

## Estado actual

`0.4.0` — Sitio público completo y bandeja de mensajes:

- Inicio, servicios y detalle, portafolio con filtros y detalle de caso, nosotros y contacto.
- Página pública de planes con los planes Esencial, Crecimiento y Transformación.
- Wizard de diagnóstico en cuatro pasos con validación por paso y resultado recomendado.
- Formulario de contacto con validación, honeypot, aviso de privacidad y mensajes accesibles.
- Dashboard administrativo con pipeline, conversión, crecimiento promedio y leads recientes.
- Bandeja de solicitudes con filtros, detalle, notas, cambio de plan/cliente/estado y conversión a proyecto.
- Gestión de proyectos con alta, edición, estado, fechas, publicación y contenido de caso.
- Bandeja de mensajes con filtros, detalle y cambio de estado.
- Login, registro, cierre de sesión y header dinámico por rol.
- Portal de cliente y panel administrativo protegidos en el front y en el backend.
- Manejo de CSRF, errores de validación y estados de carga.

El detalle del portal y los CRUD restantes siguen pendientes.

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
- El wizard público consume `GET /planes`, `GET /servicios` y `POST /diagnostico`.
- El sitio público consume `GET /config`, `GET /servicios`, `GET /servicios/{slug}`, `GET /tecnologias`, `GET /portafolio`, `GET /portafolio/{slug}` y `POST /contacto`.
- `assets/js/public.js` centraliza la configuración de la empresa, el formateo de fechas, los badges y las tarjetas de caso.
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
| Servicios | `/pages/servicios.html` | Público |
| Detalle de servicio | `/pages/servicio-detalle.html?slug=...` | Público |
| Portafolio | `/pages/portafolio.html` | Público |
| Detalle de caso | `/pages/caso-detalle.html?slug=...` | Público |
| Nosotros | `/pages/nosotros.html` | Público |
| Contacto | `/pages/contacto.html` | Público |
| Diagnóstico y planes | `/pages/cotizacion.html` | Público |
| Login | `/pages/login.html` | Público; redirige si hay sesión |
| Registro | `/pages/registro.html` | Público; redirige si hay sesión |
| Portal | `/pages/portal/index.html` | `cliente` y `admin` |
| Dashboard | `/pages/admin/dashboard.html` | Solo `admin` |
| Solicitudes | `/pages/admin/solicitudes.html` | Solo `admin` |
| Proyectos | `/pages/admin/proyectos.html` | Solo `admin` |
| Mensajes | `/pages/admin/mensajes.html` | Solo `admin` |
| No encontrado | `/404.html` | Público |

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

- Cliente pendientes: resumen completo, proyectos, detalle de proyecto, solicitudes, detalle de solicitud y perfil.
- Admin pendientes: usuarios, clientes, servicios y tecnologías.

El detalle está en [`docs/02-paginas-y-componentes.md`](docs/02-paginas-y-componentes.md).
