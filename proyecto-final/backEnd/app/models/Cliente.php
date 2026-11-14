<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;

final class Cliente
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function all(): array
    {
        $stmt = Database::connection()->query(
            'SELECT c.id, c.usuario_id, c.empresa, c.telefono, c.sitio_web, c.industria,
                    u.nombre AS contacto, u.email
             FROM dbo.Clientes c
             INNER JOIN dbo.Usuarios u ON u.id = c.usuario_id
             ORDER BY c.empresa, c.id'
        );

        return array_map(
            static fn (array $row): array => [
                'id' => (int) $row['id'],
                'usuario_id' => (int) $row['usuario_id'],
                'empresa' => (string) $row['empresa'],
                'contacto' => (string) $row['contacto'],
                'email' => (string) $row['email'],
                'telefono' => $row['telefono'] !== null ? (string) $row['telefono'] : null,
                'sitio_web' => $row['sitio_web'] !== null ? (string) $row['sitio_web'] : null,
                'industria' => $row['industria'] !== null ? (string) $row['industria'] : null,
            ],
            $stmt->fetchAll(\PDO::FETCH_ASSOC)
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findById(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT c.id, c.usuario_id, c.empresa, c.telefono, c.sitio_web, c.industria,
                    u.nombre AS contacto, u.email
             FROM dbo.Clientes c
             INNER JOIN dbo.Usuarios u ON u.id = c.usuario_id
             WHERE c.id = :id'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return [
            'id' => (int) $row['id'],
            'usuario_id' => (int) $row['usuario_id'],
            'empresa' => (string) $row['empresa'],
            'contacto' => (string) $row['contacto'],
            'email' => (string) $row['email'],
            'telefono' => $row['telefono'] !== null ? (string) $row['telefono'] : null,
            'sitio_web' => $row['sitio_web'] !== null ? (string) $row['sitio_web'] : null,
            'industria' => $row['industria'] !== null ? (string) $row['industria'] : null,
        ];
    }

    public static function create(
        int $usuarioId,
        string $empresa,
        ?string $telefono = null,
        ?string $sitioWeb = null,
        ?string $industria = null
    ): int {
        $stmt = Database::connection()->prepare(
            'INSERT INTO dbo.Clientes (usuario_id, empresa, telefono, sitio_web, industria)
             OUTPUT INSERTED.id
             VALUES (:usuario_id, :empresa, :telefono, :sitio_web, :industria)'
        );
        $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':empresa' => $empresa,
            ':telefono' => $telefono,
            ':sitio_web' => $sitioWeb,
            ':industria' => $industria,
        ]);

        return (int) $stmt->fetchColumn();
    }
}
