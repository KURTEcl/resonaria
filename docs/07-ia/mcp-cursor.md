# MCP in Cursor

## 1. Token

```bash
cd apps/api
php artisan mcp:token
```

## 2. Config

Copy [mcp.json.example](../../mcp.json.example) to `.cursor/mcp.json` (gitignored) or use project MCP settings:

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

Production: replace URL with `https://{YOUR_DOMAIN}/mcp`.

## 3. Skills

```bash
./scripts/install-agent-skills.sh
```

## 4. Instance bible

Load `portal/bible/voice-and-format.md` in rules or paste into the agent system prompt.

## Verify

```bash
curl -s -o /dev/null -w "%{http_code}\n" -X POST http://127.0.0.1:8017/mcp \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","id":1,"method":"tools/list"}'
```

Expect `401` without token, `200` with `Authorization: Bearer …`.
