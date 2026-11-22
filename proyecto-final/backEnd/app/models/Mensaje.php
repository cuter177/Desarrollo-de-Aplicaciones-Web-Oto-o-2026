<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

final class Mensaje
{
    public const ESTADOS = [
        'nuevo',
        'en_revision',
        'atendido',
        'archivado',
    ];

    /**
     * @param array<string, mixed> $data
     */
    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO dbo.MensajesContacto (nombre, email, asunto, mensaje, estado)
             OUTPUT INSERTED.id
             VALUES (:nombre, :email, :asunto, :mensaje, N\'nuevo\')'
        );
        $stmt->execute([
            ':nombre' => $data['nombre'],
            ':email' => $data['email'],
            ':asunto' => $data['asunto'],
            ':mensaje' => $data['mensaje'],
        ]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * @param array<string, mixed> $filtros
     * @return list<array<string, mixed>>
     */
    public static function all(array $filtros = []): array
    {
        $where = [];
        $params = [];

        $estado = trim((string) ($filtros['estado'] ?? ''));

        if ($estado !== '') {
            $where[] = 'estado = :estado';
            $params[':estado'] = $estado;
        }

        $q = trim((string) ($filtros['q'] ?? ''));

        if ($q !== '') {
            $where[] = '(nombre LIKE :q1 OR email LIKE :q2 OR asunto LIKE :q3 OR mensaje LIKE :q4)';
            $like = '%' . $q . '%';
            $params[':q1'] = $like;
            $params[':q2'] = $like;
            $params[':q3'] = $like;
            $params[':q4'] = $like;
        }

        $sql = 'SELECT id, nombre, email, asunto, mensaje, estado, creado_en, atendido_en
                FROM dbo.MensajesContacto';

        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY creado_en DESC, id DESC';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);

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
            'SELECT id, nombre, email, asunto, mensaje, estado, creado_en, atendido_en
             FROM dbo.MensajesContacto
             WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : self::mapRow($row);
    }

    public static function updateEstado(int $id, string $estado): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE dbo.MensajesContacto
             SET estado = :estado,
                 atendido_en = CASE
                     WHEN :estado_atendido = N\'atendido\' AND atendido_en IS NULL
                         THEN SYSUTCDATETIME()
                     ELSE atendido_en
                 END
             WHERE id = :id'
        );
        $stmt->execute([
            ':id' => $id,
            ':estado' => $estado,
            ':estado_atendido' => $estado,
        ]);
    }

    /**
     * @return array<string, int>
     */
    public static function counts(): array
    {
        $stmt = Database::connection()->query(
            'SELECT estado, COUNT(*) AS total
             FROM dbo.MensajesContacto
             GROUP BY estado'
        );

        $conteos = ['total' => 0];

        foreach (self::ESTADOS as $estado) {
            $conteos[$estado] = 0;
        }

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $estado = (string) $row['estado'];
            $total = (int) $row['total'];
            $conteos[$estado] = $total;
            $conteos['total'] += $total;
        }

        return $conteos;
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private static function mapRow(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'nombre' => (string) $row['nombre'],
            'email' => (string) $row['email'],
            'asunto' => (string) $row['asunto'],
            'mensaje' => (string) $row['mensaje'],
            'estado' => (string) $row['estado'],
            'creado_en' => (string) $row['creado_en'],
            'atendido_en' => $row['atendido_en'] !== null ? (string) $row['atendido_en'] : null,
        ];
    }
}
