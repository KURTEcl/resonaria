---
name: resonaria-social-research
description: After a Resonaria topic is published, find news and social reactions and add them via MCP without rewriting the ficha. Use for add_source, add_reaction, add_claim_check.
---

# Social and news research (Resonaria MCP)

## When

After `create_topic` or when updating an existing slug.

## Steps per topic

1. `get_topic` for current `sources` and `reactions`.
2. Search for **new** documents and press → `add_source`.
3. Instagram, TikTok, X, Threads, Reddit permalinks → `add_reaction` (platform must match URL).
4. Viral claims stated as fact → `add_claim_check` (paraphrase idea; no thread drama).
5. Related portal notes → `add_link` + `[[slug|text]]` in body.

## Limits

- `inspect_social_post`: Instagram, TikTok, X only; temporary read, does not store video.
- Social posts are **not** evidence for numbers; do not rewrite the ficha from engagement.

## References

- `portal/bible/editorial-scope.md`
- `docs/07-ia/mcp-overview.md`
