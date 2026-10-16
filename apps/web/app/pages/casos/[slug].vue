<script setup lang="ts">
import { CHANGE_TYPE_LABELS, CLAIM_VERDICT_LABELS, EFFECT_LABELS, EVIDENCE_LABELS, LINK_RELATION_LABELS, PROCEDURAL_LABELS, SOURCE_KIND_LABELS, UPDATE_DIRECTION_LABELS, UPDATE_KIND_LABELS, followUpLead, formatDate, formatMilestoneWhen, label, relatedNotesLead, repliesLead } from '~/utils/topicLabels'
import { profileInitials } from '~/utils/profileDisplay'
import { topicCoverAbsoluteUrl, topicCoverSrc } from '~/utils/topicCover'
import { narrativeDetailSections, parseDetailSections, sourceHost, splitSourceTitle, stripMarkedText, textPolarity } from '~/utils/topicDetail'
import { isDecisionFollowUp, isDevelopingStory, isReviewOverdue } from '~/utils/topicSignals'

const route = useRoute()
const slug = computed(() => String(route.params.slug || ''))
const { fetchCase } = usePublicApi()
const { trackViewCase, trackSourceClick } = useAnalytics()
const { following, busy, follow, unfollow } = useTopicFollow(() => slug.value)

const { data, error, refresh, status } = await useAsyncData(
  'case-detail',
  () => fetchCase(slug.value),
  { watch: [slug], server: false, lazy: true },
)

const caseData = computed(() => data.value?.data)
const topicCover = computed(() => topicCoverSrc(caseData.value?.seo.og_image_url))
const pane = ref<'tema' | 'redes'>('tema')
const notFound = computed(() => (error.value as { statusCode?: number } | null)?.statusCode === 404)

const trackingDecision = computed(() => (caseData.value ? isDecisionFollowUp(caseData.value) : false))
const developingStory = computed(() => (caseData.value ? isDevelopingStory(caseData.value) : false))

const detailSections = computed(() =>
  narrativeDetailSections(parseDetailSections(caseData.value?.detail ?? '')),
)
const beforeTone = computed(() => textPolarity(caseData.value?.before_text, 'gain'))
const afterTone = computed(() => textPolarity(caseData.value?.after_text, 'loss'))
const originChips = computed(() =>
  (caseData.value?.sources ?? [])
    .map((source, index) => ({ source, n: index + 1 }))
    .filter(({ source }) => source.kind === 'documento' || source.is_primary),
)

const followUps = computed(() => caseData.value?.milestones ?? [])
const followUpSummary = computed(() => followUpLead(followUps.value, { developing: developingStory.value }))
const replies = computed(() => caseData.value?.claims ?? [])
const repliesSummary = computed(() => repliesLead(replies.value.length))
const sources = computed(() => caseData.value?.sources ?? [])
const sourcesLead = computed(() => {
  const list = sources.value
  if (!list.length) return ''
  const official = list.filter((s) => s.kind === 'documento' || s.is_primary).length
  const parts: string[] = []
  if (official) {
    parts.push(official === 1 ? '1 fuente oficial' : `${official} fuentes oficiales`)
  }
  parts.push('Lista numerada; los superíndices del texto enlazan aquí.')
  return parts.join(' · ')
})
const outgoingNotes = computed(() => caseData.value?.links ?? [])
const incomingNotes = computed(() => caseData.value?.linked_from ?? [])
const portalNotes = computed(() => outgoingNotes.value)
const relatedSummary = computed(() => relatedNotesLead(outgoingNotes.value.length, incomingNotes.value.length))
const profileLinks = computed(() => caseData.value?.profiles ?? [])
const PROFILE_AVATAR_CAP = 4
const visibleProfileAvatars = computed(() => profileLinks.value.slice(0, PROFILE_AVATAR_CAP))
const profileAvatarOverflow = computed(() => Math.max(0, profileLinks.value.length - PROFILE_AVATAR_CAP))
const profileOverflowOpen = ref(false)
const profileOverflowRoot = ref<HTMLElement | null>(null)
const profileOverflowPopover = ref<HTMLElement | null>(null)
const profileOverflowPos = ref<{ top: string, left: string } | null>(null)
const repliesDetails = ref<HTMLDetailsElement | null>(null)

function toggleProfileOverflow(event: MouseEvent) {
  const opening = !profileOverflowOpen.value
  profileOverflowOpen.value = opening
  if (!opening) {
    profileOverflowPos.value = null
    return
  }
  const btn = event.currentTarget as HTMLElement
  const rect = btn.getBoundingClientRect()
  profileOverflowPos.value = {
    top: `${rect.bottom + 8}px`,
    left: `${Math.max(8, rect.left)}px`,
  }
}

function closeProfileOverflow() {
  profileOverflowOpen.value = false
  profileOverflowPos.value = null
}

function onProfileOverflowDocClick(event: MouseEvent) {
  if (!profileOverflowOpen.value) return
  const target = event.target as Node
  if (profileOverflowRoot.value?.contains(target) || profileOverflowPopover.value?.contains(target)) {
    return
  }
  closeProfileOverflow()
}

function onProfileOverflowKey(event: KeyboardEvent) {
  if (event.key === 'Escape') {
    profileOverflowOpen.value = false
  }
}

watch(profileLinks, () => {
  profileOverflowOpen.value = false
})

onMounted(() => {
  document.addEventListener('click', onProfileOverflowDocClick)
  document.addEventListener('keydown', onProfileOverflowKey)
})

onUnmounted(() => {
  document.removeEventListener('click', onProfileOverflowDocClick)
  document.removeEventListener('keydown', onProfileOverflowKey)
})

const record = computed(() => {
  const c = caseData.value
  if (!c) return []

  if (trackingDecision.value) {
    const items = [
      { icon: 'etapa', term: 'Etapa', value: label(PROCEDURAL_LABELS, c.procedural_status) },
      { icon: 'evidencia', term: 'Evidencia', value: label(EVIDENCE_LABELS, c.evidence_status, 'Sin evaluar') },
      { icon: 'efecto', term: 'Efecto', value: label(EFFECT_LABELS, c.observed_effect, 'Aún sin observar') },
    ]
    if (c.change_types.length) {
      items.push({
        icon: 'tipo',
        term: 'Tipo',
        value: c.change_types.map((t) => label(CHANGE_TYPE_LABELS, t)).join(', '),
      })
    }
    items.push({
      icon: 'revisado',
      term: 'Revisado',
      value: formatDate(c.reviewed_at) ?? 'Sin revisión',
      time: c.reviewed_at,
    })
    if (c.next_review_at) {
      items.push({
        icon: 'proxima',
        term: 'Próxima revisión',
        value: formatDate(c.next_review_at) ?? 'Sin fecha',
        time: c.next_review_at,
      })
    }
    return items
  }

  const items: Array<{ icon: string, term: string, value: string, time?: string | null }> = []
  if (c.published_at) {
    items.push({
      icon: 'revisado',
      term: 'Publicado',
      value: formatDate(c.published_at) ?? 'Sin fecha',
      time: c.published_at,
    })
  }
  const reviewedLabel = formatDate(c.reviewed_at)
  const publishedLabel = formatDate(c.published_at)
  if (c.reviewed_at && reviewedLabel && reviewedLabel !== publishedLabel) {
    items.push({
      icon: 'revisado',
      term: 'Actualizado',
      value: reviewedLabel,
      time: c.reviewed_at,
    })
  }
  if (c.sources_count > 0) {
    items.push({
      icon: 'evidencia',
      term: 'Fuentes',
      value: c.sources_count === 1 ? '1 fuente' : `${c.sources_count} fuentes`,
    })
  }
  if ((c.reactions?.length ?? 0) > 0) {
    const n = c.reactions!.length
    items.push({
      icon: 'efecto',
      term: 'En redes',
      value: n === 1 ? '1 publicación' : `${n} publicaciones`,
    })
  }
  if (c.next_review_at) {
    items.push({
      icon: 'proxima',
      term: 'Próxima revisión',
      value: formatDate(c.next_review_at) ?? 'Sin fecha',
      time: c.next_review_at,
    })
  }
  return items
})

definePageMeta({ layout: 'default' })

watch(
  () => [caseData.value?.slug, route.hash] as const,
  async ([, hash]) => {
    if (hash !== '#respuestas') return
    pane.value = 'tema'
    await nextTick()
    const el = repliesDetails.value
    if (!el) return
    el.open = true
    el.scrollIntoView({ block: 'start' })
  },
)

function syncTopicSiteHeroClass(enabled: boolean) {
  if (!import.meta.client) return
  document.documentElement.classList.toggle('has-topic-site-hero', enabled)
}

watch(
  topicCover,
  (cover) => syncTopicSiteHeroClass(Boolean(cover)),
  { immediate: true },
)

onUnmounted(() => syncTopicSiteHeroClass(false))

watch(
  () => caseData.value?.slug,
  (value) => {
    if (!value) return
    trackViewCase(value, caseData.value?.category?.title)
  },
)

watch(
  () => [caseData.value?.slug, caseData.value?.seo.meta_title] as const,
  ([, title]) => {
    if (!title) return
    gaPageView(`${location.pathname}${location.search}`, title, caseData.value?.category?.title || 'Ficha')
  },
  { immediate: true },
)

watchEffect(() => {
  if (notFound.value) {
    usePageSeo({
      title: 'No encontramos este tema',
      description: 'Puede que la dirección esté mal escrita o que el tema ya no esté publicado.',
      robots: 'noindex,follow',
    })
    return
  }
  if (!caseData.value) return
  const title = caseData.value.seo.meta_title
  const description = caseData.value.seo.meta_description
    || stripMarkedText(caseData.value.hook)
    || stripMarkedText(caseData.value.summary)
  const url = caseData.value.seo.canonical_url || absoluteUrl(`/casos/${caseData.value.slug}`)
  usePageSeo({
    title,
    description,
    path: `/casos/${caseData.value.slug}`,
    ogType: 'article',
    robots: caseData.value.seo.robots,
    image: topicCoverAbsoluteUrl(caseData.value.seo.og_image_url),
    publishedTime: caseData.value.published_at,
    modifiedTime: caseData.value.reviewed_at || caseData.value.published_at,
    jsonLd: {
      '@context': 'https://schema.org',
      '@type': 'Article',
      headline: title,
      description,
      datePublished: caseData.value.published_at,
      dateModified: caseData.value.reviewed_at || caseData.value.published_at,
      inLanguage: 'es-CL',
      mainEntityOfPage: url,
      author: { '@type': 'Organization', name: 'Resonaria', url: absoluteUrl('/') },
      publisher: { '@type': 'Organization', name: 'Resonaria', url: absoluteUrl('/') },
    },
  })
})
</script>

<template>
  <p v-if="status === 'pending'" class="topic-missing" role="status">Cargando…</p>

  <div v-else-if="error" class="topic-missing">
    <h1>{{ notFound ? 'No encontramos este tema' : 'No pudimos cargar el tema' }}</h1>
    <p>{{ notFound ? 'Puede que la dirección esté mal escrita o que el tema ya no esté publicado.' : 'El servicio no respondió. Vuelve a intentarlo en unos momentos.' }}</p>
    <p class="topic-missing__actions">
      <button v-if="!notFound" type="button" class="btn btn--primary" @click="refresh()">Volver a intentar</button>
      <NuxtLink to="/" class="btn btn--quiet">Ver todos los temas</NuxtLink>
    </p>
  </div>

  <article v-else-if="caseData" class="topic" :class="{ 'topic--has-cover': topicCover }">
    <div class="topic-split">
      <div
        id="panel-tema"
        role="tabpanel"
        aria-labelledby="tab-tema"
        class="topic-pane topic-column"
        :class="{ 'topic-column--has-cover': topicCover, 'is-active': pane === 'tema' }"
      >
        <nav class="crumbs" aria-label="Ruta" :class="{ 'crumbs--on-cover': topicCover }">
          <NuxtLink to="/">Inicio</NuxtLink>
          <template v-if="caseData.category">
            <span aria-hidden="true">/</span>
            <NuxtLink :to="{ path: '/', query: { area: caseData.category.slug } }">{{ caseData.category.title }}</NuxtLink>
          </template>
        </nav>

        <div class="topic-tabs" role="tablist" aria-label="Contenido del tema">
          <button
            id="tab-tema"
            type="button"
            role="tab"
            aria-controls="panel-tema"
            :aria-selected="pane === 'tema'"
            :class="{ 'is-selected': pane === 'tema' }"
            @click="pane = 'tema'"
          >
            El tema
          </button>
          <button
            id="tab-redes"
            type="button"
            role="tab"
            aria-controls="panel-redes"
            :aria-selected="pane === 'redes'"
            :class="{ 'is-selected': pane === 'redes' }"
            @click="pane = 'redes'"
          >
            En redes <span class="num">({{ caseData.reactions?.length ?? 0 }})</span>
          </button>
        </div>

        <div class="topic-read">
        <header class="topic-head" :class="{ 'topic-head--on-cover': topicCover }">
          <div class="topic-head__row">
            <ContentStatusBadge
              v-if="trackingDecision"
              :estado="caseData.public_status_label ?? 'por_confirmar'"
            />
            <p v-if="trackingDecision && isReviewOverdue(caseData.next_review_at)" class="topic-head__signals">
              <ContentEditorialSignal kind="review_overdue" />
            </p>
            <button
              type="button"
              class="topic-follow"
              :disabled="busy"
              :aria-pressed="following"
              @click="following ? unfollow() : follow()"
            >
              {{ following ? 'Dejar de seguir' : 'Seguir tema' }}
            </button>
          </div>
          <h1>{{ caseData.title }}</h1>
          <p v-if="caseData.hook" class="topic-hook">
            <ContentCitedText :text="caseData.hook" :sources="sources" :notes="portalNotes" />
          </p>
        </header>

        <dl v-if="record.length || profileLinks.length" class="record">
          <div v-if="profileLinks.length" class="record__profiles">
            <div ref="profileOverflowRoot" class="record__avatars-wrap">
              <dd class="record__avatars" aria-label="Perfiles asociados">
                <NuxtLink
                  v-for="(profile, index) in visibleProfileAvatars"
                  :key="profile.slug"
                  :to="`/perfiles/${profile.slug}`"
                  class="record__avatar"
                  :style="{ zIndex: visibleProfileAvatars.length - index }"
                  :title="profile.display_name"
                >
                  <img
                    v-if="profile.photo_url"
                    :src="profile.photo_url"
                    alt=""
                    width="36"
                    height="36"
                    loading="lazy"
                  >
                  <span v-else class="record__avatar-initials" aria-hidden="true">{{ profileInitials(profile.display_name) }}</span>
                </NuxtLink>
                <button
                  v-if="profileAvatarOverflow > 0"
                  type="button"
                  class="record__avatar record__avatar--more"
                  :aria-expanded="profileOverflowOpen"
                  aria-controls="record-profiles-popover"
                  :aria-label="`Ver los ${profileLinks.length} perfiles asociados`"
                  @click.stop="toggleProfileOverflow"
                >
                  +{{ profileAvatarOverflow }}
                </button>
              </dd>
              <Teleport to="body">
                <div
                  v-if="profileOverflowOpen && profileOverflowPos"
                  id="record-profiles-popover"
                  ref="profileOverflowPopover"
                  class="record__profiles-popover"
                  role="dialog"
                  aria-label="Perfiles asociados"
                  :style="{ top: profileOverflowPos.top, left: profileOverflowPos.left }"
                >
                  <p class="record__profiles-popover-title">Perfiles en esta nota</p>
                  <ul class="record__profiles-popover-list">
                    <li v-for="profile in profileLinks" :key="profile.slug">
                      <NuxtLink
                        :to="`/perfiles/${profile.slug}`"
                        class="record__profiles-popover-item"
                        @click="closeProfileOverflow"
                      >
                        <span class="record__avatar record__avatar--in-popover" aria-hidden="true">
                          <img
                            v-if="profile.photo_url"
                            :src="profile.photo_url"
                            alt=""
                            width="32"
                            height="32"
                            loading="lazy"
                          >
                          <span v-else class="record__avatar-initials">{{ profileInitials(profile.display_name) }}</span>
                        </span>
                        <span class="record__profiles-popover-name">{{ profile.display_name }}</span>
                      </NuxtLink>
                    </li>
                  </ul>
                </div>
              </Teleport>
            </div>
            <NuxtLink
              v-if="profileLinks.length === 1"
              :to="`/perfiles/${profileLinks[0].slug}`"
              class="record__profile-name"
            >{{ profileLinks[0].display_name }}</NuxtLink>
          </div>
          <div v-for="item in record" :key="item.term" :title="`${item.term}: ${item.value}`">
            <dt>
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <template v-if="item.icon === 'etapa'">
                  <circle cx="6" cy="12" r="2.2" />
                  <circle cx="18" cy="12" r="2.2" />
                  <path d="M8.4 12h7.2" />
                </template>
                <template v-else-if="item.icon === 'evidencia'">
                  <path d="M7 4h7l5 5v11H7Z" />
                  <path d="M14 4v5h5M9 13h6M9 17h4" />
                </template>
                <template v-else-if="item.icon === 'efecto'">
                  <path d="M3 13h4.2l1.6-4.2 2.4 7.4 2.2-3.2H21" />
                </template>
                <template v-else-if="item.icon === 'tipo'">
                  <path d="M20.2 13.2 13 20.4 3.6 11V3.6H11l9.2 9.6Z" />
                  <circle cx="8.2" cy="8.2" r="1.15" fill="currentColor" stroke="none" />
                </template>
                <template v-else-if="item.icon === 'revisado'">
                  <rect x="4" y="6" width="16" height="14" rx="2" />
                  <path d="M8 4v4M16 4v4M4 11h16" />
                </template>
                <template v-else>
                  <circle cx="12" cy="13" r="8" />
                  <path d="M12 9.5V13l2.6 1.6" />
                </template>
              </svg>
              <span class="sr-only">{{ item.term }}</span>
            </dt>
            <dd><time v-if="item.time" :datetime="item.time">{{ item.value }}</time><template v-else>{{ item.value }}</template></dd>
          </div>
        </dl>

        <div class="topic-facts">
          <section v-if="caseData.what_changes" aria-labelledby="h-cambia">
            <h2 id="h-cambia">Qué cambia</h2>
            <p><ContentCitedText :text="caseData.what_changes" :sources="sources" :notes="portalNotes" /></p>
          </section>
          <section v-if="caseData.who_affected" aria-labelledby="h-afecta">
            <h2 id="h-afecta">A quién afecta</h2>
            <p><ContentCitedText :text="caseData.who_affected" :sources="sources" :notes="portalNotes" /></p>
          </section>
        </div>

        <section v-if="caseData.before_text || caseData.after_text" class="topic-compare" aria-labelledby="h-compara">
          <h2 id="h-compara">
            {{ trackingDecision ? 'Antes y con el cambio' : developingStory ? 'Antes y durante el episodio' : 'Comparación en el tiempo' }}
          </h2>
          <div class="compare">
            <div class="compare__col" :class="beforeTone ? `compare__col--${beforeTone}` : undefined">
              <h3>{{ trackingDecision ? 'Hasta ahora' : developingStory ? 'Situación previa' : 'Período de referencia' }}</h3>
              <p><ContentCitedText :text="caseData.before_text || 'Sin información registrada.'" :sources="sources" :notes="portalNotes" /></p>
            </div>
            <div class="compare__col compare__col--after" :class="developingStory ? 'compare__col--episode' : afterTone ? `compare__col--${afterTone}` : undefined">
              <h3>{{ trackingDecision ? 'Si se aprueba así' : developingStory ? 'Lo reportado en el episodio' : 'Período más reciente' }}</h3>
              <p><ContentCitedText :text="caseData.after_text || 'Sin información registrada.'" :sources="sources" :notes="portalNotes" /></p>
            </div>
          </div>
        </section>

        <div class="topic-points">
          <h2 class="topic-points__heading">Para profundizar</h2>

          <details v-if="replies.length" id="respuestas" ref="repliesDetails" class="topic-point" name="ficha-tema">
            <summary>
              <span class="topic-point__copy">
                <span class="topic-point__title">Comentarios y respuestas <span class="num">({{ replies.length }})</span></span>
                <span class="topic-point__lead topic-point__lead--teaser">{{ repliesSummary }}</span>
              </span>
              <span class="topic-point__expand">
                <span class="topic-point__expand-label" aria-hidden="true">
                  <span class="topic-point__expand-when-closed">Ampliar</span>
                  <span class="topic-point__expand-when-open">Cerrar</span>
                </span>
                <span class="topic-point__mark" aria-hidden="true" />
              </span>
            </summary>
            <div class="topic-point__panel">
              <ol class="claims">
                <li
                  v-for="(item, index) in replies"
                  :key="`${item.verdict}-${index}`"
                  class="claim"
                  :class="`claim--${item.verdict}`"
                >
                  <p class="claim__verdict">{{ label(CLAIM_VERDICT_LABELS, item.verdict) }}</p>
                  <blockquote class="claim__said">{{ item.claim }}</blockquote>
                  <p v-if="item.heard_on" class="claim__where">{{ item.heard_on }}</p>
                  <div class="claim__answer">
                    <ContentCitedText :text="item.response" :sources="sources" :notes="portalNotes" />
                  </div>
                  <a
                    v-if="item.source_url"
                    class="claim__source"
                    :href="item.source_url"
                    rel="noopener noreferrer"
                    target="_blank"
                    @click="trackSourceClick(slug)"
                  >
                    {{ item.source_title || 'Ver fuente' }}
                  </a>
                </li>
              </ol>
            </div>
          </details>

          <details v-if="followUps.length" class="topic-point" name="ficha-tema">
            <summary>
              <span class="topic-point__copy">
                <span class="topic-point__title">{{ developingStory ? 'Evolución' : 'Cómo va' }}</span>
                <span class="topic-point__lead topic-point__lead--teaser">{{ followUpSummary }}</span>
              </span>
              <span class="topic-point__expand">
                <span class="topic-point__expand-label" aria-hidden="true">
                  <span class="topic-point__expand-when-closed">Ampliar</span>
                  <span class="topic-point__expand-when-open">Cerrar</span>
                </span>
                <span class="topic-point__mark" aria-hidden="true" />
              </span>
            </summary>
            <div class="topic-point__panel">
              <ol class="timeline" :class="{ 'timeline--developing': developingStory }">
                <li
                  v-for="(milestone, index) in followUps"
                  :key="`${milestone.occurred_at || milestone.occurred_on}-${index}`"
                  :class="developingStory ? 'timeline__item timeline__item--developing' : `timeline__item timeline__item--${milestone.direction || 'incierto'}`"
                >
                  <time :datetime="milestone.occurred_at || milestone.occurred_on">{{ formatMilestoneWhen(milestone) }}</time>
                  <div class="timeline__body">
                    <p v-if="developingStory" class="timeline__direction timeline__direction--developing">
                      <span v-if="milestone.kind && milestone.kind !== 'otro'" class="timeline__kind">{{ label(UPDATE_KIND_LABELS, milestone.kind) }}</span>
                    </p>
                    <p v-else class="timeline__direction">
                      <svg width="12" height="12" viewBox="0 0 12 12" aria-hidden="true">
                        <path v-if="milestone.direction === 'riesgo'" d="M6 2.2v7.6M3.4 7.2 6 9.8l2.6-2.6" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path v-else-if="milestone.direction === 'reversion'" d="M6 9.8V2.2M3.4 4.8 6 2.2l2.6 2.6" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path v-else d="M2.4 6h7.2" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                      </svg>
                      {{ label(UPDATE_DIRECTION_LABELS, milestone.direction || 'incierto') }}
                      <span v-if="milestone.kind && milestone.kind !== 'otro'" class="timeline__kind">{{ label(UPDATE_KIND_LABELS, milestone.kind) }}</span>
                    </p>
                    <p>{{ milestone.label }}</p>
                    <p v-if="milestone.note" class="timeline__note">{{ milestone.note }}</p>
                    <a
                      v-if="milestone.source_url"
                      class="timeline__source"
                      :href="milestone.source_url"
                      rel="noopener noreferrer"
                      target="_blank"
                      @click="trackSourceClick(slug)"
                    >
                      {{ milestone.source_title || 'Ver fuente' }}
                    </a>
                  </div>
                </li>
              </ol>
            </div>
          </details>

          <template v-for="section in detailSections" :key="section.title">
            <details
              v-if="section.body.length"
              class="topic-point"
              name="ficha-tema"
            >
              <summary>
                <span class="topic-point__copy">
                  <span class="topic-point__title">{{ section.title }}</span>
                  <span v-if="section.lead" class="topic-point__lead topic-point__lead--teaser">
                    <ContentCitedText :text="section.lead" :sources="sources" :notes="portalNotes" />
                  </span>
                </span>
                <span class="topic-point__expand">
                  <span class="topic-point__expand-label" aria-hidden="true">
                    <span class="topic-point__expand-when-closed">Ampliar</span>
                    <span class="topic-point__expand-when-open">Cerrar</span>
                  </span>
                  <span class="topic-point__mark" aria-hidden="true" />
                </span>
              </summary>
              <div class="topic-point__panel">
                <div v-if="section.lead" class="topic-point__lead-full">
                  <ContentCitedText :text="section.lead" :sources="sources" :notes="portalNotes" />
                </div>
                <div class="topic-point__body">
                  <p v-for="(paragraph, pIndex) in section.body" :key="pIndex">
                    <ContentCitedText :text="paragraph" :sources="sources" :notes="portalNotes" />
                  </p>
                </div>
              </div>
            </details>
            <div v-else class="topic-point topic-point--static">
              <div class="topic-point__copy">
                <span class="topic-point__title">{{ section.title }}</span>
                <span v-if="section.lead" class="topic-point__lead">
                  <ContentCitedText :text="section.lead" :sources="sources" :notes="portalNotes" />
                </span>
              </div>
            </div>
          </template>

          <details v-if="outgoingNotes.length || incomingNotes.length" id="relacionadas" class="topic-point" name="ficha-tema">
            <summary>
              <span class="topic-point__copy">
                <span class="topic-point__title">Notas relacionadas <span class="num">({{ outgoingNotes.length + incomingNotes.length }})</span></span>
                <span class="topic-point__lead topic-point__lead--teaser">{{ relatedSummary }}</span>
              </span>
              <span class="topic-point__expand">
                <span class="topic-point__expand-label" aria-hidden="true">
                  <span class="topic-point__expand-when-closed">Ampliar</span>
                  <span class="topic-point__expand-when-open">Cerrar</span>
                </span>
                <span class="topic-point__mark" aria-hidden="true" />
              </span>
            </summary>
            <div class="topic-point__panel">
            <div v-if="outgoingNotes.length">
              <p v-if="incomingNotes.length" class="note-links__kicker">Esta nota apunta a</p>
              <ul class="note-links">
                <li v-for="item in outgoingNotes" :key="`to-${item.slug}`">
                  <span class="note-links__rel">{{ label(LINK_RELATION_LABELS, item.relation) }}</span>
                  <NuxtLink :to="`/casos/${item.slug}`">{{ item.title }}</NuxtLink>
                  <p v-if="item.note || item.hook" class="note-links__why">{{ item.note || stripMarkedText(item.hook) }}</p>
                </li>
              </ul>
            </div>
            <div v-if="incomingNotes.length">
              <p class="note-links__kicker">Aparece también en</p>
              <ul class="note-links">
                <li v-for="item in incomingNotes" :key="`from-${item.slug}`">
                  <span class="note-links__rel">{{ label(LINK_RELATION_LABELS, item.relation) }}</span>
                  <NuxtLink :to="`/casos/${item.slug}`">{{ item.title }}</NuxtLink>
                  <p v-if="item.note || item.hook" class="note-links__why">{{ item.note || stripMarkedText(item.hook) }}</p>
                </li>
              </ul>
            </div>
            </div>
          </details>

          <details v-if="caseData.sources?.length" id="fuentes" class="topic-point topic-point--wide" name="ficha-tema">
            <summary>
              <span class="topic-point__copy">
                <span class="topic-point__title">Fuentes <span class="num">({{ caseData.sources.length }})</span></span>
                <span class="topic-point__lead topic-point__lead--teaser">{{ sourcesLead }}</span>
              </span>
              <span class="topic-point__expand">
                <span class="topic-point__expand-label" aria-hidden="true">
                  <span class="topic-point__expand-when-closed">Ampliar</span>
                  <span class="topic-point__expand-when-open">Cerrar</span>
                </span>
                <span class="topic-point__mark" aria-hidden="true" />
              </span>
            </summary>
            <div class="topic-point__panel source-panel">
              <nav v-if="originChips.length" class="source-panel__origins" aria-label="Fuentes oficiales destacadas">
                <p class="source-panel__kicker">Ir al origen</p>
                <ul class="source-origin-grid">
                  <li v-for="chip in originChips" :key="chip.source.url">
                    <a
                      class="source-origin-card"
                      :href="chip.source.url"
                      rel="noopener noreferrer"
                      target="_blank"
                      :title="chip.source.title"
                      @click="trackSourceClick(slug)"
                    >
                      <span class="source-origin-card__n" aria-hidden="true">{{ chip.n }}</span>
                      <span class="source-origin-card__copy">
                        <span v-if="splitSourceTitle(chip.source.title).publisher" class="source-origin-card__pub">
                          {{ splitSourceTitle(chip.source.title).publisher }}
                        </span>
                        <span class="source-origin-card__headline">{{ splitSourceTitle(chip.source.title).headline }}</span>
                      </span>
                    </a>
                  </li>
                </ul>
              </nav>
              <ul class="source-list">
                <li v-for="(source, index) in caseData.sources" :key="`${source.url}-${index}`" class="source-card">
                  <span class="source-card__n" aria-hidden="true">{{ index + 1 }}</span>
                  <div class="source-card__body">
                    <p v-if="splitSourceTitle(source.title).publisher" class="source-card__publisher">
                      {{ splitSourceTitle(source.title).publisher }}
                    </p>
                    <a
                      class="source-card__title"
                      :href="source.url"
                      rel="noopener noreferrer"
                      target="_blank"
                      @click="trackSourceClick(slug)"
                    >
                      {{ splitSourceTitle(source.title).headline }}
                    </a>
                    <p class="source-card__meta">
                      <span v-if="sourceHost(source.url)" class="source-card__host">{{ sourceHost(source.url) }}</span>
                      <time v-if="source.published_on" :datetime="source.published_on">{{ formatDate(source.published_on) }}</time>
                      <span v-if="source.is_primary" class="source-list__tag">Principal</span>
                      <span v-if="SOURCE_KIND_LABELS[source.kind]" class="source-list__tag source-list__tag--kind">{{ SOURCE_KIND_LABELS[source.kind] }}</span>
                    </p>
                  </div>
                </li>
              </ul>
            </div>
          </details>
        </div>

        <footer class="topic-foot">
          <p>
            <template v-if="caseData.content_version">Versión <span class="num">{{ caseData.content_version }}</span></template>
            <template v-if="formatDate(caseData.published_at)"><span aria-hidden="true"> · </span>Publicado <time :datetime="caseData.published_at ?? undefined">{{ formatDate(caseData.published_at) }}</time></template>
          </p>
          <p>
            <NuxtLink to="/metodologia">Cómo revisamos un tema</NuxtLink>
            <NuxtLink to="/correcciones">Reportar un error</NuxtLink>
          </p>
        </footer>
        </div>
      </div>

      <aside
        id="panel-redes"
        role="tabpanel"
        aria-labelledby="tab-redes"
        class="topic-pane topic-social"
        :class="{ 'is-active': pane === 'redes', 'topic-social--hero-layout': topicCover }"
      >
        <div class="topic-social__stack">
          <h2 class="topic-social__title">En redes</h2>
          <ContentSocialReel :reactions="caseData.reactions ?? []" />
        </div>
      </aside>
    </div>

    <Teleport to="body">
      <div
        v-if="topicCover"
        class="topic-site-hero"
        role="img"
        :aria-label="`Ilustración editorial: ${caseData.title}`"
        :style="{ backgroundImage: `url(${topicCover})` }"
      />
    </Teleport>
  </article>
</template>

<style scoped>
.topic {
  --topic-social-width: clamp(280px, 26vw, 400px);
  --topic-column-bleed: 12px;
  width: 100%;
  max-width: 100%;
  min-width: 0;
  margin: 0;
  padding: 8px 16px 32px;
  overflow-x: clip;
}

.topic--has-cover {
  padding-top: 0;
  padding-right: 16px;
  padding-left: 0;
}

.topic-column {
  position: relative;
  flex: 1 1 0;
  min-width: 0;
  padding-top: 8px;
}

.topic-column--has-cover {
  padding-top: 0;
}

.topic-column--has-cover .crumbs,
.topic-column--has-cover .topic-tabs,
.topic-column--has-cover .topic-read {
  position: relative;
  z-index: 1;
}

.topic-column--has-cover .crumbs {
  padding-left: var(--topic-column-bleed);
}

.topic-column--has-cover .topic-tabs {
  padding-left: var(--topic-column-bleed);
}

.topic-column--has-cover .topic-read {
  padding-left: var(--topic-column-bleed);
  padding-right: 16px;
}

.crumbs--on-cover a {
  text-shadow: 0 1px 10px rgb(255 255 255 / 0.85);
}

.crumbs {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 24px;
  font-size: 14px;
  color: var(--faint);
}

.crumbs a {
  color: var(--muted);
  text-decoration: none;
}

.crumbs a:hover {
  color: var(--primary);
  text-decoration: underline;
}

.topic-tabs {
  display: none;
}

.topic-split {
  display: flex;
  flex-wrap: nowrap;
  align-items: flex-start;
  gap: 28px;
  min-width: 0;
}

.topic-read {
  min-width: 0;
}

.topic-head__row {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 10px 12px;
}

.topic-head__signals {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin: 0;
}

.topic-follow {
  margin-left: auto;
  padding: 6px 12px;
  border: 1px solid var(--border);
  border-radius: 999px;
  background: var(--surface);
  color: var(--ink);
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
}

.topic-follow:hover:not(:disabled) {
  border-color: color-mix(in srgb, var(--primary) 40%, var(--border));
  color: var(--primary-hover);
}

.topic-follow:disabled {
  opacity: 0.6;
  cursor: wait;
}

.topic-head--on-cover h1 {
  text-shadow:
    0 0 2px rgb(255 255 255 / 1),
    0 1px 4px rgb(255 255 255 / 0.95),
    0 0 18px rgb(255 255 255 / 0.82),
    0 0 36px rgb(255 255 255 / 0.52),
    0 3px 16px rgb(18 48 45 / 0.16);
}

.topic-head--on-cover .topic-hook {
  display: inline-block;
  width: fit-content;
  max-width: min(52ch, 100%);
  margin: 14px 0 0;
  padding: 12px 14px 13px;
  border-radius: 12px;
  background: rgb(255 255 255 / 0.84);
  backdrop-filter: blur(8px);
  box-shadow: 0 1px 0 rgb(18 48 45 / 7%);
  color: var(--ink);
}

.topic-head h1 {
  margin: 14px 0 0;
  font-family: var(--font-serif);
  font-size: clamp(36px, 3.6vw, 52px);
  font-weight: 600;
  line-height: 1.05;
  letter-spacing: -0.03em;
  overflow-wrap: break-word;
}

.timeline {
  margin: 0;
  padding: 0;
  list-style: none;
}

.timeline li {
  display: grid;
  grid-template-columns: 7.5rem minmax(0, 1fr);
  gap: 12px;
  padding: 10px 0;
  border-top: 1px solid var(--hairline);
  font-size: 15px;
}

.timeline li:first-child {
  border-top: 0;
}

.timeline time {
  font-size: 13px;
  font-weight: 600;
  color: var(--faint);
}

.timeline__body {
  display: grid;
  gap: 4px;
  min-width: 0;
}

.timeline__body > p {
  margin: 0;
}

.timeline__direction {
  display: inline-flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 6px 8px;
  font-size: 13px;
  font-weight: 650;
}

.timeline__item--riesgo .timeline__direction {
  color: var(--loss);
}

.timeline__item--reversion .timeline__direction {
  color: var(--gain);
}

.timeline__item--incierto .timeline__direction {
  color: var(--muted);
}

.timeline__item--developing .timeline__direction--developing {
  margin-bottom: 2px;
  color: var(--muted);
}

.timeline--developing time {
  font-variant-numeric: tabular-nums;
  white-space: nowrap;
}

.timeline__kind {
  color: var(--faint);
  font-weight: 600;
}

.timeline__note {
  color: var(--muted);
}

.timeline__source {
  font-size: 13px;
  font-weight: 650;
}

.topic-point__empty {
  margin: 12px 0 0;
  font-size: 15px;
  line-height: 1.5;
  color: var(--muted);
}

.claims {
  margin: 0;
  padding: 0 0 8px;
  list-style: none;
}

.claim {
  display: grid;
  gap: 8px;
  padding: 12px 0 16px;
  border-top: 1px solid var(--hairline);
}

.claim:first-child {
  border-top: 0;
  padding-top: 0;
}

.claim__verdict {
  margin: 0;
  font-size: 13px;
  font-weight: 650;
}

.claim--se_sostiene .claim__verdict {
  color: var(--gain);
}

.claim--no_se_sostiene .claim__verdict {
  color: var(--loss);
}

.claim--a_medias .claim__verdict {
  color: var(--primary-hover);
}

.claim--sin_respaldo .claim__verdict {
  color: var(--muted);
}

.claim__said {
  margin: 0;
  padding-left: 12px;
  border-left: 2px solid var(--border);
  font-size: 16px;
  line-height: 1.45;
}

.claim__where,
.claim__source {
  margin: 0;
  font-size: 13px;
  font-weight: 650;
  color: var(--faint);
}

.claim__answer {
  font-size: 16px;
  line-height: 1.55;
}

.claim__source {
  color: var(--primary-hover);
}

.topic-point--wide .topic-point__panel {
  max-width: none;
}

.source-panel__origins {
  margin-bottom: 18px;
  padding-bottom: 18px;
  border-bottom: 1px solid var(--hairline);
}

.source-panel__kicker {
  margin: 0 0 10px;
  font-size: 12px;
  font-weight: 650;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  color: var(--faint);
}

.source-origin-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(min(100%, 300px), 1fr));
  gap: 10px;
  margin: 0;
  padding: 0;
  list-style: none;
}

.source-origin-card {
  display: grid;
  grid-template-columns: auto minmax(0, 1fr);
  gap: 12px;
  align-items: start;
  min-height: 100%;
  padding: 12px 14px;
  border: 1px solid color-mix(in srgb, var(--primary) 22%, var(--border));
  border-radius: 10px;
  background: color-mix(in srgb, var(--primary-soft) 55%, var(--surface));
  color: var(--ink);
  text-decoration: none;
}

.source-origin-card:hover,
.source-origin-card:focus-visible {
  border-color: color-mix(in srgb, var(--primary) 40%, var(--border));
  color: var(--primary-hover);
}

.source-origin-card__n {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 1.65rem;
  height: 1.65rem;
  border-radius: 999px;
  background: color-mix(in srgb, var(--primary) 16%, transparent);
  font-size: 12px;
  font-weight: 750;
  font-variant-numeric: tabular-nums;
  color: var(--primary-hover);
}

.source-origin-card__copy {
  display: grid;
  gap: 4px;
  min-width: 0;
}

.source-origin-card__pub {
  font-size: 12px;
  font-weight: 650;
  letter-spacing: 0.02em;
  text-transform: uppercase;
  color: var(--primary-hover);
}

.source-origin-card__headline {
  font-size: 14px;
  font-weight: 550;
  line-height: 1.45;
}

.source-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(min(100%, 420px), 1fr));
  gap: 12px;
  margin: 0;
  padding: 0;
  list-style: none;
}

.source-card {
  display: grid;
  grid-template-columns: 2.25rem minmax(0, 1fr);
  gap: 12px 14px;
  align-items: start;
  padding: 14px 16px;
  border: 1px solid var(--hairline);
  border-radius: 10px;
  background: var(--surface);
}

.topic-point--wide[open] .source-card {
  background: color-mix(in srgb, var(--surface) 88%, var(--primary-soft));
}

.source-card__n {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 2.25rem;
  height: 2.25rem;
  border-radius: 8px;
  background: color-mix(in srgb, var(--primary) 10%, var(--canvas));
  font-size: 14px;
  font-weight: 750;
  font-variant-numeric: tabular-nums;
  color: var(--primary-hover);
}

.source-card__body {
  min-width: 0;
}

.source-card__publisher {
  margin: 0 0 4px;
  font-size: 12px;
  font-weight: 650;
  letter-spacing: 0.02em;
  text-transform: uppercase;
  color: var(--muted);
}

.source-card__title {
  display: block;
  font-size: 15px;
  font-weight: 600;
  line-height: 1.45;
  letter-spacing: -0.015em;
  color: var(--ink);
  text-decoration: none;
}

.source-card__title:hover,
.source-card__title:focus-visible {
  color: var(--primary-hover);
  text-decoration: underline;
}

.source-card__meta {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 6px 10px;
  margin: 10px 0 0;
  font-size: 12px;
  line-height: 1.4;
  color: var(--faint);
}

.source-card__host {
  font-weight: 550;
  color: var(--muted);
}

.source-list__tag {
  padding: 2px 8px;
  border-radius: 999px;
  background: var(--primary-soft);
  color: var(--primary-hover);
  font-size: 11px;
  font-weight: 650;
  white-space: nowrap;
}

.source-list__tag--kind {
  background: var(--surface-2, #eceff1);
  color: var(--muted);
}

.note-links {
  margin: 0;
  padding: 0 0 8px;
  list-style: none;
}

.note-links li {
  display: grid;
  gap: 4px;
  padding: 12px 0 14px;
  border-top: 1px solid var(--hairline);
}

.note-links li:first-child {
  border-top: 0;
  padding-top: 0;
}

.note-links a {
  font-weight: 650;
  color: var(--ink);
  text-decoration: none;
}

.note-links a:hover,
.note-links a:focus-visible {
  color: var(--primary-hover);
}

.note-links__kicker {
  margin: 4px 0 8px;
  font-size: 12px;
  font-weight: 650;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  color: var(--faint);
}

.note-links__rel {
  font-size: 12px;
  font-weight: 650;
  color: var(--primary-hover);
}

.note-links__why {
  margin: 0;
  font-size: 14px;
  line-height: 1.5;
  color: var(--muted);
}

.topic-hook {
  max-width: 52ch;
  margin: 16px 0 0;
  font-size: 20px;
  line-height: 1.5;
}

.record {
  display: flex;
  flex-wrap: nowrap;
  align-items: center;
  gap: 0;
  width: 100%;
  max-width: 100%;
  min-width: 0;
  margin: 14px 0 0;
  padding: 5px 0;
  border-top: 1px solid var(--hairline);
  border-bottom: 1px solid var(--hairline);
  overflow-x: auto;
  overscroll-behavior-x: contain;
  scrollbar-width: thin;
}

.record > div {
  display: flex;
  align-items: center;
  gap: 5px;
  flex: 0 0 auto;
  padding: 0 9px;
}

.record__profiles {
  gap: 10px;
  padding-right: 12px;
}

.record__avatars-wrap {
  position: relative;
}

.record > div + div {
  border-left: 1px solid var(--hairline);
}

.record > div:first-child {
  padding-left: 0;
}

.record > div:last-child {
  padding-right: 0;
}

.record dt {
  display: flex;
  margin: 0;
  color: var(--faint);
}

.record dt svg {
  display: block;
}

.record dd {
  margin: 0;
  font-size: 11px;
  font-weight: 650;
  color: var(--ink);
  letter-spacing: -0.015em;
  white-space: nowrap;
}

.record__avatars {
  display: flex;
  align-items: center;
  margin: 0;
  padding: 0;
}

.record__avatar {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  margin-left: -10px;
  border: 2px solid var(--surface);
  border-radius: 50%;
  overflow: hidden;
  background: var(--primary-soft);
  color: var(--primary-hover);
  text-decoration: none;
  flex-shrink: 0;
  box-shadow: 0 0 0 1px rgb(0 0 0 / 6%);
}

.record__avatar:first-child {
  margin-left: 0;
}

.record__avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.record__avatar-initials {
  font-size: 12px;
  font-weight: 750;
  letter-spacing: -0.03em;
  line-height: 1;
  user-select: none;
}

.record__avatar:hover,
.record__avatar:focus-visible {
  z-index: 10 !important;
  box-shadow: 0 0 0 2px var(--primary-soft);
}

.record__avatar--more {
  font-size: 11px;
  font-weight: 700;
  color: var(--muted);
  background: var(--surface-2, #eceff1);
  cursor: pointer;
  padding: 0;
  font-family: inherit;
  appearance: none;
}

.record__avatar--more:hover,
.record__avatar--more:focus-visible {
  color: var(--primary-hover);
  background: color-mix(in srgb, var(--primary-soft) 70%, var(--surface-2, #eceff1));
}

.record__avatar--more[aria-expanded='true'] {
  color: var(--primary-hover);
  box-shadow: 0 0 0 2px var(--primary-soft);
}

.record__profiles-popover {
  position: fixed;
  z-index: 200;
  width: min(20rem, calc(100vw - 2rem));
  max-height: min(18rem, 50vh);
  overflow: auto;
  padding: 10px 0;
  border: 1px solid var(--border);
  border-radius: 12px;
  background: var(--surface);
  box-shadow: 0 10px 32px rgb(15 23 42 / 12%);
}

.record__profiles-popover-title {
  margin: 0;
  padding: 0 14px 8px;
  font-size: 11px;
  font-weight: 650;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  color: var(--faint);
}

.record__profiles-popover-list {
  margin: 0;
  padding: 0;
  list-style: none;
}

.record__profiles-popover-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 14px;
  color: var(--ink);
  text-decoration: none;
}

.record__profiles-popover-item:hover,
.record__profiles-popover-item:focus-visible {
  background: color-mix(in srgb, var(--primary-soft) 45%, transparent);
  color: var(--primary-hover);
}

.record__avatar--in-popover {
  width: 32px;
  height: 32px;
  margin-left: 0;
  flex-shrink: 0;
}

.record__avatar--in-popover .record__avatar-initials {
  font-size: 11px;
}

.record__profiles-popover-name {
  font-size: 13px;
  font-weight: 600;
  line-height: 1.3;
  letter-spacing: -0.02em;
}

.record__profile-name {
  font-size: 13px;
  font-weight: 650;
  letter-spacing: -0.02em;
  color: var(--ink);
  white-space: nowrap;
  text-decoration: none;
}

.record__profile-name:hover,
.record__profile-name:focus-visible {
  color: var(--primary-hover);
  text-decoration: underline;
}

.topic-facts {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px 32px;
  margin-top: 28px;
}

.topic-facts h2,
.topic-compare > h2 {
  margin: 0 0 8px;
  font-size: 18px;
  font-weight: 650;
  letter-spacing: -0.01em;
}

.topic-facts p {
  margin: 0;
  font-size: 16px;
  line-height: 1.55;
}

.topic-compare {
  margin-top: 28px;
}

.compare {
  display: grid;
  grid-template-columns: 1fr 1fr;
  border: 1px solid var(--border);
  border-radius: var(--radius);
  overflow: hidden;
}

.compare__col {
  padding: 16px 18px 18px;
  background: var(--canvas);
  color: var(--muted);
}

.compare__col--after {
  border-left: 1px solid var(--border);
}

.compare__col--episode {
  background: color-mix(in srgb, var(--accent-soft, #e8f1ff) 78%, var(--surface));
}

.compare__col--episode h3 {
  color: var(--accent, #315f9f);
}

.compare h3 {
  margin: 0 0 8px;
  font-size: 13px;
  font-weight: 650;
  color: var(--muted);
}

.compare__col--gain {
  background: color-mix(in srgb, var(--gain-soft) 82%, var(--surface));
}

.compare__col--gain h3 {
  color: var(--gain);
}

.compare__col--loss {
  background: color-mix(in srgb, var(--loss-soft) 82%, var(--surface));
}

.compare__col--loss h3 {
  color: var(--loss);
}

.compare p {
  margin: 0;
  font-size: 16px;
  line-height: 1.5;
}

.topic-cites {
  margin-top: 24px;
}

.topic-cites__label {
  margin: 0 0 8px;
  font-size: 13px;
  font-weight: 650;
  color: var(--muted);
}

.topic-cites__list {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.cite-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  max-width: 100%;
  padding: 4px 10px 4px 5px;
  border: 1px solid var(--border);
  border-radius: 999px;
  background: var(--surface);
  color: var(--muted);
  font-size: 12.5px;
  font-weight: 600;
  line-height: 1.3;
  text-decoration: none;
}

.cite-chip:hover,
.cite-chip:focus-visible {
  border-color: color-mix(in srgb, var(--primary) 45%, var(--border));
  color: var(--primary-hover);
}

.cite-chip--doc {
  border-color: transparent;
  background: var(--primary-soft);
  color: var(--primary-hover);
}

.cite-chip__n {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 1.15rem;
  height: 1.15rem;
  border-radius: 999px;
  background: color-mix(in srgb, var(--ink) 8%, transparent);
  font-size: 11px;
  font-weight: 700;
  font-variant-numeric: tabular-nums;
}

.cite-chip--doc .cite-chip__n {
  background: color-mix(in srgb, var(--primary) 18%, transparent);
}

.topic-points {
  margin-top: 28px;
  padding-top: 4px;
  border-top: 1px solid var(--hairline);
}

.topic-points__heading {
  margin: 16px 0 12px;
  font-size: 18px;
  font-weight: 650;
  letter-spacing: -0.02em;
  color: var(--ink);
}

.topic-point {
  border: 1px solid var(--hairline);
  border-radius: 12px;
  margin-bottom: 8px;
  background: var(--surface);
}

.topic-point--static {
  padding: 12px 14px;
}

.topic-point:last-child {
  margin-bottom: 0;
}

.topic-point summary {
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto;
  gap: 12px 16px;
  align-items: start;
  min-height: 44px;
  padding: 12px 14px;
  cursor: pointer;
  list-style: none;
}

.topic-point summary::-webkit-details-marker {
  display: none;
}

.topic-point summary:hover .topic-point__title,
.topic-point summary:focus-visible .topic-point__title {
  color: var(--primary-hover);
}

.topic-point[open] {
  border-color: color-mix(in srgb, var(--primary) 22%, var(--border));
  background: color-mix(in srgb, var(--primary-soft) 35%, var(--surface));
}

.topic-point[open] summary {
  padding-bottom: 10px;
  border-bottom: 1px solid var(--hairline);
}

.topic-point__copy {
  display: grid;
  gap: 6px;
  min-width: 0;
}

.topic-point__title {
  font-size: 16px;
  font-weight: 700;
  line-height: 1.25;
  letter-spacing: -0.02em;
  color: var(--ink);
}

.topic-point__lead {
  margin: 0;
  font-size: 14px;
  line-height: 1.5;
  font-weight: 450;
  color: var(--muted);
}

.topic-point:not([open]) .topic-point__lead--teaser {
  display: -webkit-box;
  overflow: hidden;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
}

.topic-point[open] .topic-point__lead--teaser {
  display: none;
}

.topic-point:not([open]) .topic-point__lead:not(.topic-point__lead--teaser) {
  display: -webkit-box;
  overflow: hidden;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 4;
}

.topic-point[open] .topic-point__lead:not(.topic-point__lead--teaser) {
  display: block;
  overflow: visible;
  -webkit-line-clamp: unset;
  color: var(--ink);
}

.topic-point__expand {
  display: inline-flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  flex-shrink: 0;
  padding-top: 2px;
}

.topic-point__expand-label {
  font-size: 11px;
  font-weight: 650;
  letter-spacing: 0.02em;
  text-transform: uppercase;
  color: var(--primary);
}

.topic-point__expand-when-open {
  display: none;
}

.topic-point[open] .topic-point__expand-when-closed {
  display: none;
}

.topic-point[open] .topic-point__expand-when-open {
  display: inline;
}

.topic-point__mark {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border: 1px solid color-mix(in srgb, var(--primary) 35%, var(--border));
  border-radius: 999px;
  background: var(--canvas);
}

.topic-point__mark::before {
  content: '';
  width: 7px;
  height: 7px;
  border-right: 1.75px solid var(--primary);
  border-bottom: 1.75px solid var(--primary);
  transform: translateY(-1px) rotate(45deg);
  transition: transform 0.16s ease;
}

.topic-point[open] .topic-point__mark::before {
  transform: translateY(1px) rotate(225deg);
}

.topic-point__panel {
  padding: 0 14px 14px;
  max-width: var(--measure);
}

.topic-point--wide .topic-point__panel.source-panel {
  padding: 14px 16px 18px;
}

.topic-point__lead-full {
  margin: 0 0 12px;
  padding: 12px 0 0;
  font-size: 15px;
  line-height: 1.55;
  color: var(--ink);
  border-top: 0;
}

.topic-point--has-more:not([open]) .topic-point__lead-full {
  display: none;
}

.topic-point__body {
  padding: 0;
}

.topic-point__body p,
.topic-point .timeline,
.topic-point .source-list,
.topic-point__panel .claims {
  margin: 0 0 12px;
  font-size: 15px;
  line-height: 1.6;
  color: var(--ink);
}

.topic-point--wide .source-list {
  margin-bottom: 0;
  font-size: inherit;
}

.topic-point__body p:last-child,
.topic-point .source-list:last-child {
  margin-bottom: 0;
}

.topic-foot {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  gap: 8px 24px;
  margin-top: 40px;
  padding-top: 16px;
  border-top: 1px solid var(--hairline);
  font-size: 14px;
  color: var(--faint);
}

.topic-foot p {
  display: flex;
  flex-wrap: wrap;
  gap: 4px 20px;
  margin: 0;
}

.topic-foot a {
  font-weight: 600;
}

.topic-social {
  flex: 0 0 var(--topic-social-width);
  width: var(--topic-social-width);
  min-width: 280px;
}

.topic-social__stack {
  display: flex;
  flex-direction: column;
  min-height: 0;
  width: 100%;
}

.topic-social__title {
  margin: 0 0 10px;
  font-size: 18px;
  font-weight: 650;
}

.topic-tabs button {
  min-height: 44px;
  padding: 0 14px;
  border: 0;
  border-bottom: 2px solid transparent;
  margin-bottom: -1px;
  background: transparent;
  color: var(--muted);
  font-weight: 650;
  cursor: pointer;
}

.topic-tabs button.is-selected {
  color: var(--primary);
  border-bottom-color: var(--primary);
}

.topic-missing {
  max-width: 620px;
  margin: 0 auto;
  padding: 56px 24px;
}

.topic-missing h1 {
  margin: 0 0 10px;
  font-size: 32px;
  letter-spacing: -0.03em;
}

.topic-missing p {
  margin: 0;
  font-size: 17px;
  line-height: 1.6;
  color: var(--muted);
}

.topic-missing__actions {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin-top: 24px !important;
}

@media (min-width: 801px) {
  .topic-column {
    margin-right: calc(var(--topic-social-width) + 28px);
  }

  .topic-head {
    display: grid;
    grid-template-columns: minmax(0, 1.12fr) minmax(14rem, 0.88fr);
    column-gap: clamp(20px, 2.8vw, 40px);
    align-items: start;
  }

  .topic-head__row {
    grid-column: 1 / -1;
  }

  .topic-head h1 {
    grid-column: 1;
    margin-top: 12px;
  }

  .topic-hook {
    grid-column: 2;
    max-width: none;
    margin-top: 12px;
    padding-top: 0.2em;
    font-size: clamp(17px, 1.25vw, 20px);
    line-height: 1.48;
  }

  .topic-head:not(:has(.topic-hook)) h1 {
    grid-column: 1 / -1;
  }

  .topic-social {
    position: fixed;
    z-index: 25;
    top: 10px;
    right: 10px;
    bottom: 10px;
    display: flex;
    flex: none;
    flex-direction: column;
    justify-content: flex-start;
    align-items: stretch;
    width: var(--topic-social-width);
    min-width: 280px;
    max-height: none;
    overflow: hidden;
    pointer-events: none;
  }

  .topic-social__stack {
    display: flex;
    flex: 1 1 auto;
    flex-direction: column;
    height: 100%;
    max-height: 100%;
    min-height: 0;
    overflow: hidden;
    pointer-events: auto;
  }

  .topic-social__title {
    flex-shrink: 0;
    margin-bottom: 8px;
  }

  .topic-social :deep(.social) {
    display: flex;
    flex: 1 1 auto;
    flex-direction: column;
    min-height: 0;
    overflow: hidden;
  }
}

@media (max-width: 1100px) {
  .topic-facts {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 800px) {
  .topic {
    padding: 0 8px 24px;
  }

  .topic--has-cover {
    padding-left: 0;
    padding-right: 8px;
    --topic-column-bleed: 8px;
  }

  .crumbs {
    margin-bottom: 12px;
  }

  .topic-tabs {
    display: flex;
    gap: 4px;
    margin-bottom: 20px;
    border-bottom: 1px solid var(--hairline);
  }

  .topic-split {
    display: block;
    min-width: 0;
  }

  .record {
    flex-wrap: wrap;
    gap: 6px 0;
    overflow: visible;
  }

  .record > div {
    padding: 2px 10px 2px 0;
  }

  .record > div + div {
    border-left: 0;
  }

  .record > div:not(:last-child) {
    border-right: 1px solid var(--hairline);
    margin-right: 9px;
    padding-right: 9px;
  }

  .record dd {
    white-space: normal;
  }

  .topic-pane:not(.is-active) {
    display: none;
  }

  .topic-column {
    margin-right: 0;
  }

  .topic-social {
    position: static;
    flex: none;
    width: 100%;
    max-width: 100%;
    min-width: 0;
    overflow-x: clip;
    pointer-events: auto;
  }

  .topic-social__stack {
    max-height: none;
  }

  .topic-hook {
    font-size: 18px;
  }

  .timeline li {
    grid-template-columns: 1fr;
    gap: 4px;
  }

  .compare {
    grid-template-columns: 1fr;
  }

  .compare__col--after {
    border-left: 0;
    border-top: 1px solid var(--hairline);
  }
}
</style>
