type GaValue = string | number | boolean | undefined

declare global {
  interface Window {
    dataLayer?: unknown[]
    gtag?: (...args: unknown[]) => void
  }
}

export function gaMeasurementId() {
  const id = useRuntimeConfig().public.gaMeasurementId
  return typeof id === 'string' ? id.trim() : ''
}

export function gaEvent(name: string, params: Record<string, GaValue> = {}) {
  if (!import.meta.client || typeof window.gtag !== 'function') return

  const payload: Record<string, string | number | boolean> = {}
  for (const [key, value] of Object.entries(params)) {
    if (value !== undefined && value !== '') payload[key] = value
  }
  if (import.meta.dev) payload.debug_mode = true

  window.gtag('event', name, payload)
}

export function gaContentGroup(path: string) {
  if (path === '/') return 'Portada'
  if (/^\/casos\/[^/]+/.test(path)) return 'Ficha'
  if (path === '/casos' || path.startsWith('/casos?')) return 'Listado'
  return 'Institucional'
}

export function gaCaseSlug(path: string) {
  return path.match(/^\/casos\/([^/?#]+)/)?.[1]
}

export function gaPageView(path: string, title?: string, contentGroup?: string) {
  const id = gaMeasurementId()
  if (!id) return

  const pageTitle = title
    ? (title.includes('Resonaria') ? title : `${title} | Resonaria`)
    : document.title

  gaEvent('page_view', {
    send_to: id,
    page_title: pageTitle,
    page_location: `${location.origin}${path}`,
    page_path: path.split('#')[0],
    content_group: contentGroup || gaContentGroup(path),
    case_slug: gaCaseSlug(path),
  })
}
