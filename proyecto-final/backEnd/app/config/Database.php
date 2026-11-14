<?php

declare(strict_types=1);

namespace App\Config;

use App\Helpers\Env;
use PDO;
use PDOException;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $host = Env::get('DB_HOST', 'mssql');
        $port = Env::get('DB_PORT', '1433');
        $database = Env::get('DB_NAME', 'DevStudioDB');
        $user = Env::get('DB_USER', 'sa');
        $password = Env::get('DB_PASSWORD', '');
        $encrypt = Env::bool('DB_ENCRYPT', true);
        $trustCertificate = Env::bool('DB_TRUST_CERT', true);

        $dsn = sprintf(
            'sqlsrv:Server=%s,%s;Database=%s;Encrypt=%d;TrustServerCertificate=%d',
            $host,
            $port,
            $database,
            $encrypt ? 1 : 0,
            $trustCertificate ? 1 : 0
        );

        try {
            self::$connection = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $exception) {
            throw new PDOException(
                'No se pudo conectar a SQL Server. Revisa las variables DB_* y que el contenedor mssql esté saludable.',
                (int) $exception->getCode(),
                $exception
            );
        }

        return self::$connection;
    }

    public static function reset(): void
    {
        self::$connection = null;
    }
}
