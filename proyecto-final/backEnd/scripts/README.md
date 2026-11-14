# Scripts del backend

## Crear administrador

Genera el usuario administrador con `password_hash` a partir de las variables de entorno.

```bash
# Desde la raíz de proyecto-final/
docker compose exec api php scripts/crear-admin.php
```

Variables leídas de `.env` (o del entorno del contenedor):

| Variable | Ejemplo |
|---|---|
| `ADMIN_NAME` | `Administrador` |
| `ADMIN_EMAIL` | `admin@devstudio.local` |
| `ADMIN_PASSWORD` | `Admin#2026` |

El script es idempotente: si el correo ya existe, no lo modifica. Requiere haber ejecutado `seed.sql` para que exista el rol `admin`.

> Cambiar la contraseña de ejemplo antes de cualquier despliegue real.
