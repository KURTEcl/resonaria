import { createError } from 'h3'

const FETCH_HEADERS = {
  'User-Agent': 'Mozilla/5.0 (compatible; Resonaria/1.0; +https://resonaria.cl)',
  Accept: 'text/html,application/json',
}

function decodeEscapedUrl(raw: string): string {
  return raw
    .replace(/\\u002F/gi, '/')
    .replace(/\\\//g, '/')
    .replace(/&amp;/g, '&')
}

function extractOgImage(html: string): string | null {
  const match = html.match(/property="og:image"\s+content="([^"]+)"/i)
  return match?.[1] ? decodeEscapedUrl(match[1]) : null
}

function extractTwitterMedia(html: string): string | null {
  const match = html.match(/https:\/\/pbs\.twimg\.com\/media\/[^"'\s]+/i)
  return match?.[0] ? decodeEscapedUrl(match[0]) : null
}

function extractTikTokCover(html: string): string | null {
  const match = html.match(/"cover":"(https:[^"]+)"/i)
  return match?.[1] ? decodeEscapedUrl(match[1]) : null
}

function assertAllowedUrl(url: string, platform: string): URL {
  let parsed: URL
  try {
    parsed = new URL(url)
  } catch {
    throw createError({ statusCode: 400, message: 'URL inválida' })
  }

  if (parsed.protocol !== 'https:') {
    throw createError({ statusCode: 400, message: 'Solo HTTPS' })
  }

  const host = parsed.hostname.replace(/^www\./, '')
  const ok =
    (platform === 'tiktok' && host === 'tiktok.com') ||
    (platform === 'instagram' && host === 'instagram.com') ||
    ((platform === 'x' || platform === 'twitter') && (host === 'x.com' || host === 'twitter.com')) ||
    (platform === 'reddit' && (host === 'reddit.com' || host === 'old.reddit.com')) ||
    (platform === 'threads' && (host === 'threads.com' || host === 'threads.net'))

  if (!ok) {
    throw createError({ statusCode: 400, message: 'Dominio no permitido para esta red' })
  }

  return parsed
}

async function fetchHtml(url: string): Promise<string> {
  const response = await fetch(url, {
    headers: FETCH_HEADERS,
    redirect: 'follow',
    signal: AbortSignal.timeout(12_000),
  })
  if (!response.ok) {
    throw createError({ statusCode: 502, message: 'No se pudo leer la publicación' })
  }
  return response.text()
}

async function tiktokThumbnail(url: string): Promise<string | null> {
  try {
    const oembed = await $fetch<{ thumbnail_url?: string }>(
      `https://www.tiktok.com/oembed?url=${encodeURIComponent(url)}`,
      { timeout: 10_000 },
    )
    if (oembed.thumbnail_url) return oembed.thumbnail_url
  } catch {
    /* oEmbed a veces responde 503; seguimos con HTML */
  }

  const html = await fetchHtml(url)
  return extractTikTokCover(html)
}

async function instagramThumbnail(url: string): Promise<string | null> {
  try {
    const { resolveInstagramMedia } = await import('./instagramMedia')
    const media = await resolveInstagramMedia(url)
    return media.thumbnail_url || media.media_url
  } catch {
    const html = await fetchHtml(url)
    return extractOgImage(html)
  }
}

async function redditThumbnail(url: string): Promise<string | null> {
  try {
    const oembed = await $fetch<{ thumbnail_url?: string }>(
      `https://www.reddit.com/oembed?url=${encodeURIComponent(url)}`,
      { timeout: 10_000 },
    )
    return oembed.thumbnail_url ?? null
  } catch {
    return null
  }
}

async function xThumbnail(url: string): Promise<string | null> {
  const html = await fetchHtml(url)
  const media = extractTwitterMedia(html)
  if (media) return media

  const og = extractOgImage(html)
  if (og && !og.includes('profile_images')) return og

  return og
}

export async function resolveSocialPreview(platform: string, url: string): Promise<{ thumbnail_url: string | null }> {
  const normalizedPlatform = platform === 'twitter' ? 'x' : platform
  assertAllowedUrl(url, normalizedPlatform)

  let thumbnail_url: string | null = null

  if (normalizedPlatform === 'tiktok') {
    thumbnail_url = await tiktokThumbnail(url)
  } else if (normalizedPlatform === 'instagram') {
    thumbnail_url = await instagramThumbnail(url)
  } else if (normalizedPlatform === 'x') {
    thumbnail_url = await xThumbnail(url)
  } else if (normalizedPlatform === 'reddit') {
    thumbnail_url = await redditThumbnail(url)
  }

  return { thumbnail_url }
}
