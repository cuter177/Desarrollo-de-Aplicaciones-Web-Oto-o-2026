<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Helpers\Response;
use App\Middleware\Auth;
use App\Models\Solicitud;

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
                (SELECT COUNT(*) FROM dbo.MensajesContacto WHERE estado = N'nuevo') AS mensajes_nuevos,
                (SELECT COUNT(*) FROM dbo.SolicitudesCotizacion) AS solicitudes_total,
                (SELECT COUNT(*) FROM dbo.SolicitudesCotizacion WHERE estado = N'aceptada') AS solicitudes_aceptadas,
                (SELECT ISNULL(SUM(presupuesto_estimado), 0)
                   FROM dbo.SolicitudesCotizacion
                  WHERE estado IN (N'nueva', N'en_revision', N'cotizada')) AS pipeline_valor,
                (SELECT ISNULL(AVG(meta_crecimiento_pct), 0)
                   FROM dbo.SolicitudesCotizacion
                  WHERE meta_crecimiento_pct IS NOT NULL) AS promedio_crecimiento"
        )->fetch();

        $recientes = array_slice(Solicitud::all(), 0, 5);

        $metricas = [
            'usuarios_activos' => (int) ($row['usuarios_activos'] ?? 0),
            'clientes' => (int) ($row['clientes'] ?? 0),
            'proyectos' => (int) ($row['proyectos'] ?? 0),
            'solicitudes_nuevas' => (int) ($row['solicitudes_nuevas'] ?? 0),
            'mensajes_nuevos' => (int) ($row['mensajes_nuevos'] ?? 0),
            'solicitudes_total' => (int) ($row['solicitudes_total'] ?? 0),
            'solicitudes_aceptadas' => (int) ($row['solicitudes_aceptadas'] ?? 0),
            'pipeline_valor' => (float) ($row['pipeline_valor'] ?? 0),
            'promedio_crecimiento' => (float) ($row['promedio_crecimiento'] ?? 0),
        ];

        $metricas['tasa_conversion'] = $metricas['solicitudes_total'] > 0
            ? round(($metricas['solicitudes_aceptadas'] / $metricas['solicitudes_total']) * 100, 1)
            : 0.0;

        Response::data([
            'usuario' => $usuario,
            'metricas' => $metricas,
            'solicitudes_recientes' => $recientes,
        ]);
    }
}
