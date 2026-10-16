<script setup lang="ts">
import { AREA_OPTIONS } from '~/composables/useCatalogFilters'

const route = useRoute()
const { filters, applyFilters, toggleInList } = useCatalogFilters()
const { name: portalName, githubUrl, supportUrl, socialInstagram, socialX } = usePortalSite()

const primary = computed(() => [
  { to: '/', label: portalName.value, icon: 'brand' as const },
  { to: '/', label: 'Inicio', icon: 'home' as const },
])

const secondary = [
  { to: '/fuentes', label: 'Fuentes', icon: 'docs' },
  { to: '/metodologia', label: 'Metodología', icon: 'method' },
  { to: '/ayuda', label: 'Ayuda', icon: 'help' },
] as const

const projectLinks = computed(() => {
  const links: { href: string, label: string, icon: 'github' | 'support' }[] = [
    { href: githubUrl.value, label: 'Código abierto en GitHub', icon: 'github' },
    { href: supportUrl.value, label: 'Apoyar el proyecto', icon: 'support' },
  ]
  return links
})

const social = computed(() => {
  const links: { href: string, label: string, icon: 'instagram' | 'x' }[] = []
  if (socialInstagram.value) {
    links.push({ href: socialInstagram.value, label: 'Instagram', icon: 'instagram' })
  }
  if (socialX.value) {
    links.push({ href: socialX.value, label: 'X', icon: 'x' })
  }
  return links
})

const railEl = ref<HTMLElement | null>(null)
const areasOpen = ref(false)

const selectedAreas = computed(() =>
  AREA_OPTIONS.filter((opt) => filters.value.area.includes(opt.value)),
)

const stackPreview = computed(() =>
  selectedAreas.value.length ? selectedAreas.value.slice(0, 3) : AREA_OPTIONS.slice(0, 3),
)

const areasTriggerLabel = computed(() => {
  if (!selectedAreas.value.length) return 'Áreas'
  if (selectedAreas.value.length === 1) return `Áreas, ${selectedAreas.value[0].label}`
  return `Áreas, ${selectedAreas.value.length} seleccionadas`
})

function isActive(to: string) {
  if (to === '/') return route.path === '/'
  return route.path === to || route.path.startsWith(`${to}/`)
}

const tip = ref<{ label: string, x: number, y: number, below: boolean } | null>(null)

function showTip(event: MouseEvent | FocusEvent) {
  if (areasOpen.value) return
  const el = event.currentTarget
  if (!(el instanceof HTMLElement)) return
  const box = el.getBoundingClientRect()
  const below = window.matchMedia('(max-width: 600px)').matches
  tip.value = {
    label: el.getAttribute('aria-label') || '',
    x: below ? box.left + box.width / 2 : box.right + 10,
    y: below ? box.bottom + 8 : box.top + box.height / 2,
    below,
  }
}

function hideTip() {
  tip.value = null
}

function positionAreasPanel() {
  const rail = railEl.value
  if (!rail) return
  const box = rail.getBoundingClientRect()
  rail.style.setProperty('--areas-top', `${Math.round(box.bottom + 6)}px`)
}

function toggleAreas() {
  areasOpen.value = !areasOpen.value
  hideTip()
  if (areasOpen.value) {
    nextTick(positionAreasPanel)
  }
}

function closeAreas() {
  areasOpen.value = false
}

function toggleArea(value: string) {
  applyFilters(
    { ...filters.value, area: toggleInList(filters.value.area, value) },
    '/',
  )
}

function onPointerDown(event: PointerEvent) {
  if (!areasOpen.value) return
  const rail = railEl.value
  if (rail && !rail.contains(event.target as Node)) closeAreas()
}

function onKeydown(event: KeyboardEvent) {
  if (event.key === 'Escape') closeAreas()
}

watch(() => route.path, closeAreas)

function onViewportChange(event: MediaQueryListEvent) {
  if (!event.matches) closeAreas()
}

onMounted(() => {
  document.addEventListener('pointerdown', onPointerDown)
  document.addEventListener('keydown', onKeydown)
  window.matchMedia('(max-width: 600px)').addEventListener('change', onViewportChange)
})

onBeforeUnmount(() => {
  document.removeEventListener('pointerdown', onPointerDown)
  document.removeEventListener('keydown', onKeydown)
  window.matchMedia('(max-width: 600px)').removeEventListener('change', onViewportChange)
})
</script>

<template>
  <nav ref="railEl" class="icon-rail" aria-label="Navegación principal">
    <ul class="icon-rail__list">
      <li v-for="item in primary" :key="`${item.to}-${item.icon}`">
        <NuxtLink
          :to="item.to"
          class="icon-rail__btn"
          :class="{ 'icon-rail__btn--active': isActive(item.to) }"
          :aria-label="item.label"
          :aria-current="isActive(item.to) ? 'page' : undefined"
          @mouseenter="showTip"
          @mouseleave="hideTip"
          @focus="showTip"
          @blur="hideTip"
        >
          <span class="icon-rail__glyph" aria-hidden="true">
            <img
              v-if="item.icon === 'brand'"
              class="icon-rail__brand"
              src="/brand/icono.svg?v=resonaria-conversation-1"
              width="34"
              height="34"
              alt=""
            />
            <svg v-else-if="item.icon === 'home'" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
              <path d="M4 10.5 12 4l8 6.5V20a1 1 0 0 1-1 1h-5v-6H10v6H5a1 1 0 0 1-1-1v-9.5Z" />
            </svg>
          </span>
        </NuxtLink>
      </li>

      <li class="icon-rail__sep" aria-hidden="true"></li>

      <li
        class="icon-rail__areas"
        :class="{ 'is-open': areasOpen, 'has-selection': selectedAreas.length > 0 }"
      >
        <button
          type="button"
          class="icon-rail__btn icon-rail__areas-trigger"
          :class="{ 'icon-rail__btn--active': selectedAreas.length > 0 || areasOpen }"
          :aria-label="areasTriggerLabel"
          :aria-expanded="areasOpen"
          aria-controls="rail-areas"
          aria-haspopup="true"
          @click="toggleAreas"
          @mouseenter="showTip"
          @mouseleave="hideTip"
          @focus="showTip"
          @blur="hideTip"
        >
          <span class="icon-rail__areas-stack" aria-hidden="true">
            <span v-for="opt in stackPreview" :key="opt.value" class="icon-rail__areas-dot">
              <ContentCategoryIcon :slug="opt.value" />
            </span>
          </span>
        </button>
        <ul id="rail-areas" class="icon-rail__areas-list" role="group" aria-label="Áreas">
          <li
            v-for="opt in AREA_OPTIONS"
            :key="opt.value"
          >
            <button
              type="button"
              class="icon-rail__btn"
              :class="{ 'icon-rail__btn--active': filters.area.includes(opt.value) }"
              :aria-label="opt.label"
              :aria-pressed="filters.area.includes(opt.value)"
              @click="toggleArea(opt.value)"
              @mouseenter="showTip"
              @mouseleave="hideTip"
              @focus="showTip"
              @blur="hideTip"
            >
              <span class="icon-rail__glyph" aria-hidden="true">
                <ContentCategoryIcon :slug="opt.value" />
              </span>
              <span class="icon-rail__areas-name">{{ opt.label }}</span>
            </button>
          </li>
        </ul>
      </li>

      <li class="icon-rail__sep" aria-hidden="true"></li>

      <li
        v-for="item in secondary"
        :key="item.to"
      >
        <NuxtLink
          :to="item.to"
          class="icon-rail__btn"
          :class="{ 'icon-rail__btn--active': isActive(item.to) }"
          :aria-label="item.label"
          :aria-current="isActive(item.to) ? 'page' : undefined"
          @mouseenter="showTip"
          @mouseleave="hideTip"
          @focus="showTip"
          @blur="hideTip"
        >
          <span class="icon-rail__glyph" aria-hidden="true">
            <svg v-if="item.icon === 'docs'" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
              <path d="M6 4h9l3 3v13H6z" />
              <path d="M15 4v3h3M8 12h8M8 16h5" />
            </svg>
            <svg v-else-if="item.icon === 'method'" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
              <path d="M4 6h16M4 12h16M4 18h10" />
            </svg>
            <svg v-else width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
              <circle cx="12" cy="12" r="9" />
              <path d="M9.5 9a2.5 2.5 0 0 1 4.2 1.8c0 2-2.2 2-2.2 3.2V15" />
              <circle cx="12" cy="17.5" r="0.6" fill="currentColor" />
            </svg>
          </span>
        </NuxtLink>
      </li>

      <li class="icon-rail__sep icon-rail__sep--end" aria-hidden="true"></li>

      <li v-for="item in projectLinks" :key="item.href">
        <a
          :href="item.href"
          class="icon-rail__btn icon-rail__btn--support"
          target="_blank"
          rel="noopener noreferrer"
          :aria-label="item.label"
          @mouseenter="showTip"
          @mouseleave="hideTip"
          @focus="showTip"
          @blur="hideTip"
        >
          <span class="icon-rail__glyph" aria-hidden="true">
            <svg v-if="item.icon === 'github'" width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 2a10 10 0 0 0-3.16 19.49c.5.09.68-.22.68-.48l-.01-1.7c-2.78.6-3.37-1.34-3.37-1.34-.45-1.15-1.11-1.46-1.11-1.46-.91-.62.07-.61.07-.61 1 .07 1.53 1.05 1.53 1.05.9 1.53 2.36 1.09 2.94.83.09-.65.35-1.09.63-1.34-2.22-.25-4.55-1.11-4.55-4.94 0-1.09.39-1.98 1.03-2.68-.1-.25-.45-1.27.1-2.64 0 0 .84-.27 2.75 1.02A9.6 9.6 0 0 1 12 6.8c.85 0 1.71.11 2.51.33 1.91-1.29 2.75-1.02 2.75-1.02.55 1.37.2 2.39.1 2.64.64.7 1.03 1.59 1.03 2.68 0 3.84-2.34 4.69-4.57 4.93.36.31.68.92.68 1.85l-.01 2.75c0 .27.18.58.69.48A10 10 0 0 0 12 2Z" />
            </svg>
            <svg v-else width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
              <path d="M12 21s-6.5-4.35-6.5-9.5a6.5 6.5 0 1 1 13 0C18.5 16.65 12 21 12 21Z" />
              <circle cx="12" cy="11.5" r="2.2" />
            </svg>
          </span>
        </a>
      </li>

      <li v-for="item in social" :key="item.href" :class="{ 'icon-rail__pin': item.icon === 'instagram' }">
        <a
          :href="item.href"
          class="icon-rail__btn"
          target="_blank"
          rel="noopener noreferrer"
          :aria-label="item.label"
          @mouseenter="showTip"
          @mouseleave="hideTip"
          @focus="showTip"
          @blur="hideTip"
        >
          <span class="icon-rail__glyph" aria-hidden="true">
            <svg v-if="item.icon === 'instagram'" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
              <rect x="4" y="4" width="16" height="16" rx="5" />
              <circle cx="12" cy="12" r="3.5" />
              <circle cx="17.2" cy="6.8" r="0.8" fill="currentColor" stroke="none" />
            </svg>
            <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
              <path d="M14.7 10.3 22.2 2h-1.8l-6.5 7.2L8.8 2H2.5l8 11.1L2.5 22h1.8l7-7.8L15.2 22h6.3L14.7 10.3Zm-2.5 2.8-.8-1.1L5 3.4h2.8l5.2 7.1.8 1.1 6.7 9.1h-2.8l-5.5-7.6Z" />
            </svg>
          </span>
        </a>
      </li>

    </ul>
    <Teleport to="body">
      <span
        v-if="tip"
        class="icon-rail-tip"
        :class="{ 'icon-rail-tip--below': tip.below }"
        role="tooltip"
        :style="{ left: `${tip.x}px`, top: `${tip.y}px` }"
      >{{ tip.label }}</span>
    </Teleport>
  </nav>
</template>
