# Deploy to production

Generic checklist for any Resonaria instance. Replace placeholders with your host paths and domain.

## 1. Prepare secrets locally

```bash
cp deploy/secrets.env.example deploy/secrets.env
# Fill database and paths — never commit secrets.env
```

Generate Laravel key once:

```bash
cd apps/api && php artisan key:generate --show
```

Copy into server `apps/api/.env` as `APP_KEY`.

## 2. Server requirements

- PHP **8.3** with `mbstring`, `pdo_mysql`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`
- Composer, MySQL 8+, nginx (or equivalent)
- Optional for social tools: `yt-dlp`, `ffmpeg`, `whisper-cli`

Build the static front **on CI or your laptop**:

```bash
cd apps/web
NUXT_PUBLIC_API_BASE=/api NUXT_PUBLIC_SITE_URL=https://YOUR_DOMAIN npm run generate
```

Sync code and `.output/public` to the server (rsync, git pull, or your pipeline).

## 3. First migration on server

```bash
cd {INSTALL_DIR}/apps/api
cp .env.example .env   # then edit for production
composer install --no-dev
php artisan migrate --force
php artisan db:seed --force   # categories only, if seeder enabled
php artisan config:cache
php artisan route:cache
```

Set:

- `APP_URL=https://YOUR_DOMAIN`
- `APP_DEBUG=false`
- `NUXT_PUBLIC_*` and `PORTAL_*` matching your portal bible
- `DISCOVERY_PUBLIC_PATH` if sitemap/robots should write to a public directory

## 4. Nginx

Use [deploy/nginx.conf.example](../../deploy/nginx.conf.example). Ensure:

- `/api/` and `/mcp` → Laravel `index.php`
- `/` → Nuxt static `200.html` fallback for client routes

## 5. MCP token on server

```bash
php artisan mcp:token
```

Configure agents with `https://YOUR_DOMAIN/mcp` — see [../07-ia/mcp-production.md](../07-ia/mcp-production.md).

## 6. Updates

- Pull or rsync new code
- `composer install --no-dev`
- `php artisan migrate --force`
- Regenerate Nuxt when front changes
- `php artisan discovery:publish` after editorial publishes

## Instance-specific operations

Production hostnames, SSH users, and database sync scripts for a **private** deployment are not part of the public OSS docs. Keep them in your operator runbook or `docs/interno/` (gitignored).
