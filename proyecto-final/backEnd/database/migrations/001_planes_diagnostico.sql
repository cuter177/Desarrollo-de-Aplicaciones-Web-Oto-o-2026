/*
 * Migración 001 — Planes y diagnóstico de proyectos
 * SQL Server 2022
 *
 * Agrega el catálogo de planes y los campos del wizard de diagnóstico
 * a la tabla SolicitudesCotizacion. Es idempotente.
 *
 * Uso:
 *   docker compose exec -T mssql /bin/bash -c \
 *     '/opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P "$MSSQL_SA_PASSWORD" -C -i /dev/stdin' \
 *     < backEnd/database/migrations/001_planes_diagnostico.sql
 */

USE [DevStudioDB];
GO

SET NOCOUNT ON;
GO

/* Planes */

IF OBJECT_ID(N'dbo.Planes', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.Planes (
        id              INT IDENTITY(1,1) NOT NULL,
        slug            NVARCHAR(80)      NOT NULL,
        nombre          NVARCHAR(120)     NOT NULL,
        subtitulo       NVARCHAR(200)     NULL,
        descripcion     NVARCHAR(MAX)     NOT NULL,
        precio_desde    DECIMAL(12,2)     NOT NULL
            CONSTRAINT DF_Planes_PrecioDesde DEFAULT (0),
        precio_hasta    DECIMAL(12,2)     NULL,
        caracteristicas NVARCHAR(MAX)     NULL,
        dirigido_a      NVARCHAR(200)     NULL,
        destacado       BIT               NOT NULL
            CONSTRAINT DF_Planes_Destacado DEFAULT (0),
        activo          BIT               NOT NULL
            CONSTRAINT DF_Planes_Activo DEFAULT (1),
        orden           INT               NOT NULL
            CONSTRAINT DF_Planes_Orden DEFAULT (0),
        creado_en       DATETIME2(0)      NOT NULL
            CONSTRAINT DF_Planes_Creado DEFAULT SYSUTCDATETIME(),
        actualizado_en  DATETIME2(0)      NOT NULL
            CONSTRAINT DF_Planes_Actualizado DEFAULT SYSUTCDATETIME(),
        CONSTRAINT PK_Planes PRIMARY KEY (id),
        CONSTRAINT UQ_Planes_Slug UNIQUE (slug),
        CONSTRAINT CK_Planes_Precios CHECK (
            precio_desde >= 0
            AND (precio_hasta IS NULL OR precio_hasta >= precio_desde)
        )
    );
END
GO

/* Columnas del diagnóstico */

IF COL_LENGTH(N'dbo.SolicitudesCotizacion', N'plan_id') IS NULL
    ALTER TABLE dbo.SolicitudesCotizacion ADD plan_id INT NULL;
GO

IF COL_LENGTH(N'dbo.SolicitudesCotizacion', N'giro') IS NULL
    ALTER TABLE dbo.SolicitudesCotizacion ADD giro NVARCHAR(100) NULL;
GO

IF COL_LENGTH(N'dbo.SolicitudesCotizacion', N'necesidades') IS NULL
    ALTER TABLE dbo.SolicitudesCotizacion ADD necesidades NVARCHAR(MAX) NULL;
GO

IF COL_LENGTH(N'dbo.SolicitudesCotizacion', N'servicios_interes') IS NULL
    ALTER TABLE dbo.SolicitudesCotizacion ADD servicios_interes NVARCHAR(MAX) NULL;
GO

IF COL_LENGTH(N'dbo.SolicitudesCotizacion', N'meta_crecimiento_pct') IS NULL
    ALTER TABLE dbo.SolicitudesCotizacion ADD meta_crecimiento_pct DECIMAL(5,2) NULL;
GO

IF COL_LENGTH(N'dbo.SolicitudesCotizacion', N'ahorro_horas_semana') IS NULL
    ALTER TABLE dbo.SolicitudesCotizacion ADD ahorro_horas_semana INT NULL;
GO

IF COL_LENGTH(N'dbo.SolicitudesCotizacion', N'procesos_automatizar') IS NULL
    ALTER TABLE dbo.SolicitudesCotizacion ADD procesos_automatizar NVARCHAR(MAX) NULL;
GO

IF COL_LENGTH(N'dbo.SolicitudesCotizacion', N'plazo_deseado') IS NULL
    ALTER TABLE dbo.SolicitudesCotizacion ADD plazo_deseado NVARCHAR(50) NULL;
GO

IF COL_LENGTH(N'dbo.SolicitudesCotizacion', N'etapa_decision') IS NULL
    ALTER TABLE dbo.SolicitudesCotizacion ADD etapa_decision NVARCHAR(30) NULL;
GO

IF COL_LENGTH(N'dbo.SolicitudesCotizacion', N'puntaje') IS NULL
    ALTER TABLE dbo.SolicitudesCotizacion ADD puntaje INT NULL;
GO

IF COL_LENGTH(N'dbo.SolicitudesCotizacion', N'recomendacion') IS NULL
    ALTER TABLE dbo.SolicitudesCotizacion ADD recomendacion NVARCHAR(MAX) NULL;
GO

IF COL_LENGTH(N'dbo.SolicitudesCotizacion', N'notas_admin') IS NULL
    ALTER TABLE dbo.SolicitudesCotizacion ADD notas_admin NVARCHAR(MAX) NULL;
GO

IF COL_LENGTH(N'dbo.SolicitudesCotizacion', N'proyecto_id') IS NULL
    ALTER TABLE dbo.SolicitudesCotizacion ADD proyecto_id INT NULL;
GO

/* Llaves y restricciones */

IF NOT EXISTS (
    SELECT 1 FROM sys.foreign_keys
    WHERE name = N'FK_Solicitudes_Planes'
      AND parent_object_id = OBJECT_ID(N'dbo.SolicitudesCotizacion')
)
BEGIN
    ALTER TABLE dbo.SolicitudesCotizacion
        ADD CONSTRAINT FK_Solicitudes_Planes FOREIGN KEY (plan_id)
            REFERENCES dbo.Planes (id) ON DELETE SET NULL;
END
GO

IF NOT EXISTS (
    SELECT 1 FROM sys.foreign_keys
    WHERE name = N'FK_Solicitudes_Proyectos'
      AND parent_object_id = OBJECT_ID(N'dbo.SolicitudesCotizacion')
)
BEGIN
    ALTER TABLE dbo.SolicitudesCotizacion
        ADD CONSTRAINT FK_Solicitudes_Proyectos FOREIGN KEY (proyecto_id)
            REFERENCES dbo.Proyectos (id) ON DELETE SET NULL;
END
GO

IF NOT EXISTS (
    SELECT 1 FROM sys.check_constraints
    WHERE name = N'CK_Solicitudes_Meta'
      AND parent_object_id = OBJECT_ID(N'dbo.SolicitudesCotizacion')
)
BEGIN
    ALTER TABLE dbo.SolicitudesCotizacion WITH CHECK
        ADD CONSTRAINT CK_Solicitudes_Meta CHECK (
            meta_crecimiento_pct IS NULL
            OR (meta_crecimiento_pct >= 0 AND meta_crecimiento_pct <= 1000)
        );
END
GO

IF NOT EXISTS (
    SELECT 1 FROM sys.check_constraints
    WHERE name = N'CK_Solicitudes_Ahorro'
      AND parent_object_id = OBJECT_ID(N'dbo.SolicitudesCotizacion')
)
BEGIN
    ALTER TABLE dbo.SolicitudesCotizacion WITH CHECK
        ADD CONSTRAINT CK_Solicitudes_Ahorro CHECK (
            ahorro_horas_semana IS NULL
            OR (ahorro_horas_semana >= 0 AND ahorro_horas_semana <= 168)
        );
END
GO

IF NOT EXISTS (
    SELECT 1 FROM sys.check_constraints
    WHERE name = N'CK_Solicitudes_Puntaje'
      AND parent_object_id = OBJECT_ID(N'dbo.SolicitudesCotizacion')
)
BEGIN
    ALTER TABLE dbo.SolicitudesCotizacion WITH CHECK
        ADD CONSTRAINT CK_Solicitudes_Puntaje CHECK (
            puntaje IS NULL OR puntaje >= 0
        );
END
GO

IF NOT EXISTS (
    SELECT 1 FROM sys.indexes
    WHERE name = N'IX_Solicitudes_Plan'
      AND object_id = OBJECT_ID(N'dbo.SolicitudesCotizacion')
)
BEGIN
    CREATE INDEX IX_Solicitudes_Plan
        ON dbo.SolicitudesCotizacion (plan_id);
END
GO

PRINT 'Migración 001 (planes y diagnóstico) aplicada correctamente.';
GO
