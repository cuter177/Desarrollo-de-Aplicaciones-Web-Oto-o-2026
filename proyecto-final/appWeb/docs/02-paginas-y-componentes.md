# Páginas y componentes

Inventario de pantallas y piezas reutilizables del front.

## 1. Páginas públicas

| Página | Archivo previsto | Contenido | API |
|---|---|---|---|
| Inicio | `index.html` | Hero, servicios destacados, portafolio y CTA | `GET /servicios`, `GET /portafolio` |
| Servicios | `pages/servicios.html` | Catálogo | `GET /servicios` |
| Detalle de servicio | `pages/servicio-detalle.html?slug=` | Descripción y CTA | `GET /servicios/{slug}` |
| Portafolio | `pages/portafolio.html` | Casos publicados | `GET /portafolio` |
| Detalle de caso | `pages/caso-detalle.html?slug=` | Reto, solución y tecnologías | `GET /portafolio/{slug}` |
| Nosotros | `pages/nosotros.html` | Historia y enfoque | `GET /config` |
| Contacto | `pages/contacto.html` | Formulario | `POST /contacto` |
| Cotización | `pages/cotizacion.html` | Formulario | `GET /servicios`, `POST /cotizaciones` |
| Login | `pages/login.html` | Acceso | `GET /auth/csrf`, `POST /auth/login` |
| Registro | `pages/registro.html` | Alta de cliente | `GET /auth/csrf`, `POST /auth/registro` |

## 2. Portal del cliente

| Página | Archivo previsto | Contenido | API |
|---|---|---|---|
| Resumen | `pages/portal/index.html` | Bienvenida y accesos | `GET /auth/me` |
| Proyectos | `pages/portal/proyectos.html` | Lista | `GET /mi/proyectos` |
| Detalle | `pages/portal/proyecto-detalle.html?id=` | Estado y tecnologías | `GET /mi/proyectos/{id}` |
| Solicitudes | `pages/portal/solicitudes.html` | Historial y alta | `GET/POST /mi/solicitudes` |
| Detalle | `pages/portal/solicitud-detalle.html?id=` | Estado | `GET /mi/solicitudes/{id}` |
| Perfil | `pages/portal/perfil.html` | Datos y contraseña | `GET/PATCH /mi/perfil` |

## 3. Panel de administración

| Página | Archivo previsto | Contenido | API |
|---|---|---|---|
| Dashboard | `pages/admin/dashboard.html` | Métricas y actividad | `GET /admin/dashboard` |
| Usuarios | `pages/admin/usuarios.html` | Tabla y formulario | `GET/POST/PATCH /admin/usuarios` |
| Clientes | `pages/admin/clientes.html` | Tabla y formulario | `GET/POST/PATCH /admin/clientes` |
| Servicios | `pages/admin/servicios.html` | CRUD | `GET/POST/PATCH/DELETE /admin/servicios` |
| Proyectos | `pages/admin/proyectos.html` | CRUD y publicación | `GET/POST/PATCH /admin/proyectos` |
| Tecnologías | `pages/admin/tecnologias.html` | CRUD | `GET/POST/PATCH/DELETE /admin/tecnologias` |
| Solicitudes | `pages/admin/solicitudes.html` | Bandeja y estados | `GET/PATCH /admin/solicitudes` |
| Mensajes | `pages/admin/mensajes.html` | Bandeja | `GET/PATCH/DELETE /admin/mensajes` |

## 4. Componentes reutilizables

| Componente | Clases | Uso |
|---|---|---|
| Encabezado | `.site-header`, `.nav`, `.nav__link` | Navegación y sesión |
| Pie | `.site-footer` | Enlaces y copyright |
| Hero | `.hero`, `.hero__title`, `.hero__actions` | Portada |
| Tarjeta | `.card`, `.card__title`, `.card__body` | Servicios y proyectos |
| Botón | `.button`, `.button--primary`, `.button--ghost` | Acciones |
| Formulario | `.form`, `.form__group`, `.form__error` | Contacto, login, CRUD |
| Campo | `.field`, `.field__label`, `.field__input` | Entradas |
| Tabla | `.table`, `.table--responsive` | Listados administrativos |
| Badge de estado | `.badge`, `.badge--success`, `.badge--warning` | Estados |
| Alerta | `.alert`, `.alert--error`, `.alert--success` | Mensajes |
| Modal | `.modal`, `.modal__dialog` | Confirmaciones |
| Estado vacío | `.empty-state` | Listas sin datos |
| Paginación | `.pagination` | Listados |
| Cargando | `.spinner`, `.skeleton` | Peticiones |

## 5. Estados de interfaz

Cada página que consume la API debe manejar:

1. **Cargando:** esqueleto o spinner.
2. **Vacío:** mensaje y acción sugerida.
3. **Error:** mensaje claro y botón de reintento.
4. **Sin permiso:** acceso denegado con enlace al portal.
5. **Éxito:** confirmación y actualización de la vista.

## 6. Formularios

- Etiqueta asociada a cada campo (`for`/`id`).
- Texto de ayuda cuando el formato no es obvio.
- Error junto al campo y resumen al inicio en formularios largos.
- Estado `disabled` mientras se envía para evitar dobles peticiones.
- Conservar los datos capturados cuando hay error.
- Confirmación antes de acciones destructivas.

## 7. Matriz de acceso

| Zona | Visitante | Cliente | Admin |
|---|---|---|---|
| Público | ✅ | ✅ | ✅ |
| Login / registro | ✅ | Redirige | Redirige |
| `/pages/portal/*` | ❌ | ✅ | ✅ |
| `/pages/admin/*` | ❌ | ❌ | ✅ |

La matriz es informativa; la autorización definitiva está en el backend.

## 8. Convenciones de nombres

- Archivos en minúsculas y guiones: `proyecto-detalle.html`.
- Páginas administrativas dentro de `pages/admin/`.
- Un archivo JS por página en `assets/js/pages/`.
- Clases BEM: `.bloque__elemento--modificador`.
- Identificadores de formulario en `camelCase`: `nombreContacto`.
