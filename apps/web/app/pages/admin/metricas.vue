<script setup lang="ts">
const { fetchPortfolioStats } = usePublicApi()

const { data, error, status, refresh } = await useAsyncData(
  'admin-portfolio',
  () => fetchPortfolioStats(),
  { server: false, lazy: true },
)

const internal = computed(() => data.value?.internal)
const gaId = useRuntimeConfig().public.gaMeasurementId
const gaBase = 'https://analytics.google.com/analytics/web/#/a411527435p558430396'
const gaReports = [
  { href: `${gaBase}/reports/intelligenthome`, label: 'Informes', hint: 'Usuarios, vistas y tiempo de interacción' },
  { href: `${gaBase}/reports/realtime`, label: 'Tiempo real', hint: 'Visitas de los últimos minutos' },
  { href: `${gaBase}/reports/explorer?params=_u..nav%3Dmaui&r=all-pages-and-screens`, label: 'Páginas', hint: 'Qué fichas concentran la lectura' },
  { href: `${gaBase}/reports/explorer?params=_u..nav%3Dmaui&r=tech-details`, label: 'Dispositivo', hint: 'Móvil y escritorio' },
  { href: `${gaBase}/reports/explorer?params=_u..nav%3Dmaui&r=lifecycle-traffic-acquisition-v2`, label: 'Adquisición', hint: 'De dónde llega la audiencia' },
  { href: `${gaBase}/admin/debugview`, label: 'DebugView', hint: 'Pruebas locales, fuera del kit' },
]

definePageMeta({ layout: 'default' })
useSeoMeta({ title: 'Métricas de cartera', robots: 'noindex,nofollow' })
</script>

<template>
  <div class="read-page admin-metrics">
    <header>
      <h1>Métricas de cartera</h1>
      <p>Vista interna de agregados editoriales. No incluye datos personales de lectores.</p>
      <button v-if="error" type="button" class="btn btn--primary" @click="refresh()">Reintentar</button>
    </header>

    <section class="media-kit" aria-labelledby="audience-title">
      <h2 id="audience-title">Audiencia para publicidad</h2>
      <p>
        Google Analytics de <strong>Resonaria.cl</strong>, medición <span class="num">{{ gaId }}</span>.
        El kit usa totales: usuarios, sesiones, vistas, tiempo de interacción, páginas, dispositivo, país y ciudad.
        La dimensión <strong>Categoría</strong> parte el tráfico editorial y <strong>Ficha</strong> identifica cada tema.
      </p>
      <ul class="ga-links">
        <li v-for="report in gaReports" :key="report.href">
          <a :href="report.href" target="_blank" rel="noopener noreferrer">{{ report.label }}</a>
          <span>{{ report.hint }}</span>
        </li>
      </ul>
      <p class="media-kit__note">Los informes se llenan después del próximo despliegue. Las visitas de desarrollo no entran al kit.</p>
    </section>

    <p v-if="status === 'pending'" role="status">Cargando…</p>

    <template v-else-if="data">
      <section aria-labelledby="summary-title">
        <h2 id="summary-title">Resumen público</h2>
        <dl class="metrics-dl">
          <div v-for="(value, key) in data.data.summary" :key="key">
            <dt>{{ key }}</dt>
            <dd class="num">{{ value }}</dd>
          </div>
        </dl>
      </section>

      <section v-if="internal" aria-labelledby="internal-title">
        <h2 id="internal-title">Desglose interno</h2>
        <pre class="metrics-json">{{ JSON.stringify(internal, null, 2) }}</pre>
      </section>
    </template>
  </div>
</template>

<style scoped>
.admin-metrics {
  max-width: 960px;
}

.metrics-dl {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 12px;
  margin: 16px 0 0;
}

.metrics-dl div {
  padding: 12px;
  border: 1px solid var(--hairline);
  border-radius: var(--radius-sm);
  background: var(--surface);
}

.metrics-dl dt {
  font-size: 12px;
  color: var(--muted);
  text-transform: capitalize;
}

.metrics-dl dd {
  margin: 4px 0 0;
  font-size: 22px;
  font-weight: 650;
}

.metrics-json {
  overflow: auto;
  padding: 16px;
  border-radius: var(--radius-sm);
  background: var(--canvas);
  font-size: 12px;
  line-height: 1.45;
}

.media-kit {
  margin-top: 28px;
  padding: 16px;
  border: 1px solid var(--hairline);
  border-radius: var(--radius-sm);
  background: var(--surface);
}

.media-kit p {
  max-width: 68ch;
}

.ga-links {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 12px;
  margin: 16px 0;
  padding: 0;
  list-style: none;
}

.ga-links li {
  display: flex;
  flex-direction: column;
  gap: 4px;
  padding: 12px;
  border: 1px solid var(--hairline);
  border-radius: var(--radius-sm);
  background: var(--canvas);
}

.ga-links span,
.media-kit__note {
  color: var(--muted);
  font-size: 13px;
}
</style>
