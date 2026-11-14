<?php

declare(strict_types=1);

/**
 * Crea el usuario administrador inicial.
 *
 * Uso dentro del contenedor:
 *   docker compose exec api php scripts/crear-admin.php
 *
 * Variables:
 *   ADMIN_NAME, ADMIN_EMAIL, ADMIN_PASSWORD
 *
 * El script es idempotente: si el correo ya existe, no lo modifica.
 */

use App\Helpers\Env;
use App\Helpers\Validator;
use App\Models\Rol;
use App\Models\Usuario;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Este script solo puede ejecutarse desde la línea de comandos.\n");
    exit(1);
}

require __DIR__ . '/../config/bootstrap.php';

try {
    $nombre = trim((string) Env::get('ADMIN_NAME', 'Administrador'));
    $email = strtolower(trim((string) Env::get('ADMIN_EMAIL', 'admin@devstudio.local')));
    $password = (string) Env::get('ADMIN_PASSWORD', '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        fwrite(STDERR, "ADMIN_EMAIL no es un correo válido.\n");
        exit(1);
    }

    if ($password === '') {
        fwrite(STDERR, "Define ADMIN_PASSWORD en .env antes de ejecutar este script.\n");
        exit(1);
    }

    $passwordError = Validator::password($password);

    if ($passwordError !== null) {
        fwrite(STDERR, $passwordError . "\n");
        exit(1);
    }

    $rolId = Rol::idByNombre('admin');

    if ($rolId === null) {
        fwrite(STDERR, "No existe el rol admin. Ejecuta backEnd/database/seed.sql primero.\n");
        exit(1);
    }

    $existente = Usuario::findByEmail($email);

    if ($existente !== null) {
        fwrite(STDOUT, sprintf(
            "El administrador %s ya existe (id %d). No se modificó.\n",
            $email,
            (int) $existente['id']
        ));
        exit(0);
    }

    $id = Usuario::create(
        $nombre,
        $email,
        password_hash($password, PASSWORD_DEFAULT),
        $rolId
    );

    fwrite(STDOUT, sprintf(
        "Administrador creado: %s (id %d).\n",
        $email,
        $id
    ));
    exit(0);
} catch (Throwable $exception) {
    fwrite(STDERR, 'Error: ' . $exception->getMessage() . "\n");
    exit(1);
}
