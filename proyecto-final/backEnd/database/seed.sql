/*
 * Datos iniciales y de demostración — DevStudioDB
 * SQL Server 2022
 *
 * Se ejecuta después de schema.sql. Es idempotente: se puede ejecutar
 * varias veces sin duplicar registros.
 *
 * Incluye catálogos, cuentas y datos de ejemplo para probar el sitio,
 * el portal del cliente y el panel de administración.
 *
 * Cuentas de prueba (contraseñas solo para desarrollo):
 *   admin@devstudio.local       / Admin#2026
 *   cliente@devstudio.local     / Cliente#2026   (Comercial Andina)
 *   carlos.ruiz@innovatech.mx   / Cliente#2026   (InnovaTech Solutions)
 *   ana.torres@nexoclinica.com  / Cliente#2026   (Nexo Clínica)
 *
 * Los hashes se generaron con password_hash(..., PASSWORD_DEFAULT) de PHP 8.3.
 * Cambiar todas las contraseñas antes de cualquier despliegue real.
 */

USE [DevStudioDB];
GO

SET NOCOUNT ON;
GO

/* ==========================================================================
   1. Roles
   ========================================================================== */

IF NOT EXISTS (SELECT 1 FROM dbo.Roles WHERE nombre = N'admin')
BEGIN
    INSERT INTO dbo.Roles (nombre, descripcion)
    VALUES (N'admin', N'Administrador del sistema');
END
GO

IF NOT EXISTS (SELECT 1 FROM dbo.Roles WHERE nombre = N'cliente')
BEGIN
    INSERT INTO dbo.Roles (nombre, descripcion)
    VALUES (N'cliente', N'Cliente con acceso al portal');
END
GO

/* ==========================================================================
   2. Servicios
   ========================================================================== */

IF NOT EXISTS (SELECT 1 FROM dbo.Servicios WHERE slug = N'desarrollo-web')
BEGIN
    INSERT INTO dbo.Servicios (slug, nombre, descripcion, icono, orden)
    VALUES (
        N'desarrollo-web',
        N'Desarrollo web',
        N'Sitios corporativos, portales y aplicaciones web a la medida, con diseño responsive y enfoque en rendimiento.',
        N'code',
        1
    );
END
GO

IF NOT EXISTS (SELECT 1 FROM dbo.Servicios WHERE slug = N'dashboards')
BEGIN
    INSERT INTO dbo.Servicios (slug, nombre, descripcion, icono, orden)
    VALUES (
        N'dashboards',
        N'Dashboards y análisis',
        N'Tableros de control para visualizar indicadores, automatizar reportes y apoyar la toma de decisiones.',
        N'chart',
        2
    );
END
GO

IF NOT EXISTS (SELECT 1 FROM dbo.Servicios WHERE slug = N'apps-moviles')
BEGIN
    INSERT INTO dbo.Servicios (slug, nombre, descripcion, icono, orden)
    VALUES (
        N'apps-moviles',
        N'Aplicaciones móviles',
        N'Apps para iOS y Android con experiencias nativas o multiplataforma, integradas a tus sistemas.',
        N'mobile',
        3
    );
END
GO

IF NOT EXISTS (SELECT 1 FROM dbo.Servicios WHERE slug = N'consultoria')
BEGIN
    INSERT INTO dbo.Servicios (slug, nombre, descripcion, icono, orden)
    VALUES (
        N'consultoria',
        N'Consultoría tecnológica',
        N'Acompañamiento en arquitectura, buenas prácticas, seguridad y evolución de productos digitales.',
        N'compass',
        4
    );
END
GO

/* Beneficios de los servicios (solo si aún no tienen contenido). */

UPDATE dbo.Servicios
SET beneficios = N'Diseño responsive y Mobile First
Carga rápida y buenas prácticas de accesibilidad
Formularios conectados a tu operación
Base preparada para crecer por etapas'
WHERE slug = N'desarrollo-web'
  AND (beneficios IS NULL OR LTRIM(RTRIM(beneficios)) = N'');
GO

UPDATE dbo.Servicios
SET beneficios = N'Indicadores de ventas, operación y crecimiento
Reportes automáticos y exportación de datos
Filtros por sucursal, periodo y responsable
Acceso seguro desde cualquier dispositivo'
WHERE slug = N'dashboards'
  AND (beneficios IS NULL OR LTRIM(RTRIM(beneficios)) = N'');
GO

UPDATE dbo.Servicios
SET beneficios = N'Experiencia para iOS y Android
Notificaciones y recordatorios
Integración con tus sistemas actuales
Publicación y acompañamiento posterior'
WHERE slug = N'apps-moviles'
  AND (beneficios IS NULL OR LTRIM(RTRIM(beneficios)) = N'');
GO

UPDATE dbo.Servicios
SET beneficios = N'Diagnóstico de procesos y oportunidades
Priorización por impacto y esfuerzo
Acompañamiento técnico para tu equipo
Plan de modernización por etapas'
WHERE slug = N'consultoria'
  AND (beneficios IS NULL OR LTRIM(RTRIM(beneficios)) = N'');
GO

/* ==========================================================================
   3. Tecnologías
   ========================================================================== */

IF NOT EXISTS (SELECT 1 FROM dbo.Tecnologias WHERE nombre = N'HTML')
    INSERT INTO dbo.Tecnologias (nombre, categoria) VALUES (N'HTML', N'Frontend');
GO
IF NOT EXISTS (SELECT 1 FROM dbo.Tecnologias WHERE nombre = N'CSS')
    INSERT INTO dbo.Tecnologias (nombre, categoria) VALUES (N'CSS', N'Frontend');
GO
IF NOT EXISTS (SELECT 1 FROM dbo.Tecnologias WHERE nombre = N'JavaScript')
    INSERT INTO dbo.Tecnologias (nombre, categoria) VALUES (N'JavaScript', N'Frontend');
GO
IF NOT EXISTS (SELECT 1 FROM dbo.Tecnologias WHERE nombre = N'PHP')
    INSERT INTO dbo.Tecnologias (nombre, categoria) VALUES (N'PHP', N'Backend');
GO
IF NOT EXISTS (SELECT 1 FROM dbo.Tecnologias WHERE nombre = N'SQL Server')
    INSERT INTO dbo.Tecnologias (nombre, categoria) VALUES (N'SQL Server', N'Base de datos');
GO
IF NOT EXISTS (SELECT 1 FROM dbo.Tecnologias WHERE nombre = N'Docker')
    INSERT INTO dbo.Tecnologias (nombre, categoria) VALUES (N'Docker', N'Infraestructura');
GO
IF NOT EXISTS (SELECT 1 FROM dbo.Tecnologias WHERE nombre = N'Nginx')
    INSERT INTO dbo.Tecnologias (nombre, categoria) VALUES (N'Nginx', N'Infraestructura');
GO
IF NOT EXISTS (SELECT 1 FROM dbo.Tecnologias WHERE nombre = N'Flutter')
    INSERT INTO dbo.Tecnologias (nombre, categoria) VALUES (N'Flutter', N'Móvil');
GO
IF NOT EXISTS (SELECT 1 FROM dbo.Tecnologias WHERE nombre = N'React Native')
    INSERT INTO dbo.Tecnologias (nombre, categoria) VALUES (N'React Native', N'Móvil');
GO

/* ==========================================================================
   4. Planes
   ========================================================================== */

IF NOT EXISTS (SELECT 1 FROM dbo.Planes WHERE slug = N'esencial')
BEGIN
    INSERT INTO dbo.Planes (
        slug, nombre, subtitulo, descripcion, precio_desde, precio_hasta,
        caracteristicas, dirigido_a, destacado, activo, orden
    )
    VALUES (
        N'esencial',
        N'Plan Esencial',
        N'Presencia digital que convierte',
        N'Sitio web profesional, responsive y optimizado para captar clientes, con formularios y analítica básica.',
        18000.00,
        35000.00,
        N'["Sitio web responsive","Hasta 5 secciones","Formulario de contacto","SEO básico","Analítica de visitas","Soporte por 30 días"]',
        N'Negocios que necesitan presencia web profesional',
        0,
        1,
        1
    );
END
GO

IF NOT EXISTS (SELECT 1 FROM dbo.Planes WHERE slug = N'crecimiento')
BEGIN
    INSERT INTO dbo.Planes (
        slug, nombre, subtitulo, descripcion, precio_desde, precio_hasta,
        caracteristicas, dirigido_a, destacado, activo, orden
    )
    VALUES (
        N'crecimiento',
        N'Plan Crecimiento',
        N'Automatiza y mide tu operación',
        N'Sitio web, dashboard de indicadores y automatizaciones para reducir trabajo manual y tomar mejores decisiones.',
        45000.00,
        90000.00,
        N'["Todo el Plan Esencial","Dashboard de indicadores","Integración con bases de datos","Automatización de reportes","Capacitación al equipo","Soporte por 90 días"]',
        N'PyMEs con procesos manuales y metas de crecimiento',
        1,
        1,
        2
    );
END
GO

IF NOT EXISTS (SELECT 1 FROM dbo.Planes WHERE slug = N'transformacion')
BEGIN
    INSERT INTO dbo.Planes (
        slug, nombre, subtitulo, descripcion, precio_desde, precio_hasta,
        caracteristicas, dirigido_a, destacado, activo, orden
    )
    VALUES (
        N'transformacion',
        N'Plan Transformación',
        N'Software a la medida para escalar',
        N'Aplicación web o móvil a la medida, integraciones, consultoría y acompañamiento para transformar la operación.',
        120000.00,
        250000.00,
        N'["Todo el Plan Crecimiento","Aplicación web o móvil","Integraciones con sistemas existentes","Consultoría de arquitectura","Soporte prioritario","Acompañamiento por 6 meses"]',
        N'Empresas que buscan digitalizar procesos críticos',
        0,
        1,
        3
    );
END
GO

/* ==========================================================================
   5. Usuarios de prueba
   ========================================================================== */

IF NOT EXISTS (SELECT 1 FROM dbo.Usuarios WHERE email = N'admin@devstudio.local')
BEGIN
    INSERT INTO dbo.Usuarios (nombre, email, password_hash, rol_id, activo)
    SELECT
        N'Administrador DevStudio',
        N'admin@devstudio.local',
        N'$2y$10$mqDtp1R2F0KTnGAIJ0OqgO3GlKVZInROZ97gQBpgLZwGH6dUQaDs2',
        r.id,
        1
    FROM dbo.Roles r
    WHERE r.nombre = N'admin';
END
GO

IF NOT EXISTS (SELECT 1 FROM dbo.Usuarios WHERE email = N'cliente@devstudio.local')
BEGIN
    INSERT INTO dbo.Usuarios (nombre, email, password_hash, rol_id, activo)
    SELECT
        N'Laura Méndez',
        N'cliente@devstudio.local',
        N'$2y$10$Vv8zLaYgWgQ4YnjOx8qk5.Ss2kt2Y00phlfh.skgNK7ho3H7Y6ZAa',
        r.id,
        1
    FROM dbo.Roles r
    WHERE r.nombre = N'cliente';
END
GO

IF NOT EXISTS (SELECT 1 FROM dbo.Usuarios WHERE email = N'carlos.ruiz@innovatech.mx')
BEGIN
    INSERT INTO dbo.Usuarios (nombre, email, password_hash, rol_id, activo)
    SELECT
        N'Carlos Ruiz',
        N'carlos.ruiz@innovatech.mx',
        N'$2y$10$Vv8zLaYgWgQ4YnjOx8qk5.Ss2kt2Y00phlfh.skgNK7ho3H7Y6ZAa',
        r.id,
        1
    FROM dbo.Roles r
    WHERE r.nombre = N'cliente';
END
GO

IF NOT EXISTS (SELECT 1 FROM dbo.Usuarios WHERE email = N'ana.torres@nexoclinica.com')
BEGIN
    INSERT INTO dbo.Usuarios (nombre, email, password_hash, rol_id, activo)
    SELECT
        N'Ana Torres',
        N'ana.torres@nexoclinica.com',
        N'$2y$10$Vv8zLaYgWgQ4YnjOx8qk5.Ss2kt2Y00phlfh.skgNK7ho3H7Y6ZAa',
        r.id,
        1
    FROM dbo.Roles r
    WHERE r.nombre = N'cliente';
END
GO

/* ==========================================================================
   6. Clientes
   ========================================================================== */

IF NOT EXISTS (
    SELECT 1
    FROM dbo.Clientes c
    INNER JOIN dbo.Usuarios u ON u.id = c.usuario_id
    WHERE u.email = N'cliente@devstudio.local'
)
BEGIN
    INSERT INTO dbo.Clientes (usuario_id, empresa, telefono, sitio_web, industria)
    SELECT
        u.id,
        N'Comercial Andina S.A. de C.V.',
        N'+52 55 1234 5678',
        N'https://comercialandina.mx',
        N'Comercio minorista'
    FROM dbo.Usuarios u
    WHERE u.email = N'cliente@devstudio.local';
END
GO

IF NOT EXISTS (
    SELECT 1
    FROM dbo.Clientes c
    INNER JOIN dbo.Usuarios u ON u.id = c.usuario_id
    WHERE u.email = N'carlos.ruiz@innovatech.mx'
)
BEGIN
    INSERT INTO dbo.Clientes (usuario_id, empresa, telefono, sitio_web, industria)
    SELECT
        u.id,
        N'InnovaTech Solutions',
        N'+52 81 8765 4321',
        N'https://innovatech.mx',
        N'Tecnología'
    FROM dbo.Usuarios u
    WHERE u.email = N'carlos.ruiz@innovatech.mx';
END
GO

IF NOT EXISTS (
    SELECT 1
    FROM dbo.Clientes c
    INNER JOIN dbo.Usuarios u ON u.id = c.usuario_id
    WHERE u.email = N'ana.torres@nexoclinica.com'
)
BEGIN
    INSERT INTO dbo.Clientes (usuario_id, empresa, telefono, sitio_web, industria)
    SELECT
        u.id,
        N'Nexo Clínica',
        N'+52 33 2468 1357',
        N'https://nexoclinica.com',
        N'Salud'
    FROM dbo.Usuarios u
    WHERE u.email = N'ana.torres@nexoclinica.com';
END
GO

/* ==========================================================================
   7. Proyectos
   ========================================================================== */

IF NOT EXISTS (SELECT 1 FROM dbo.Proyectos WHERE slug = N'portal-comercial-andina')
BEGIN
    INSERT INTO dbo.Proyectos (
        cliente_id, nombre, slug, descripcion, estado,
        fecha_inicio, fecha_fin, url_demo, publicado, destacado
    )
    SELECT
        c.id,
        N'Portal de clientes Comercial Andina',
        N'portal-comercial-andina',
        N'Portal web para consultar pedidos, facturas y estado de entregas, con autenticación y tablero de indicadores.',
        N'en_progreso',
        '20260803',
        NULL,
        NULL,
        1,
        1
    FROM dbo.Clientes c
    INNER JOIN dbo.Usuarios u ON u.id = c.usuario_id
    WHERE u.email = N'cliente@devstudio.local';
END
GO

IF NOT EXISTS (SELECT 1 FROM dbo.Proyectos WHERE slug = N'dashboard-innovatech')
BEGIN
    INSERT INTO dbo.Proyectos (
        cliente_id, nombre, slug, descripcion, estado,
        fecha_inicio, fecha_fin, url_demo, publicado, destacado
    )
    SELECT
        c.id,
        N'Dashboard comercial InnovaTech',
        N'dashboard-innovatech',
        N'Tablero de ventas y metas con filtros por sucursal, exportación a CSV y actualización diaria.',
        N'entregado',
        '20260406',
        '20260619',
        N'https://demo.devstudio.local/innovatech',
        1,
        1
    FROM dbo.Clientes c
    INNER JOIN dbo.Usuarios u ON u.id = c.usuario_id
    WHERE u.email = N'carlos.ruiz@innovatech.mx';
END
GO

IF NOT EXISTS (SELECT 1 FROM dbo.Proyectos WHERE slug = N'app-movil-nexo-clinica')
BEGIN
    INSERT INTO dbo.Proyectos (
        cliente_id, nombre, slug, descripcion, estado,
        fecha_inicio, fecha_fin, url_demo, publicado, destacado
    )
    SELECT
        c.id,
        N'App móvil Nexo Clínica',
        N'app-movil-nexo-clinica',
        N'Aplicación móvil para agendar citas, recibir recordatorios y consultar resultados de laboratorio.',
        N'en_revision',
        '20260914',
        NULL,
        NULL,
        0,
        0
    FROM dbo.Clientes c
    INNER JOIN dbo.Usuarios u ON u.id = c.usuario_id
    WHERE u.email = N'ana.torres@nexoclinica.com';
END
GO

IF NOT EXISTS (SELECT 1 FROM dbo.Proyectos WHERE slug = N'tienda-linea-andina')
BEGIN
    INSERT INTO dbo.Proyectos (
        cliente_id, nombre, slug, descripcion, estado,
        fecha_inicio, fecha_fin, url_demo, publicado, destacado
    )
    SELECT
        c.id,
        N'Tienda en línea Comercial Andina',
        N'tienda-linea-andina',
        N'Catálogo de productos, carrito de compras y pago en línea con integración a pasarela.',
        N'planificado',
        '20261102',
        NULL,
        NULL,
        0,
        0
    FROM dbo.Clientes c
    INNER JOIN dbo.Usuarios u ON u.id = c.usuario_id
    WHERE u.email = N'cliente@devstudio.local';
END
GO

IF NOT EXISTS (SELECT 1 FROM dbo.Proyectos WHERE slug = N'sistema-citas-nexo')
BEGIN
    INSERT INTO dbo.Proyectos (
        cliente_id, nombre, slug, descripcion, estado,
        fecha_inicio, fecha_fin, url_demo, publicado, destacado
    )
    SELECT
        c.id,
        N'Sistema de citas Nexo Clínica',
        N'sistema-citas-nexo',
        N'Plataforma interna para administrar agenda médica, expedientes y notificaciones.',
        N'en_progreso',
        '20260701',
        NULL,
        NULL,
        1,
        0
    FROM dbo.Clientes c
    INNER JOIN dbo.Usuarios u ON u.id = c.usuario_id
    WHERE u.email = N'ana.torres@nexoclinica.com';
END
GO

IF NOT EXISTS (SELECT 1 FROM dbo.Proyectos WHERE slug = N'landing-innovatech')
BEGIN
    INSERT INTO dbo.Proyectos (
        cliente_id, nombre, slug, descripcion, estado,
        fecha_inicio, fecha_fin, url_demo, publicado, destacado
    )
    SELECT
        c.id,
        N'Landing page InnovaTech',
        N'landing-innovatech',
        N'Sitio de una página para el lanzamiento de la nueva línea de servicios en la nube.',
        N'entregado',
        '20260210',
        '20260302',
        N'https://demo.devstudio.local/innovatech-landing',
        1,
        0
    FROM dbo.Clientes c
    INNER JOIN dbo.Usuarios u ON u.id = c.usuario_id
    WHERE u.email = N'carlos.ruiz@innovatech.mx';
END
GO

/* Contenido de caso de éxito (solo si aún no tiene detalle). */

UPDATE dbo.Proyectos
SET
    reto = N'El equipo comercial y los clientes dependían de correos y hojas de cálculo para consultar pedidos, facturas y fechas de entrega.',
    solucion = N'Portal autenticado con consulta de pedidos, descarga de facturas, seguimiento de entregas y un tablero de indicadores para el equipo interno.',
    resultados = N'Los clientes consultan su información sin intermediarios y el equipo comercial redujo el seguimiento manual de cada pedido.',
    mostrar_cliente = 1
WHERE slug = N'portal-comercial-andina'
  AND reto IS NULL;
GO

UPDATE dbo.Proyectos
SET
    reto = N'Las ventas por sucursal se consolidaban manualmente cada semana y no había una visión común de metas.',
    solucion = N'Tablero con indicadores de venta, comparación contra meta, filtros por sucursal y exportación a CSV, alimentado por un proceso diario.',
    resultados = N'El reporte semanal pasó a consultarse en tiempo real y las decisiones de surtido se tomaron con datos comparables.',
    mostrar_cliente = 1
WHERE slug = N'dashboard-innovatech'
  AND reto IS NULL;
GO

UPDATE dbo.Proyectos
SET
    reto = N'Los pacientes agendaban citas por teléfono y los recordatorios dependían del personal de recepción.',
    solucion = N'Aplicación móvil para agendar citas, recibir recordatorios y consultar resultados, con integración al sistema interno de la clínica.',
    resultados = N'Menos llamadas para agendar y recordatorios automáticos para los pacientes.',
    mostrar_cliente = 0
WHERE slug = N'app-movil-nexo-clinica'
  AND reto IS NULL;
GO

UPDATE dbo.Proyectos
SET
    reto = N'El catálogo vivía en redes sociales y los pedidos se cerraban por mensaje, sin control de inventario.',
    solucion = N'Tienda en línea con catálogo, carrito, pago con pasarela e integración al inventario existente.',
    resultados = N'El proceso de compra quedó centralizado y el inventario se actualiza con cada pedido.',
    mostrar_cliente = 1
WHERE slug = N'tienda-linea-andina'
  AND reto IS NULL;
GO

UPDATE dbo.Proyectos
SET
    reto = N'La agenda médica se administraba en hojas de cálculo separadas por consultorio.',
    solucion = N'Plataforma interna con agenda, expedientes y notificaciones para el personal de la clínica.',
    resultados = N'Una sola agenda para todos los consultorios y menos errores de duplicidad.',
    mostrar_cliente = 0
WHERE slug = N'sistema-citas-nexo'
  AND reto IS NULL;
GO

UPDATE dbo.Proyectos
SET
    reto = N'El lanzamiento de la nueva línea de servicios en la nube necesitaba una página clara y medible.',
    solucion = N'Landing page con propuesta de valor, secciones de servicio y formulario de contacto conectado al equipo comercial.',
    resultados = N'Campaña de lanzamiento con una ruta directa de contacto y medición de conversiones.',
    mostrar_cliente = 1
WHERE slug = N'landing-innovatech'
  AND reto IS NULL;
GO

/* ==========================================================================
   8. Tecnologías por proyecto
   ========================================================================== */

INSERT INTO dbo.ProyectoTecnologia (proyecto_id, tecnologia_id)
SELECT p.id, t.id
FROM dbo.Proyectos p
INNER JOIN dbo.Tecnologias t
    ON t.nombre IN (N'HTML', N'CSS', N'JavaScript', N'PHP', N'SQL Server', N'Docker', N'Nginx')
WHERE p.slug = N'portal-comercial-andina'
  AND NOT EXISTS (
      SELECT 1 FROM dbo.ProyectoTecnologia pt
      WHERE pt.proyecto_id = p.id AND pt.tecnologia_id = t.id
  );
GO

INSERT INTO dbo.ProyectoTecnologia (proyecto_id, tecnologia_id)
SELECT p.id, t.id
FROM dbo.Proyectos p
INNER JOIN dbo.Tecnologias t
    ON t.nombre IN (N'HTML', N'CSS', N'JavaScript', N'PHP', N'SQL Server')
WHERE p.slug = N'dashboard-innovatech'
  AND NOT EXISTS (
      SELECT 1 FROM dbo.ProyectoTecnologia pt
      WHERE pt.proyecto_id = p.id AND pt.tecnologia_id = t.id
  );
GO

INSERT INTO dbo.ProyectoTecnologia (proyecto_id, tecnologia_id)
SELECT p.id, t.id
FROM dbo.Proyectos p
INNER JOIN dbo.Tecnologias t
    ON t.nombre IN (N'Flutter', N'PHP', N'SQL Server')
WHERE p.slug = N'app-movil-nexo-clinica'
  AND NOT EXISTS (
      SELECT 1 FROM dbo.ProyectoTecnologia pt
      WHERE pt.proyecto_id = p.id AND pt.tecnologia_id = t.id
  );
GO

INSERT INTO dbo.ProyectoTecnologia (proyecto_id, tecnologia_id)
SELECT p.id, t.id
FROM dbo.Proyectos p
INNER JOIN dbo.Tecnologias t
    ON t.nombre IN (N'HTML', N'CSS', N'JavaScript', N'PHP')
WHERE p.slug = N'tienda-linea-andina'
  AND NOT EXISTS (
      SELECT 1 FROM dbo.ProyectoTecnologia pt
      WHERE pt.proyecto_id = p.id AND pt.tecnologia_id = t.id
  );
GO

INSERT INTO dbo.ProyectoTecnologia (proyecto_id, tecnologia_id)
SELECT p.id, t.id
FROM dbo.Proyectos p
INNER JOIN dbo.Tecnologias t
    ON t.nombre IN (N'HTML', N'CSS', N'JavaScript', N'PHP', N'SQL Server', N'Docker')
WHERE p.slug = N'sistema-citas-nexo'
  AND NOT EXISTS (
      SELECT 1 FROM dbo.ProyectoTecnologia pt
      WHERE pt.proyecto_id = p.id AND pt.tecnologia_id = t.id
  );
GO

INSERT INTO dbo.ProyectoTecnologia (proyecto_id, tecnologia_id)
SELECT p.id, t.id
FROM dbo.Proyectos p
INNER JOIN dbo.Tecnologias t
    ON t.nombre IN (N'HTML', N'CSS', N'JavaScript')
WHERE p.slug = N'landing-innovatech'
  AND NOT EXISTS (
      SELECT 1 FROM dbo.ProyectoTecnologia pt
      WHERE pt.proyecto_id = p.id AND pt.tecnologia_id = t.id
  );
GO

/* ==========================================================================
   9. Solicitudes de cotización
   ========================================================================== */

IF NOT EXISTS (
    SELECT 1 FROM dbo.SolicitudesCotizacion
    WHERE email = N'maria.lopez@startup.mx'
      AND nombre_contacto = N'María López'
)
BEGIN
    INSERT INTO dbo.SolicitudesCotizacion (
        cliente_id, servicio_id, nombre_contacto, email, empresa, telefono,
        descripcion, presupuesto_estimado, estado, origen, creado_en
    )
    SELECT
        NULL,
        s.id,
        N'María López',
        N'maria.lopez@startup.mx',
        N'Startup MX',
        N'+52 55 4433 2211',
        N'Necesitamos un sitio corporativo con catálogo de productos y formulario de contacto.',
        85000.00,
        N'nueva',
        N'publico',
        DATEADD(DAY, -3, SYSUTCDATETIME())
    FROM dbo.Servicios s
    WHERE s.slug = N'desarrollo-web';
END
GO

IF NOT EXISTS (
    SELECT 1 FROM dbo.SolicitudesCotizacion
    WHERE email = N'jorge.ramirez@logisticaexpress.com'
      AND nombre_contacto = N'Jorge Ramírez'
)
BEGIN
    INSERT INTO dbo.SolicitudesCotizacion (
        cliente_id, servicio_id, nombre_contacto, email, empresa, telefono,
        descripcion, presupuesto_estimado, estado, origen, creado_en
    )
    SELECT
        NULL,
        s.id,
        N'Jorge Ramírez',
        N'jorge.ramirez@logisticaexpress.com',
        N'Logística Express',
        N'+52 81 2233 4455',
        N'Buscamos un tablero para monitorear envíos, tiempos de entrega y costos por ruta.',
        32000.00,
        N'en_revision',
        N'publico',
        DATEADD(DAY, -6, SYSUTCDATETIME())
    FROM dbo.Servicios s
    WHERE s.slug = N'dashboards';
END
GO

IF NOT EXISTS (
    SELECT 1 FROM dbo.SolicitudesCotizacion
    WHERE email = N'cliente@devstudio.local'
      AND nombre_contacto = N'Laura Méndez'
)
BEGIN
    INSERT INTO dbo.SolicitudesCotizacion (
        cliente_id, servicio_id, nombre_contacto, email, empresa, telefono,
        descripcion, presupuesto_estimado, estado, origen, creado_en
    )
    SELECT
        c.id,
        s.id,
        N'Laura Méndez',
        N'cliente@devstudio.local',
        N'Comercial Andina S.A. de C.V.',
        N'+52 55 1234 5678',
        N'Queremos una app para que nuestros clientes consulten pedidos y reciban promociones.',
        150000.00,
        N'cotizada',
        N'portal',
        DATEADD(DAY, -9, SYSUTCDATETIME())
    FROM dbo.Clientes c
    INNER JOIN dbo.Usuarios u ON u.id = c.usuario_id
    CROSS JOIN dbo.Servicios s
    WHERE u.email = N'cliente@devstudio.local'
      AND s.slug = N'apps-moviles';
END
GO

IF NOT EXISTS (
    SELECT 1 FROM dbo.SolicitudesCotizacion
    WHERE email = N'carlos.ruiz@innovatech.mx'
      AND nombre_contacto = N'Carlos Ruiz'
)
BEGIN
    INSERT INTO dbo.SolicitudesCotizacion (
        cliente_id, servicio_id, nombre_contacto, email, empresa, telefono,
        descripcion, presupuesto_estimado, estado, origen, creado_en
    )
    SELECT
        c.id,
        s.id,
        N'Carlos Ruiz',
        N'carlos.ruiz@innovatech.mx',
        N'InnovaTech Solutions',
        N'+52 81 8765 4321',
        N'Revisión de arquitectura y plan de migración del portal a la nube.',
        48000.00,
        N'aceptada',
        N'portal',
        DATEADD(DAY, -14, SYSUTCDATETIME())
    FROM dbo.Clientes c
    INNER JOIN dbo.Usuarios u ON u.id = c.usuario_id
    CROSS JOIN dbo.Servicios s
    WHERE u.email = N'carlos.ruiz@innovatech.mx'
      AND s.slug = N'consultoria';
END
GO

IF NOT EXISTS (
    SELECT 1 FROM dbo.SolicitudesCotizacion
    WHERE email = N'sofia.herrera@escuelacrecer.mx'
      AND nombre_contacto = N'Sofía Herrera'
)
BEGIN
    INSERT INTO dbo.SolicitudesCotizacion (
        cliente_id, servicio_id, nombre_contacto, email, empresa, telefono,
        descripcion, presupuesto_estimado, estado, origen, creado_en
    )
    SELECT
        NULL,
        s.id,
        N'Sofía Herrera',
        N'sofia.herrera@escuelacrecer.mx',
        N'Escuela Crecer',
        NULL,
        N'Sitio informativo para la escuela con calendario escolar y galería.',
        18000.00,
        N'rechazada',
        N'publico',
        DATEADD(DAY, -20, SYSUTCDATETIME())
    FROM dbo.Servicios s
    WHERE s.slug = N'desarrollo-web';
END
GO

/* Completar el diagnóstico de las solicitudes de ejemplo */

UPDATE dbo.SolicitudesCotizacion
SET plan_id = (SELECT id FROM dbo.Planes WHERE slug = N'esencial'),
    giro = N'Tecnología',
    necesidades = N'Sitio corporativo con catálogo de productos, formulario de contacto y presencia profesional.',
    servicios_interes = N'["desarrollo-web"]',
    meta_crecimiento_pct = 25.00,
    ahorro_horas_semana = 8,
    procesos_automatizar = N'Captura de prospectos y envío de cotizaciones.',
    plazo_deseado = N'1 a 3 meses',
    etapa_decision = N'evaluando',
    puntaje = 5,
    recomendacion = N'El Plan Esencial cubre la presencia web y el formulario de contacto; el crecimiento esperado y las horas de ahorro son moderados.'
WHERE email = N'maria.lopez@startup.mx'
  AND nombre_contacto = N'María López';
GO

UPDATE dbo.SolicitudesCotizacion
SET plan_id = (SELECT id FROM dbo.Planes WHERE slug = N'crecimiento'),
    giro = N'Logística',
    necesidades = N'Tablero para monitorear envíos, tiempos de entrega, costos por ruta y desempeño operativo.',
    servicios_interes = N'["dashboards"]',
    meta_crecimiento_pct = 30.00,
    ahorro_horas_semana = 20,
    procesos_automatizar = N'Reportes manuales de entregas y consolidación de indicadores.',
    plazo_deseado = N'2 a 4 meses',
    etapa_decision = N'comparando opciones',
    puntaje = 9,
    recomendacion = N'El Plan Crecimiento permite centralizar indicadores y automatizar reportes; el ahorro de 20 horas semanales justifica el tablero.'
WHERE email = N'jorge.ramirez@logisticaexpress.com'
  AND nombre_contacto = N'Jorge Ramírez';
GO

UPDATE dbo.SolicitudesCotizacion
SET plan_id = (SELECT id FROM dbo.Planes WHERE slug = N'transformacion'),
    giro = N'Comercio minorista',
    necesidades = N'App móvil para que los clientes consulten pedidos, reciban promociones y den seguimiento a sus compras.',
    servicios_interes = N'["apps-moviles","desarrollo-web"]',
    meta_crecimiento_pct = 60.00,
    ahorro_horas_semana = 35,
    procesos_automatizar = N'Atención de pedidos, notificaciones y seguimiento postventa.',
    plazo_deseado = N'3 a 6 meses',
    etapa_decision = N'lista para iniciar',
    puntaje = 15,
    recomendacion = N'El Plan Transformación integra app móvil, portal y automatizaciones; la meta de crecimiento y el volumen de procesos a eficientar requieren una solución a la medida.'
WHERE email = N'cliente@devstudio.local'
  AND nombre_contacto = N'Laura Méndez';
GO

UPDATE dbo.SolicitudesCotizacion
SET plan_id = (SELECT id FROM dbo.Planes WHERE slug = N'crecimiento'),
    giro = N'Tecnología',
    necesidades = N'Revisión de arquitectura, seguridad y plan de migración del portal a la nube.',
    servicios_interes = N'["consultoria","dashboards"]',
    meta_crecimiento_pct = 20.00,
    ahorro_horas_semana = 12,
    procesos_automatizar = N'Despliegues manuales y monitoreo de la plataforma.',
    plazo_deseado = N'1 a 3 meses',
    etapa_decision = N'lista para iniciar',
    puntaje = 8,
    recomendacion = N'El Plan Crecimiento combina consultoría y automatización para ordenar la arquitectura y reducir trabajo operativo.'
WHERE email = N'carlos.ruiz@innovatech.mx'
  AND nombre_contacto = N'Carlos Ruiz';
GO

UPDATE dbo.SolicitudesCotizacion
SET plan_id = (SELECT id FROM dbo.Planes WHERE slug = N'esencial'),
    giro = N'Educación',
    necesidades = N'Sitio informativo con calendario escolar, galería y formulario de admisión.',
    servicios_interes = N'["desarrollo-web"]',
    meta_crecimiento_pct = 10.00,
    ahorro_horas_semana = 4,
    procesos_automatizar = N'Recepción de solicitudes de admisión.',
    plazo_deseado = N'1 a 2 meses',
    etapa_decision = N'evaluando',
    puntaje = 3,
    recomendacion = N'El Plan Esencial es suficiente para una presencia informativa con formulario de admisión.'
WHERE email = N'sofia.herrera@escuelacrecer.mx'
  AND nombre_contacto = N'Sofía Herrera';
GO

/* ==========================================================================
   10. Mensajes de contacto
   ========================================================================== */

IF NOT EXISTS (
    SELECT 1 FROM dbo.MensajesContacto
    WHERE email = N'pedro.sanchez@ferreteria.mx'
      AND asunto = N'Cotización de sitio web'
)
BEGIN
    INSERT INTO dbo.MensajesContacto (nombre, email, asunto, mensaje, estado, creado_en)
    VALUES (
        N'Pedro Sánchez',
        N'pedro.sanchez@ferreteria.mx',
        N'Cotización de sitio web',
        N'Hola, me interesa un sitio con catálogo y carrito. ¿Podrían enviarme una cotización?',
        N'nuevo',
        DATEADD(DAY, -1, SYSUTCDATETIME())
    );
END
GO

IF NOT EXISTS (
    SELECT 1 FROM dbo.MensajesContacto
    WHERE email = N'lucia.gomez@escuelacrecer.mx'
      AND asunto = N'Mantenimiento de dashboard'
)
BEGIN
    INSERT INTO dbo.MensajesContacto (nombre, email, asunto, mensaje, estado, creado_en)
    VALUES (
        N'Lucía Gómez',
        N'lucia.gomez@escuelacrecer.mx',
        N'Mantenimiento de dashboard',
        N'Tenemos un tablero en PHP y necesitamos actualizar dependencias y corregir errores.',
        N'en_revision',
        DATEADD(DAY, -2, SYSUTCDATETIME())
    );
END
GO

IF NOT EXISTS (
    SELECT 1 FROM dbo.MensajesContacto
    WHERE email = N'diego.fernandez@retailmx.com'
      AND asunto = N'Integración con pasarela de pago'
)
BEGIN
    INSERT INTO dbo.MensajesContacto (
        nombre, email, asunto, mensaje, estado, creado_en, atendido_en
    )
    VALUES (
        N'Diego Fernández',
        N'diego.fernandez@retailmx.com',
        N'Integración con pasarela de pago',
        N'Necesitamos integrar pagos con tarjeta en nuestra tienda en línea.',
        N'atendido',
        DATEADD(DAY, -5, SYSUTCDATETIME()),
        DATEADD(DAY, -4, SYSUTCDATETIME())
    );
END
GO

IF NOT EXISTS (
    SELECT 1 FROM dbo.MensajesContacto
    WHERE email = N'valentina.rios@consultoriarios.mx'
      AND asunto = N'Propuesta de consultoría'
)
BEGIN
    INSERT INTO dbo.MensajesContacto (
        nombre, email, asunto, mensaje, estado, creado_en, atendido_en
    )
    VALUES (
        N'Valentina Ríos',
        N'valentina.rios@consultoriarios.mx',
        N'Propuesta de consultoría',
        N'Quisiéramos una propuesta para revisar la seguridad de nuestra plataforma.',
        N'archivado',
        DATEADD(DAY, -12, SYSUTCDATETIME()),
        DATEADD(DAY, -10, SYSUTCDATETIME())
    );
END
GO

PRINT 'Datos iniciales y de demostración cargados correctamente.';
GO
