<script setup lang="ts">
const props = withDefaults(
  defineProps<{
    label: string
    field: 'pieza' | 'incluye' | 'perfil'
    options: readonly { value: string, label: string }[]
    filterPath?: string
  }>(),
  { filterPath: '/casos' },
)

const { filters, applyFilters, toggleInList } = useCatalogFilters()

const root = ref<HTMLElement | null>(null)
const open = ref(false)
const listId = `filter-menu-${props.field}`

const selected = computed(() =>
  props.options.filter((opt) => filters.value[props.field].includes(opt.value)),
)

const triggerLabel = computed(() => {
  if (selected.value.length === 0) return props.label
  if (selected.value.length === 1) return selected.value[0].label
  const plural = props.field === 'pieza' ? 'formatos' : props.field === 'perfil' ? 'perfiles' : 'criterios'
  return `${selected.value.length} ${plural}`
})

function choose(value: string | null) {
  const next = value
    ? toggleInList(filters.value[props.field], value)
    : []
  applyFilters({ ...filters.value, [props.field]: next }, props.filterPath)
}

function onPointerDown(event: PointerEvent) {
  if (!root.value?.contains(event.target as Node)) open.value = false
}

function onKey(event: KeyboardEvent) {
  if (event.key === 'Escape') open.value = false
}

onMounted(() => {
  document.addEventListener('pointerdown', onPointerDown)
  document.addEventListener('keydown', onKey)
})

onBeforeUnmount(() => {
  document.removeEventListener('pointerdown', onPointerDown)
  document.removeEventListener('keydown', onKey)
})
</script>

<template>
  <div ref="root" class="filter-menu">
    <button
      type="button"
      class="filter-menu__trigger"
      :aria-expanded="open"
      :aria-controls="listId"
      aria-haspopup="listbox"
      @click="open = !open"
    >
      <span>{{ triggerLabel }}</span>
      <svg viewBox="0 0 16 16" width="14" height="14" aria-hidden="true">
        <path d="M4 6.2 8 10l4-3.8" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
      </svg>
    </button>
    <ul
      v-if="open"
      :id="listId"
      class="filter-menu__panel"
      role="listbox"
      :aria-label="label"
      aria-multiselectable="true"
    >
      <li>
        <button
          type="button"
          class="filter-menu__item"
          role="option"
          :aria-selected="selected.length === 0"
          :class="{ 'filter-menu__item--on': selected.length === 0 }"
          @click="choose(null)"
        >
          Todos
        </button>
      </li>
      <li v-for="opt in options" :key="opt.value">
        <button
          type="button"
          class="filter-menu__item"
          role="option"
          :aria-selected="filters[field].includes(opt.value)"
          :class="{ 'filter-menu__item--on': filters[field].includes(opt.value) }"
          @click="choose(opt.value)"
        >
          {{ opt.label }}
        </button>
      </li>
    </ul>
  </div>
</template>
