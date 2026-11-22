/*
 * Migración 002 — Portafolio público y beneficios de servicios
 * SQL Server 2022
 *
 * Agrega el contenido de caso de éxito a Proyectos y los beneficios
 * editables de Servicios. Es idempotente.
 *
 * Uso:
 *   docker compose exec -T mssql /bin/bash -c \
 *     '/opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P "$MSSQL_SA_PASSWORD" -C -i /dev/stdin' \
 *     < backEnd/database/migrations/002_portafolio_publico.sql
 */

USE [DevStudioDB];
GO

SET NOCOUNT ON;
GO

/* Contenido de caso de éxito */

IF COL_LENGTH(N'dbo.Proyectos', N'reto') IS NULL
    ALTER TABLE dbo.Proyectos ADD reto NVARCHAR(MAX) NULL;
GO

IF COL_LENGTH(N'dbo.Proyectos', N'solucion') IS NULL
    ALTER TABLE dbo.Proyectos ADD solucion NVARCHAR(MAX) NULL;
GO

IF COL_LENGTH(N'dbo.Proyectos', N'resultados') IS NULL
    ALTER TABLE dbo.Proyectos ADD resultados NVARCHAR(MAX) NULL;
GO

IF COL_LENGTH(N'dbo.Proyectos', N'mostrar_cliente') IS NULL
    ALTER TABLE dbo.Proyectos ADD mostrar_cliente BIT NOT NULL
        CONSTRAINT DF_Proyectos_MostrarCliente DEFAULT (0) WITH VALUES;
GO

/* Beneficios de cada servicio */

IF COL_LENGTH(N'dbo.Servicios', N'beneficios') IS NULL
    ALTER TABLE dbo.Servicios ADD beneficios NVARCHAR(MAX) NULL;
GO

/* Índice para el portafolio público */

IF NOT EXISTS (
    SELECT 1 FROM sys.indexes
    WHERE name = N'IX_Proyectos_Publicado'
      AND object_id = OBJECT_ID(N'dbo.Proyectos')
)
BEGIN
    CREATE INDEX IX_Proyectos_Publicado
        ON dbo.Proyectos (publicado, destacado, fecha_inicio);
END
GO

PRINT 'Migración 002 aplicada correctamente.';
GO
