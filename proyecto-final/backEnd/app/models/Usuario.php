<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

final class Usuario
{
    /**
     * @return array<string, mixed>|null
     */
    public static function findByEmail(string $email): ?array
    {
        return self::findBy('u.email = :valor', $email);
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findById(int $id): ?array
    {
        return self::findBy('u.id = :valor', $id);
    }

    public static function emailExists(string $email, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM dbo.Usuarios WHERE email = :email';
        $params = [':email' => $email];

        if ($exceptId !== null) {
            $sql .= ' AND id <> :id';
            $params[':id'] = $exceptId;
        }

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * @param array{q?: string, rol?: string, activo?: int} $filters
     * @return list<array<string, mixed>>
     */
    public static function all(array $filters = []): array
    {
        [$where, $params] = self::adminFilters($filters);
        $stmt = Database::connection()->prepare(self::adminSelect() . $where . ' ORDER BY u.nombre, u.id');
        $stmt->execute($params);

        return array_map(
            static fn (array $row): array => self::mapAdmin($row),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findPublicById(int $id): ?array
    {
        $stmt = Database::connection()->prepare(self::adminSelect() . ' WHERE u.id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : self::mapAdmin($row);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function updateAccount(int $id, array $data): void
    {
        $allowed = ['nombre', 'email', 'rol_id', 'activo'];
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
            'UPDATE dbo.Usuarios SET ' . implode(', ', $sets) . ' WHERE id = :id'
        );
        $stmt->execute($params);
    }

    public static function create(
        string $nombre,
        string $email,
        string $passwordHash,
        int $rolId,
        bool $activo = true
    ): int {
        $stmt = Database::connection()->prepare(
            'INSERT INTO dbo.Usuarios (nombre, email, password_hash, rol_id, activo)
             OUTPUT INSERTED.id
             VALUES (:nombre, :email, :password_hash, :rol_id, :activo)'
        );
        $stmt->execute([
            ':nombre' => $nombre,
            ':email' => $email,
            ':password_hash' => $passwordHash,
            ':rol_id' => $rolId,
            ':activo' => $activo ? 1 : 0,
        ]);

        return (int) $stmt->fetchColumn();
    }

    public static function updatePassword(int $id, string $passwordHash): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE dbo.Usuarios
             SET password_hash = :password_hash,
                 actualizado_en = SYSUTCDATETIME()
             WHERE id = :id'
        );
        $stmt->execute([
            ':password_hash' => $passwordHash,
            ':id' => $id,
        ]);
    }

    public static function touchLastAccess(int $id): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE dbo.Usuarios
             SET ultimo_acceso = SYSUTCDATETIME()
             WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    public static function publicData(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'nombre' => (string) $row['nombre'],
            'email' => (string) $row['email'],
            'rol' => (string) $row['rol'],
            'cliente_id' => $row['cliente_id'] !== null ? (int) $row['cliente_id'] : null,
        ];
    }

    private static function adminSelect(): string
    {
        return 'SELECT u.id, u.nombre, u.email, u.activo, u.ultimo_acceso, u.creado_en,
                       r.nombre AS rol, c.id AS cliente_id, c.empresa AS cliente_empresa
                FROM dbo.Usuarios u
                INNER JOIN dbo.Roles r ON r.id = u.rol_id
                LEFT JOIN dbo.Clientes c ON c.usuario_id = u.id';
    }

    /**
     * @param array{q?: string, rol?: string, activo?: int} $filters
     * @return array{0: string, 1: array<string, mixed>}
     */
    private static function adminFilters(array $filters): array
    {
        $conditions = [];
        $params = [];

        $q = trim((string) ($filters['q'] ?? ''));

        if ($q !== '') {
            $conditions[] = '(u.nombre LIKE :q1 OR u.email LIKE :q2)';
            $like = '%' . $q . '%';
            $params[':q1'] = $like;
            $params[':q2'] = $like;
        }

        if (isset($filters['rol']) && $filters['rol'] !== '') {
            $conditions[] = 'r.nombre = :rol';
            $params[':rol'] = $filters['rol'];
        }

        if (array_key_exists('activo', $filters)) {
            $conditions[] = 'u.activo = :activo';
            $params[':activo'] = (int) $filters['activo'];
        }

        return [
            $conditions === [] ? '' : ' WHERE ' . implode(' AND ', $conditions),
            $params,
        ];
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private static function mapAdmin(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'nombre' => (string) $row['nombre'],
            'email' => (string) $row['email'],
            'rol' => (string) $row['rol'],
            'activo' => (int) $row['activo'] === 1,
            'ultimo_acceso' => $row['ultimo_acceso'] !== null ? (string) $row['ultimo_acceso'] : null,
            'creado_en' => (string) $row['creado_en'],
            'cliente_id' => $row['cliente_id'] !== null ? (int) $row['cliente_id'] : null,
            'cliente_empresa' => $row['cliente_empresa'] !== null ? (string) $row['cliente_empresa'] : null,
        ];
    }

    /**
     * @param mixed $value
     * @return array<string, mixed>|null
     */
    private static function findBy(string $condition, mixed $value): ?array
    {
        $sql = 'SELECT u.id, u.nombre, u.email, u.password_hash, u.rol_id, u.activo,
                       r.nombre AS rol, c.id AS cliente_id
                FROM dbo.Usuarios u
                INNER JOIN dbo.Roles r ON r.id = u.rol_id
                LEFT JOIN dbo.Clientes c ON c.usuario_id = u.id
                WHERE ' . $condition;

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([':valor' => $value]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }
}
