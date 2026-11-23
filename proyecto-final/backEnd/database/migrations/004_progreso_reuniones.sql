/*
 * Migración 004 — Avance de proyectos y reuniones
 * SQL Server 2022
 *
 * Agrega el porcentaje de avance, su historial y las reuniones del cliente.
 * Es idempotente.
 *
 * Uso:
 *   docker compose exec -T mssql /bin/bash -c \
 *     '/opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P "$MSSQL_SA_PASSWORD" -C -i /dev/stdin' \
 *     < backEnd/database/migrations/004_progreso_reuniones.sql
 */

USE [DevStudioDB];
GO

SET NOCOUNT ON;
GO

IF COL_LENGTH(N'dbo.Proyectos', N'progreso') IS NULL
    ALTER TABLE dbo.Proyectos
        ADD progreso TINYINT NOT NULL
            CONSTRAINT DF_Proyectos_Progreso DEFAULT (0);
GO

IF COL_LENGTH(N'dbo.Proyectos', N'progreso') IS NOT NULL
   AND NOT EXISTS (
       SELECT 1 FROM sys.check_constraints
       WHERE name = N'CK_Proyectos_Progreso'
         AND parent_object_id = OBJECT_ID(N'dbo.Proyectos')
   )
    ALTER TABLE dbo.Proyectos
        ADD CONSTRAINT CK_Proyectos_Progreso CHECK (progreso <= 100);
GO

IF OBJECT_ID(N'dbo.ProyectoAvances', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.ProyectoAvances (
        id           INT IDENTITY(1,1) NOT NULL,
        proyecto_id  INT               NOT NULL,
        porcentaje   TINYINT           NOT NULL,
        nota         NVARCHAR(500)     NOT NULL,
        creado_en    DATETIME2(0)      NOT NULL
            CONSTRAINT DF_Avances_Creado DEFAULT SYSUTCDATETIME(),
        CONSTRAINT PK_ProyectoAvances PRIMARY KEY (id),
        CONSTRAINT CK_Avances_Porcentaje CHECK (porcentaje <= 100),
        CONSTRAINT FK_Avances_Proyectos FOREIGN KEY (proyecto_id)
            REFERENCES dbo.Proyectos (id) ON DELETE CASCADE
    );
END
GO

IF OBJECT_ID(N'dbo.Reuniones', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.Reuniones (
        id             INT IDENTITY(1,1) NOT NULL,
        proyecto_id    INT               NOT NULL,
        titulo         NVARCHAR(120)     NOT NULL,
        inicio         DATETIME2(0)      NOT NULL,
        fin            DATETIME2(0)      NOT NULL,
        estado         NVARCHAR(20)      NOT NULL
            CONSTRAINT DF_Reuniones_Estado DEFAULT N'solicitada',
        puntos         NVARCHAR(2000)    NULL,
        respuesta      NVARCHAR(1000)    NULL,
        creado_en      DATETIME2(0)      NOT NULL
            CONSTRAINT DF_Reuniones_Creado DEFAULT SYSUTCDATETIME(),
        actualizado_en DATETIME2(0)      NOT NULL
            CONSTRAINT DF_Reuniones_Actualizado DEFAULT SYSUTCDATETIME(),
        CONSTRAINT PK_Reuniones PRIMARY KEY (id),
        CONSTRAINT CK_Reuniones_Estado CHECK (
            estado IN (N'solicitada', N'confirmada', N'rechazada', N'cancelada')
        ),
        CONSTRAINT CK_Reuniones_Horario CHECK (inicio < fin),
        CONSTRAINT CK_Reuniones_Duracion CHECK (
            DATEDIFF(MINUTE, inicio, fin) BETWEEN 15 AND 240
        ),
        CONSTRAINT FK_Reuniones_Proyectos FOREIGN KEY (proyecto_id)
            REFERENCES dbo.Proyectos (id) ON DELETE CASCADE
    );
END
GO

IF NOT EXISTS (
    SELECT 1 FROM sys.indexes
    WHERE name = N'IX_Reuniones_Proyecto_Inicio'
      AND object_id = OBJECT_ID(N'dbo.Reuniones')
)
BEGIN
    CREATE INDEX IX_Reuniones_Proyecto_Inicio
        ON dbo.Reuniones (proyecto_id, inicio);
END
GO
