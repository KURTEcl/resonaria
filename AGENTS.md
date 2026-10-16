# Agent installation guide — Resonaria

Use this file when a human asks you to **install or configure** a Resonaria portal on their machine or server. Work in order; do not skip secrets handling.

## One-line handoff (for humans)

```text
Clone https://github.com/kurtecl/resonaria, open AGENTS.md, and execute every step until MCP and dev URLs respond.
```

Raw URL: `https://github.com/kurtecl/resonaria/blob/main/AGENTS.md`

## 1. Clone and wizard

```bash
git clone https://github.com/kurtecl/resonaria.git
cd resonaria
chmod +x scripts/install-wizard.sh scripts/install-agent-skills.sh
./scripts/install-wizard.sh
```

If non-interactive:

```bash
cd apps/api && php artisan portal:init --non-interactive \
  --name="My Portal" \
  --tagline="What changes. Who is affected." \
  --site-url="https://example.org"
```

Merge generated snippets:

- `portal/env-snippets/root.env` → repository `.env`
- `portal/env-snippets/api.env` → `apps/api/.env`

Read the generated **portal bible**: `portal/bible/README.md`.

## 2. Database and dev servers

```bash
./scripts/bootstrap-mysql.sh
./dev-start.sh
```

Verify:

- `curl -s http://127.0.0.1:8017/api/v1/health`
- `curl -s http://127.0.0.1:8017/api/v1/portal`
- Open `http://127.0.0.1:3017`

Adjust `API_PORT` / `WEB_PORT` in root `.env` if ports are busy.

## 3. MCP token

```bash
cd apps/api
php artisan mcp:token
```

Store the token in a secret manager. Export for local agents:

```bash
export RESONARIA_MCP_TOKEN="paste-token"
```

Local MCP URL (default): `http://127.0.0.1:8017/mcp`  
Production: `https://{YOUR_DOMAIN}/mcp`

## 4. Cursor MCP

Copy `portal/env-snippets/mcp.json` to `.cursor/mcp.json` or merge:

```json
{
  "mcpServers": {
    "resonaria": {
      "url": "http://127.0.0.1:8017/mcp",
      "headers": {
        "Authorization": "Bearer YOUR_TOKEN"
      }
    }
  }
}
```

## 5. Codex CLI

```bash
export RESONARIA_MCP_TOKEN="your-token"
codex mcp add resonaria --url http://127.0.0.1:8017/mcp \
  --bearer-token-env-var RESONARIA_MCP_TOKEN
```

## 6. Skills

From repo root:

```bash
./scripts/install-agent-skills.sh
```

Skills: `resonaria-create-case`, `resonaria-social-research`, `resonaria-topic-cover`, `resonaria-profiles`.

Load `portal/bible/voice-and-format.md` and `portal/bible/editorial-scope.md` before publishing.

## 7. Production checklist

Human must provide:

| Variable | Example |
| --- | --- |
| Public site URL | `https://news.example.org` |
| `APP_URL` | same origin as API |
| `NUXT_PUBLIC_SITE_URL` | public site |
| TLS + nginx | [deploy/nginx.conf.example](deploy/nginx.conf.example) |
| `php artisan migrate --force` | on server |

After deploy, rotate MCP tokens if they were ever pasted into chat.

## 8. Editorial policy

**Do not** assume Chilean budget politics or Resonaria.cl defaults. Follow `portal/bible/*` for tone and content types.

Human review is required before treating MCP output as published fact—see `docs/07-ia/politica-de-uso.md`.

## Support

Open-source maintenance: [https://ko-fi.com/kurtecl](https://ko-fi.com/kurtecl)
