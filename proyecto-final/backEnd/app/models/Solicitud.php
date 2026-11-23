<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

final class Solicitud
{
    public const ESTADOS = ['nueva', 'en_revision', 'cotizada', 'aceptada', 'rechazada'];

    /**
     * @param array<string, mixed> $data
     */
    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO dbo.SolicitudesCotizacion (
                cliente_id, servicio_id, plan_id, nombre_contacto, email,
                empresa, giro, telefono, descripcion, necesidades,
                servicios_interes, meta_crecimiento_pct, ahorro_horas_semana,
                procesos_automatizar, plazo_deseado, etapa_decision, puntaje,
                recomendacion, presupuesto_estimado, estado, origen
             )
             OUTPUT INSERTED.id
             VALUES (
                :cliente_id, :servicio_id, :plan_id, :nombre_contacto, :email,
                :empresa, :giro, :telefono, :descripcion, :necesidades,
                :servicios_interes, :meta_crecimiento_pct, :ahorro_horas_semana,
                :procesos_automatizar, :plazo_deseado, :etapa_decision, :puntaje,
                :recomendacion, :presupuesto_estimado, :estado, :origen
             )'
        );
        $stmt->execute([
            ':cliente_id' => $data['cliente_id'] ?? null,
            ':servicio_id' => $data['servicio_id'] ?? null,
            ':plan_id' => $data['plan_id'] ?? null,
            ':nombre_contacto' => $data['nombre_contacto'],
            ':email' => $data['email'],
            ':empresa' => $data['empresa'] ?? null,
            ':giro' => $data['giro'] ?? null,
            ':telefono' => $data['telefono'] ?? null,
            ':descripcion' => $data['descripcion'],
            ':necesidades' => $data['necesidades'] ?? null,
            ':servicios_interes' => $data['servicios_interes'] ?? null,
            ':meta_crecimiento_pct' => $data['meta_crecimiento_pct'] ?? null,
            ':ahorro_horas_semana' => $data['ahorro_horas_semana'] ?? null,
            ':procesos_automatizar' => $data['procesos_automatizar'] ?? null,
            ':plazo_deseado' => $data['plazo_deseado'] ?? null,
            ':etapa_decision' => $data['etapa_decision'] ?? null,
            ':puntaje' => $data['puntaje'] ?? null,
            ':recomendacion' => $data['recomendacion'] ?? null,
            ':presupuesto_estimado' => $data['presupuesto_estimado'] ?? null,
            ':estado' => $data['estado'] ?? 'nueva',
            ':origen' => $data['origen'] ?? 'publico',
        ]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * @param array{estado?: string, q?: string, plan_id?: int} $filters
     * @return list<array<string, mixed>>
     */
    public static function all(array $filters = []): array
    {
        [$where, $params] = self::buildFilters($filters);

        $stmt = Database::connection()->prepare(
            self::selectSql() . $where . ' ORDER BY s.creado_en DESC, s.id DESC'
        );
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
            self::selectSql() . ' WHERE s.id = :id'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : self::mapRow($row);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function allForCliente(int $clienteId): array
    {
        $stmt = Database::connection()->prepare(
            self::selectSql() . ' WHERE s.cliente_id = :cliente_id ORDER BY s.creado_en DESC, s.id DESC'
        );
        $stmt->execute([':cliente_id' => $clienteId]);

        return array_map(
            static fn (array $row): array => self::mapRow($row),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findForCliente(int $id, int $clienteId): ?array
    {
        $stmt = Database::connection()->prepare(
            self::selectSql() . ' WHERE s.id = :id AND s.cliente_id = :cliente_id'
        );
        $stmt->execute([':id' => $id, ':cliente_id' => $clienteId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : self::mapRow($row);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function update(int $id, array $data): void
    {
        $allowed = [
            'estado',
            'plan_id',
            'cliente_id',
            'notas_admin',
            'recomendacion',
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
            'UPDATE dbo.SolicitudesCotizacion SET ' . implode(', ', $sets) . ' WHERE id = :id'
        );
        $stmt->execute($params);
    }

    public static function isValidEstado(string $estado): bool
    {
        return in_array($estado, self::ESTADOS, true);
    }

    public static function linkProyecto(int $id, int $proyectoId): void
    {
        $stmt = Database::connection()->prepare(
            "UPDATE dbo.SolicitudesCotizacion
             SET proyecto_id = :proyecto_id,
                 estado = CASE WHEN estado = N'aceptada' THEN estado ELSE N'aceptada' END,
                 actualizado_en = SYSUTCDATETIME()
             WHERE id = :id"
        );
        $stmt->execute([
            ':proyecto_id' => $proyectoId,
            ':id' => $id,
        ]);
    }

    private static function selectSql(): string
    {
        return 'SELECT s.*, p.slug AS plan_slug, p.nombre AS plan_nombre,
                       p.precio_desde AS plan_precio_desde,
                       sv.slug AS servicio_slug, sv.nombre AS servicio_nombre,
                       c.empresa AS cliente_empresa, c.telefono AS cliente_telefono,
                       c.sitio_web AS cliente_sitio_web, c.industria AS cliente_industria
                FROM dbo.SolicitudesCotizacion s
                LEFT JOIN dbo.Planes p ON p.id = s.plan_id
                LEFT JOIN dbo.Servicios sv ON sv.id = s.servicio_id
                LEFT JOIN dbo.Clientes c ON c.id = s.cliente_id';
    }

    /**
     * @param array{estado?: string, q?: string, plan_id?: int} $filters
     * @return array{0: string, 1: array<string, mixed>}
     */
    private static function buildFilters(array $filters): array
    {
        $conditions = [];
        $params = [];

        if (isset($filters['estado']) && self::isValidEstado((string) $filters['estado'])) {
            $conditions[] = 's.estado = :estado';
            $params[':estado'] = $filters['estado'];
        }

        if (isset($filters['plan_id']) && (int) $filters['plan_id'] > 0) {
            $conditions[] = 's.plan_id = :plan_id';
            $params[':plan_id'] = (int) $filters['plan_id'];
        }

        $q = trim((string) ($filters['q'] ?? ''));

        if ($q !== '') {
            $conditions[] = '(s.nombre_contacto LIKE :q1 OR s.email LIKE :q2 OR s.empresa LIKE :q3)';
            $like = '%' . $q . '%';
            $params[':q1'] = $like;
            $params[':q2'] = $like;
            $params[':q3'] = $like;
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
    private static function mapRow(array $row): array
    {
        $servicios = [];

        if (is_string($row['servicios_interes'] ?? null) && $row['servicios_interes'] !== '') {
            $decoded = json_decode($row['servicios_interes'], true);
            $servicios = is_array($decoded) ? array_values($decoded) : [];
        }

        return [
            'id' => (int) $row['id'],
            'cliente_id' => $row['cliente_id'] !== null ? (int) $row['cliente_id'] : null,
            'cliente_empresa' => $row['cliente_empresa'] !== null ? (string) $row['cliente_empresa'] : null,
            'cliente_telefono' => $row['cliente_telefono'] !== null ? (string) $row['cliente_telefono'] : null,
            'cliente_sitio_web' => $row['cliente_sitio_web'] !== null ? (string) $row['cliente_sitio_web'] : null,
            'cliente_industria' => $row['cliente_industria'] !== null ? (string) $row['cliente_industria'] : null,
            'servicio_id' => $row['servicio_id'] !== null ? (int) $row['servicio_id'] : null,
            'servicio_slug' => $row['servicio_slug'] !== null ? (string) $row['servicio_slug'] : null,
            'servicio_nombre' => $row['servicio_nombre'] !== null ? (string) $row['servicio_nombre'] : null,
            'plan_id' => $row['plan_id'] !== null ? (int) $row['plan_id'] : null,
            'plan_slug' => $row['plan_slug'] !== null ? (string) $row['plan_slug'] : null,
            'plan_nombre' => $row['plan_nombre'] !== null ? (string) $row['plan_nombre'] : null,
            'plan_precio_desde' => $row['plan_precio_desde'] !== null ? (float) $row['plan_precio_desde'] : null,
            'proyecto_id' => $row['proyecto_id'] !== null ? (int) $row['proyecto_id'] : null,
            'nombre_contacto' => (string) $row['nombre_contacto'],
            'email' => (string) $row['email'],
            'empresa' => $row['empresa'] !== null ? (string) $row['empresa'] : null,
            'giro' => $row['giro'] !== null ? (string) $row['giro'] : null,
            'telefono' => $row['telefono'] !== null ? (string) $row['telefono'] : null,
            'descripcion' => (string) $row['descripcion'],
            'necesidades' => $row['necesidades'] !== null ? (string) $row['necesidades'] : null,
            'servicios_interes' => $servicios,
            'meta_crecimiento_pct' => $row['meta_crecimiento_pct'] !== null ? (float) $row['meta_crecimiento_pct'] : null,
            'ahorro_horas_semana' => $row['ahorro_horas_semana'] !== null ? (int) $row['ahorro_horas_semana'] : null,
            'procesos_automatizar' => $row['procesos_automatizar'] !== null ? (string) $row['procesos_automatizar'] : null,
            'plazo_deseado' => $row['plazo_deseado'] !== null ? (string) $row['plazo_deseado'] : null,
            'etapa_decision' => $row['etapa_decision'] !== null ? (string) $row['etapa_decision'] : null,
            'puntaje' => $row['puntaje'] !== null ? (int) $row['puntaje'] : null,
            'recomendacion' => $row['recomendacion'] !== null ? (string) $row['recomendacion'] : null,
            'notas_admin' => $row['notas_admin'] !== null ? (string) $row['notas_admin'] : null,
            'presupuesto_estimado' => $row['presupuesto_estimado'] !== null ? (float) $row['presupuesto_estimado'] : null,
            'estado' => (string) $row['estado'],
            'origen' => (string) $row['origen'],
            'creado_en' => (string) $row['creado_en'],
            'actualizado_en' => (string) $row['actualizado_en'],
        ];
    }
}
