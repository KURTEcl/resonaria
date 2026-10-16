export type InstagramPostType = 'p' | 'reel' | 'tv'

export type InstagramMedia = {
  kind: 'video' | 'image' | 'carousel'
  media_url: string | null
  thumbnail_url: string | null
  author: string | null
  caption: string | null
  permalink: string
}

export type ParsedInstagramUrl = {
  type: InstagramPostType
  id: string
  isShare: boolean
}

const IG_HOSTS = new Set(['instagram.com', 'instagr.am'])

export function instagramHostname(url: URL): string {
  return url.hostname.replace(/^www\./, '').toLowerCase()
}

export function isInstagramPermalinkHost(url: URL): boolean {
  return IG_HOSTS.has(instagramHostname(url))
}

function stripSegment(id: string): string {
  return id.replace(/\/+$/, '')
}

function asPostType(value: string): InstagramPostType | null {
  if (value === 'reels') return 'reel'
  if (value === 'p' || value === 'reel' || value === 'tv') return value
  return null
}

export function parseInstagramPermalink(raw: string): ParsedInstagramUrl | null {
  let parsed: URL
  try {
    parsed = new URL(raw.trim())
  } catch {
    return null
  }

  if (parsed.protocol !== 'https:') return null
  if (!isInstagramPermalinkHost(parsed)) return null

  const parts = parsed.pathname.split('/').filter(Boolean)
  if (parts.length === 0) return null

  if (parts[0] === 'share') {
    const typed = parts[1] ? asPostType(parts[1]) : null
    if (typed && parts[2]) {
      return { type: typed, id: stripSegment(parts[2]), isShare: true }
    }
    if (parts[1]) {
      return { type: 'reel', id: stripSegment(parts[1]), isShare: true }
    }
    return null
  }

  const direct = asPostType(parts[0])
  if (direct && parts[1]) {
    return { type: direct, id: stripSegment(parts[1]), isShare: false }
  }

  const nested = parts[1] ? asPostType(parts[1]) : null
  if (nested && parts[2]) {
    return { type: nested, id: stripSegment(parts[2]), isShare: false }
  }

  return null
}

export function instagramCanonicalUrl(parsed: ParsedInstagramUrl): string {
  return `https://www.instagram.com/${parsed.type}/${parsed.id}/`
}

export function instagramEmbedPageUrl(parsed: ParsedInstagramUrl): string {
  return `https://www.instagram.com/${parsed.type}/${parsed.id}/embed/captioned/`
}
