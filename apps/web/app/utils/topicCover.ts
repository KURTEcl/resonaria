import { absoluteUrl } from '~/composables/usePageSeo'

/** Ruta para `<img>`: mismo host que el navegador (local o producción). */
export function topicCoverSrc(path: string | null | undefined): string | undefined {
  if (!path?.trim()) return undefined
  const value = path.trim()
  if (/^https?:\/\//i.test(value)) return value
  return value.startsWith('/') ? value : `/${value}`
}

/** URL absoluta para Open Graph y metadatos. */
export function topicCoverAbsoluteUrl(path: string | null | undefined): string | undefined {
  const src = topicCoverSrc(path)
  if (!src) return undefined
  if (/^https?:\/\//i.test(src)) return src
  return absoluteUrl(src)
}
