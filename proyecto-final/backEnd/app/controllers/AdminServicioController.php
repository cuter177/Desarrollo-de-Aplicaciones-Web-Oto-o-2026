<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Csrf;
use App\Helpers\Request;
use App\Helpers\Response;
use App\Middleware\Auth;
use App\Models\Servicio;

final class AdminServicioController
{
    public function index(): never
    {
        Auth::requireRole('admin');
        Response::data(Servicio::allAdmin());
    }

    public function show(int $id): never
    {
        Auth::requireRole('admin');
        $servicio = Servicio::findById($id);

        if ($servicio === null) {
            Response::error('NO_ENCONTRADO', 'El servicio no existe.', 404);
        }

        Response::data($servicio);
    }

    public function store(): never
    {
        Auth::requireRole('admin');
        Csrf::validate();
        $data = Request::json();
        $nombre = trim((string) ($data['nombre'] ?? ''));
        $descripcion = trim((string) ($data['descripcion'] ?? ''));
        $slug = trim((string) ($data['slug'] ?? ''));
        $errors = $this->validarContenido($nombre, $descripcion, $data);

        if ($slug === '') {
            $base = Servicio::slugify($nombre !== '' ? $nombre : 'servicio');
            $slug = $base;
            $sufijo = 2;

            while (Servicio::slugExists($slug)) {
                $slug = $base . '-' . $sufijo;
                $sufijo++;
            }
        } elseif (!$this->slugValido($slug)) {
            $errors['slug'] = 'Usa un slug en minúsculas, con guiones y sin espacios.';
        } elseif (Servicio::slugExists($slug)) {
            Response::error(
                'SLUG_DUPLICADO',
                'Ya existe un servicio con ese slug.',
                409,
                ['slug' => 'Ya existe un servicio con ese slug.']
            );
        }

        if ($errors !== []) {
            Response::error('VALIDACION', 'Revisa los datos del servicio.', 422, $errors);
        }

        $id = Servicio::create([
            'slug' => $slug,
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'beneficios' => $this->beneficios($data['beneficios'] ?? null),
            'icono' => $this->icono($data['icono'] ?? null),
            'activo' => !array_key_exists('activo', $data) || !empty($data['activo']) ? 1 : 0,
            'orden' => $this->orden($data['orden'] ?? 0),
        ]);

        Response::data(Servicio::findById($id), 201);
    }

    public function update(int $id): never
    {
        Auth::requireRole('admin');
        Csrf::validate();
        $servicio = Servicio::findById($id);

        if ($servicio === null) {
            Response::error('NO_ENCONTRADO', 'El servicio no existe.', 404);
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

        if (array_key_exists('descripcion', $data)) {
            $descripcion = trim((string) $data['descripcion']);

            if (strlen($descripcion) < 10 || strlen($descripcion) > 5000) {
                $errors['descripcion'] = 'La descripción debe tener entre 10 y 5000 caracteres.';
            } else {
                $cambios['descripcion'] = $descripcion;
            }
        }

        if (array_key_exists('slug', $data)) {
            $slug = trim((string) $data['slug']);

            if (!$this->slugValido($slug)) {
                $errors['slug'] = 'Usa un slug en minúsculas, con guiones y sin espacios.';
            } elseif (Servicio::slugExists($slug, $id)) {
                Response::error(
                    'SLUG_DUPLICADO',
                    'Ya existe un servicio con ese slug.',
                    409,
                    ['slug' => 'Ya existe un servicio con ese slug.']
                );
            } else {
                $cambios['slug'] = $slug;
            }
        }

        if (array_key_exists('beneficios', $data)) {
            $beneficios = $this->beneficios($data['beneficios']);

            if ($beneficios !== null && strlen($beneficios) > 5000) {
                $errors['beneficios'] = 'Los beneficios no pueden superar los 5000 caracteres.';
            } else {
                $cambios['beneficios'] = $beneficios;
            }
        }

        if (array_key_exists('icono', $data)) {
            $icono = $this->icono($data['icono']);

            if ($icono !== null && strlen($icono) > 80) {
                $errors['icono'] = 'El icono no puede superar los 80 caracteres.';
            } else {
                $cambios['icono'] = $icono;
            }
        }

        if (array_key_exists('orden', $data)) {
            $cambios['orden'] = $this->orden($data['orden']);
        }

        if (array_key_exists('activo', $data)) {
            $cambios['activo'] = !empty($data['activo']) ? 1 : 0;
        }

        if ($errors !== []) {
            Response::error('VALIDACION', 'Revisa los datos del servicio.', 422, $errors);
        }

        if ($cambios === []) {
            Response::error('VALIDACION', 'No se recibieron cambios para el servicio.', 422);
        }

        Servicio::update($id, $cambios);
        Response::data(Servicio::findById($id));
    }

    public function destroy(int $id): never
    {
        Auth::requireRole('admin');
        Csrf::validate();
        $servicio = Servicio::findById($id);

        if ($servicio === null) {
            Response::error('NO_ENCONTRADO', 'El servicio no existe.', 404);
        }

        Servicio::update($id, ['activo' => 0]);
        Response::data(Servicio::findById($id));
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, string>
     */
    private function validarContenido(string $nombre, string $descripcion, array $data): array
    {
        $errors = [];

        if (strlen($nombre) < 2 || strlen($nombre) > 120) {
            $errors['nombre'] = 'El nombre debe tener entre 2 y 120 caracteres.';
        }

        if (strlen($descripcion) < 10 || strlen($descripcion) > 5000) {
            $errors['descripcion'] = 'La descripción debe tener entre 10 y 5000 caracteres.';
        }

        $beneficios = $this->beneficios($data['beneficios'] ?? null);

        if ($beneficios !== null && strlen($beneficios) > 5000) {
            $errors['beneficios'] = 'Los beneficios no pueden superar los 5000 caracteres.';
        }

        $icono = $this->icono($data['icono'] ?? null);

        if ($icono !== null && strlen($icono) > 80) {
            $errors['icono'] = 'El icono no puede superar los 80 caracteres.';
        }

        return $errors;
    }

    private function slugValido(string $slug): bool
    {
        return strlen($slug) <= 120 && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) === 1;
    }

    private function icono(mixed $value): ?string
    {
        $texto = trim((string) ($value ?? ''));

        return $texto !== '' ? $texto : null;
    }

    private function orden(mixed $value): int
    {
        $orden = (int) $value;

        return max(0, min(9999, $orden));
    }

    private function beneficios(mixed $value): ?string
    {
        if (is_array($value)) {
            $lineas = [];

            foreach ($value as $item) {
                $linea = trim((string) $item);

                if ($linea !== '') {
                    $lineas[] = $linea;
                }
            }

            return $lineas === [] ? null : implode("\n", $lineas);
        }

        $texto = trim((string) ($value ?? ''));

        return $texto !== '' ? $texto : null;
    }
}
