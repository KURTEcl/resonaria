/** Número compacto para pastillas (≥1.000 → notación corta es-CL). */
export function formatCompactCount(value: number): string {
  const n = Math.max(0, Math.floor(value))
  if (n < 1000) return n.toLocaleString('es-CL')
  return new Intl.NumberFormat('es-CL', {
    notation: 'compact',
    compactDisplay: 'short',
    maximumFractionDigits: n >= 10_000 ? 0 : 1,
  }).format(n)
}
