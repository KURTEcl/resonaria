<script setup lang="ts">
definePageMeta({ layout: 'default' })
usePageSeo({
  title: 'Metodología',
  description: 'Cómo identificamos, comparamos y revisamos cada decisión pública antes de publicarla.',
})

const fields = [
  { term: 'Etapa de la decisión', text: 'En qué punto del procedimiento está: anunciada, en tramitación, aprobada, vigente, en aplicación o revertida.' },
  { term: 'Evidencia', text: 'Qué documentos respaldan el tema y si alcanzan para acreditar el acto, el monto y el estado.' },
  { term: 'Efecto observado', text: 'Si ya hay efectos medidos, estimados o todavía ninguno. Una medida vigente puede no tener impacto cuantificado aún.' },
  { term: 'Publicación', text: 'Que un tema esté publicado no significa que esté verificado, ni que algo aprobado sea una pérdida ya aplicada.' },
]

const phases = [
  {
    title: 'Identificar',
    lead: 'Sin una situación anterior y un acto localizable no hay comparación.',
    steps: [
      { n: 1, title: 'Definir qué se evalúa', text: 'Una norma, un programa, un componente, un convenio o una institución. Relacionamos las piezas para no contar dos veces el mismo cambio.' },
      { n: 2, title: 'Acreditar la situación anterior', text: 'Período, requisitos, cobertura, prestaciones, recursos y origen.' },
      { n: 3, title: 'Localizar la decisión', text: 'Autor, organismo, tipo de acto, fecha, documento y localizador. Una declaración de prensa puede ser un anuncio, no el acto que materializa el cambio.' },
    ],
  },
  {
    title: 'Comparar',
    lead: 'La misma unidad, el mismo año y el mismo perímetro.',
    steps: [
      { n: 4, title: 'Establecer la etapa', text: 'Desde el anuncio hasta la aplicación o la reversión. La etapa, la evidencia y el efecto observado se registran por separado.' },
      { n: 5, title: 'Comparar antes y después', text: 'Distinguimos presupuesto propuesto, ley aprobada y ejecución, y revisamos si los recursos reaparecen en otra partida.' },
      { n: 6, title: 'Evaluar población y causalidad', text: 'Separamos beneficiarios del año anterior, población potencial y personas afectadas comprobadas. Explicitamos los supuestos de cualquier estimación.' },
    ],
  },
  {
    title: 'Publicar',
    lead: 'Cada afirmación central lleva respaldo, y el tema queda con fecha de revisión.',
    steps: [
      { n: 7, title: 'Examinar argumentos y alternativas', text: 'Registramos la justificación oficial y la evaluación citada. Una promesa de reemplazo figura como promesa hasta que tenga respaldo.' },
      { n: 8, title: 'Atribuir responsabilidades', text: 'Separamos propuesta del Ejecutivo, indicaciones parlamentarias, votación, acto administrativo y ejecución.' },
      { n: 9, title: 'Redactar y revisar', text: 'Si solo hay respaldo periodístico, lo atribuimos al medio y lo decimos.' },
      { n: 10, title: 'Programar el seguimiento', text: 'Cada tema tiene un próximo hito y una fecha de revisión. Si la fecha pasa sin revisión, se marca como actualización pendiente.' },
    ],
  },
]

const statusGroups = [
  {
    estado: 'en_discusion',
    stages: [
      { name: 'Anunciada', meaning: 'Existe un anuncio atribuible; falta el acto o texto formal.' },
      { name: 'Propuesta formal', meaning: 'Existe un texto o instrumento sometido a decisión.' },
      { name: 'En tramitación', meaning: 'El texto se discute y puede cambiar.' },
    ],
  },
  {
    estado: 'aprobado',
    stages: [
      { name: 'Aprobada, pendiente de vigencia', meaning: 'La aprobación está documentada; faltan hitos para aplicarla.' },
      { name: 'Vigente', meaning: 'Se cumplen los hitos de entrada en vigor, sin efectos operativos documentados.' },
    ],
  },
  {
    estado: 'en_aplicacion',
    stages: [
      { name: 'En aplicación', meaning: 'Hay evidencia de ejecución o de efectos operativos.' },
    ],
  },
  {
    estado: 'revertido',
    stages: [
      { name: 'Revertida o sustituida', meaning: 'Otra decisión modifica su resultado. Indicamos el alcance.' },
    ],
  },
]

const blocks = [
  'No hay una situación anterior identificable.',
  'Hay una acusación sin respaldo.',
  'Hay un número sin origen.',
  'La atribución es dudosa.',
  'El estado es contradictorio.',
]
</script>

<template>
  <article class="read-page">
    <header class="read-page__head">
      <h1>Metodología</h1>
      <p class="read-page__lede">Cada tema parte de una decisión concreta y documentada. Comparamos la situación anterior con la que propone o produce esa decisión, y decimos en qué etapa está y qué evidencia la respalda.</p>
      <nav class="read-page__nav" aria-label="En esta página">
        <a href="#separado">Qué separamos</a>
        <a href="#pasos">Cómo se arma un tema</a>
        <a href="#estados">Estados públicos</a>
        <a href="#no-publicamos">Qué no publicamos</a>
      </nav>
    </header>

    <section id="separado" class="read-page__block" aria-labelledby="m-separamos">
      <h2 id="m-separamos">Cuatro cosas que no se mezclan</h2>
      <dl class="read-fields">
        <div v-for="field in fields" :key="field.term">
          <dt>{{ field.term }}</dt>
          <dd>{{ field.text }}</dd>
        </div>
      </dl>
    </section>

    <section id="pasos" class="read-page__block" aria-labelledby="m-pasos">
      <header class="read-page__block-head">
        <h2 id="m-pasos">Cómo se arma un tema</h2>
        <p>Diez pasos, en orden. Los tres primeros ubican el cambio; los siguientes lo miden; los últimos deciden si se publica y cuándo se vuelve a revisar.</p>
      </header>
      <div class="read-phases">
        <section v-for="phase in phases" :key="phase.title" class="read-phase" :aria-labelledby="`fase-${phase.title}`">
          <div>
            <h3 :id="`fase-${phase.title}`">{{ phase.title }}</h3>
            <p class="read-phase__lead">{{ phase.lead }}</p>
          </div>
          <ol class="read-steps">
            <li v-for="step in phase.steps" :key="step.n">
              <span class="read-steps__n">{{ step.n }}</span>
              <div>
                <strong>{{ step.title }}</strong>
                <span>{{ step.text }}</span>
              </div>
            </li>
          </ol>
        </section>
      </div>
    </section>

    <section id="estados" class="read-page__block" aria-labelledby="m-etapas">
      <header class="read-page__block-head">
        <h2 id="m-etapas">De la etapa precisa al estado público</h2>
        <p>En el portal se ven cuatro estados. Cada uno resume una o más etapas del procedimiento, que también aparecen en la ficha.</p>
      </header>
      <div class="read-statuses">
        <section v-for="group in statusGroups" :key="group.estado" class="read-status-group">
          <h3><ContentStatusBadge :estado="group.estado" /></h3>
          <ul>
            <li v-for="stage in group.stages" :key="stage.name">
              <strong>{{ stage.name }}</strong>
              <span>{{ stage.meaning }}</span>
            </li>
          </ul>
        </section>
      </div>
      <p class="read-page__note">El estado nunca se deduce de palabras en el título. Si una medida no está consumada usamos lenguaje condicional: “en riesgo”, “propuesta”, “contempla”.</p>
    </section>

    <section id="no-publicamos" class="read-page__block" aria-labelledby="m-bloqueos">
      <h2 id="m-bloqueos">Cuándo no publicamos</h2>
      <ul class="read-blocks">
        <li v-for="item in blocks" :key="item">{{ item }}</li>
      </ul>
      <p class="read-page__note">Sí publicamos fichas limitadas cuando exponen con claridad lo que se sabe y lo que falta por confirmar.</p>
    </section>

    <nav class="read-page__related" aria-label="Páginas relacionadas">
      <NuxtLink to="/fuentes">Fuentes<span>Qué documentos usamos y cuánto pesa cada uno.</span></NuxtLink>
      <NuxtLink to="/correcciones">Correcciones<span>Cómo registramos los cambios en un tema publicado.</span></NuxtLink>
    </nav>
  </article>
</template>
