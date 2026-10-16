<script setup lang="ts">
defineProps<{ slug?: string | null }>()
</script>

<template>
  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    <template v-if="slug === 'salud'">
      <path d="M3 13h4.2l1.6-4.2 2.4 7.4 2.2-3.2H21" />
    </template>
    <template v-else-if="slug === 'educacion'">
      <path d="M3 10 12 6l9 4-9 4-9-4Z" />
      <path d="M7 12v4.2c0 .9 2.2 2.3 5 2.3s5-1.4 5-2.3V12" />
    </template>
    <template v-else-if="slug === 'trabajo-y-pensiones'">
      <rect x="3" y="8" width="18" height="11" rx="1.5" />
      <path d="M8 8V6.5A1.5 1.5 0 0 1 9.5 5h5A1.5 1.5 0 0 1 16 6.5V8M3 13h18" />
    </template>
    <template v-else-if="slug === 'vivienda'">
      <path d="M4 11 12 4l8 7" />
      <path d="M6 10.5V20h12v-9.5" />
      <path d="M10 20v-4h4v4" />
    </template>
    <template v-else-if="slug === 'proteccion-social'">
      <path d="M12 3.5 19 6.5v5c0 4-2.8 6.4-7 8-4.2-1.6-7-4-7-8v-5L12 3.5Z" />
    </template>
    <template v-else>
      <path d="M4 20h16M6 20V10h12v10M9.5 20v-4h5v4M4 10 12 4l8 6" />
    </template>
  </svg>
</template>
