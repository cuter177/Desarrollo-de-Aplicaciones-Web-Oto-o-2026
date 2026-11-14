<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Response;
use App\Middleware\Auth;
use App\Models\Cliente;

final class AdminClienteController
{
    public function index(): never
    {
        Auth::requireRole('admin');
        Response::data(Cliente::all());
    }
}
