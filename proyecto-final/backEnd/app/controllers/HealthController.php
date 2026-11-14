<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Helpers\Response;
use Throwable;

final class HealthController
{
    public function info(): never
    {
        Response::data([
            'name' => 'API Proyecto Final',
            'version' => '0.2.0',
            'status' => 'ready',
            'docs' => 'backEnd/docs/03-api.md',
        ]);
    }

    public function health(): never
    {
        $database = 'ok';
        $status = 'ok';
        $httpStatus = 200;

        try {
            Database::connection()->query('SELECT 1');
        } catch (Throwable $exception) {
            error_log('Health check de base de datos falló: ' . $exception->getMessage());
            $database = 'error';
            $status = 'degraded';
            $httpStatus = 503;
        }

        Response::data([
            'status' => $status,
            'database' => $database,
            'version' => '0.2.0',
            'timestamp' => gmdate('c'),
        ], $httpStatus);
    }
}
