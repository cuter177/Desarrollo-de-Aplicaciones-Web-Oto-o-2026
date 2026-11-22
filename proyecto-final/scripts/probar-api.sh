#!/usr/bin/env bash
# Pruebas de la etapa 5: portal del cliente, aislamiento y CRUD administrativo.
# Uso: ./scripts/probar-api.sh [base_url]
set -euo pipefail

BASE="${1:-http://localhost:8080/api/v1}"
TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT

ok=0
fail=0

check() {
  local nombre="$1"
  local esperado="$2"
  local real="$3"

  if [[ "$real" == "$esperado" ]]; then
    printf 'OK   %s\n' "$nombre"
    ok=$((ok + 1))
  else
    printf 'FAIL %s (esperado %s, obtuvo %s)\n' "$nombre" "$esperado" "$real"
    fail=$((fail + 1))
  fi
}

request() {
  local jar="$1"
  local metodo="$2"
  local ruta="$3"
  local cuerpo="${4:-}"
  local token="${5:-}"
  local args=(-sS -o "$TMP/body.json" -w '%{http_code}' -b "$jar" -c "$jar" -X "$metodo" -H 'Accept: application/json')

  if [[ -n "$token" ]]; then
    args+=(-H "X-CSRF-Token: $token")
  fi

  if [[ -n "$cuerpo" ]]; then
    args+=(-H 'Content-Type: application/json' --data "$cuerpo")
  fi

  curl "${args[@]}" "$BASE$ruta"
}

campo() {
  python3 -c 'import json,sys
cur=json.load(open(sys.argv[1]))
for parte in sys.argv[2].split("."):
    cur=cur[parte]
print("" if cur is None else cur)' "$TMP/body.json" "$1"
}

login() {
  local jar="$1"
  local email="$2"
  local password="$3"
  request "$jar" GET /auth/csrf >/dev/null
  local token payload
  token="$(campo data.csrf_token)"
  payload="$(EMAIL="$email" PASSWORD="$password" python3 -c 'import json,os; print(json.dumps({"email":os.environ["EMAIL"],"password":os.environ["PASSWORD"]}))')"
  request "$jar" POST /auth/login "$payload" "$token" >/dev/null
  campo data.csrf_token
}

if ! curl -sf "$BASE/health" >/dev/null; then
  echo "La API no responde en $BASE. Levanta los contenedores antes de ejecutar las pruebas."
  exit 1
fi

admin_jar="$TMP/admin.cookies"
cliente_jar="$TMP/cliente.cookies"
otro_jar="$TMP/otro.cookies"

admin_token="$(login "$admin_jar" "admin@devstudio.local" "Admin#2026")"
cliente_token="$(login "$cliente_jar" "cliente@devstudio.local" "Cliente#2026")"
otro_token="$(login "$otro_jar" "carlos.ruiz@innovatech.mx" "Cliente#2026")"

check "admin entra al dashboard" 200 "$(request "$admin_jar" GET /admin/dashboard)"
check "cliente no entra a usuarios" 403 "$(request "$cliente_jar" GET /admin/usuarios)"
check "admin no usa el portal" 403 "$(request "$admin_jar" GET /mi/resumen)"
check "cliente ve su resumen" 200 "$(request "$cliente_jar" GET /mi/resumen)"
check "otro cliente ve su resumen" 200 "$(request "$otro_jar" GET /mi/resumen)"

request "$cliente_jar" GET /mi/proyectos >/dev/null
proyecto_id="$(python3 -c 'import json,sys; datos=json.load(open(sys.argv[1]))["data"]; print(datos[0]["id"] if datos else 0)' "$TMP/body.json")"

if [[ "$proyecto_id" != "0" ]]; then
  check "otro cliente no ve ese proyecto" 404 "$(request "$otro_jar" GET "/mi/proyectos/$proyecto_id")"
else
  printf 'FAIL no hay proyectos del cliente demo para probar aislamiento\n'
  fail=$((fail + 1))
fi

check "alta de solicitud sin CSRF" 403 "$(request "$cliente_jar" POST /mi/solicitudes '{"descripcion":"Solicitud sin token de seguridad."}')"

marca="etapa5-$(date +%s)"
payload="$(MARCA="$marca" python3 -c 'import json,os; print(json.dumps({"descripcion":"Solicitud de prueba "+os.environ["MARCA"]+" para el portal."}))')"
check "cliente crea solicitud" 201 "$(request "$cliente_jar" POST /mi/solicitudes "$payload" "$cliente_token")"
solicitud_id="$(campo data.id)"
check "el otro cliente no ve esa solicitud" 404 "$(request "$otro_jar" GET "/mi/solicitudes/$solicitud_id")"
check "el cliente sí ve su solicitud" 200 "$(request "$cliente_jar" GET "/mi/solicitudes/$solicitud_id")"

request "$cliente_jar" GET /mi/perfil >/dev/null
telefono_original="$(campo data.cliente.telefono)"
check "cliente actualiza perfil" 200 "$(request "$cliente_jar" PATCH /mi/perfil '{"telefono":"5512345678"}' "$cliente_token")"
restaurar="$(TELEFONO="$telefono_original" python3 -c 'import json,os; print(json.dumps({"telefono":os.environ["TELEFONO"]}))')"
request "$cliente_jar" PATCH /mi/perfil "$restaurar" "$cliente_token" >/dev/null

payload="$(MARCA="$marca" python3 -c 'import json,os; print(json.dumps({"nombre":"Prueba "+os.environ["MARCA"],"categoria":"Prueba","activo":True}))')"
check "admin crea tecnología" 201 "$(request "$admin_jar" POST /admin/tecnologias "$payload" "$admin_token")"
tecnologia_id="$(campo data.id)"
check "admin desactiva tecnología" 200 "$(request "$admin_jar" DELETE "/admin/tecnologias/$tecnologia_id" '{}' "$admin_token")"

check "admin lista usuarios" 200 "$(request "$admin_jar" GET /admin/usuarios)"
if python3 -c 'import sys; texto=open(sys.argv[1]).read(); raise SystemExit(0 if "password_hash" not in texto else 1)' "$TMP/body.json"; then
  printf 'OK   usuarios no exponen password_hash\n'
  ok=$((ok + 1))
else
  printf 'FAIL usuarios exponen password_hash\n'
  fail=$((fail + 1))
fi

check "slug de servicio duplicado responde 409" 409 "$(request "$admin_jar" POST /admin/servicios '{"nombre":"Servicio duplicado de prueba","descripcion":"Descripcion de prueba automatizada.","slug":"desarrollo-web"}' "$admin_token")"

printf '\nResultado: %s correctas, %s fallidas\n' "$ok" "$fail"
[[ "$fail" -eq 0 ]]
