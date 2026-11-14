<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Response;
use App\Models\Plan;
use App\Models\Servicio;

final class CatalogoController
{
    public function planes(): never
    {
        Response::data(Plan::allActive());
    }

    public function servicios(): never
    {
        Response::data(Servicio::allActive());
    }
}
