<?php

declare(strict_types=1);

/**
 * Bootstrap del backend.
 *
 * Carga el autoloader, las variables de entorno, la configuración de errores
 * y la sesión. En CLI (scripts) no inicia sesión ni envía cabeceras HTTP.
 */

use App\Helpers\Env;

define('BASE_PATH', dirname(__DIR__));

$prefixes = [
    'App\\Controllers\\' => BASE_PATH . '/app/controllers/',
    'App\\Models\\' => BASE_PATH . '/app/models/',
    'App\\Helpers\\' => BASE_PATH . '/app/helpers/',
    'App\\Middleware\\' => BASE_PATH . '/app/middleware/',
    'App\\Services\\' => BASE_PATH . '/app/services/',
    'App\\Config\\' => BASE_PATH . '/app/config/',];

spl_autoload_register(static function (string $class) use ($prefixes): void {
    foreach ($prefixes as $prefix => $directory) {
        if (!str_starts_with($class, $prefix)) {
            continue;
        }

        $relative = substr($class, strlen($prefix));
        $file = $directory . str_replace('\\', '/', $relative) . '.php';

        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

Env::load(dirname(BASE_PATH) . '/.env', BASE_PATH . '/.env');

$isProduction = Env::get('APP_ENV', 'development') === 'production';

date_default_timezone_set(Env::get('APP_TIMEZONE', 'America/Mexico_City'));
error_reporting(E_ALL);
ini_set('display_errors', $isProduction ? '0' : '1');
ini_set('log_errors', '1');

if (PHP_SAPI !== 'cli') {
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    }

    if (session_status() !== PHP_SESSION_ACTIVE) {
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', Env::get('SESSION_SAMESITE', 'Lax'));
        ini_set('session.gc_maxlifetime', '7200');

        session_name(Env::get('SESSION_NAME', 'devstudio_session'));
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => Env::bool('SESSION_SECURE', false),
            'httponly' => true,
            'samesite' => Env::get('SESSION_SAMESITE', 'Lax'),
        ]);
        session_start();
    }
}
