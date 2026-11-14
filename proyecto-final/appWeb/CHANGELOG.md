# Changelog — appWeb

Cambios relevantes del front estático: páginas, estilos, JavaScript, accesibilidad y consumo de la API.

Formato basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.1.0/) y versionado semántico.

## [Unreleased]

### Added

- Nada por ahora.

## [0.2.0] - 2026-11-13

### Added

- `assets/css/components.css` con navegación, botones, formularios, alertas, paneles, métricas y estados vacíos.
- Páginas `pages/login.html` y `pages/registro.html` con validación y mensajes accesibles.
- Portal de cliente en `pages/portal/index.html` y dashboard en `pages/admin/dashboard.html`.
- Módulos `api.js` (fetch, CSRF y errores), `auth.js` (sesión y guardas por rol), `ui.js` (header, alertas y carga), `app.js` y `main.js`.
- Lógica de páginas en `assets/js/pages/`.
- Header dinámico con `data-auth` para visitante, sesión y administrador.

### Changed

- `index.html` incorpora navegación, accesos de login/registro y estado `0.2.0`.
- `appWeb/README.md` documenta estructura, rutas, integración con la API y estado actual.

### Security

- Uso de `textContent` para datos de usuario y mensajes de error.
- Guardas de página que redirigen a login o al portal según el rol.
- El token CSRF se solicita antes de las mutaciones y se renueva tras el login.

### Pending

- Sitio público completo, catálogos y CRUD de negocio.
- Capturas y validación visual en navegador (sin Docker en el equipo).

## [0.1.0] - 2026-11-13

### Added

- Estructura inicial de carpetas (`pages`, `assets/css`, `assets/js`, `assets/img`, `assets/fonts`, `docs`).
- Página `index.html` de andamiaje.
- Hoja de estilos base Mobile First con variables de diseño.
- Archivo `assets/js/main.js` como punto de entrada.
- Documentación de arquitectura, páginas y componentes, guía de estilos y accesibilidad.

### Security

- Convención de usar `textContent` para datos del usuario y evitar `innerHTML` sin sanitizar.
- Documentación del manejo de sesión y token CSRF en el front.
