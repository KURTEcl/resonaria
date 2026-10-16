<script setup lang="ts">
definePageMeta({ layout: 'default' })
usePageSeo({
  title: 'Fuentes',
  description: 'Qué fuentes usamos para documentar cada tema y cómo ponderamos documentos oficiales, prensa y redes sociales.',
})

const levels = [
  { rank: 1, level: 'Primario documental', sources: 'Leyes, decretos, resoluciones, partidas, glosas, oficios, actas y votaciones.', use: 'Acreditar el acto, el monto, el texto y el estado.' },
  { rank: 2, level: 'Primario explicativo', sources: 'Organismo ejecutor, ministerio, ChileAtiende y evaluaciones oficiales.', use: 'Describir cómo operaba el programa y los argumentos institucionales.' },
  { rank: 3, level: 'Periodístico', sources: 'Reportajes, noticias, entrevistas y cobertura regional.', use: 'Descubrir temas, dar contexto y ubicar antecedentes.' },
  { rank: 4, level: 'Social y testimonial', sources: 'Publicaciones de autoridades, organizaciones y personas afectadas.', use: 'Documentar declaraciones y experiencias, con sus límites.' },
]

const directory = [
  { name: 'Dirección de Presupuestos', href: 'https://www.dipres.gob.cl/', what: 'Partidas, asignaciones, glosas y evaluaciones de programas.' },
  { name: 'LeyChile', href: 'https://www.bcn.cl/leychile/', what: 'Normas y sus versiones, para reconstruir cambios normativos.' },
  { name: 'Datos BCN', href: 'https://datos.bcn.cl/', what: 'Datos y servicios publicados por la Biblioteca del Congreso.' },
  { name: 'Cámara de Diputadas y Diputados', href: 'https://www.camara.cl/', what: 'Proyectos, oficios, informes y votaciones.' },
  { name: 'Senado', href: 'https://www.senado.cl/', what: 'Proyectos, comisiones y votaciones.' },
  { name: 'Diario Oficial', href: 'https://www.diariooficial.interior.gob.cl/', what: 'Publicación de actos y normas.' },
  { name: 'ChileAtiende', href: 'https://www.chileatiende.gob.cl/', what: 'Fichas de beneficios: requisitos y acceso antes del cambio.' },
]
</script>

<template>
  <article class="read-page">
    <header class="read-page__head">
      <h1>Fuentes</h1>
      <p class="read-page__lede">Cada afirmación relevante debe poder verificarse. Usamos documentos oficiales para acreditar lo que se decidió; prensa y redes ayudan a entender el contexto y cómo se vive el cambio.</p>
      <nav class="read-page__nav" aria-label="En esta página">
        <a href="#jerarquia">Jerarquía</a>
        <a href="#directorio">Dónde buscamos</a>
        <a href="#otras">Prensa y redes</a>
      </nav>
    </header>

    <section id="jerarquia" class="read-page__block" aria-labelledby="f-jerarquia">
      <header class="read-page__block-head">
        <h2 id="f-jerarquia">Cuánto pesa cada fuente</h2>
        <p>De mayor a menor peso probatorio. Un testimonio o una noticia no sustituyen un acto administrativo ni una partida presupuestaria.</p>
      </header>
      <ol class="read-levels">
        <li v-for="row in levels" :key="row.level">
          <span class="read-levels__rank">{{ row.rank }}</span>
          <strong>{{ row.level }}</strong>
          <span>{{ row.sources }}</span>
          <span>{{ row.use }}</span>
        </li>
      </ol>
      <p class="read-page__note">Que muchas noticias repitan una afirmación no la vuelve más cierta: pueden venir del mismo reportaje. Registramos el origen y si hay corroboración independiente.</p>
    </section>

    <section id="directorio" class="read-page__block" aria-labelledby="f-donde">
      <header class="read-page__block-head">
        <h2 id="f-donde">Dónde buscamos</h2>
        <p>Portales de partida. Cada tema enlaza al documento concreto, con página, artículo o glosa.</p>
      </header>
      <ul class="read-dir">
        <li v-for="item in directory" :key="item.href">
          <a :href="item.href" target="_blank" rel="noopener noreferrer">{{ item.name }}</a>
          <span>{{ item.what }}</span>
        </li>
      </ul>
    </section>

    <section id="otras" class="read-page__block" aria-labelledby="f-otras">
      <h2 id="f-otras" class="sr-only">Prensa y redes</h2>
      <div class="read-split">
        <div>
          <h3 id="f-prensa">Prensa</h3>
          <p>Evaluamos cada pieza por autoría, evidencia enlazada, investigación propia, correcciones y distinción entre noticia y opinión. La línea editorial de un medio no es garantía ni motivo de descarte.</p>
          <p>Guardamos título, medio, autor cuando consta, fecha y enlace, con un resumen propio. No reproducimos artículos completos.</p>
        </div>
        <div>
          <h3 id="f-redes">Redes sociales</h3>
          <p>Las publicaciones que aparecen en cada tema muestran cómo se discute y cómo lo viven las personas.</p>
          <p>Un testimonio puede ilustrar una experiencia, pero no prueba una cifra nacional.</p>
        </div>
      </div>
    </section>

    <nav class="read-page__related" aria-label="Páginas relacionadas">
      <NuxtLink to="/metodologia">Metodología<span>Cómo usamos estas fuentes al armar un tema.</span></NuxtLink>
      <NuxtLink to="/correcciones">Correcciones<span>Cómo reportar un error en una ficha.</span></NuxtLink>
    </nav>
  </article>
</template>
