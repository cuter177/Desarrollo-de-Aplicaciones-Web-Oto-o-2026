# Diagrama de navegación

Flujos de navegación y restricciones de acceso. Las flechas representan transiciones visibles para el usuario; la autorización real se valida en el backend.

> En la implementación estática actual las rutas equivalen a archivos `.html` y los detalles usan `?slug=`: `/pages/servicios.html`, `/pages/servicio-detalle.html?slug=...`, `/pages/portafolio.html` y `/pages/caso-detalle.html?slug=...`. Las rutas limpias quedan como mejora futura.

## 1. Navegación pública

```mermaid
flowchart LR
    Inicio["/"] --> Servicios["/servicios"]
    Inicio --> Portafolio["/portafolio"]
    Inicio --> Nosotros["/nosotros"]
    Inicio --> Contacto["/contacto"]
    Inicio --> Cotizacion["/cotizacion"]
    Inicio --> Login["/login"]
    Inicio --> Registro["/registro"]

    Servicios --> DetalleServicio["/servicios/{slug}"]
    DetalleServicio --> Cotizacion
    Portafolio --> DetalleCaso["/portafolio/{slug}"]
    DetalleCaso --> Contacto
    Registro --> Login
```

## 2. Acceso y redirección por rol

```mermaid
flowchart TD
    Login["/login"] --> Validar{"¿Credenciales válidas?"}
    Validar -- No --> Error["Mensaje de error"]
    Error --> Login
    Validar -- Sí --> Rol{"¿Rol?"}
    Rol -- cliente --> Portal["/portal"]
    Rol -- admin --> Admin["/admin/dashboard"]

    Portal --> Proyectos["/portal/proyectos"]
    Proyectos --> ProyectoDetalle["/portal/proyectos/{id}"]
    Portal --> Solicitudes["/portal/solicitudes"]
    Solicitudes --> SolicitudDetalle["/portal/solicitudes/{id}"]
    Portal --> Perfil["/portal/perfil"]

    Admin --> Usuarios["/admin/usuarios"]
    Admin --> Clientes["/admin/clientes"]
    Admin --> AdminServicios["/admin/servicios"]
    Admin --> AdminProyectos["/admin/proyectos"]
    Admin --> Tecnologias["/admin/tecnologias"]
    Admin --> AdminSolicitudes["/admin/solicitudes"]
    Admin --> Mensajes["/admin/mensajes"]
```

## 3. Restricciones y salidas

```mermaid
flowchart TD
    Ruta["Solicitud de ruta protegida"] --> Sesion{"¿Hay sesión?"}
    Sesion -- No --> Login["Redirigir a /login"]
    Sesion -- Sí --> Rol{"¿Rol autorizado?"}
    Rol -- No --> Denegado["403 / acceso denegado"]
    Rol -- Sí --> Vista["Mostrar página"]
    Denegado --> Portal["Volver al portal"]
    Login --> Despues["Tras autenticar, volver a la ruta solicitada"]
```

## 4. Matriz de rutas

| Ruta | Visitante | Cliente | Admin | Comportamiento sin permiso |
|---|---|---|---|---|
| `/`, `/servicios`, `/portafolio`, `/nosotros`, `/contacto`, `/cotizacion` | ✅ | ✅ | ✅ | — |
| `/login`, `/registro` | ✅ | Redirige al portal | Redirige al panel | — |
| `/portal`, `/portal/proyectos`, `/portal/solicitudes`, `/portal/perfil` | ❌ | ✅ | ✅ | Redirige a `/login` |
| `/admin`, `/admin/*` | ❌ | ❌ | ✅ | `403` y enlace al portal |
| Ruta inexistente | ❌ | ❌ | ❌ | Página `404` |

## 5. Reglas de navegación

- El encabezado cambia según la sesión: visitante, cliente o admin.
- Tras iniciar sesión, se redirige al destino solicitado o al inicio del rol.
- Al cerrar sesión se limpia el estado local y se vuelve a `/`.
- Las rutas del portal incluyen el identificador del recurso, pero el backend verifica la propiedad.
- Los formularios conservan los datos capturados cuando hay errores de validación.
- Las acciones destructivas piden confirmación y muestran el resultado.
