<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Helpers\Csrf;
use App\Helpers\Request;
use App\Helpers\Response;
use App\Helpers\Validator;
use App\Middleware\Auth;
use App\Models\Cliente;
use App\Models\Rol;
use App\Models\Usuario;
use PDOException;
use Throwable;

final class AdminUsuarioController
{
    public function index(): never
    {
        Auth::requireRole('admin');
        $filters = [];
        $q = Request::query('q');
        $rol = Request::query('rol');
        $activo = Request::query('activo');

        if ($q !== null) {
            $filters['q'] = $q;
        }

        if ($rol !== null) {
            if (Rol::idByNombre($rol) === null) {
                Response::error('VALIDACION', 'El rol no existe.', 422, ['rol' => 'Selecciona un rol válido.']);
            }

            $filters['rol'] = $rol;
        }

        if ($activo !== null) {
            if (!in_array($activo, ['0', '1'], true)) {
                Response::error('VALIDACION', 'El filtro de estado no es válido.', 422, ['activo' => 'Usa 0 o 1.']);
            }

            $filters['activo'] = (int) $activo;
        }

        Response::data(Usuario::all($filters));
    }

    public function show(int $id): never
    {
        Auth::requireRole('admin');
        $usuario = Usuario::findPublicById($id);

        if ($usuario === null) {
            Response::error('NO_ENCONTRADO', 'El usuario no existe.', 404);
        }

        Response::data($usuario);
    }

    public function roles(): never
    {
        Auth::requireRole('admin');
        Response::data(Rol::all());
    }

    public function store(): never
    {
        Auth::requireRole('admin');
        Csrf::validate();
        $data = Request::json();
        $nombre = trim((string) ($data['nombre'] ?? ''));
        $email = strtolower(trim((string) ($data['email'] ?? '')));
        $rol = trim((string) ($data['rol'] ?? ''));
        $password = (string) ($data['password'] ?? '');
        $empresa = trim((string) ($data['empresa'] ?? ''));
        $telefono = trim((string) ($data['telefono'] ?? ''));
        $activo = !array_key_exists('activo', $data) || !empty($data['activo']);
        $errors = [];

        if (strlen($nombre) < 2 || strlen($nombre) > 120) {
            $errors['nombre'] = 'El nombre debe tener entre 2 y 120 caracteres.';
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
            $errors['email'] = 'Ingresa un correo electrónico válido.';
        }

        $rolId = Rol::idByNombre($rol);

        if ($rolId === null) {
            $errors['rol'] = 'Selecciona un rol válido.';
        }

        $passwordError = Validator::password($password);

        if ($passwordError !== null) {
            $errors['password'] = $passwordError;
        }

        if ($rol === 'cliente' && $empresa !== '' && (strlen($empresa) < 2 || strlen($empresa) > 150)) {
            $errors['empresa'] = 'La empresa debe tener entre 2 y 150 caracteres.';
        }

        if ($rol !== 'cliente' && $empresa !== '') {
            $errors['empresa'] = 'Solo un usuario cliente puede vincularse a una empresa.';
        }

        if ($telefono !== '' && (strlen($telefono) < 7 || strlen($telefono) > 30)) {
            $errors['telefono'] = 'El teléfono debe tener entre 7 y 30 caracteres.';
        }

        if ($errors !== []) {
            Response::error('VALIDACION', 'Revisa los datos del usuario.', 422, $errors);
        }

        if (Usuario::emailExists($email)) {
            Response::error(
                'CORREO_DUPLICADO',
                'Ya existe una cuenta con ese correo electrónico.',
                409,
                ['email' => 'Este correo ya está registrado.']
            );
        }

        $pdo = Database::connection();

        try {
            $pdo->beginTransaction();
            $id = Usuario::create(
                $nombre,
                $email,
                password_hash($password, PASSWORD_DEFAULT),
                (int) $rolId,
                $activo
            );

            if ($rol === 'cliente' && $empresa !== '') {
                Cliente::create($id, $empresa, $telefono !== '' ? $telefono : null);
            }

            $pdo->commit();
        } catch (PDOException $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            if (in_array((string) $exception->getCode(), ['23000', '2627'], true)) {
                Response::error('CORREO_DUPLICADO', 'Ya existe una cuenta con ese correo electrónico.', 409);
            }

            throw $exception;
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }

        $usuario = Usuario::findPublicById($id);
        Response::data($usuario, 201);
    }

    public function update(int $id): never
    {
        $admin = Auth::requireRole('admin');
        Csrf::validate();
        $actual = Usuario::findPublicById($id);

        if ($actual === null) {
            Response::error('NO_ENCONTRADO', 'El usuario no existe.', 404);
        }

        $data = Request::json();
        $cambios = [];
        $errors = [];

        if (array_key_exists('nombre', $data)) {
            $nombre = trim((string) $data['nombre']);

            if (strlen($nombre) < 2 || strlen($nombre) > 120) {
                $errors['nombre'] = 'El nombre debe tener entre 2 y 120 caracteres.';
            } else {
                $cambios['nombre'] = $nombre;
            }
        }

        if (array_key_exists('email', $data)) {
            $email = strtolower(trim((string) $data['email']));

            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
                $errors['email'] = 'Ingresa un correo electrónico válido.';
            } elseif (Usuario::emailExists($email, $id)) {
                $errors['email'] = 'Este correo ya está registrado.';
            } else {
                $cambios['email'] = $email;
            }
        }

        if (array_key_exists('rol', $data)) {
            $rolId = Rol::idByNombre(trim((string) $data['rol']));

            if ($rolId === null) {
                $errors['rol'] = 'Selecciona un rol válido.';
            } elseif ((int) $admin['id'] === $id && (string) $data['rol'] !== 'admin') {
                $errors['rol'] = 'No puedes cambiar tu propio rol.';
            } else {
                $cambios['rol_id'] = $rolId;
            }
        }

        if (array_key_exists('activo', $data)) {
            $activo = !empty($data['activo']);

            if ((int) $admin['id'] === $id && !$activo) {
                $errors['activo'] = 'No puedes desactivar tu propia cuenta.';
            } else {
                $cambios['activo'] = $activo ? 1 : 0;
            }
        }

        if ($errors !== []) {
            Response::error('VALIDACION', 'Revisa los datos del usuario.', 422, $errors);
        }

        if ($cambios === []) {
            Response::error('VALIDACION', 'No se recibieron cambios para el usuario.', 422);
        }

        Usuario::updateAccount($id, $cambios);
        Response::data(Usuario::findPublicById($id));
    }

    public function estado(int $id): never
    {
        $this->update($id);
    }
}
