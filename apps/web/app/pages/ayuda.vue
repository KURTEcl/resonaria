<script setup lang="ts">
definePageMeta({ layout: 'default' })
usePageSeo({
  title: 'Ayuda',
  description: 'Cómo buscar y leer los contenidos de Resonaria.',
})

const find = [
  { title: 'Buscar', text: 'Escribe el nombre de un programa, beneficio o institución en el buscador superior. La portada se actualiza al enviar.' },
  { title: 'Categorías en el menú lateral', text: 'Los íconos filtran por Salud, Educación, Trabajo y pensiones, Vivienda, Protección social o Estado y servicios. Puedes activar varias; vuelve a pulsar para quitar.' },
  { title: 'Formato e incluye', text: 'En la portada, junto al buscador, filtra por formato (actualidad o seguimiento de decisión) y por lo que trae la ficha: fuentes, conversación en redes o comparación.' },
  { title: 'Compartir o limpiar', text: 'La dirección guarda los filtros. Usa “Quitar filtros” sobre la lista o vuelve al inicio para ver todos los temas.' },
]

const read = [
  { title: 'Estado', badges: true, text: 'El sello resume la etapa pública. Cada uno lleva texto e ícono, no solo color.' },
  { title: 'Registro del tema', text: 'Bajo el título: etapa precisa, evidencia, efecto observado, tipo de cambio y fechas de revisión.' },
  { title: 'Antes y con el cambio', text: 'La comparación central. Se puede leer sin abrir videos ni enlaces externos.' },
  { title: 'En redes', text: 'Publicaciones seleccionadas sobre el tema. En el teléfono están en una pestaña; usa Anterior y Siguiente para recorrerlas.' },
]

const terms = [
  { term: 'Evidencia parcial', text: 'Hay documentos que respaldan parte del tema, pero falta acreditar algún monto, texto o etapa.' },
  { term: 'Evidencia documentada', text: 'Los documentos primarios acreditan el acto, el monto y el estado.' },
  { term: 'Efecto aún sin observar', text: 'Todavía no hay datos de lo que la medida produce en las personas. No significa que no tenga efecto.' },
  { term: 'Próxima revisión', text: 'Fecha en que volvemos a revisar el tema. Si pasa sin revisión, se marca como actualización pendiente.' },
]
</script>

<template>
  <article class="read-page">
    <header class="read-page__head">
      <h1>Ayuda</h1>
      <p class="read-page__lede">La portada reúne los temas en seguimiento. Desde ahí puedes buscar, filtrar y abrir cada ficha para ver qué cambia y en qué estado está la decisión.</p>
      <nav class="read-page__nav" aria-label="En esta página">
        <a href="#buscar">Buscar y filtrar</a>
        <a href="#leer">Leer un tema</a>
        <a href="#terminos">Términos de la ficha</a>
      </nav>
    </header>

    <section id="buscar" class="read-page__block" aria-labelledby="a-buscar">
      <header class="read-page__block-head">
        <h2 id="a-buscar">Buscar y filtrar</h2>
        <p>Todo parte de la portada. Los filtros se combinan entre sí.</p>
      </header>
      <ol class="read-rows">
        <li v-for="item in find" :key="item.title">
          <strong>{{ item.title }}</strong>
          <span>{{ item.text }}</span>
        </li>
      </ol>
    </section>

    <section id="leer" class="read-page__block" aria-labelledby="a-leer">
      <header class="read-page__block-head">
        <h2 id="a-leer">Leer un tema</h2>
        <p>Al abrir un tema, el orden de lectura es de arriba hacia abajo: estado, registro, comparación y, si aplica, redes.</p>
      </header>
      <ol class="read-rows">
        <li v-for="item in read" :key="item.title">
          <strong>{{ item.title }}</strong>
          <span v-if="item.badges" class="read-rows__badges">
            <ContentStatusBadge estado="en_discusion" />
            <ContentStatusBadge estado="aprobado" />
            <ContentStatusBadge estado="en_aplicacion" />
            <ContentStatusBadge estado="revertido" />
          </span>
          <span v-else>{{ item.text }}</span>
        </li>
      </ol>
    </section>

    <section id="terminos" class="read-page__block" aria-labelledby="a-terminos">
      <h2 id="a-terminos">Términos de la ficha</h2>
      <dl class="read-fields">
        <div v-for="item in terms" :key="item.term">
          <dt>{{ item.term }}</dt>
          <dd>{{ item.text }}</dd>
        </div>
      </dl>
      <p class="read-page__note">Para el criterio editorial completo, revisa <NuxtLink to="/metodologia">Metodología</NuxtLink>.</p>
    </section>

    <nav class="read-page__related" aria-label="Páginas relacionadas">
      <NuxtLink to="/metodologia">Metodología<span>Cómo armamos y revisamos cada tema.</span></NuxtLink>
      <NuxtLink to="/fuentes">Fuentes<span>Qué documentos usamos y cuánto pesa cada uno.</span></NuxtLink>
    </nav>
  </article>
</template>
