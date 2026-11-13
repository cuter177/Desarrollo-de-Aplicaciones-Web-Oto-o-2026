# Manual de usuario

**Sistema:** `[Nombre de la empresa]` — sitio web, portal de clientes y panel administrativo
**Versión:** 0.1.0 · **Última actualización:** 2026-11-13

> Este manual está en construcción. La estructura y el contenido inicial ya están definidos; las capturas y los pasos definitivos se agregarán cuando las pantallas estén implementadas.

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

1. Abrir `http://localhost:8080/registro`.
2. Capturar nombre completo, correo electrónico, empresa y contraseña.
3. Leer y aceptar los términos.
4. Pulsar **Crear cuenta**.
5. Iniciar sesión con el correo y la contraseña registrados.

**Política de contraseña:** mínimo 8 caracteres, una mayúscula, una minúscula y un número.

### 3.2 Iniciar sesión

1. Abrir `http://localhost:8080/login`.
2. Capturar correo y contraseña.
3. Pulsar **Entrar**.
4. El sistema redirige automáticamente según el rol:
   - Cliente → `/portal`
   - Administrador → `/admin/dashboard`

### 3.3 Cerrar sesión

Pulsar **Cerrar sesión** en el encabezado. El sistema elimina la sesión y vuelve al inicio.

> **Captura pendiente:** pantalla de inicio de sesión.

## 4. Sitio público

### 4.1 Inicio

Presenta la propuesta de valor, servicios destacados y casos de éxito. Desde aquí se accede a las demás secciones.

### 4.2 Servicios

1. Abrir **Servicios** en el menú.
2. Seleccionar un servicio para ver su descripción, beneficios y tecnologías.
3. Pulsar **Solicitar cotización** para iniciar una solicitud.

### 4.3 Portafolio

Muestra proyectos publicados por el administrador. Cada caso incluye reto, solución, tecnologías y resultados.

### 4.4 Contacto

1. Abrir **Contacto**.
2. Capturar nombre, correo, asunto y mensaje.
3. Pulsar **Enviar mensaje**.
4. Verificar el mensaje de confirmación.

### 4.5 Solicitud de cotización

1. Abrir **Cotización**.
2. Capturar datos de contacto, servicio de interés, descripción y presupuesto estimado.
3. Pulsar **Enviar solicitud**.
4. El equipo administrativo revisará la solicitud y dará seguimiento.

**Errores frecuentes:**

| Mensaje | Causa | Solución |
|---|---|---|
| “Correo inválido” | Formato incorrecto | Revisar el correo |
| “Campo obligatorio” | Falta información | Completar el campo marcado |
| “Demasiados intentos” | Límite de envíos | Esperar unos minutos |

> **Capturas pendientes:** inicio, servicios, portafolio, contacto y cotización.

## 5. Portal del cliente

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

### 6.1 Dashboard

Muestra conteos de usuarios, proyectos, solicitudes nuevas y mensajes sin atender, además de la actividad reciente.

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
- Asignar cliente, estado, fechas y tecnologías.
- Publicar o despublicar en el portafolio.

### 6.6 Tecnologías

Catálogo usado para clasificar servicios y proyectos.

### 6.7 Solicitudes

1. Abrir **Solicitudes**.
2. Filtrar por estado.
3. Abrir una solicitud y revisar los datos.
4. Cambiar el estado según el avance.
5. Opcional: convertir la solicitud en cliente o proyecto.

### 6.8 Mensajes

1. Abrir **Mensajes**.
2. Revisar el detalle.
3. Marcar como atendido o eliminar si es spam.

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
