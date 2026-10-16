type EmbedOptions = {
  width?: number
}

const TWITTER_EMBED_MAX_HEIGHT = 1200

/** Pixel height from X/Twitter embed iframe postMessage (2026 shapes). */
export function extractTwitterEmbedHeight(data: unknown): number | undefined {
  let payload: unknown = data
  if (typeof payload === 'string') {
    try {
      payload = JSON.parse(payload)
    } catch {
      return undefined
    }
  }
  if (!payload || typeof payload !== 'object') return undefined

  const record = payload as Record<string, unknown>
  const tw = record['twttr.embed']
  const calls = Array.isArray(tw) ? tw : tw && typeof tw === 'object' ? [tw] : []

  for (const call of calls) {
    if (!call || typeof call !== 'object') continue
    const method = (call as { method?: string }).method
    if (method !== 'twttr.private.resize') continue
    const params = (call as { params?: unknown[] }).params
    if (!Array.isArray(params) || params.length === 0) continue

    const first = params[0]
    if (typeof first === 'number' && first > 0) return first
    if (first && typeof first === 'object') {
      const h = (first as { height?: number }).height
      if (typeof h === 'number' && h > 0) return h
    }
  }

  const legacy = record['twttr.embed'] as { method?: string, params?: number[] } | undefined
  if (
    legacy &&
    !Array.isArray(legacy) &&
    legacy.method === 'twttr.private.resize' &&
    typeof legacy.params?.[0] === 'number' &&
    legacy.params[0] > 0
  ) {
    return legacy.params[0]
  }

  const twttr = (record as { twttr?: { resize?: number } }).twttr
  if (typeof twttr?.resize === 'number' && twttr.resize > 0) return twttr.resize

  return undefined
}

export function clampTwitterEmbedHeight(px: number, viewportMax?: number): number {
  const max = Math.min(
    TWITTER_EMBED_MAX_HEIGHT,
    viewportMax ?? (typeof window !== 'undefined' ? Math.round(window.innerHeight * 0.88) : TWITTER_EMBED_MAX_HEIGHT),
  )
  return Math.min(Math.max(Math.ceil(px), 280), max)
}

export function embedSrc(platform: string, url: string, options?: EmbedOptions): string | null {
  const normalized = url.trim()
  const width = Math.min(Math.max(options?.width ?? 360, 280), 550)

  if (platform === 'tiktok') {
    const id = normalized.match(/\/video\/(\d+)/)?.[1]
    return id ? `https://www.tiktok.com/player/v1/${id}?description=1&music_info=1&rel=0` : null
  }

  if (platform === 'instagram') {
    const match = normalized.match(/instagram\.com\/(reel|p|tv)\/([^/?#]+)/i)
    return match ? `https://www.instagram.com/${match[1]}/${match[2]}/embed/captioned` : null
  }

  if (platform === 'x' || platform === 'twitter') {
    const id = normalized.match(/status\/(\d+)/)?.[1]
    return id
      ? `https://platform.twitter.com/embed/Tweet.html?id=${id}&dnt=true&theme=light&hide_thread=true&width=${width}`
      : null
  }

  if (platform === 'reddit') {
    const match = normalized.match(/reddit\.com\/r\/([\w-]+)\/comments\/([\w]+)/i)
    return match ? `https://embed.reddit.com/r/${match[1]}/comments/${match[2]}/?embed=true` : null
  }

  return null
}

export function threadsPermalink(url: string): string | null {
  let parsed: URL
  try {
    parsed = new URL(url.trim())
  } catch {
    return null
  }

  if (parsed.protocol !== 'https:') return null
  const host = parsed.hostname.replace(/^www\./, '')
  if (host !== 'threads.com' && host !== 'threads.net') return null

  const path = parsed.pathname
  if (!/^\/@[^/]+\/post\/[^/]+/.test(path) && !/^\/t\/[^/]+/.test(path)) return null

  return parsed.toString()
}
