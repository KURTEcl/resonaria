# MCP setup — {{PORTAL_NAME}}

**Endpoint:** `POST {{PORTAL_MCP_URL}}`

**Auth:** `Authorization: Bearer <token>` (Sanctum). Generate on the server:

```bash
cd apps/api
php artisan mcp:token
```

Never commit tokens. Store in a password manager or agent env var `RESONARIA_MCP_TOKEN`.

## Cursor

Copy `portal/env-snippets/mcp.json` to `.cursor/mcp.json` and replace the bearer placeholder.

## Codex / ChatGPT desktop

```bash
export RESONARIA_MCP_TOKEN="your-token"
codex mcp add resonaria --url {{PORTAL_MCP_URL}} \
  --bearer-token-env-var RESONARIA_MCP_TOKEN
```

## Local development

With `./dev-start.sh`, MCP is available at `http://127.0.0.1:8017/mcp` (adjust port via `API_PORT`).

Full docs: `docs/07-ia/mcp-overview.md`
