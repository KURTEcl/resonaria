<script setup lang="ts">
import { profileInitials } from '~/utils/profileDisplay'
import { profileKindLabel } from '~/utils/profileKindLabels'

const { apiUrl } = usePublicApi()

const { data, error, status } = await useAsyncData('perfiles-index', () =>
  $fetch<{ data: Array<{
    slug: string
    display_name: string
    short_name: string | null
    kind: string
    photo_url: string | null
    current_role: string | null
    mentions_count?: number
  }> }>(apiUrl('/v1/perfiles')),
  { server: false, lazy: true },
)

const profiles = computed(() => data.value?.data ?? [])

usePageSeo({
  title: 'Perfiles públicos',
  description: 'Autoridades, legisladores, gobernadores, dirigentes, líderes, periodistas y otras figuras de interés público vinculadas a la cobertura de Resonaria.',
  path: '/perfiles',
})
</script>

<template>
  <div class="profiles-index">
    <header class="profiles-index__intro">
      <h1>Perfiles</h1>
      <p>Figuras del Estado, del Congreso y de los gobiernos regionales, dirigentes de partidos, líderes sociales y gremiales, voces de prensa y otras personas de interés público en nuestra cobertura.</p>
    </header>

    <p v-if="status === 'pending'" class="profiles-index__state" role="status">Cargando…</p>
    <p v-else-if="error" class="profiles-index__state" role="alert">No se pudo cargar el listado.</p>

    <ul v-else-if="profiles.length" class="profile-grid">
      <li v-for="profile in profiles" :key="profile.slug">
        <NuxtLink :to="`/perfiles/${profile.slug}`" class="profile-card">
          <span class="profile-card__avatar" aria-hidden="true">
            <img v-if="profile.photo_url" :src="profile.photo_url" alt="" width="72" height="72">
            <span v-else class="profile-card__initials">{{ profileInitials(profile.display_name) }}</span>
          </span>
          <span class="profile-card__copy">
            <span class="profile-card__name">{{ profile.display_name }}</span>
            <span v-if="profile.current_role" class="profile-card__role">{{ profile.current_role }}</span>
            <span v-else-if="profile.kind" class="profile-card__role">{{ profileKindLabel(profile.kind) }}</span>
            <span v-if="profile.mentions_count" class="profile-card__meta">
              {{ profile.mentions_count }} {{ profile.mentions_count === 1 ? 'tema relacionado' : 'temas relacionados' }}
            </span>
          </span>
        </NuxtLink>
      </li>
    </ul>

    <p v-else class="profiles-index__state">No hay perfiles publicados todavía.</p>
  </div>
</template>

<style scoped>
.profiles-index {
  width: 100%;
  padding: 8px 8px 40px;
}

.profiles-index__intro {
  max-width: 52ch;
  padding: 12px 8px 20px;
  border-bottom: 1px solid var(--hairline);
}

.profiles-index h1 {
  margin: 0;
  font-family: var(--font-serif);
  font-size: clamp(2rem, 3vw, 2.5rem);
  font-weight: 600;
  letter-spacing: -0.03em;
}

.profiles-index__intro p {
  margin: 12px 0 0;
  font-size: 16px;
  line-height: 1.55;
  color: var(--muted);
}

.profiles-index__state {
  padding: 24px 8px;
  color: var(--muted);
}

.profile-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(min(100%, 340px), 1fr));
  gap: 12px;
  margin: 20px 0 0;
  padding: 0;
  list-style: none;
}

.profile-card {
  display: grid;
  grid-template-columns: auto minmax(0, 1fr);
  gap: 16px;
  align-items: center;
  min-height: 100%;
  padding: 16px 18px;
  border: 1px solid var(--border);
  border-radius: 12px;
  background: var(--surface);
  text-decoration: none;
  color: inherit;
  transition: border-color 0.16s ease;
}

.profile-card:hover,
.profile-card:focus-visible {
  border-color: color-mix(in srgb, var(--primary) 35%, var(--border));
}

.profile-card__avatar {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  overflow: hidden;
  background: var(--primary-soft);
  flex-shrink: 0;
}

.profile-card__avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.profile-card__initials {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
  font-size: 1.35rem;
  font-weight: 750;
  color: var(--primary-hover);
}

.profile-card__copy {
  display: grid;
  gap: 4px;
  min-width: 0;
}

.profile-card__name {
  font-size: 1.05rem;
  font-weight: 650;
  letter-spacing: -0.02em;
  line-height: 1.25;
}

.profile-card__role {
  font-size: 14px;
  line-height: 1.4;
  color: var(--muted);
}

.profile-card__meta {
  font-size: 12px;
  font-weight: 600;
  color: var(--faint);
}
</style>
