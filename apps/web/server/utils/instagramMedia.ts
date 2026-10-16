import { execFile } from 'node:child_process'
import { promisify } from 'node:util'
import { createError } from 'h3'
import {
  instagramCanonicalUrl,
  instagramEmbedPageUrl,
  isInstagramPermalinkHost,
  parseInstagramPermalink,
  type InstagramMedia,
  type ParsedInstagramUrl,
} from '../../app/utils/instagramUrl'

const execFileAsync = promisify(execFile)
const YTDLP_BINS = ['yt-dlp', '/opt/homebrew/bin/yt-dlp', '/usr/local/bin/yt-dlp']
const EMPTY_CACHE_TTL_MS = 45_000

export type { InstagramMedia }

const CACHE_TTL_MS = 8 * 60 * 1000
const cache = new Map<string, { at: number; data: InstagramMedia }>()

const FETCH_HEADERS = {
  'User-Agent':
    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
  Accept: 'text/html,application/json',
  'Accept-Language': 'es-CL,es;q=0.9,en;q=0.8',
}

function decodeEscaped(raw: string): string {
  return raw
    .replace(/\\u002F/gi, '/')
    .replace(/\\u0026/gi, '&')
    .replace(/\\\//g, '/')
    .replace(/&amp;/g, '&')
}

function metaContent(html: string, property: string): string | null {
  const match = html.match(
    new RegExp(`(?:property|name)="${property}"\\s+content="([^"]+)"`, 'i'),
  ) ?? html.match(
    new RegExp(`content="([^"]+)"\\s+(?:property|name)="${property}"`, 'i'),
  )
  return match?.[1] ? decodeEscaped(match[1]) : null
}

function jsonHttps(html: string, key: string): string | null {
  const match = html.match(new RegExp(`"${key}"\\s*:\\s*"(https:[^"]+)"`, 'i'))
  return match?.[1] ? decodeEscaped(match[1]) : null
}

function classText(html: string, className: string): string | null {
  const match = html.match(new RegExp(`class="[^"]*${className}[^"]*"[^>]*>([^<]+)`, 'i'))
  const text = match?.[1]?.trim()
  return text || null
}

function attrSrc(html: string, className: string): string | null {
  const match = html.match(
    new RegExp(`class="[^"]*${className}[^"]*"[^>]*src="([^"]+)"`, 'i'),
  ) ?? html.match(
    new RegExp(`src="([^"]+)"[^>]*class="[^"]*${className}[^"]*"`, 'i'),
  )
  return match?.[1] ? decodeEscaped(match[1]) : null
}

async function fetchText(url: string): Promise<{ url: string; body: string }> {
  const response = await fetch(url, {
    headers: FETCH_HEADERS,
    redirect: 'follow',
    signal: AbortSignal.timeout(12_000),
  })
  if (!response.ok) {
    throw createError({ statusCode: 502, message: 'No se pudo leer la publicación de Instagram' })
  }
  return { url: response.url, body: await response.text() }
}

async function resolveShare(url: string, parsed: ParsedInstagramUrl): Promise<ParsedInstagramUrl> {
  try {
    const { url: finalUrl, body } = await fetchText(url)
    const fromFinal = parseInstagramPermalink(finalUrl)
    if (fromFinal && !fromFinal.isShare) return fromFinal

    const canonical =
      metaContent(body, 'og:url') ||
      body.match(/rel="canonical"\s+href="([^"]+)"/i)?.[1] ||
      body.match(/href="([^"]+)"\s+rel="canonical"/i)?.[1]
    if (canonical) {
      const fromCanon = parseInstagramPermalink(canonical)
      if (fromCanon && !fromCanon.isShare) return fromCanon
    }
  } catch {
    /* seguimos con el id de share */
  }
  return parsed
}

function extractFromHtml(html: string, permalink: string): InstagramMedia {
  const video =
    metaContent(html, 'og:video:secure_url') ||
    metaContent(html, 'og:video') ||
    jsonHttps(html, 'video_url') ||
    attrSrc(html, 'EmbeddedMediaVideo')
  const image =
    jsonHttps(html, 'display_url') ||
    metaContent(html, 'og:image') ||
    attrSrc(html, 'EmbeddedMediaImage')
  const sidecar = /edge_sidecar_to_children/i.test(html)
  const kind: InstagramMedia['kind'] = sidecar
    ? 'carousel'
    : video
      ? 'video'
      : 'image'
  const ogTitle = metaContent(html, 'og:title')
  const authorFromTitle = ogTitle?.match(/^([^:]+?)\s+on Instagram/i)?.[1]?.replace(/^@/, '') ?? null
  const caption =
    classText(html, 'Caption') ||
    metaContent(html, 'og:description')?.replace(/^[^:]+:\s*"?/, '').replace(/"$/, '') ||
    null

  return {
    kind,
    media_url: video || image,
    thumbnail_url: image,
    author: classText(html, 'UsernameText') || authorFromTitle,
    caption,
    permalink,
  }
}

async function scrapeGraphql(postId: string): Promise<Partial<InstagramMedia> | null> {
  const body = new URLSearchParams({
    av: '0',
    __d: 'www',
    __a: '1',
    __comet_req: '7',
    lsd: 'AVoPBTXMX0Y',
    fb_api_caller_class: 'RelayModern',
    fb_api_req_friendly_name: 'PolarisPostActionLoadPostQueryQuery',
    variables: JSON.stringify({
      shortcode: postId,
      fetch_comment_count: 0,
      parent_comment_count: 0,
      child_comment_count: 0,
      fetch_like_count: 0,
      fetch_tagged_user_count: null,
      fetch_preview_comment_count: 0,
      has_threaded_comments: false,
    }),
    server_timestamps: 'true',
    doc_id: '25531498899829322',
  })

  const response = await fetch('https://www.instagram.com/graphql/query/', {
    method: 'POST',
    headers: {
      ...FETCH_HEADERS,
      'Content-Type': 'application/x-www-form-urlencoded',
      Origin: 'https://www.instagram.com',
      'X-Fb-Friendly-Name': 'PolarisPostActionLoadPostQueryQuery',
      'X-Ig-App-Id': '936619743392459',
    },
    body,
    signal: AbortSignal.timeout(12_000),
  })
  if (!response.ok) return null

  const json = await response.json().catch(() => null) as Record<string, unknown> | null
  if (!json) return null
  const data = json.data as Record<string, unknown> | undefined
  const item = (data?.xdt_shortcode_media || data?.shortcode_media) as Record<string, unknown> | undefined
  if (!item || typeof item !== 'object') return null

  const sidecar = item.edge_sidecar_to_children as { edges?: Array<{ node?: Record<string, unknown> }> } | undefined
  const first = sidecar?.edges?.[0]?.node || item
  const video = typeof first.video_url === 'string' ? decodeEscaped(first.video_url) : null
  const image = typeof first.display_url === 'string' ? decodeEscaped(first.display_url) : null
  const owner = item.owner as { username?: string } | undefined
  const captionEdges = item.edge_media_to_caption as { edges?: Array<{ node?: { text?: string } }> } | undefined

  return {
    kind: sidecar?.edges && sidecar.edges.length > 1 ? 'carousel' : video ? 'video' : 'image',
    media_url: video || image || null,
    thumbnail_url: image || null,
    author: owner?.username ?? null,
    caption: captionEdges?.edges?.[0]?.node?.text ?? null,
  }
}

function assertInstagramUrl(url: string): URL {
  let parsed: URL
  try {
    parsed = new URL(url)
  } catch {
    throw createError({ statusCode: 400, message: 'URL inválida' })
  }
  if (parsed.protocol !== 'https:') {
    throw createError({ statusCode: 400, message: 'Solo HTTPS' })
  }
  if (!isInstagramPermalinkHost(parsed)) {
    throw createError({ statusCode: 400, message: 'Dominio no permitido para esta red' })
  }
  return parsed
}

export async function resolveInstagramMedia(url: string): Promise<InstagramMedia> {
  assertInstagramUrl(url)
  const key = url.trim()
  const cached = cache.get(key)
  const ttl = cached?.data.media_url ? CACHE_TTL_MS : EMPTY_CACHE_TTL_MS
  if (cached && Date.now() - cached.at < ttl) {
    return cached.data
  }

  let parsed = parseInstagramPermalink(url)
  if (!parsed) {
    throw createError({ statusCode: 400, message: 'No se reconoce el enlace de Instagram' })
  }
  if (parsed.isShare) {
    parsed = await resolveShare(url, parsed)
  }

  const permalink = parsed.isShare ? key : instagramCanonicalUrl(parsed)
  let media: InstagramMedia

  try {
    const { body } = await fetchText(instagramEmbedPageUrl(parsed))
    media = extractFromHtml(body, permalink)
  } catch {
    media = {
      kind: 'image',
      media_url: null,
      thumbnail_url: null,
      author: null,
      caption: null,
      permalink,
    }
  }

  if (!media.media_url && !parsed.isShare) {
    const gql = await scrapeGraphql(parsed.id).catch(() => null)
    if (gql?.media_url) {
      media = {
        kind: gql.kind || media.kind,
        media_url: gql.media_url,
        thumbnail_url: gql.thumbnail_url || media.thumbnail_url,
        author: gql.author || media.author,
        caption: gql.caption || media.caption,
        permalink,
      }
    }
  }

  if (!media.media_url) {
    const fromYtdlp = await scrapeYtdlp(permalink).catch(() => null)
    if (fromYtdlp?.media_url) {
      media = {
        kind: fromYtdlp.kind || media.kind,
        media_url: fromYtdlp.media_url,
        thumbnail_url: fromYtdlp.thumbnail_url || media.thumbnail_url,
        author: fromYtdlp.author || media.author,
        caption: fromYtdlp.caption || media.caption,
        permalink,
      }
    }
  }

  cache.set(key, { at: Date.now(), data: media })
  return media
}

type YtdlpFormat = {
  url?: string
  ext?: string
  vcodec?: string
  acodec?: string
  format_note?: string
}

function pickYtdlpUrl(data: { url?: string; formats?: YtdlpFormat[] }): string | null {
  if (typeof data.url === 'string' && data.url.startsWith('http')) return data.url
  const formats = data.formats ?? []
  const progressive = formats.find(
    (format) =>
      format.url &&
      format.vcodec &&
      format.vcodec !== 'none' &&
      format.acodec &&
      format.acodec !== 'none',
  )
  if (progressive?.url) return progressive.url
  const unlabeled = formats.find(
    (format) => format.url && format.ext === 'mp4' && !format.format_note,
  )
  if (unlabeled?.url) return unlabeled.url
  const video = [...formats].reverse().find(
    (format) => format.url && format.vcodec && format.vcodec !== 'none',
  )
  return video?.url ?? null
}

async function scrapeYtdlp(url: string): Promise<Partial<InstagramMedia> | null> {
  let lastError: unknown
  for (const bin of YTDLP_BINS) {
    try {
      const { stdout } = await execFileAsync(
        bin,
        ['-j', '--no-warnings', '--no-playlist', '--no-download', url],
        { timeout: 25_000, maxBuffer: 8 * 1024 * 1024 },
      )
      const data = JSON.parse(stdout) as {
        url?: string
        ext?: string
        thumbnail?: string
        uploader?: string
        description?: string
        formats?: YtdlpFormat[]
      }
      const mediaUrl = pickYtdlpUrl(data)
      if (!mediaUrl) return null
      const isVideo = data.ext === 'mp4' || /\.(mp4|m3u8)(\?|$)/i.test(mediaUrl)
      return {
        kind: isVideo ? 'video' : 'image',
        media_url: mediaUrl,
        thumbnail_url: typeof data.thumbnail === 'string' ? data.thumbnail : null,
        author: typeof data.uploader === 'string' ? data.uploader : null,
        caption: typeof data.description === 'string' ? data.description : null,
      }
    } catch (error) {
      lastError = error
    }
  }
  if (lastError) return null
  return null
}
