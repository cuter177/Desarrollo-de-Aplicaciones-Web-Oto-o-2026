# Guiones de uso

Escenarios narrativos que describen las interacciones principales con el sistema. Cada guion incluye actor, objetivo, precondiciones, flujo principal, alternativas y resultado.

## G-01 — El visitante conoce los servicios y solicita una cotización

- **Actor:** Visitante
- **Objetivo:** Conocer la oferta y pedir una propuesta económica.
- **Precondiciones:** El sitio está disponible y existen servicios activos.
- **Flujo principal:**
  1. El visitante entra a `/`.
  2. Revisa los servicios destacados.
  3. Entra a `/servicios` y abre el detalle de un servicio.
  4. Pulsa **Solicitar cotización** y llega a `/cotizacion`.
  5. Captura nombre, correo, empresa, teléfono, servicio y descripción del proyecto.
  6. Envía el formulario.
  7. El sistema valida los datos, guarda la solicitud y muestra un mensaje de confirmación.
- **Alternativas:**
  - Si faltan datos obligatorios, el formulario muestra los errores y conserva lo capturado.
  - Si el servicio ya no está activo, se muestra un aviso y se ofrece el catálogo actualizado.
- **Resultado:** Se crea una solicitud en estado `nueva`, visible en el panel administrativo.

## G-02 — El visitante envía un mensaje de contacto

- **Actor:** Visitante
- **Objetivo:** Hacer una consulta general.
- **Precondiciones:** Ninguna.
- **Flujo principal:**
  1. El visitante entra a `/contacto`.
  2. Captura nombre, correo, asunto y mensaje.
  3. Envía el formulario.
  4. El sistema valida, guarda el mensaje y confirma el envío.
- **Alternativas:**
  - Correo con formato inválido: se muestra el error junto al campo.
  - Demasiados envíos en poco tiempo: se aplica un límite y se pide esperar.
- **Resultado:** El mensaje aparece en `/admin/mensajes` con estado `nuevo`.

## G-03 — Un visitante se registra como cliente

- **Actor:** Visitante
- **Objetivo:** Crear una cuenta para acceder al portal.
- **Precondiciones:** El correo no está registrado.
- **Flujo principal:**
  1. El visitante entra a `/registro`.
  2. Captura nombre, correo, empresa y contraseña.
  3. Acepta los términos.
  4. Envía el formulario.
  5. El sistema valida, crea el usuario con rol `cliente`, crea su registro de cliente y muestra el login.
- **Alternativas:**
  - El correo ya existe: se muestra un mensaje y se ofrece iniciar sesión.
  - La contraseña no cumple la política: se indica el requisito incumplido.
- **Resultado:** El cliente puede iniciar sesión y acceder a `/portal`.

## G-04 — El cliente consulta el avance de sus proyectos

- **Actor:** Cliente
- **Objetivo:** Revisar el estado del proyecto contratado.
- **Precondiciones:** Sesión iniciada y al menos un proyecto asociado.
- **Flujo principal:**
  1. El cliente inicia sesión en `/login`.
  2. El sistema crea la sesión y redirige a `/portal`.
  3. El cliente entra a **Mis proyectos**.
  4. El sistema solicita `GET /api/v1/mi/proyectos`.
  5. El cliente abre un proyecto y ve estado, fechas, tecnologías y enlaces.
- **Alternativas:**
  - Si intenta abrir el proyecto de otro cliente, el backend responde `403` y el front muestra acceso denegado.
  - Si no hay proyectos, se muestra un estado vacío con enlace a contacto.
- **Resultado:** El cliente conoce el avance sin exponer información de terceros.

## G-05 — El cliente crea una nueva solicitud

- **Actor:** Cliente
- **Objetivo:** Pedir una ampliación o un nuevo servicio.
- **Precondiciones:** Sesión de cliente.
- **Flujo principal:**
  1. El cliente entra a `/portal/solicitudes`.
  2. Pulsa **Nueva solicitud**.
  3. Captura servicio, descripción y presupuesto estimado.
  4. Envía el formulario.
  5. El sistema valida, asocia la solicitud a su cliente y confirma.
- **Alternativas:**
  - Si el servicio requiere información adicional, se muestran los campos solicitados.
  - Si la sesión expira, se redirige a `/login` conservando el borrador local.
- **Resultado:** La solicitud queda en estado `nueva` y visible en el panel admin.

## G-06 — El administrador atiende una solicitud

- **Actor:** Administrador
- **Objetivo:** Revisar y responder una solicitud de cotización.
- **Precondiciones:** Sesión de administrador y solicitudes pendientes.
- **Flujo principal:**
  1. El administrador entra a `/admin/dashboard` y ve el contador de solicitudes nuevas.
  2. Abre `/admin/solicitudes`.
  3. Filtra por estado `nueva` y abre una solicitud.
  4. Revisa los datos del prospecto.
  5. Cambia el estado a `en_revision` y registra una nota interna.
  6. Al terminar, cambia el estado a `cotizada`.
- **Alternativas:**
  - Si la solicitud está incompleta, la marca como `rechazada` con motivo.
  - Si detecta que corresponde a un cliente existente, la vincula a ese cliente.
- **Resultado:** La solicitud refleja su nuevo estado y conserva el historial.

## G-07 — El administrador publica un caso de éxito

- **Actor:** Administrador
- **Objetivo:** Mostrar un proyecto terminado en el portafolio público.
- **Precondiciones:** Proyecto registrado y con información completa.
- **Flujo principal:**
  1. El administrador entra a `/admin/proyectos`.
  2. Abre el proyecto y revisa nombre, descripción, tecnologías e imágenes.
  3. Activa **Publicado** y guarda.
  4. El sistema actualiza el proyecto y lo incluye en `GET /api/v1/portafolio`.
- **Alternativas:**
  - Si faltan datos obligatorios, no se permite publicar y se indica qué falta.
  - Si se despublica, el proyecto deja de aparecer en el sitio público.
- **Resultado:** El caso aparece en `/portafolio` y en su ruta `/portafolio/{slug}`.

## G-08 — Un cliente intenta entrar al panel administrativo

- **Actor:** Cliente autenticado
- **Objetivo:** Acceder a funciones administrativas sin autorización.
- **Precondiciones:** Sesión de cliente.
- **Flujo principal:**
  1. El cliente escribe `/admin/dashboard` en el navegador.
  2. El front consulta `GET /api/v1/auth/me` y detecta rol `cliente`.
  3. Redirige a `/portal` o muestra acceso denegado.
  4. Si intenta llamar directamente a `GET /api/v1/admin/usuarios`, el backend responde `403`.
- **Resultado:** La restricción se aplica en el servidor, no solo en la interfaz.

## Matriz de permisos

| Acción | Visitante | Cliente | Admin |
|---|---|---|---|
| Ver sitio público | ✅ | ✅ | ✅ |
| Enviar contacto / cotización | ✅ | ✅ | ✅ |
| Registrarse / iniciar sesión | ✅ | — | — |
| Ver perfil propio | ❌ | ✅ | ✅ |
| Ver proyectos propios | ❌ | ✅ | ✅ |
| Crear solicitud propia | ❌ | ✅ | ✅ |
| Ver todos los usuarios | ❌ | ❌ | ✅ |
| Gestionar contenido | ❌ | ❌ | ✅ |
| Atender solicitudes y mensajes | ❌ | ❌ | ✅ |
| Publicar portafolio | ❌ | ❌ | ✅ |
