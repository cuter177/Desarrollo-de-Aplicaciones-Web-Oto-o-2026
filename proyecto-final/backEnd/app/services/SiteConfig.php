<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Env;

final class SiteConfig
{
    /**
     * Datos públicos de la empresa. No incluye secretos ni credenciales.
     *
     * @return array<string, mixed>
     */
    public static function public(): array
    {
        $redes = array_filter([
            'linkedin' => self::text('COMPANY_LINKEDIN'),
            'github' => self::text('COMPANY_GITHUB'),
            'instagram' => self::text('COMPANY_INSTAGRAM'),
        ], static fn (?string $value): bool => $value !== null);

        return [
            'nombre' => self::text('APP_NAME', 'DevStudio'),
            'tagline' => self::text('COMPANY_TAGLINE', 'Claridad para tu negocio, sin que sepas de sistemas'),
            'descripcion' => self::text(
                'COMPANY_DESCRIPTION',
                'Acompañamos a empresas de Puebla a conseguir clientes, ordenar la operación y atender desde el teléfono.'
            ),
            'email' => self::text('COMPANY_EMAIL', 'contacto@devstudio.local'),
            'telefono' => self::text('COMPANY_PHONE', '+52 222 000 0000'),
            'whatsapp' => self::text('COMPANY_WHATSAPP', '') ?? '',
            'direccion' => self::text('COMPANY_ADDRESS', 'Puebla, México'),
            'horario' => self::text('COMPANY_HOURS', 'Lunes a viernes, 9:00 a 18:00'),
            'sitio' => self::text('APP_URL', 'http://localhost:8080'),
            'redes' => $redes,
        ];
    }

    private static function text(string $key, ?string $default = null): ?string
    {
        $value = Env::get($key);

        if ($value === null || trim($value) === '') {
            return $default;
        }

        return trim($value);
    }
}
