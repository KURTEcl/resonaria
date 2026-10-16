<script setup lang="ts">
import type { PublicCaseListItem, PublicReaction } from '~/composables/usePublicApi'
import { profileInitials, socialProfileUrl } from '~/utils/profileDisplay'
import { profileKindLabel } from '~/utils/profileKindLabels'
import { SOURCE_KIND_LABELS, formatDate } from '~/utils/topicLabels'
import { sourceHost, splitSourceTitle } from '~/utils/topicDetail'

type ProfileRole = { title: string, organization: string | null }
type SocialAccount = { platform: string, handle: string, account_kind: string, label: string | null }
type LinkedSource = {
  title: string
  url: string
  kind: string
  published_on: string | null
  case_slug: string | null
  case_title: string | null
}
type LinkedReaction = PublicReaction & {
  case_slug: string | null
  case_title: string | null
}

type ProfileDetail = {
  slug: string
  display_name: string
  kind: string
  photo_url: string | null
  bio: string | null
  roles: ProfileRole[]
  social_accounts: SocialAccount[]
  topics: PublicCaseListItem[]
  linked_sources: LinkedSource[]
  linked_reactions: LinkedReaction[]
  seo: { meta_title: string, meta_description: string }
}

const route = useRoute()
const slug = computed(() => String(route.params.slug))
const { apiUrl } = usePublicApi()

const { data, error, status } = await useAsyncData(
  () => `perfil-${slug.value}`,
  () => $fetch<{ data: ProfileDetail }>(apiUrl(`/v1/perfiles/${slug.value}`)),
  { watch: [slug], server: false, lazy: true },
)

const profile = computed(() => data.value?.data)

const platformLabel: Record<string, string> = {
  x: 'X',
  twitter: 'X',
  instagram: 'Instagram',
  tiktok: 'TikTok',
  threads: 'Threads',
  reddit: 'Reddit',
}

watch(
  () => profile.value,
  (p) => {
    if (!p) return
    usePageSeo({
      title: p.seo.meta_title,
      description: p.seo.meta_description,
      path: `/perfiles/${p.slug}`,
      image: p.photo_url,
    })
  },
  { immediate: true },
)
</script>

<template>
  <div class="profile-page">
    <p v-if="status === 'pending'" class="profile-page__state" role="status">Cargando…</p>
    <p v-else-if="error" class="profile-page__state" role="alert">No se encontró este perfil.</p>

    <template v-else-if="profile">
      <header class="profile-hero">
        <div class="profile-hero__avatar" aria-hidden="true">
          <img v-if="profile.photo_url" :src="profile.photo_url" alt="" width="112" height="112">
          <span v-else class="profile-hero__initials">{{ profileInitials(profile.display_name) }}</span>
        </div>
        <div class="profile-hero__copy">
          <p class="profile-hero__kicker">{{ profileKindLabel(profile.kind) || 'Perfil público' }}</p>
          <h1>{{ profile.display_name }}</h1>
          <ul v-if="profile.roles.length" class="profile-hero__roles">
            <li v-for="(role, index) in profile.roles" :key="`${role.title}-${index}`">
              <strong>{{ role.title }}</strong>
              <span v-if="role.organization"> · {{ role.organization }}</span>
            </li>
          </ul>
          <p v-if="profile.bio" class="profile-hero__bio">{{ profile.bio }}</p>
          <nav v-if="profile.social_accounts.length" class="profile-social" aria-label="Cuentas públicas">
            <a
              v-for="account in profile.social_accounts"
              :key="`${account.platform}-${account.handle}`"
              class="profile-social__chip"
              :href="socialProfileUrl(account.platform, account.handle)"
              rel="noopener noreferrer"
              target="_blank"
            >
              <ContentSocialPlatformIcon :platform="account.platform === 'twitter' ? 'x' : account.platform" />
              <span>@{{ account.handle.replace(/^@/, '') }}</span>
              <span v-if="account.label" class="profile-social__kind">{{ account.label }}</span>
            </a>
          </nav>
        </div>
      </header>

      <section v-if="profile.topics.length" class="profile-section" aria-labelledby="profile-topics-title">
        <header class="profile-section__head">
          <h2 id="profile-topics-title">Temas en la cobertura</h2>
          <p><span class="num">{{ profile.topics.length }}</span> {{ profile.topics.length === 1 ? 'nota' : 'notas' }}</p>
        </header>
        <ol class="profile-cards">
          <li v-for="topic in profile.topics" :key="topic.slug">
            <CatalogTopicCard :topic="topic" />
          </li>
        </ol>
      </section>

      <section v-if="profile.linked_sources.length" class="profile-section" aria-labelledby="profile-sources-title">
        <header class="profile-section__head">
          <h2 id="profile-sources-title">Fuentes vinculadas</h2>
          <p>Documentos y notas donde aparece en la ficha.</p>
        </header>
        <ul class="profile-artifact-grid profile-artifact-grid--sources">
          <li v-for="source in profile.linked_sources" :key="source.url" class="profile-source-card">
            <p v-if="splitSourceTitle(source.title).publisher" class="profile-source-card__pub">
              {{ splitSourceTitle(source.title).publisher }}
            </p>
            <a class="profile-source-card__title" :href="source.url" rel="noopener noreferrer" target="_blank">
              {{ splitSourceTitle(source.title).headline }}
            </a>
            <p class="profile-source-card__meta">
              <span v-if="sourceHost(source.url)">{{ sourceHost(source.url) }}</span>
              <time v-if="source.published_on" :datetime="source.published_on">{{ formatDate(source.published_on) }}</time>
              <span v-if="SOURCE_KIND_LABELS[source.kind]" class="profile-tag">{{ SOURCE_KIND_LABELS[source.kind] }}</span>
            </p>
            <p v-if="source.case_slug" class="profile-source-card__case">
              En la nota
              <NuxtLink :to="`/casos/${source.case_slug}`">{{ source.case_title }}</NuxtLink>
            </p>
          </li>
        </ul>
      </section>

      <section v-if="profile.linked_reactions.length" class="profile-section" aria-labelledby="profile-reactions-title">
        <header class="profile-section__head">
          <h2 id="profile-reactions-title">En redes</h2>
          <p>Publicaciones del archivo editorial asociadas a este perfil.</p>
        </header>
        <ul class="profile-artifact-grid">
          <li v-for="reaction in profile.linked_reactions" :key="reaction.url" class="profile-reaction-card">
            <div class="profile-reaction-card__head">
              <ContentSocialPlatformIcon :platform="reaction.platform === 'twitter' ? 'x' : reaction.platform" />
              <span class="profile-reaction-card__platform">{{ platformLabel[reaction.platform] ?? reaction.platform }}</span>
              <span v-if="reaction.author_handle" class="profile-reaction-card__handle">@{{ reaction.author_handle.replace(/^@/, '') }}</span>
            </div>
            <p v-if="reaction.caption" class="profile-reaction-card__caption">{{ reaction.caption }}</p>
            <p class="profile-reaction-card__links">
              <a :href="reaction.url" rel="noopener noreferrer" target="_blank">Ver publicación</a>
              <template v-if="reaction.case_slug">
                · <NuxtLink :to="`/casos/${reaction.case_slug}#panel-redes`">En la ficha</NuxtLink>
              </template>
            </p>
          </li>
        </ul>
      </section>
    </template>
  </div>
</template>

<style scoped>
.profile-page {
  width: 100%;
  padding: 8px 8px 40px;
}

.profile-page__state {
  padding: 24px 8px;
  color: var(--muted);
}

.profile-hero {
  display: grid;
  grid-template-columns: auto minmax(0, 1fr);
  gap: 24px 28px;
  align-items: start;
  padding: 12px 8px 28px;
  border-bottom: 1px solid var(--hairline);
}

.profile-hero__avatar {
  width: 112px;
  height: 112px;
  border-radius: 50%;
  overflow: hidden;
  background: var(--primary-soft);
  box-shadow: 0 0 0 1px var(--hairline);
}

.profile-hero__avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.profile-hero__initials {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
  font-size: 2rem;
  font-weight: 750;
  letter-spacing: -0.04em;
  color: var(--primary-hover);
}

.profile-hero__kicker {
  margin: 0 0 6px;
  font-size: 12px;
  font-weight: 650;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  color: var(--faint);
}

.profile-hero h1 {
  margin: 0;
  font-family: var(--font-serif);
  font-size: clamp(2rem, 3vw, 2.75rem);
  font-weight: 600;
  line-height: 1.08;
  letter-spacing: -0.03em;
}

.profile-hero__roles {
  margin: 12px 0 0;
  padding: 0;
  list-style: none;
  font-size: 15px;
  line-height: 1.5;
  color: var(--muted);
}

.profile-hero__roles li + li {
  margin-top: 4px;
}

.profile-hero__bio {
  max-width: 62ch;
  margin: 14px 0 0;
  font-size: 16px;
  line-height: 1.55;
  color: var(--ink);
}

.profile-social {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 18px;
}

.profile-social__chip {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  border: 1px solid var(--hairline);
  border-radius: 999px;
  background: var(--canvas);
  color: var(--ink);
  font-size: 13px;
  font-weight: 600;
  text-decoration: none;
}

.profile-social__chip:hover,
.profile-social__chip:focus-visible {
  border-color: color-mix(in srgb, var(--primary) 35%, var(--border));
  color: var(--primary-hover);
}

.profile-social__kind {
  font-size: 11px;
  font-weight: 650;
  color: var(--faint);
}

.profile-section {
  margin-top: 28px;
  padding-top: 8px;
}

.profile-section__head {
  display: flex;
  flex-wrap: wrap;
  align-items: baseline;
  gap: 8px 16px;
  margin-bottom: 14px;
  padding: 0 8px;
}

.profile-section__head h2 {
  margin: 0;
  font-size: 22px;
  font-weight: 650;
  letter-spacing: -0.02em;
}

.profile-section__head p {
  margin: 0;
  font-size: 14px;
  color: var(--muted);
}

.profile-cards {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 14px;
  margin: 0;
  padding: 0;
  list-style: none;
}

.profile-cards > li {
  display: flex;
  min-height: 0;
}

.profile-artifact-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(min(100%, 320px), 1fr));
  gap: 12px;
  margin: 0;
  padding: 0;
  list-style: none;
}

.profile-source-card,
.profile-reaction-card {
  padding: 14px 16px;
  border: 1px solid var(--hairline);
  border-radius: 12px;
  background: var(--surface);
}

.profile-source-card__pub {
  margin: 0 0 4px;
  font-size: 11px;
  font-weight: 650;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  color: var(--muted);
}

.profile-source-card__title {
  display: block;
  font-size: 15px;
  font-weight: 600;
  line-height: 1.4;
  color: var(--ink);
  text-decoration: none;
}

.profile-source-card__title:hover,
.profile-source-card__title:focus-visible {
  color: var(--primary-hover);
  text-decoration: underline;
}

.profile-source-card__meta {
  display: flex;
  flex-wrap: wrap;
  gap: 6px 10px;
  margin: 10px 0 0;
  font-size: 12px;
  color: var(--faint);
}

.profile-tag {
  padding: 2px 8px;
  border-radius: 999px;
  background: var(--canvas);
  color: var(--muted);
  font-weight: 650;
}

.profile-source-card__case {
  margin: 10px 0 0;
  font-size: 13px;
  line-height: 1.45;
  color: var(--muted);
}

.profile-source-card__case a {
  font-weight: 650;
  color: var(--primary-hover);
  text-decoration: none;
}

.profile-source-card__case a:hover {
  text-decoration: underline;
}

.profile-reaction-card__head {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 6px 8px;
  font-size: 12px;
  font-weight: 650;
  color: var(--muted);
}

.profile-reaction-card__platform {
  color: var(--ink);
}

.profile-reaction-card__caption {
  margin: 10px 0 0;
  font-size: 14px;
  line-height: 1.5;
  color: var(--ink);
  display: -webkit-box;
  overflow: hidden;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
}

.profile-reaction-card__links {
  margin: 12px 0 0;
  font-size: 13px;
  font-weight: 600;
}

.profile-reaction-card__links a {
  color: var(--primary-hover);
  text-decoration: none;
}

.profile-reaction-card__links a:hover {
  text-decoration: underline;
}

@media (max-width: 980px) {
  .profile-cards {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 720px) {
  .profile-hero {
    grid-template-columns: 1fr;
    justify-items: start;
  }

  .profile-cards {
    grid-template-columns: 1fr;
  }
}
</style>
