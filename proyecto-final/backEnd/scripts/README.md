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

> El `seed.sql` de desarrollo ya crea `admin@devstudio.local` / `Admin#2026` y tres clientes de prueba. El script resulta útil cuando quieres crear el administrador con tus propias variables `ADMIN_*` en lugar de usar la cuenta demo.

> Cambiar la contraseña de ejemplo antes de cualquier despliegue real.
