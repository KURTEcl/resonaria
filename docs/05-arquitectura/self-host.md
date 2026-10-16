# Self-host

## Topología mínima

- **nginx** — TLS, proxy a Nuxt y Laravel
- **php-fpm** — Laravel `apps/api`
- **Node** — Nuxt `apps/web` (SSR) o contenedor dedicado
- **MySQL 8+** (desarrollo local en este proyecto) o **PostgreSQL 16+** (alternativa documentada para despliegues)
- **Redis**

## Desarrollo local

```bash
# API (MySQL)
cp apps/api/.env.example apps/api/.env
# Editar DB_PASSWORD y demás en apps/api/.env

./scripts/bootstrap-mysql.sh

./dev-start.sh          # artisan + nuxt
./dev-start.sh --docker # cuando exista deploy/docker-compose.yml
```

Variables clave: ver [.env.example](../../.env.example). En desarrollo, `dev-start.sh` usa por defecto **API 8017** y **Nuxt 3017** (`API_PORT` / `WEB_PORT` en `.env` raíz) para no chocar con otros proyectos en 8000/3000.

## Producción

- HTTPS obligatorio; `APP_URL` con dominio real.
- `APP_DEBUG=false`
- Backups diarios de Postgres + prueba de restauración.
- Secretos solo en `.env` o gestor del host (no en git).

## Staging

- `noindex` global o `robots.txt` disallow `/`
- Base de datos separada

## Actualizaciones

- Migraciones Laravel con ventana de mantenimiento breve.
- Versionar releases en git tags; notas en CHANGELOG (futuro).

## IA

- Las claves de LLM pueden vivir en el **cliente del agente** (Cursor, etc.); el servidor no requiere proveedor de IA para funcionar.
- MCP opcional: [integracion-mcp.md](integracion-mcp.md)

## Docker (futuro)

`deploy/docker-compose.yml` definirá servicios `api`, `web`, `db`, `redis`, `nginx`. Datos en volumen `deploy/data/` (gitignored).
