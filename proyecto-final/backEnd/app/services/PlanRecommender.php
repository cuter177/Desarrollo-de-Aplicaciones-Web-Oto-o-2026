<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Calcula el plan recomendado a partir del diagnóstico del cliente.
 *
 * El puntaje es una heurística de prioridad, no una promesa de resultados.
 */
final class PlanRecommender
{
    /**
     * @param array<string, mixed> $data
     * @return array{plan_slug: string, puntaje: int, recomendacion: string}
     */
    public static function recomendar(array $data): array
    {
        $servicios = is_array($data['servicios_interes'] ?? null)
            ? array_values(array_filter($data['servicios_interes'], 'is_string'))
            : [];

        $presupuesto = (float) ($data['presupuesto_estimado'] ?? 0);
        $crecimiento = (float) ($data['meta_crecimiento_pct'] ?? 0);
        $horas = (int) ($data['ahorro_horas_semana'] ?? 0);
        $plazo = (string) ($data['plazo_deseado'] ?? '');

        $puntaje = 0;

        foreach ($servicios as $slug) {
            $puntaje += match ($slug) {
                'apps-moviles', 'dashboards', 'consultoria' => 2,
                default => 1,
            };
        }

        $puntaje += match (true) {
            $presupuesto >= 120000 => 3,
            $presupuesto >= 70000 => 1,
            default => 0,
        };

        $puntaje += match (true) {
            $crecimiento >= 60 => 3,
            $crecimiento >= 15 => 1,
            default => 0,
        };

        $puntaje += match (true) {
            $horas >= 30 => 3,
            $horas >= 15 => 2,
            $horas >= 6 => 1,
            default => 0,
        };

        $puntaje += match ($plazo) {
            '3 a 6 meses', 'listo para iniciar' => 2,
            '2 a 4 meses' => 1,
            default => 0,
        };

        $planSlug = match (true) {
            $puntaje >= 12 => 'transformacion',
            $puntaje >= 6 => 'crecimiento',
            default => 'esencial',
        };

        $recomendacion = self::texto($planSlug, $puntaje, $crecimiento, $horas);

        return [
            'plan_slug' => $planSlug,
            'puntaje' => $puntaje,
            'recomendacion' => $recomendacion,
        ];
    }

    private static function texto(
        string $planSlug,
        int $puntaje,
        float $crecimiento,
        int $horas
    ): string {
        $impacto = sprintf(
            'Meta declarada: crecer %.0f%% y ahorrar %d horas por semana.',
            $crecimiento,
            $horas
        );

        return match ($planSlug) {
            'transformacion' => 'El Plan Transformación se ajusta a un proyecto de mayor alcance: '
                . 'requiere aplicación a la medida, integraciones y acompañamiento. ' . $impacto,
            'crecimiento' => 'El Plan Crecimiento permite automatizar procesos y medir indicadores '
                . 'sin llegar a una aplicación completamente a la medida. ' . $impacto,
            default => 'El Plan Esencial cubre la presencia digital y la captación inicial. '
                . 'Si el volumen de procesos crece, se puede escalar a Crecimiento. ' . $impacto,
        } . sprintf(' Puntaje de prioridad: %d.', $puntaje);
    }
}
