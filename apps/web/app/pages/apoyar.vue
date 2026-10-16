<script setup lang="ts">
definePageMeta({ layout: 'default' })
const { name: portalName, supportUrl } = usePortalSite()
usePageSeo({
  title: 'Apoyar el proyecto',
  description: 'Cómo se financia el portal y qué reglas de independencia aplicamos a aportes y patrocinios.',
})
</script>

<template>
  <div class="doc">
    <header class="doc__head">
      <h1>Apoyar el proyecto</h1>
      <p class="doc__lede">{{ portalName }} es de acceso abierto: el contenido no está detrás de un pago. El financiamiento y los patrocinios se identificarán con claridad y no podrán intervenir en las decisiones editoriales.</p>
    </header>

    <section class="doc__section" aria-labelledby="ap-estado">
      <h2 id="ap-estado">Estado actual</h2>
      <div class="doc__body">
        <p>Puedes apoyar el desarrollo del software open source en Ko-fi:</p>
        <p><a class="doc__cta" :href="supportUrl" target="_blank" rel="noopener noreferrer">Apoyar en Ko-fi</a></p>
        <p class="doc__note">Los aportes no dan influencia editorial sobre {{ portalName }}. Publicaremos un informe periódico de ingresos y gastos agregados cuando corresponda.</p>
      </div>
    </section>

    <section class="doc__section" aria-labelledby="ap-uso">
      <h2 id="ap-uso">Para qué se usará</h2>
      <div class="doc__body">
        <dl class="doc__defs">
          <div><dt>Operación</dt><dd>Servidores, dominio, respaldos y correo.</dd></div>
          <div><dt>Investigación</dt><dd>Tiempo para revisar documentos, comparar presupuestos y actualizar los temas.</dd></div>
          <div><dt>Mantenimiento</dt><dd>Mejoras al portal y corrección de errores.</dd></div>
        </dl>
        <p class="doc__note">Publicaremos una meta mensual y un informe periódico de ingresos y gastos agregados.</p>
      </div>
    </section>

    <section class="doc__section" aria-labelledby="ap-independencia">
      <h2 id="ap-independencia">Independencia</h2>
      <div class="doc__body">
        <ul class="doc__list">
          <li>Aportar no da influencia sobre qué temas se eligen, cómo se redactan ni cómo se corrigen.</li>
          <li>Un patrocinador no puede aprobar, modificar, retrasar ni retirar un tema.</li>
          <li>No aceptamos financiamiento de partidos, candidaturas ni autoridades que cubrimos.</li>
          <li>No vendemos el patrocinio de un tema que evalúe al propio patrocinador.</li>
          <li>Publicamos quién patrocina, durante qué período y de qué tipo es el aporte.</li>
        </ul>
      </div>
    </section>

    <section class="doc__section" aria-labelledby="ap-publicidad">
      <h2 id="ap-publicidad">Si hay publicidad</h2>
      <div class="doc__body">
        <ul class="doc__list">
          <li>Como máximo un anuncio por página, rotulado como “Publicidad” o “Patrocinio”.</li>
          <li>Nunca entre el título y el estado de un tema: siempre después de la explicación principal.</li>
          <li>Sin ventanas emergentes, anuncios flotantes, videos automáticos ni bloqueos de lectura.</li>
          <li>Sin anuncios ni audiencias según afiliación o ideología política.</li>
        </ul>
      </div>
    </section>

    <section class="doc__section" aria-labelledby="ap-privacidad">
      <h2 id="ap-privacidad">Privacidad</h2>
      <div class="doc__body">
        <p>No publicaremos nombres de quienes aporten salvo que lo autoricen expresamente, y no compartiremos listas de lectores con patrocinadores. Medimos visitas agregadas con Google Analytics (páginas, dispositivo y ciudad) para informar el espacio publicitario. No armamos audiencias por afiliación o ideología.</p>
      </div>
    </section>
  </div>
</template>
