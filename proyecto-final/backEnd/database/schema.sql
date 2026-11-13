/*
 * Esquema de base de datos — DevStudioDB
 * SQL Server 2022
 *
 * Estado: esqueleto del andamiaje.
 * Las tablas se implementarán en la siguiente etapa siguiendo el modelo ER y
 * el diccionario de datos:
 *   docs/02-base-datos/01-modelo-er.md
 *   docs/02-base-datos/02-diccionario-datos.md
 *
 * Orden previsto de creación:
 *   1. Roles
 *   2. Usuarios
 *   3. Clientes
 *   4. Servicios
 *   5. Tecnologias
 *   6. Proyectos
 *   7. ProyectoTecnologia
 *   8. SolicitudesCotizacion
 *   9. MensajesContacto
 */

IF DB_ID(N'DevStudioDB') IS NULL
BEGIN
    CREATE DATABASE [DevStudioDB];
END
GO

USE [DevStudioDB];
GO

-- TODO: crear las tablas con sus llaves, restricciones e índices.

/*
Ejemplo de la estructura esperada (no ejecutar todavía):

CREATE TABLE Roles (
    id          INT IDENTITY(1,1) NOT NULL CONSTRAINT PK_Roles PRIMARY KEY,
    nombre      NVARCHAR(30)      NOT NULL CONSTRAINT UQ_Roles_Nombre UNIQUE,
    descripcion NVARCHAR(200)     NULL,
    creado_en   DATETIME2(0)      NOT NULL CONSTRAINT DF_Roles_Creado DEFAULT SYSUTCDATETIME()
);
*/
