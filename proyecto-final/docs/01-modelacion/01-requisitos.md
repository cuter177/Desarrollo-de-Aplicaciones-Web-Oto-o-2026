# Requisitos del sistema

**Proyecto:** aplicación web para `[Nombre de la empresa]`, empresa desarrolladora de software (sitios web, dashboards y aplicaciones móviles iOS/Android).

**Versión:** 0.1.0 · **Fecha:** 2026-11-13

## 1. Contexto

La empresa necesita una presencia web profesional y un canal de contacto para atraer clientes, además de un portal privado donde cada cliente consulte el avance de sus proyectos y un panel administrativo para gestionar el contenido y las solicitudes.

El sistema se compone de:

- **Sitio público:** información de la empresa, servicios, portafolio y formularios.
- **Portal del cliente:** acceso autenticado a proyectos y solicitudes propias.
- **Panel de administración:** gestión de usuarios, clientes, contenido y mensajes.
- **API PHP + SQL Server:** centraliza reglas de negocio, autenticación y persistencia.

## 2. Actores

| Actor | Descripción | Permisos principales |
|---|---|---|
| Visitante | Persona sin sesión | Consultar sitio, enviar contacto y cotización |
| Cliente | Usuario registrado con rol `cliente` | Ver y editar su perfil, consultar sus proyectos y solicitudes |
| Administrador | Usuario con rol `admin` | Gestionar usuarios, clientes, servicios, proyectos, tecnologías, solicitudes y mensajes |

## 3. Requisitos funcionales

| ID | Requisito | Actor | Prioridad |
|---|---|---|---|
| RF-01 | El sitio público debe presentar la empresa, sus servicios y su propuesta de valor. | Visitante | Alta |
| RF-02 | El sitio debe listar servicios activos y mostrar el detalle de cada uno por `slug`. | Visitante | Alta |
| RF-03 | El sitio debe mostrar proyectos publicados como casos de éxito del portafolio. | Visitante | Alta |
| RF-04 | El visitante debe poder enviar un mensaje mediante el formulario de contacto. | Visitante | Alta |
| RF-05 | El visitante debe poder solicitar una cotización sin crear una cuenta. | Visitante | Alta |
| RF-06 | El visitante debe poder registrarse como cliente con nombre, correo y contraseña. | Visitante | Alta |
| RF-07 | El sistema debe permitir iniciar y cerrar sesión de forma segura. | Cliente / Admin | Alta |
| RF-08 | El sistema debe restringir el acceso según el rol del usuario. | Todos | Alta |
| RF-09 | El cliente debe poder consultar y editar sus datos de perfil. | Cliente | Media |
| RF-10 | El cliente debe poder consultar únicamente sus proyectos y el detalle de cada uno. | Cliente | Alta |
| RF-11 | El cliente debe poder crear y consultar sus solicitudes de cotización. | Cliente | Alta |
| RF-12 | El administrador debe poder gestionar usuarios y su estado (`activo`/`inactivo`). | Administrador | Alta |
| RF-13 | El administrador debe poder gestionar clientes, servicios, proyectos y tecnologías. | Administrador | Alta |
| RF-14 | El administrador debe poder atender solicitudes y mensajes, y cambiar su estado. | Administrador | Alta |
| RF-15 | El administrador debe poder publicar o despublicar proyectos en el portafolio. | Administrador | Media |
| RF-16 | El panel administrativo debe mostrar un resumen de métricas y actividad reciente. | Administrador | Media |
| RF-17 | El usuario autenticado debe poder cambiar su contraseña. | Cliente / Admin | Media |
| RF-18 | El sistema debe validar los datos y devolver mensajes de error claros. | Todos | Alta |

## 4. Requisitos no funcionales

| ID | Requisito | Criterio de aceptación |
|---|---|---|
| RNF-01 | Diseño Mobile First | Los estilos base son para móvil y se amplían con `min-width` en 480 px, 768 px y 1024 px. |
| RNF-02 | Seguridad de credenciales | Contraseñas almacenadas con `password_hash`; nunca en texto plano. |
| RNF-03 | Prevención de inyección SQL | Todas las consultas usan PDO con parámetros preparados. |
| RNF-04 | Protección de sesión | Cookie `HttpOnly`, `SameSite=Lax` y `Secure` en producción; token CSRF en mutaciones. |
| RNF-05 | Autorización en servidor | El backend valida rol y propiedad del recurso en cada petición. |
| RNF-06 | Rendimiento percibido | El sitio público carga en menos de 3 s en una conexión móvil razonable. |
| RNF-07 | Accesibilidad | HTML semántico, etiquetas asociadas, foco visible y contraste AA. |
| RNF-08 | Compatibilidad | Últimas dos versiones de Chrome, Firefox, Edge y Safari. |
| RNF-09 | Mantenibilidad | Separación front/back, documentación y changelogs actualizados. |
| RNF-10 | Disponibilidad de datos | Respaldo de la base de datos antes de cada entrega. |
| RNF-11 | Configuración | Credenciales y parámetros por variables de entorno. |
| RNF-12 | Trazabilidad | Cambios registrados en el changelog correspondiente. |

## 5. Reglas de negocio

- RN-01: El correo de un usuario es único en el sistema.
- RN-02: Un cliente solo puede consultar proyectos y solicitudes asociados a su propio registro.
- RN-03: Un visitante puede enviar una cotización sin cuenta; si se registra después, el administrador puede vincularla manualmente.
- RN-04: Solo el administrador puede publicar un proyecto en el portafolio.
- RN-05: Los usuarios y clientes se desactivan lógicamente (`activo = 0`); no se eliminan si tienen historial.
- RN-06: Una solicitud solo puede avanzar por transiciones permitidas: `nueva → en_revision → cotizada → aceptada` o `rechazada`.
- RN-07: Un proyecto entregado o cancelado no puede volver a un estado anterior sin autorización del administrador.
- RN-08: Las contraseñas deben tener al menos 8 caracteres, una mayúscula, una minúscula y un número.

## 6. Alcances

- Sitio público responsive con cinco secciones principales.
- Registro e inicio de sesión de clientes.
- Portal de cliente con perfil, proyectos y solicitudes.
- Panel administrativo con CRUD de contenido y atención de solicitudes.
- API REST documentada y base de datos SQL Server.
- Manuales de usuario y técnico, contrato y documentos de modelación.

## 7. Limitaciones

- No incluye pasarela de pagos ni facturación electrónica.
- No incluye aplicación móvil nativa; el sitio será responsive.
- No incluye chat en tiempo real ni videollamadas.
- La recuperación de contraseña por correo queda como funcionalidad opcional.
- La carga de imágenes del portafolio se definirá en una segunda iteración.
- El envío de correos transaccionales depende de un proveedor externo no contemplado en el alcance inicial.

## 8. Supuestos

- El cliente proporciona logotipo, textos, imágenes y datos de contacto.
- El sistema se despliega en un entorno con Docker y SQL Server 2022.
- Los usuarios tienen acceso a un navegador moderno con JavaScript habilitado.
- El proyecto es académico y no sustituye una auditoría de seguridad profesional.

## 9. Criterios de aceptación del proyecto

1. Un visitante completa contacto y cotización sin errores.
2. Un cliente se registra, inicia sesión y ve solo su información.
3. Un administrador gestiona el contenido y las solicitudes.
4. El backend rechaza peticiones sin sesión o con rol incorrecto.
5. La base de datos se reconstruye desde los scripts SQL.
6. La documentación permite instalar y operar el sistema.
