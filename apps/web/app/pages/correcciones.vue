<script setup lang="ts">
definePageMeta({ layout: 'default' })
usePageSeo({
  title: 'Correcciones',
  description: 'Cómo registramos y publicamos correcciones en Resonaria.',
})
</script>

<template>
  <div class="doc">
    <header class="doc__head">
      <h1>Correcciones</h1>
      <p class="doc__lede">Un tema puede cambiar después de publicado: aparece un documento nuevo, se restituye un programa o encontramos un error. Cuando el cambio es material, lo dejamos registrado con fecha, motivo y alcance.</p>
    </header>

    <section class="doc__section" aria-labelledby="c-historial">
      <h2 id="c-historial">Historial</h2>
      <div class="doc__body">
        <div class="notice"><p>Aún no hay correcciones registradas.</p></div>
      </div>
    </section>

    <section class="doc__section" aria-labelledby="c-registro">
      <h2 id="c-registro">Qué registramos</h2>
      <div class="doc__body">
        <dl class="doc__defs">
          <div><dt>Fecha</dt><dd>Cuándo se hizo el cambio en el tema.</dd></div>
          <div><dt>Motivo</dt><dd>Por qué cambió: error propio, documento nuevo, nueva etapa de la decisión o restitución.</dd></div>
          <div><dt>Alcance</dt><dd>Qué parte del tema cambió y si modifica su conclusión.</dd></div>
          <div><dt>Versión</dt><dd>Cada tema muestra su número de versión al final de la ficha.</dd></div>
        </dl>
        <p class="doc__note">Registramos también las restituciones y la continuidad parcial, aunque cambien la conclusión inicial del tema.</p>
      </div>
    </section>

    <section class="doc__section" aria-labelledby="c-reportar">
      <h2 id="c-reportar">Reportar un error</h2>
      <div class="doc__body">
        <p>El formulario de aportes y correcciones está en preparación. Mientras tanto, puedes escribirnos por <a href="https://www.instagram.com/resonaria.cl/" target="_blank" rel="noopener noreferrer">Instagram</a> o <a href="https://x.com/resonariacl" target="_blank" rel="noopener noreferrer">X</a> indicando el tema, la afirmación y la fuente que la contradice.</p>
        <p>Ningún aporte ni patrocinio da acceso privilegiado a correcciones o decisiones editoriales.</p>
      </div>
    </section>
  </div>
</template>
