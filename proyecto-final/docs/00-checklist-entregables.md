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
| 2.1 | HTML + CSS Mobile First | `appWeb/` | Pendiente | Solo placeholder |
| 2.2 | JavaScript: usuarios, login y restricciones | `appWeb/assets/js/` | Pendiente | Sin implementar |
| 2.3 | Backend PHP (API REST) | `backEnd/` | Pendiente | Solo health check |
| 2.4 | Diseño de la base de datos | [`backEnd/docs/02-base-datos/`](../backEnd/docs/02-base-datos/) | En progreso | Modelo ER y diccionario creados |
| 2.5 | Scripts SQL | `backEnd/database/` | Pendiente | Esqueleto sin tablas |
| 2.6 | Autenticación y roles | `backEnd/app/` | Pendiente | Documentado, no implementado |
| 2.7 | Pruebas | `backEnd/tests/` | Pendiente | Sin pruebas |

## 3. Documentación

| # | Entregable | Ruta | Estado | Observaciones |
|---|---|---|---|---|
| 3.1 | Manual de usuario | [`02-manual-usuario/README.md`](02-manual-usuario/README.md) | En progreso | Índice y plantilla |
| 3.2 | Manual técnico | [`03-manual-tecnico/README.md`](03-manual-tecnico/README.md) | En progreso | Índice y enlaces |
| 3.3 | Documentación del backend | [`../backEnd/docs/`](../backEnd/docs/) | En progreso | Base técnica |
| 3.4 | Documentación del frontend | [`../appWeb/docs/`](../appWeb/docs/) | En progreso | Base técnica |
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
| 5.4 | Prueba de instalación desde cero | — | Pendiente | Docker no probado aún en el equipo |

## Criterios de aceptación globales

- [ ] El sitio funciona en móvil, tablet y escritorio.
- [ ] Un visitante puede enviar contacto y solicitud de cotización.
- [ ] Un cliente puede registrarse, iniciar sesión y ver solo sus proyectos.
- [ ] Un administrador puede gestionar el contenido y los usuarios.
- [ ] El backend rechaza accesos no autorizados aunque se manipule el front.
- [ ] La base de datos se crea desde `schema.sql` sin errores.
- [ ] Los manuales permiten instalar y usar el sistema sin ayuda externa.
- [ ] El ZIP y los PDFs cumplen el formato de entrega.
