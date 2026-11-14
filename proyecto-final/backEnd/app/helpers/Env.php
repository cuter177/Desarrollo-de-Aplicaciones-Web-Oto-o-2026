<?php

declare(strict_types=1);

namespace App\Helpers;

final class Env
{
    /**
     * Carga archivos .env sin sobrescribir variables ya definidas por el
     * entorno (por ejemplo, las que inyecta Docker Compose).
     */
    public static function load(string ...$paths): void
    {
        foreach ($paths as $path) {
            if (!is_file($path) || !is_readable($path)) {
                continue;
            }

            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            if ($lines === false) {
                continue;
            }

            foreach ($lines as $line) {
                $line = trim($line);

                if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                    continue;
                }

                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                if ($key === '' || getenv($key) !== false) {
                    continue;
                }

                if ($value !== '' && ($value[0] === '"' || $value[0] === "'")) {
                    $quote = $value[0];
                    $end = strrpos($value, $quote);

                    if ($end !== false && $end > 0) {
                        $value = substr($value, 1, $end - 1);
                    }
                } else {
                    $comment = strpos($value, ' #');

                    if ($comment !== false) {
                        $value = rtrim(substr($value, 0, $comment));
                    }
                }

                putenv($key . '=' . $value);
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false || $value === null) {
            return $default;
        }

        return (string) $value;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = self::get($key);

        if ($value === null) {
            return $default;
        }

        $parsed = filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);

        return $parsed ?? $default;
    }

    public static function int(string $key, int $default = 0): int
    {
        $value = self::get($key);

        if ($value === null || !is_numeric($value)) {
            return $default;
        }

        return (int) $value;
    }
}
