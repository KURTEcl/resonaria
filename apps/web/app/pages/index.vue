<script setup lang="ts">
import { AREA_OPTIONS, INCLUYE_OPTIONS, PIEZA_OPTIONS } from '~/composables/useCatalogFilters'
import { topicCoverSrc } from '~/utils/topicCover'

const HOME_PAGE_SIZE = 12

const route = useRoute()
const router = useRouter()
const { fetchCases, apiUrl } = usePublicApi()
const { filters, toApiQuery, applyFilters, clearFilters } = useCatalogFilters()

type ActiveFilterChip = {
  key: string
  field: 'q' | 'area' | 'pieza' | 'incluye' | 'perfil'
  value: string
  label: string
}

const currentPage = computed(() => {
  const raw = Number(route.query.page)
  return Number.isFinite(raw) && raw >= 1 ? Math.floor(raw) : 1
})

const query = computed(() => ({
  per_page: String(HOME_PAGE_SIZE),
  page: String(currentPage.value),
  ...toApiQuery(filters.value),
}))
const { data, error, status, refresh } = await useAsyncData(
  'home-cases',
  () => fetchCases(query.value),
  { watch: [query], server: false, lazy: true },
)
const topics = computed(() => data.value?.data ?? [])
const total = computed(() => data.value?.meta.total ?? 0)
const lastPage = computed(() => Math.max(1, data.value?.meta.last_page ?? 1))
const pageNumbers = computed(() => Array.from({ length: lastPage.value }, (_, i) => i + 1))
const rangeStart = computed(() => {
  if (!total.value) return 0
  return (currentPage.value - 1) * HOME_PAGE_SIZE + 1
})
const rangeEnd = computed(() => {
  if (!total.value) return 0
  return Math.min(currentPage.value * HOME_PAGE_SIZE, total.value)
})

function catalogRouteQuery(page: number) {
  const next: Record<string, string | undefined> = {}
  const f = filters.value
  if (f.q) next.q = f.q
  if (f.area.length) next.area = f.area.join(',')
  if (f.pieza.length) next.pieza = f.pieza.join(',')
  if (f.incluye.length) next.incluye = f.incluye.join(',')
  if (f.perfil.length) next.perfil = f.perfil.join(',')
  if (page > 1) next.page = String(page)
  return next
}

async function goToPage(page: number) {
  const target = Math.min(Math.max(1, page), lastPage.value)
  if (target === currentPage.value) return
  await router.push({ path: '/', query: catalogRouteQuery(target) })
  document.getElementById('topics-toolbar')?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

watch(
  () => data.value?.meta,
  (meta) => {
    if (!meta || status.value === 'pending') return
    if (currentPage.value > meta.last_page && meta.last_page > 0) {
      void router.replace({ path: '/', query: catalogRouteQuery(meta.last_page) })
    }
  },
)

const { data: latestCoverData } = await useAsyncData(
  'home-latest-cover',
  () => fetchCases({ per_page: '1' }),
  { server: false, lazy: true },
)
const homeCoverSrc = computed(() => topicCoverSrc(latestCoverData.value?.data?.[0]?.cover_image_url))
const homeCoverStyle = computed(() =>
  homeCoverSrc.value ? { '--home-cover': `url(${homeCoverSrc.value})` } : undefined,
)
const { data: profileFilterData } = await useAsyncData('home-profile-filters', () =>
  $fetch<{ data: Array<{ slug: string, display_name: string, mentions_count?: number }> }>(
    apiUrl('/v1/perfiles'),
  ),
  { server: false, lazy: true },
)

const profileFilterOptions = computed(() =>
  (profileFilterData.value?.data ?? [])
    .filter((profile) => (profile.mentions_count ?? 0) > 0)
    .map((profile) => ({ value: profile.slug, label: profile.display_name })),
)

const filtered = computed(() => Boolean(
  filters.value.q
  || filters.value.area.length
  || filters.value.pieza.length
  || filters.value.incluye.length
  || filters.value.perfil.length,
))

const activeFilterChips = computed((): ActiveFilterChip[] => {
  const chips: ActiveFilterChip[] = []
  const f = filters.value

  if (f.q) {
    chips.push({ key: `q:${f.q}`, field: 'q', value: f.q, label: f.q })
  }
  for (const opt of AREA_OPTIONS) {
    if (f.area.includes(opt.value)) {
      chips.push({ key: `area:${opt.value}`, field: 'area', value: opt.value, label: opt.label })
    }
  }
  for (const opt of PIEZA_OPTIONS) {
    if (f.pieza.includes(opt.value)) {
      chips.push({ key: `pieza:${opt.value}`, field: 'pieza', value: opt.value, label: opt.label })
    }
  }
  for (const opt of INCLUYE_OPTIONS) {
    if (f.incluye.includes(opt.value)) {
      chips.push({ key: `incluye:${opt.value}`, field: 'incluye', value: opt.value, label: opt.label })
    }
  }
  for (const opt of profileFilterOptions.value) {
    if (f.perfil.includes(opt.value)) {
      chips.push({ key: `perfil:${opt.value}`, field: 'perfil', value: opt.value, label: opt.label })
    }
  }

  return chips
})

function removeFilterChip(chip: ActiveFilterChip) {
  const f = filters.value
  const next = {
    ...f,
    q: chip.field === 'q' ? '' : f.q,
    area: chip.field === 'area' ? f.area.filter((v) => v !== chip.value) : f.area,
    pieza: chip.field === 'pieza' ? f.pieza.filter((v) => v !== chip.value) : f.pieza,
    incluye: chip.field === 'incluye' ? f.incluye.filter((v) => v !== chip.value) : f.incluye,
    perfil: chip.field === 'perfil' ? f.perfil.filter((v) => v !== chip.value) : f.perfil,
  }
  applyFilters(next, '/')
}

definePageMeta({ layout: 'default' })
usePageSeo({
  title: 'Actualidad, análisis y opinión política',
  description: 'Noticias y análisis políticos de Chile y el mundo, con contexto y fuentes.',
  path: '/',
  jsonLd: {
    '@context': 'https://schema.org',
    '@type': 'WebSite',
    name: 'Resonaria',
    url: absoluteUrl('/'),
    inLanguage: 'es-CL',
    potentialAction: {
      '@type': 'SearchAction',
      target: `${siteOrigin()}/?q={search_term_string}`,
      'query-input': 'required name=search_term_string',
    },
  },
})
</script>

<template>
  <div
    class="home"
    :class="{ 'home--has-cover': homeCoverSrc }"
    :style="homeCoverStyle"
  >
    <div v-if="homeCoverSrc" class="home__cover" aria-hidden="true" />
    <section class="intro" aria-labelledby="home-title">
      <div class="intro__lead">
        <h1 id="home-title">La actualidad política.<br><span>En contexto.</span></h1>
      </div>
      <div class="intro__copy">
        <p class="intro__deck">Noticias, análisis y opinión sobre Chile y el mundo. Seguimos las decisiones públicas y sus efectos con contexto, fuentes y perspectivas claramente diferenciadas.</p>
      </div>
    </section>

    <section class="topics" aria-label="Catálogo de temas">
      <header id="topics-toolbar" class="topics__toolbar">
        <div class="topics__filters">
          <ShellFilterMenu label="Formato" field="pieza" :options="PIEZA_OPTIONS" filter-path="/" />
          <ShellFilterMenu label="Incluye" field="incluye" :options="INCLUYE_OPTIONS" filter-path="/" />
          <ShellFilterMenu
            v-if="profileFilterOptions.length"
            label="Perfil"
            field="perfil"
            :options="profileFilterOptions"
            filter-path="/"
          />
        </div>
        <div v-if="activeFilterChips.length" class="topics__active-filters">
          <ul class="filter-chips" aria-label="Filtros activos">
            <li v-for="chip in activeFilterChips" :key="chip.key">
              <button
                type="button"
                class="filter-chip"
                :aria-label="`Quitar filtro ${chip.label}`"
                @click="removeFilterChip(chip)"
              >
                <span class="filter-chip__label">{{ chip.label }}</span>
                <span class="filter-chip__icon" aria-hidden="true">×</span>
              </button>
            </li>
          </ul>
          <button type="button" class="filter-chips__clear" @click="clearFilters('/')">
            Quitar todos
          </button>
        </div>
      </header>

      <div :aria-busy="status === 'pending'">
        <p v-if="status === 'pending'" class="state" role="status">Buscando temas…</p>

        <div v-else-if="error" class="state" role="alert">
          <h3>No pudimos cargar los temas</h3>
          <p>El servicio no respondió. Vuelve a intentarlo en unos momentos.</p>
          <button type="button" class="btn btn--primary" @click="refresh()">Volver a intentar</button>
        </div>

        <div v-else-if="!topics.length" class="state">
          <h3>{{ filtered ? 'Ningún tema coincide con estos filtros' : 'Aún no hay temas publicados' }}</h3>
          <p>{{ filtered ? 'Prueba con otra categoría del menú lateral o quita los filtros para ver todos los temas.' : 'Aquí aparecerán las decisiones públicas que estamos siguiendo.' }}</p>
          <button v-if="filtered" type="button" class="btn btn--quiet" @click="clearFilters('/')">Ver todos los temas</button>
        </div>

        <template v-else>
          <ol class="cards">
            <li v-for="topic in topics" :key="topic.slug">
              <CatalogTopicCard :topic="topic" />
            </li>
          </ol>

          <nav
            v-if="lastPage > 1"
            class="topics-pagination"
            aria-label="Paginación de temas"
          >
            <p class="topics-pagination__summary" aria-live="polite">
              {{ rangeStart }}–{{ rangeEnd }} de {{ total }}
            </p>
            <div class="topics-pagination__controls">
              <button
                type="button"
                class="topics-pagination__nav"
                :disabled="currentPage <= 1"
                @click="goToPage(currentPage - 1)"
              >
                Anterior
              </button>
              <ol class="topics-pagination__pages">
                <li v-for="page in pageNumbers" :key="page">
                  <button
                    type="button"
                    class="topics-pagination__page"
                    :class="{ 'topics-pagination__page--current': page === currentPage }"
                    :aria-current="page === currentPage ? 'page' : undefined"
                    @click="goToPage(page)"
                  >
                    {{ page }}
                  </button>
                </li>
              </ol>
              <button
                type="button"
                class="topics-pagination__nav"
                :disabled="currentPage >= lastPage"
                @click="goToPage(currentPage + 1)"
              >
                Siguiente
              </button>
            </div>
          </nav>
        </template>
      </div>

    </section>
  </div>
</template>

<style scoped>
.home {
  --home-cover-opacity: 0.22;
  position: relative;
  isolation: isolate;
  width: 100%;
  padding: 0 8px 28px;
}

.home__cover {
  position: fixed;
  inset: 0;
  z-index: -1;
  pointer-events: none;
  background-color: var(--surface, #f6f5f2);
  background-image:
    linear-gradient(
      180deg,
      color-mix(in srgb, var(--surface, #f6f5f2) 72%, transparent) 0%,
      color-mix(in srgb, var(--surface, #f6f5f2) 88%, transparent) 38%,
      var(--surface, #f6f5f2) 72%
    ),
    var(--home-cover);
  background-size: cover, cover;
  background-position: center, center;
  opacity: var(--home-cover-opacity);
  filter: saturate(0.92) blur(0);
}

.home--has-cover .intro h1,
.home--has-cover .intro__deck,
.home--has-cover .topics__toolbar {
  text-shadow: 0 1px 24px color-mix(in srgb, var(--surface, #f6f5f2) 65%, transparent);
}

.intro {
  display: grid;
  grid-template-columns: minmax(0, 1.25fr) minmax(0, 1fr);
  gap: 20px 48px;
  align-items: end;
  margin: 0 0 8px;
  padding: 16px 8px 0;
}

.intro__lead {
  min-width: 0;
}

.intro h1 {
  margin: 0;
  font-family: var(--font-serif);
  font-size: clamp(44px, 5.2vw, 76px);
  font-weight: 600;
  line-height: 0.98;
  letter-spacing: -0.035em;
  color: var(--ink);
}

.intro h1 span {
  color: var(--primary);
  font-style: italic;
  font-weight: 560;
}

.intro__deck {
  max-width: 48ch;
  margin: 0;
  font-size: 18px;
  line-height: 1.55;
  color: var(--muted);
}

.intro__deck strong {
  color: var(--ink);
  font-weight: 650;
}

.topics__toolbar {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 10px 16px;
  margin-top: 28px;
  padding: 20px 8px 14px;
  border-top: 1px solid var(--hairline);
  scroll-margin-top: 16px;
  overflow: visible;
}

.topics__filters {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  flex: 0 0 auto;
  overflow: visible;
}

.topics__active-filters {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px 12px;
  min-width: 0;
  margin-left: auto;
}

.filter-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin: 0;
  padding: 0;
  list-style: none;
}

.filter-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  max-width: min(280px, 100%);
  padding: 6px 10px;
  border: 1px solid var(--hairline);
  border-radius: 999px;
  background: color-mix(in srgb, var(--surface) 88%, var(--primary) 12%);
  color: var(--ink);
  font-size: 13px;
  font-weight: 600;
  line-height: 1.2;
  cursor: pointer;
}

.filter-chip:hover {
  border-color: color-mix(in srgb, var(--primary) 40%, var(--hairline));
  color: var(--primary);
}

.filter-chip__label {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.filter-chip__icon {
  flex-shrink: 0;
  font-size: 16px;
  line-height: 1;
  opacity: 0.65;
}

.filter-chips__clear {
  padding: 6px 0;
  border: 0;
  background: none;
  color: var(--primary);
  font-size: 13px;
  font-weight: 650;
  text-decoration: underline;
  text-underline-offset: 0.18em;
  cursor: pointer;
  white-space: nowrap;
}

.filter-chips__clear:hover {
  color: var(--primary-hover);
}

.cards {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 14px;
  align-items: stretch;
  margin: 0;
  padding: 0;
  list-style: none;
}

.cards > li {
  display: flex;
  min-height: 0;
}

.topics-pagination {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 12px 20px;
  margin: 22px 8px 0;
  padding-top: 18px;
  border-top: 1px solid var(--hairline);
}

.topics-pagination__summary {
  margin: 0;
  font-size: 14px;
  color: var(--muted);
}

.topics-pagination__controls {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px 12px;
  margin-left: auto;
}

.topics-pagination__nav {
  padding: 8px 12px;
  border: 1px solid var(--hairline);
  border-radius: 8px;
  background: var(--surface);
  color: var(--ink);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
}

.topics-pagination__nav:hover:not(:disabled) {
  border-color: color-mix(in srgb, var(--primary) 35%, var(--hairline));
  color: var(--primary);
}

.topics-pagination__nav:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

.topics-pagination__pages {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin: 0;
  padding: 0;
  list-style: none;
}

.topics-pagination__page {
  min-width: 36px;
  padding: 8px 10px;
  border: 1px solid transparent;
  border-radius: 8px;
  background: transparent;
  color: var(--muted);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
}

.topics-pagination__page:hover {
  color: var(--primary);
  background: color-mix(in srgb, var(--primary) 8%, transparent);
}

.topics-pagination__page--current {
  border-color: var(--hairline);
  background: var(--surface);
  color: var(--ink);
}

.state {
  max-width: 560px;
  padding: 28px 8px;
  color: var(--muted);
}

.state h3 {
  margin: 0 0 8px;
  font-size: 22px;
}

.state p {
  margin: 0 0 18px;
  line-height: 1.6;
}

@media (max-width: 980px) {
  .cards {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .intro {
    grid-template-columns: 1fr;
    gap: 20px;
  }
}

@media (max-width: 640px) {
  .cards {
    grid-template-columns: 1fr;
  }

  .intro {
    margin: 0;
    padding: 12px 4px 0;
  }

  .topics__toolbar {
    margin-top: 24px;
    padding-top: 18px;
  }

  .topics__active-filters {
    margin-left: 0;
    width: 100%;
  }

  .intro h1 {
    font-size: 36px;
  }

  .intro__deck {
    font-size: 17px;
  }

  .topics-pagination {
    flex-direction: column;
    align-items: stretch;
  }

  .topics-pagination__controls {
    margin-left: 0;
    justify-content: center;
  }
}
</style>
