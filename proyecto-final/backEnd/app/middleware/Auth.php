<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Helpers\Response;

final class Auth
{
    /**
     * @return array<string, mixed>|null
     */
    public static function user(): ?array
    {
        $usuario = $_SESSION['usuario'] ?? null;

        return is_array($usuario) ? $usuario : null;
    }

    /**
     * @return array<string, mixed>
     */
    public static function requireLogin(): array
    {
        $usuario = self::user();

        if ($usuario === null) {
            Response::error(
                'NO_AUTENTICADO',
                'Debes iniciar sesión para acceder a este recurso.',
                401
            );
        }

        return $usuario;
    }

    /**
     * @return array<string, mixed>
     */
    public static function requireRole(string $rol): array
    {
        $usuario = self::requireLogin();

        if (($usuario['rol'] ?? null) !== $rol) {
            Response::error(
                'SIN_PERMISOS',
                'No tienes permisos para acceder a este recurso.',
                403
            );
        }

        return $usuario;
    }

    /**
     * @param list<string> $roles
     * @return array<string, mixed>
     */
    public static function requireAnyRole(array $roles): array
    {
        $usuario = self::requireLogin();

        if (!in_array($usuario['rol'] ?? null, $roles, true)) {
            Response::error(
                'SIN_PERMISOS',
                'No tienes permisos para acceder a este recurso.',
                403
            );
        }

        return $usuario;
    }

    /**
     * @param array<string, mixed> $usuario
     */
    public static function login(array $usuario): void
    {
        session_regenerate_id(true);
        $_SESSION['usuario'] = $usuario;
    }

    public static function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies') && !headers_sent()) {
            $params = session_get_cookie_params();

            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'] ?? '/',
                'domain' => $params['domain'] ?? '',
                'secure' => (bool) ($params['secure'] ?? false),
                'httponly' => (bool) ($params['httponly'] ?? true),
                'samesite' => $params['samesite'] ?? 'Lax',
            ]);
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }
}
