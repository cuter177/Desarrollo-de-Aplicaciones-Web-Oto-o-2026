#!/usr/bin/env bash
# Compila los cuatro PDF con latexbuild y empaqueta el código sin secretos.
set -euo pipefail

RAIZ="$(cd "$(dirname "$0")/.." && pwd)"
LATEXBUILD="/usr/bin/latexbuild"
DIST="$RAIZ/dist"
FECHA="$(date +%Y%m%d)"
ZIP="$DIST/devstudio-codigo-${FECHA}.zip"

compilar() {
  local carpeta="$1"
  local archivo="$2"
  echo "Compilando ${carpeta}/${archivo}"
  (cd "$RAIZ/$carpeta" && "$LATEXBUILD" "$archivo")
}

compilar reporte reporte.tex
compilar manual-usuario manual-usuario.tex
compilar manual-tecnico manual-tecnico.tex
compilar contrato contrato.tex

mkdir -p "$DIST/pdf"
cp "$RAIZ/reporte/build/reporte.pdf" "$DIST/pdf/reporte.pdf"
cp "$RAIZ/manual-usuario/build/manual-usuario.pdf" "$DIST/pdf/manual-usuario.pdf"
cp "$RAIZ/manual-tecnico/build/manual-tecnico.pdf" "$DIST/pdf/manual-tecnico.pdf"
cp "$RAIZ/contrato/build/contrato.pdf" "$DIST/pdf/contrato.pdf"

rm -f "$ZIP"
(
  cd "$RAIZ"
  zip -r "$ZIP" . \
    -x "./.env" \
    -x "./.env.local" \
    -x "./.git/*" \
    -x "./dist/*" \
    -x "./*/build/*" \
    -x "./backEnd/storage/avatars/*" \
    -x "./backEnd/storage/avatars/.gitkeep" \
    -x "*/node_modules/*" \
    -x "*/.DS_Store"
  zip "$ZIP" "./backEnd/storage/avatars/.gitkeep"
)

echo "PDF: $DIST/pdf"
echo "ZIP: $ZIP"
