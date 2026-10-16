<script setup lang="ts">
import type { PublicCaseListItem } from '~/composables/usePublicApi'
import {
  comparisonMarkLabel,
  effectMarkLabel,
  evidenceMarkLabel,
  hasCardMetaMarks,
  pageviewsMarkLabel,
  reactionsMarkLabel,
  repliesMarkLabel,
  sourcesMarkLabel,
  tipoMarkLabel,
  tracksDecisionOnCard,
  useCatalogTopicMarkTip,
} from '~/composables/useCatalogTopicMarks'
import { CHANGE_TYPE_LABELS, EFFECT_LABELS, EVIDENCE_LABELS, formatDate, label } from '~/utils/topicLabels'
import { formatCompactCount } from '~/utils/formatCount'
import { profileInitials } from '~/utils/profileDisplay'
import { topicCoverSrc } from '~/utils/topicCover'

const props = withDefaults(
  defineProps<{
    topic: PublicCaseListItem
    /** false en home mientras se ajusta diseño (sin overlay ni navegación). */
    linked?: boolean
  }>(),
  { linked: true },
)

const coverSrc = computed(() => topicCoverSrc(props.topic.cover_image_url))
const cardStyle = computed(() =>
  coverSrc.value ? { backgroundImage: `url(${coverSrc.value})` } : undefined,
)

const CARD_PROFILE_CAP = 3
const cardProfiles = computed(() => props.topic.profiles ?? [])
const visibleCardProfiles = computed(() => cardProfiles.value.slice(0, CARD_PROFILE_CAP))
const cardProfileOverflow = computed(() => Math.max(0, cardProfiles.value.length - CARD_PROFILE_CAP))
const showCardFoot = computed(
  () => tracksDecisionOnCard(props.topic) || cardProfiles.value.length > 0,
)

const { markTip, showMarkTip, hideMarkTip } = useCatalogTopicMarkTip()

function openTopic(slug: string) {
  return navigateTo(`/casos/${slug}`)
}

function openReplies(slug: string) {
  return navigateTo(`/casos/${slug}#respuestas`)
}
</script>

<template>
  <article
    class="card"
    :class="{ 'card--has-cover': coverSrc, 'card--static': !linked }"
    :data-area="topic.category?.slug ?? 'otros'"
    :style="cardStyle"
    :aria-label="coverSrc ? `Ilustración: ${topic.title}` : undefined"
  >
    <header class="card__top">
      <div class="card__aside">
        <p class="card__cat">
          <ContentCategoryIcon :slug="topic.category?.slug" />
          {{ topic.category?.title ?? 'Interés público' }}
        </p>
        <ul
          class="card-marks card-marks--corner"
          aria-label="Contenido en la ficha"
        >
          <li
            class="card-mark card-mark--pageviews"
            tabindex="0"
            :aria-label="pageviewsMarkLabel(topic)"
            @mouseenter="showMarkTip($event, pageviewsMarkLabel(topic))"
            @mouseleave="hideMarkTip"
            @focus="showMarkTip($event, pageviewsMarkLabel(topic))"
            @blur="hideMarkTip"
            @click="linked && openTopic(topic.slug)"
          >
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
              <path d="M2.5 12.5s3.5-6.5 9.5-6.5 9.5 6.5 9.5 6.5-3.5 6.5-9.5 6.5-9.5-6.5-9.5-6.5Z" />
              <circle cx="12" cy="12.5" r="2.25" />
            </svg>
            <span class="card-mark__n">{{ formatCompactCount(topic.pageviews ?? 0) }}</span>
          </li>
          <li
            v-if="topic.sources_count > 0"
            class="card-mark card-mark--sources"
            tabindex="0"
            :aria-label="sourcesMarkLabel(topic)"
            @mouseenter="showMarkTip($event, sourcesMarkLabel(topic))"
            @mouseleave="hideMarkTip"
            @focus="showMarkTip($event, sourcesMarkLabel(topic))"
            @blur="hideMarkTip"
            @click="linked && openTopic(topic.slug)"
          >
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
              <path d="M7 4h7l3 3v13H7V4Z" />
              <path d="M14 4v4h4M9 12h6M9 16h6" />
            </svg>
            <span class="card-mark__n">{{ topic.sources_count }}</span>
          </li>
          <li
            v-if="topic.reactions_count > 0"
            class="card-mark card-mark--reactions"
            tabindex="0"
            :aria-label="reactionsMarkLabel(topic)"
            @mouseenter="showMarkTip($event, reactionsMarkLabel(topic))"
            @mouseleave="hideMarkTip"
            @focus="showMarkTip($event, reactionsMarkLabel(topic))"
            @blur="hideMarkTip"
            @click="linked && openTopic(topic.slug)"
          >
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
              <path d="M7 10a4 4 0 1 0 8 0" />
              <path d="M5.5 18.5c.6-2.5 2.8-4 6.5-4s5.9 1.5 6.5 4" />
            </svg>
            <span class="card-mark__n">{{ topic.reactions_count }}</span>
          </li>
          <li
            v-if="(topic.claims_count ?? 0) > 0"
            class="card-mark card-mark--claims"
            tabindex="0"
            :aria-label="repliesMarkLabel(topic)"
            @mouseenter="showMarkTip($event, repliesMarkLabel(topic))"
            @mouseleave="hideMarkTip"
            @focus="showMarkTip($event, repliesMarkLabel(topic))"
            @blur="hideMarkTip"
            @click.stop="linked && openReplies(topic.slug)"
          >
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
              <path d="M5 6.5h14v8.5H9.5L5 19v-4H5V6.5Z" />
              <path d="M8.5 10.5h7" />
            </svg>
            <span class="card-mark__n">{{ topic.claims_count }}</span>
          </li>
          <li
            v-if="topic.has_comparison"
            class="card-mark card-mark--icon card-mark--comparison"
            tabindex="0"
            :aria-label="comparisonMarkLabel"
            @mouseenter="showMarkTip($event, comparisonMarkLabel)"
            @mouseleave="hideMarkTip"
            @focus="showMarkTip($event, comparisonMarkLabel)"
            @blur="hideMarkTip"
            @click="linked && openTopic(topic.slug)"
          >
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
              <path d="M8 7H4v10h4M16 7h4v10h-4M11 5v14" />
            </svg>
          </li>
        </ul>
      </div>
      <h3>
        <NuxtLink
          v-if="linked"
          :to="`/casos/${topic.slug}`"
          class="card__headline"
          :title="topic.title"
        >
          {{ topic.title }}
        </NuxtLink>
        <span v-else class="card__headline card__headline--text">{{ topic.title }}</span>
      </h3>
      <time
        v-if="formatDate(topic.next_review_at)"
        class="card__review"
        :datetime="topic.next_review_at ?? undefined"
      >
        Revisión {{ formatDate(topic.next_review_at) }}
      </time>
    </header>
    <div class="card__mid">
      <p v-if="topic.hook" class="card__hook">
        <ContentCitedText :text="topic.hook" :sources="[]" />
      </p>
      <ul v-if="hasCardMetaMarks(topic)" class="card-marks card-marks--meta" aria-label="Señales editoriales">
        <li
          v-for="tipo in topic.change_types"
          :key="`tipo-${topic.slug}-${tipo}`"
          class="card-mark card-mark--meta"
          tabindex="0"
          :aria-label="tipoMarkLabel(tipo)"
          @mouseenter="showMarkTip($event, tipoMarkLabel(tipo))"
          @mouseleave="hideMarkTip"
          @focus="showMarkTip($event, tipoMarkLabel(tipo))"
          @blur="hideMarkTip"
          @click="linked && openTopic(topic.slug)"
        >
          {{ label(CHANGE_TYPE_LABELS, tipo) }}
        </li>
        <li
          v-if="topic.evidence_status && topic.evidence_status !== 'unknown'"
          class="card-mark card-mark--meta"
          tabindex="0"
          :aria-label="evidenceMarkLabel(topic)"
          @mouseenter="showMarkTip($event, evidenceMarkLabel(topic))"
          @mouseleave="hideMarkTip"
          @focus="showMarkTip($event, evidenceMarkLabel(topic))"
          @blur="hideMarkTip"
          @click="linked && openTopic(topic.slug)"
        >
          {{ label(EVIDENCE_LABELS, topic.evidence_status) }}
        </li>
        <li
          v-if="topic.observed_effect && topic.observed_effect !== 'unknown'"
          class="card-mark card-mark--meta"
          tabindex="0"
          :aria-label="effectMarkLabel(topic)"
          @mouseenter="showMarkTip($event, effectMarkLabel(topic))"
          @mouseleave="hideMarkTip"
          @focus="showMarkTip($event, effectMarkLabel(topic))"
          @blur="hideMarkTip"
          @click="linked && openTopic(topic.slug)"
        >
          {{ label(EFFECT_LABELS, topic.observed_effect) }}
        </li>
      </ul>
    </div>
    <footer v-if="showCardFoot" class="card__foot">
      <ContentStatusBadge
        v-if="tracksDecisionOnCard(topic)"
        :estado="topic.public_status_label ?? 'por_confirmar'"
      />
      <div
        v-if="cardProfiles.length"
        class="card__profiles"
        :class="{ 'card__profiles--solo': !tracksDecisionOnCard(topic) }"
      >
        <NuxtLink
          v-for="(profile, index) in visibleCardProfiles"
          :key="profile.slug"
          :to="`/perfiles/${profile.slug}`"
          class="card__profile-avatar"
          :style="{ zIndex: visibleCardProfiles.length - index }"
          :title="profile.display_name"
          @click.stop
        >
          <img
            v-if="profile.photo_url"
            :src="profile.photo_url"
            alt=""
            width="28"
            height="28"
            loading="lazy"
          >
          <span v-else class="card__profile-initials" aria-hidden="true">{{ profileInitials(profile.display_name) }}</span>
        </NuxtLink>
        <NuxtLink
          v-if="cardProfileOverflow > 0"
          :to="`/casos/${topic.slug}`"
          class="card__profile-avatar card__profile-avatar--more"
          :title="`Ver los ${cardProfiles.length} perfiles en la ficha`"
          @click.stop
        >
          +{{ cardProfileOverflow }}
        </NuxtLink>
      </div>
    </footer>
    <Teleport to="body">
      <span
        v-if="markTip"
        class="icon-rail-tip icon-rail-tip--above"
        role="tooltip"
        :style="{ left: `${markTip.x}px`, top: `${markTip.y}px` }"
      >{{ markTip.label }}</span>
    </Teleport>
  </article>
</template>

<style scoped>
.card {
  --cat: #0f766e;
  position: relative;
  display: flex;
  flex: 1;
  flex-direction: column;
  width: 100%;
  padding: 14px 16px 14px;
  border: 1px solid var(--border);
  border-radius: 12px;
  background: var(--surface);
  overflow: hidden;
  cursor: pointer;
  transition: border-color 0.16s ease;
}

.card:hover {
  border-color: color-mix(in srgb, var(--cat) 38%, var(--border));
}

.card--static {
  cursor: default;
}

.card--static .card__headline::after {
  content: none;
}

.card--static .card__headline--text {
  text-decoration: none;
  color: var(--ink);
  cursor: text;
}

.card--has-cover {
  background-color: var(--canvas);
  background-repeat: no-repeat;
  background-position: center center;
  background-size: cover;
  border-color: color-mix(in srgb, var(--cat) 18%, var(--border));
}

.card--has-cover::before {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: inherit;
  background: linear-gradient(
    165deg,
    rgb(255 255 255 / 0.42) 0%,
    rgb(255 255 255 / 0.78) 38%,
    rgb(255 255 255 / 0.94) 72%,
    rgb(255 255 255 / 0.98) 100%
  );
  pointer-events: none;
}

.card--has-cover .card__top,
.card--has-cover .card__mid,
.card--has-cover .card__foot {
  position: relative;
  z-index: 1;
}

.card__top {
  display: flex;
  flex-direction: column;
  align-items: stretch;
  gap: 8px;
}

.card__aside {
  display: flex;
  flex-direction: row;
  align-items: flex-start;
  justify-content: space-between;
  gap: 10px;
  box-sizing: border-box;
  width: calc(100% + 32px);
  min-width: 0;
  max-width: none;
  margin: -14px -16px 0 -16px;
  pointer-events: none;
}

.card__aside > * {
  pointer-events: auto;
}

.card[data-area='salud'] { --cat: #be123c; }
.card[data-area='educacion'] { --cat: #1d4e89; }
.card[data-area='trabajo-y-pensiones'] { --cat: #b45309; }
.card[data-area='vivienda'] { --cat: #6d28d9; }
.card[data-area='proteccion-social'] { --cat: #0f766e; }
.card[data-area='estado-y-servicios'] { --cat: #334155; }

.card__top h3 {
  width: 100%;
  min-width: 0;
  margin: 0;
  padding-top: 0;
  font-family: var(--font-serif);
  font-size: clamp(1.14rem, 0.4vw + 1.05rem, 1.32rem);
  font-weight: 600;
  line-height: 1.2;
  letter-spacing: -0.032em;
}

.card__mid {
  display: flex;
  flex: 1;
  flex-direction: column;
  gap: 10px;
  min-height: 0;
}

.card__headline {
  display: -webkit-box;
  overflow: hidden;
  -webkit-line-clamp: 3;
  line-clamp: 3;
  -webkit-box-orient: vertical;
  color: var(--ink);
  text-decoration: underline;
  text-decoration-color: transparent;
  text-decoration-thickness: 2px;
  text-underline-offset: 0.16em;
  transition: color 0.16s ease, text-decoration-color 0.16s ease;
}

.card:hover .card__headline {
  color: var(--cat);
  text-decoration-color: color-mix(in srgb, var(--cat) 55%, transparent);
}

.card__headline::after {
  content: '';
  position: absolute;
  inset: 0;
  z-index: 1;
  border-radius: inherit;
}

.card:has(.card__headline:focus-visible) {
  outline: 2px solid color-mix(in srgb, var(--cat) 70%, var(--ink));
  outline-offset: 3px;
}

.card__headline:focus-visible {
  outline: none;
}

.card__cat {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  max-width: min(14rem, 58%);
  margin: 0;
  padding: 8px 11px 7px 10px;
  border: 0;
  border-right: 1px solid color-mix(in srgb, var(--cat) 22%, var(--border));
  border-bottom: 1px solid color-mix(in srgb, var(--cat) 22%, var(--border));
  border-radius: 11px 0 10px 0;
  background: color-mix(in srgb, var(--cat) 12%, var(--surface));
  color: var(--cat);
  font-family: var(--font);
  font-size: 11.5px;
  font-weight: 650;
  letter-spacing: -0.01em;
  line-height: 1.2;
}

.card__cat :deep(svg) {
  width: 13px;
  height: 13px;
  flex-shrink: 0;
}

.card:hover .card__cat {
  background: color-mix(in srgb, var(--cat) 18%, var(--surface));
}

.card__review {
  display: block;
  margin: 6px 0 0;
  font-size: 12.5px;
  font-weight: 500;
  line-height: 1.3;
  color: var(--faint);
}

.card-marks {
  position: relative;
  z-index: 2;
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin: 0;
  padding: 0;
  list-style: none;
}

.card-marks--meta {
  margin-top: auto;
}

.card-marks--corner {
  flex-shrink: 0;
  flex-wrap: nowrap;
  gap: 5px;
  justify-content: flex-end;
  width: auto;
  margin: 0;
  padding: 6px 8px 4px 6px;
}

.card-marks--corner .card-mark {
  flex-shrink: 0;
}

.card-mark {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  min-height: 24px;
  padding: 2px 7px 2px 5px;
  border: 1px solid var(--hairline);
  border-radius: 999px;
  background: var(--canvas);
  color: var(--muted);
  font-size: 12px;
  font-weight: 650;
  line-height: 1;
  cursor: help;
}

.card-mark--icon {
  padding-right: 7px;
  padding-left: 7px;
}

.card-mark--meta {
  padding-inline: 8px;
  border-style: dashed;
  background: rgb(255 255 255 / 65%);
  font-size: 11px;
  font-weight: 600;
}

.card-mark__n {
  color: var(--ink);
  font-variant-numeric: tabular-nums;
}

.card-mark--pageviews {
  border-color: color-mix(in srgb, var(--st-aplicacion) 28%, var(--hairline));
  background: color-mix(in srgb, var(--st-aplicacion-bg) 88%, white);
  color: var(--st-aplicacion);
}

.card-mark--pageviews .card-mark__n {
  color: var(--st-aplicacion);
}

.card-mark--sources {
  border-color: color-mix(in srgb, #475569 24%, var(--hairline));
  background: color-mix(in srgb, #e8edf3 90%, white);
  color: #475569;
}

.card-mark--sources .card-mark__n {
  color: #334155;
}

.card-mark--reactions {
  border-color: color-mix(in srgb, var(--secondary) 26%, var(--hairline));
  background: color-mix(in srgb, #ede8f8 92%, white);
  color: var(--secondary);
}

.card-mark--reactions .card-mark__n {
  color: var(--secondary-hover);
}

.card-mark--claims {
  border-color: color-mix(in srgb, var(--st-discusion) 28%, var(--hairline));
  background: color-mix(in srgb, var(--st-discusion-bg) 90%, white);
  color: var(--st-discusion);
}

.card-mark--claims .card-mark__n {
  color: var(--st-discusion);
}

.card-mark--comparison {
  border-color: color-mix(in srgb, var(--primary) 28%, var(--hairline));
  background: color-mix(in srgb, var(--primary-soft) 92%, white);
  color: var(--primary);
}

.card-mark:focus {
  outline: none;
}

.card-mark:focus-visible {
  outline: 2px solid var(--primary);
  outline-offset: 2px;
}

.card__hook {
  display: -webkit-box;
  margin: 4px 0 0;
  overflow: hidden;
  font-size: 14px;
  line-height: 1.45;
  color: var(--muted);
  -webkit-line-clamp: 4;
  line-clamp: 4;
  -webkit-box-orient: vertical;
}

.card__foot {
  position: relative;
  z-index: 2;
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 10px;
  margin-top: auto;
  padding-top: 10px;
  font-size: 12.5px;
  color: var(--faint);
}

.card__profiles {
  display: flex;
  align-items: center;
  flex-shrink: 0;
  margin-left: auto;
}

.card__profiles--solo {
  margin-left: auto;
}

.card__profile-avatar {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  margin-left: -8px;
  border: 2px solid var(--surface);
  border-radius: 50%;
  overflow: hidden;
  background: var(--primary-soft);
  color: var(--primary-hover);
  font-size: 10px;
  font-weight: 750;
  letter-spacing: -0.03em;
  line-height: 1;
  text-decoration: none;
  flex-shrink: 0;
  box-shadow: 0 0 0 1px rgb(0 0 0 / 6%);
}

.card__profile-avatar:first-child {
  margin-left: 0;
}

.card__profile-avatar img {
  display: block;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.card__profile-avatar--more {
  background: color-mix(in srgb, var(--muted) 14%, var(--surface));
  color: var(--ink);
  font-size: 10px;
  font-variant-numeric: tabular-nums;
}

.card__profile-avatar:hover,
.card__profile-avatar:focus-visible {
  z-index: 5;
  outline: 2px solid var(--primary);
  outline-offset: 1px;
}
</style>
