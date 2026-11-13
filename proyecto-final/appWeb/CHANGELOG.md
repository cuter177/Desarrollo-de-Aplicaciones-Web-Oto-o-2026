# Changelog — appWeb

Cambios relevantes del front estático: páginas, estilos, JavaScript, accesibilidad y consumo de la API.

Formato basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.1.0/) y versionado semántico.

## [Unreleased]

### Added

- Nada por ahora.

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
