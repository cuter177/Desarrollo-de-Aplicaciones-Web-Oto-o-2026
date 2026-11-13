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
├── tests/             # Pruebas del backend
├── CHANGELOG.md
└── README.md
```

## Estado actual

`0.1.0` — Andamiaje. `public/index.php` solo responde información básica y un health check sin base de datos. No hay autenticación ni CRUD implementados.

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
```
