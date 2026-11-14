# backEnd — API PHP + SQL Server

API REST del proyecto. Sirve al front estático `appWeb` mediante el proxy `/api` de Nginx.

## Estructura

```text
backEnd/
├── app/
│   ├── controllers/   # Reciben la petición y devuelven JSON
│   ├── models/        # Acceso a datos con PDO (sqlsrv)
│   ├── middleware/    # Sesión, roles, CSRF y validación
│   └── helpers/       # Utilidades comunes (respuestas, validación)
├── config/            # Configuración y conexión a la base de datos
├── database/
│   ├── schema.sql     # Esquema de SQL Server
│   ├── seed.sql       # Datos iniciales
│   └── migrations/    # Cambios incrementales del esquema
├── docs/              # Arquitectura, BD, API, seguridad y despliegue
├── public/            # Document root: .htaccess e index.php (API)
├── scripts/           # Utilidades CLI (alta de administrador)
├── tests/             # Pruebas del backend
├── CHANGELOG.md
└── README.md
```

## Estado actual

`0.2.0` — Base de datos y autenticación implementadas:

- Esquema completo e idempotente en `database/schema.sql` y catálogos en `database/seed.sql`.
- Router en `public/index.php` con rutas `/api/v1/*`.
- Registro de clientes, login, logout, sesión por cookie, `GET /auth/me` y cambio de contraseña.
- Roles `admin`/`cliente` verificados en el servidor; dashboard administrativo protegido.
- CSRF con `X-CSRF-Token`, rate limit de login y health check real contra SQL Server.
- Script `scripts/crear-admin.php` para el administrador inicial.

Los endpoints públicos de catálogos, el portal `/mi/*` y los CRUD administrativos siguen pendientes.

## Documentación

| Documento | Ruta |
|---|---|
| Arquitectura | [`docs/01-arquitectura.md`](docs/01-arquitectura.md) |
| Modelo ER | [`docs/02-base-datos/01-modelo-er.md`](docs/02-base-datos/01-modelo-er.md) |
| Diccionario de datos | [`docs/02-base-datos/02-diccionario-datos.md`](docs/02-base-datos/02-diccionario-datos.md) |
| Contrato de API | [`docs/03-api.md`](docs/03-api.md) |
| Seguridad | [`docs/04-seguridad.md`](docs/04-seguridad.md) |
| Despliegue | [`docs/05-despliegue.md`](docs/05-despliegue.md) |
| Changelog | [`CHANGELOG.md`](CHANGELOG.md) |

## Convenciones

- PHP 8.3 con `declare(strict_types=1)` en todos los archivos.
- PDO con el DSN `sqlsrv:` y **consultas preparadas**.
- Respuestas JSON con el formato `{ "data": ... }` o `{ "error": ... }`.
- Roles `admin` y `cliente`; el backend siempre valida permisos.
- Contraseñas con `password_hash` (Argon2id o bcrypt) y `password_verify`.
- Variables de entorno; nunca credenciales en el código.

## Comandos útiles

```bash
# Levantar el entorno completo desde la raíz de proyecto-final/
docker compose up -d --build

# Ver logs de la API
docker compose logs -f api

# Entrar al contenedor de PHP
docker compose exec api bash

# Verificar extensiones de SQL Server
docker compose exec api php -m | grep -i sqlsrv

# Crear el administrador inicial (lee ADMIN_* de .env)
docker compose exec api php scripts/crear-admin.php

# Health check con estado de la base de datos
curl -i http://localhost:8080/api/v1/health
```

## Rutas implementadas

| Método | Ruta | Acceso |
|---|---|---|
| GET | `/api/v1` | Público |
| GET | `/api/v1/health` | Público |
| GET | `/api/v1/auth/csrf` | Público |
| POST | `/api/v1/auth/login` | Público |
| POST | `/api/v1/auth/logout` | Autenticado |
| GET | `/api/v1/auth/me` | Autenticado |
| POST | `/api/v1/auth/registro` | Público |
| PATCH | `/api/v1/auth/password` | Autenticado |
| GET | `/api/v1/admin/dashboard` | Solo `admin` |
