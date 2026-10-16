<script setup lang="ts">
const props = defineProps<{
  platform: 'tiktok' | 'instagram' | 'x' | 'twitter' | 'threads' | 'reddit'
}>()

const key = computed(() => (props.platform === 'twitter' ? 'x' : props.platform))
</script>

<template>
  <svg v-if="key === 'instagram'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
    <rect x="4" y="4" width="16" height="16" rx="5" />
    <circle cx="12" cy="12" r="3.5" />
    <circle cx="17.2" cy="6.8" r="0.8" fill="currentColor" stroke="none" />
  </svg>
  <svg v-else-if="key === 'x'" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
    <path d="M14.7 10.3 22.2 2h-1.8l-6.5 7.2L8.8 2H2.5l8 11.1L2.5 22h1.8l7-7.8L15.2 22h6.3L14.7 10.3Zm-2.5 2.8-.8-1.1L5 3.4h2.8l5.2 7.1.8 1.1 6.7 9.1h-2.8l-5.5-7.6Z" />
  </svg>
  <svg v-else-if="key === 'threads'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
    <circle cx="12" cy="12" r="8" />
    <path d="M9 12.2c.4 2 2 3.2 3.8 2.8 1.6-.3 2.5-1.6 2.4-3.1-.2-2.2-2.4-3-4.2-2.6-2 .4-3.2 2-2.8 3.8.4 1.6 1.8 2.4 3.4 2.2" />
  </svg>
  <svg v-else-if="key === 'reddit'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
    <circle cx="12" cy="13" r="7" />
    <circle cx="9.2" cy="12.2" r="0.8" fill="currentColor" stroke="none" />
    <circle cx="14.8" cy="12.2" r="0.8" fill="currentColor" stroke="none" />
    <path d="M9.5 15.4c.8.8 1.6 1.1 2.5 1.1s1.7-.3 2.5-1.1" />
    <path d="M16.2 7.2 14.6 4.8" />
    <circle cx="17.4" cy="6.4" r="1" fill="currentColor" stroke="none" />
  </svg>
  <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
    <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z" />
  </svg>
</template>
