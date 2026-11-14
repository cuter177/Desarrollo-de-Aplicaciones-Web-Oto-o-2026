/*
 * Datos iniciales — DevStudioDB
 * SQL Server 2022
 *
 * Se ejecuta después de schema.sql. Es idempotente.
 * El usuario administrador NO se crea aquí: se crea con
 *   php backEnd/scripts/crear-admin.php
 * para generar el hash con password_hash().
 */

USE [DevStudioDB];
GO

SET NOCOUNT ON;
GO

/* Roles */
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

/* Servicios */
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

/* Tecnologías */
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

PRINT 'Datos iniciales cargados correctamente.';
GO
