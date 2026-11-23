<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Csrf;
use App\Helpers\Request;
use App\Helpers\Response;
use App\Middleware\Auth;
use App\Models\Tecnologia;
use PDOException;

final class AdminTecnologiaController
{
    public function index(): never
    {
        Auth::requireRole('admin');
        Response::data(Tecnologia::allAdmin());
    }

    public function store(): never
    {
        Auth::requireRole('admin');
        Csrf::validate();
        $data = Request::json();
        [$payload, $errors] = $this->validar($data, null);

        if ($errors !== []) {
            Response::error('VALIDACION', 'Revisa los datos de la tecnología.', 422, $errors);
        }

        try {
            $id = Tecnologia::create($payload);
        } catch (PDOException $exception) {
            if (in_array((string) $exception->getCode(), ['23000', '2627'], true)) {
                Response::error(
                    'NOMBRE_DUPLICADO',
                    'Ya existe una tecnología con ese nombre.',
                    409,
                    ['nombre' => 'Ese nombre ya está registrado.']
                );
            }

            throw $exception;
        }

        Response::data(Tecnologia::findById($id), 201);
    }

    public function update(int $id): never
    {
        Auth::requireRole('admin');
        Csrf::validate();

        if (Tecnologia::findById($id) === null) {
            Response::error('NO_ENCONTRADO', 'La tecnología no existe.', 404);
        }

        $data = Request::json();
        [$payload, $errors] = $this->validar($data, $id, true);

        if ($errors !== []) {
            Response::error('VALIDACION', 'Revisa los datos de la tecnología.', 422, $errors);
        }

        if ($payload === []) {
            Response::error('VALIDACION', 'No se recibieron cambios para la tecnología.', 422);
        }

        try {
            Tecnologia::update($id, $payload);
        } catch (PDOException $exception) {
            if (in_array((string) $exception->getCode(), ['23000', '2627'], true)) {
                Response::error(
                    'NOMBRE_DUPLICADO',
                    'Ya existe una tecnología con ese nombre.',
                    409,
                    ['nombre' => 'Ese nombre ya está registrado.']
                );
            }

            throw $exception;
        }

        Response::data(Tecnologia::findById($id));
    }

    public function destroy(int $id): never
    {
        Auth::requireRole('admin');
        Csrf::validate();

        if (Tecnologia::findById($id) === null) {
            Response::error('NO_ENCONTRADO', 'La tecnología no existe.', 404);
        }

        Tecnologia::update($id, ['activo' => 0]);
        Response::data(Tecnologia::findById($id));
    }

    /**
     * @param array<string, mixed> $data
     * @return array{0: array<string, mixed>, 1: array<string, string>}
     */
    private function validar(array $data, ?int $exceptId, bool $parcial = false): array
    {
        $payload = [];
        $errors = [];

        if (!$parcial || array_key_exists('nombre', $data)) {
            $nombre = trim((string) ($data['nombre'] ?? ''));

            if (strlen($nombre) < 2 || strlen($nombre) > 80) {
                $errors['nombre'] = 'El nombre debe tener entre 2 y 80 caracteres.';
            } elseif (Tecnologia::nombreExists($nombre, $exceptId)) {
                $errors['nombre'] = 'Ya existe una tecnología con ese nombre.';
            } else {
                $payload['nombre'] = $nombre;
            }
        }

        if (!$parcial || array_key_exists('categoria', $data)) {
            $categoria = trim((string) ($data['categoria'] ?? ''));

            if (strlen($categoria) > 60) {
                $errors['categoria'] = 'La categoría no puede superar los 60 caracteres.';
            } else {
                $payload['categoria'] = $categoria !== '' ? $categoria : null;
            }
        }

        if (!$parcial || array_key_exists('icono', $data)) {
            $icono = trim((string) ($data['icono'] ?? ''));

            if (strlen($icono) > 80) {
                $errors['icono'] = 'El icono no puede superar los 80 caracteres.';
            } else {
                $payload['icono'] = $icono !== '' ? $icono : null;
            }
        }

        if (array_key_exists('activo', $data) || !$parcial) {
            $payload['activo'] = !array_key_exists('activo', $data) || !empty($data['activo']) ? 1 : 0;
        }

        return [$payload, $errors];
    }
}
