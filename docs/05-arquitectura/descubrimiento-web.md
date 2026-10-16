# Descubrimiento web (sitemap, robots, llms)

Laravel genera los archivos desde los temas `published`. Tras cada publicación o edición, se reescriben en el htdocs (`DISCOVERY_PUBLIC_PATH`) y las mismas rutas se sirven en vivo si nginx apunta a PHP.

## Rutas

| Ruta | Origen |
| --- | --- |
| `/sitemap.xml` | Temas publicados + páginas estáticas |
| `/robots.txt` | Allow público; Disallow admin, API, mesa, MCP |
| `/llms.txt` | Reglas de citación + lista automática de fichas |
| `/llms-full.txt` | Título, URL, área, etapa y gancho en texto plano |
| `/google{token}.html` | Verificación Search Console si `GOOGLE_SITE_VERIFICATION` está definido |

`<lastmod>` usa `reviewed_at` o `published_at`. Admin, API, previews, `/cuaderno` y paginación filtrada no entran al sitemap.

El slug de la ficha es estable (se genera al publicar). Un título posterior distinto **no** cambia la URL: Google usa `<title>`, H1 y canonical; el slug desalineado es una señal débil. No renombrar slugs en vivo sin `slug_history` y 301.

## robots.txt

- `Allow: /`
- `Disallow: /admin`, `/api/`, `/preview/`, `/cuaderno`, `/mcp`
- `Sitemap: https://resonaria.cl/sitemap.xml`

**Staging:** disallow `/` o autenticación.

### Crawlers de IA

- Permitir Google/Bing en contenido público.
- `llms.txt` guía la citación aunque un bot de entrenamiento esté bloqueado.
- Documentar decisión en [../09-open-source/gobernanza.md](../09-open-source/gobernanza.md).

## Google Search Console

1. Propiedad de prefijo `https://resonaria.cl/`.
2. Verificación: Analytics (`G-XEG6FF1Q1Z`), meta `google-site-verification`, o archivo HTML servido por Laravel.
3. Enviar sitemap `https://resonaria.cl/sitemap.xml`.
4. En la propiedad de `https://queperdimos.cl/`, usar Cambio de dirección hacia Resonaria cuando los 301 ya respondan. No borrar esa propiedad.

## Headers

- Preview borrador: `X-Robots-Tag: noindex, nofollow`
- HTTPS + HSTS en producción

## OG / meta / JSON-LD

- Nuxt `usePageSeo`: canonical, Open Graph, Twitter y JSON-LD `WebSite` (portada) / `Article` (ficha).
- La descripción de ficha se entrega en texto plano (sin `{+}`, `{-}`, `**`).

Ver [../01-producto/seo-urls-y-compartir.md](../01-producto/seo-urls-y-compartir.md).
