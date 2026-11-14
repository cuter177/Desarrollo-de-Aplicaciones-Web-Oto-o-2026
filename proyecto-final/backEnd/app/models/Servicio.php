<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;

final class Servicio
{
    /**
     * @return array<string, mixed>|null
     */
    public static function findBySlug(string $slug): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT id, slug, nombre, descripcion, icono, orden
             FROM dbo.Servicios
             WHERE slug = :slug AND activo = 1'
        );
        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return [
            'id' => (int) $row['id'],
            'slug' => (string) $row['slug'],
            'nombre' => (string) $row['nombre'],
            'descripcion' => (string) $row['descripcion'],
            'icono' => $row['icono'] !== null ? (string) $row['icono'] : null,
            'orden' => (int) $row['orden'],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function allActive(): array
    {
        $stmt = Database::connection()->query(
            'SELECT id, slug, nombre, descripcion, icono, orden
             FROM dbo.Servicios
             WHERE activo = 1
             ORDER BY orden, id'
        );

        return array_map(
            static fn (array $row): array => [
                'id' => (int) $row['id'],
                'slug' => (string) $row['slug'],
                'nombre' => (string) $row['nombre'],
                'descripcion' => (string) $row['descripcion'],
                'icono' => $row['icono'] !== null ? (string) $row['icono'] : null,
                'orden' => (int) $row['orden'],
            ],
            $stmt->fetchAll(\PDO::FETCH_ASSOC)
        );
    }
}
