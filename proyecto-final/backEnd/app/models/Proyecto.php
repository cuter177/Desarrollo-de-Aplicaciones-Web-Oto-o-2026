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

    private const SELECT_ADMIN = 'p.id, p.cliente_id, p.nombre, p.slug, p.descripcion,
                    p.reto, p.solucion, p.resultados, p.mostrar_cliente, p.estado, p.progreso,
                    p.fecha_inicio, p.fecha_fin, p.url_demo, p.publicado, p.destacado,
                    p.creado_en, c.empresa AS cliente_empresa, c.industria AS cliente_industria';

    private const SELECT_PUBLICO = 'p.id, p.nombre, p.slug, p.descripcion,
                    p.reto, p.solucion, p.resultados, p.mostrar_cliente, p.estado,
                    p.fecha_inicio, p.fecha_fin, p.url_demo, p.destacado,
                    c.empresa AS cliente_empresa, c.industria AS cliente_industria';

    /**
     * @return list<array<string, mixed>>
     */
    public static function all(): array
    {
        $stmt = Database::connection()->query(
            'SELECT ' . self::SELECT_ADMIN . '
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
            'SELECT ' . self::SELECT_ADMIN . '
             FROM dbo.Proyectos p
             INNER JOIN dbo.Clientes c ON c.id = p.cliente_id
             WHERE p.id = :id'
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
            'SELECT ' . self::SELECT_ADMIN . '
             FROM dbo.Proyectos p
             INNER JOIN dbo.Clientes c ON c.id = p.cliente_id
             WHERE p.cliente_id = :cliente_id
             ORDER BY p.creado_en DESC, p.id DESC'
        );
        $stmt->execute([':cliente_id' => $clienteId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return self::withTechnologies($rows);
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findForCliente(int $id, int $clienteId): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT ' . self::SELECT_ADMIN . '
             FROM dbo.Proyectos p
             INNER JOIN dbo.Clientes c ON c.id = p.cliente_id
             WHERE p.id = :id AND p.cliente_id = :cliente_id'
        );
        $stmt->execute([':id' => $id, ':cliente_id' => $clienteId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return self::withTechnologies([$row])[0];
    }

    /**
     * Proyectos visibles en el portafolio público.
     *
     * @return list<array<string, mixed>>
     */
    public static function allPublished(): array
    {
        $stmt = Database::connection()->query(
            'SELECT ' . self::SELECT_PUBLICO . '
             FROM dbo.Proyectos p
             INNER JOIN dbo.Clientes c ON c.id = p.cliente_id
             WHERE p.publicado = 1
             ORDER BY p.destacado DESC, p.fecha_inicio DESC, p.id DESC'
        );

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $tecnologias = self::technologiesForProjects(
            array_map(static fn (array $row): int => (int) $row['id'], $rows)
        );

        return array_map(
            static fn (array $row): array => self::mapPublicRow(
                $row,
                $tecnologias[(int) $row['id']] ?? []
            ),
            $rows
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findPublishedBySlug(string $slug): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT ' . self::SELECT_PUBLICO . '
             FROM dbo.Proyectos p
             INNER JOIN dbo.Clientes c ON c.id = p.cliente_id
             WHERE p.slug = :slug AND p.publicado = 1'
        );
        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        $tecnologias = self::technologiesForProjects([(int) $row['id']]);

        return self::mapPublicRow($row, $tecnologias[(int) $row['id']] ?? []);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO dbo.Proyectos (
                cliente_id, nombre, slug, descripcion, reto, solucion, resultados,
                mostrar_cliente, estado, fecha_inicio, fecha_fin, url_demo, publicado, destacado
             )
             OUTPUT INSERTED.id
             VALUES (
                :cliente_id, :nombre, :slug, :descripcion, :reto, :solucion, :resultados,
                :mostrar_cliente, :estado, :fecha_inicio, :fecha_fin, :url_demo, :publicado, :destacado
             )'
        );
        $stmt->execute([
            ':cliente_id' => $data['cliente_id'],
            ':nombre' => $data['nombre'],
            ':slug' => $data['slug'],
            ':descripcion' => $data['descripcion'],
            ':reto' => $data['reto'] ?? null,
            ':solucion' => $data['solucion'] ?? null,
            ':resultados' => $data['resultados'] ?? null,
            ':mostrar_cliente' => (int) ($data['mostrar_cliente'] ?? 0),
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
            'reto',
            'solucion',
            'resultados',
            'mostrar_cliente',
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
     * @param list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    private static function withTechnologies(array $rows): array
    {
        $ids = array_map(static fn (array $row): int => (int) $row['id'], $rows);
        $tecnologias = self::technologiesForProjects($ids);
        $avances = self::avancesForProjects($ids);

        return array_map(
            static function (array $row) use ($tecnologias, $avances): array {
                $proyecto = self::mapRow($row);
                $proyecto['tecnologias'] = $tecnologias[(int) $row['id']] ?? [];
                $proyecto['avances'] = $avances[(int) $row['id']] ?? [];

                return $proyecto;
            },
            $rows
        );
    }

    public static function registrarAvance(int $id, int $porcentaje, string $nota): int
    {
        $conexion = Database::connection();
        $conexion->beginTransaction();

        try {
            $actualizar = $conexion->prepare(
                'UPDATE dbo.Proyectos
                 SET progreso = :progreso, actualizado_en = SYSUTCDATETIME()
                 WHERE id = :id'
            );
            $actualizar->execute([
                ':progreso' => $porcentaje,
                ':id' => $id,
            ]);

            $insertar = $conexion->prepare(
                'INSERT INTO dbo.ProyectoAvances (proyecto_id, porcentaje, nota)
                 OUTPUT INSERTED.id
                 VALUES (:proyecto_id, :porcentaje, :nota)'
            );
            $insertar->execute([
                ':proyecto_id' => $id,
                ':porcentaje' => $porcentaje,
                ':nota' => $nota,
            ]);
            $avanceId = (int) $insertar->fetchColumn();
            $conexion->commit();

            return $avanceId;
        } catch (\Throwable $exception) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }

            throw $exception;
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function avancePorId(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT id, proyecto_id, porcentaje, nota, creado_en
             FROM dbo.ProyectoAvances
             WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return [
            'id' => (int) $row['id'],
            'proyecto_id' => (int) $row['proyecto_id'],
            'porcentaje' => (int) $row['porcentaje'],
            'nota' => (string) $row['nota'],
            'creado_en' => self::formatoUtc((string) $row['creado_en']),
        ];
    }

    /**
     * @param list<int> $ids
     * @return array<int, list<array<string, mixed>>>
     */
    private static function avancesForProjects(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($ids), '?'));
        $stmt = Database::connection()->prepare(
            'SELECT id, proyecto_id, porcentaje, nota, creado_en
             FROM dbo.ProyectoAvances
             WHERE proyecto_id IN (' . $placeholders . ')
             ORDER BY creado_en DESC, id DESC'
        );
        $stmt->execute($ids);
        $resultado = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $resultado[(int) $row['proyecto_id']][] = [
                'id' => (int) $row['id'],
                'porcentaje' => (int) $row['porcentaje'],
                'nota' => (string) $row['nota'],
                'creado_en' => self::formatoUtc((string) $row['creado_en']),
            ];
        }

        return $resultado;
    }

    private static function formatoUtc(string $valor): string
    {
        $fecha = new \DateTimeImmutable($valor, new \DateTimeZone('UTC'));

        return $fecha->format('Y-m-d\TH:i:s\Z');
    }

    /**
     * @param list<int> $ids
     * @return array<int, list<string>>
     */
    private static function technologiesForProjects(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($ids), '?'));
        $stmt = Database::connection()->prepare(
            'SELECT pt.proyecto_id, t.nombre
             FROM dbo.ProyectoTecnologia pt
             INNER JOIN dbo.Tecnologias t ON t.id = pt.tecnologia_id
             WHERE pt.proyecto_id IN (' . $placeholders . ')
               AND t.activo = 1
             ORDER BY t.nombre'
        );
        $stmt->execute($ids);

        $resultado = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $resultado[(int) $row['proyecto_id']][] = (string) $row['nombre'];
        }

        return $resultado;
    }

    /**
     * @param array<string, mixed> $row
     * @param list<string> $tecnologias
     * @return array<string, mixed>
     */
    private static function mapPublicRow(array $row, array $tecnologias): array
    {
        $mostrarCliente = (int) $row['mostrar_cliente'] === 1;

        return [
            'id' => (int) $row['id'],
            'slug' => (string) $row['slug'],
            'nombre' => (string) $row['nombre'],
            'resumen' => (string) $row['descripcion'],
            'reto' => $row['reto'] !== null ? (string) $row['reto'] : null,
            'solucion' => $row['solucion'] !== null ? (string) $row['solucion'] : null,
            'resultados' => $row['resultados'] !== null ? (string) $row['resultados'] : null,
            'sector' => $row['cliente_industria'] !== null ? (string) $row['cliente_industria'] : null,
            'cliente' => $mostrarCliente ? (string) $row['cliente_empresa'] : null,
            'estado' => (string) $row['estado'],
            'fecha_inicio' => $row['fecha_inicio'] !== null ? (string) $row['fecha_inicio'] : null,
            'fecha_fin' => $row['fecha_fin'] !== null ? (string) $row['fecha_fin'] : null,
            'url_demo' => $row['url_demo'] !== null ? (string) $row['url_demo'] : null,
            'destacado' => (int) $row['destacado'] === 1,
            'tecnologias' => $tecnologias,
        ];
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
            'cliente_industria' => $row['cliente_industria'] !== null
                ? (string) $row['cliente_industria']
                : null,
            'nombre' => (string) $row['nombre'],
            'slug' => (string) $row['slug'],
            'descripcion' => (string) $row['descripcion'],
            'reto' => $row['reto'] !== null ? (string) $row['reto'] : null,
            'solucion' => $row['solucion'] !== null ? (string) $row['solucion'] : null,
            'resultados' => $row['resultados'] !== null ? (string) $row['resultados'] : null,
            'mostrar_cliente' => (int) $row['mostrar_cliente'] === 1,
            'estado' => (string) $row['estado'],
            'progreso' => (int) ($row['progreso'] ?? 0),
            'fecha_inicio' => $row['fecha_inicio'] !== null ? (string) $row['fecha_inicio'] : null,
            'fecha_fin' => $row['fecha_fin'] !== null ? (string) $row['fecha_fin'] : null,
            'url_demo' => $row['url_demo'] !== null ? (string) $row['url_demo'] : null,
            'publicado' => (int) $row['publicado'] === 1,
            'destacado' => (int) $row['destacado'] === 1,
            'creado_en' => (string) $row['creado_en'],
        ];
    }
}
