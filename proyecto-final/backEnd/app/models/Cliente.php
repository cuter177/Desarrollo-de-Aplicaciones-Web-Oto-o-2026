<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;

final class Cliente
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function all(?string $q = null): array
    {
        $sql = 'SELECT c.id, c.usuario_id, c.empresa, c.telefono, c.sitio_web, c.industria,
                    c.cargo, c.frase, c.ciudad, u.nombre AS contacto, u.email
             FROM dbo.Clientes c
             INNER JOIN dbo.Usuarios u ON u.id = c.usuario_id';
        $params = [];
        $q = trim((string) $q);

        if ($q !== '') {
            $sql .= ' WHERE c.empresa LIKE :q1 OR u.nombre LIKE :q2 OR u.email LIKE :q3';
            $like = '%' . $q . '%';
            $params = [':q1' => $like, ':q2' => $like, ':q3' => $like];
        }

        $sql .= ' ORDER BY c.empresa, c.id';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);

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
                'cargo' => $row['cargo'] !== null ? (string) $row['cargo'] : null,
                'frase' => $row['frase'] !== null ? (string) $row['frase'] : null,
                'ciudad' => $row['ciudad'] !== null ? (string) $row['ciudad'] : null,
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
                    c.cargo, c.frase, c.ciudad, u.nombre AS contacto, u.email
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
            'cargo' => $row['cargo'] !== null ? (string) $row['cargo'] : null,
            'frase' => $row['frase'] !== null ? (string) $row['frase'] : null,
            'ciudad' => $row['ciudad'] !== null ? (string) $row['ciudad'] : null,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findByUsuarioId(int $usuarioId): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT c.id, c.usuario_id, c.empresa, c.telefono, c.sitio_web, c.industria,
                    c.cargo, c.frase, c.ciudad, u.nombre AS contacto, u.email
             FROM dbo.Clientes c
             INNER JOIN dbo.Usuarios u ON u.id = c.usuario_id
             WHERE c.usuario_id = :usuario_id'
        );
        $stmt->execute([':usuario_id' => $usuarioId]);
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
            'cargo' => $row['cargo'] !== null ? (string) $row['cargo'] : null,
            'frase' => $row['frase'] !== null ? (string) $row['frase'] : null,
            'ciudad' => $row['ciudad'] !== null ? (string) $row['ciudad'] : null,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function update(int $id, array $data): void
    {
        $allowed = ['empresa', 'telefono', 'sitio_web', 'industria', 'cargo', 'frase', 'ciudad'];
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
            'UPDATE dbo.Clientes SET ' . implode(', ', $sets) . ' WHERE id = :id'
        );
        $stmt->execute($params);
    }

    public static function create(
        int $usuarioId,
        string $empresa,
        ?string $telefono = null,
        ?string $sitioWeb = null,
        ?string $industria = null,
        ?string $cargo = null,
        ?string $frase = null,
        ?string $ciudad = null
    ): int {
        $stmt = Database::connection()->prepare(
            'INSERT INTO dbo.Clientes (usuario_id, empresa, telefono, sitio_web, industria, cargo, frase, ciudad)
             OUTPUT INSERTED.id
             VALUES (:usuario_id, :empresa, :telefono, :sitio_web, :industria, :cargo, :frase, :ciudad)'
        );
        $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':empresa' => $empresa,
            ':telefono' => $telefono,
            ':sitio_web' => $sitioWeb,
            ':industria' => $industria,
            ':cargo' => $cargo,
            ':frase' => $frase,
            ':ciudad' => $ciudad,
        ]);

        return (int) $stmt->fetchColumn();
    }
}
