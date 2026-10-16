#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

echo "== Resonaria install wizard =="
echo "Repository: $ROOT"
echo ""

if [[ ! -f apps/api/.env ]]; then
  echo "→ Copying apps/api/.env.example → apps/api/.env"
  cp apps/api/.env.example apps/api/.env
fi

if [[ ! -f .env ]]; then
  echo "→ Copying .env.example → .env"
  cp .env.example .env
fi

echo "→ Generating portal bible and env snippets (php artisan portal:init)"
(cd apps/api && php artisan portal:init)

echo ""
echo "Merge these into your env files (review before overwriting secrets):"
echo "  portal/env-snippets/root.env  →  .env"
echo "  portal/env-snippets/api.env   →  apps/api/.env"
echo ""
echo "Optional: copy MCP config"
echo "  portal/env-snippets/mcp.json  →  .cursor/mcp.json"
echo ""
read -r -p "Run MySQL bootstrap now? [y/N] " bootstrap
if [[ "${bootstrap,,}" == "y" ]]; then
  ./scripts/bootstrap-mysql.sh
fi

echo ""
echo "Start dev servers: ./dev-start.sh"
echo "Install agent skills: ./scripts/install-agent-skills.sh"
