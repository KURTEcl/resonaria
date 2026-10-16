<script setup lang="ts">
import { STATUS_LABELS } from '~/utils/topicLabels'

const props = defineProps<{ estado: string }>()

const known = computed(() => props.estado in STATUS_LABELS)
const text = computed(() => STATUS_LABELS[props.estado] ?? props.estado)
</script>

<template>
  <span class="status" :class="known ? `status--${estado}` : 'status--unknown'">
    <svg viewBox="0 0 12 12" width="12" height="12" aria-hidden="true">
      <circle v-if="estado === 'en_discusion'" cx="6" cy="6" r="4.25" fill="none" stroke="currentColor" stroke-width="1.5" stroke-dasharray="2.2 1.6" />
      <path v-else-if="estado === 'aprobado'" d="M2.5 6.3 5 8.6l4.5-5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
      <circle v-else-if="estado === 'en_aplicacion'" cx="6" cy="6" r="4" fill="currentColor" />
      <path v-else-if="estado === 'revertido'" d="M4 3.2 2.2 5 4 6.8M2.4 5h4.4a2.6 2.6 0 0 1 0 5.2H5" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" />
      <circle v-else cx="6" cy="6" r="4.25" fill="none" stroke="currentColor" stroke-width="1.4" />
    </svg>
    {{ text }}
  </span>
</template>
