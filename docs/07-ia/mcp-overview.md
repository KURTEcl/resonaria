# MCP overview

Resonaria exposes an **HTTP MCP** endpoint on the Laravel app (`POST /mcp`), authenticated with a **Sanctum bearer token**.

## Security

- Generate: `cd apps/api && php artisan mcp:token`
- Store as `RESONARIA_MCP_TOKEN` (legacy env name `QUEPERDIMOS_MCP_TOKEN` may still work in old notes—prefer `RESONARIA_MCP_TOKEN`).
- Never commit tokens or paste them into public chats.
- Without a token, `POST /mcp` must return **401**, not 404.

## URLs

| Environment | URL |
| --- | --- |
| Local default | `http://127.0.0.1:{API_PORT}/mcp` |
| Production | `https://{YOUR_DOMAIN}/mcp` |

Replace `{API_PORT}` with root `.env` `API_PORT` (default `8017`).

## Tooling docs

- [mcp-cursor.md](mcp-cursor.md)
- [mcp-codex.md](mcp-codex.md)
- [mcp-production.md](mcp-production.md)

## Agent workflow

See [../agents/README.md](../agents/README.md) and your instance `portal/bible/mcp-setup.md`.

Legacy single-page guide: [configurar-chatgpt-mcp.md](configurar-chatgpt-mcp.md) (being replaced by the split docs above).
