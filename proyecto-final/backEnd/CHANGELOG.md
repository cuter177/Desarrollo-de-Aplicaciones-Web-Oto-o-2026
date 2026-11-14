# Changelog — backEnd

Todos los cambios relevantes de la API, la base de datos y la seguridad se documentan aquí.

El formato sigue [Keep a Changelog](https://keepachangelog.com/es-ES/1.1.0/) y el versionado semántico.

## [Unreleased]

### Added

- Seed de desarrollo con datos de demostración: cuentas `admin`/`cliente`, 3 clientes, 6 proyectos con tecnologías, 5 solicitudes de cotización y 4 mensajes de contacto. Sigue siendo idempotente.
- Contraseñas demo documentadas para probar login, portal y dashboard (`admin@devstudio.local` / `Admin#2026`, `cliente@devstudio.local` / `Cliente#2026`).
- Migración `001_planes_diagnostico.sql` con la tabla `Planes` y los campos del diagnóstico.
- Catálogo de planes, servicios públicos y `POST /diagnostico` con recomendación de plan en el servidor.
- Bandeja administrativa de solicitudes: filtros, detalle, notas, cambio de plan/cliente/estado y conversión a proyecto.
- CRUD básico de proyectos con publicación y destacado.
- Router con parámetros dinámicos para recursos `/admin/*/{id}`.
- Datos demo de planes y diagnósticos en `seed.sql`.

## [0.2.0] - 2026-11-13

### Added

- Esquema completo e idempotente de `DevStudioDB` con 9 tablas, llaves, restricciones e índices.
- Datos iniciales de roles, 4 servicios y 9 tecnologías.
- Bootstrap con autoload PSR-4 manual, carga de `.env`, sesión segura y cabeceras HTTP.
- Helpers `Env`, `Request`, `Response`, `Csrf`, `Validator` y `RateLimiter`.
- Modelos `Rol`, `Usuario` y `Cliente` con PDO y consultas preparadas.
- `AuthController` con CSRF, login, logout, `me`, registro transaccional y cambio de contraseña.
- `HealthController` con verificación real de SQL Server y `AdminController` con métricas del dashboard.
- Router en `public/index.php` con `404`, `405` y manejo global de errores.
- Script `scripts/crear-admin.php` para crear el administrador inicial desde `ADMIN_*`.
- Variables `APP_TIMEZONE`, `DB_ENCRYPT`, `DB_TRUST_CERT` y `ADMIN_*` en `.env.example` y `compose.yaml`.

### Changed

- Contrato de API actualizado a `0.2.0` con ejemplos de sesión, dashboard, errores y estado de implementación.
- Documentación de seguridad y despliegue alineada con el código (sesiones, CSRF, rate limit y alta de administrador).
- `backEnd/README.md` documenta el estado, los comandos y las rutas implementadas.

### Security

- Contraseñas con `password_hash`/`password_verify` y rehash automático.
- Sesión con `use_strict_mode`, `HttpOnly`, `SameSite` y `Secure` configurable; ID regenerado al login.
- Token CSRF rotado en cada login y validado en mutaciones.
- Rate limit de login de 5 intentos por IP y por correo cada 15 minutos.
- Validación de entrada y respuestas de error sin filtrar si el correo existe.
- Registro transaccional que crea usuario y cliente o revierte todo.

### Pending

- Ejecución real en Docker/SQL Server: el equipo no tiene Docker ni PHP instalados.
- Rate limit de registro y formularios públicos.
- Endpoints públicos, portal `/mi/*` y CRUD administrativos.

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
