# Resonaria

Stack de código abierto para un **portal editorial con fuentes trazables**: fichas públicas, perfiles, herramientas MCP y front Nuxt. Puedes bifurcarlo, ejecutar el asistente de instalación y definir nombre, tono y dominio de producción.

**Resonaria.cl** es una instancia de este software, no la única misión editorial posible.

| Recurso | Ubicación |
| --- | --- |
| Índice de documentación | [docs/README.md](docs/README.md) |
| Tokens de diseño | [design/tokens.json](design/tokens.json) · [DESIGN.md](DESIGN.md) |
| Instalación para agentes | [AGENTS.md](AGENTS.md) |
| Apoyar el proyecto | [Ko-fi](https://ko-fi.com/kurtecl) |

## Características

- API Laravel (casos, perfiles, cuaderno con Sanctum)
- Portal Nuxt con filtros, metodología y portadas WebP
- **MCP HTTP** para agentes (`create_topic`, `add_source`, `set_topic_cover`, …)
- Wizard que genera la **biblia del portal** en `portal/bible/`
- **Skills** para Cursor/Codex en [`skills/`](skills/)

## Requisitos

- PHP 8.3+, Composer, Node 20+, MySQL 8+

## Inicio rápido

```bash
git clone https://github.com/kurtecl/resonaria.git
cd resonaria
chmod +x scripts/install-wizard.sh scripts/install-agent-skills.sh
./scripts/install-wizard.sh
```

Fusiona `portal/env-snippets/root.env` y `api.env` en tus `.env`, luego:

```bash
./scripts/bootstrap-mysql.sh
./dev-start.sh
```

- API: `http://127.0.0.1:8017/api/v1/health`
- Web: `http://127.0.0.1:3017`

## Agentes de IA

Pega en Cursor, Codex o ChatGPT:

```text
Clona https://github.com/kurtecl/resonaria y sigue AGENTS.md de punta a punta:
wizard, biblia del portal, token MCP y URL de producción.
```

Instalar skills:

```bash
./scripts/install-agent-skills.sh
```

## Producción

[Self-host](docs/05-arquitectura/self-host.md) · [Despliegue](docs/08-operaciones/deploy.md) · [nginx.conf.example](deploy/nginx.conf.example)

## Licencia

MIT — [LICENSE](LICENSE). El contenido publicado en cada instancia puede tener otra licencia.
