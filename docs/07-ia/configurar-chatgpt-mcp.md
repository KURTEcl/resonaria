# Configurar ChatGPT (u otro cliente) para crear temas

> **Documentación actualizada:** usa la guía dividida [mcp-overview.md](mcp-overview.md) (Cursor, Codex, producción). Este archivo se mantiene como referencia extendida del flujo editorial.

El agente editorial habla con Laravel por **MCP HTTP** en `POST https://{TU_DOMINIO}/mcp`, autenticado con un **token Sanctum**.

## 1. Servidor (una vez)

1. **Nginx / CloudPanel:** además de `location ^~ /api/`, debe existir `location ^~ /mcp` hacia el mismo `index.php` de Laravel (ver [`deploy/nginx-queperdimos.conf.example`](../../deploy/nginx-queperdimos.conf.example)).
2. **Token:** en el servidor:
   ```bash
   cd {INSTALL_DIR}/apps/api
   php artisan mcp:token
   ```
   Guarda el token en un gestor de secretos (1Password, etc.). **No lo pegues en chats.** Si se filtró, revoca el token en `personal_access_tokens` y genera otro.

3. Comprueba que sin token responde **401** (no 404 ni 500):
   ```bash
   curl -s -o /dev/null -w "%{http_code}\n" -X POST https://{TU_DOMINIO}/mcp \
     -H "Content-Type: application/json" \
     -d '{"jsonrpc":"2.0","id":1,"method":"tools/list"}'
   ```

## 2. ChatGPT desktop (Codex) — recomendado hoy

La app de escritorio comparte configuración con Codex CLI ([documentación MCP](https://learn.chatgpt.com/docs/extend/mcp)).

### Opción A: interfaz

1. **Settings → MCP servers → Add server**
2. Tipo: **Streamable HTTP**
3. URL: `https://{TU_DOMINIO}/mcp`
4. Autenticación: header `Authorization: Bearer <tu-token>` (si la UI lo pide) o variable de entorno (opción B).

### Opción B: CLI

```bash
export RESONARIA_MCP_TOKEN="pegá-el-token-aquí"
codex mcp add resonaria --url https://{TU_DOMINIO}/mcp \
  --bearer-token-env-var RESONARIA_MCP_TOKEN
```

Reinicia la app o `codex mcp list`. En el compositor, `/mcp` muestra servidores conectados.

## 3. ChatGPT en el navegador

El chat web **no** lee tu `config.toml` local. Ahí los tools suelen venir de **plugins** del workspace o conectores con **OAuth**. Para un servidor propio con solo Bearer, lo más directo es la **app de escritorio** o **Cursor** (MCP local/remoto).

Si más adelante publicas un plugin OpenAI, habrá que añadir OAuth según [guía de autenticación de conectores](https://developers.openai.com/plugins/build/auth).

## 4. Cursor (alternativa)

En `.cursor/mcp.json` (o configuración MCP del proyecto):

```json
{
  "mcpServers": {
    "resonaria": {
      "url": "https://{TU_DOMINIO}/mcp",
      "headers": {
        "Authorization": "Bearer TU_TOKEN"
      }
    }
  }
}
```

En desarrollo local también puedes usar `php artisan mcp:start` / servidor local según [laravel/mcp](https://laravel.com/docs/mcp).

## 5. Cómo debe trabajar el agente

1. Pegar en el sistema el prompt de [`docs/agents/periodista-investigador.md`](../agents/periodista-investigador.md) (fuentes, no inventar cifras, etc.).
2. Llamar **`list_categories`** y elegir un `category_slug` (p. ej. `salud`, `educacion`, `trabajo-y-pensiones`).
3. Armar el contenido con evidencia y con el formato visual obligatorio del portal. **No entregar prosa plana:** `hook`, `what_changes`, `who_affected`, `before_text`, `after_text` y `detail` usan `**negrita**`, `{-pérdida}` en rojo, `{+aumento o continuidad}` en verde, `[n]` y `##` en `detail`. El **titular** nombra el programa o derecho afectado; no empieces varios casos con «Presupuesto 2027» ni copies la fórmula de otro título (`list_topics` antes). Luego llamar **`create_topic`**.
4. Tras publicar (y en cada revisión), pasar por **cada tema** de `list_topics`: `get_topic`, buscar noticias y redes que no estén en `sources`/`reactions`, y cargarlas. Documento o prensa → **`add_source`**. Instagram, TikTok, X, Threads o Reddit → **`add_reaction`**. Para Reddit, usa permalinks HTTPS de `reddit.com` o `old.reddit.com` con ruta `/r/{comunidad}/comments/{id}`. Para Threads, usa `threads.com` o `threads.net` con `/@usuario/post/{id}` o `/t/{id}`. `inspect_social_post` sigue limitado a Instagram, TikTok y X. No reescribas la ficha con un post viral.
5. Si en comentarios o en otros medios se repite una idea como si fuera un hecho, **`add_claim_check`**: parafrasea la idea (sin nombres ni hilos), di si se sostiene, no se sostiene, queda a medias o falta el dato, y enlaza la fuente. No sintetices «opinión de la gente».
6. Si otra nota del portal da contexto o es consecuencia, **`add_link`** (`contexto`, `consecuencia`, `antecedente` o `relacionado`) y, en el cuerpo, el markup `[[slug|texto visible]]` que devuelve la tool. Mira `list_topics` para no inventar slugs.
7. Para el seguimiento: **`add_update`** sobre el mismo `slug`. Un hito por hecho nuevo (trámite, votación, anuncio, conversación pública o fuente). En **seguimiento de decisión**, `direction`: `riesgo`, `reversion` o `incierto`. En **noticia en desarrollo** (clima, emergencia, sin `change_types` ni estado de medida), hitos factuales con `occurred_at` si hay hora; no uses lenguaje de «propuesta negativa». En copy público evita palabras de doble sentido en Chile (p. ej. «pico»); usa «situación previa» / intensificación. Ver [estados-y-lenguaje.md](../01-producto/estados-y-lenguaje.md). No reescribe la ficha.
8. Para leer una publicación (pie, autor, transcripción temporal): **`inspect_social_post`**. No guarda el video ni publica el tema. Testimonio de redes ≠ hecho.
9. Tras generar la ilustración 16:9 (sin titular quemado en la imagen; ver [base-publicaciones-redes.md](base-publicaciones-redes.md)), sube la portada con **`set_topic_cover`**: mismo `slug` que el tema, y **`image_base64`** (PNG/JPEG/WebP) o **`image_url`** HTTPS pública. El servidor guarda `/casos/{slug}/cover.webp` y actualiza la ficha. No hace falta token del cuaderno ni `curl` si usas el mismo Bearer que el MCP (`QUEPERDIMOS_MCP_TOKEN`).

**Importante:** hoy `create_topic` deja el tema en estado **publicado** en el portal. La política editorial en docs prevé revisión humana; hasta que exista flujo `draft` + `publish`, trata el token como **llave de publicación**.

Antes de llamar `create_topic`, revisar que:

- el titular nombra el afectado (no «Presupuesto 2027 deja sin…» si el origen es solo el proyecto de ley compartido);
- `list_topics` no muestra ya un título con la misma fórmula;
- `hook` y el resto de campos **no** están en texto plano: hay negrita, `{-…}` y `{+…}` donde hay cifra, estado o límite;
- el estado del trámite y el límite principal estén destacados en negrita;
- las cifras de pérdida y aumento usen las marcas de color correctas;
- el color no sea el único modo de comunicar el significado;
- `detail` empiece por el estado del trámite y esté separado en bloques `##` breves;
- las referencias `[n]` coincidan con fuentes estructuradas y no aparezcan enlaces Markdown literales;
- si otra nota del portal da contexto o es consecuencia, el cuerpo lleve `[[slug|texto visible]]` (no la URL del sitio); `list_topics` para no inventar el slug;
- la ficha haya sido previsualizada con el mismo renderizador del portal.

Si falta el formato visual o el titular es redundante, **no publiques**: reescribe (ver [periodista-investigador.md](../agents/periodista-investigador.md)).

## 6. Tools disponibles

| Tool | Uso |
| --- | --- |
| `list_categories` | Categorías válidas |
| `list_topics` / `get_topic` | Leer lo publicado |
| `create_topic` | Alta de ficha |
| `set_topic_cover` | Portada WebP del tema (`image_base64` o `image_url`; tema ya publicado) |
| `add_source` | Documento o noticia en un tema ya publicado (no duplica URL) |
| `add_reaction` | Enlace a post en redes (solo URL) |
| `add_update` | Hito de seguimiento: rumbo del trámite o de la conversación |
| `add_claim_check` | Idea que circula y respuesta con fuente (se sostiene, no se sostiene, a medias o falta el dato) |
| `add_link` | Asocia dos notas publicadas y devuelve `[[slug|texto]]` para pegar en hook o detail. Cómo usarlo: [enlaces-internos.md](../03-metodologia/enlaces-internos.md) |
| `list_profiles` | Perfiles activos (slug, nombre, cargo) más `kind_options` y guía de creación |
| `add_profile_mention` | Vincula un perfil a un tema (o fuente, hito, reacción, claim). Si el slug no existe, **crea el perfil activo** y la mención **confirmada** (pasa `display_name` y `kind`; el nombre puede inferirse del slug). El cuaderno es para desactivar o revisar sugerencias del scan, no para dar de alta manual. |
| `inspect_social_post` | Metadata + transcripción temporal de un enlace público |

## 7. Dependencias locales para `inspect_social_post`

En la máquina de desarrollo (y en el host API):

```bash
brew install yt-dlp ffmpeg whisper.cpp
cd apps/api
php artisan social:whisper-model
php artisan social:inspect-doctor
```

El doctor debe marcar `yt_dlp`, `ffmpeg`, `whisper` y `whisper_model` como OK. La transcripción corre **Whisper local** (gratis, modelo GGML `base`). `OPENAI_API_KEY` es opcional, solo como respaldo. Cookies de Instagram opcionales: `YTDLP_COOKIES_FILE` (fuera de git).

Instrucciones del servidor MCP: ver `EditorialServer` en el código (`apps/api/app/Mcp/Servers/EditorialServer.php`).
