<script setup lang="ts">
const config = useRuntimeConfig()
const verification = String(config.public.googleSiteVerification || '')
if (verification) {
  useSeoMeta({ googleSiteVerification: verification })
}
</script>

<template>
  <NuxtRouteAnnouncer />
  <NuxtLayout>
    <NuxtPage />
  </NuxtLayout>
</template>
