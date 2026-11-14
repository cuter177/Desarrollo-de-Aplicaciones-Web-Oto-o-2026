<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;

final class Rol
{
    public static function idByNombre(string $nombre): ?int
    {
        $stmt = Database::connection()->prepare(
            'SELECT id FROM dbo.Roles WHERE nombre = :nombre'
        );
        $stmt->execute([':nombre' => $nombre]);
        $id = $stmt->fetchColumn();

        return $id === false ? null : (int) $id;
    }
}
