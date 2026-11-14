<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Csrf;
use App\Helpers\RateLimiter;
use App\Helpers\Request;
use App\Helpers\Response;
use App\Helpers\Validator;
use App\Models\Plan;
use App\Models\Servicio;
use App\Models\Solicitud;
use App\Services\PlanRecommender;

final class DiagnosticoController
{
    public function create(): never
    {
        Csrf::validate();

        $ipKey = 'diagnostico:ip:' . Request::ip();
        RateLimiter::enforce($ipKey, 10, 3600);

        $data = Request::json();
        $servicios = Servicio::allActive();
        $slugsValidos = array_map(
            static fn (array $servicio): string => (string) $servicio['slug'],
            $servicios
        );

        $errors = Validator::diagnostico($data, $slugsValidos);

        if ($errors !== []) {
            Response::error('VALIDACION', 'Revisa los datos del diagnóstico.', 422, $errors);
        }

        $seleccionados = array_values(array_unique(array_intersect(
            array_map('strval', (array) $data['servicios_interes']),
            $slugsValidos
        )));

        $servicioPrincipal = Servicio::findBySlug($seleccionados[0]);

        $diagnostico = [
            'servicios_interes' => $seleccionados,
            'meta_crecimiento_pct' => (float) $data['meta_crecimiento_pct'],
            'ahorro_horas_semana' => (int) $data['ahorro_horas_semana'],
            'presupuesto_estimado' => (float) $data['presupuesto_estimado'],
            'plazo_deseado' => (string) $data['plazo_deseado'],
        ];

        $resultado = PlanRecommender::recomendar($diagnostico);
        $plan = Plan::findBySlug($resultado['plan_slug']);

        if ($plan === null) {
            Response::error(
                'CONFIGURACION_INCOMPLETA',
                'No hay planes configurados. Ejecuta el seed o la migración de planes.',
                500
            );
        }

        $id = Solicitud::create([
            'cliente_id' => null,
            'servicio_id' => $servicioPrincipal['id'] ?? null,
            'plan_id' => $plan['id'],
            'nombre_contacto' => trim((string) $data['nombre_contacto']),
            'email' => strtolower(trim((string) $data['email'])),
            'empresa' => trim((string) $data['empresa']),
            'giro' => trim((string) ($data['giro'] ?? '')),
            'telefono' => trim((string) ($data['telefono'] ?? '')),
            'descripcion' => trim((string) $data['necesidades']),
            'necesidades' => trim((string) $data['necesidades']),
            'servicios_interes' => json_encode($seleccionados, JSON_UNESCAPED_UNICODE),
            'meta_crecimiento_pct' => (float) $data['meta_crecimiento_pct'],
            'ahorro_horas_semana' => (int) $data['ahorro_horas_semana'],
            'procesos_automatizar' => trim((string) ($data['procesos_automatizar'] ?? '')),
            'plazo_deseado' => (string) $data['plazo_deseado'],
            'etapa_decision' => (string) $data['etapa_decision'],
            'puntaje' => $resultado['puntaje'],
            'recomendacion' => $resultado['recomendacion'],
            'presupuesto_estimado' => (float) $data['presupuesto_estimado'],
            'estado' => 'nueva',
            'origen' => 'publico',
        ]);

        RateLimiter::hit($ipKey);

        Response::data([
            'solicitud_id' => $id,
            'plan' => $plan,
            'puntaje' => $resultado['puntaje'],
            'recomendacion' => $resultado['recomendacion'],
        ], 201);
    }
}
