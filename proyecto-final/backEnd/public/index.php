<?php

declare(strict_types=1);

use App\Controllers\AdminClienteController;
use App\Controllers\AdminController;
use App\Controllers\AdminMensajeController;
use App\Controllers\AdminProyectoController;
use App\Controllers\AdminSolicitudController;
use App\Controllers\AuthController;
use App\Controllers\CatalogoController;
use App\Controllers\ContactoController;
use App\Controllers\DiagnosticoController;
use App\Controllers\HealthController;
use App\Helpers\Request;
use App\Helpers\Response;

/**
 * @return array<string, int|string>|null
 */
function matchPath(string $pattern, string $path): ?array
{
    if ($pattern === $path) {
        return [];
    }

    $patternParts = explode('/', trim($pattern, '/'));
    $pathParts = explode('/', trim($path, '/'));

    if (count($patternParts) !== count($pathParts)) {
        return null;
    }

    $params = [];

    foreach ($patternParts as $index => $part) {
        if (preg_match('/^\{([a-zA-Z_][a-zA-Z0-9_]*)\}$/', $part, $matches) === 1) {
            $value = $pathParts[$index];

            if ($matches[1] === 'id' && !ctype_digit($value)) {
                return null;
            }

            $params[$matches[1]] = $matches[1] === 'id' ? (int) $value : $value;
            continue;
        }

        if ($part !== $pathParts[$index]) {
            return null;
        }
    }

    return $params;
}

try {
    require __DIR__ . '/../config/bootstrap.php';

    $routes = [
        'GET /' => [HealthController::class, 'info'],
        'GET /api' => [HealthController::class, 'info'],
        'GET /api/v1' => [HealthController::class, 'info'],
        'GET /api/v1/health' => [HealthController::class, 'health'],

        'GET /api/v1/planes' => [CatalogoController::class, 'planes'],
        'GET /api/v1/config' => [CatalogoController::class, 'config'],
        'GET /api/v1/servicios' => [CatalogoController::class, 'servicios'],
        'GET /api/v1/servicios/{slug}' => [CatalogoController::class, 'servicio'],
        'GET /api/v1/tecnologias' => [CatalogoController::class, 'tecnologias'],
        'GET /api/v1/portafolio' => [CatalogoController::class, 'portafolio'],
        'GET /api/v1/portafolio/{slug}' => [CatalogoController::class, 'caso'],
        'POST /api/v1/diagnostico' => [DiagnosticoController::class, 'create'],
        'POST /api/v1/contacto' => [ContactoController::class, 'create'],

        'GET /api/v1/auth/csrf' => [AuthController::class, 'csrf'],
        'POST /api/v1/auth/login' => [AuthController::class, 'login'],
        'POST /api/v1/auth/logout' => [AuthController::class, 'logout'],
        'GET /api/v1/auth/me' => [AuthController::class, 'me'],
        'POST /api/v1/auth/registro' => [AuthController::class, 'register'],
        'PATCH /api/v1/auth/password' => [AuthController::class, 'changePassword'],

        'GET /api/v1/admin/dashboard' => [AdminController::class, 'dashboard'],
        'GET /api/v1/admin/clientes' => [AdminClienteController::class, 'index'],
        'GET /api/v1/admin/solicitudes' => [AdminSolicitudController::class, 'index'],
        'GET /api/v1/admin/solicitudes/{id}' => [AdminSolicitudController::class, 'show'],
        'PATCH /api/v1/admin/solicitudes/{id}' => [AdminSolicitudController::class, 'update'],
        'POST /api/v1/admin/solicitudes/{id}/convertir' => [AdminSolicitudController::class, 'convertir'],
        'GET /api/v1/admin/proyectos' => [AdminProyectoController::class, 'index'],
        'GET /api/v1/admin/proyectos/{id}' => [AdminProyectoController::class, 'show'],
        'POST /api/v1/admin/proyectos' => [AdminProyectoController::class, 'store'],
        'PATCH /api/v1/admin/proyectos/{id}' => [AdminProyectoController::class, 'update'],

        'GET /api/v1/admin/mensajes' => [AdminMensajeController::class, 'index'],
        'GET /api/v1/admin/mensajes/{id}' => [AdminMensajeController::class, 'show'],
        'PATCH /api/v1/admin/mensajes/{id}' => [AdminMensajeController::class, 'update'],
    ];

    $method = Request::method();
    $path = Request::path();

    foreach ($routes as $key => $handler) {
        [$routeMethod, $routePath] = explode(' ', $key, 2);

        if ($routeMethod !== $method) {
            continue;
        }

        $params = matchPath($routePath, $path);

        if ($params === null) {
            continue;
        }

        [$controller, $action] = $handler;
        (new $controller())->$action(...array_values($params));
        exit;
    }

    $allowed = [];

    foreach ($routes as $key => $handler) {
        [$routeMethod, $routePath] = explode(' ', $key, 2);

        if (matchPath($routePath, $path) !== null) {
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
