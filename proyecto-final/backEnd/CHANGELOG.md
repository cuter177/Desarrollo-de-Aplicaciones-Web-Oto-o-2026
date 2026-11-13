# Changelog — backEnd

Todos los cambios relevantes de la API, la base de datos y la seguridad se documentan aquí.

El formato sigue [Keep a Changelog](https://keepachangelog.com/es-ES/1.1.0/) y el versionado semántico.

## [Unreleased]

### Added

- Nada por ahora.

## [0.1.0] - 2026-11-13

### Added

- Estructura inicial de carpetas (`app`, `config`, `database`, `public`, `tests`, `docs`).
- Punto de entrada `public/index.php` con respuesta informativa y health check sin base de datos.
- Esqueleto de `database/schema.sql`, `database/seed.sql` y carpeta de migraciones.
- Documentación de arquitectura, modelo ER, diccionario de datos, contrato de API, seguridad y despliegue.
- Dockerfile de PHP 8.3 con `pdo_sqlsrv`, `sqlsrv` y `msodbcsql18`.

### Security

- Variables de entorno para credenciales; `.env` excluido de Git.
- Convenciones de contraseñas con `password_hash`, sesiones `HttpOnly` y CSRF documentadas.
