---
name: resonaria-create-case
description: Create and publish a Resonaria tracking topic via MCP with correct visual markup and headlines. Use when drafting cases, fichas, or calling create_topic.
---

# Create a case (Resonaria MCP)

## Before writing

1. Read `portal/bible/editorial-scope.md` and `portal/bible/voice-and-format.md` for this instance.
2. MCP: `list_categories`, then `list_topics` to avoid duplicate headline patterns.
3. Never invent figures; cite sources in `detail` with `[n]`.

## Required field style

`hook`, `what_changes`, `who_affected`, `before_text`, `after_text`, `detail` must use:

- `**bold**` for limits (`**not law yet**`)
- `{-loss}` / `{+gain}` color markers (plain language inside braces)
- Not flat prose

Headline names the **program or right affected**, not a repeated legal vehicle shared across many topics.

## Publish flow

1. Assemble JSON fields from verified sources.
2. `create_topic` with chosen `category_slug`.
3. Human review if policy requires—token may publish live.
4. Hand off to **resonaria-social-research** and **resonaria-topic-cover**.

## References

- Agent prompt template: `docs/agents/periodista-investigador.md` (adapt to portal bible)
- MCP tools: `docs/05-arquitectura/integracion-mcp.md`
