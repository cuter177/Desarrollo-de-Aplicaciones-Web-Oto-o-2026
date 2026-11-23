<?php

declare(strict_types=1);

namespace App\Helpers;

final class Response
{
    public static function json(mixed $payload, int $status = 200): never
    {
        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
        }

        echo json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
            | JSON_INVALID_UTF8_SUBSTITUTE
        );

        exit;
    }

    public static function data(mixed $data, int $status = 200, array $meta = []): never
    {
        $payload = ['data' => $data];

        if ($meta !== []) {
            $payload['meta'] = $meta;
        }

        self::json($payload, $status);
    }

    public static function error(
        string $code,
        string $message,
        int $status = 400,
        array $details = []
    ): never {
        $error = [
            'code' => $code,
            'message' => $message,
        ];

        if ($details !== []) {
            $error['details'] = $details;
        }

        self::json(['error' => $error], $status);
    }

    public static function file(string $path, string $type): never
    {
        if (!is_file($path)) {
            self::error('NO_ENCONTRADO', 'No hay foto de perfil.', 404);
        }

        if (!headers_sent()) {
            http_response_code(200);
            header('Content-Type: ' . $type);
            header('Content-Length: ' . (string) filesize($path));
            header('X-Content-Type-Options: nosniff');
            header('Cache-Control: private, max-age=86400');
        }

        readfile($path);
        exit;
    }

    public static function noContent(): never
    {
        if (!headers_sent()) {
            http_response_code(204);
        }

        exit;
    }
}
