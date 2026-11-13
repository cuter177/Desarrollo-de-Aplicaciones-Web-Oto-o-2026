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
- El seed usa una contraseña temporal que debe cambiarse antes de desplegar.

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
- Guardar en la sesión solo `usuario_id`, `rol` y `csrf_token`; no datos sensibles.

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

Aplicar en:

- Login: 5 intentos por IP cada 15 minutos.
- Registro: 3 por IP cada hora.
- Contacto y cotizaciones: 3 por IP cada hora.

Registrar el intento y devolver `429` con `Retry-After`.

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

- [ ] Contraseñas con hash y política mínima.
- [ ] Consultas preparadas en todos los modelos.
- [ ] Sesión `HttpOnly`, `SameSite` y `Secure` en producción.
- [ ] CSRF en mutaciones.
- [ ] Roles y propiedad validados en el servidor.
- [ ] Entradas validadas y salidas escapadas.
- [ ] Rate limit en login y formularios públicos.
- [ ] Cabeceras de seguridad configuradas.
- [ ] `.env` fuera de Git y del ZIP.
- [ ] Usuario de BD con permisos mínimos.
- [ ] Respaldos y restauración probados.
- [ ] Errores de producción sin trazas.
