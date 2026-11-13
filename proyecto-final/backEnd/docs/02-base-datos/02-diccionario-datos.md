# Diccionario de datos

Definición de tablas, columnas, tipos y restricciones para SQL Server 2022.

**Base de datos:** `DevStudioDB`
**Colación sugerida:** `Modern_Spanish_CI_AS`
**Convención:** tablas en PascalCase plural; columnas en `snake_case`.

## 1. Roles

Catálogo de roles del sistema.

| Columna | Tipo | Nulo | Clave / valor | Descripción |
|---|---|---|---|---|
| `id` | `INT IDENTITY(1,1)` | No | PK | Identificador |
| `nombre` | `NVARCHAR(30)` | No | UNIQUE | `admin` o `cliente` |
| `descripcion` | `NVARCHAR(200)` | Sí | — | Descripción del rol |
| `creado_en` | `DATETIME2(0)` | No | DEFAULT `SYSUTCDATETIME()` | Fecha de alta |

## 2. Usuarios

Credenciales y datos de acceso.

| Columna | Tipo | Nulo | Clave / valor | Descripción |
|---|---|---|---|---|
| `id` | `INT IDENTITY(1,1)` | No | PK | Identificador |
| `nombre` | `NVARCHAR(120)` | No | — | Nombre completo |
| `email` | `NVARCHAR(254)` | No | UNIQUE | Correo de acceso |
| `password_hash` | `NVARCHAR(255)` | No | — | Hash generado con `password_hash` |
| `rol_id` | `INT` | No | FK → `Roles.id` | Rol del usuario |
| `activo` | `BIT` | No | DEFAULT `1` | Borrado lógico |
| `ultimo_acceso` | `DATETIME2(0)` | Sí | — | Último login exitoso |
| `creado_en` | `DATETIME2(0)` | No | DEFAULT `SYSUTCDATETIME()` | Fecha de alta |
| `actualizado_en` | `DATETIME2(0)` | No | DEFAULT `SYSUTCDATETIME()` | Última modificación |

**Reglas:** correo único sin distinguir mayúsculas; contraseña mínima de 8 caracteres con mayúscula, minúscula y número; no se elimina físicamente si tiene historial.

## 3. Clientes

Perfil de empresa vinculado a un usuario con rol `cliente`.

| Columna | Tipo | Nulo | Clave / valor | Descripción |
|---|---|---|---|---|
| `id` | `INT IDENTITY(1,1)` | No | PK | Identificador |
| `usuario_id` | `INT` | No | UNIQUE, FK → `Usuarios.id` | Usuario de acceso |
| `empresa` | `NVARCHAR(150)` | No | — | Nombre de la empresa |
| `telefono` | `NVARCHAR(30)` | Sí | — | Teléfono de contacto |
| `sitio_web` | `NVARCHAR(200)` | Sí | — | Sitio de la empresa |
| `industria` | `NVARCHAR(100)` | Sí | — | Giro o sector |
| `creado_en` | `DATETIME2(0)` | No | DEFAULT `SYSUTCDATETIME()` | Fecha de alta |
| `actualizado_en` | `DATETIME2(0)` | No | DEFAULT `SYSUTCDATETIME()` | Última modificación |

## 4. Servicios

Catálogo público de servicios.

| Columna | Tipo | Nulo | Clave / valor | Descripción |
|---|---|---|---|---|
| `id` | `INT IDENTITY(1,1)` | No | PK | Identificador |
| `slug` | `NVARCHAR(120)` | No | UNIQUE | Identificador para URL |
| `nombre` | `NVARCHAR(120)` | No | — | Nombre del servicio |
| `descripcion` | `NVARCHAR(MAX)` | No | — | Descripción completa |
| `icono` | `NVARCHAR(80)` | Sí | — | Nombre o ruta del icono |
| `activo` | `BIT` | No | DEFAULT `1` | Visible en el sitio |
| `orden` | `INT` | No | DEFAULT `0` | Orden de presentación |
| `creado_en` | `DATETIME2(0)` | No | DEFAULT `SYSUTCDATETIME()` | Fecha de alta |
| `actualizado_en` | `DATETIME2(0)` | No | DEFAULT `SYSUTCDATETIME()` | Última modificación |

**Reglas:** el `slug` solo contiene minúsculas, números y guiones; cambiarlo altera la URL pública.

## 5. Tecnologías

Catálogo de tecnologías para clasificar proyectos.

| Columna | Tipo | Nulo | Clave / valor | Descripción |
|---|---|---|---|---|
| `id` | `INT IDENTITY(1,1)` | No | PK | Identificador |
| `nombre` | `NVARCHAR(80)` | No | UNIQUE | Nombre de la tecnología |
| `categoria` | `NVARCHAR(60)` | Sí | — | Frontend, backend, móvil, BD, etc. |
| `icono` | `NVARCHAR(80)` | Sí | — | Icono opcional |
| `activo` | `BIT` | No | DEFAULT `1` | Disponible en catálogo |
| `creado_en` | `DATETIME2(0)` | No | DEFAULT `SYSUTCDATETIME()` | Fecha de alta |

## 6. Proyectos

Proyectos de clientes y casos de portafolio.

| Columna | Tipo | Nulo | Clave / valor | Descripción |
|---|---|---|---|---|
| `id` | `INT IDENTITY(1,1)` | No | PK | Identificador |
| `cliente_id` | `INT` | No | FK → `Clientes.id` | Cliente propietario |
| `nombre` | `NVARCHAR(150)` | No | — | Nombre del proyecto |
| `slug` | `NVARCHAR(160)` | No | UNIQUE | Identificador para URL pública |
| `descripcion` | `NVARCHAR(MAX)` | No | — | Descripción |
| `estado` | `NVARCHAR(30)` | No | CHECK | `planificado`, `en_progreso`, `en_revision`, `entregado`, `pausado`, `cancelado` |
| `fecha_inicio` | `DATE` | Sí | — | Inicio del proyecto |
| `fecha_fin` | `DATE` | Sí | — | Fin estimado o real |
| `url_demo` | `NVARCHAR(300)` | Sí | — | Enlace a la demo |
| `publicado` | `BIT` | No | DEFAULT `0` | Visible en portafolio |
| `destacado` | `BIT` | No | DEFAULT `0` | Destacado en inicio |
| `creado_en` | `DATETIME2(0)` | No | DEFAULT `SYSUTCDATETIME()` | Fecha de alta |
| `actualizado_en` | `DATETIME2(0)` | No | DEFAULT `SYSUTCDATETIME()` | Última modificación |

**Reglas:** solo los proyectos `publicado = 1` aparecen en el sitio público; un proyecto cancelado no puede publicarse sin autorización.

## 7. ProyectoTecnologia

Relación N:M entre proyectos y tecnologías.

| Columna | Tipo | Nulo | Clave / valor | Descripción |
|---|---|---|---|---|
| `proyecto_id` | `INT` | No | PK, FK → `Proyectos.id` | Proyecto |
| `tecnologia_id` | `INT` | No | PK, FK → `Tecnologias.id` | Tecnología |

**Reglas:** la llave primaria es compuesta; al eliminar un proyecto se eliminan sus relaciones (`ON DELETE CASCADE`).

## 8. SolicitudesCotizacion

Solicitudes de prospectos y clientes.

| Columna | Tipo | Nulo | Clave / valor | Descripción |
|---|---|---|---|---|
| `id` | `INT IDENTITY(1,1)` | No | PK | Identificador |
| `cliente_id` | `INT` | Sí | FK → `Clientes.id` | Cliente, si la solicitud viene del portal |
| `servicio_id` | `INT` | Sí | FK → `Servicios.id` | Servicio de interés |
| `nombre_contacto` | `NVARCHAR(120)` | No | — | Nombre de quien solicita |
| `email` | `NVARCHAR(254)` | No | — | Correo de contacto |
| `empresa` | `NVARCHAR(150)` | Sí | — | Empresa del prospecto |
| `telefono` | `NVARCHAR(30)` | Sí | — | Teléfono |
| `descripcion` | `NVARCHAR(MAX)` | No | — | Detalle de la necesidad |
| `presupuesto_estimado` | `DECIMAL(12,2)` | Sí | CHECK `>= 0` | Presupuesto expresado por el cliente |
| `estado` | `NVARCHAR(30)` | No | CHECK | `nueva`, `en_revision`, `cotizada`, `aceptada`, `rechazada` |
| `origen` | `NVARCHAR(20)` | No | CHECK | `publico` o `portal` |
| `creado_en` | `DATETIME2(0)` | No | DEFAULT `SYSUTCDATETIME()` | Fecha de alta |
| `actualizado_en` | `DATETIME2(0)` | No | DEFAULT `SYSUTCDATETIME()` | Última modificación |

**Reglas:** `cliente_id` es obligatorio cuando `origen = 'portal'`; las transiciones de estado se validan en el backend.

## 9. MensajesContacto

Mensajes del formulario público.

| Columna | Tipo | Nulo | Clave / valor | Descripción |
|---|---|---|---|---|
| `id` | `INT IDENTITY(1,1)` | No | PK | Identificador |
| `nombre` | `NVARCHAR(120)` | No | — | Nombre del remitente |
| `email` | `NVARCHAR(254)` | No | — | Correo |
| `asunto` | `NVARCHAR(150)` | No | — | Asunto |
| `mensaje` | `NVARCHAR(MAX)` | No | — | Contenido |
| `estado` | `NVARCHAR(20)` | No | CHECK | `nuevo`, `en_revision`, `atendido`, `archivado` |
| `creado_en` | `DATETIME2(0)` | No | DEFAULT `SYSUTCDATETIME()` | Fecha de alta |
| `atendido_en` | `DATETIME2(0)` | Sí | — | Fecha de atención |

## 10. Resumen de restricciones

| Restricción | Tabla | Definición |
|---|---|---|
| `UQ_Usuarios_Email` | `Usuarios` | `UNIQUE (email)` |
| `UQ_Clientes_Usuario` | `Clientes` | `UNIQUE (usuario_id)` |
| `UQ_Servicios_Slug` | `Servicios` | `UNIQUE (slug)` |
| `UQ_Proyectos_Slug` | `Proyectos` | `UNIQUE (slug)` |
| `CK_Proyectos_Estado` | `Proyectos` | Lista de estados permitidos |
| `CK_Solicitudes_Estado` | `SolicitudesCotizacion` | Lista de estados permitidos |
| `CK_Solicitudes_Origen` | `SolicitudesCotizacion` | `publico` o `portal` |
| `CK_Solicitudes_Presupuesto` | `SolicitudesCotizacion` | `presupuesto_estimado IS NULL OR presupuesto_estimado >= 0` |
| `CK_Mensajes_Estado` | `MensajesContacto` | Lista de estados permitidos |
| `FK_ProyectoTecnologia_Proyecto` | `ProyectoTecnologia` | `ON DELETE CASCADE` |

## 11. Datos iniciales

`seed.sql` debe insertar, como mínimo:

1. Roles `admin` y `cliente`.
2. Un usuario administrador de desarrollo con contraseña temporal.
3. Servicios de ejemplo: desarrollo web, dashboards, apps móviles, consultoría.
4. Tecnologías de ejemplo: HTML, CSS, JavaScript, PHP, SQL Server, Flutter, React Native.

> Las contraseñas del seed deben cambiarse antes de cualquier despliegue real.

## 12. Tipos y convenciones

| Necesidad | Tipo |
|---|---|
| Identificadores | `INT IDENTITY(1,1)` |
| Textos cortos | `NVARCHAR(120)` / `NVARCHAR(150)` |
| Correos | `NVARCHAR(254)` |
| Textos largos | `NVARCHAR(MAX)` |
| Booleanos | `BIT` con `DEFAULT 0/1` |
| Fechas y horas | `DATETIME2(0)` en UTC |
| Solo fecha | `DATE` |
| Dinero | `DECIMAL(12,2)` |
| Enumeraciones | `NVARCHAR` + `CHECK` |
