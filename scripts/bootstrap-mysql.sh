#!/usr/bin/env bash
# Crea la base MySQL y ejecuta migraciones + seed en apps/api
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
API_DIR="$ROOT/apps/api"
ENV_FILE="$API_DIR/.env"

# shellcheck source=lib/read-env.sh
source "$ROOT/scripts/lib/read-env.sh"

if [[ ! -f "$ENV_FILE" ]]; then
  echo "Falta $ENV_FILE"
  echo "Copia: cp apps/api/.env.example apps/api/.env"
  echo "Luego define DB_PASSWORD (y DB_* si hace falta)."
  exit 1
fi

DB_CONNECTION="$(read_env_var "$ENV_FILE" DB_CONNECTION || true)"
if [[ "${DB_CONNECTION:-mysql}" != "mysql" ]]; then
  echo "DB_CONNECTION debe ser mysql (actual: ${DB_CONNECTION:-vacío})"
  exit 1
fi

DB_DATABASE="$(read_env_var "$ENV_FILE" DB_DATABASE || true)"
DB_USERNAME="$(read_env_var "$ENV_FILE" DB_USERNAME || true)"
DB_PASSWORD="$(read_env_var "$ENV_FILE" DB_PASSWORD || true)"
DB_HOST="$(read_env_var "$ENV_FILE" DB_HOST || true)"
DB_PORT="$(read_env_var "$ENV_FILE" DB_PORT || true)"

DB_DATABASE="${DB_DATABASE:-queperdimos}"
DB_USERNAME="${DB_USERNAME:-root}"
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"

if [[ -z "$DB_PASSWORD" ]]; then
  echo "DB_PASSWORD está vacío en $ENV_FILE"
  echo "Añade la clave de MySQL (usuario: ${DB_USERNAME})."
  exit 1
fi

MYSQL_CNF="$(mktemp)"
chmod 600 "$MYSQL_CNF"
trap 'rm -f "$MYSQL_CNF"' EXIT

cat > "$MYSQL_CNF" <<EOF
[client]
host=${DB_HOST}
port=${DB_PORT}
user=${DB_USERNAME}
password=${DB_PASSWORD}
EOF

echo "→ Creando base de datos '${DB_DATABASE}' (si no existe)…"
mysql --defaults-extra-file="$MYSQL_CNF" \
  -e "CREATE DATABASE IF NOT EXISTS \`${DB_DATABASE}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

echo "→ Migraciones y seed (Laravel lee apps/api/.env)…"
cd "$API_DIR"
php artisan migrate:fresh --seed --force

echo ""
echo "Listo."
echo "  ./dev-start.sh"
echo "  API: http://127.0.0.1:\${API_PORT:-8017}/api/v1/health"
