<?php

declare(strict_types=1);

namespace App\Helpers;

final class RateLimiter
{
    private static function directory(): string
    {
        $directory = Env::get('RATE_LIMIT_DIR');

        if ($directory === null || $directory === '') {
            $directory = sys_get_temp_dir() . '/devstudio-rate-limit';
        }

        if (!is_dir($directory)) {
            @mkdir($directory, 0775, true);
        }

        return $directory;
    }

    private static function path(string $key): string
    {
        return self::directory() . '/' . hash('sha256', $key) . '.json';
    }

    /**
     * @return list<int>
     */
    private static function attempts(string $path, int $window): array
    {
        if (!is_file($path)) {
            return [];
        }

        $raw = @file_get_contents($path);
        $data = json_decode($raw !== false ? $raw : '[]', true);

        if (!is_array($data)) {
            return [];
        }

        $now = time();
        $valid = array_values(array_filter(
            $data,
            static fn (mixed $timestamp): bool => is_int($timestamp)
                && $timestamp > $now - $window
        ));

        sort($valid);

        return $valid;
    }

    public static function enforce(string $key, int $maxAttempts, int $windowSeconds): void
    {
        $attempts = self::attempts(self::path($key), $windowSeconds);

        if (count($attempts) < $maxAttempts) {
            return;
        }

        $oldest = $attempts[0] ?? time();
        $retryAfter = max(1, $oldest + $windowSeconds - time());

        if (!headers_sent()) {
            header('Retry-After: ' . $retryAfter);
        }

        Response::error(
            'DEMASIADAS_PETICIONES',
            'Demasiados intentos. Espera unos minutos antes de volver a intentarlo.',
            429,
            ['retry_after' => $retryAfter]
        );
    }

    public static function hit(string $key): void
    {
        $path = self::path($key);
        $data = [];

        if (is_file($path)) {
            $raw = @file_get_contents($path);
            $decoded = json_decode($raw !== false ? $raw : '[]', true);

            if (is_array($decoded)) {
                $data = array_values(array_filter(
                    $decoded,
                    static fn (mixed $timestamp): bool => is_int($timestamp)
                ));
            }
        }

        $data[] = time();
        $data = array_slice($data, -50);

        @file_put_contents($path, json_encode($data), LOCK_EX);
    }

    public static function clear(string $key): void
    {
        @unlink(self::path($key));
    }
}
