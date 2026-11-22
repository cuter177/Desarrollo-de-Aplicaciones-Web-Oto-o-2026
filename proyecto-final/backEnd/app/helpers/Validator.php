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

    /**
     * @param list<string> $serviciosValidos
     * @return array<string, string>
     */
    public static function diagnostico(array $data, array $serviciosValidos): array
    {
        $errors = [];
        $nombre = self::text($data, 'nombre_contacto');
        $email = self::text($data, 'email');
        $empresa = self::text($data, 'empresa');
        $giro = self::text($data, 'giro');
        $telefono = self::text($data, 'telefono');
        $necesidades = self::text($data, 'necesidades');
        $procesos = self::text($data, 'procesos_automatizar');
        $servicios = $data['servicios_interes'] ?? null;
        $meta = $data['meta_crecimiento_pct'] ?? null;
        $horas = $data['ahorro_horas_semana'] ?? null;
        $presupuesto = $data['presupuesto_estimado'] ?? null;
        $plazo = self::text($data, 'plazo_deseado');
        $etapa = self::text($data, 'etapa_decision');

        if (strlen($nombre) < 2 || strlen($nombre) > 120) {
            $errors['nombre_contacto'] = 'El nombre debe tener entre 2 y 120 caracteres.';
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
            $errors['email'] = 'Ingresa un correo electrónico válido.';
        }

        if (strlen($empresa) < 2 || strlen($empresa) > 150) {
            $errors['empresa'] = 'La empresa debe tener entre 2 y 150 caracteres.';
        }

        if ($giro !== '' && strlen($giro) > 100) {
            $errors['giro'] = 'El giro no puede superar los 100 caracteres.';
        }

        if ($telefono !== '' && (strlen($telefono) < 7 || strlen($telefono) > 30)) {
            $errors['telefono'] = 'El teléfono debe tener entre 7 y 30 caracteres.';
        }

        if (strlen($necesidades) < 10 || strlen($necesidades) > 5000) {
            $errors['necesidades'] = 'Describe tu necesidad principal (entre 10 y 5000 caracteres).';
        }

        if ($procesos !== '' && strlen($procesos) > 5000) {
            $errors['procesos_automatizar'] = 'El texto no puede superar los 5000 caracteres.';
        }

        if (!is_array($servicios) || $servicios === []) {
            $errors['servicios_interes'] = 'Selecciona al menos un servicio.';
        } else {
            $validos = array_values(array_intersect($servicios, $serviciosValidos));

            if ($validos === []) {
                $errors['servicios_interes'] = 'Los servicios seleccionados no son válidos.';
            }
        }

        if (!is_numeric($meta) || (float) $meta < 0 || (float) $meta > 1000) {
            $errors['meta_crecimiento_pct'] = 'Indica una meta de crecimiento entre 0 y 1000 %.';
        }

        if (!is_numeric($horas) || (int) $horas < 0 || (int) $horas > 168) {
            $errors['ahorro_horas_semana'] = 'Indica entre 0 y 168 horas por semana.';
        }

        if (!is_numeric($presupuesto) || (float) $presupuesto < 0) {
            $errors['presupuesto_estimado'] = 'Indica un presupuesto válido.';
        }

        $plazos = ['1 a 2 meses', '1 a 3 meses', '2 a 4 meses', '3 a 6 meses', 'más de 6 meses', 'listo para iniciar'];

        if (!in_array($plazo, $plazos, true)) {
            $errors['plazo_deseado'] = 'Selecciona un plazo válido.';
        }

        $etapas = ['explorando', 'evaluando', 'comparando opciones', 'lista para iniciar'];

        if (!in_array($etapa, $etapas, true)) {
            $errors['etapa_decision'] = 'Selecciona una etapa válida.';
        }

        return $errors;
    }

    /**
     * @return array<string, string>
     */
    public static function contacto(array $data): array
    {
        $errors = [];
        $nombre = self::text($data, 'nombre');
        $email = self::text($data, 'email');
        $asunto = self::text($data, 'asunto');
        $mensaje = self::text($data, 'mensaje');

        if (strlen($nombre) < 2 || strlen($nombre) > 120) {
            $errors['nombre'] = 'El nombre debe tener entre 2 y 120 caracteres.';
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
            $errors['email'] = 'Ingresa un correo electrónico válido.';
        }

        if (strlen($asunto) < 3 || strlen($asunto) > 150) {
            $errors['asunto'] = 'El asunto debe tener entre 3 y 150 caracteres.';
        }

        if (strlen($mensaje) < 10 || strlen($mensaje) > 5000) {
            $errors['mensaje'] = 'El mensaje debe tener entre 10 y 5000 caracteres.';
        }

        if (($data['acepta_privacidad'] ?? false) !== true) {
            $errors['acepta_privacidad'] = 'Debes aceptar el aviso de privacidad.';
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
