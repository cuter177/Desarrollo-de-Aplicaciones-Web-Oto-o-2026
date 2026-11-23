<?php

declare(strict_types=1);

namespace App\Helpers;

final class Request
{
    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public static function path(): string
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        if (!is_string($path) || $path === '') {
            return '/';
        }

        $path = '/' . trim($path, '/');

        return $path === '/' ? '/' : $path;
    }

    public static function json(): array
    {
        static $cached = null;

        if (is_array($cached)) {
            return $cached;
        }

        $raw = file_get_contents('php://input');

        if ($raw === false || trim($raw) === '') {
            $cached = [];

            return $cached;
        }

        $data = json_decode($raw, true);

        if (!is_array($data)) {
            Response::error(
                'JSON_INVALIDO',
                'El cuerpo de la petición no es un JSON válido.',
                400
            );
        }

        $cached = $data;

        return $cached;
    }

    public static function query(string $key): ?string
    {
        $value = $_GET[$key] ?? null;

        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    public static function header(string $name): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        $value = $_SERVER[$key] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }

    public static function ip(): string
    {
        $candidate = $_SERVER['HTTP_X_REAL_IP']
            ?? $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['REMOTE_ADDR']
            ?? '';

        if (is_string($candidate) && str_contains($candidate, ',')) {
            $candidate = trim(explode(',', $candidate)[0]);
        }

        return is_string($candidate) && filter_var($candidate, FILTER_VALIDATE_IP)
            ? $candidate
            : '0.0.0.0';
    }
}
