import type { PublicReaction } from '~/composables/usePublicApi'
import type { InstagramMedia } from '~/utils/instagramUrl'

type MediaState = InstagramMedia | null | 'pending'

export function useInstagramMedia(reactions: () => PublicReaction[]) {
  const { apiUrl } = usePublicApi()
  const media = reactive<Record<string, MediaState>>({})

  async function load(reaction: PublicReaction) {
    if (reaction.platform !== 'instagram') return
    if (media[reaction.url] !== undefined) return

    media[reaction.url] = 'pending'
    try {
      media[reaction.url] = await $fetch<InstagramMedia>(apiUrl('/v1/instagram-media', { url: reaction.url }))
    } catch {
      media[reaction.url] = null
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

  function mediaFor(url: string): InstagramMedia | null {
    const value = media[url]
    return value && value !== 'pending' ? value : null
  }

  function isLoading(url: string): boolean {
    return media[url] === 'pending'
  }

  return { mediaFor, isLoading }
}
