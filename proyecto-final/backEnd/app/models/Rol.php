<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;

final class Rol
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function all(): array
    {
        $stmt = Database::connection()->query(
            'SELECT id, nombre, descripcion FROM dbo.Roles ORDER BY id'
        );

        return array_map(
            static fn (array $row): array => [
                'id' => (int) $row['id'],
                'nombre' => (string) $row['nombre'],
                'descripcion' => $row['descripcion'] !== null ? (string) $row['descripcion'] : null,
            ],
            $stmt->fetchAll(\PDO::FETCH_ASSOC)
        );
    }

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
