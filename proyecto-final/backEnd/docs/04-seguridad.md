# Seguridad

Lineamientos obligatorios para la implementación del backend y del frontend.

## 1. Modelo de amenazas

| Amenaza | Impacto | Mitigación |
|---|---|---|
| Inyección SQL | Alto | PDO con consultas preparadas |
| Robo de sesión | Alto | Cookie `HttpOnly`, `Secure` en producción, regeneración de ID |
| CSRF | Alto | Token `X-CSRF-Token` y `SameSite` |
| XSS | Alto | Escape de salida y validación de entrada |
| Escalada de privilegios | Alto | Verificación de rol en cada endpoint |
| Fuerza bruta | Medio | Rate limit y bloqueo temporal |
| Exposición de secretos | Alto | `.env` fuera de Git y gestor de secretos en producción |
| Enumeración de usuarios | Medio | Mensajes genéricos en login y recuperación |
| Carga de archivos maliciosos | Medio | Validar MIME, tamaño y nombre; almacenar fuera del web root |
| Fuga de datos entre clientes | Alto | Filtrar por `cliente_id` en el `WHERE` |

## 2. Contraseñas

- Generar con `password_hash($password, PASSWORD_DEFAULT)` o `PASSWORD_ARGON2ID` si está disponible.
- Verificar con `password_verify`.
- Política mínima: 8 caracteres, una mayúscula, una minúscula y un número.
- Nunca registrar contraseñas ni hashes en logs.
- No devolver `password_hash` en ninguna respuesta.
- El administrador inicial se crea con `scripts/crear-admin.php` usando `ADMIN_PASSWORD`. El `seed.sql` de desarrollo incluye cuentas demo con hashes precalculados; no usar esas credenciales en producción.
- Cambiar `ADMIN_PASSWORD` y la contraseña del administrador antes de desplegar.

## 3. Sesiones

```php
session_name(getenv('SESSION_NAME') ?: 'devstudio_session');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => filter_var(getenv('SESSION_SECURE'), FILTER_VALIDATE_BOOL),
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();
```

- Regenerar el ID después de iniciar sesión: `session_regenerate_id(true)`.
- Destruir la sesión y la cookie al cerrar sesión.
- Expirar por inactividad (por ejemplo, 2 horas).
- Guardar en la sesión solo datos públicos del usuario (`id`, `nombre`, `email`, `rol`, `cliente_id`) y el token CSRF; nunca el hash.

Implementación actual (`config/bootstrap.php` y `app/middleware/Auth.php`):

- `session.use_strict_mode`, `use_only_cookies`, `cookie_httponly` y `SameSite` configurable (`SESSION_SAMESITE`).
- `SESSION_SECURE` se activa en producción con HTTPS.
- `GET /auth/me` revalida contra la base de datos que el usuario siga activo; si no, destruye la sesión.

## 4. CSRF

- Generar un token aleatorio por sesión: `bin2hex(random_bytes(32))`.
- Enviarlo en `GET /auth/me` y en `GET /auth/csrf`.
- Exigir `X-CSRF-Token` en `POST`, `PATCH` y `DELETE`.
- Comparar con `hash_equals`.
- Renovar el token después del login.

## 5. Inyección SQL

Incorrecto:

```php
$sql = "SELECT * FROM Usuarios WHERE email = '$email'";
```

Correcto:

```php
$sql = 'SELECT id, nombre, email, password_hash, rol_id
        FROM Usuarios
        WHERE email = :email AND activo = 1';
$stmt = $pdo->prepare($sql);
$stmt->execute([':email' => $email]);
```

- Usar siempre parámetros nombrados o posicionales.
- No concatenar identificadores ni valores.
- Validar y convertir los `id` a entero antes de usarlos.
- Limitar los permisos del usuario de base de datos en producción.

## 6. XSS

- Escapar toda salida en HTML con `htmlspecialchars($valor, ENT_QUOTES, 'UTF-8')`.
- En el front, usar `textContent` en lugar de `innerHTML` para datos del usuario.
- No insertar HTML sin sanitizar.
- Configurar `Content-Security-Policy` en producción.

## 7. Autorización

- Verificar sesión en `/mi/*` y `/admin/*`.
- Verificar rol `admin` en `/admin/*`.
- Verificar propiedad del recurso en `/mi/*` mediante `cliente_id`.
- No confiar en el front: aunque el menú oculte una opción, el endpoint debe rechazar la petición.
- Responder `401` sin sesión y `403` sin permisos.

## 8. Validación de entrada

- Validar tipo, longitud, formato y rango.
- Normalizar correos con `filter_var($email, FILTER_VALIDATE_EMAIL)`.
- Validar `slug` con expresión regular.
- Rechazar campos desconocidos o payloads demasiado grandes.
- Devolver `422` con errores por campo.

## 9. Rate limiting

Estado:

- **Login (implementado):** 5 intentos por IP y por correo cada 15 minutos. Devuelve `429` con `Retry-After` y `details.retry_after`; el contador del correo se limpia tras un login exitoso.
- **Registro (pendiente):** 3 por IP cada hora.
- **Contacto (implementado):** 5 por IP cada hora, con honeypot `website`.
- **Diagnóstico (implementado):** 10 por IP cada hora.
- **Cotizaciones (pendiente):** endpoint sustituido por el diagnóstico.

El login usa `app/helpers/RateLimiter.php` con archivos JSON en `sys_get_temp_dir()`, endurecido con `LOCK_EX` y nombres hasheados. Para varios nodos, reemplazar por Redis o una tabla de la base de datos.

## 9.1 Exposición de datos públicos

- `GET /portafolio` y `GET /portafolio/{slug}` usan una proyección que omite `cliente_id`, correos y teléfonos.
- El nombre del cliente solo se incluye con `mostrar_cliente = 1`.
- Los proyectos con `publicado = 0` responden `404` aunque se conozca el `slug`.
- `GET /config` publica únicamente los datos comerciales definidos en `COMPANY_*`.
- `POST /contacto` no devuelve datos de otros mensajes ni permite listar la bandeja.

## 10. Cabeceras HTTP

Configurar en Apache o Nginx:

```text
X-Content-Type-Options: nosniff
X-Frame-Options: DENY
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: geolocation=(), microphone=(), camera=()
Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self'; script-src 'self'
Strict-Transport-Security: max-age=31536000; includeSubDomains
```

`Strict-Transport-Security` solo en HTTPS.

## 11. Secretos y configuración

- `.env` no se versiona.
- `.env.example` solo contiene valores de ejemplo.
- En producción, usar variables del proveedor o un gestor de secretos.
- No usar `sa` en producción; crear un usuario con permisos mínimos sobre `DevStudioDB`.
- Rotar contraseñas si se filtran.

## 12. Base de datos en producción

```sql
CREATE LOGIN app_devstudio WITH PASSWORD = N'[contraseña fuerte]';
USE [DevStudioDB];
CREATE USER app_devstudio FOR LOGIN app_devstudio;
ALTER ROLE db_datareader ADD MEMBER app_devstudio;
ALTER ROLE db_datawriter ADD MEMBER app_devstudio;
GRANT EXECUTE TO app_devstudio;
```

Evitar `db_owner` y `sysadmin` para la aplicación.

## 13. Registro y monitoreo

- Registrar errores con fecha, ruta, usuario y código, sin datos sensibles.
- No registrar contraseñas, tokens ni cuerpos completos con datos personales.
- Monitorear intentos fallidos de login y picos de errores.
- Revisar logs antes de cada entrega.

## 14. Respaldos

- Programar respaldos antes de cada cambio importante.
- Guardar copias fuera del contenedor.
- Probar la restauración.
- No incluir respaldos en el repositorio ni en el ZIP de entrega.

## 15. Checklist de seguridad

- [x] Contraseñas con hash y política mínima.
- [x] Consultas preparadas en los modelos implementados.
- [x] Sesión `HttpOnly`, `SameSite` y `Secure` configurable.
- [x] CSRF en mutaciones.
- [x] Roles y propiedad validados en el servidor para las rutas implementadas.
- [x] Entradas validadas y salidas escapadas con `textContent`.
- [x] Rate limit en login, contacto y diagnóstico.
- [ ] Rate limit en registro.
- [x] Proyección pública sin datos privados de clientes.
- [x] Honeypot y aviso de privacidad en contacto.
- [x] Cabeceras de seguridad base en la API.
- [x] `.env` fuera de Git.
- [ ] Usuario de BD con permisos mínimos.
- [ ] Respaldos y restauración probados.
- [ ] Errores de producción sin trazas (verificar con `APP_ENV=production`).
