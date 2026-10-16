# Integración MCP (laravel/mcp)

**Opcional.** La API REST ([contratos-api.md](contratos-api.md)) es el contrato canónico; MCP expone tools para clientes compatibles (Cursor, Claude Desktop, ChatGPT con conector HTTP).

## Paquete

[`laravel/mcp`](https://laravel.com/docs/mcp) — registro en `routes/ai.php`:

- `Mcp::local()` — desarrollo (stdio / Artisan)
- `Mcp::web()` — HTTP con middleware **Sanctum** o Passport

`MCP_ENABLED=false` en `.env` desactiva rutas en self-host que no quieran MCP.

## Diseño

- Tools llaman los **mismos Actions/Services** que la API REST.
- No exponer SQL genérico ni operaciones destructivas a tokens de agente.

## Tools iniciales (borrador)

| Tool | Equivalente API | Notas |
| --- | --- | --- |
| `search_cases` | `GET /admin/cases?q=` | Solo lectura |
| `get_case` | `GET /admin/cases/{id}` | |
| `upsert_draft_case` | POST/PATCH cases | Solo `draft` |
| `add_claim` | POST claims | |
| `link_source` | POST sources | Validar URL (SSRF) |
| `list_pending_review` | GET review-queue | Scope review |
| `create_social_pack` | — | Solo si `published`; genera borrador de redes |

**Excluir por defecto en tokens `agente_ia`:** `publish`, `delete_case`.

## Resources (solo lectura)

- Metodología resumida
- Glosario de estados procedimentales
- Plantilla de ficha

## Prompts

Plantillas alineadas a [../agents/](../agents/) para comportamiento consistente entre clientes.

## Catálogo grande

Usar **searchable tool catalog** de Laravel MCP (`search_tools` / `execute_tools`) si el número de tools crece.

## Seguridad

- OAuth2/Passport o tokens Sanctum de corta vida
- Auditoría: `agent_token_id`, tool, payload hash, timestamp
- Rate limiting por token

Ver [../07-ia/orquestacion-agentes.md](../07-ia/orquestacion-agentes.md).
