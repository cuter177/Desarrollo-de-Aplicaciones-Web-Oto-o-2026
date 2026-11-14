<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

final class Proyecto
{
    public const ESTADOS = [
        'planificado',
        'en_progreso',
        'en_revision',
        'entregado',
        'pausado',
        'cancelado',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public static function all(): array
    {
        $stmt = Database::connection()->query(
            'SELECT p.id, p.cliente_id, p.nombre, p.slug, p.descripcion, p.estado,
                    p.fecha_inicio, p.fecha_fin, p.url_demo, p.publicado, p.destacado,
                    p.creado_en, c.empresa AS cliente_empresa
             FROM dbo.Proyectos p
             INNER JOIN dbo.Clientes c ON c.id = p.cliente_id
             ORDER BY p.creado_en DESC, p.id DESC'
        );

        return array_map(
            static fn (array $row): array => self::mapRow($row),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findById(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT p.id, p.cliente_id, p.nombre, p.slug, p.descripcion, p.estado,
                    p.fecha_inicio, p.fecha_fin, p.url_demo, p.publicado, p.destacado,
                    p.creado_en, c.empresa AS cliente_empresa
             FROM dbo.Proyectos p
             INNER JOIN dbo.Clientes c ON c.id = p.cliente_id
             WHERE p.id = :id'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : self::mapRow($row);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO dbo.Proyectos (
                cliente_id, nombre, slug, descripcion, estado,
                fecha_inicio, fecha_fin, url_demo, publicado, destacado
             )
             OUTPUT INSERTED.id
             VALUES (
                :cliente_id, :nombre, :slug, :descripcion, :estado,
                :fecha_inicio, :fecha_fin, :url_demo, :publicado, :destacado
             )'
        );
        $stmt->execute([
            ':cliente_id' => $data['cliente_id'],
            ':nombre' => $data['nombre'],
            ':slug' => $data['slug'],
            ':descripcion' => $data['descripcion'],
            ':estado' => $data['estado'] ?? 'planificado',
            ':fecha_inicio' => $data['fecha_inicio'] ?? null,
            ':fecha_fin' => $data['fecha_fin'] ?? null,
            ':url_demo' => $data['url_demo'] ?? null,
            ':publicado' => (int) ($data['publicado'] ?? 0),
            ':destacado' => (int) ($data['destacado'] ?? 0),
        ]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function update(int $id, array $data): void
    {
        $allowed = [
            'cliente_id',
            'nombre',
            'descripcion',
            'estado',
            'fecha_inicio',
            'fecha_fin',
            'url_demo',
            'publicado',
            'destacado',
        ];
        $sets = [];
        $params = [':id' => $id];

        foreach ($allowed as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }

            $sets[] = $field . ' = :' . $field;
            $params[':' . $field] = $data[$field];
        }

        if ($sets === []) {
            return;
        }

        $sets[] = 'actualizado_en = SYSUTCDATETIME()';

        $stmt = Database::connection()->prepare(
            'UPDATE dbo.Proyectos SET ' . implode(', ', $sets) . ' WHERE id = :id'
        );
        $stmt->execute($params);
    }

    public static function slugify(string $texto): string
    {
        $slug = strtolower(trim($texto));
        $slug = strtr($slug, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'ü' => 'u', 'ñ' => 'n',
        ]);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
        $slug = trim($slug, '-');

        return $slug !== '' ? substr($slug, 0, 150) : 'proyecto';
    }

    public static function slugExists(string $slug): bool
    {
        $stmt = Database::connection()->prepare(
            'SELECT COUNT(*) FROM dbo.Proyectos WHERE slug = :slug'
        );
        $stmt->execute([':slug' => $slug]);

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private static function mapRow(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'cliente_id' => (int) $row['cliente_id'],
            'cliente_empresa' => (string) $row['cliente_empresa'],
            'nombre' => (string) $row['nombre'],
            'slug' => (string) $row['slug'],
            'descripcion' => (string) $row['descripcion'],
            'estado' => (string) $row['estado'],
            'fecha_inicio' => $row['fecha_inicio'] !== null ? (string) $row['fecha_inicio'] : null,
            'fecha_fin' => $row['fecha_fin'] !== null ? (string) $row['fecha_fin'] : null,
            'url_demo' => $row['url_demo'] !== null ? (string) $row['url_demo'] : null,
            'publicado' => (int) $row['publicado'] === 1,
            'destacado' => (int) $row['destacado'] === 1,
            'creado_en' => (string) $row['creado_en'],
        ];
    }
}
