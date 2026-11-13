# Contrato de API

**Base URL:** `http://localhost:8080/api/v1`
**Formato:** JSON UTF-8
**Autenticación:** cookie de sesión + token CSRF
**Versión:** 0.1.0 (contrato; implementación pendiente)

## 1. Convenciones

### 1.1 Peticiones

- `Content-Type: application/json` en cuerpos JSON.
- `X-CSRF-Token` obligatorio en `POST`, `PATCH` y `DELETE`.
- Fechas en formato ISO 8601 (`2026-11-13T15:30:00Z`).
- Paginación: `?page=1&per_page=20` (máximo 100).
- Búsqueda: `?q=texto`.
- Filtros por estado: `?estado=nueva`.

### 1.2 Respuestas

Éxito:

```json
{ "data": { } }
```

Lista:

```json
{
  "data": [],
  "meta": {
    "page": 1,
    "per_page": 20,
    "total": 0,
    "total_pages": 0
  }
}
```

Error:

```json
{
  "error": {
    "code": "VALIDACION",
    "message": "Los datos enviados no son válidos.",
    "details": {
      "email": "El correo no tiene un formato válido."
    }
  }
}
```

### 1.3 Códigos HTTP

| Código | Uso |
|---|---|
| `200` | Consulta o actualización exitosa |
| `201` | Recurso creado |
| `204` | Eliminación exitosa sin cuerpo |
| `400` | Petición mal formada |
| `401` | Sin sesión o sesión expirada |
| `403` | Sin permisos para el recurso |
| `404` | Recurso inexistente |
| `409` | Conflicto (correo duplicado, transición inválida) |
| `422` | Error de validación |
| `429` | Demasiadas peticiones |
| `500` | Error interno |

### 1.4 Roles

| Rol | Alcance |
|---|---|
| `visitante` | Endpoints públicos |
| `cliente` | Endpoints `/mi/*` y contenido público |
| `admin` | Endpoints `/admin/*` y contenido público |

## 2. Autenticación

### Flujo

1. `GET /auth/csrf` crea la sesión y devuelve un token.
2. `POST /auth/login` envía credenciales y el token CSRF.
3. El servidor regenera la sesión y devuelve el usuario.
4. El front llama `GET /auth/me` para restaurar el estado al recargar.
5. `POST /auth/logout` destruye la sesión.

### Endpoints

| Método | Ruta | Rol | Descripción |
|---|---|---|---|
| GET | `/auth/csrf` | público | Crea sesión y devuelve `csrf_token` |
| POST | `/auth/login` | público | Inicia sesión |
| POST | `/auth/logout` | autenticado | Cierra sesión |
| GET | `/auth/me` | autenticado | Usuario actual y token CSRF |
| POST | `/auth/registro` | público | Registro de cliente |
| PATCH | `/auth/password` | autenticado | Cambio de contraseña |
| POST | `/auth/recuperar` | público | *Opcional:* solicita recuperación |
| POST | `/auth/restablecer` | público | *Opcional:* restablece con token |

### Ejemplo: login

```http
POST /api/v1/auth/login
Content-Type: application/json
X-CSRF-Token: <token>

{
  "email": "cliente@ejemplo.com",
  "password": "Secreta123"
}
```

Respuesta `200`:

```json
{
  "data": {
    "usuario": {
      "id": 12,
      "nombre": "Ana Pérez",
      "email": "cliente@ejemplo.com",
      "rol": "cliente",
      "cliente_id": 5
    },
    "csrf_token": "nuevo-token"
  }
}
```

### Ejemplo: registro

```http
POST /api/v1/auth/registro
Content-Type: application/json
X-CSRF-Token: <token>

{
  "nombre": "Ana Pérez",
  "email": "ana@ejemplo.com",
  "empresa": "Acme SA",
  "password": "Secreta123",
  "password_confirmacion": "Secreta123",
  "acepta_terminos": true
}
```

Respuesta `201` con el usuario creado. Si el correo ya existe: `409 CORREO_DUPLICADO`.

## 3. Endpoints públicos

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/health` | Estado de la API y de la base de datos |
| GET | `/config` | Datos de la empresa, redes y textos |
| GET | `/servicios` | Servicios activos |
| GET | `/servicios/{slug}` | Detalle por `slug` |
| GET | `/tecnologias` | Catálogo activo |
| GET | `/portafolio` | Proyectos publicados |
| GET | `/portafolio/{slug}` | Detalle de caso publicado |
| POST | `/contacto` | Envía mensaje de contacto |
| POST | `/cotizaciones` | Solicitud de cotización pública |

### Ejemplo: contacto

```http
POST /api/v1/contacto
Content-Type: application/json
X-CSRF-Token: <token>

{
  "nombre": "Luis Gómez",
  "email": "luis@ejemplo.com",
  "asunto": "Consulta por dashboard",
  "mensaje": "Necesito un tablero para ventas.",
  "acepta_privacidad": true
}
```

Respuesta `201`:

```json
{
  "data": {
    "id": 31,
    "estado": "nuevo",
    "mensaje": "Mensaje recibido. Te contactaremos pronto."
  }
}
```

### Ejemplo: cotización pública

```http
POST /api/v1/cotizaciones
Content-Type: application/json
X-CSRF-Token: <token>

{
  "nombre_contacto": "María Ruiz",
  "email": "maria@empresa.com",
  "empresa": "Empresa SA",
  "telefono": "5551234567",
  "servicio_id": 2,
  "descripcion": "Aplicación móvil para clientes.",
  "presupuesto_estimado": 80000
}
```

Respuesta `201` con `estado: "nueva"` y `origen: "publico"`.

## 4. Portal del cliente

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/mi/perfil` | Perfil del cliente autenticado |
| PATCH | `/mi/perfil` | Actualiza nombre, empresa, teléfono y sitio |
| GET | `/mi/proyectos` | Proyectos del cliente |
| GET | `/mi/proyectos/{id}` | Detalle validando propiedad |
| GET | `/mi/solicitudes` | Solicitudes del cliente |
| POST | `/mi/solicitudes` | Nueva solicitud desde el portal |
| GET | `/mi/solicitudes/{id}` | Detalle validando propiedad |
| DELETE | `/mi/solicitudes/{id}` | Cancela una solicitud en estado `nueva` |

**Regla:** todas las consultas incluyen `cliente_id = <cliente de la sesión>` en el `WHERE`. Un `id` de otro cliente devuelve `403` o `404` sin filtrar información.

### Ejemplo: mis proyectos

```http
GET /api/v1/mi/proyectos?page=1&per_page=10
```

```json
{
  "data": [
    {
      "id": 7,
      "nombre": "Portal de proveedores",
      "estado": "en_progreso",
      "fecha_inicio": "2026-09-01",
      "fecha_fin": "2026-12-15",
      "tecnologias": ["PHP", "SQL Server", "JavaScript"]
    }
  ],
  "meta": {
    "page": 1,
    "per_page": 10,
    "total": 1,
    "total_pages": 1
  }
}
```

## 5. Panel de administración

### 5.1 Dashboard

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/admin/dashboard` | Conteos de usuarios, clientes, proyectos, solicitudes nuevas y mensajes sin atender |

### 5.2 Usuarios y roles

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/admin/usuarios` | Lista con filtros `q`, `rol`, `activo` |
| POST | `/admin/usuarios` | Crea usuario |
| GET | `/admin/usuarios/{id}` | Detalle |
| PATCH | `/admin/usuarios/{id}` | Actualiza nombre, correo y rol |
| PATCH | `/admin/usuarios/{id}/estado` | Activa o desactiva |
| GET | `/admin/roles` | Catálogo de roles |

### 5.3 Clientes

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/admin/clientes` | Lista con búsqueda |
| POST | `/admin/clientes` | Crea cliente y lo vincula a un usuario |
| GET | `/admin/clientes/{id}` | Detalle con proyectos y solicitudes |
| PATCH | `/admin/clientes/{id}` | Actualiza datos de empresa |

### 5.4 Servicios

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/admin/servicios` | Lista completa, incluidos inactivos |
| POST | `/admin/servicios` | Crea servicio |
| GET | `/admin/servicios/{id}` | Detalle |
| PATCH | `/admin/servicios/{id}` | Actualiza |
| DELETE | `/admin/servicios/{id}` | Desactiva (`activo = 0`) |

### 5.5 Proyectos

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/admin/proyectos` | Lista con filtros `cliente`, `estado`, `publicado` |
| POST | `/admin/proyectos` | Crea proyecto |
| GET | `/admin/proyectos/{id}` | Detalle con tecnologías |
| PATCH | `/admin/proyectos/{id}` | Actualiza datos y tecnologías |
| DELETE | `/admin/proyectos/{id}` | Elimina si no tiene historial; si lo tiene, responde `409` y debe cancelarse con `PATCH` |
| PATCH | `/admin/proyectos/{id}/publicacion` | Publica o despublica en portafolio |

### 5.6 Tecnologías

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/admin/tecnologias` | Lista completa |
| POST | `/admin/tecnologias` | Crea tecnología |
| PATCH | `/admin/tecnologias/{id}` | Actualiza |
| DELETE | `/admin/tecnologias/{id}` | Desactiva |

### 5.7 Solicitudes

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/admin/solicitudes` | Lista con filtros `estado`, `origen`, `q` |
| GET | `/admin/solicitudes/{id}` | Detalle |
| PATCH | `/admin/solicitudes/{id}/estado` | Cambia estado |
| POST | `/admin/solicitudes/{id}/convertir` | Crea cliente y/o proyecto desde la solicitud |

### 5.8 Mensajes

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/admin/mensajes` | Lista con filtros `estado`, `q` |
| GET | `/admin/mensajes/{id}` | Detalle |
| PATCH | `/admin/mensajes/{id}/estado` | Marca como atendido o archivado |
| DELETE | `/admin/mensajes/{id}` | Elimina spam |

## 6. Transiciones de estado

### Solicitudes

```text
nueva → en_revision → cotizada → aceptada
                             ↘ rechazada
```

- `nueva`: recién recibida.
- `en_revision`: el administrador la está evaluando.
- `cotizada`: se envió una propuesta.
- `aceptada` / `rechazada`: cierre.

### Proyectos

```text
planificado → en_progreso → en_revision → entregado
      ↘ pausado ↗
      ↘ cancelado
```

El backend valida la transición y devuelve `409 TRANSICION_INVALIDA` si no está permitida.

### Mensajes

```text
nuevo → en_revision → atendido → archivado
```

## 7. Validaciones principales

| Campo | Regla |
|---|---|
| `nombre` | 2–120 caracteres |
| `email` | Formato válido, máximo 254 caracteres |
| `password` | 8+ caracteres, mayúscula, minúscula y número |
| `slug` | `^[a-z0-9]+(?:-[a-z0-9]+)*$` |
| `descripcion` | 10–5000 caracteres |
| `presupuesto_estimado` | Número ≥ 0, máximo 12 dígitos |
| `estado` | Valor dentro del catálogo permitido |
| `telefono` | 7–30 caracteres, dígitos y separadores |

## 8. Rate limit

| Endpoint | Límite sugerido |
|---|---|
| `POST /auth/login` | 5 intentos por IP cada 15 minutos |
| `POST /auth/registro` | 3 por IP cada hora |
| `POST /contacto` | 3 por IP cada hora |
| `POST /cotizaciones` | 3 por IP cada hora |

Respuesta `429`:

```json
{
  "error": {
    "code": "DEMASIADAS_PETICIONES",
    "message": "Espera unos minutos antes de volver a intentarlo.",
    "details": { "retry_after": 300 }
  }
}
```

## 9. Seguridad del contrato

- Ningún endpoint devuelve `password_hash`.
- Los errores de login no distinguen entre correo inexistente y contraseña incorrecta.
- Los identificadores de otros clientes no se exponen en mensajes de error.
- Los listados administrativos respetan paginación y filtros.
- El token CSRF se renueva al iniciar sesión.

## 10. Estado de implementación

| Grupo | Estado |
|---|---|
| Públicos | Pendiente |
| Autenticación | Pendiente |
| Portal del cliente | Pendiente |
| Administración | Pendiente |
| Health check | Placeholder implementado |

La implementación debe respetar este contrato o actualizarlo junto con el changelog del backend.
