# Pruebas del backend

Carpeta reservada para las pruebas de la API.

## Pruebas previstas

| Tipo | Qué cubre |
|---|---|
| Unitarias | Helpers de validación, slugs, respuestas y estados |
| Integración | Login, registro, permisos y CRUD contra una BD de prueba |
| Seguridad | Acceso sin sesión, rol incorrecto, CSRF y SQL injection |
| Contrato | Formato de respuestas y códigos HTTP |

## Convenciones

- Cada prueba debe crear y limpiar sus datos.
- No usar la base de datos de desarrollo.
- Documentar cómo ejecutar las pruebas en el manual técnico.
- Registrar los cambios relevantes en `backEnd/CHANGELOG.md`.

## Estado

`0.5.0` — `scripts/probar-api.sh` ejercita autenticación, roles, aislamiento entre clientes, CSRF y el CRUD de tecnologías y servicios contra la API en ejecución.
