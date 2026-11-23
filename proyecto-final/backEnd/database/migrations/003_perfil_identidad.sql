/*
 * Migración 003 — Identidad del perfil de cliente
 * SQL Server 2022
 *
 * Agrega cargo, frase y ciudad a Clientes. Es idempotente.
 *
 * Uso:
 *   docker compose exec -T mssql /bin/bash -c \
 *     '/opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P "$MSSQL_SA_PASSWORD" -C -i /dev/stdin' \
 *     < backEnd/database/migrations/003_perfil_identidad.sql
 */

USE [DevStudioDB];
GO

SET NOCOUNT ON;
GO

IF COL_LENGTH(N'dbo.Clientes', N'cargo') IS NULL
    ALTER TABLE dbo.Clientes ADD cargo NVARCHAR(80) NULL;
GO

IF COL_LENGTH(N'dbo.Clientes', N'frase') IS NULL
    ALTER TABLE dbo.Clientes ADD frase NVARCHAR(160) NULL;
GO

IF COL_LENGTH(N'dbo.Clientes', N'ciudad') IS NULL
    ALTER TABLE dbo.Clientes ADD ciudad NVARCHAR(80) NULL;
GO
