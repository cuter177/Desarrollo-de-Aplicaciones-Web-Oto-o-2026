<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;

final class Tecnologia
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function allActive(): array
    {
        $stmt = Database::connection()->query(
            'SELECT id, nombre, categoria, icono
             FROM dbo.Tecnologias
             WHERE activo = 1
             ORDER BY categoria, nombre'
        );

        return array_map(
            static fn (array $row): array => [
                'id' => (int) $row['id'],
                'nombre' => (string) $row['nombre'],
                'categoria' => $row['categoria'] !== null ? (string) $row['categoria'] : null,
                'icono' => $row['icono'] !== null ? (string) $row['icono'] : null,
            ],
            $stmt->fetchAll(\PDO::FETCH_ASSOC)
        );
    }
}
