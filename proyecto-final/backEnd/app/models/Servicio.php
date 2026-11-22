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
            'SELECT id, slug, nombre, descripcion, beneficios, icono, orden
             FROM dbo.Servicios
             WHERE slug = :slug AND activo = 1'
        );
        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return self::mapRow($row);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function allActive(): array
    {
        $stmt = Database::connection()->query(
            'SELECT id, slug, nombre, descripcion, beneficios, icono, orden
             FROM dbo.Servicios
             WHERE activo = 1
             ORDER BY orden, id'
        );

        return array_map(
            static fn (array $row): array => self::mapRow($row),
            $stmt->fetchAll(\PDO::FETCH_ASSOC)
        );
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private static function mapRow(array $row): array
    {
        $beneficios = [];

        if ($row['beneficios'] !== null) {
            foreach (preg_split('/\r\n|\r|\n/', (string) $row['beneficios']) ?: [] as $linea) {
                $linea = trim($linea);

                if ($linea !== '') {
                    $beneficios[] = $linea;
                }
            }
        }

        return [
            'id' => (int) $row['id'],
            'slug' => (string) $row['slug'],
            'nombre' => (string) $row['nombre'],
            'descripcion' => (string) $row['descripcion'],
            'beneficios' => $beneficios,
            'icono' => $row['icono'] !== null ? (string) $row['icono'] : null,
            'orden' => (int) $row['orden'],
        ];
    }
}
