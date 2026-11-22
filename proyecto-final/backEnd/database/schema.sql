/*
 * Esquema de base de datos — DevStudioDB
 * SQL Server 2022
 *
 * Etapa 2: esquema completo del modelo ER.
 * Referencias:
 *   backEnd/docs/02-base-datos/01-modelo-er.md
 *   backEnd/docs/02-base-datos/02-diccionario-datos.md
 *
 * El script es idempotente: se puede ejecutar varias veces sin recrear
 * objetos existentes.
 */

IF DB_ID(N'DevStudioDB') IS NULL
BEGIN
    CREATE DATABASE [DevStudioDB];
END
GO

USE [DevStudioDB];
GO

SET ANSI_NULLS ON;
GO
SET QUOTED_IDENTIFIER ON;
GO

/* ==========================================================================
   1. Roles
   ========================================================================== */

IF OBJECT_ID(N'dbo.Roles', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.Roles (
        id          INT IDENTITY(1,1) NOT NULL,
        nombre      NVARCHAR(30)      NOT NULL,
        descripcion NVARCHAR(200)     NULL,
        creado_en   DATETIME2(0)      NOT NULL
            CONSTRAINT DF_Roles_Creado DEFAULT SYSUTCDATETIME(),
        CONSTRAINT PK_Roles PRIMARY KEY (id),
        CONSTRAINT UQ_Roles_Nombre UNIQUE (nombre)
    );
END
GO

/* ==========================================================================
   2. Usuarios
   ========================================================================== */

IF OBJECT_ID(N'dbo.Usuarios', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.Usuarios (
        id              INT IDENTITY(1,1) NOT NULL,
        nombre          NVARCHAR(120)     NOT NULL,
        email           NVARCHAR(254)     NOT NULL,
        password_hash   NVARCHAR(255)     NOT NULL,
        rol_id          INT               NOT NULL,
        activo          BIT               NOT NULL
            CONSTRAINT DF_Usuarios_Activo DEFAULT (1),
        ultimo_acceso   DATETIME2(0)      NULL,
        creado_en       DATETIME2(0)      NOT NULL
            CONSTRAINT DF_Usuarios_Creado DEFAULT SYSUTCDATETIME(),
        actualizado_en  DATETIME2(0)      NOT NULL
            CONSTRAINT DF_Usuarios_Actualizado DEFAULT SYSUTCDATETIME(),
        CONSTRAINT PK_Usuarios PRIMARY KEY (id),
        CONSTRAINT UQ_Usuarios_Email UNIQUE (email),
        CONSTRAINT FK_Usuarios_Roles FOREIGN KEY (rol_id)
            REFERENCES dbo.Roles (id)
    );
END
GO

/* ==========================================================================
   3. Clientes
   ========================================================================== */

IF OBJECT_ID(N'dbo.Clientes', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.Clientes (
        id              INT IDENTITY(1,1) NOT NULL,
        usuario_id      INT               NOT NULL,
        empresa         NVARCHAR(150)     NOT NULL,
        telefono        NVARCHAR(30)      NULL,
        sitio_web       NVARCHAR(200)     NULL,
        industria       NVARCHAR(100)     NULL,
        creado_en       DATETIME2(0)      NOT NULL
            CONSTRAINT DF_Clientes_Creado DEFAULT SYSUTCDATETIME(),
        actualizado_en  DATETIME2(0)      NOT NULL
            CONSTRAINT DF_Clientes_Actualizado DEFAULT SYSUTCDATETIME(),
        CONSTRAINT PK_Clientes PRIMARY KEY (id),
        CONSTRAINT UQ_Clientes_Usuario UNIQUE (usuario_id),
        CONSTRAINT FK_Clientes_Usuarios FOREIGN KEY (usuario_id)
            REFERENCES dbo.Usuarios (id)
    );
END
GO

/* ==========================================================================
   4. Servicios
   ========================================================================== */

IF OBJECT_ID(N'dbo.Servicios', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.Servicios (
        id              INT IDENTITY(1,1) NOT NULL,
        slug            NVARCHAR(120)     NOT NULL,
        nombre          NVARCHAR(120)     NOT NULL,
        descripcion     NVARCHAR(MAX)     NOT NULL,
        beneficios      NVARCHAR(MAX)     NULL,
        icono           NVARCHAR(80)      NULL,
        activo          BIT               NOT NULL
            CONSTRAINT DF_Servicios_Activo DEFAULT (1),
        orden           INT               NOT NULL
            CONSTRAINT DF_Servicios_Orden DEFAULT (0),
        creado_en       DATETIME2(0)      NOT NULL
            CONSTRAINT DF_Servicios_Creado DEFAULT SYSUTCDATETIME(),
        actualizado_en  DATETIME2(0)      NOT NULL
            CONSTRAINT DF_Servicios_Actualizado DEFAULT SYSUTCDATETIME(),
        CONSTRAINT PK_Servicios PRIMARY KEY (id),
        CONSTRAINT UQ_Servicios_Slug UNIQUE (slug)
    );
END
GO

/* ==========================================================================
   5. Tecnologías
   ========================================================================== */

IF OBJECT_ID(N'dbo.Tecnologias', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.Tecnologias (
        id          INT IDENTITY(1,1) NOT NULL,
        nombre      NVARCHAR(80)      NOT NULL,
        categoria   NVARCHAR(60)      NULL,
        icono       NVARCHAR(80)      NULL,
        activo      BIT               NOT NULL
            CONSTRAINT DF_Tecnologias_Activo DEFAULT (1),
        creado_en   DATETIME2(0)      NOT NULL
            CONSTRAINT DF_Tecnologias_Creado DEFAULT SYSUTCDATETIME(),
        CONSTRAINT PK_Tecnologias PRIMARY KEY (id),
        CONSTRAINT UQ_Tecnologias_Nombre UNIQUE (nombre)
    );
END
GO

/* ==========================================================================
   6. Proyectos
   ========================================================================== */

IF OBJECT_ID(N'dbo.Proyectos', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.Proyectos (
        id              INT IDENTITY(1,1) NOT NULL,
        cliente_id      INT               NOT NULL,
        nombre          NVARCHAR(150)     NOT NULL,
        slug            NVARCHAR(160)     NOT NULL,
        descripcion     NVARCHAR(MAX)     NOT NULL,
        reto            NVARCHAR(MAX)     NULL,
        solucion        NVARCHAR(MAX)     NULL,
        resultados      NVARCHAR(MAX)     NULL,
        mostrar_cliente BIT               NOT NULL
            CONSTRAINT DF_Proyectos_MostrarCliente DEFAULT (0),
        estado          NVARCHAR(30)      NOT NULL
            CONSTRAINT DF_Proyectos_Estado DEFAULT N'planificado',
        fecha_inicio    DATE              NULL,
        fecha_fin       DATE              NULL,
        url_demo        NVARCHAR(300)     NULL,
        publicado       BIT               NOT NULL
            CONSTRAINT DF_Proyectos_Publicado DEFAULT (0),
        destacado       BIT               NOT NULL
            CONSTRAINT DF_Proyectos_Destacado DEFAULT (0),
        creado_en       DATETIME2(0)      NOT NULL
            CONSTRAINT DF_Proyectos_Creado DEFAULT SYSUTCDATETIME(),
        actualizado_en  DATETIME2(0)      NOT NULL
            CONSTRAINT DF_Proyectos_Actualizado DEFAULT SYSUTCDATETIME(),
        CONSTRAINT PK_Proyectos PRIMARY KEY (id),
        CONSTRAINT UQ_Proyectos_Slug UNIQUE (slug),
        CONSTRAINT CK_Proyectos_Estado CHECK (
            estado IN (
                N'planificado',
                N'en_progreso',
                N'en_revision',
                N'entregado',
                N'pausado',
                N'cancelado'
            )
        ),
        CONSTRAINT FK_Proyectos_Clientes FOREIGN KEY (cliente_id)
            REFERENCES dbo.Clientes (id)
    );
END
GO

/* ==========================================================================
   7. ProyectoTecnologia (N:M)
   ========================================================================== */

IF OBJECT_ID(N'dbo.ProyectoTecnologia', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.ProyectoTecnologia (
        proyecto_id    INT NOT NULL,
        tecnologia_id  INT NOT NULL,
        CONSTRAINT PK_ProyectoTecnologia
            PRIMARY KEY (proyecto_id, tecnologia_id),
        CONSTRAINT FK_ProyectoTecnologia_Proyectos
            FOREIGN KEY (proyecto_id) REFERENCES dbo.Proyectos (id)
            ON DELETE CASCADE,
        CONSTRAINT FK_ProyectoTecnologia_Tecnologias
            FOREIGN KEY (tecnologia_id) REFERENCES dbo.Tecnologias (id)
    );
END
GO

/* ==========================================================================
   8. Planes
   ========================================================================== */

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

/* ==========================================================================
   9. SolicitudesCotizacion
   ========================================================================== */

IF OBJECT_ID(N'dbo.SolicitudesCotizacion', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.SolicitudesCotizacion (
        id                    INT IDENTITY(1,1) NOT NULL,
        cliente_id            INT               NULL,
        servicio_id           INT               NULL,
        plan_id               INT               NULL,
        proyecto_id           INT               NULL,
        nombre_contacto       NVARCHAR(120)     NOT NULL,
        email                 NVARCHAR(254)     NOT NULL,
        empresa               NVARCHAR(150)     NULL,
        giro                  NVARCHAR(100)     NULL,
        telefono              NVARCHAR(30)      NULL,
        descripcion           NVARCHAR(MAX)     NOT NULL,
        necesidades           NVARCHAR(MAX)     NULL,
        servicios_interes     NVARCHAR(MAX)     NULL,
        meta_crecimiento_pct  DECIMAL(5,2)      NULL,
        ahorro_horas_semana   INT               NULL,
        procesos_automatizar  NVARCHAR(MAX)     NULL,
        plazo_deseado         NVARCHAR(50)      NULL,
        etapa_decision        NVARCHAR(30)      NULL,
        puntaje               INT               NULL,
        recomendacion         NVARCHAR(MAX)     NULL,
        notas_admin           NVARCHAR(MAX)     NULL,
        presupuesto_estimado  DECIMAL(12,2)     NULL,
        estado                NVARCHAR(30)      NOT NULL
            CONSTRAINT DF_Solicitudes_Estado DEFAULT N'nueva',
        origen                NVARCHAR(20)      NOT NULL
            CONSTRAINT DF_Solicitudes_Origen DEFAULT N'publico',
        creado_en             DATETIME2(0)      NOT NULL
            CONSTRAINT DF_Solicitudes_Creado DEFAULT SYSUTCDATETIME(),
        actualizado_en        DATETIME2(0)      NOT NULL
            CONSTRAINT DF_Solicitudes_Actualizado DEFAULT SYSUTCDATETIME(),
        CONSTRAINT PK_SolicitudesCotizacion PRIMARY KEY (id),
        CONSTRAINT CK_Solicitudes_Estado CHECK (
            estado IN (
                N'nueva',
                N'en_revision',
                N'cotizada',
                N'aceptada',
                N'rechazada'
            )
        ),
        CONSTRAINT CK_Solicitudes_Origen CHECK (
            origen IN (N'publico', N'portal')
        ),
        CONSTRAINT CK_Solicitudes_Presupuesto CHECK (
            presupuesto_estimado IS NULL OR presupuesto_estimado >= 0
        ),
        CONSTRAINT CK_Solicitudes_Meta CHECK (
            meta_crecimiento_pct IS NULL
            OR (meta_crecimiento_pct >= 0 AND meta_crecimiento_pct <= 1000)
        ),
        CONSTRAINT CK_Solicitudes_Ahorro CHECK (
            ahorro_horas_semana IS NULL
            OR (ahorro_horas_semana >= 0 AND ahorro_horas_semana <= 168)
        ),
        CONSTRAINT CK_Solicitudes_Puntaje CHECK (
            puntaje IS NULL OR puntaje >= 0
        ),
        CONSTRAINT FK_Solicitudes_Clientes FOREIGN KEY (cliente_id)
            REFERENCES dbo.Clientes (id) ON DELETE SET NULL,
        CONSTRAINT FK_Solicitudes_Servicios FOREIGN KEY (servicio_id)
            REFERENCES dbo.Servicios (id) ON DELETE SET NULL,
        CONSTRAINT FK_Solicitudes_Planes FOREIGN KEY (plan_id)
            REFERENCES dbo.Planes (id) ON DELETE SET NULL,
        CONSTRAINT FK_Solicitudes_Proyectos FOREIGN KEY (proyecto_id)
            REFERENCES dbo.Proyectos (id) ON DELETE SET NULL
    );
END
GO

/* ==========================================================================
   10. MensajesContacto
   ========================================================================== */

IF OBJECT_ID(N'dbo.MensajesContacto', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.MensajesContacto (
        id           INT IDENTITY(1,1) NOT NULL,
        nombre       NVARCHAR(120)     NOT NULL,
        email        NVARCHAR(254)     NOT NULL,
        asunto       NVARCHAR(150)     NOT NULL,
        mensaje      NVARCHAR(MAX)     NOT NULL,
        estado       NVARCHAR(20)      NOT NULL
            CONSTRAINT DF_Mensajes_Estado DEFAULT N'nuevo',
        creado_en    DATETIME2(0)      NOT NULL
            CONSTRAINT DF_Mensajes_Creado DEFAULT SYSUTCDATETIME(),
        atendido_en  DATETIME2(0)      NULL,
        CONSTRAINT PK_MensajesContacto PRIMARY KEY (id),
        CONSTRAINT CK_Mensajes_Estado CHECK (
            estado IN (N'nuevo', N'en_revision', N'atendido', N'archivado')
        )
    );
END
GO

/* ==========================================================================
   Índices de consulta
   ========================================================================== */

IF NOT EXISTS (
    SELECT 1 FROM sys.indexes
    WHERE name = N'IX_Proyectos_Cliente'
      AND object_id = OBJECT_ID(N'dbo.Proyectos')
)
BEGIN
    CREATE INDEX IX_Proyectos_Cliente ON dbo.Proyectos (cliente_id);
END
GO

IF NOT EXISTS (
    SELECT 1 FROM sys.indexes
    WHERE name = N'IX_Proyectos_Estado_Publicado'
      AND object_id = OBJECT_ID(N'dbo.Proyectos')
)
BEGIN
    CREATE INDEX IX_Proyectos_Estado_Publicado
        ON dbo.Proyectos (estado, publicado);
END
GO

IF NOT EXISTS (
    SELECT 1 FROM sys.indexes
    WHERE name = N'IX_Solicitudes_Estado'
      AND object_id = OBJECT_ID(N'dbo.SolicitudesCotizacion')
)
BEGIN
    CREATE INDEX IX_Solicitudes_Estado
        ON dbo.SolicitudesCotizacion (estado);
END
GO

IF NOT EXISTS (
    SELECT 1 FROM sys.indexes
    WHERE name = N'IX_Solicitudes_Email'
      AND object_id = OBJECT_ID(N'dbo.SolicitudesCotizacion')
)
BEGIN
    CREATE INDEX IX_Solicitudes_Email
        ON dbo.SolicitudesCotizacion (email);
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

IF NOT EXISTS (
    SELECT 1 FROM sys.indexes
    WHERE name = N'IX_Mensajes_Estado'
      AND object_id = OBJECT_ID(N'dbo.MensajesContacto')
)
BEGIN
    CREATE INDEX IX_Mensajes_Estado ON dbo.MensajesContacto (estado);
END
GO

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

PRINT 'Esquema DevStudioDB creado o actualizado correctamente.';
GO
