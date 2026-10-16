---
name: resonaria-topic-cover
description: Generate and upload a 16:9 WebP topic cover for Resonaria without burned-in headlines, using set_topic_cover MCP.
---

# Topic cover (Resonaria)

## Rules

Follow `portal/bible/cover-guidelines.md` and `design/tokens.json` palette.

- Master **16:9**, ≥1600×900, WebP output.
- **No headline text in the image**—UI renders titles.
- Calm left ~55% for overlay; illustration on the right.
- Generated art is ambiance, not evidence.

## Upload

MCP `set_topic_cover`:

- Same `slug` as the topic
- `image_base64` (PNG/JPEG/WebP) or HTTPS `image_url`

Same Bearer token as other MCP tools.

## Checklist

- [ ] Motif readable at card and OG crop
- [ ] Alt text descriptive in editorial workflow
- [ ] Provenance noted internally if AI-generated
