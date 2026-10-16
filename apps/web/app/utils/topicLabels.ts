import { ESTADO_OPTIONS, TIPO_OPTIONS } from '~/composables/useCatalogFilters'

export const STATUS_LABELS: Record<string, string> = Object.fromEntries(ESTADO_OPTIONS.map((o) => [o.value, o.label]))

export const CHANGE_TYPE_LABELS: Record<string, string> = Object.fromEntries(TIPO_OPTIONS.map((o) => [o.value, o.label]))

export const PROCEDURAL_LABELS: Record<string, string> = {
  anunciada: 'Anunciada',
  propuesta_formal: 'Propuesta formal',
  en_tramitacion: 'En tramitación',
  aprobada_pendiente: 'Aprobada, pendiente de vigencia',
  vigente: 'Vigente',
  en_aplicacion: 'En aplicación',
  suspendida: 'Suspendida o retirada',
  revertida: 'Revertida o sustituida',
}

export const EVIDENCE_LABELS: Record<string, string> = {
  unknown: 'Sin evaluar',
  partial: 'Parcial',
  documented: 'Documentada',
  complete: 'Documentada',
}

export const EFFECT_LABELS: Record<string, string> = {
  unknown: 'Aún sin observar',
  estimated: 'Estimado',
  observed: 'Observado',
}

export const SOURCE_KIND_LABELS: Record<string, string> = {
  documento: 'Oficial',
  prensa: 'Prensa',
  noticia: 'Noticia',
}

export const UPDATE_DIRECTION_LABELS: Record<string, string> = {
  riesgo: 'La medida avanza',
  reversion: 'Puede revertirse',
  incierto: 'Aún no se ve el rumbo',
}

export const CLAIM_VERDICT_LABELS: Record<string, string> = {
  se_sostiene: 'Se sostiene',
  no_se_sostiene: 'No se sostiene',
  a_medias: 'A medias',
  sin_respaldo: 'Falta el dato',
}

export const LINK_RELATION_LABELS: Record<string, string> = {
  contexto: 'Da contexto',
  consecuencia: 'Consecuencia',
  antecedente: 'Antecedente',
  relacionado: 'Relacionado',
}

export const UPDATE_KIND_LABELS: Record<string, string> = {
  tramite: 'Trámite',
  votacion: 'Votación',
  anuncio: 'Anuncio',
  redes: 'Conversación pública',
  fuente: 'Fuente',
  otro: 'Otro',
}

export function label(map: Record<string, string>, value: string | null | undefined, fallback = 'Por confirmar') {
  if (!value) return fallback
  return map[value] ?? value.replace(/_/g, ' ')
}

const dateFormat = new Intl.DateTimeFormat('es-CL', { day: 'numeric', month: 'short', year: 'numeric' })
const dateTimeFormat = new Intl.DateTimeFormat('es-CL', {
  day: 'numeric',
  month: 'short',
  year: 'numeric',
  hour: '2-digit',
  minute: '2-digit',
  hour12: false,
})

export function formatDate(value: string | null | undefined) {
  if (!value) return null
  const date = new Date(value.length === 10 ? `${value}T12:00:00` : value)
  if (Number.isNaN(date.getTime())) return null
  return dateFormat.format(date).replace('.', '')
}

export function formatDateTime(value: string | null | undefined) {
  if (!value) return null
  const date = new Date(value.length === 10 ? `${value}T12:00:00` : value)
  if (Number.isNaN(date.getTime())) return null
  return dateTimeFormat.format(date).replace(/\./g, '').replace(',', ', ')
}

export function formatMilestoneWhen(milestone: { occurred_on: string, occurred_at?: string | null }) {
  return formatDateTime(milestone.occurred_at) ?? formatDate(milestone.occurred_on)
}

export function relatedNotesLead(outgoing: number, incoming: number) {
  const total = outgoing + incoming
  if (total === 0) {
    return 'Todavía no hay otras notas de este portal enlazadas aquí.'
  }
  if (total === 1) {
    return 'Hay otra nota de este portal enlazada con este tema.'
  }
  return `Hay ${total} notas de este portal enlazadas con este tema.`
}

export function repliesLead(count: number) {
  if (count === 0) {
    return 'Todavía no contrastamos lo que circula sobre este tema.'
  }
  if (count === 1) {
    return '1 comentario que se da por cierto, respondido con fuentes y datos.'
  }
  return `${count} comentarios que se dan por ciertos, respondidos con fuentes y datos.`
}

export function followUpLead(
  milestones: Array<{ occurred_on: string; occurred_at?: string | null; label: string; direction?: string | null }>,
  options?: { developing?: boolean },
) {
  if (!milestones.length) {
    return options?.developing
      ? 'Todavía no hay entradas en la línea de tiempo. Cuando haya un hecho nuevo verificado, aparece aquí con fecha y hora.'
      : 'Todavía no hay hitos posteriores a esta ficha. Cuando el trámite o la conversación pública se mueva, aparece aquí.'
  }

  const latest = milestones[milestones.length - 1]
  const when = formatMilestoneWhen(latest)
  const stamp = when ? `Última entrada (${when})` : 'Última entrada'

  if (options?.developing) {
    return `${stamp}: ${latest.label}`
  }

  const legacyStamp = when ? `Según el último hito (${when})` : 'Según el último hito'
  if (latest.direction === 'riesgo') {
    return `${legacyStamp}, la propuesta negativa avanza. ${latest.label}`
  }
  if (latest.direction === 'reversion') {
    return `${legacyStamp}, hay señales de que la propuesta negativa puede revertirse. ${latest.label}`
  }
  return `${legacyStamp}, aún no se ve si la medida avanza o puede revertirse. ${latest.label}`
}
