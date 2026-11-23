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
     * @return array<string, mixed>|null
     */
    public static function findById(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT id, slug, nombre, descripcion, beneficios, icono, orden, activo
             FROM dbo.Servicios
             WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row === false ? null : self::mapRow($row);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function allAdmin(): array
    {
        $stmt = Database::connection()->query(
            'SELECT id, slug, nombre, descripcion, beneficios, icono, orden, activo
             FROM dbo.Servicios
             ORDER BY orden, id'
        );

        return array_map(
            static fn (array $row): array => self::mapRow($row),
            $stmt->fetchAll(\PDO::FETCH_ASSOC)
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO dbo.Servicios (slug, nombre, descripcion, beneficios, icono, activo, orden)
             OUTPUT INSERTED.id
             VALUES (:slug, :nombre, :descripcion, :beneficios, :icono, :activo, :orden)'
        );
        $stmt->execute([
            ':slug' => $data['slug'],
            ':nombre' => $data['nombre'],
            ':descripcion' => $data['descripcion'],
            ':beneficios' => $data['beneficios'] ?? null,
            ':icono' => $data['icono'] ?? null,
            ':activo' => (int) ($data['activo'] ?? 1),
            ':orden' => (int) ($data['orden'] ?? 0),
        ]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function update(int $id, array $data): void
    {
        $allowed = ['slug', 'nombre', 'descripcion', 'beneficios', 'icono', 'activo', 'orden'];
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
            'UPDATE dbo.Servicios SET ' . implode(', ', $sets) . ' WHERE id = :id'
        );
        $stmt->execute($params);
    }

    public static function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM dbo.Servicios WHERE slug = :slug';
        $params = [':slug' => $slug];

        if ($exceptId !== null) {
            $sql .= ' AND id <> :id';
            $params[':id'] = $exceptId;
        }

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
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

        return $slug !== '' ? substr($slug, 0, 110) : 'servicio';
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

        $mapped = [
            'id' => (int) $row['id'],
            'slug' => (string) $row['slug'],
            'nombre' => (string) $row['nombre'],
            'descripcion' => (string) $row['descripcion'],
            'beneficios' => $beneficios,
            'icono' => $row['icono'] !== null ? (string) $row['icono'] : null,
            'orden' => (int) $row['orden'],
        ];

        if (array_key_exists('activo', $row)) {
            $mapped['activo'] = (int) $row['activo'] === 1;
        }

        return $mapped;
    }
}
