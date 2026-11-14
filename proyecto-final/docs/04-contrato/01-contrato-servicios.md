# Contrato de prestación de servicios

> **Plantilla académica.** Este documento es un modelo para el proyecto de la materia y no constituye asesoría legal. Antes de usarlo con un cliente real, revísalo con un profesional del derecho.

**Contrato número:** `[PF-2026-001]`
**Lugar y fecha:** `[Ciudad]`, a `[día]` de `[mes]` de `[año]`.

## 1. Partes

**Prestador del servicio:**
- Nombre o razón social: `[Nombre de la empresa]`
- Representante legal: `[Nombre]`
- RFC: `[RFC]`
- Domicilio: `[Dirección]`
- Correo: `[correo@dominio.com]`
- Teléfono: `[Teléfono]`

**Cliente:**
- Nombre o razón social: `[Nombre del cliente]`
- Representante legal: `[Nombre]`
- RFC: `[RFC]`
- Domicilio: `[Dirección]`
- Correo: `[correo@dominio.com]`
- Teléfono: `[Teléfono]`

Ambas partes se reconocen con capacidad legal para celebrar el presente contrato.

## 2. Objeto

El prestador se obliga a diseñar, desarrollar e implantar una aplicación web para `[Nombre del cliente]`, compuesta por:

1. Sitio público informativo.
2. Portal de clientes con autenticación.
3. Panel de administración.
4. API y base de datos.
5. Documentación de usuario y técnica.

El proyecto se desarrolla en el marco de la materia **Desarrollo de Aplicaciones Web** y tiene fines académicos.

## 3. Alcances

### 3.1 Módulos incluidos

| Módulo | Funcionalidad |
|---|---|
| Sitio público | Inicio, servicios, portafolio, nosotros, contacto y cotización |
| Autenticación | Registro, inicio y cierre de sesión, roles `cliente` y `admin` |
| Portal del cliente | Perfil, proyectos propios y solicitudes propias |
| Administración | Usuarios, clientes, servicios, proyectos, tecnologías, solicitudes y mensajes |
| Reportes básicos | Dashboard con conteos y actividad reciente |
| Documentación | Manual de usuario, manual técnico y contrato |
| Despliegue | Entorno Docker con Nginx, PHP y SQL Server |

### 3.2 Entregables

1. Código fuente en repositorio y archivo ZIP.
2. Scripts de base de datos (`schema.sql`, `seed.sql` y migraciones).
3. Documentación en PDF generada con LaTeX.
4. Manual de usuario y manual técnico.
5. Contrato firmado.
6. Sesión de demostración y transferencia.

### 3.3 Criterios de aceptación

- El sitio funciona en móvil, tablet y escritorio.
- El registro, login y restricciones por rol operan correctamente.
- El cliente solo accede a su propia información.
- El administrador gestiona el contenido y las solicitudes.
- La base de datos se reconstruye desde los scripts.
- La documentación permite instalar y operar el sistema.

## 4. Limitaciones y exclusiones

No forman parte del alcance, salvo cotización adicional por escrito:

- Pasarela de pagos, facturación electrónica o nómina.
- Aplicaciones móviles nativas para iOS o Android.
- Chat en tiempo real, videollamadas o notificaciones push.
- Integraciones con CRM, ERP o sistemas de terceros.
- Recuperación de contraseña por correo y envío de correos transaccionales.
- Carga y edición avanzada de imágenes del portafolio.
- Contenido legal, fiscal o de marketing distinto de los textos proporcionados por el cliente.
- Hosting, dominio, certificados SSL y costos de servicios en la nube.
- Soporte y mantenimiento posterior al periodo de garantía.
- Auditoría de seguridad, pruebas de penetración o cumplimiento normativo.

## 5. Responsabilidades del cliente

- Proporcionar logotipo, textos, imágenes y datos de contacto.
- Designar un responsable para revisiones y aprobaciones.
- Responder a solicitudes de información en un plazo máximo de `[5]` días hábiles.
- Proporcionar accesos necesarios para el despliegue.
- Realizar las pruebas de aceptación en el plazo acordado.

## 6. Responsabilidades del prestador

- Ejecutar el proyecto conforme a los alcances.
- Mantener comunicación periódica y registrar avances.
- Entregar código, documentación y scripts.
- Realizar una sesión de demostración y transferencia.
- Corregir defectos atribuibles al desarrollo durante la garantía.

## 7. Cronograma

| Fase | Actividad | Entregable | Fecha |
|---|---|---|---|
| 1 | Requisitos y modelación | Documentos de modelación | `[fecha]` |
| 2 | Diseño de interfaz y base de datos | Prototipo y modelo ER | `[fecha]` |
| 3 | Desarrollo del backend y frontend | Versión funcional | `[fecha]` |
| 4 | Pruebas y correcciones | Versión candidata | `[fecha]` |
| 5 | Documentación y entrega | ZIP y PDFs | `[fecha]` |

Los plazos se ajustarán por escrito si el cliente no entrega insumos o aprobaciones a tiempo.

## 8. Contraprestación

- **Monto total:** `$[monto]` MXN.
- **Anticipo:** `[40]%` a la firma del contrato.
- **Segundo pago:** `[30]%` a la aprobación de la versión funcional.
- **Pago final:** `[30]%` contra entrega y aceptación.
- **Forma de pago:** transferencia bancaria a la cuenta `[datos bancarios]`.
- **Facturación:** `[sí/no]`, con datos fiscales del cliente.

Los pagos no incluyen impuestos, hosting ni servicios de terceros, salvo mención expresa.

## 9. Cambios de alcance

Cualquier funcionalidad no descrita en la sección 3 se considera un cambio de alcance. El prestador cotizará el impacto en tiempo y costo, y el trabajo iniciará cuando el cliente apruebe la cotización por escrito.

## 10. Propiedad intelectual

- El código y la documentación específicos del proyecto se transfieren al cliente una vez liquidado el pago total.
- El prestador conserva la titularidad de componentes, plantillas, librerías y conocimientos previos reutilizables.
- El cliente otorga al prestador permiso para mostrar el proyecto en su portafolio, salvo aviso escrito de confidencialidad.
- Las licencias de software de terceros se rigen por sus propios términos.

## 11. Confidencialidad

Ambas partes se obligan a proteger la información confidencial recibida y a usarla solo para el proyecto. La obligación permanece vigente durante `[2]` años después de terminar el contrato, salvo información pública o requerida por autoridad.

## 12. Garantía

El prestador corrige sin costo los defectos reproducibles atribuibles al desarrollo durante `[30]` días naturales posteriores a la aceptación. No cubre:

- Cambios solicitados por el cliente.
- Errores derivados de modificaciones de terceros.
- Fallas de hosting, red, navegador o servicios externos.
- Contenido proporcionado por el cliente.

## 13. Aceptación

El cliente revisa los entregables y comunica por escrito la aceptación o los defectos dentro de `[10]` días hábiles. Si no hay respuesta en ese plazo, se considera aceptado salvo pacto en contrario.

## 14. Terminación

Cualquiera de las partes puede terminar el contrato con aviso escrito de `[15]` días. El cliente paga el trabajo proporcional entregado. Los entregables se liberan contra el pago correspondiente.

## 15. Legislación y jurisdicción

Este contrato se rige por las leyes de los Estados Unidos Mexicanos. Para cualquier controversia, las partes se someten a los tribunales de `[Ciudad]`, renunciando a cualquier otro fuero.

## 16. Anexos

- Anexo A: Requisitos del sistema (`docs/01-modelacion/01-requisitos.md`).
- Anexo B: Mapa de sitio y navegación.
- Anexo C: Modelo de base de datos.
- Anexo D: Cronograma detallado.
- Anexo E: Criterios de aceptación.

## 17. Firmas

| Prestador | Cliente |
|---|---|
| Nombre: `[Nombre]` | Nombre: `[Nombre]` |
| Firma: ______________________ | Firma: ______________________ |
| Fecha: `[fecha]` | Fecha: `[fecha]` |

---

**Testigos**

| Testigo 1 | Testigo 2 |
|---|---|
| Nombre: `[Nombre]` | Nombre: `[Nombre]` |
| Firma: ______________________ | Firma: ______________________ |
