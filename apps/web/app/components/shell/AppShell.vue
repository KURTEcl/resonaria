<script setup lang="ts">
const route = useRoute()
const query = computed(() => (typeof route.query.q === 'string' ? route.query.q : ''))
const { name: portalName, githubUrl } = usePortalSite()
</script>

<template>
  <div class="app-shell">
    <ShellIconRail />
    <div class="app-shell__main">
      <header class="top-header">
        <div class="top-header__start">
          <div class="top-header__brand">
            <NuxtLink to="/" class="top-header__logo" :aria-label="`${portalName}, inicio`"><img src="/brand/logo.svg?v=resonaria-conversation-1" width="867" height="240" :alt="portalName" /></NuxtLink>
          </div>
          <form class="top-header__search" action="/" method="get" role="search">
            <label class="sr-only" for="site-search">Buscar en {{ portalName }}</label>
            <input
              id="site-search"
              :key="query"
              name="q"
              type="search"
              :value="query"
              placeholder="Buscar noticias, análisis o temas"
              autocomplete="off"
            />
          </form>
        </div>
      </header>
      <main id="contenido-principal" class="page-main">
        <slot />
      </main>
      <footer class="site-footer">
        <NuxtLink to="/" class="site-footer__brand" :aria-label="`${portalName}, inicio`"><img src="/brand/logo.svg?v=resonaria-conversation-1" width="867" height="240" :alt="portalName" /></NuxtLink>
        <nav aria-label="Información del proyecto">
          <NuxtLink to="/metodologia">Metodología</NuxtLink>
          <NuxtLink to="/fuentes">Fuentes</NuxtLink>
          <NuxtLink to="/correcciones">Correcciones</NuxtLink>
          <NuxtLink to="/ayuda">Ayuda</NuxtLink>
          <a :href="githubUrl" target="_blank" rel="noopener noreferrer">GitHub</a>
        </nav>
      </footer>
    </div>
  </div>
</template>
