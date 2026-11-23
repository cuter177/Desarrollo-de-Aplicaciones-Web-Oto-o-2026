<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Helpers\Csrf;
use App\Helpers\Request;
use App\Helpers\Response;
use App\Middleware\Auth;
use App\Models\Cliente;
use App\Models\Plan;
use App\Models\Proyecto;
use App\Models\Solicitud;
use Throwable;

final class AdminSolicitudController
{
    public function index(): never
    {
        Auth::requireRole('admin');

        $filtros = [
            'estado' => isset($_GET['estado']) ? (string) $_GET['estado'] : '',
            'q' => isset($_GET['q']) ? (string) $_GET['q'] : '',
            'plan_id' => isset($_GET['plan_id']) ? (int) $_GET['plan_id'] : 0,
        ];

        Response::data(Solicitud::all($filtros));
    }

    public function show(int $id): never
    {
        Auth::requireRole('admin');
        $solicitud = Solicitud::findById($id);

        if ($solicitud === null) {
            Response::error('NO_ENCONTRADO', 'La solicitud no existe.', 404);
        }

        Response::data($solicitud);
    }

    public function update(int $id): never
    {
        Auth::requireRole('admin');
        Csrf::validate();

        $solicitud = Solicitud::findById($id);

        if ($solicitud === null) {
            Response::error('NO_ENCONTRADO', 'La solicitud no existe.', 404);
        }

        $data = Request::json();
        $cambios = [];

        if (array_key_exists('estado', $data)) {
            $estado = (string) $data['estado'];

            if (!Solicitud::isValidEstado($estado)) {
                Response::error(
                    'VALIDACION',
                    'El estado no es válido.',
                    422,
                    ['estado' => 'Estados permitidos: ' . implode(', ', Solicitud::ESTADOS) . '.']
                );
            }

            $cambios['estado'] = $estado;
        }

        if (array_key_exists('plan_id', $data)) {
            $planId = $data['plan_id'] !== null ? (int) $data['plan_id'] : null;

            if ($planId !== null && Plan::findById($planId) === null) {
                Response::error(
                    'VALIDACION',
                    'El plan seleccionado no existe.',
                    422,
                    ['plan_id' => 'Selecciona un plan válido.']
                );
            }

            $cambios['plan_id'] = $planId;
        }

        if (array_key_exists('cliente_id', $data)) {
            $clienteId = $data['cliente_id'] !== null ? (int) $data['cliente_id'] : null;

            if ($clienteId !== null && Cliente::findById($clienteId) === null) {
                Response::error(
                    'VALIDACION',
                    'El cliente seleccionado no existe.',
                    422,
                    ['cliente_id' => 'Selecciona un cliente válido.']
                );
            }

            $cambios['cliente_id'] = $clienteId;
        }

        if (array_key_exists('notas_admin', $data)) {
            $notas = trim((string) $data['notas_admin']);

            if (strlen($notas) > 5000) {
                Response::error(
                    'VALIDACION',
                    'Las notas son demasiado largas.',
                    422,
                    ['notas_admin' => 'Máximo 5000 caracteres.']
                );
            }

            $cambios['notas_admin'] = $notas !== '' ? $notas : null;
        }

        if ($cambios === []) {
            Response::error('VALIDACION', 'No se recibieron cambios para la solicitud.', 422);
        }

        Solicitud::update($id, $cambios);

        Response::data(Solicitud::findById($id));
    }

    public function convertir(int $id): never
    {
        Auth::requireRole('admin');
        Csrf::validate();

        $solicitud = Solicitud::findById($id);

        if ($solicitud === null) {
            Response::error('NO_ENCONTRADO', 'La solicitud no existe.', 404);
        }

        if ($solicitud['proyecto_id'] !== null) {
            Response::data([
                'proyecto' => Proyecto::findById((int) $solicitud['proyecto_id']),
                'solicitud' => $solicitud,
                'message' => 'La solicitud ya estaba convertida en proyecto.',
            ]);
        }

        if ($solicitud['cliente_id'] === null) {
            Response::error(
                'SOLICITUD_SIN_CLIENTE',
                'Asocia la solicitud a un cliente antes de convertirla en proyecto.',
                409
            );
        }

        $data = Request::json();
        $nombre = trim((string) ($data['nombre'] ?? ''));
        $descripcion = trim((string) ($data['descripcion'] ?? ''));
        $estado = trim((string) ($data['estado'] ?? 'planificado'));

        if ($nombre === '') {
            $empresa = trim((string) ($solicitud['empresa'] ?? ''));

            if ($empresa === '') {
                $empresa = trim((string) ($solicitud['cliente_empresa'] ?? 'Cliente'));
            }

            $nombre = 'Proyecto para ' . $empresa;
        }

        if (strlen($nombre) > 150) {
            $nombre = substr($nombre, 0, 150);
        }

        if ($descripcion === '') {
            $descripcion = (string) ($solicitud['necesidades'] ?? $solicitud['descripcion']);
        }

        if (!in_array($estado, Proyecto::ESTADOS, true)) {
            Response::error(
                'VALIDACION',
                'El estado del proyecto no es válido.',
                422,
                ['estado' => 'Estados permitidos: ' . implode(', ', Proyecto::ESTADOS) . '.']
            );
        }

        $base = Proyecto::slugify($nombre);
        $slug = $base;
        $sufijo = 2;

        while (Proyecto::slugExists($slug)) {
            $slug = $base . '-' . $sufijo;
            $sufijo++;
        }

        $pdo = Database::connection();

        try {
            $pdo->beginTransaction();

            $proyectoId = Proyecto::create([
                'cliente_id' => (int) $solicitud['cliente_id'],
                'nombre' => $nombre,
                'slug' => $slug,
                'descripcion' => $descripcion,
                'estado' => $estado,
                'publicado' => 0,
                'destacado' => 0,
            ]);

            Solicitud::linkProyecto($id, $proyectoId);
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }

        Response::data([
            'proyecto' => Proyecto::findById($proyectoId),
            'solicitud' => Solicitud::findById($id),
        ], 201);
    }
}
