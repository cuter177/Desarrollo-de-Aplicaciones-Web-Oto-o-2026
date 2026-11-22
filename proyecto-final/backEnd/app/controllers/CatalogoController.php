<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Response;
use App\Models\Plan;
use App\Models\Proyecto;
use App\Models\Servicio;
use App\Models\Tecnologia;
use App\Services\SiteConfig;

final class CatalogoController
{
    public function config(): never
    {
        Response::data(SiteConfig::public());
    }

    public function planes(): never
    {
        Response::data(Plan::allActive());
    }

    public function servicios(): never
    {
        Response::data(Servicio::allActive());
    }

    public function servicio(string $slug): never
    {
        $servicio = Servicio::findBySlug($slug);

        if ($servicio === null) {
            Response::error(
                'NO_ENCONTRADO',
                'El servicio no existe o no está disponible.',
                404
            );
        }

        $casos = array_slice(Proyecto::allPublished(), 0, 3);

        Response::data([
            'servicio' => $servicio,
            'casos' => $casos,
        ]);
    }

    public function portafolio(): never
    {
        Response::data(Proyecto::allPublished());
    }

    public function caso(string $slug): never
    {
        $proyecto = Proyecto::findPublishedBySlug($slug);

        if ($proyecto === null) {
            Response::error(
                'NO_ENCONTRADO',
                'El caso no existe o no está publicado.',
                404
            );
        }

        Response::data($proyecto);
    }

    public function tecnologias(): never
    {
        Response::data(Tecnologia::allActive());
    }
}
