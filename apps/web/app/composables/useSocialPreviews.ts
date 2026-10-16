import type { PublicReaction } from '~/composables/usePublicApi'

type PreviewState = string | null | 'pending'

export function useSocialPreviews(reactions: () => PublicReaction[]) {
  const previews = reactive<Record<string, PreviewState>>({})

  async function load(reaction: PublicReaction) {
    if (reaction.preview_image_url?.trim()) {
      previews[reaction.url] = reaction.preview_image_url.trim()
      return
    }
    if (previews[reaction.url] !== undefined) return
    if (reaction.platform === 'instagram') return

    previews[reaction.url] = 'pending'
    try {
      const result = await $fetch<{ thumbnail_url: string | null }>('/api/social-preview', {
        query: { platform: reaction.platform, url: reaction.url },
      })
      previews[reaction.url] = result.thumbnail_url
    } catch {
      previews[reaction.url] = null
    }
  }

  watch(
    reactions,
    (list) => {
      for (const reaction of list) {
        load(reaction)
      }
    },
    { immediate: true },
  )

  function thumbnailFor(url: string): string | null {
    const value = previews[url]
    return value && value !== 'pending' ? value : null
  }

  function isLoading(url: string): boolean {
    return previews[url] === 'pending'
  }

  return { thumbnailFor, isLoading }
}
