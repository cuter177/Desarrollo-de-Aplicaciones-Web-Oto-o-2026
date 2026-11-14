<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Helpers\Csrf;
use App\Helpers\RateLimiter;
use App\Helpers\Request;
use App\Helpers\Response;
use App\Helpers\Validator;
use App\Middleware\Auth;
use App\Models\Cliente;
use App\Models\Rol;
use App\Models\Usuario;
use PDOException;
use Throwable;

final class AuthController
{
    public function csrf(): never
    {
        Response::data(['csrf_token' => Csrf::token()]);
    }

    public function login(): never
    {
        Csrf::validate();
        $data = Request::json();
        $errors = Validator::login($data);

        if ($errors !== []) {
            Response::error('VALIDACION', 'Los datos enviados no son válidos.', 422, $errors);
        }

        $email = strtolower(trim((string) ($data['email'] ?? '')));
        $password = (string) ($data['password'] ?? '');
        $ipKey = 'login:ip:' . Request::ip();
        $emailKey = 'login:email:' . $email;

        RateLimiter::enforce($ipKey, 5, 900);
        RateLimiter::enforce($emailKey, 5, 900);

        $usuario = Usuario::findByEmail($email);
        $credencialesValidas = $usuario !== null
            && (int) $usuario['activo'] === 1
            && password_verify($password, (string) $usuario['password_hash']);

        if (!$credencialesValidas) {
            RateLimiter::hit($ipKey);
            RateLimiter::hit($emailKey);

            Response::error(
                'CREDENCIALES_INVALIDAS',
                'El correo o la contraseña no son correctos.',
                401
            );
        }

        if (password_needs_rehash((string) $usuario['password_hash'], PASSWORD_DEFAULT)) {
            Usuario::updatePassword(
                (int) $usuario['id'],
                password_hash($password, PASSWORD_DEFAULT)
            );
        }

        RateLimiter::clear($emailKey);

        $publico = Usuario::publicData($usuario);
        Auth::login($publico);
        Csrf::rotate();
        Usuario::touchLastAccess((int) $usuario['id']);

        Response::data([
            'usuario' => $publico,
            'csrf_token' => Csrf::token(),
        ]);
    }

    public function logout(): never
    {
        Auth::requireLogin();
        Csrf::validate();
        Auth::logout();

        Response::noContent();
    }

    public function me(): never
    {
        $sesion = Auth::requireLogin();
        $usuario = Usuario::findById((int) $sesion['id']);

        if ($usuario === null || (int) $usuario['activo'] !== 1) {
            Auth::logout();

            Response::error(
                'NO_AUTENTICADO',
                'La sesión ya no es válida. Inicia sesión de nuevo.',
                401
            );
        }

        $_SESSION['usuario'] = Usuario::publicData($usuario);

        Response::data([
            'usuario' => $_SESSION['usuario'],
            'csrf_token' => Csrf::token(),
        ]);
    }

    public function register(): never
    {
        Csrf::validate();
        $data = Request::json();
        $errors = Validator::registration($data);

        if ($errors !== []) {
            Response::error('VALIDACION', 'Los datos enviados no son válidos.', 422, $errors);
        }

        $nombre = trim((string) ($data['nombre'] ?? ''));
        $email = strtolower(trim((string) ($data['email'] ?? '')));
        $empresa = trim((string) ($data['empresa'] ?? ''));
        $telefono = trim((string) ($data['telefono'] ?? ''));
        $password = (string) ($data['password'] ?? '');

        if (Usuario::emailExists($email)) {
            Response::error(
                'CORREO_DUPLICADO',
                'Ya existe una cuenta con ese correo electrónico.',
                409,
                ['email' => 'Este correo ya está registrado.']
            );
        }

        $rolId = Rol::idByNombre('cliente');

        if ($rolId === null) {
            Response::error(
                'CONFIGURACION_INCOMPLETA',
                'No existe el rol cliente. Ejecuta seed.sql antes de registrar usuarios.',
                500
            );
        }

        $pdo = Database::connection();

        try {
            $pdo->beginTransaction();

            $usuarioId = Usuario::create(
                $nombre,
                $email,
                password_hash($password, PASSWORD_DEFAULT),
                $rolId
            );

            Cliente::create(
                $usuarioId,
                $empresa,
                $telefono !== '' ? $telefono : null
            );

            $pdo->commit();
        } catch (PDOException $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            if (in_array((string) $exception->getCode(), ['23000', '2627'], true)) {
                Response::error(
                    'CORREO_DUPLICADO',
                    'Ya existe una cuenta con ese correo electrónico.',
                    409
                );
            }

            throw $exception;
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }

        $usuario = Usuario::findById($usuarioId);

        if ($usuario === null) {
            Response::error(
                'ERROR_INTERNO',
                'No se pudo recuperar el usuario recién creado.',
                500
            );
        }

        Response::data(['usuario' => Usuario::publicData($usuario)], 201);
    }

    public function changePassword(): never
    {
        $sesion = Auth::requireLogin();
        Csrf::validate();
        $data = Request::json();
        $errors = Validator::changePassword($data);

        if ($errors !== []) {
            Response::error('VALIDACION', 'Los datos enviados no son válidos.', 422, $errors);
        }

        $usuario = Usuario::findById((int) $sesion['id']);

        if ($usuario === null) {
            Response::error('NO_AUTENTICADO', 'La sesión ya no es válida.', 401);
        }

        if (!password_verify((string) ($data['password_actual'] ?? ''), (string) $usuario['password_hash'])) {
            Response::error(
                'CREDENCIALES_INVALIDAS',
                'La contraseña actual no es correcta.',
                401,
                ['password_actual' => 'La contraseña actual no es correcta.']
            );
        }

        Usuario::updatePassword(
            (int) $usuario['id'],
            password_hash((string) ($data['password_nueva'] ?? ''), PASSWORD_DEFAULT)
        );

        Response::data(['message' => 'Contraseña actualizada correctamente.']);
    }
}
