<script setup lang="ts">
type DeskCase = {
  slug: string
  title: string
  hook: string | null
  summary: string | null
  publication_status: string
  public_status_label: string | null
  category: string | null
  updated_at: string | null
}

type StatusFilter = 'all' | 'published' | 'draft'

const cases = ref<DeskCase[]>([])
const search = ref('')
const statusFilter = ref<StatusFilter>('all')
const editing = ref<DeskCase | null>(null)
const draft = ref({ title: '', hook: '', summary: '' })

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
  await loadCases()
})

definePageMeta({ layout: false })
useHead({
  title: 'Temas · Cuaderno',
  titleTemplate: '%s',
  meta: [{ name: 'robots', content: 'noindex, nofollow' }],
})

async function loadCases() {
  const response = await $fetch<{ data: DeskCase[] }>(apiUrl('/v1/desk/cases'), {
    headers: authHeaders(),
  })
  cases.value = response.data
}

const filteredCases = computed(() => {
  const q = search.value.trim().toLowerCase()
  return cases.value.filter((item) => {
    if (statusFilter.value === 'published' && item.publication_status !== 'published') return false
    if (statusFilter.value === 'draft' && item.publication_status !== 'draft') return false
    if (!q) return true
    const hay = `${item.title} ${item.slug} ${item.category ?? ''}`.toLowerCase()
    return hay.includes(q)
  })
})

const counts = computed(() => ({
  all: cases.value.length,
  published: cases.value.filter((c) => c.publication_status === 'published').length,
  draft: cases.value.filter((c) => c.publication_status === 'draft').length,
}))

function formatDate(iso: string | null) {
  if (!iso) return '—'
  return new Date(iso).toLocaleDateString('es-CL', { day: 'numeric', month: 'short', year: 'numeric' })
}

function openEdit(item: DeskCase) {
  editing.value = item
  draft.value = {
    title: item.title,
    hook: item.hook ?? '',
    summary: item.summary ?? '',
  }
}

function closeEdit() {
  editing.value = null
}

async function saveEdit() {
  const item = editing.value
  if (!item) return
  error.value = ''
  pending.value = true
  try {
    const response = await $fetch<{ data: DeskCase }>(apiUrl(`/v1/desk/cases/${item.slug}`), {
      method: 'PATCH',
      headers: authHeaders(),
      body: {
        title: draft.value.title,
        hook: draft.value.hook || null,
        summary: draft.value.summary || null,
      },
    })
    cases.value = cases.value.map((row) => (row.slug === item.slug ? response.data : row))
    closeEdit()
  } catch {
    error.value = 'No se pudo guardar.'
  } finally {
    pending.value = false
  }
}

async function setStatus(item: DeskCase, publicationStatus: 'draft' | 'published') {
  error.value = ''
  pending.value = true
  try {
    const response = await $fetch<{ data: DeskCase }>(apiUrl(`/v1/desk/cases/${item.slug}`), {
      method: 'PATCH',
      headers: authHeaders(),
      body: { publication_status: publicationStatus },
    })
    cases.value = cases.value.map((row) => (row.slug === item.slug ? response.data : row))
    if (editing.value?.slug === item.slug) {
      editing.value = response.data
    }
  } catch {
    error.value = 'No se pudo cambiar el estado.'
  } finally {
    pending.value = false
  }
}
</script>

<template>
  <DeskShell
    v-model:email="email"
    v-model:password="password"
    title="Temas"
    section="temas"
    :phase="phase"
    :user-name="name"
    :error="error"
    :pending="pending"
    login-title="Cuaderno"
    login-lead="Acceso restringido para editar fichas publicadas y borradores."
    @enter="enter"
    @leave="leave"
  >
    <div class="desk-toolbar">
      <div class="desk-search">
        <input v-model="search" type="search" placeholder="Buscar por título o slug…" autocomplete="off">
      </div>
      <div class="desk-filters" role="group" aria-label="Estado de publicación">
        <button type="button" :class="{ 'is-active': statusFilter === 'all' }" @click="statusFilter = 'all'">
          Todos ({{ counts.all }})
        </button>
        <button
          type="button"
          :class="{ 'is-active': statusFilter === 'published' }"
          @click="statusFilter = 'published'"
        >
          Publicadas ({{ counts.published }})
        </button>
        <button type="button" :class="{ 'is-active': statusFilter === 'draft' }" @click="statusFilter = 'draft'">
          Borradores ({{ counts.draft }})
        </button>
      </div>
    </div>

    <div class="desk-panel">
      <div class="desk-table-wrap">
        <table class="desk-table">
          <thead>
            <tr>
              <th scope="col">Título</th>
              <th scope="col">Categoría</th>
              <th scope="col">Estado</th>
              <th scope="col">Actualizado</th>
              <th scope="col"><span class="visually-hidden">Acciones</span></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in filteredCases" :key="item.slug">
              <td>
                <div class="desk-table__title">{{ item.title }}</div>
                <div class="desk-table__meta">{{ item.slug }}</div>
              </td>
              <td>{{ item.category || '—' }}</td>
              <td>
                <span
                  class="desk-pill"
                  :class="item.publication_status === 'published' ? 'desk-pill--published' : 'desk-pill--draft'"
                >
                  {{ item.publication_status === 'published' ? 'Publicada' : 'Borrador' }}
                </span>
              </td>
              <td>{{ formatDate(item.updated_at) }}</td>
              <td>
                <div class="desk-table__actions">
                  <button class="btn btn--quiet" type="button" @click="openEdit(item)">Editar</button>
                  <NuxtLink class="btn btn--quiet" :to="`/casos/${item.slug}`" target="_blank" rel="noopener">
                    Ver
                  </NuxtLink>
                  <button
                    v-if="item.publication_status === 'published'"
                    class="btn btn--quiet"
                    type="button"
                    :disabled="pending"
                    @click="setStatus(item, 'draft')"
                  >
                    Retirar
                  </button>
                  <button
                    v-else
                    class="btn btn--primary"
                    type="button"
                    :disabled="pending"
                    @click="setStatus(item, 'published')"
                  >
                    Publicar
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredCases.length === 0">
              <td colspan="5" class="desk-table__empty">No hay temas que coincidan con el filtro.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <Teleport to="body">
      <template v-if="editing">
        <button type="button" class="desk-drawer-backdrop" aria-label="Cerrar" @click="closeEdit" />
        <aside class="desk-drawer" role="dialog" aria-labelledby="desk-edit-title">
          <div class="desk-drawer__head">
            <div>
              <h2 id="desk-edit-title">Editar tema</h2>
              <p class="desk-drawer__slug">{{ editing.slug }}</p>
            </div>
            <button class="btn btn--quiet" type="button" @click="closeEdit">Cerrar</button>
          </div>
          <div class="desk-drawer__body">
            <label class="desk-field">
              Título
              <input v-model="draft.title" required maxlength="180">
            </label>
            <label class="desk-field">
              Gancho
              <textarea v-model="draft.hook" rows="3" maxlength="280" />
            </label>
            <label class="desk-field">
              Resumen
              <textarea v-model="draft.summary" rows="6" maxlength="5000" />
            </label>
            <NuxtLink class="desk-link" :to="`/casos/${editing.slug}`" target="_blank" rel="noopener">
              Abrir ficha pública →
            </NuxtLink>
          </div>
          <div class="desk-drawer__foot">
            <button class="btn btn--primary" type="button" :disabled="pending" @click="saveEdit">
              Guardar cambios
            </button>
            <button
              v-if="editing.publication_status === 'published'"
              class="btn btn--quiet"
              type="button"
              :disabled="pending"
              @click="setStatus(editing, 'draft')"
            >
              Pasar a borrador
            </button>
            <button
              v-else
              class="btn btn--primary"
              type="button"
              :disabled="pending"
              @click="setStatus(editing, 'published')"
            >
              Publicar
            </button>
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
