<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Csrf;
use App\Helpers\RateLimiter;
use App\Helpers\Request;
use App\Helpers\Response;
use App\Helpers\Validator;
use App\Models\Mensaje;

final class ContactoController
{
    public function create(): never
    {
        Csrf::validate();

        $ipKey = 'contacto:ip:' . Request::ip();
        RateLimiter::enforce($ipKey, 5, 3600);

        $data = Request::json();

        // Honeypot: un humano no ve ni completa este campo.
        if (trim((string) ($data['website'] ?? '')) !== '') {
            Response::data([
                'id' => null,
                'estado' => 'nuevo',
                'mensaje' => 'Mensaje recibido. Te contactaremos pronto.',
            ], 201);
        }

        $errors = Validator::contacto($data);

        if ($errors !== []) {
            Response::error('VALIDACION', 'Revisa los datos del mensaje.', 422, $errors);
        }

        $id = Mensaje::create([
            'nombre' => trim((string) $data['nombre']),
            'email' => strtolower(trim((string) $data['email'])),
            'asunto' => trim((string) $data['asunto']),
            'mensaje' => trim((string) $data['mensaje']),
        ]);

        RateLimiter::hit($ipKey);

        Response::data([
            'id' => $id,
            'estado' => 'nuevo',
            'mensaje' => 'Mensaje recibido. Te contactaremos pronto.',
        ], 201);
    }
}
