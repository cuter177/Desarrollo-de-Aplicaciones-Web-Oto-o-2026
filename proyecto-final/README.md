# Proyecto Final — [Nombre de la empresa]

Aplicación web para una empresa desarrolladora de software dedicada a sitios web, dashboards y aplicaciones móviles (iOS y Android). Este repositorio contiene el proyecto de fin de materia de **Desarrollo de Aplicaciones Web**.

> **Nombre comercial:** `[Nombre de la empresa]` (placeholder).
> **Base de datos:** `DevStudioDB` (placeholder).
> Actualizar ambos nombres en la documentación y en `.env` cuando se defina la marca.

## Equipo

| Integrante | Matrícula | Rol |
|---|---|---|
| `[Integrante 1]` | `[Matrícula]` | `[Rol]` |
| `[Integrante 2]` | `[Matrícula]` | `[Rol]` |

- **Materia:** Desarrollo de Aplicaciones Web
- **Periodo:** Otoño 2026
- **Profesor:** `[Nombre del profesor]`

## Estado actual

`0.3.0` — Diagnóstico público y gestión de proyectos.

| Experiencia | Estado |
|---|---|
| Sitio público | Inicio y diagnóstico con planes implementados; servicios, portafolio, nosotros y contacto pendientes. |
| Portal del cliente | Registro, login y acceso protegido; vistas de proyectos, solicitudes y perfil pendientes. |
| Panel administrativo | Dashboard, bandeja de solicitudes y gestión de proyectos implementados; usuarios, clientes, servicios, tecnologías y mensajes pendientes. |
| API y base de datos | Autenticación, planes, diagnóstico, solicitudes, proyectos y seed de demostración implementados. |

## Alcance

El sistema se divide en tres experiencias:

1. **Sitio público:** presentación de la empresa, planes, diagnóstico de necesidades, portafolio y contacto.
2. **Portal del cliente:** registro, inicio de sesión, perfil, proyectos y solicitudes.
3. **Panel de administración:** dashboard, solicitudes, proyectos, usuarios, clientes, servicios, tecnologías y mensajes.

La arquitectura es **desacoplada**: `appWeb` es un front estático (HTML + CSS + JavaScript) y `backEnd` expone una API REST en PHP que accede a SQL Server.

## Funcionalidades implementadas

### Autenticación y seguridad

- Registro de clientes, login, logout y restauración de sesión.
- Roles `admin` y `cliente` validados en el servidor.
- Contraseñas con `password_hash`/`password_verify` y rehash automático.
- CSRF con `X-CSRF-Token`, rate limit de login y sesiones `HttpOnly`.
- Portal y panel administrativo protegidos por rol.

### Captación y diagnóstico público

- Página de presentación con los planes **Esencial**, **Crecimiento** y **Transformación**.
- Wizard de diagnóstico en cuatro pasos: contacto, necesidades, objetivos y resultado.
- Preguntas de negocio: crecimiento esperado, horas a ahorrar, procesos a eficientar, presupuesto, plazo y etapa de decisión.
- Recomendación de plan calculada en el servidor con un puntaje de prioridad.
- Guardado del diagnóstico como solicitud con origen `publico`.

### Panel administrativo

- Dashboard con usuarios, clientes, proyectos, solicitudes nuevas, mensajes, pipeline estimado, tasa de conversión y crecimiento promedio.
- Bandeja de solicitudes con filtros por estado, plan y búsqueda.
- Detalle del diagnóstico, notas internas, cambio de estado, plan propuesto y cliente vinculado.
- Conversión de una solicitud en proyecto.
- Gestión de proyectos: alta, edición, estado, fechas, URL demo, publicación y destacado.

### Datos de demostración

- Seed idempotente con roles, servicios, tecnologías, planes, usuarios, clientes, proyectos, solicitudes y mensajes.
- Cuentas de prueba para administrador y clientes.
- Diagnósticos de ejemplo con metas de crecimiento y ahorro de horas.

## Stack

| Capa | Tecnología |
|---|---|
| Front | HTML5, CSS3 Mobile First, JavaScript (ES Modules) |
| Back | PHP 8.3, API REST, PDO con `sqlsrv` |
| Base de datos | Microsoft SQL Server 2022 |
| Entorno | Docker + Docker Compose (Nginx, PHP/Apache, SQL Server) |
| Documentación | Markdown; PDFs en LaTeX (pendiente) |

## Estructura del repositorio

```text
proyecto-final/
├── appWeb/                       # Front estático
│   ├── index.html                # Inicio y propuesta de valor
│   ├── pages/
│   │   ├── cotizacion.html       # Diagnóstico y planes
│   │   ├── login.html
│   │   ├── registro.html
│   │   ├── portal/index.html
│   │   └── admin/
│   │       ├── dashboard.html
│   │       ├── solicitudes.html
│   │       └── proyectos.html
│   ├── assets/css/
│   ├── assets/js/pages/
│   └── docs/
├── backEnd/                      # API PHP + SQL Server
│   ├── app/
│   │   ├── controllers/
│   │   ├── models/
│   │   ├── services/             # PlanRecommender
│   │   ├── middleware/
│   │   └── helpers/
│   ├── config/
│   ├── database/
│   │   ├── schema.sql
│   │   ├── seed.sql
│   │   └── migrations/
│   ├── public/
│   ├── scripts/
│   └── docs/
├── docker/                       # Imágenes y configuración de contenedores
├── docs/                         # Modelación, manuales, contrato y LaTeX
├── scripts/                      # Utilidades de desarrollo y entrega
├── compose.yaml
└── .env.example
```

La descripción detallada está en [`docs/01-modelacion/05-estructura-archivos.md`](docs/01-modelacion/05-estructura-archivos.md).

## Inicio rápido

### Requisitos

- Docker Engine 24+ y Docker Compose v2.
- Arquitectura `x86_64` (la imagen de SQL Server para Linux no soporta ARM de forma nativa).
- Al menos 2 GB de RAM disponibles para el contenedor de SQL Server.
- Puerto `8080` libre para la web y `1433` para SQL Server.

### Pasos

```bash
cd proyecto-final
cp .env.example .env
# Editar .env y cambiar DB_PASSWORD, ADMIN_PASSWORD y APP_URL
docker compose up -d --build
```

Servicios disponibles:

| Servicio | URL |
|---|---|
| Sitio web | <http://localhost:8080> |
| Diagnóstico público | <http://localhost:8080/pages/cotizacion.html> |
| API | <http://localhost:8080/api/v1> |
| Health check | <http://localhost:8080/api/v1/health> |
| SQL Server | `localhost:1433` |

Cargar el esquema y los datos iniciales (catálogos, cuentas y datos de demostración):

```bash
docker compose exec -T mssql /bin/bash -c \
  '/opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P "$MSSQL_SA_PASSWORD" -C -i /dev/stdin' \
  < backEnd/database/schema.sql

docker compose exec -T mssql /bin/bash -c \
  '/opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P "$MSSQL_SA_PASSWORD" -C -i /dev/stdin' \
  < backEnd/database/seed.sql
```

> Si ya tenías la base de datos de la etapa 2, aplica antes la migración
> `backEnd/database/migrations/001_planes_diagnostico.sql` con el mismo comando.

El seed es idempotente e incluye cuentas de prueba:

| Rol | Correo | Contraseña |
|---|---|---|
| `admin` | `admin@devstudio.local` | `Admin#2026` |
| `cliente` | `cliente@devstudio.local` | `Cliente#2026` |
| `cliente` | `carlos.ruiz@innovatech.mx` | `Cliente#2026` |
| `cliente` | `ana.torres@nexoclinica.com` | `Cliente#2026` |

> Las contraseñas del seed son solo para desarrollo; cámbialas antes de cualquier despliegue.
> Si prefieres crear el administrador con tus propias variables `ADMIN_*`, ejecuta
> `docker compose exec api php scripts/crear-admin.php` después del seed. El script no modifica
> una cuenta existente.

> Si tu instalación usa la ruta antigua de las herramientas, cambia `/opt/mssql-tools18/bin/sqlcmd` por `/opt/mssql-tools/bin/sqlcmd`.

Abrir <http://localhost:8080/pages/login.html> e iniciar sesión con alguna de las cuentas de prueba.

Los pasos completos, la conexión desde Azure Data Studio o DBeaver y la solución de problemas están en el [manual técnico](docs/03-manual-tecnico/README.md).

## Cómo probar el flujo completo

1. Abrir <http://localhost:8080/pages/cotizacion.html>.
2. Revisar los planes y completar el wizard con datos de prueba.
3. Enviar el diagnóstico y revisar el plan recomendado.
4. Iniciar sesión como `admin@devstudio.local` / `Admin#2026`.
5. Entrar a <http://localhost:8080/pages/admin/solicitudes.html>.
6. Abrir la solicitud, vincular un cliente y usar **Crear proyecto**.
7. Entrar a <http://localhost:8080/pages/admin/proyectos.html> y verificar el proyecto creado.
8. Volver al dashboard y comprobar las métricas actualizadas.

## Rutas del front

| Página | Ruta | Acceso |
|---|---|---|
| Inicio | `/index.html` | Público |
| Diagnóstico y planes | `/pages/cotizacion.html` | Público |
| Login | `/pages/login.html` | Público; redirige si hay sesión |
| Registro | `/pages/registro.html` | Público; redirige si hay sesión |
| Portal del cliente | `/pages/portal/index.html` | `cliente` y `admin` |
| Dashboard | `/pages/admin/dashboard.html` | Solo `admin` |
| Solicitudes | `/pages/admin/solicitudes.html` | Solo `admin` |
| Proyectos | `/pages/admin/proyectos.html` | Solo `admin` |

## API implementada

Base: `/api/v1`.

| Método | Ruta | Acceso |
|---|---|---|
| GET | `/health` | Público |
| GET | `/planes` | Público |
| GET | `/servicios` | Público |
| POST | `/diagnostico` | Público + CSRF |
| GET | `/auth/csrf` | Público |
| POST | `/auth/login` | Público |
| POST | `/auth/logout` | Autenticado |
| GET | `/auth/me` | Autenticado |
| POST | `/auth/registro` | Público |
| PATCH | `/auth/password` | Autenticado |
| GET | `/admin/dashboard` | Solo `admin` |
| GET | `/admin/clientes` | Solo `admin` |
| GET | `/admin/solicitudes` | Solo `admin` |
| GET | `/admin/solicitudes/{id}` | Solo `admin` |
| PATCH | `/admin/solicitudes/{id}` | Solo `admin` |
| POST | `/admin/solicitudes/{id}/convertir` | Solo `admin` |
| GET | `/admin/proyectos` | Solo `admin` |
| GET | `/admin/proyectos/{id}` | Solo `admin` |
| POST | `/admin/proyectos` | Solo `admin` |
| PATCH | `/admin/proyectos/{id}` | Solo `admin` |

El contrato completo está en [`backEnd/docs/03-api.md`](backEnd/docs/03-api.md).

## Base de datos

Tablas del esquema:

`Roles`, `Usuarios`, `Clientes`, `Servicios`, `Tecnologias`, `Proyectos`, `ProyectoTecnologia`, `Planes`, `SolicitudesCotizacion` y `MensajesContacto`.

Scripts:

| Archivo | Función |
|---|---|
| `backEnd/database/schema.sql` | Esquema completo e idempotente |
| `backEnd/database/seed.sql` | Catálogos, cuentas y datos de demostración |
| `backEnd/database/migrations/001_planes_diagnostico.sql` | Planes y campos del diagnóstico para bases existentes |

El modelo y el diccionario están en [`backEnd/docs/02-base-datos/`](backEnd/docs/02-base-datos/).

## Documentación

### Documentación general

| Documento | Ruta |
|---|---|
| Checklist de entregables | [`docs/00-checklist-entregables.md`](docs/00-checklist-entregables.md) |
| Requisitos | [`docs/01-modelacion/01-requisitos.md`](docs/01-modelacion/01-requisitos.md) |
| Mapa de sitio | [`docs/01-modelacion/02-mapa-sitio.md`](docs/01-modelacion/02-mapa-sitio.md) |
| Guiones | [`docs/01-modelacion/03-guiones.md`](docs/01-modelacion/03-guiones.md) |
| Diagrama de navegación | [`docs/01-modelacion/04-diagrama-navegacion.md`](docs/01-modelacion/04-diagrama-navegacion.md) |
| Estructura de archivos | [`docs/01-modelacion/05-estructura-archivos.md`](docs/01-modelacion/05-estructura-archivos.md) |
| Manual de usuario | [`docs/02-manual-usuario/README.md`](docs/02-manual-usuario/README.md) |
| Manual técnico | [`docs/03-manual-tecnico/README.md`](docs/03-manual-tecnico/README.md) |
| Contrato de servicios | [`docs/04-contrato/01-contrato-servicios.md`](docs/04-contrato/01-contrato-servicios.md) |
| Documentación LaTeX | [`docs/latex/README.md`](docs/latex/README.md) |
| Changelog de documentación | [`docs/CHANGELOG.md`](docs/CHANGELOG.md) |

### Backend

| Documento | Ruta |
|---|---|
| README del backend | [`backEnd/README.md`](backEnd/README.md) |
| Arquitectura | [`backEnd/docs/01-arquitectura.md`](backEnd/docs/01-arquitectura.md) |
| Modelo ER | [`backEnd/docs/02-base-datos/01-modelo-er.md`](backEnd/docs/02-base-datos/01-modelo-er.md) |
| Diccionario de datos | [`backEnd/docs/02-base-datos/02-diccionario-datos.md`](backEnd/docs/02-base-datos/02-diccionario-datos.md) |
| Contrato de API | [`backEnd/docs/03-api.md`](backEnd/docs/03-api.md) |
| Seguridad | [`backEnd/docs/04-seguridad.md`](backEnd/docs/04-seguridad.md) |
| Despliegue | [`backEnd/docs/05-despliegue.md`](backEnd/docs/05-despliegue.md) |
| Scripts del backend | [`backEnd/scripts/README.md`](backEnd/scripts/README.md) |
| Changelog del backend | [`backEnd/CHANGELOG.md`](backEnd/CHANGELOG.md) |

### Frontend

| Documento | Ruta |
|---|---|
| README del frontend | [`appWeb/README.md`](appWeb/README.md) |
| Arquitectura del front | [`appWeb/docs/01-arquitectura-front.md`](appWeb/docs/01-arquitectura-front.md) |
| Páginas y componentes | [`appWeb/docs/02-paginas-y-componentes.md`](appWeb/docs/02-paginas-y-componentes.md) |
| Guía de estilos | [`appWeb/docs/03-guia-estilos.md`](appWeb/docs/03-guia-estilos.md) |
| Accesibilidad | [`appWeb/docs/04-accesibilidad.md`](appWeb/docs/04-accesibilidad.md) |
| Changelog del frontend | [`appWeb/CHANGELOG.md`](appWeb/CHANGELOG.md) |

## Convenciones

- **Mobile First:** los estilos base se escriben para móvil y se amplían con `min-width`.
- **Seguridad:** contraseñas con `password_hash`/`password_verify`, PDO con consultas preparadas, sesiones `HttpOnly`, token CSRF y roles verificados en el servidor.
- **Restricciones:** el front oculta rutas por rol, pero el backend siempre valida permisos.
- **Secretos:** `.env` no se versiona; solo se publica `.env.example`.
- **Nombres:** carpetas en minúsculas, recursos de la API en español y rutas en plural.
- **Changelogs:** formato [Keep a Changelog](https://keepachangelog.com/es-ES/1.1.0/) y versionado semántico.

## Verificación realizada

- Esquema y migración aplicados sobre SQL Server 2022 en Docker.
- Seed ejecutado dos veces sin duplicar registros.
- `php -l` sin errores en los archivos nuevos y modificados.
- Pruebas de API con `curl`: diagnóstico público, dashboard, bandeja, actualización y conversión.
- Prueba en Chromium headless: planes y servicios públicos, métricas del dashboard, bandeja con detalle y listado de proyectos.

## Pendientes

- Catálogos públicos de servicios, portafolio, nosotros y contacto.
- Portal del cliente con proyectos y solicitudes propias.
- CRUD de usuarios, clientes, servicios, tecnologías y mensajes.
- Recuperación de contraseña (opcional).
- Capturas y validación visual final en navegador.
- Documentos PDF en LaTeX.

## Licencia

El repositorio del curso se distribuye bajo **GNU GPL v3**; consulta el archivo [`LICENSE`](../LICENSE).
