import { clampTwitterEmbedHeight, extractTwitterEmbedHeight } from '~/utils/socialEmbed'

const TWITTER_ORIGINS = new Set([
  'https://platform.twitter.com',
  'https://platform.x.com',
])

const DEFAULT_HEIGHT = 360

export function useTwitterEmbedHeight(enabled: () => boolean) {
  const height = ref(DEFAULT_HEIGHT)
  const activeIframe = shallowRef<HTMLIFrameElement | null>(null)

  function reset() {
    height.value = DEFAULT_HEIGHT
  }

  function setIframe(el: HTMLIFrameElement | null) {
    activeIframe.value = el
  }

  function applyHeight(px: number) {
    height.value = clampTwitterEmbedHeight(px)
  }

  function onMessage(event: MessageEvent) {
    if (!enabled()) return
    if (!TWITTER_ORIGINS.has(event.origin)) return
    if (activeIframe.value && event.source !== activeIframe.value.contentWindow) return

    const next = extractTwitterEmbedHeight(event.data)
    if (typeof next === 'number' && next > 0) {
      applyHeight(next)
    }
  }

  onMounted(() => window.addEventListener('message', onMessage))
  onBeforeUnmount(() => window.removeEventListener('message', onMessage))

  return { height, reset, setIframe }
}
