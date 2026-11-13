<?php

declare(strict_types=1);

/**
 * Punto de entrada de la API.
 *
 * Este archivo es un placeholder del andamiaje: todavía no hay rutas,
 * controladores ni conexión a base de datos. El contrato completo de la
 * API está documentado en backEnd/docs/03-api.md.
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

$responder = static function (int $status, array $payload): never {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
};

if ($path === '/' || $path === '/api' || $path === '/api/') {
    $responder(200, [
        'data' => [
            'name' => 'API Proyecto Final',
            'version' => '0.1.0',
            'status' => 'scaffold',
            'method' => $method,
            'message' => 'La API aún no implementa endpoints. Consulta backEnd/docs/03-api.md.',
        ],
    ]);
}

if ($path === '/api/v1/health') {
    $responder(200, [
        'data' => [
            'status' => 'ok',
            'database' => 'not_configured',
            'version' => '0.1.0',
        ],
    ]);
}

$responder(404, [
    'error' => [
        'code' => 'NO_ENCONTRADO',
        'message' => 'Recurso no encontrado.',
        'details' => ['path' => $path, 'method' => $method],
    ],
]);
