<?php

declare(strict_types=1);

namespace App\Helpers;

final class Csrf
{
    private const SESSION_KEY = 'csrf_token';

    public static function token(): string
    {
        if (!isset($_SESSION[self::SESSION_KEY]) || !is_string($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::SESSION_KEY];
    }

    public static function rotate(): string
    {
        $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));

        return $_SESSION[self::SESSION_KEY];
    }

    public static function validate(): void
    {
        $token = Request::header('X-CSRF-Token');

        if ($token === null || !hash_equals(self::token(), $token)) {
            Response::error(
                'CSRF_INVALIDO',
                'El token de seguridad no es válido o expiró.',
                403
            );
        }
    }
}
