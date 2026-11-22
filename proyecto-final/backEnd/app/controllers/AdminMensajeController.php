<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Csrf;
use App\Helpers\Request;
use App\Helpers\Response;
use App\Middleware\Auth;
use App\Models\Mensaje;

final class AdminMensajeController
{
    public function index(): never
    {
        Auth::requireRole('admin');

        $filtros = [
            'estado' => isset($_GET['estado']) ? (string) $_GET['estado'] : '',
            'q' => isset($_GET['q']) ? (string) $_GET['q'] : '',
        ];

        if ($filtros['estado'] !== '' && !in_array($filtros['estado'], Mensaje::ESTADOS, true)) {
            Response::error(
                'VALIDACION',
                'El estado no es válido.',
                422,
                ['estado' => 'Estados permitidos: ' . implode(', ', Mensaje::ESTADOS) . '.']
            );
        }

        Response::data(
            Mensaje::all($filtros),
            200,
            ['conteos' => Mensaje::counts()]
        );
    }

    public function show(int $id): never
    {
        Auth::requireRole('admin');
        $mensaje = Mensaje::findById($id);

        if ($mensaje === null) {
            Response::error('NO_ENCONTRADO', 'El mensaje no existe.', 404);
        }

        Response::data($mensaje);
    }

    public function update(int $id): never
    {
        Auth::requireRole('admin');
        Csrf::validate();

        $mensaje = Mensaje::findById($id);

        if ($mensaje === null) {
            Response::error('NO_ENCONTRADO', 'El mensaje no existe.', 404);
        }

        $data = Request::json();

        if (!array_key_exists('estado', $data)) {
            Response::error(
                'VALIDACION',
                'No se recibieron cambios para el mensaje.',
                422
            );
        }

        $estado = (string) $data['estado'];

        if (!in_array($estado, Mensaje::ESTADOS, true)) {
            Response::error(
                'VALIDACION',
                'El estado no es válido.',
                422,
                ['estado' => 'Estados permitidos: ' . implode(', ', Mensaje::ESTADOS) . '.']
            );
        }

        Mensaje::updateEstado($id, $estado);

        Response::data([
            'mensaje' => Mensaje::findById($id),
            'conteos' => Mensaje::counts(),
        ]);
    }
}
