<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;

final class Tecnologia
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function allAdmin(): array
    {
        $stmt = Database::connection()->query(
            'SELECT id, nombre, categoria, icono, activo
             FROM dbo.Tecnologias
             ORDER BY categoria, nombre'
        );

        return array_map(
            static fn (array $row): array => self::mapRow($row),
            $stmt->fetchAll(\PDO::FETCH_ASSOC)
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findById(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT id, nombre, categoria, icono, activo
             FROM dbo.Tecnologias
             WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row === false ? null : self::mapRow($row);
    }

    public static function nombreExists(string $nombre, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM dbo.Tecnologias WHERE nombre = :nombre';
        $params = [':nombre' => $nombre];

        if ($exceptId !== null) {
            $sql .= ' AND id <> :id';
            $params[':id'] = $exceptId;
        }

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO dbo.Tecnologias (nombre, categoria, icono, activo)
             OUTPUT INSERTED.id
             VALUES (:nombre, :categoria, :icono, :activo)'
        );
        $stmt->execute([
            ':nombre' => $data['nombre'],
            ':categoria' => $data['categoria'] ?? null,
            ':icono' => $data['icono'] ?? null,
            ':activo' => (int) ($data['activo'] ?? 1),
        ]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function update(int $id, array $data): void
    {
        $allowed = ['nombre', 'categoria', 'icono', 'activo'];
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

        $stmt = Database::connection()->prepare(
            'UPDATE dbo.Tecnologias SET ' . implode(', ', $sets) . ' WHERE id = :id'
        );
        $stmt->execute($params);
    }

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
        $mapped = [
            'id' => (int) $row['id'],
            'nombre' => (string) $row['nombre'],
            'categoria' => $row['categoria'] !== null ? (string) $row['categoria'] : null,
            'icono' => $row['icono'] !== null ? (string) $row['icono'] : null,
        ];

        if (array_key_exists('activo', $row)) {
            $mapped['activo'] = (int) $row['activo'] === 1;
        }

        return $mapped;
    }
}
