<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

final class Plan
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function allActive(): array
    {
        $stmt = Database::connection()->query(
            'SELECT id, slug, nombre, subtitulo, descripcion, precio_desde,
                    precio_hasta, caracteristicas, dirigido_a, destacado, orden
             FROM dbo.Planes
             WHERE activo = 1
             ORDER BY orden, id'
        );

        return array_map(
            static fn (array $row): array => self::publicData($row),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findBySlug(string $slug): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT id, slug, nombre, subtitulo, descripcion, precio_desde,
                    precio_hasta, caracteristicas, dirigido_a, destacado, orden
             FROM dbo.Planes
             WHERE slug = :slug AND activo = 1'
        );
        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : self::publicData($row);
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findById(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT id, slug, nombre, subtitulo, descripcion, precio_desde,
                    precio_hasta, caracteristicas, dirigido_a, destacado, orden
             FROM dbo.Planes
             WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : self::publicData($row);
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private static function publicData(array $row): array
    {
        $caracteristicas = [];

        if (is_string($row['caracteristicas'] ?? null) && $row['caracteristicas'] !== '') {
            $decoded = json_decode($row['caracteristicas'], true);
            $caracteristicas = is_array($decoded) ? array_values($decoded) : [];
        }

        return [
            'id' => (int) $row['id'],
            'slug' => (string) $row['slug'],
            'nombre' => (string) $row['nombre'],
            'subtitulo' => $row['subtitulo'] !== null ? (string) $row['subtitulo'] : null,
            'descripcion' => (string) $row['descripcion'],
            'precio_desde' => (float) $row['precio_desde'],
            'precio_hasta' => $row['precio_hasta'] !== null ? (float) $row['precio_hasta'] : null,
            'caracteristicas' => $caracteristicas,
            'dirigido_a' => $row['dirigido_a'] !== null ? (string) $row['dirigido_a'] : null,
            'destacado' => (int) $row['destacado'] === 1,
            'orden' => (int) $row['orden'],
        ];
    }
}
