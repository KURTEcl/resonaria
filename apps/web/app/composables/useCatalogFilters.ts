export const AREA_OPTIONS = [
  { value: 'salud', label: 'Salud' },
  { value: 'educacion', label: 'Educación' },
  { value: 'trabajo-y-pensiones', label: 'Trabajo y pensiones' },
  { value: 'vivienda', label: 'Vivienda' },
  { value: 'proteccion-social', label: 'Protección social' },
  { value: 'estado-y-servicios', label: 'Estado y servicios' },
] as const

export const ESTADO_OPTIONS = [
  { value: 'en_discusion', label: 'En discusión' },
  { value: 'aprobado', label: 'Aprobado' },
  { value: 'en_aplicacion', label: 'En aplicación' },
  { value: 'revertido', label: 'Revertido' },
] as const

export const TIPO_OPTIONS = [
  { value: 'presupuesto', label: 'Presupuesto' },
  { value: 'cobertura', label: 'Cobertura' },
  { value: 'requisitos', label: 'Requisitos' },
  { value: 'cierre', label: 'Cierre de programa' },
] as const

/** Filtros de portada: tipo de pieza editorial. */
export const PIEZA_OPTIONS = [
  { value: 'actualidad', label: 'Actualidad y análisis' },
  { value: 'seguimiento', label: 'Seguimiento de decisión' },
] as const

/** Filtros de portada: qué trae la ficha. */
export const INCLUYE_OPTIONS = [
  { value: 'fuentes', label: 'Con fuentes' },
  { value: 'redes', label: 'Con conversación en redes' },
  { value: 'comparacion', label: 'Con comparación' },
] as const

export type CatalogFilterField = 'pieza' | 'incluye' | 'perfil'

export type CatalogFilters = {
  area: string[]
  pieza: string[]
  incluye: string[]
  perfil: string[]
  q: string
}

export function parseListParam(value: unknown): string[] {
  if (Array.isArray(value)) return value.filter(Boolean).map(String)
  if (typeof value === 'string' && value) return value.split(',').map((s) => s.trim()).filter(Boolean)
  return []
}

export function useCatalogFilters() {
  const route = useRoute()
  const router = useRouter()

  const filters = computed<CatalogFilters>(() => ({
    area: parseListParam(route.query.area),
    pieza: parseListParam(route.query.pieza),
    incluye: parseListParam(route.query.incluye),
    perfil: parseListParam(route.query.perfil),
    q: typeof route.query.q === 'string' ? route.query.q : '',
  }))

  function toApiQuery(f: CatalogFilters) {
    const query: Record<string, string | string[]> = {}
    if (f.q) query.q = f.q
    if (f.area.length) query.area = f.area
    if (f.pieza.length) query.pieza = f.pieza
    if (f.incluye.length) query.incluye = f.incluye
    if (f.perfil.length) query.perfil = f.perfil
    return query
  }

  function applyFilters(next: CatalogFilters, path = route.path) {
    const query: Record<string, string | undefined> = {}
    if (next.q) query.q = next.q
    if (next.area.length) query.area = next.area.join(',')
    if (next.pieza.length) query.pieza = next.pieza.join(',')
    if (next.incluye.length) query.incluye = next.incluye.join(',')
    if (next.perfil.length) query.perfil = next.perfil.join(',')
    router.push({ path, query })
  }

  function clearFilters(path = route.path) {
    router.push({ path, query: {} })
  }

  function toggleInList(list: string[], value: string) {
    return list.includes(value) ? list.filter((v) => v !== value) : [...list, value]
  }

  return { filters, toApiQuery, applyFilters, clearFilters, toggleInList }
}
