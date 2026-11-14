<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Csrf;
use App\Helpers\Request;
use App\Helpers\Response;
use App\Middleware\Auth;
use App\Models\Cliente;
use App\Models\Proyecto;

final class AdminProyectoController
{
    public function index(): never
    {
        Auth::requireRole('admin');
        Response::data(Proyecto::all());
    }

    public function show(int $id): never
    {
        Auth::requireRole('admin');
        $proyecto = Proyecto::findById($id);

        if ($proyecto === null) {
            Response::error('NO_ENCONTRADO', 'El proyecto no existe.', 404);
        }

        Response::data($proyecto);
    }

    public function store(): never
    {
        Auth::requireRole('admin');
        Csrf::validate();

        $data = Request::json();
        $clienteId = (int) ($data['cliente_id'] ?? 0);
        $nombre = trim((string) ($data['nombre'] ?? ''));
        $descripcion = trim((string) ($data['descripcion'] ?? ''));
        $estado = trim((string) ($data['estado'] ?? 'planificado'));

        $errors = [];

        if ($clienteId <= 0 || Cliente::findById($clienteId) === null) {
            $errors['cliente_id'] = 'Selecciona un cliente válido.';
        }

        if (strlen($nombre) < 3 || strlen($nombre) > 150) {
            $errors['nombre'] = 'El nombre debe tener entre 3 y 150 caracteres.';
        }

        if (strlen($descripcion) < 10 || strlen($descripcion) > 5000) {
            $errors['descripcion'] = 'La descripción debe tener entre 10 y 5000 caracteres.';
        }

        if (!in_array($estado, Proyecto::ESTADOS, true)) {
            $errors['estado'] = 'Selecciona un estado válido.';
        }

        if ($errors !== []) {
            Response::error('VALIDACION', 'Revisa los datos del proyecto.', 422, $errors);
        }

        $base = Proyecto::slugify($nombre);
        $slug = $base;
        $sufijo = 2;

        while (Proyecto::slugExists($slug)) {
            $slug = $base . '-' . $sufijo;
            $sufijo++;
        }

        $id = Proyecto::create([
            'cliente_id' => $clienteId,
            'nombre' => $nombre,
            'slug' => $slug,
            'descripcion' => $descripcion,
            'estado' => $estado,
            'fecha_inicio' => self::nullableDate($data['fecha_inicio'] ?? null),
            'fecha_fin' => self::nullableDate($data['fecha_fin'] ?? null),
            'url_demo' => self::nullableString($data['url_demo'] ?? null),
            'publicado' => !empty($data['publicado']) ? 1 : 0,
            'destacado' => !empty($data['destacado']) ? 1 : 0,
        ]);

        Response::data(Proyecto::findById($id), 201);
    }

    public function update(int $id): never
    {
        Auth::requireRole('admin');
        Csrf::validate();

        $proyecto = Proyecto::findById($id);

        if ($proyecto === null) {
            Response::error('NO_ENCONTRADO', 'El proyecto no existe.', 404);
        }

        $data = Request::json();
        $cambios = [];

        if (array_key_exists('cliente_id', $data)) {
            $clienteId = (int) $data['cliente_id'];

            if ($clienteId <= 0 || Cliente::findById($clienteId) === null) {
                Response::error(
                    'VALIDACION',
                    'El cliente no es válido.',
                    422,
                    ['cliente_id' => 'Selecciona un cliente válido.']
                );
            }

            $cambios['cliente_id'] = $clienteId;
        }

        if (array_key_exists('nombre', $data)) {
            $nombre = trim((string) $data['nombre']);

            if (strlen($nombre) < 3 || strlen($nombre) > 150) {
                Response::error(
                    'VALIDACION',
                    'El nombre no es válido.',
                    422,
                    ['nombre' => 'Debe tener entre 3 y 150 caracteres.']
                );
            }

            $cambios['nombre'] = $nombre;
        }

        if (array_key_exists('descripcion', $data)) {
            $descripcion = trim((string) $data['descripcion']);

            if (strlen($descripcion) < 10 || strlen($descripcion) > 5000) {
                Response::error(
                    'VALIDACION',
                    'La descripción no es válida.',
                    422,
                    ['descripcion' => 'Debe tener entre 10 y 5000 caracteres.']
                );
            }

            $cambios['descripcion'] = $descripcion;
        }

        if (array_key_exists('estado', $data)) {
            $estado = (string) $data['estado'];

            if (!in_array($estado, Proyecto::ESTADOS, true)) {
                Response::error(
                    'VALIDACION',
                    'El estado no es válido.',
                    422,
                    ['estado' => 'Estados permitidos: ' . implode(', ', Proyecto::ESTADOS) . '.']
                );
            }

            $cambios['estado'] = $estado;
        }

        foreach (['fecha_inicio', 'fecha_fin'] as $campo) {
            if (array_key_exists($campo, $data)) {
                $cambios[$campo] = self::nullableDate($data[$campo]);
            }
        }

        if (array_key_exists('url_demo', $data)) {
            $cambios['url_demo'] = self::nullableString($data['url_demo']);
        }

        foreach (['publicado', 'destacado'] as $campo) {
            if (array_key_exists($campo, $data)) {
                $cambios[$campo] = !empty($data[$campo]) ? 1 : 0;
            }
        }

        if ($cambios === []) {
            Response::error('VALIDACION', 'No se recibieron cambios para el proyecto.', 422);
        }

        Proyecto::update($id, $cambios);

        Response::data(Proyecto::findById($id));
    }

    private static function nullableDate(mixed $value): ?string
    {
        $texto = trim((string) ($value ?? ''));

        return $texto !== '' ? $texto : null;
    }

    private static function nullableString(mixed $value): ?string
    {
        $texto = trim((string) ($value ?? ''));

        return $texto !== '' ? $texto : null;
    }
}
