<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Csrf;
use App\Helpers\Request;
use App\Helpers\Response;
use App\Middleware\Auth;
use App\Models\Cliente;
use App\Models\Proyecto;
use App\Models\Solicitud;
use App\Models\Usuario;
use PDOException;

final class AdminClienteController
{
    public function index(): never
    {
        Auth::requireRole('admin');
        Response::data(Cliente::all(Request::query('q')));
    }

    public function show(int $id): never
    {
        Auth::requireRole('admin');
        $cliente = Cliente::findById($id);

        if ($cliente === null) {
            Response::error('NO_ENCONTRADO', 'El cliente no existe.', 404);
        }

        $cliente['proyectos'] = Proyecto::allForCliente($id);
        $cliente['solicitudes'] = Solicitud::allForCliente($id);
        Response::data($cliente);
    }

    public function store(): never
    {
        Auth::requireRole('admin');
        Csrf::validate();
        $data = Request::json();
        $usuarioId = (int) ($data['usuario_id'] ?? 0);
        $empresa = trim((string) ($data['empresa'] ?? ''));
        $telefono = trim((string) ($data['telefono'] ?? ''));
        $sitio = trim((string) ($data['sitio_web'] ?? ''));
        $industria = trim((string) ($data['industria'] ?? ''));
        $cargo = trim((string) ($data['cargo'] ?? ''));
        $frase = trim((string) ($data['frase'] ?? ''));
        $ciudad = trim((string) ($data['ciudad'] ?? ''));
        $errors = [];
        $usuario = $usuarioId > 0 ? Usuario::findById($usuarioId) : null;

        if ($usuario === null) {
            $errors['usuario_id'] = 'Selecciona un usuario válido.';
        } elseif ((string) $usuario['rol'] !== 'cliente') {
            $errors['usuario_id'] = 'El usuario debe tener rol cliente.';
        } elseif ($usuario['cliente_id'] !== null) {
            $errors['usuario_id'] = 'Ese usuario ya tiene un cliente vinculado.';
        }

        if (strlen($empresa) < 2 || strlen($empresa) > 150) {
            $errors['empresa'] = 'La empresa debe tener entre 2 y 150 caracteres.';
        }

        if ($telefono !== '' && (strlen($telefono) < 7 || strlen($telefono) > 30)) {
            $errors['telefono'] = 'El teléfono debe tener entre 7 y 30 caracteres.';
        }

        if ($sitio !== '' && (!filter_var($sitio, FILTER_VALIDATE_URL) || strlen($sitio) > 200)) {
            $errors['sitio_web'] = 'Ingresa una URL válida de hasta 200 caracteres.';
        }

        if (strlen($industria) > 100) {
            $errors['industria'] = 'La industria no puede superar los 100 caracteres.';
        }

        if (strlen($cargo) > 80) {
            $errors['cargo'] = 'El cargo no puede superar los 80 caracteres.';
        }

        if (strlen($frase) > 160) {
            $errors['frase'] = 'La frase no puede superar los 160 caracteres.';
        }

        if (strlen($ciudad) > 80) {
            $errors['ciudad'] = 'La ciudad no puede superar los 80 caracteres.';
        }

        if ($errors !== []) {
            Response::error('VALIDACION', 'Revisa los datos del cliente.', 422, $errors);
        }

        try {
            $id = Cliente::create(
                $usuarioId,
                $empresa,
                $telefono !== '' ? $telefono : null,
                $sitio !== '' ? $sitio : null,
                $industria !== '' ? $industria : null,
                $cargo !== '' ? $cargo : null,
                $frase !== '' ? $frase : null,
                $ciudad !== '' ? $ciudad : null
            );
        } catch (PDOException $exception) {
            if (in_array((string) $exception->getCode(), ['23000', '2627'], true)) {
                Response::error(
                    'CLIENTE_DUPLICADO',
                    'Ese usuario ya tiene un cliente vinculado.',
                    409
                );
            }

            throw $exception;
        }

        Response::data(Cliente::findById($id), 201);
    }

    public function update(int $id): never
    {
        Auth::requireRole('admin');
        Csrf::validate();

        if (Cliente::findById($id) === null) {
            Response::error('NO_ENCONTRADO', 'El cliente no existe.', 404);
        }

        $data = Request::json();
        $cambios = [];
        $errors = [];

        if (array_key_exists('empresa', $data)) {
            $empresa = trim((string) $data['empresa']);

            if (strlen($empresa) < 2 || strlen($empresa) > 150) {
                $errors['empresa'] = 'La empresa debe tener entre 2 y 150 caracteres.';
            } else {
                $cambios['empresa'] = $empresa;
            }
        }

        if (array_key_exists('telefono', $data)) {
            $telefono = trim((string) $data['telefono']);

            if ($telefono !== '' && (strlen($telefono) < 7 || strlen($telefono) > 30)) {
                $errors['telefono'] = 'El teléfono debe tener entre 7 y 30 caracteres.';
            } else {
                $cambios['telefono'] = $telefono !== '' ? $telefono : null;
            }
        }

        if (array_key_exists('sitio_web', $data)) {
            $sitio = trim((string) $data['sitio_web']);

            if ($sitio !== '' && (!filter_var($sitio, FILTER_VALIDATE_URL) || strlen($sitio) > 200)) {
                $errors['sitio_web'] = 'Ingresa una URL válida de hasta 200 caracteres.';
            } else {
                $cambios['sitio_web'] = $sitio !== '' ? $sitio : null;
            }
        }

        if (array_key_exists('industria', $data)) {
            $industria = trim((string) $data['industria']);

            if (strlen($industria) > 100) {
                $errors['industria'] = 'La industria no puede superar los 100 caracteres.';
            } else {
                $cambios['industria'] = $industria !== '' ? $industria : null;
            }
        }

        $this->textoOpcional($data, 'cargo', 80, 'El cargo no puede superar los 80 caracteres.', $cambios, $errors);
        $this->textoOpcional($data, 'frase', 160, 'La frase no puede superar los 160 caracteres.', $cambios, $errors);
        $this->textoOpcional($data, 'ciudad', 80, 'La ciudad no puede superar los 80 caracteres.', $cambios, $errors);

        if ($errors !== []) {
            Response::error('VALIDACION', 'Revisa los datos del cliente.', 422, $errors);
        }

        if ($cambios === []) {
            Response::error('VALIDACION', 'No se recibieron cambios para el cliente.', 422);
        }

        Cliente::update($id, $cambios);
        Response::data(Cliente::findById($id));
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $cambios
     * @param array<string, string> $errors
     */
    private function textoOpcional(
        array $data,
        string $campo,
        int $maximo,
        string $mensaje,
        array &$cambios,
        array &$errors
    ): void {
        if (!array_key_exists($campo, $data)) {
            return;
        }

        $valor = trim((string) $data[$campo]);

        if (strlen($valor) > $maximo) {
            $errors[$campo] = $mensaje;
            return;
        }

        $cambios[$campo] = $valor !== '' ? $valor : null;
    }
}
