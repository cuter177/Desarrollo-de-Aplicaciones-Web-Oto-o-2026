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

    public static function emailExists(string $email): bool
    {
        $stmt = Database::connection()->prepare(
            'SELECT COUNT(*) FROM dbo.Usuarios WHERE email = :email'
        );
        $stmt->execute([':email' => $email]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public static function create(
        string $nombre,
        string $email,
        string $passwordHash,
        int $rolId
    ): int {
        $stmt = Database::connection()->prepare(
            'INSERT INTO dbo.Usuarios (nombre, email, password_hash, rol_id, activo)
             OUTPUT INSERTED.id
             VALUES (:nombre, :email, :password_hash, :rol_id, 1)'
        );
        $stmt->execute([
            ':nombre' => $nombre,
            ':email' => $email,
            ':password_hash' => $passwordHash,
            ':rol_id' => $rolId,
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
