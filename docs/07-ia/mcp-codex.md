# MCP in Codex / ChatGPT desktop

The desktop app shares MCP configuration with [Codex CLI](https://learn.chatgpt.com/docs/extend/mcp).

## CLI setup

```bash
export RESONARIA_MCP_TOKEN="paste-from-php-artisan-mcp-token"
codex mcp add resonaria --url https://YOUR_DOMAIN/mcp \
  --bearer-token-env-var RESONARIA_MCP_TOKEN
```

Local development:

```bash
codex mcp add resonaria --url http://127.0.0.1:8017/mcp \
  --bearer-token-env-var RESONARIA_MCP_TOKEN
```

Restart the app or run `codex mcp list`.

## UI setup

**Settings → MCP servers → Add server**

- Type: **Streamable HTTP**
- URL: your `/mcp` endpoint
- Auth: Bearer token header or env var as above

## Browser ChatGPT

The web chat does not read local `mcp.json`. Use desktop Cursor/Codex or a published connector with OAuth.

## Skills

Copy instructions from repository `skills/resonaria-*` or run `./scripts/install-agent-skills.sh` for Cursor; for Codex, reference `docs/07-ia/skills-install.md`.
