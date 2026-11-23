# Estructura de archivos

Organización del repositorio y responsabilidades de cada carpeta. El proyecto se divide en `backEnd` (API PHP + SQL Server) y `appWeb` (front estático), con documentación independiente para cada parte.

## 1. Árbol completo

```text
proyecto-final/
├── README.md                      # Visión general, inicio rápido y enlaces
├── .gitignore
├── .editorconfig
├── .env.example                   # Variables de entorno de ejemplo
├── compose.yaml                   # Nginx + PHP/Apache + SQL Server
│
├── docker/
│   ├── api/
│   │   ├── Dockerfile             # PHP 8.3 + pdo_sqlsrv + msodbcsql18
│   │   └── php.ini                # Configuración de desarrollo
│   └── web/
│       └── nginx.conf             # Sitio estático + proxy /api
│
├── docs/                          # Documentación académica y transversal
│   ├── CHANGELOG.md               # Cambios en la documentación
│   ├── 00-checklist-entregables.md
│   ├── 01-modelacion/
│   │   ├── 01-requisitos.md
│   │   ├── 02-mapa-sitio.md
│   │   ├── 03-guiones.md
│   │   ├── 04-diagrama-navegacion.md
│   │   └── 05-estructura-archivos.md
│   ├── 02-manual-usuario/
│   │   └── README.md
│   ├── 03-manual-tecnico/
│   │   └── README.md
│   ├── 04-contrato/
│   │   └── 01-contrato-servicios.md
│   └── latex/
│       └── README.md              # Generación de PDFs
│
├── backEnd/                       # API PHP
│   ├── README.md
│   ├── CHANGELOG.md
│   ├── docs/
│   │   ├── 01-arquitectura.md
│   │   ├── 02-base-datos/
│   │   │   ├── 01-modelo-er.md
│   │   │   └── 02-diccionario-datos.md
│   │   ├── 03-api.md
│   │   ├── 04-seguridad.md
│   │   └── 05-despliegue.md
│   ├── public/
│   │   ├── .htaccess              # Rewrite hacia index.php
│   │   └── index.php              # Front controller de la API
│   ├── app/
│   │   ├── controllers/           # Peticiones y respuestas
│   │   ├── models/                # Acceso a datos
│   │   ├── middleware/            # Sesión, roles, CSRF
│   │   └── helpers/               # Utilidades
│   ├── config/                    # Configuración y conexión
│   ├── database/
│   │   ├── schema.sql             # Esquema de SQL Server
│   │   ├── seed.sql               # Datos iniciales
│   │   └── migrations/            # 001_planes_diagnostico.sql y 002_portafolio_publico.sql
│   ├── scripts/                   # Utilidades CLI (crear-admin.php)
│   └── tests/
│
├── appWeb/                        # Front estático
│   ├── README.md
│   ├── CHANGELOG.md
│   ├── docs/
│   │   ├── 01-arquitectura-front.md
│   │   ├── 02-paginas-y-componentes.md
│   │   ├── 03-guia-estilos.md
│   │   └── 04-accesibilidad.md
│   ├── index.html
│   ├── 404.html                  # Página no encontrada
│   ├── pages/                    # Páginas internas
│   │   ├── servicios.html
│   │   ├── servicio-detalle.html
│   │   ├── portafolio.html
│   │   ├── caso-detalle.html
│   │   ├── nosotros.html
│   │   ├── contacto.html
│   │   ├── cotizacion.html
│   │   ├── login.html
│   │   ├── registro.html
│   │   ├── portal/
│   │   └── admin/                # dashboard, solicitudes, proyectos y mensajes
│   └── assets/
│       ├── css/
│       ├── js/
│       ├── img/
│       └── fonts/
│
└── scripts/                       # Utilidades de desarrollo y entrega
    └── README.md
```

## 2. Responsabilidades

### `appWeb/`

- HTML semántico y accesible.
- CSS Mobile First con variables y breakpoints.
- JavaScript para navegación, formularios, sesión y consumo de la API.
- Sin lógica de negocio crítica: las reglas y permisos viven en el backend.

### `backEnd/`

- API REST versionada en `/api/v1`.
- Validación de datos y reglas de negocio.
- Autenticación, sesiones, CSRF y autorización por roles.
- Acceso a SQL Server con PDO y consultas preparadas.
- Scripts de esquema, datos iniciales y migraciones.

### `docs/`

- Entregables académicos transversales: requisitos, modelación, manuales, contrato y PDFs.
- Enlaza a la documentación técnica detallada de cada carpeta.

### `docker/` y `compose.yaml`

- Reproducen el entorno completo: Nginx, PHP/Apache y SQL Server.
- Nginx sirve `appWeb` y hace proxy de `/api` al backend para mantener el mismo origen.

### `scripts/`

- Automatizaciones futuras: empaquetado del ZIP, respaldos de BD y verificación del entorno.

## 3. Convenciones

| Elemento | Convención | Ejemplo |
|---|---|---|
| Carpetas | minúsculas | `backEnd`, `appWeb`, `assets` |
| Archivos de documentación | prefijo numérico | `01-requisitos.md` |
| Recursos de API | plural, español | `/servicios`, `/proyectos` |
| Variables de entorno | mayúsculas con guion bajo | `DB_PASSWORD` |
| Clases CSS | BEM | `.card__title`, `.form--compact` |
| Archivos JS | módulos ES | `api.js`, `auth.js` |
| Tablas SQL | PascalCase en plural | `Usuarios`, `Proyectos` |

## 4. Reglas de dependencia

```text
appWeb  →  /api  →  backEnd  →  SQL Server
```

- `appWeb` no accede directamente a la base de datos.
- `backEnd` no contiene HTML ni presentación.
- `docs/` no depende del código; describe el sistema.
- Los secretos viven en `.env`, nunca en archivos versionados.

## 5. Archivos que no se versionan

- `.env` y variantes locales.
- `vendor/`, `node_modules/`.
- Respaldos de SQL Server (`.bak`, `.mdf`, `.ldf`).
- Auxiliares de LaTeX y PDFs generados.
- ZIPs de entrega.
- Logs y archivos temporales.

Ver [`.gitignore`](../../.gitignore) para la lista completa.
