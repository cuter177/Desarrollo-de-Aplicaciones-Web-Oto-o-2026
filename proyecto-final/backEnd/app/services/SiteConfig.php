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
            'nombre' => self::text('APP_NAME', '[Nombre de la empresa]'),
            'tagline' => self::text('COMPANY_TAGLINE', 'Software que hace crecer tu negocio'),
            'descripcion' => self::text(
                'COMPANY_DESCRIPTION',
                'Diseñamos sitios web, dashboards y aplicaciones móviles para empresas que quieren vender más y trabajar con menos procesos manuales.'
            ),
            'email' => self::text('COMPANY_EMAIL', 'contacto@devstudio.local'),
            'telefono' => self::text('COMPANY_PHONE', '+52 55 0000 0000'),
            'whatsapp' => self::text('COMPANY_WHATSAPP', '') ?? '',
            'direccion' => self::text('COMPANY_ADDRESS', 'Ciudad de México, México'),
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
