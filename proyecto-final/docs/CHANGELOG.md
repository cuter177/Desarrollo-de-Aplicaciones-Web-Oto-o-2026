# Changelog — documentación

Registro de cambios de la documentación académica y transversal del proyecto: requisitos, diagramas, manuales, contrato y PDFs.

Formato basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.1.0/) y versionado semántico.

## [Unreleased]

### Added

- Nada por ahora.

## [0.2.0] - 2026-11-13

### Added

- Estado de la etapa 2 en el README raíz: carga de `schema.sql`/`seed.sql` y alta del administrador.
- Rutas reales de registro, login, portal y dashboard en el manual de usuario.
- Sección de instalación de la base de datos, variables nuevas y problemas de CSRF/rate limit en el manual técnico.
- Checklist actualizado con el avance de autenticación, scripts SQL y documentación.
- Estado de la etapa 2 en la arquitectura del backend y en la estructura de archivos.

### Changed

- Manuales de usuario y técnico actualizados a `0.2.0`.
- Checklist de entregables refleja el estado real de la etapa 2.
- Documentación del frontend (arquitectura, páginas, estilos y accesibilidad) alineada con los módulos y clases implementados.
- `scripts/README.md` y `backEnd/tests/README.md` reflejan el estado real.

## [0.1.0] - 2026-11-13

### Added

- Checklist de entregables de la materia.
- Documentos de modelación: requisitos, mapa de sitio, guiones, diagrama de navegación y estructura de archivos.
- Índices de manual de usuario y manual técnico.
- Plantilla de contrato de prestación de servicios con alcances y limitaciones.
- Nota de generación de PDFs con LaTeX.
- Enlaces cruzados entre la documentación general, `backEnd/docs` y `appWeb/docs`.

### Changed

- Se adoptó la división del proyecto en `backEnd` y `appWeb` con documentación y changelog independientes.
