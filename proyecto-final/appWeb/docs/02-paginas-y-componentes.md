# Páginas y componentes

Inventario de pantallas y piezas reutilizables del front.

## 1. Páginas públicas

| Página | Archivo | Contenido | API | Estado |
|---|---|---|---|---|
| Inicio | `index.html` | Hero, accesos y estado del proyecto | `GET /config` | Implementado |
| Servicios | `pages/servicios.html` | Catálogo | `GET /servicios` | Implementado |
| Detalle de servicio | `pages/servicio-detalle.html?slug=` | Descripción, beneficios y casos | `GET /servicios/{slug}` | Implementado |
| Portafolio | `pages/portafolio.html` | Casos publicados y filtros | `GET /portafolio` | Implementado |
| Detalle de caso | `pages/caso-detalle.html?slug=` | Reto, solución, resultados y tecnologías | `GET /portafolio/{slug}` | Implementado |
| Nosotros | `pages/nosotros.html` | Proceso, enfoque y equipo | `GET /config`, `GET /servicios`, `GET /portafolio` | Implementado |
| Contacto | `pages/contacto.html` | Formulario y datos de contacto | `GET /config`, `POST /contacto` | Implementado |
| Cotización | `pages/cotizacion.html` | Planes y wizard de diagnóstico | `GET /planes`, `GET /servicios`, `POST /diagnostico` | Implementado |
| Login | `pages/login.html` | Acceso y redirección por rol | `GET /auth/csrf`, `POST /auth/login`, `GET /auth/me` | Implementado |
| Registro | `pages/registro.html` | Alta de cliente | `GET /auth/csrf`, `POST /auth/registro` | Implementado |
| No encontrado | `404.html` | Error 404 y enlaces de recuperación | — | Implementado |

## 2. Portal del cliente

| Página | Archivo | Contenido | API | Estado |
|---|---|---|---|---|
| Resumen | `pages/portal/index.html` | Bienvenida, datos de sesión y placeholders | `GET /auth/me` | Implementado |
| Proyectos | `pages/portal/proyectos.html` | Lista | `GET /mi/proyectos` | Pendiente |
| Detalle | `pages/portal/proyecto-detalle.html?id=` | Estado y tecnologías | `GET /mi/proyectos/{id}` | Pendiente |
| Solicitudes | `pages/portal/solicitudes.html` | Historial y alta | `GET/POST /mi/solicitudes` | Pendiente |
| Detalle | `pages/portal/solicitud-detalle.html?id=` | Estado | `GET /mi/solicitudes/{id}` | Pendiente |
| Perfil | `pages/portal/perfil.html` | Datos y contraseña | `GET/PATCH /mi/perfil` | Pendiente |

## 3. Panel de administración

| Página | Archivo | Contenido | API | Estado |
|---|---|---|---|---|
| Dashboard | `pages/admin/dashboard.html` | Métricas reales y datos de sesión | `GET /admin/dashboard` | Implementado |
| Usuarios | `pages/admin/usuarios.html` | Tabla y formulario | `GET/POST/PATCH /admin/usuarios` | Pendiente |
| Clientes | `pages/admin/clientes.html` | Tabla y formulario | `GET/POST/PATCH /admin/clientes` | Pendiente |
| Servicios | `pages/admin/servicios.html` | CRUD | `GET/POST/PATCH/DELETE /admin/servicios` | Pendiente |
| Proyectos | `pages/admin/proyectos.html` | CRUD, publicación y contenido de caso | `GET/POST/PATCH /admin/proyectos` | Implementado |
| Tecnologías | `pages/admin/tecnologias.html` | CRUD | `GET/POST/PATCH/DELETE /admin/tecnologias` | Pendiente |
| Solicitudes | `pages/admin/solicitudes.html` | Bandeja y estados | `GET/PATCH /admin/solicitudes` | Implementado |
| Mensajes | `pages/admin/mensajes.html` | Bandeja y estados | `GET/PATCH /admin/mensajes` | Implementado |

## 4. Componentes reutilizables

| Componente | Clases | Uso | Estado |
|---|---|---|---|
| Encabezado | `.site-header`, `.site-nav`, `.site-nav__link`, `[data-auth]` | Navegación y sesión | Implementado |
| Pie | `.site-footer` | Enlaces y copyright | Implementado |
| Hero | `.hero`, `.hero__title`, `.hero__actions` | Portada | Implementado |
| Tarjeta | `.card`, `.card__title`, `.card__body` | Servicios y proyectos | Implementado |
| Botón | `.btn`, `.btn--secondary`, `.btn--ghost`, `.btn--danger`, `.btn--block` | Acciones | Implementado |
| Formulario | `.form`, `.form__field`, `.form__error`, `.form__actions` | Login, registro, CRUD | Implementado |
| Campo | `.form__label`, `.form__input`, `.form__select`, `.form__textarea` | Entradas | Implementado |
| Alerta | `.alert`, `.alert--info`, `.alert--success`, `.alert--warning`, `.alert--error` | Mensajes | Implementado |
| Panel | `.panel`, `.panel__title` | Bloques de contenido | Implementado |
| Métrica | `.metrics`, `.metric`, `.metric__value` | Dashboard y portal | Implementado |
| Lista de datos | `.data-list`, `.data-list__row` | Datos de sesión | Implementado |
| Estado vacío | `.empty-state` | Listas sin datos | Implementado |
| Tabla | `.table`, `.table--responsive` | Listados administrativos | Pendiente |
| Modal | `.modal`, `.modal__dialog` | Confirmaciones | Pendiente |
| Paginación | `.pagination` | Listados | Pendiente |
| Cargando | `.spinner`, `.skeleton` | Peticiones | Pendiente |
| Tarjeta de servicio | `.service-cards`, `.service-card`, `.service-card__icon` | Catálogo público | Implementado |
| Tarjeta de caso | `.case-grid`, `.case-card`, `.case-card__media` | Portafolio | Implementado |
| Filtros | `.filters`, `.filter-chip` | Portafolio | Implementado |
| Tecnologías | `.tech-list`, `.tech` | Servicios y casos | Implementado |
| Lista de beneficios | `.check-list` | Detalle de servicio | Implementado |
| Métricas de nosotros | `.stats`, `.stat` | Página Nosotros | Implementado |
| Pasos de proceso | `.steps`, `.step` | Página Nosotros | Implementado |
| Equipo | `.team-grid`, `.team-card`, `.avatar` | Página Nosotros | Implementado |
| Contacto | `.contact-grid`, `.contact-card`, `.contact-list` | Página Contacto | Implementado |
| Banda CTA | `.cta-band` | Cierre de secciones | Implementado |
| Honeypot | `.honeypot` | Anti-spam del contacto | Implementado |

> La nomenclatura real usa `.btn` y `.form__field`; las clases `.button` y `.field` quedan descartadas.

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
