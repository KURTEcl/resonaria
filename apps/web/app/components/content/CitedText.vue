<script setup lang="ts">
import type { PublicCaseLink, PublicCaseSource } from '~/composables/usePublicApi'
import { splitCitedText } from '~/utils/topicDetail'

const props = defineProps<{
  text: string
  sources: PublicCaseSource[]
  notes?: PublicCaseLink[]
}>()

const route = useRoute()
const { trackSourceClick } = useAnalytics()
const parts = computed(() => splitCitedText(props.text))
const notesBySlug = computed(() => {
  const map = new Map<string, PublicCaseLink>()
  for (const note of props.notes ?? []) {
    map.set(note.slug, note)
  }
  return map
})

function sourceAt(n: number): PublicCaseSource | undefined {
  return props.sources[n - 1]
}

function noteAt(slug: string): PublicCaseLink | undefined {
  return notesBySlug.value.get(slug)
}

function canLinkNote(slug: string): boolean {
  return props.notes === undefined || notesBySlug.value.has(slug)
}

function noteLabel(slug: string, value: string): string {
  return value || noteAt(slug)?.title || slug
}

function onCite() {
  trackSourceClick(String(route.params.slug || ''))
}
</script>

<template>
  <span class="cited"><template v-for="(part, index) in parts" :key="index"><template
    v-if="part.type === 'text'"
  >{{ part.value }}</template><strong
    v-else-if="part.type === 'strong'"
  >{{ part.value }}</strong><span
    v-else-if="part.type === 'gain'"
    class="val val--gain"
  >{{ part.value }}</span><span
    v-else-if="part.type === 'loss'"
    class="val val--loss"
  >{{ part.value }}</span><NuxtLink
    v-else-if="part.type === 'note' && canLinkNote(part.slug)"
    class="wiki-ref"
    :to="`/casos/${part.slug}`"
    :title="noteAt(part.slug)?.title || noteLabel(part.slug, part.value)"
  >{{ noteLabel(part.slug, part.value) }}</NuxtLink><template
    v-else-if="part.type === 'note'"
  >{{ noteLabel(part.slug, part.value) }}</template><a
    v-else-if="part.type === 'cite' && sourceAt(part.n)"
    class="cite-ref"
    :href="sourceAt(part.n)!.url"
    :title="sourceAt(part.n)!.title"
    rel="noopener noreferrer"
    target="_blank"
    @click="onCite"
  >{{ part.n }}</a><sup
    v-else-if="part.type === 'cite'"
    class="cite-ref cite-ref--missing"
  >{{ part.n }}</sup></template></span>
</template>

<style scoped>
.cited {
  font-variant-numeric: tabular-nums;
}

.cited strong {
  font-weight: 700;
  color: var(--ink);
}

.val {
  font-style: normal;
  font-weight: 700;
  padding: 0.05em 0.32em;
  border-radius: 4px;
}

.val--gain {
  color: var(--gain);
  background: var(--gain-soft);
}

.val--loss {
  color: var(--loss);
  background: var(--loss-soft);
}

.wiki-ref {
  color: var(--primary-hover);
  font-weight: 650;
  text-decoration: none;
  border-bottom: 1px dotted color-mix(in srgb, var(--primary) 55%, transparent);
}

.wiki-ref:hover,
.wiki-ref:focus-visible {
  color: var(--primary);
  border-bottom-style: solid;
}

.cite-ref {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 1.15rem;
  height: 1.15rem;
  margin: 0 0.14em;
  padding: 0 4px;
  border-radius: 999px;
  background: var(--primary-soft);
  color: var(--primary-hover);
  font-size: 11px;
  font-weight: 700;
  line-height: 1;
  text-decoration: none;
  vertical-align: 0.25em;
}

.cite-ref:hover,
.cite-ref:focus-visible {
  background: var(--primary);
  color: #fff;
}

.cite-ref--missing {
  background: var(--border);
  color: var(--muted);
}
</style>
