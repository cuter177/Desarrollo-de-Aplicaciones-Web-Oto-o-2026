# Despliegue

Guía para levantar el entorno con Docker y preparar un despliegue real.

## 1. Arquitectura de contenedores

| Servicio | Imagen | Puerto host | Función |
|---|---|---|---|
| `web` | `nginx:1.27-alpine` | `8080` | Sirve `appWeb` y proxy `/api` |
| `api` | PHP 8.3/Apache (build local) | interno | API REST |
| `mssql` | `mcr.microsoft.com/mssql/server:2022-latest` | `1433` | Base de datos |

## 2. Requisitos

- Docker Engine 24+ y Compose v2.
- `x86_64`; SQL Server para Linux no soporta ARM de forma nativa.
- 2 GB de RAM libres.
- Puertos `8080` y `1433` disponibles.
- Cliente de BD: Azure Data Studio, DBeaver o `sqlcmd`.

## 3. Preparación

```bash
cd proyecto-final
cp .env.example .env
```

Editar `.env`:

```dotenv
APP_ENV=development
APP_NAME="[Nombre de la empresa]"
APP_URL=http://localhost:8080
APP_TIMEZONE=America/Mexico_City
WEB_PORT=8080
DB_NAME=DevStudioDB
DB_USER=sa
DB_PASSWORD="UnaClaveSegura#2026"
DB_ENCRYPT=true
DB_TRUST_CERT=true
SESSION_SECURE=false
SESSION_SAMESITE=Lax
ADMIN_NAME="Administrador"
ADMIN_EMAIL=admin@devstudio.local
ADMIN_PASSWORD="Admin#2026"
```

> La contraseña de SQL Server debe tener al menos 8 caracteres, mayúscula, minúscula, número y símbolo.

## 4. Construcción y arranque

```bash
docker compose up -d --build
docker compose ps
docker compose logs -f api
```

La primera construcción tarda varios minutos porque compila `sqlsrv` y `pdo_sqlsrv`.

Verificar:

```bash
curl http://localhost:8080/api/v1/health
curl -I http://localhost:8080/
docker compose exec api php -m | grep -i sqlsrv
```

Salida esperada de extensiones:

```text
pdo_sqlsrv
sqlsrv
```

## 5. Base de datos

### 5.1 Crear el esquema

```bash
docker compose exec -T mssql /bin/bash -c \
  '/opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P "$MSSQL_SA_PASSWORD" -C -i /dev/stdin' \
  < backEnd/database/schema.sql
```

Si la imagen usa la ruta anterior:

```bash
docker compose exec -T mssql /bin/bash -c \
  '/opt/mssql-tools/bin/sqlcmd -S localhost -U sa -P "$MSSQL_SA_PASSWORD" -C -i /dev/stdin' \
  < backEnd/database/schema.sql
```

### 5.2 Cargar datos iniciales

```bash
docker compose exec -T mssql /bin/bash -c \
  '/opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P "$MSSQL_SA_PASSWORD" -C -i /dev/stdin' \
  < backEnd/database/seed.sql
```

### 5.3 Crear el administrador inicial

El `seed.sql` de desarrollo ya crea `admin@devstudio.local` con la contraseña temporal `Admin#2026`. Si prefieres usar tus propias variables `ADMIN_*`, ejecuta:

```bash
docker compose exec api php scripts/crear-admin.php
```

Lee `ADMIN_NAME`, `ADMIN_EMAIL` y `ADMIN_PASSWORD` de `.env`. Es idempotente: si el correo ya existe, no modifica la cuenta. Requiere que `seed.sql` haya creado el rol `admin`.

Verificar el login:

```bash
curl -c cookies.txt http://localhost:8080/api/v1/auth/csrf
# Usar el csrf_token devuelto y la cookie guardada
curl -b cookies.txt -c cookies.txt -X POST http://localhost:8080/api/v1/auth/login \
  -H 'Content-Type: application/json' \
  -H 'X-CSRF-Token: <token>' \
  -d '{"email":"admin@devstudio.local","password":"Admin#2026"}'
```

### 5.4 Conexión desde el host

| Parámetro | Valor |
|---|---|
| Servidor | `localhost,1433` |
| Autenticación | SQL Server |
| Usuario | `sa` |
| Contraseña | la de `.env` |
| Base | `DevStudioDB` |
| Cifrado | Habilitado; confiar en certificado en desarrollo |

Cadena de conexión PDO:

```text
sqlsrv:Server=mssql,1433;Database=DevStudioDB;TrustServerCertificate=1;Encrypt=1
```

## 6. Prueba de conexión desde PHP

```php
$dsn = sprintf(
    'sqlsrv:Server=%s,%s;Database=%s;TrustServerCertificate=1;Encrypt=1',
    getenv('DB_HOST'),
    getenv('DB_PORT'),
    getenv('DB_NAME')
);

$pdo = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASSWORD'), [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

echo $pdo->query('SELECT DB_NAME()')->fetchColumn();
```

## 7. Producción

Checklist mínimo:

- [ ] `APP_ENV=production` y `display_errors=Off`.
- [ ] `SESSION_SECURE=true` y HTTPS con certificado válido.
- [ ] `DB_TRUST_CERT=false` con certificado válido y `DB_ENCRYPT=true`.
- [ ] Usuario de SQL Server distinto de `sa`, con permisos mínimos.
- [ ] `ADMIN_PASSWORD` fuerte y contraseña del administrador cambiada tras el primer acceso.
- [ ] `RATE_LIMIT_DIR` en un volumen persistente o rate limit migrado a Redis/BD.
- [ ] Contraseñas y secretos en un gestor seguro.
- [ ] Respaldos automáticos y restauración probada.
- [ ] Logs centralizados y monitoreo.
- [ ] Cabeceras de seguridad configuradas.
- [ ] Firewall: solo exponer `80/443`; SQL Server sin acceso público.
- [ ] DNS y `APP_URL` con el dominio real.

## 8. Respaldos

```bash
# Crear la carpeta de respaldos y generar el archivo dentro del contenedor
docker compose exec mssql mkdir -p /var/opt/mssql/backup
docker compose exec mssql bash -c "/opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P \"\$MSSQL_SA_PASSWORD\" -C -Q \"BACKUP DATABASE [DevStudioDB] TO DISK = N'/var/opt/mssql/backup/DevStudioDB.bak' WITH INIT\""

# Copiar al host
docker compose cp mssql:/var/opt/mssql/backup/DevStudioDB.bak ./dist/
```

Restauración:

```sql
RESTORE DATABASE [DevStudioDB]
FROM DISK = N'/var/opt/mssql/backup/DevStudioDB.bak'
WITH REPLACE;
```

## 9. Actualizaciones

1. Respaldar la base de datos.
2. Revisar el changelog correspondiente.
3. Aplicar migraciones en orden.
4. Reconstruir contenedores si cambiaron dependencias.
5. Verificar health check y flujos críticos.
6. Registrar el cambio en `backEnd/CHANGELOG.md` o `appWeb/CHANGELOG.md`.

## 10. Solución de problemas

| Problema | Diagnóstico | Solución |
|---|---|---|
| `api` reinicia en bucle | `docker compose logs api` | Corregir `Dockerfile` o dependencias |
| `sqlsrv` no instalado | `php -m` | Reconstruir sin caché: `docker compose build --no-cache api` |
| Error de login `sa` | Contraseña o política | Revisar `.env`; contraseña compleja |
| `502` en `/api` | `api` no disponible | `docker compose ps` y logs |
| SQL Server lento o reinicia | RAM insuficiente | Aumentar memoria de Docker |
| No conecta desde el host | Puerto ocupado o firewall | Verificar `1433` y `DB_PORT` |
| Error de arquitectura | Host ARM | Usar `x86_64` o emulación |
| Cambios no reflejados | Caché de Nginx/navegador | Recargar sin caché; revisar volúmenes |
| `429 DEMASIADAS_PETICIONES` al hacer login | Rate limit de 5 intentos / 15 min | Esperar `Retry-After` o borrar `devstudio-rate-limit` en el contenedor |
| `403 CSRF_INVALIDO` | Falta el token o la sesión cambió | Solicitar `GET /api/v1/auth/csrf` y reenviar `X-CSRF-Token` |
| `401` inmediato tras login | Cookie bloqueada o `SESSION_SECURE=true` sin HTTPS | Revisar `SESSION_SECURE`, dominio y cookies del navegador |
| `scripts/crear-admin.php` falla por rol | `seed.sql` no ejecutado | Cargar `seed.sql` antes del script |
| `health` responde `503 degraded` | La API no conecta a SQL Server | Revisar `DB_*`, que `mssql` esté saludable y las extensiones `sqlsrv` |

## 11. Comandos de referencia

```bash
docker compose up -d --build      # Construir y levantar
docker compose down               # Detener
docker compose down -v            # Detener y borrar datos
docker compose logs -f api        # Logs de la API
docker compose exec api bash      # Shell en PHP
docker compose exec mssql bash    # Shell en SQL Server
docker compose config             # Validar configuración
```
