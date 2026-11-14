<?php

declare(strict_types=1);

namespace App\Helpers;

final class Validator
{
    /**
     * @return array<string, string>
     */
    public static function login(array $data): array
    {
        $errors = [];
        $email = self::text($data, 'email');
        $password = (string) ($data['password'] ?? '');

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
            $errors['email'] = 'Ingresa un correo electrónico válido.';
        }

        if ($password === '') {
            $errors['password'] = 'Ingresa tu contraseña.';
        } elseif (strlen($password) > 200) {
            $errors['password'] = 'La contraseña es demasiado larga.';
        }

        return $errors;
    }

    /**
     * @return array<string, string>
     */
    public static function registration(array $data): array
    {
        $errors = [];
        $nombre = self::text($data, 'nombre');
        $email = self::text($data, 'email');
        $empresa = self::text($data, 'empresa');
        $telefono = self::text($data, 'telefono');
        $password = (string) ($data['password'] ?? '');
        $confirmacion = (string) ($data['password_confirmacion'] ?? '');

        if (strlen($nombre) < 2 || strlen($nombre) > 120) {
            $errors['nombre'] = 'El nombre debe tener entre 2 y 120 caracteres.';
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
            $errors['email'] = 'Ingresa un correo electrónico válido.';
        }

        if (strlen($empresa) < 2 || strlen($empresa) > 150) {
            $errors['empresa'] = 'La empresa debe tener entre 2 y 150 caracteres.';
        }

        if ($telefono !== '' && (strlen($telefono) < 7 || strlen($telefono) > 30)) {
            $errors['telefono'] = 'El teléfono debe tener entre 7 y 30 caracteres.';
        }

        $passwordError = self::password($password);

        if ($passwordError !== null) {
            $errors['password'] = $passwordError;
        }

        if ($password !== $confirmacion) {
            $errors['password_confirmacion'] = 'Las contraseñas no coinciden.';
        }

        if (($data['acepta_terminos'] ?? false) !== true) {
            $errors['acepta_terminos'] = 'Debes aceptar los términos para crear la cuenta.';
        }

        return $errors;
    }

    /**
     * @return array<string, string>
     */
    public static function changePassword(array $data): array
    {
        $errors = [];
        $actual = (string) ($data['password_actual'] ?? '');
        $nueva = (string) ($data['password_nueva'] ?? '');
        $confirmacion = (string) ($data['password_confirmacion'] ?? '');

        if ($actual === '') {
            $errors['password_actual'] = 'Ingresa tu contraseña actual.';
        }

        $passwordError = self::password($nueva);

        if ($passwordError !== null) {
            $errors['password_nueva'] = $passwordError;
        }

        if ($nueva !== $confirmacion) {
            $errors['password_confirmacion'] = 'Las contraseñas no coinciden.';
        }

        return $errors;
    }

    public static function password(string $password): ?string
    {
        if (strlen($password) < 8) {
            return 'La contraseña debe tener al menos 8 caracteres.';
        }

        if (strlen($password) > 200) {
            return 'La contraseña no puede superar los 200 caracteres.';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            return 'La contraseña debe incluir al menos una mayúscula.';
        }

        if (!preg_match('/[a-z]/', $password)) {
            return 'La contraseña debe incluir al menos una minúscula.';
        }

        if (!preg_match('/\d/', $password)) {
            return 'La contraseña debe incluir al menos un número.';
        }

        return null;
    }

    private static function text(array $data, string $key): string
    {
        $value = $data[$key] ?? '';

        return is_string($value) ? trim($value) : '';
    }
}
