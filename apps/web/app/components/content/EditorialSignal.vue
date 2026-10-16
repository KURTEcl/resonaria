<script setup lang="ts">
defineProps<{
  kind: 'review_overdue'
}>()

const copy: Record<'review_overdue', { label: string; title: string }> = {
  review_overdue: {
    label: 'Revisión pendiente',
    title: 'La fecha programada de revisión editorial ya pasó; estamos actualizando la ficha.',
  },
}
</script>

<template>
  <span class="signal" :class="`signal--${kind}`" :title="copy[kind].title">
    {{ copy[kind].label }}
  </span>
</template>

<style scoped>
.signal {
  display: inline-flex;
  align-items: center;
  min-height: 22px;
  padding: 2px 8px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 650;
  line-height: 1.2;
  letter-spacing: 0.01em;
}

.signal--review_overdue {
  border: 1px dashed color-mix(in srgb, var(--st-discusion) 40%, transparent);
  background: color-mix(in srgb, var(--st-discusion-bg) 70%, white);
  color: var(--st-discusion);
}
</style>
