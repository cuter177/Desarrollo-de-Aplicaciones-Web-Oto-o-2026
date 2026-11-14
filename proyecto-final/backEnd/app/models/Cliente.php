<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;

final class Cliente
{
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
