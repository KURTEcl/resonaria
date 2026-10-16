type PageSeo = {
  title: string
  description: string
  path?: string
  ogType?: 'website' | 'article'
  robots?: string
  publishedTime?: string | null
  modifiedTime?: string | null
  image?: string | null
  jsonLd?: Record<string, unknown> | Record<string, unknown>[]
}

export function siteOrigin() {
  const config = useRuntimeConfig()
  return String(config.public.siteUrl || 'https://resonaria.cl').replace(/\/$/, '')
}

export function absoluteUrl(path = '/') {
  const origin = siteOrigin()
  const normalized = path === '/' ? '' : path.startsWith('/') ? path.replace(/\/$/, '') : `/${path.replace(/\/$/, '')}`
  return `${origin}${normalized || ''}`
}

export function usePageSeo(options: PageSeo) {
  const route = useRoute()
  const path = options.path ?? (route.path === '/' ? '/' : route.path.replace(/\/$/, ''))
  const url = absoluteUrl(path)
  const image = options.image || undefined
  const jsonLd = Array.isArray(options.jsonLd) ? options.jsonLd : options.jsonLd ? [options.jsonLd] : []

  useSeoMeta({
    title: options.title,
    description: options.description,
    ogTitle: options.title,
    ogDescription: options.description,
    ogType: options.ogType ?? 'website',
    ogUrl: url,
    ogLocale: 'es_CL',
    ogSiteName: 'Resonaria',
    ogImage: image,
    twitterCard: image ? 'summary_large_image' : 'summary',
    twitterTitle: options.title,
    twitterDescription: options.description,
    twitterImage: image,
    robots: options.robots,
    articlePublishedTime: options.publishedTime || undefined,
    articleModifiedTime: options.modifiedTime || undefined,
  })

  useHead({
    link: [{ rel: 'canonical', href: url }],
    script: jsonLd.map((block) => ({
      type: 'application/ld+json',
      innerHTML: JSON.stringify(block),
    })),
  })
}
