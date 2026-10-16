# Resonaria

Open-source stack for a **source-driven publication portal**: public case pages, profile mentions, editorial MCP tools, and a Nuxt front end. Fork it, run the install wizard, and define your own name, voice, and production domain.

**Resonaria.cl** is one deployment of this software—not the only possible editorial mission.

| Resource | Location |
| --- | --- |
| Documentation index | [docs/README.md](docs/README.md) |
| Design tokens | [design/tokens.json](design/tokens.json) · [DESIGN.md](DESIGN.md) |
| Agent install (copy into your LLM) | [AGENTS.md](AGENTS.md) |
| Support the project | [Ko-fi](https://ko-fi.com/kurtecl) |

## Features

- Laravel API with public cases, profiles, and Sanctum-protected desk routes
- Nuxt SSR portal with filters, methodology pages, and topic covers (WebP)
- **MCP HTTP** endpoint for agents (`create_topic`, `add_source`, `set_topic_cover`, …)
- Install wizard generates a **portal bible** under `portal/bible/` (scope, tone, covers, MCP)
- Cursor / Codex **skills** in [`skills/`](skills/)

## Requirements

- PHP 8.3+, Composer, Node 20+, MySQL 8+
- Optional: Redis, yt-dlp + ffmpeg for social inspection tools

## Quick start

```bash
git clone https://github.com/kurtecl/resonaria.git
cd resonaria
chmod +x scripts/install-wizard.sh scripts/install-agent-skills.sh
./scripts/install-wizard.sh
```

The wizard copies `.env` templates, runs `php artisan portal:init`, and writes snippets in `portal/env-snippets/`. Merge those into `.env` and `apps/api/.env`, then:

```bash
./scripts/bootstrap-mysql.sh   # if not done in wizard
./dev-start.sh
```

- API: `http://127.0.0.1:8017/api/v1/health` (override with `API_PORT`)
- Web: `http://127.0.0.1:3017` (override with `WEB_PORT`)

## AI agents

Paste this into Cursor, Codex, or ChatGPT:

```text
Clone https://github.com/kurtecl/resonaria and follow AGENTS.md end-to-end:
install wizard, portal bible, MCP token, and production URL placeholders.
```

Details: [AGENTS.md](AGENTS.md) · MCP docs in [docs/07-ia/](docs/07-ia/)

Install bundled skills on your machine:

```bash
./scripts/install-agent-skills.sh
```

## Production

Generic self-host guide: [docs/05-arquitectura/self-host.md](docs/05-arquitectura/self-host.md)  
Step-by-step deploy: [docs/08-operaciones/deploy.md](docs/08-operaciones/deploy.md)  
Nginx example: [deploy/nginx.conf.example](deploy/nginx.conf.example)

Set `NUXT_PUBLIC_SITE_URL` and `APP_URL` to your domain. Expose `POST /mcp` on the same host as the API.

## Open-source export

To produce a clean public tree (exclude private editorial assets):

```bash
./scripts/prepare-oss-export.sh
```

See [docs/09-open-source/publicar-repositorio.md](docs/09-open-source/publicar-repositorio.md).

## License

MIT — [LICENSE](LICENSE). Published **content** on each instance may use a different license; declare it on your site.

## Disclaimer

This software helps publish information with traceable sources; it does **not** replace human editorial, legal, or fact-checking judgment.
