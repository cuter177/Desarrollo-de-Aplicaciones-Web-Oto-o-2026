# Changelog — appWeb

Cambios relevantes del front estático: páginas, estilos, JavaScript, accesibilidad y consumo de la API.

Formato basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.1.0/) y versionado semántico.

## [Unreleased]

### Added

- Portal del cliente con resumen, proyectos, detalle, solicitudes, alta y perfil con cambio de contraseña.
- Páginas administrativas de usuarios, clientes, servicios y tecnologías.
- Enlaces de esos módulos en la navegación y el dashboard del panel.

### Changed

- El portal deja de ser un placeholder y solo admite el rol `cliente`.

## [0.4.0] - 2026-11-15

### Added

- Páginas públicas `pages/servicios.html`, `pages/servicio-detalle.html`, `pages/portafolio.html`, `pages/caso-detalle.html`, `pages/nosotros.html` y `pages/contacto.html`.
- Bandeja administrativa `pages/admin/mensajes.html` con filtros, detalle y cambio de estado.
- `assets/js/public.js` con configuración de la empresa, formateo de fechas, badges, tarjetas de caso, iconos SVG y estados vacíos.
- Módulos de página `servicios.js`, `servicio-detalle.js`, `portafolio.js`, `caso-detalle.js`, `nosotros.js`, `contacto.js` y `admin-mensajes.js`.
- Página `404.html` y configuración de Nginx con `error_page 404`.
- Estilos de servicios, portafolio, caso, nosotros, contacto, filtros, CTA y footer.
- Campos de caso de éxito y `mostrar_cliente` en el formulario de proyectos.

### Changed

- Navegación pública completa en `index.html`, cotización, login, registro, portal y panel administrativo.
- `main.js` aplica `aplicarConfig()` para los datos de la empresa.
- `index.html` actualiza el estado a `0.4.0` y enlaza servicios y portafolio.
- `admin-proyectos.js` envía y carga `reto`, `solucion`, `resultados` y `mostrar_cliente`.
- Validación visual con Chromium headless en 375, 768 y 1280 px, sin errores de consola.

### Fixed

- La fila “Redes” de Contacto se oculta cuando no hay redes configuradas.

## [0.3.0] - 2026-11-13

### Added

- Página `pages/cotizacion.html` con presentación, planes y wizard de diagnóstico en cuatro pasos.
- Lógica `assets/js/pages/cotizacion.js` para cargar planes/servicios, validar por pasos y mostrar el plan recomendado.
- Página `pages/admin/solicitudes.html` con bandeja, filtros, detalle y acciones de gestión.
- Página `pages/admin/proyectos.html` con formulario de alta/edición y listado de proyectos.
- Módulos `admin-solicitudes.js` y `admin-proyectos.js`.
- Dashboard administrativo ampliado con pipeline, conversión, crecimiento promedio y solicitudes recientes.
- Estilos para planes, wizard, bandeja, tablas, estados y detalle.
- Navegación al diagnóstico desde `index.html` y copy comercial de la página principal.

### Changed

- `index.html` actualiza la propuesta de valor y el estado a `0.3.0`.
- `admin.js` formatea métricas y renderiza solicitudes recientes.
- `components.css` incorpora los componentes de la etapa 3.

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
