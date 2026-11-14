# Scripts

Utilidades previstas para desarrollo y entrega. En `0.2.0` la documentación sigue siendo la única pieza de esta carpeta; el único script implementado es `backEnd/scripts/crear-admin.php`.

## Scripts planeados

| Script | Función | Uso previsto |
|---|---|---|
| `empaquetar.sh` | Genera el ZIP de entrega | Excluye `.git`, `.env`, `vendor`, `node_modules`, respaldos, auxiliares de LaTeX y `dist` |
| `respaldar-bd.sh` | Respaldo de SQL Server | Ejecuta `BACKUP DATABASE` y copia el `.bak` al host |
| `verificar-entorno.sh` | Comprueba Docker, Compose, puertos y variables | Falla con mensaje claro si falta algo |
| `cargar-bd.sh` | Aplica `schema.sql` y `seed.sql` | Evita escribir el comando de `sqlcmd` a mano |

## Reglas para los scripts

- `#!/usr/bin/env bash` y `set -euo pipefail`.
- Verificar dependencias antes de ejecutar.
- No imprimir contraseñas ni secretos.
- Confirmar acciones destructivas.
- Usar rutas relativas a la raíz del proyecto.
- Documentar cada script en este archivo y en el manual técnico.

## Empaquetado: contenido esperado

Incluir:

- `appWeb/`
- `backEnd/`
- `docs/`
- `docker/`
- `compose.yaml`
- `.env.example`
- `README.md`
- `LICENSE`

Excluir:

- `.git/`
- `.env`
- `vendor/`, `node_modules/`
- `dist/`, `*.zip`
- `*.bak`, `*.mdf`, `*.ldf`
- Auxiliares de LaTeX y PDFs generados
- Logs y archivos temporales

## Estado

`0.2.0` — Pendiente de implementación; el alta de administrador vive en `backEnd/scripts/`.
