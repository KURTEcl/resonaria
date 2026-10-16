/** Etiquetas alineadas con `config/profiles.php` (kinds). */
export const PROFILE_KIND_LABELS: Record<string, string> = {
  ejecutivo: 'Ejecutivo',
  diputado: 'Diputado',
  senador: 'Senador',
  legislador: 'Legislador',
  gobernador: 'Gobernador regional',
  alcalde: 'Alcalde',
  concejal: 'Concejal',
  seremi: 'Seremi',
  funcionario: 'Autoridad del Estado',
  dirigente: 'Dirigente político',
  lider: 'Líder',
  periodista: 'Periodista',
  economista: 'Economista',
  comentarista: 'Comentarista político',
  profesional: 'Profesional',
  organizacion: 'Organización',
  interes_publico: 'Interés público',
  otro: 'Figura pública',
}

export function profileKindLabel(kind: string | null | undefined): string {
  if (!kind) return ''
  return PROFILE_KIND_LABELS[kind] ?? kind.replace(/_/g, ' ')
}
