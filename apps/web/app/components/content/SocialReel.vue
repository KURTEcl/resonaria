<script setup lang="ts">
import type { PublicReaction } from '~/composables/usePublicApi'
import { embedSrc, threadsPermalink } from '~/utils/socialEmbed'
import { REACTION_AUTHOR_KIND_OPTIONS, reactionAuthorKindLabel } from '~/utils/reactionLabels'

const props = defineProps<{ reactions: PublicReaction[] }>()

const { thumbnailFor, isLoading } = useSocialPreviews(() => props.reactions)
const { mediaFor: instagramMediaFor, isLoading: instagramMediaLoading } = useInstagramMedia(() => props.reactions)

type ViewMode = 'gallery' | 'reel'

const view = ref<ViewMode>('reel')
const index = ref(0)
const shell = ref<HTMLElement | null>(null)
const embedWidth = ref(360)
const wheelLocked = ref(false)
const desktopColumn = ref(false)
const DESKTOP_MQ = '(min-width: 801px)'
let touchStartY = 0
let resizeTimer: ReturnType<typeof setTimeout> | undefined

const platformLabel: Record<string, string> = {
  instagram: 'Instagram',
  tiktok: 'TikTok',
  x: 'X',
  twitter: 'X',
  threads: 'Threads',
  reddit: 'Reddit',
}

const platformFilter = ref<string | null>(null)
const authorKindFilter = ref<string | null>(null)

const allSlides = computed(() =>
  props.reactions.map((reaction) => {
    const platform = reaction.platform === 'twitter' ? 'x' : reaction.platform
    const authorKind = reaction.author_kind || 'sin_clasificar'
    return {
      ...reaction,
      author_kind: authorKind,
      authorKindLabel: reactionAuthorKindLabel(authorKind),
      label: platformLabel[platform] ?? platform,
      platform,
    }
  }),
)

const platformsPresent = computed(() => {
  const order = ['instagram', 'tiktok', 'x', 'threads', 'reddit']
  const set = new Set(allSlides.value.map((s) => s.platform))
  return order.filter((p) => set.has(p))
})

const authorKindsPresent = computed(() => {
  const set = new Set(allSlides.value.map((s) => s.author_kind))
  return REACTION_AUTHOR_KIND_OPTIONS.filter((o) => set.has(o.value))
})

const slides = computed(() => {
  let list = allSlides.value
  if (platformFilter.value) {
    list = list.filter((s) => s.platform === platformFilter.value)
  }
  if (authorKindFilter.value) {
    list = list.filter((s) => s.author_kind === authorKindFilter.value)
  }
  return list
})

function onPlatformChange(event: Event) {
  const value = (event.target as HTMLSelectElement).value
  platformFilter.value = value || null
}

function onAuthorKindChange(event: Event) {
  const value = (event.target as HTMLSelectElement).value
  authorKindFilter.value = value || null
}

function tileThumb(slide: { platform: string; url: string }): string | null {
  if (slide.platform === 'instagram') {
    const media = instagramMediaFor(slide.url)
    return media?.thumbnail_url || media?.media_url || null
  }
  return thumbnailFor(slide.url)
}

function tileLoading(slide: { platform: string; url: string }): boolean {
  if (slide.platform === 'instagram') return instagramMediaLoading(slide.url)
  return isLoading(slide.url)
}

const current = computed(() => slides.value[index.value])

const isFluidEmbed = computed(() => current.value?.platform === 'x')
const { height: twitterHeight, reset: resetTwitterHeight, setIframe: setTwitterIframe } = useTwitterEmbedHeight(
  () => isFluidEmbed.value,
)
const twitterIframe = ref<HTMLIFrameElement | null>(null)

function onTwitterIframeLoad() {
  setTwitterIframe(twitterIframe.value)
}

const currentIgMedia = computed(() => {
  const slide = current.value
  if (!slide || slide.platform !== 'instagram') return null
  return instagramMediaFor(slide.url)
})

const igIsVideo = computed(() => {
  const media = currentIgMedia.value
  if (!media?.media_url) return false
  if (media.kind === 'image') return false
  return media.kind === 'video' || /\.(mp4|m3u8)(\?|$)/i.test(media.media_url)
})

const currentEmbedSrc = computed(() => {
  const slide = current.value
  if (!slide || slide.platform === 'instagram' || slide.platform === 'threads') return null
  return embedSrc(slide.platform, slide.url, slide.platform === 'x' ? { width: embedWidth.value } : undefined)
})

const currentThreadsUrl = computed(() => {
  const slide = current.value
  if (!slide || slide.platform !== 'threads') return null
  return threadsPermalink(slide.url)
})

function mountThreadsEmbed() {
  document.querySelectorAll('script[data-threads-embed]').forEach((node) => node.remove())
  const script = document.createElement('script')
  script.src = `https://www.threads.com/embed.js?ts=${Date.now()}`
  script.async = true
  script.dataset.threadsEmbed = '1'
  document.body.appendChild(script)
}

watch([currentThreadsUrl, view], () => {
  if (view.value !== 'reel' || !currentThreadsUrl.value) return
  mountThreadsEmbed()
}, { flush: 'post', immediate: true })

function setView(mode: ViewMode) {
  view.value = mode
}

function pickRandomSlide() {
  const len = slides.value.length
  index.value = len <= 1 ? 0 : Math.floor(Math.random() * len)
}

function openReel(at: number) {
  index.value = at
  view.value = 'reel'
}

function go(direction: number) {
  const len = slides.value.length
  if (len === 0) return
  index.value = (index.value + direction + len) % len
}

function onKeydown(event: KeyboardEvent) {
  if (view.value !== 'reel') return
  if (event.key === 'ArrowDown' || event.key === 'ArrowRight') {
    event.preventDefault()
    go(1)
  }
  if (event.key === 'ArrowUp' || event.key === 'ArrowLeft') {
    event.preventDefault()
    go(-1)
  }
}

function onWheel(event: WheelEvent) {
  if (desktopColumn.value) return
  if (view.value !== 'reel' || slides.value.length < 2) return
  if (Math.abs(event.deltaY) < 32) return
  if (wheelLocked.value) return

  event.preventDefault()
  wheelLocked.value = true
  go(event.deltaY > 0 ? 1 : -1)
  window.setTimeout(() => {
    wheelLocked.value = false
  }, 420)
}

function onTouchStart(event: TouchEvent) {
  touchStartY = event.touches[0]?.clientY ?? 0
}

function onTouchEnd(event: TouchEvent) {
  if (view.value !== 'reel' || slides.value.length < 2) return
  const endY = event.changedTouches[0]?.clientY ?? touchStartY
  const delta = touchStartY - endY
  if (Math.abs(delta) < 48) return
  go(delta > 0 ? 1 : -1)
}

watch(
  () => props.reactions,
  () => {
    platformFilter.value = null
    authorKindFilter.value = null
    if (import.meta.client) pickRandomSlide()
  },
)

watch([platformFilter, authorKindFilter], () => {
  if (import.meta.client) pickRandomSlide()
})

onMounted(() => {
  pickRandomSlide()
})

watch(
  () => current.value?.url,
  () => {
    resetTwitterHeight()
    setTwitterIframe(null)
  },
)

let resizeObserver: ResizeObserver | undefined

function bindShell(el: HTMLElement | null) {
  resizeObserver?.disconnect()
  shell.value?.removeEventListener('wheel', onWheel)

  if (!el) return

  resizeObserver = new ResizeObserver((entries) => {
    const width = entries[0]?.contentRect.width
    if (!width) return
    clearTimeout(resizeTimer)
    resizeTimer = setTimeout(() => {
      const next = Math.min(550, Math.max(280, Math.round(width)))
      if (next !== embedWidth.value) embedWidth.value = next
    }, 120)
  })
  resizeObserver.observe(el)
  el.addEventListener('wheel', onWheel, { passive: false })
}

watch(shell, (el) => bindShell(el), { flush: 'post' })

let desktopMq: MediaQueryList | undefined

function syncDesktopColumn() {
  desktopColumn.value = typeof window !== 'undefined' && window.matchMedia(DESKTOP_MQ).matches
}

onMounted(() => {
  desktopMq = window.matchMedia(DESKTOP_MQ)
  syncDesktopColumn()
  desktopMq.addEventListener('change', syncDesktopColumn)
})

onBeforeUnmount(() => {
  bindShell(null)
  desktopMq?.removeEventListener('change', syncDesktopColumn)
})

const reelShellLabel = computed(() =>
  desktopColumn.value
    ? 'Publicaciones en redes. Usa las flechas de la barra superior para cambiar; al final vuelve al inicio.'
    : 'Publicaciones en redes. Desliza o usa la rueda para cambiar; al final vuelve al inicio.',
)

const showReelNav = computed(() => view.value === 'reel' && slides.value.length > 1)
</script>

<template>
  <div v-if="!allSlides.length" class="social-empty">Todavía no hay reacciones seleccionadas.</div>

  <div v-else class="social">
    <div class="social-toolbar">
      <div
        v-if="platformsPresent.length > 1 || authorKindsPresent.length > 1"
        class="social-toolbar__filters"
      >
        <label v-if="platformsPresent.length > 1" class="social-filter">
          <span class="sr-only">Red social</span>
          <select
            class="social-filter__select"
            :class="{ 'is-active': Boolean(platformFilter) }"
            :value="platformFilter ?? ''"
            @change="onPlatformChange"
          >
            <option value="">Todas las redes</option>
            <option v-for="platform in platformsPresent" :key="platform" :value="platform">
              {{ platformLabel[platform] ?? platform }}
            </option>
          </select>
        </label>
        <label v-if="authorKindsPresent.length > 1" class="social-filter">
          <span class="sr-only">Tipo de cuenta</span>
          <select
            class="social-filter__select"
            :class="{ 'is-active': Boolean(authorKindFilter) }"
            :value="authorKindFilter ?? ''"
            @change="onAuthorKindChange"
          >
            <option value="">Todas las voces</option>
            <option v-for="kind in authorKindsPresent" :key="kind.value" :value="kind.value">
              {{ kind.label }}
            </option>
          </select>
        </label>
      </div>
      <div class="social-toolbar__controls">
        <div class="social-view" role="tablist" aria-label="Modo de vista">
          <button
            type="button"
            role="tab"
            :aria-selected="view === 'gallery'"
            :class="{ 'is-active': view === 'gallery' }"
            @click="setView('gallery')"
          >
            Galería
          </button>
          <button
            type="button"
            role="tab"
            :aria-selected="view === 'reel'"
            :class="{ 'is-active': view === 'reel' }"
            @click="setView('reel')"
          >
            Reel
          </button>
        </div>
        <p v-if="!showReelNav" class="social-count">{{ slides.length }}<span class="sr-only"> publicaciones</span></p>
        <div v-else class="social-nav" role="group" aria-label="Cambiar publicación">
          <button type="button" class="social-nav__btn" aria-label="Publicación anterior" @click="go(-1)">
            ‹
          </button>
          <span class="social-nav__pos" aria-live="polite">{{ index + 1 }} / {{ slides.length }}</span>
          <button type="button" class="social-nav__btn" aria-label="Siguiente publicación" @click="go(1)">
            ›
          </button>
        </div>
      </div>
    </div>

    <p v-if="!slides.length" class="social-empty">No hay publicaciones con estos filtros.</p>

    <div v-else-if="view === 'gallery'" class="gallery" role="list">
      <button
        v-for="(slide, i) in slides"
        :key="slide.url"
        type="button"
        class="gallery-tile"
        :class="`gallery-tile--${slide.platform}`"
        role="listitem"
        :aria-label="`Abrir ${slide.label} de ${slide.author_handle || 'cuenta pública'}`"
        @click="openReel(i)"
      >
        <img
          v-if="tileThumb(slide)"
          class="gallery-tile__thumb"
          :src="tileThumb(slide)!"
          alt=""
          loading="lazy"
          decoding="async"
          referrerpolicy="no-referrer"
        >
        <span v-else-if="tileLoading(slide)" class="gallery-tile__loading" aria-hidden="true">…</span>
        <span class="gallery-tile__shade" aria-hidden="true" />
        <span class="gallery-tile__platform" aria-hidden="true">{{ slide.label }}</span>
        <span v-if="slide.authorKindLabel" class="gallery-tile__voice">{{ slide.authorKindLabel }}</span>
        <span v-if="slide.author_handle" class="gallery-tile__author">{{ slide.author_handle }}</span>
        <span v-if="slide.caption" class="gallery-tile__caption">{{ slide.caption }}</span>
        <span class="gallery-tile__play" aria-hidden="true">Ver</span>
      </button>
    </div>

    <div v-else-if="slides.length" class="social-reel">
      <div
        ref="shell"
        class="reel-shell"
        tabindex="0"
        :aria-label="reelShellLabel"
        @keydown="onKeydown"
        @touchstart.passive="onTouchStart"
        @touchend.passive="onTouchEnd"
      >
        <div
          class="reel-stage"
          :class="[
            current ? `reel-stage--${current.platform}` : '',
            { 'reel-stage--fluid': isFluidEmbed },
          ]"
        >
          <video
            v-if="current?.platform === 'instagram' && igIsVideo && currentIgMedia?.media_url"
            :key="current.url"
            class="reel-player"
            :src="currentIgMedia.media_url"
            :poster="currentIgMedia.thumbnail_url || undefined"
            controls
            playsinline
            preload="metadata"
            referrerpolicy="no-referrer"
          />
          <img
            v-else-if="current?.platform === 'instagram' && currentIgMedia?.media_url"
            :key="current.url"
            class="reel-player reel-player--image"
            :src="currentIgMedia.media_url"
            alt=""
            referrerpolicy="no-referrer"
          >
          <p
            v-else-if="current?.platform === 'instagram' && instagramMediaLoading(current.url)"
            class="reel-fallback"
          >
            Cargando publicación…
          </p>
          <div v-else-if="currentThreadsUrl" :key="currentThreadsUrl" class="reel-threads">
            <blockquote
              class="text-post-media"
              :data-text-post-permalink="currentThreadsUrl"
              data-text-post-version="0"
            >
              <a :href="currentThreadsUrl" target="_blank" rel="noopener noreferrer">Ver en Threads</a>
            </blockquote>
          </div>
          <iframe
            v-else-if="currentEmbedSrc"
            :ref="(el) => { if (isFluidEmbed) twitterIframe = el as HTMLIFrameElement | null }"
            :key="`${current?.url}-${embedWidth}`"
            class="reel-iframe"
            :class="{ 'reel-iframe--fluid': isFluidEmbed }"
            :src="currentEmbedSrc"
            :title="`${current?.label} de ${current?.author_handle || 'una cuenta pública'}`"
            :style="isFluidEmbed ? { height: `${twitterHeight}px` } : undefined"
            scrolling="no"
            loading="lazy"
            referrerpolicy="strict-origin-when-cross-origin"
            allow="fullscreen; encrypted-media; picture-in-picture"
            allowfullscreen
            @load="current?.platform === 'x' ? onTwitterIframeLoad() : undefined"
          />
          <p v-else class="reel-fallback">
            Esta publicación no tiene reproductor embebido.
            <a v-if="current" :href="current.url" target="_blank" rel="noopener noreferrer">Abrir en {{ current.label }}</a>
          </p>
        </div>
        <p class="reel-hint" aria-hidden="true">Desliza ↑↓ o usa la rueda en los bordes</p>
      </div>

      <div class="reel-meta">
        <p class="reel-head">
          <span class="reel-head__platform">{{ current?.label }}</span>
          <span v-if="current?.authorKindLabel" class="reel-head__voice">{{ current.authorKindLabel }}</span>
          <span v-if="current?.author_handle" class="reel-head__author">{{ current.author_handle }}</span>
          <a
            v-if="current"
            class="reel-head__link"
            :href="current.url"
            target="_blank"
            rel="noopener noreferrer"
          >
            Abrir en {{ current.label }}
          </a>
        </p>
        <p v-if="current?.caption" class="reel-caption">{{ current.caption }}</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.social-empty {
  margin: 0.4rem 0 0;
  color: var(--muted);
}

.social {
  min-width: 0;
}

.social-toolbar {
  display: flex;
  flex-shrink: 0;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 10px;
}

.social-toolbar__filters {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(0, 1fr));
  gap: 8px;
}

.social-toolbar__controls {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  min-width: 0;
}

.social-filter {
  position: relative;
  min-width: 0;
}

.social-filter::after {
  content: '';
  position: absolute;
  top: 50%;
  right: 12px;
  width: 6px;
  height: 6px;
  border-right: 1.5px solid var(--muted);
  border-bottom: 1.5px solid var(--muted);
  transform: translateY(-70%) rotate(45deg);
  pointer-events: none;
}

.social-filter__select {
  width: 100%;
  max-width: 100%;
  min-height: 34px;
  padding: 0 28px 0 12px;
  border: 1px solid var(--border);
  border-radius: 999px;
  background: var(--surface);
  color: var(--muted);
  font: inherit;
  font-size: 12px;
  font-weight: 650;
  cursor: pointer;
  appearance: none;
  text-overflow: ellipsis;
}

.social-filter__select.is-active {
  border-color: var(--ink);
  color: var(--ink);
  background: var(--canvas);
}

.social-filter__select:focus-visible {
  outline: 3px solid var(--primary);
  outline-offset: 2px;
}

.social-count {
  margin: 0;
  font-size: 13px;
  font-weight: 650;
  color: var(--muted);
  white-space: nowrap;
}

.social-nav {
  display: inline-flex;
  flex-shrink: 0;
  align-items: center;
  gap: 4px;
  padding: 3px;
  border: 1px solid var(--border);
  border-radius: 999px;
  background: var(--canvas);
}

.social-nav__btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  padding: 0;
  border: 0;
  border-radius: 999px;
  background: var(--surface);
  color: var(--ink);
  font-size: 20px;
  line-height: 1;
  cursor: pointer;
}

.social-nav__btn:hover {
  background: var(--surface);
  color: var(--primary-hover);
}

.social-nav__btn:focus-visible {
  outline: 3px solid var(--secondary);
  outline-offset: 2px;
}

.social-nav__pos {
  min-width: 3.25rem;
  padding: 0 4px;
  font-size: 12px;
  font-weight: 650;
  text-align: center;
  color: var(--muted);
  font-variant-numeric: tabular-nums;
}

.social-view {
  display: inline-flex;
  padding: 3px;
  border: 1px solid var(--border);
  border-radius: 999px;
  background: var(--canvas);
}

.social-view button {
  min-height: 36px;
  padding: 0 14px;
  border: 0;
  border-radius: 999px;
  background: transparent;
  color: var(--muted);
  font: inherit;
  font-size: 13px;
  font-weight: 650;
  cursor: pointer;
}

.social-view button.is-active {
  background: var(--surface);
  color: var(--ink);
  box-shadow: 0 1px 2px rgb(15 23 42 / 8%);
}

.gallery {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 4px;
  margin: 0;
  padding: 0;
  list-style: none;
}

.gallery-tile {
  position: relative;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  gap: 4px;
  min-height: 0;
  aspect-ratio: 1 / 1;
  padding: 10px;
  border: 0;
  border-radius: 6px;
  overflow: hidden;
  text-align: left;
  cursor: pointer;
  color: #fff;
  background: #1a1f28;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.gallery-tile__thumb {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  pointer-events: none;
}

.gallery-tile__shade {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgb(0 0 0 / 82%) 0%, rgb(0 0 0 / 28%) 42%, rgb(0 0 0 / 12%) 100%);
  pointer-events: none;
}

.gallery-tile__loading {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 22px;
  color: rgb(255 255 255 / 55%);
  pointer-events: none;
}

.gallery-tile__platform,
.gallery-tile__voice,
.gallery-tile__author,
.gallery-tile__caption,
.gallery-tile__play {
  position: relative;
  z-index: 1;
}

.gallery-tile:hover {
  transform: scale(1.02);
  box-shadow: 0 8px 24px rgb(15 23 42 / 18%);
  z-index: 1;
}

.gallery-tile:focus-visible {
  outline: 3px solid var(--secondary);
  outline-offset: 2px;
}

.gallery-tile--tiktok {
  background: linear-gradient(165deg, #0f172a 0%, #1e293b 45%, #0d9488 100%);
}

.gallery-tile--instagram {
  background: linear-gradient(165deg, #4c1d95 0%, #be185d 55%, #f97316 100%);
}

.gallery-tile--x {
  background: linear-gradient(165deg, #0f172a 0%, #334155 100%);
}

.gallery-tile--threads {
  background: linear-gradient(165deg, #111111 0%, #3f3f46 100%);
}

.gallery-tile--reddit {
  background: linear-gradient(165deg, #7c2d12 0%, #ea580c 100%);
}

.gallery-tile__platform {
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  opacity: 0.9;
}

.gallery-tile__voice {
  display: inline-block;
  margin-top: 2px;
  padding: 1px 6px;
  border-radius: 999px;
  background: rgb(255 255 255 / 22%);
  font-size: 10px;
  font-weight: 650;
  letter-spacing: 0.02em;
  text-transform: uppercase;
}

.gallery-tile__author {
  font-size: 13px;
  font-weight: 650;
  line-height: 1.2;
}

.gallery-tile__caption {
  display: -webkit-box;
  overflow: hidden;
  font-size: 11px;
  line-height: 1.35;
  opacity: 0.88;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
}

.gallery-tile__play {
  position: absolute;
  top: 8px;
  right: 8px;
  padding: 4px 8px;
  border-radius: 999px;
  background: rgb(0 0 0 / 45%);
  font-size: 11px;
  font-weight: 650;
}

.social-reel {
  display: flex;
  flex: 1 1 auto;
  flex-direction: column;
  min-height: 0;
  overflow: hidden;
}

.reel-meta {
  flex-shrink: 0;
}

.reel-shell {
  width: 100%;
  outline: none;
  touch-action: pan-y;
}

.reel-shell:focus-visible {
  outline: 3px solid var(--secondary);
  outline-offset: 3px;
  border-radius: 16px;
}

.reel-stage {
  display: flex;
  align-items: stretch;
  justify-content: stretch;
  width: 100%;
  height: min(78dvh, 740px);
  border: 1px solid var(--border);
  border-radius: 16px;
  background: var(--surface);
  overflow: hidden;
}

.reel-stage--fluid {
  height: auto;
  min-height: 0;
  overflow: visible;
}

.reel-stage--tiktok {
  height: auto;
  max-height: min(78dvh, 740px);
  aspect-ratio: 9 / 16;
  background: #0b0f14;
}

.reel-stage--instagram {
  background: #0b0f14;
}

.reel-stage--x,
.reel-stage--reddit,
.reel-stage--threads {
  background: #fff;
}

.reel-threads {
  width: 100%;
  height: 100%;
  overflow: auto;
  padding: 12px;
}

.reel-threads :deep(iframe) {
  max-width: 100%;
}

.reel-iframe,
.reel-player {
  display: block;
  width: 100%;
  height: 100%;
  min-height: 0;
  border: 0;
  background: transparent;
}

.reel-player {
  object-fit: contain;
  background: #0b0f14;
}

.reel-player--image {
  object-fit: contain;
}

.reel-iframe--fluid {
  flex: 1;
  width: 100%;
  min-height: 280px;
  height: auto;
}

.reel-stage--tiktok .reel-iframe {
  flex: 1 1 auto;
  width: 100%;
  max-width: 100%;
  height: 100%;
  min-height: 0;
  max-height: 100%;
  margin: 0 auto;
}

.reel-hint {
  margin: 8px 0 0;
  font-size: 12px;
  text-align: center;
  color: var(--faint);
}

.reel-fallback {
  margin: auto;
  padding: 1.5rem;
  max-width: 36ch;
  text-align: center;
  color: var(--muted);
  line-height: 1.5;
}

.reel-fallback a {
  color: var(--primary);
  font-weight: 600;
}

.reel-meta {
  flex-shrink: 0;
  margin-top: 0;
  padding-top: 8px;
  border-top: 1px solid var(--hairline);
}

.reel-head {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 6px 8px;
  margin: 0;
  font-size: 12px;
  line-height: 1.35;
}

.reel-head__platform {
  font-weight: 700;
  letter-spacing: 0.02em;
  text-transform: uppercase;
}

.reel-head__voice {
  padding: 1px 7px;
  border-radius: 999px;
  background: color-mix(in srgb, var(--secondary) 12%, transparent);
  color: var(--secondary);
  font-size: 10px;
  font-weight: 650;
  letter-spacing: 0.02em;
  text-transform: uppercase;
}

.reel-head__author {
  font-weight: 650;
  color: var(--ink);
}

.reel-head__link {
  margin-left: auto;
  font-size: 12px;
  font-weight: 650;
  color: var(--primary);
  text-decoration: none;
}

.reel-head__link:hover {
  text-decoration: underline;
}

.reel-caption {
  display: -webkit-box;
  margin: 6px 0 0;
  overflow: hidden;
  color: var(--muted);
  font-size: 13px;
  line-height: 1.4;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
}

@media (min-width: 801px) {
  .social:has(.social-reel) {
    display: flex;
    flex: 1;
    flex-direction: column;
    min-height: 0;
    overflow: hidden;
  }

  .social:has(.gallery) {
    display: flex;
    flex: 1;
    flex-direction: column;
    min-height: 0;
    overflow: hidden;
  }

  .social:has(.gallery) .gallery {
    flex: 1;
    min-height: 0;
    overflow-y: auto;
    overscroll-behavior: contain;
    align-content: start;
  }

  .social-toolbar {
    margin-bottom: 8px;
    padding-bottom: 8px;
    border-bottom: 1px solid var(--hairline);
  }

  .social-reel {
    flex: 1;
    min-height: 0;
  }

  .reel-shell {
    display: flex;
    flex: 1 1 auto;
    flex-direction: column;
    justify-content: center;
    min-height: 0;
    overflow-x: hidden;
    overflow-y: auto;
    overscroll-behavior: contain;
    touch-action: auto;
  }

  .reel-stage:not(.reel-stage--fluid) {
    flex: 0 1 auto;
    width: 100%;
    height: auto;
    min-height: 0;
    max-height: 100%;
  }

  .reel-stage--tiktok {
    flex: 0 0 auto;
    width: 100%;
    max-height: min(calc(100dvh - 220px), 740px);
  }

  .reel-stage--fluid {
    flex: 0 0 auto;
    width: 100%;
    min-height: 0;
    max-height: none;
    overflow: visible;
  }

  .reel-hint {
    display: none;
  }

  .reel-meta {
    flex-shrink: 0;
    margin-top: auto;
    padding-top: 10px;
  }
}

@media (max-width: 800px) {
  .reel-stage:not(.reel-stage--fluid):not(.reel-stage--tiktok) {
    height: min(72dvh, 640px);
  }

  .reel-stage--tiktok {
    max-height: min(72dvh, 640px);
  }

  .gallery {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 3px;
  }
}

@media (max-width: 420px) {
  .gallery {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}
</style>
