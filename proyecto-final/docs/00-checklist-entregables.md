# Checklist de entregables

Seguimiento de los requisitos de la materia. Actualizar el estado conforme avance el proyecto.

**Estados:** `Pendiente` · `En progreso` · `Listo` · `No aplica`

## 1. Modelación del proyecto

| # | Entregable | Ruta | Estado | Observaciones |
|---|---|---|---|---|
| 1.1 | Requisitos funcionales y no funcionales | [`01-modelacion/01-requisitos.md`](01-modelacion/01-requisitos.md) | En progreso | Base creada; falta validar con el cliente |
| 1.2 | Mapa de sitio | [`01-modelacion/02-mapa-sitio.md`](01-modelacion/02-mapa-sitio.md) | En progreso | Pendiente revisión final |
| 1.3 | Guiones | [`01-modelacion/03-guiones.md`](01-modelacion/03-guiones.md) | En progreso | 6 escenarios iniciales |
| 1.4 | Diagrama de navegación | [`01-modelacion/04-diagrama-navegacion.md`](01-modelacion/04-diagrama-navegacion.md) | En progreso | Diagramas Mermaid |
| 1.5 | Estructura de archivos | [`01-modelacion/05-estructura-archivos.md`](01-modelacion/05-estructura-archivos.md) | Listo | Refleja el split backEnd/appWeb |

## 2. Desarrollo del proyecto

| # | Entregable | Ruta | Estado | Observaciones |
|---|---|---|---|---|
| 2.1 | HTML + CSS Mobile First | `appWeb/` | Listo | Sitio público completo, login, registro, portal y panel; validado en 375, 768 y 1280 px |
| 2.2 | JavaScript: usuarios, login y restricciones | `appWeb/assets/js/` | Listo | Sesión, CSRF, roles, guardas y módulos públicos implementados |
| 2.3 | Backend PHP (API REST) | `backEnd/` | En progreso | Router, auth, catálogo público, portafolio, contacto y panel; faltan CRUD restantes |
| 2.4 | Diseño de la base de datos | [`backEnd/docs/02-base-datos/`](../backEnd/docs/02-base-datos/) | Listo | Modelo ER, diccionario y esquema alineados |
| 2.5 | Scripts SQL | `backEnd/database/` | Listo | `schema.sql` y `seed.sql` idempotentes, verificados en base limpia |
| 2.6 | Autenticación y roles | `backEnd/app/` | Listo | Login, registro, sesión, CSRF, rate limit y roles |
| 2.7 | Pruebas | `backEnd/tests/` | En progreso | 40 pruebas manuales de API con `curl`; falta suite automatizada |

## 3. Documentación

| # | Entregable | Ruta | Estado | Observaciones |
|---|---|---|---|---|
| 3.1 | Manual de usuario | [`02-manual-usuario/README.md`](02-manual-usuario/README.md) | En progreso | Sitio público, registro, login, portal y panel documentados; faltan capturas |
| 3.2 | Manual técnico | [`03-manual-tecnico/README.md`](03-manual-tecnico/README.md) | En progreso | Instalación, BD, API y seguridad actualizados a 0.4.0 |
| 3.3 | Documentación del backend | [`../backEnd/docs/`](../backEnd/docs/) | En progreso | API, seguridad y despliegue actualizados a 0.4.0 |
| 3.4 | Documentación del frontend | [`../appWeb/docs/`](../appWeb/docs/) | En progreso | Páginas públicas, estilos y accesibilidad de la etapa 4 documentados |
| 3.5 | Changelogs | `docs/CHANGELOG.md`, `backEnd/CHANGELOG.md`, `appWeb/CHANGELOG.md` | Listo | Formato Keep a Changelog |
| 3.6 | PDFs en LaTeX | [`latex/`](latex/) | Pendiente | Proyecto LaTeX por crear |

## 4. Contrato

| # | Entregable | Ruta | Estado | Observaciones |
|---|---|---|---|---|
| 4.1 | Contrato de prestación de servicios | [`04-contrato/01-contrato-servicios.md`](04-contrato/01-contrato-servicios.md) | En progreso | Plantilla con placeholders |
| 4.2 | Alcances y limitaciones | [`04-contrato/01-contrato-servicios.md`](04-contrato/01-contrato-servicios.md) | En progreso | Incluidos en el contrato |

## 5. Entrega final

| # | Entregable | Ruta | Estado | Observaciones |
|---|---|---|---|---|
| 5.1 | Código fuente en ZIP | `dist/` | Pendiente | Crear script de empaquetado |
| 5.2 | Documentación en PDF | `docs/latex/build/` | Pendiente | Compilar con `latexmk` |
| 5.3 | Verificación de `.env` fuera del ZIP | — | Pendiente | Excluir secretos y `.git` |
| 5.4 | Prueba de instalación desde cero | — | En progreso | `schema.sql` y `seed.sql` verificados en base limpia; falta reconstruir los contenedores desde cero |

## Criterios de aceptación globales

- [x] El sitio funciona en móvil, tablet y escritorio.
- [x] Un visitante puede enviar contacto y solicitud de cotización.
  - [x] Contacto y diagnóstico probados con `curl` sobre Docker.
- [ ] Un cliente puede registrarse, iniciar sesión y ver solo sus proyectos.
  - [x] Registro, login y sesión implementados y probados.
  - [ ] Listado de proyectos propios (etapa siguiente).
- [ ] Un administrador puede gestionar el contenido y los usuarios.
  - [x] Dashboard con métricas reales y acceso restringido a `admin`.
  - [x] Bandeja de solicitudes, proyectos y mensajes.
  - [ ] CRUD de usuarios, clientes, servicios y tecnologías.
- [x] El backend rechaza accesos no autorizados aunque se manipule el front.
- [x] La base de datos se crea desde `schema.sql` sin errores en una base limpia.
- [ ] Los manuales permiten instalar y usar el sistema sin ayuda externa.
- [ ] El ZIP y los PDFs cumplen el formato de entrega.
