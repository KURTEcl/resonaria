export function useAnalytics() {
  const { apiUrl } = usePublicApi()

  async function trackViewCase(slug: string, category?: string | null) {
    if (!import.meta.client || !slug) return

    gaEvent('view_case', { case_slug: slug, content_group: category || 'Ficha' })

    try {
      await $fetch(apiUrl('/v1/analytics/events'), {
        method: 'POST',
        body: { event: 'view_case', slug },
      })
    } catch {
      // No bloquear lectura si falla el conteo agregado.
    }
  }

  async function trackSourceClick(slug: string) {
    if (!import.meta.client || !slug) return
    gaEvent('click_primary_source', { case_slug: slug, content_group: 'Ficha' })
    try {
      await $fetch(apiUrl('/v1/analytics/events'), {
        method: 'POST',
        body: { event: 'click_primary_source', slug },
      })
    } catch {
      // ignorar
    }
  }

  return { trackViewCase, trackSourceClick }
}
