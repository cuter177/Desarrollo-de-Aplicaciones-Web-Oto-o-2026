<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Csrf;
use App\Helpers\Request;
use App\Helpers\Response;
use App\Middleware\Auth;
use App\Models\Reunion;

final class AdminReunionController
{
    public function index(): never
    {
        Auth::requireRole('admin');
        Response::data(Reunion::todas());
    }

    public function update(int $id): never
    {
        Auth::requireRole('admin');
        Csrf::validate();
        $reunion = Reunion::findById($id);

        if ($reunion === null) {
            Response::error('NO_ENCONTRADO', 'La reunión no existe.', 404);
        }

        if ($reunion['estado'] === 'cancelada') {
            Response::error('CONFLICTO', 'La reunión ya fue cancelada.', 409);
        }

        $data = Request::json();
        $cambios = [];
        $errors = [];

        if (array_key_exists('estado', $data)) {
            $estado = trim((string) $data['estado']);

            if (!in_array($estado, ['confirmada', 'rechazada'], true)) {
                $errors['estado'] = 'Responde con confirmada o rechazada.';
            } elseif ($reunion['estado'] !== 'solicitada') {
                $errors['estado'] = 'Esta reunión ya fue respondida.';
            } else {
                $cambios['estado'] = $estado;
            }
        }

        if (array_key_exists('puntos', $data)) {
            $puntos = trim((string) $data['puntos']);

            if (strlen($puntos) > 2000) {
                $errors['puntos'] = 'Los puntos no pueden superar los 2000 caracteres.';
            } else {
                $cambios['puntos'] = $puntos !== '' ? $puntos : null;
            }
        }

        if (array_key_exists('respuesta', $data)) {
            $respuesta = trim((string) $data['respuesta']);

            if (strlen($respuesta) > 1000) {
                $errors['respuesta'] = 'La respuesta no puede superar los 1000 caracteres.';
            } else {
                $cambios['respuesta'] = $respuesta !== '' ? $respuesta : null;
            }
        }

        if ($cambios === [] && $errors === []) {
            $errors['estado'] = 'Indica la respuesta, los puntos o el estado.';
        }

        if ($errors !== []) {
            Response::error('VALIDACION', 'Revisa la respuesta de la reunión.', 422, $errors);
        }

        Reunion::responder($id, $cambios);
        Response::data(Reunion::findById($id));
    }
}
