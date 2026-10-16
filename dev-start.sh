#!/usr/bin/env bash
# QuePerdimos — arranque de entorno de desarrollo local
# Uso: ./dev-start.sh [--docker]
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT"

COMPOSE_FILE="${COMPOSE_FILE:-$ROOT/deploy/docker-compose.yml}"
API_DIR="${API_DIR:-$ROOT/apps/api}"
WEB_DIR="${WEB_DIR:-$ROOT/apps/web}"

# Puertos por defecto poco usados (evitar 8000/3000 de otros proyectos)
API_PORT="${API_PORT:-8017}"
WEB_PORT="${WEB_PORT:-3017}"

log() { printf '%s\n' "$*"; }
warn() { printf '⚠ %s\n' "$*" >&2; }

load_env_ports() {
  local file=$1
  [[ -f "$file" ]] || return 0
  while IFS= read -r line || [[ -n "$line" ]]; do
    line="${line%%#*}"
    line="${line//$'\r'/}"
    [[ "$line" =~ ^[[:space:]]*$ ]] && continue
    if [[ "$line" =~ ^(API_PORT|WEB_PORT)= ]]; then
      # shellcheck disable=SC2163
      export "$line"
    fi
  done < "$file"
}

load_env_ports "$ROOT/.env"

API_PORT="${API_PORT:-8017}"
WEB_PORT="${WEB_PORT:-3017}"

port_listening() {
  local port=$1
  if command -v lsof >/dev/null 2>&1; then
    lsof -nP -iTCP:"$port" -sTCP:LISTEN >/dev/null 2>&1
    return $?
  fi
  if command -v nc >/dev/null 2>&1; then
    nc -z 127.0.0.1 "$port" >/dev/null 2>&1
    return $?
  fi
  return 1
}

require_free_port() {
  local port=$1 name=$2
  if port_listening "$port"; then
    warn "El puerto $port ($name) ya está en uso en este equipo."
    warn "Define otro en .env del repo, por ejemplo:"
    warn "  API_PORT=8021"
    warn "  WEB_PORT=3021"
    warn "Luego vuelve a ejecutar ./dev-start.sh"
    exit 1
  fi
}

usage() {
  cat <<EOF
QuePerdimos — dev-start.sh

Arranca el stack local (Laravel API + Nuxt).

Opciones:
  --docker    Usa deploy/docker-compose.yml si está presente
  --help      Muestra esta ayuda

Puertos (orden de precedencia: variables de entorno > .env en la raíz > valores por defecto):

  API_PORT   (defecto 8017)  → http://127.0.0.1:API_PORT
  WEB_PORT   (defecto 3017)  → http://127.0.0.1:WEB_PORT

Ejemplo en .env del repositorio:

  API_PORT=8847
  WEB_PORT=3847

Nuxt usa automáticamente NUXT_PUBLIC_API_BASE=http://127.0.0.1:\${API_PORT}/api al arrancar.

Otras variables: API_DIR, WEB_DIR, COMPOSE_FILE
EOF
}

if [[ "${1:-}" == "--help" || "${1:-}" == "-h" ]]; then
  usage
  exit 0
fi

if [[ "${1:-}" == "--docker" ]] && [[ -f "$COMPOSE_FILE" ]]; then
  log "→ Docker Compose: $COMPOSE_FILE"
  docker compose -f "$COMPOSE_FILE" up --build
  exit 0
fi

if [[ -d "$API_DIR" ]] && [[ -f "$API_DIR/artisan" ]] && [[ -d "$WEB_DIR" ]]; then
  command -v php >/dev/null 2>&1 || { warn "Falta php en PATH"; exit 1; }
  command -v npm >/dev/null 2>&1 || { warn "Falta npm en PATH"; exit 1; }

  require_free_port "$API_PORT" "API Laravel"
  require_free_port "$WEB_PORT" "Nuxt"

  export NUXT_PUBLIC_API_BASE="${NUXT_PUBLIC_API_BASE:-http://127.0.0.1:${API_PORT}/api}"

  log "→ API Laravel: http://127.0.0.1:${API_PORT} (api: ${NUXT_PUBLIC_API_BASE})"
  log "→ Nuxt:        http://127.0.0.1:${WEB_PORT}"
  log "   Puertos distintos a 8000/3000 por defecto; ajústalos en .env si hace falta."
  log "   (Ctrl+C detiene ambos procesos)"

  cleanup() {
    [[ -n "${API_PID:-}" ]] && kill "$API_PID" 2>/dev/null || true
    [[ -n "${WEB_PID:-}" ]] && kill "$WEB_PID" 2>/dev/null || true
  }
  trap cleanup EXIT INT TERM

  (cd "$API_DIR" && php artisan serve --host=127.0.0.1 --port="$API_PORT") &
  API_PID=$!

  (cd "$WEB_DIR" && npm run dev -- --host 127.0.0.1 --port "$WEB_PORT") &
  WEB_PID=$!

  wait -n "$API_PID" "$WEB_PID" 2>/dev/null || wait "$API_PID" "$WEB_PID" || true
  exit 0
fi

log "QuePerdimos — aún no hay apps/api ni deploy/docker-compose.yml."
log ""
log "Documentación: docs/README.md"
log "Puertos por defecto al arrancar: API ${API_PORT}, Web ${WEB_PORT}"
exit 0
