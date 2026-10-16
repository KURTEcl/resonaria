<script setup lang="ts">
import { profileKindLabel } from '~/utils/profileKindLabels'

type DeskProfileRow = {
  slug: string
  display_name: string
  kind: string
  status: string
  photo_url: string | null
  current_role: string | null
  mentions_confirmed: number
  mentions_suggested: number
}

type DeskProfileDetail = DeskProfileRow & {
  bio: string | null
  aliases: { id: number; alias: string; alias_type: string }[]
  social_accounts: {
    id: number
    platform: string
    handle: string
    account_kind: string
    use_for_avatar: boolean
  }[]
}

type DeskMention = {
  id: number
  profile_slug: string | null
  profile_display_name: string | null
  confidence: string
  excerpt: string | null
  context: { case_slug: string | null; label: string; url: string | null }
}

const tab = ref<'profiles' | 'mentions'>('profiles')
const profiles = ref<DeskProfileRow[]>([])
const mentions = ref<DeskMention[]>([])
const profileSearch = ref('')
const detail = ref<DeskProfileDetail | null>(null)
const detailOpen = ref(false)

const {
  phase,
  name,
  email,
  password,
  error,
  pending,
  enter,
  leave,
  authHeaders,
  apiUrl,
} = useDeskSession(async () => {
  await Promise.all([loadProfiles(), loadMentions()])
})

definePageMeta({ layout: false })
useHead({
  title: 'Perfiles · Cuaderno',
  titleTemplate: '%s',
  meta: [{ name: 'robots', content: 'noindex, nofollow' }],
})

async function loadProfiles() {
  const response = await $fetch<{ data: DeskProfileRow[] }>(apiUrl('/v1/desk/profiles'), {
    headers: authHeaders(),
  })
  profiles.value = response.data
}

async function loadMentions() {
  const response = await $fetch<{ data: DeskMention[] }>(apiUrl('/v1/desk/profile-mentions?suggested=1'), {
    headers: authHeaders(),
  })
  mentions.value = response.data
}

const filteredProfiles = computed(() => {
  const q = profileSearch.value.trim().toLowerCase()
  if (!q) return profiles.value
  return profiles.value.filter((p) => {
    const hay = `${p.display_name} ${p.slug} ${p.current_role ?? ''} ${p.kind}`.toLowerCase()
    return hay.includes(q)
  })
})

function statusLabel(status: string) {
  if (status === 'active') return 'Activo'
  if (status === 'archived') return 'Archivado'
  return status
}

function statusClass(status: string) {
  if (status === 'active') return 'desk-pill--published'
  if (status === 'archived') return 'desk-pill--archived'
  return 'desk-pill--draft'
}

async function openProfile(slug: string) {
  detail.value = await $fetch<{ data: DeskProfileDetail }>(apiUrl(`/v1/desk/profiles/${slug}`), {
    headers: authHeaders(),
  }).then((r) => r.data)
  detailOpen.value = true
}

function closeDetail() {
  detailOpen.value = false
  detail.value = null
}

async function refreshAvatar() {
  const slug = detail.value?.slug
  if (!slug) return
  pending.value = true
  error.value = ''
  try {
    const response = await $fetch<{ data: DeskProfileDetail }>(apiUrl(`/v1/desk/profiles/${slug}/refresh-avatar`), {
      method: 'POST',
      headers: authHeaders(),
    })
    detail.value = response.data
    await loadProfiles()
  } catch {
    error.value = 'No se pudo actualizar la foto.'
  } finally {
    pending.value = false
  }
}

async function confirmMention(id: number) {
  pending.value = true
  try {
    await $fetch(apiUrl(`/v1/desk/profile-mentions/${id}`), {
      method: 'PATCH',
      headers: authHeaders(),
      body: { confidence: 'confirmed' },
    })
    await loadMentions()
    await loadProfiles()
  } catch {
    error.value = 'No se pudo confirmar.'
  } finally {
    pending.value = false
  }
}

async function rejectMention(id: number) {
  pending.value = true
  try {
    await $fetch(apiUrl(`/v1/desk/profile-mentions/${id}`), {
      method: 'DELETE',
      headers: authHeaders(),
    })
    await loadMentions()
    await loadProfiles()
  } catch {
    error.value = 'No se pudo descartar.'
  } finally {
    pending.value = false
  }
}
</script>

<template>
  <DeskShell
    v-model:email="email"
    v-model:password="password"
    title="Perfiles"
    section="perfiles"
    :phase="phase"
    :user-name="name"
    :error="error"
    :pending="pending"
    login-title="Perfiles"
    login-lead="Desactiva perfiles o revisa menciones dudosas del escaneo. Lo habitual (Estado, Congreso, gobernadores, partidos, líderes, prensa y personas de interés) se crea y confirma al publicar vía MCP."
    @enter="enter"
    @leave="leave"
  >
    <div class="desk-tabs" role="tablist">
      <button type="button" :class="{ 'is-active': tab === 'profiles' }" @click="tab = 'profiles'">
        Catálogo
      </button>
      <button type="button" :class="{ 'is-active': tab === 'mentions' }" @click="tab = 'mentions'">
        Revisión
        <span v-if="mentions.length" class="desk-count">{{ mentions.length }}</span>
      </button>
    </div>

    <template v-if="tab === 'profiles'">
      <div class="desk-toolbar">
        <div class="desk-search">
          <input v-model="profileSearch" type="search" placeholder="Buscar por nombre o slug…" autocomplete="off">
        </div>
      </div>

      <div class="desk-panel">
        <div class="desk-table-wrap">
          <table class="desk-table">
            <thead>
              <tr>
                <th scope="col">Persona</th>
                <th scope="col">Cargo</th>
                <th scope="col">Estado</th>
                <th scope="col">Menciones</th>
                <th scope="col"><span class="visually-hidden">Acciones</span></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in filteredProfiles" :key="item.slug">
                <td>
                  <div style="display: flex; gap: 10px; align-items: center">
                    <img
                      v-if="item.photo_url"
                      :src="item.photo_url"
                      alt=""
                      class="desk-avatar"
                      width="36"
                      height="36"
                    >
                    <div>
                      <div class="desk-table__title">{{ item.display_name }}</div>
                      <div class="desk-table__meta">{{ item.slug }}</div>
                    </div>
                  </div>
                </td>
                <td>{{ item.current_role || profileKindLabel(item.kind) }}</td>
                <td>
                  <span class="desk-pill" :class="statusClass(item.status)">{{ statusLabel(item.status) }}</span>
                </td>
                <td>
                  {{ item.mentions_confirmed }} confirmadas
                  <span v-if="item.mentions_suggested" class="desk-table__meta">
                    · {{ item.mentions_suggested }} sugeridas
                  </span>
                </td>
                <td>
                  <div class="desk-table__actions">
                    <button class="btn btn--quiet" type="button" @click="openProfile(item.slug)">Detalle</button>
                    <NuxtLink
                      v-if="item.status === 'active'"
                      class="btn btn--quiet"
                      :to="`/perfiles/${item.slug}`"
                      target="_blank"
                      rel="noopener"
                    >
                      Ver
                    </NuxtLink>
                  </div>
                </td>
              </tr>
              <tr v-if="filteredProfiles.length === 0">
                <td colspan="5" class="desk-table__empty">No hay perfiles que coincidan.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>

    <template v-else>
      <div class="desk-panel">
        <div class="desk-table-wrap">
          <table class="desk-table">
            <thead>
              <tr>
                <th scope="col">Perfil</th>
                <th scope="col">Dónde aparece</th>
                <th scope="col">Extracto</th>
                <th scope="col"><span class="visually-hidden">Acciones</span></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="mention in mentions" :key="mention.id">
                <td class="desk-table__title">{{ mention.profile_display_name || '—' }}</td>
                <td>{{ mention.context.label }}</td>
                <td>
                  <span v-if="mention.excerpt" class="desk-table__meta" style="font-style: italic">
                    {{ mention.excerpt }}
                  </span>
                  <span v-else>—</span>
                </td>
                <td>
                  <div class="desk-table__actions">
                    <NuxtLink
                      v-if="mention.context.case_slug"
                      class="btn btn--quiet"
                      :to="`/casos/${mention.context.case_slug}`"
                      target="_blank"
                      rel="noopener"
                    >
                      Tema
                    </NuxtLink>
                    <button class="btn btn--primary" type="button" :disabled="pending" @click="confirmMention(mention.id)">
                      Confirmar
                    </button>
                    <button class="btn btn--quiet" type="button" :disabled="pending" @click="rejectMention(mention.id)">
                      Descartar
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="mentions.length === 0">
                <td colspan="4" class="desk-table__empty">No hay menciones pendientes de revisión.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>

    <Teleport to="body">
      <template v-if="detailOpen && detail">
        <button type="button" class="desk-drawer-backdrop" aria-label="Cerrar" @click="closeDetail" />
        <aside class="desk-drawer" role="dialog" aria-labelledby="desk-profile-title">
          <div class="desk-drawer__head">
            <div style="display: flex; gap: 14px; align-items: flex-start">
              <img
                v-if="detail.photo_url"
                :src="detail.photo_url"
                alt=""
                class="desk-avatar desk-avatar--lg"
                width="72"
                height="72"
              >
              <div>
                <h2 id="desk-profile-title">{{ detail.display_name }}</h2>
                <p class="desk-drawer__slug">{{ detail.slug }}</p>
                <span class="desk-pill" :class="statusClass(detail.status)">{{ statusLabel(detail.status) }}</span>
              </div>
            </div>
            <button class="btn btn--quiet" type="button" @click="closeDetail">Cerrar</button>
          </div>
          <div class="desk-drawer__body">
            <p><strong>Tipo:</strong> {{ profileKindLabel(detail.kind) }}</p>
            <p v-if="detail.current_role"><strong>Cargo:</strong> {{ detail.current_role }}</p>
            <p v-if="detail.bio">{{ detail.bio }}</p>
            <div>
              <strong>Aliases</strong>
              <div class="desk-chip-row" style="margin-top: 8px">
                <span v-for="alias in detail.aliases" :key="alias.id" class="desk-chip">
                  {{ alias.alias }}
                  <span style="opacity: 0.7">({{ alias.alias_type }})</span>
                </span>
                <span v-if="!detail.aliases.length" class="desk-table__meta">—</span>
              </div>
            </div>
            <div>
              <strong>Cuentas</strong>
              <div class="desk-chip-row" style="margin-top: 8px">
                <span v-for="acc in detail.social_accounts" :key="acc.id" class="desk-chip">
                  {{ acc.platform }} @{{ acc.handle }}
                </span>
                <span v-if="!detail.social_accounts.length" class="desk-table__meta">—</span>
              </div>
            </div>
            <p class="desk-table__meta">
              {{ detail.mentions_confirmed }} menciones confirmadas · {{ detail.mentions_suggested }} sugeridas
            </p>
          </div>
          <div class="desk-drawer__foot">
            <button class="btn btn--primary" type="button" :disabled="pending" @click="refreshAvatar">
              Actualizar foto desde X
            </button>
            <NuxtLink
              v-if="detail.status === 'active'"
              class="desk-link"
              :to="`/perfiles/${detail.slug}`"
              target="_blank"
              rel="noopener"
            >
              Ficha pública →
            </NuxtLink>
          </div>
        </aside>
      </template>
    </Teleport>
  </DeskShell>
</template>

<style scoped>
.visually-hidden {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}
</style>
