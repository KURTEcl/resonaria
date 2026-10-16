export const REACTION_AUTHOR_KIND_OPTIONS = [
  { value: 'politico', label: 'Figura política' },
  { value: 'periodista', label: 'Periodista' },
  { value: 'persona_natural', label: 'Persona natural' },
  { value: 'medio_oficial', label: 'Medio o cuenta oficial' },
  { value: 'medio_comunicacion', label: 'Medio de comunicación' },
  { value: 'organizacion', label: 'Organización o colectivo' },
  { value: 'sin_clasificar', label: 'Sin clasificar' },
] as const

export const REACTION_AUTHOR_KIND_LABELS: Record<string, string> = Object.fromEntries(
  REACTION_AUTHOR_KIND_OPTIONS.map((o) => [o.value, o.label]),
)

export function reactionAuthorKindLabel(value: string | null | undefined) {
  if (!value || value === 'sin_clasificar') return null
  return REACTION_AUTHOR_KIND_LABELS[value] ?? value.replace(/_/g, ' ')
}
