import type { PublicCaseListItem } from '~/composables/usePublicApi'
import { CHANGE_TYPE_LABELS, EFFECT_LABELS, EVIDENCE_LABELS, label } from '~/utils/topicLabels'
import { isDecisionFollowUp } from '~/utils/topicSignals'

export function tracksDecisionOnCard(topic: PublicCaseListItem) {
  return isDecisionFollowUp({
    change_types: topic.change_types,
    evidence_status: topic.evidence_status,
    observed_effect: topic.observed_effect,
  })
}

export function hasCardMetaMarks(topic: PublicCaseListItem) {
  return (
    topic.change_types.length > 0
    || (topic.evidence_status && topic.evidence_status !== 'unknown')
    || (topic.observed_effect && topic.observed_effect !== 'unknown')
  )
}

export function pageviewsMarkLabel(topic: PublicCaseListItem) {
  const n = topic.pageviews ?? 0
  return n === 1
    ? '1 lectura de esta ficha en el sitio.'
    : `${n.toLocaleString('es-CL')} lecturas de esta ficha en el sitio.`
}

export function sourcesMarkLabel(topic: PublicCaseListItem) {
  const n = topic.sources_count
  return n === 1
    ? '1 fuente documental: leyes, informes o notas para verificar.'
    : `${n} fuentes documentales: leyes, informes o notas para verificar.`
}

export function reactionsMarkLabel(topic: PublicCaseListItem) {
  const n = topic.reactions_count
  return n === 1
    ? '1 publicación en redes. Es un testimonio; no prueba cifras.'
    : `${n} publicaciones en redes. Son testimonios; no prueban cifras.`
}

export function repliesMarkLabel(topic: PublicCaseListItem) {
  const n = topic.claims_count ?? 0
  return n === 1
    ? '1 comentario contrastado con fuentes. Abre la ficha en Comentarios y respuestas.'
    : `${n} comentarios contrastados con fuentes. Abre la ficha en Comentarios y respuestas.`
}

export const comparisonMarkLabel = 'Compara cómo era hasta ahora y qué quedaría con el cambio.'

export function tipoMarkLabel(tipo: string) {
  return `Tipo de cambio: ${label(CHANGE_TYPE_LABELS, tipo).toLowerCase()}.`
}

export function evidenceMarkLabel(topic: PublicCaseListItem) {
  return `Evidencia documental: ${label(EVIDENCE_LABELS, topic.evidence_status).toLowerCase()}.`
}

export function effectMarkLabel(topic: PublicCaseListItem) {
  return `Efecto observado: ${label(EFFECT_LABELS, topic.observed_effect).toLowerCase()}.`
}

export function useCatalogTopicMarkTip() {
  const markTip = ref<{ label: string, x: number, y: number } | null>(null)

  function showMarkTip(event: MouseEvent | FocusEvent, tipLabel: string) {
    const el = event.currentTarget
    if (!(el instanceof HTMLElement)) return
    const box = el.getBoundingClientRect()
    markTip.value = {
      label: tipLabel,
      x: box.right,
      y: box.top - 8,
    }
  }

  function hideMarkTip() {
    markTip.value = null
  }

  return { markTip, showMarkTip, hideMarkTip }
}
