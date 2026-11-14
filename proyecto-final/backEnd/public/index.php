<?php

declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\HealthController;
use App\Helpers\Request;
use App\Helpers\Response;

try {
    require __DIR__ . '/../config/bootstrap.php';

    $routes = [
        'GET /' => [HealthController::class, 'info'],
        'GET /api' => [HealthController::class, 'info'],
        'GET /api/v1' => [HealthController::class, 'info'],
        'GET /api/v1/health' => [HealthController::class, 'health'],

        'GET /api/v1/auth/csrf' => [AuthController::class, 'csrf'],
        'POST /api/v1/auth/login' => [AuthController::class, 'login'],
        'POST /api/v1/auth/logout' => [AuthController::class, 'logout'],
        'GET /api/v1/auth/me' => [AuthController::class, 'me'],
        'POST /api/v1/auth/registro' => [AuthController::class, 'register'],
        'PATCH /api/v1/auth/password' => [AuthController::class, 'changePassword'],

        'GET /api/v1/admin/dashboard' => [AdminController::class, 'dashboard'],
    ];

    $method = Request::method();
    $path = Request::path();
    $routeKey = $method . ' ' . $path;

    if (isset($routes[$routeKey])) {
        [$controller, $action] = $routes[$routeKey];
        (new $controller())->$action();
        exit;
    }

    $allowed = [];

    foreach ($routes as $key => $handler) {
        [$routeMethod, $routePath] = explode(' ', $key, 2);

        if ($routePath === $path) {
            $allowed[] = $routeMethod;
        }
    }

    if ($allowed !== []) {
        if (!headers_sent()) {
            header('Allow: ' . implode(', ', array_unique($allowed)));
        }

        Response::error(
            'METODO_NO_PERMITIDO',
            'El método HTTP no está permitido para este recurso.',
            405
        );
    }

    Response::error(
        'NO_ENCONTRADO',
        'Recurso no encontrado.',
        404,
        ['path' => $path, 'method' => $method]
    );
} catch (Throwable $exception) {
    error_log(sprintf(
        "Error no controlado: %s en %s:%d\n%s",
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        $exception->getTraceAsString()
    ));

    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
    }

    echo json_encode([
        'error' => [
            'code' => 'ERROR_INTERNO',
            'message' => 'Ocurrió un error interno. Intenta de nuevo más tarde.',
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
