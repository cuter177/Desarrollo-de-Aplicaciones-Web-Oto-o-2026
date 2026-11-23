/*
 * Migración 005 — Foto de perfil
 * SQL Server 2022
 *
 * Agrega la extensión de la foto a Usuarios. Es idempotente.
 * El archivo vive en backEnd/storage/avatars/{id}.{ext}.
 *
 * Uso:
 *   docker compose exec -T mssql /bin/bash -c \
 *     '/opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P "$MSSQL_SA_PASSWORD" -C -i /dev/stdin' \
 *     < backEnd/database/migrations/005_foto_perfil.sql
 */

USE [DevStudioDB];
GO

SET NOCOUNT ON;
GO

IF COL_LENGTH(N'dbo.Usuarios', N'foto') IS NULL
    ALTER TABLE dbo.Usuarios ADD foto NVARCHAR(8) NULL;
GO
