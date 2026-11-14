<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Helpers\Response;
use App\Middleware\Auth;

final class AdminController
{
    public function dashboard(): never
    {
        $usuario = Auth::requireRole('admin');

        $row = Database::connection()->query(
            "SELECT
                (SELECT COUNT(*) FROM dbo.Usuarios WHERE activo = 1) AS usuarios_activos,
                (SELECT COUNT(*) FROM dbo.Clientes) AS clientes,
                (SELECT COUNT(*) FROM dbo.Proyectos) AS proyectos,
                (SELECT COUNT(*) FROM dbo.SolicitudesCotizacion WHERE estado = N'nueva') AS solicitudes_nuevas,
                (SELECT COUNT(*) FROM dbo.MensajesContacto WHERE estado = N'nuevo') AS mensajes_nuevos"
        )->fetch();

        Response::data([
            'usuario' => $usuario,
            'metricas' => [
                'usuarios_activos' => (int) ($row['usuarios_activos'] ?? 0),
                'clientes' => (int) ($row['clientes'] ?? 0),
                'proyectos' => (int) ($row['proyectos'] ?? 0),
                'solicitudes_nuevas' => (int) ($row['solicitudes_nuevas'] ?? 0),
                'mensajes_nuevos' => (int) ($row['mensajes_nuevos'] ?? 0),
            ],
        ]);
    }
}
