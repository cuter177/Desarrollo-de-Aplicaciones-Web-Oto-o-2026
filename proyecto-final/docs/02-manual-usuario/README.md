# Manual de usuario

**Sistema:** `[Nombre de la empresa]` — sitio web, portal de clientes y panel administrativo
**Versión:** 0.4.0 · **Última actualización:** 2026-11-15

> Este manual está en construcción. La versión 0.4.0 permite navegar el sitio público completo, enviar mensajes de contacto y solicitar un diagnóstico. Las secciones del portal de proyectos/solicitudes y de los CRUD administrativos restantes describen el comportamiento previsto y se completarán en las siguientes etapas.

## Contenido

1. [Introducción](#1-introducción)
2. [Requisitos del usuario](#2-requisitos-del-usuario)
3. [Acceso al sistema](#3-acceso-al-sistema)
4. [Sitio público](#4-sitio-público)
5. [Portal del cliente](#5-portal-del-cliente)
6. [Panel de administración](#6-panel-de-administración)
7. [Preguntas frecuentes](#7-preguntas-frecuentes)
8. [Soporte](#8-soporte)

## 1. Introducción

Este manual explica cómo usar la aplicación web de `[Nombre de la empresa]`. El sistema permite:

- Conocer los servicios y el portafolio de la empresa.
- Enviar mensajes y solicitudes de cotización.
- Registrarse como cliente y consultar proyectos y solicitudes.
- Administrar el contenido y la atención de prospectos.

## 2. Requisitos del usuario

- Navegador actualizado: Chrome, Firefox, Edge o Safari.
- Conexión a internet.
- JavaScript habilitado.
- Para el portal: cuenta de cliente activa.
- Para el panel: cuenta con rol de administrador.

## 3. Acceso al sistema

### 3.1 Crear una cuenta

1. Abrir `http://localhost:8080/pages/registro.html`.
2. Capturar nombre completo, correo electrónico y empresa. El teléfono es opcional.
3. Escribir la contraseña dos veces y aceptar los términos.
4. Pulsar **Crear cuenta**.
5. El sistema vuelve al login con un mensaje de confirmación.
6. Iniciar sesión con el correo y la contraseña registrados.

**Política de contraseña:** entre 8 y 200 caracteres, con al menos una mayúscula, una minúscula y un número.

**Errores frecuentes:**

| Mensaje | Causa | Solución |
|---|---|---|
| “Este correo ya está registrado” | La cuenta existe | Iniciar sesión o usar otro correo |
| “Las contraseñas no coinciden” | Confirmación distinta | Volver a escribirla |
| “La contraseña debe incluir…” | No cumple la política | Agregar mayúscula, minúscula o número |

### 3.2 Iniciar sesión

1. Abrir `http://localhost:8080/pages/login.html`.
2. Capturar correo y contraseña.
3. Pulsar **Entrar**.
4. El sistema redirige automáticamente según el rol:
   - Cliente → `/pages/portal/index.html`
   - Administrador → `/pages/admin/dashboard.html`

Tras cinco intentos fallidos desde la misma IP o con el mismo correo, el sistema bloquea el login durante 15 minutos.

### 3.3 Cerrar sesión

Pulsar **Cerrar sesión** en el encabezado. El sistema elimina la sesión y vuelve al inicio.

El encabezado cambia según la sesión: un visitante ve **Iniciar sesión** y **Crear cuenta**; un cliente ve **Mi portal**; un administrador ve además **Administración**.

> **Captura pendiente:** pantallas de registro y login implementadas en la versión 0.2.0.

## 4. Sitio público

### 4.1 Inicio

Presenta la propuesta de valor, servicios destacados y casos de éxito. Desde aquí se accede a las demás secciones.

### 4.2 Servicios

1. Abrir **Servicios** en el menú.
2. Seleccionar un servicio para ver su descripción, beneficios y casos relacionados.
3. Pulsar **Hacer mi diagnóstico** o **Solicitar diagnóstico** para iniciar una solicitud.

### 4.3 Portafolio

1. Abrir **Portafolio** en el menú.
2. Filtrar por tecnología con los botones superiores.
3. Seleccionar un caso para ver reto, solución, resultados y tecnologías.

Solo aparecen los proyectos publicados por el administrador.

### 4.4 Contacto

1. Abrir **Contacto**.
2. Capturar nombre, correo, asunto y mensaje.
3. Aceptar el aviso de privacidad.
4. Pulsar **Enviar mensaje**.
5. Verificar el mensaje de confirmación.

### 4.5 Solicitud de cotización

1. Abrir **Diagnóstico**.
2. Capturar datos de contacto y responder el cuestionario en cuatro pasos.
3. Pulsar **Enviar diagnóstico**.
4. El equipo administrativo revisará la solicitud y dará seguimiento.

**Errores frecuentes:**

| Mensaje | Causa | Solución |
|---|---|---|
| “Correo inválido” | Formato incorrecto | Revisar el correo |
| “Campo obligatorio” | Falta información | Completar el campo marcado |
| “Demasiados intentos” | Límite de envíos | Esperar unos minutos |

> **Capturas pendientes:** inicio, servicios, portafolio, contacto y diagnóstico.

## 5. Portal del cliente

El cliente entra con su cuenta y consulta solo sus proyectos y solicitudes. Cambiar un identificador en la dirección no muestra datos de otra empresa.

### 5.1 Resumen

Después de iniciar sesión, el cliente ve un resumen con sus proyectos y solicitudes recientes.

### 5.2 Mis proyectos

1. Abrir **Mis proyectos**.
2. Consultar la lista con estado y fechas.
3. Abrir un proyecto para ver el detalle.

El cliente solo puede ver proyectos asociados a su cuenta.

### 5.3 Mis solicitudes

1. Abrir **Mis solicitudes**.
2. Consultar el historial y el estado de cada solicitud.
3. Pulsar **Nueva solicitud** para registrar otra.

Estados posibles: `nueva`, `en_revision`, `cotizada`, `aceptada`, `rechazada`.

### 5.4 Mi perfil

Permite actualizar nombre, teléfono, empresa y contraseña. El correo electrónico no se puede cambiar desde el portal; para hacerlo, contactar al administrador.

> **Capturas pendientes:** portal, proyectos, solicitudes y perfil.

## 6. Panel de administración

El panel admite el rol `admin`. Desde ahí se gestionan solicitudes, proyectos, mensajes, usuarios, clientes, servicios y tecnologías. Un cliente que abre estas páginas vuelve a su portal; la API responde `403`.

### 6.1 Dashboard

Muestra conteos de usuarios, proyectos, solicitudes nuevas y mensajes sin atender, además de la actividad reciente.

Si un cliente intenta abrir `/pages/admin/dashboard.html`, el front lo redirige a su portal; si intenta llamar la API directamente, el backend responde `403`.

### 6.2 Usuarios

- Consultar usuarios.
- Cambiar rol o estado (`activo`/`inactivo`).
- Restablecer contraseña de forma administrativa (si está habilitado).

### 6.3 Clientes

- Registrar empresas y datos de contacto.
- Vincular un cliente con su usuario.
- Consultar proyectos y solicitudes asociados.

### 6.4 Servicios

- Crear, editar y desactivar servicios.
- Cambiar el `slug` con cuidado: afecta la URL pública.

### 6.5 Proyectos

- Crear y editar proyectos.
- Asignar cliente, estado, fechas y URL demo.
- Capturar reto, solución y resultados del caso.
- Publicar o despublicar en el portafolio, destacar y decidir si se muestra el nombre del cliente.

### 6.6 Tecnologías

Catálogo usado para clasificar servicios y proyectos.

### 6.7 Solicitudes

1. Abrir **Solicitudes**.
2. Filtrar por estado.
3. Abrir una solicitud y revisar los datos.
4. Cambiar el estado según el avance.
5. Opcional: convertir la solicitud en proyecto.

### 6.8 Mensajes

1. Abrir **Mensajes**.
2. Filtrar por estado o buscar por nombre, correo o asunto.
3. Abrir el detalle.
4. Cambiar el estado a `en_revision`, `atendido` o `archivado`.

> **Capturas pendientes:** dashboard y cada módulo administrativo.

## 7. Preguntas frecuentes

**¿Necesito una cuenta para pedir una cotización?**
No. Cualquier visitante puede enviar el formulario. La cuenta es necesaria para consultar proyectos y solicitudes.

**Olvidé mi contraseña.**
La recuperación por correo es una funcionalidad opcional. Mientras no esté habilitada, contactar al administrador.

**¿Por qué no veo el menú de administración?**
Porque la cuenta no tiene rol `admin`. El acceso se valida en el servidor.

**¿Puedo ver los proyectos de otra empresa?**
No. El sistema filtra por el cliente de la sesión.

**¿Qué navegadores son compatibles?**
Las últimas dos versiones de Chrome, Firefox, Edge y Safari.

## 8. Soporte

- **Responsable:** `[Nombre del responsable]`
- **Correo:** `[correo@dominio.com]`
- **Horario:** `[días y horario]`
- **Repositorio:** `[URL del repositorio]`

Al reportar un problema, incluir: página, acción realizada, mensaje de error, navegador y captura de pantalla.

## Historial de cambios del manual

| Versión | Fecha | Cambio |
|---|---|---|
| 0.1.0 | 2026-11-13 | Estructura inicial del manual |
| 0.2.0 | 2026-11-13 | Rutas reales de registro/login, política de contraseña, rate limit y estado del portal/dashboard |
| 0.4.0 | 2026-11-15 | Sitio público completo: servicios, portafolio, nosotros, contacto, diagnóstico y bandeja de mensajes |
