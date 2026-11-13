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

## Alcance

El sistema se divide en tres experiencias:

1. **Sitio público:** servicios, portafolio, nosotros, contacto y solicitud de cotización.
2. **Portal del cliente:** registro, inicio de sesión, perfil, proyectos y solicitudes.
3. **Panel de administración:** usuarios, clientes, servicios, proyectos, tecnologías, solicitudes y mensajes.

La arquitectura es **desacoplada**: `appWeb` es un front estático (HTML + CSS + JavaScript) y `backEnd` expone una API REST en PHP que accede a SQL Server.

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
├── appWeb/       # Front estático: páginas, estilos y JavaScript
├── backEnd/      # API PHP, configuración y base de datos
├── docker/       # Imágenes y configuración de contenedores
├── docs/         # Modelación, manuales, contrato y LaTeX
├── scripts/      # Utilidades de desarrollo y entrega
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
# Editar .env y cambiar DB_PASSWORD
docker compose up -d --build
```

Servicios disponibles:

| Servicio | URL |
|---|---|
| Sitio web | <http://localhost:8080> |
| API | <http://localhost:8080/api/v1> |
| SQL Server | `localhost:1433` |

Para cargar el esquema inicial:

```bash
docker compose exec -T mssql /bin/bash -c \
  '/opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P "$MSSQL_SA_PASSWORD" -C -i /dev/stdin' \
  < backEnd/database/schema.sql
```

> Si tu instalación usa la ruta antigua de las herramientas, cambia `/opt/mssql-tools18/bin/sqlcmd` por `/opt/mssql-tools/bin/sqlcmd`.

Los pasos completos, la conexión desde Azure Data Studio o DBeaver y la solución de problemas están en el [manual técnico](docs/03-manual-tecnico/README.md).

## Documentación

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
| Documentación del backend | [`backEnd/docs/`](backEnd/docs/) |
| Documentación del frontend | [`appWeb/docs/`](appWeb/docs/) |
| Changelog de documentación | [`docs/CHANGELOG.md`](docs/CHANGELOG.md) |
| Changelog del backend | [`backEnd/CHANGELOG.md`](backEnd/CHANGELOG.md) |
| Changelog del frontend | [`appWeb/CHANGELOG.md`](appWeb/CHANGELOG.md) |

## Convenciones

- **Mobile First:** los estilos base se escriben para móvil y se amplían con `min-width`.
- **Seguridad:** contraseñas con `password_hash`/`password_verify`, PDO con consultas preparadas, sesiones `HttpOnly`, token CSRF y roles verificados en el servidor.
- **Restricciones:** el front oculta rutas por rol, pero el backend siempre valida permisos.
- **Secretos:** `.env` no se versiona; solo se publica `.env.example`.
- **Nombres:** carpetas en minúsculas, recursos de la API en español y rutas en plural.
- **Changelogs:** formato [Keep a Changelog](https://keepachangelog.com/es-ES/1.1.0/) y versionado semántico.

## Estado del proyecto

`0.1.0` — Andamiaje del repositorio y documentación base. Aún no hay endpoints ni pantallas funcionales.

## Licencia

El repositorio del curso se distribuye bajo **GNU GPL v3**; consulta el archivo [`LICENSE`](../LICENSE).
