<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use DateTimeImmutable;
use DateTimeZone;
use PDO;

final class Reunion
{
    public const ESTADOS = ['solicitada', 'confirmada', 'rechazada', 'cancelada'];

    public const ACTIVAS = ['solicitada', 'confirmada'];

    private const ZONA = 'America/Mexico_City';

    /**
     * @return list<array<string, mixed>>
     */
    public static function paraCliente(int $clienteId, ?DateTimeImmutable $desde, ?DateTimeImmutable $hasta): array
    {
        $sql = 'SELECT r.id, r.proyecto_id, p.nombre AS proyecto_nombre, r.titulo,
                       r.inicio, r.fin, r.estado, r.puntos, r.respuesta, r.creado_en
                FROM dbo.Reuniones r
                INNER JOIN dbo.Proyectos p ON p.id = r.proyecto_id
                WHERE p.cliente_id = :cliente_id';
        $params = [':cliente_id' => $clienteId];

        if ($desde !== null) {
            $sql .= ' AND r.fin > :desde';
            $params[':desde'] = self::aSql($desde);
        }

        if ($hasta !== null) {
            $sql .= ' AND r.inicio < :hasta';
            $params[':hasta'] = self::aSql($hasta);
        }

        $sql .= ' ORDER BY r.inicio ASC, r.id ASC';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);

        return array_map(
            static fn (array $row): array => self::mapRow($row, false),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function todas(): array
    {
        $stmt = Database::connection()->query(
            'SELECT r.id, r.proyecto_id, p.nombre AS proyecto_nombre,
                    c.empresa AS cliente_empresa, r.titulo, r.inicio, r.fin,
                    r.estado, r.puntos, r.respuesta, r.creado_en
             FROM dbo.Reuniones r
             INNER JOIN dbo.Proyectos p ON p.id = r.proyecto_id
             INNER JOIN dbo.Clientes c ON c.id = p.cliente_id
             ORDER BY r.inicio ASC, r.id ASC'
        );

        return array_map(
            static fn (array $row): array => self::mapRow($row, true),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findForCliente(int $id, int $clienteId): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT r.id, r.proyecto_id, p.nombre AS proyecto_nombre, p.cliente_id,
                    r.titulo, r.inicio, r.fin, r.estado, r.puntos, r.respuesta, r.creado_en
             FROM dbo.Reuniones r
             INNER JOIN dbo.Proyectos p ON p.id = r.proyecto_id
             WHERE r.id = :id AND p.cliente_id = :cliente_id'
        );
        $stmt->execute([':id' => $id, ':cliente_id' => $clienteId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : self::mapRow($row, false);
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findById(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT r.id, r.proyecto_id, p.nombre AS proyecto_nombre,
                    c.empresa AS cliente_empresa, p.cliente_id, r.titulo,
                    r.inicio, r.fin, r.estado, r.puntos, r.respuesta, r.creado_en
             FROM dbo.Reuniones r
             INNER JOIN dbo.Proyectos p ON p.id = r.proyecto_id
             INNER JOIN dbo.Clientes c ON c.id = p.cliente_id
             WHERE r.id = :id'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : self::mapRow($row, true);
    }

    public static function hayEmpalme(int $clienteId, DateTimeImmutable $inicio, DateTimeImmutable $fin): bool
    {
        $stmt = Database::connection()->prepare(
            'SELECT COUNT(*)
             FROM dbo.Reuniones r
             INNER JOIN dbo.Proyectos p ON p.id = r.proyecto_id
             WHERE p.cliente_id = :cliente_id
               AND r.estado IN (N\'solicitada\', N\'confirmada\')
               AND r.inicio < :fin
               AND r.fin > :inicio'
        );
        $stmt->execute([
            ':cliente_id' => $clienteId,
            ':inicio' => self::aSql($inicio),
            ':fin' => self::aSql($fin),
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * @return int
     */
    public static function crear(int $proyectoId, string $titulo, DateTimeImmutable $inicio, DateTimeImmutable $fin): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO dbo.Reuniones (proyecto_id, titulo, inicio, fin, estado)
             OUTPUT INSERTED.id
             VALUES (:proyecto_id, :titulo, :inicio, :fin, N\'solicitada\')'
        );
        $stmt->execute([
            ':proyecto_id' => $proyectoId,
            ':titulo' => $titulo,
            ':inicio' => self::aSql($inicio),
            ':fin' => self::aSql($fin),
        ]);

        return (int) $stmt->fetchColumn();
    }

    public static function cancelar(int $id): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE dbo.Reuniones
             SET estado = N\'cancelada\', actualizado_en = SYSUTCDATETIME()
             WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
    }

    /**
     * @param array<string, mixed> $cambios
     */
    public static function responder(int $id, array $cambios): void
    {
        $sets = [];
        $params = [':id' => $id];

        foreach (['estado', 'puntos', 'respuesta'] as $campo) {
            if (!array_key_exists($campo, $cambios)) {
                continue;
            }

            $sets[] = $campo . ' = :' . $campo;
            $params[':' . $campo] = $cambios[$campo];
        }

        if ($sets === []) {
            return;
        }

        $sets[] = 'actualizado_en = SYSUTCDATETIME()';
        $stmt = Database::connection()->prepare(
            'UPDATE dbo.Reuniones SET ' . implode(', ', $sets) . ' WHERE id = :id'
        );
        $stmt->execute($params);
    }

    public static function parseLocal(string $valor): ?DateTimeImmutable
    {
        $valor = trim($valor);
        $zona = new DateTimeZone(self::ZONA);

        foreach (['Y-m-d\TH:i', 'Y-m-d\TH:i:s', 'Y-m-d H:i', 'Y-m-d H:i:s'] as $formato) {
            $fecha = DateTimeImmutable::createFromFormat('!' . $formato, $valor, $zona);

            if (!$fecha instanceof DateTimeImmutable) {
                continue;
            }

            $errores = DateTimeImmutable::getLastErrors();

            if ($errores !== false && (($errores['warning_count'] ?? 0) > 0 || ($errores['error_count'] ?? 0) > 0)) {
                continue;
            }

            return $fecha->setTimezone(new DateTimeZone('UTC'));
        }

        return null;
    }

    public static function ahora(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', new DateTimeZone('UTC'));
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private static function mapRow(array $row, bool $admin): array
    {
        $reunion = [
            'id' => (int) $row['id'],
            'proyecto_id' => (int) $row['proyecto_id'],
            'proyecto_nombre' => (string) $row['proyecto_nombre'],
            'titulo' => (string) $row['titulo'],
            'inicio' => self::formatoUtc((string) $row['inicio']),
            'fin' => self::formatoUtc((string) $row['fin']),
            'estado' => (string) $row['estado'],
            'puntos' => $row['puntos'] !== null ? (string) $row['puntos'] : null,
            'respuesta' => $row['respuesta'] !== null ? (string) $row['respuesta'] : null,
            'creado_en' => self::formatoUtc((string) $row['creado_en']),
        ];

        if ($admin) {
            $reunion['cliente_empresa'] = (string) ($row['cliente_empresa'] ?? '');
        }

        return $reunion;
    }

    private static function aSql(DateTimeImmutable $fecha): string
    {
        return $fecha->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
    }

    private static function formatoUtc(string $valor): string
    {
        $fecha = new DateTimeImmutable($valor, new DateTimeZone('UTC'));

        return $fecha->format('Y-m-d\TH:i:s\Z');
    }
}
