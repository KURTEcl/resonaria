export type TopicSection = {
  title: string
  lead: string
  body: string[]
}

export type CitedPart =
  | { type: 'text'; value: string }
  | { type: 'cite'; n: number }
  | { type: 'strong'; value: string }
  | { type: 'gain'; value: string }
  | { type: 'loss'; value: string }
  | { type: 'note'; slug: string; value: string }

/** $ solo abre una cifra. {-$menos} se lee como {-menos}. */
export function normalizeMarks(text: string): string {
  return text.replace(/(\{[+-])\$(?=\p{L})/gu, '$1')
}

export function splitCitedText(text: string): CitedPart[] {
  text = normalizeMarks(text)
  const parts: CitedPart[] = []
  const re = /\[\[([a-z0-9][a-z0-9-]*)(?:\|([^\]]+))?\]\]|\[(\d+)\]|\{\+([^}]+)\}|\{-([^}]+)\}|\*\*([^*]+)\*\*/g
  let last = 0

  for (const match of text.matchAll(re)) {
    const index = match.index ?? 0
    if (index > last) {
      parts.push({ type: 'text', value: text.slice(last, index) })
    }
    if (match[1] !== undefined) {
      parts.push({ type: 'note', slug: match[1], value: match[2] ?? '' })
    }
    else if (match[3] !== undefined) {
      parts.push({ type: 'cite', n: Number(match[3]) })
    }
    else if (match[4] !== undefined) {
      parts.push({ type: 'gain', value: match[4] })
    }
    else if (match[5] !== undefined) {
      parts.push({ type: 'loss', value: match[5] })
    }
    else {
      parts.push({ type: 'strong', value: match[6] ?? '' })
    }
    last = index + match[0].length
  }

  if (last < text.length) {
    parts.push({ type: 'text', value: text.slice(last) })
  }

  return parts
}

export function textPolarity(
  text: string | null | undefined,
  prefer?: 'gain' | 'loss',
): 'gain' | 'loss' | null {
  if (!text) return null
  const loss = (text.match(/\{-/g) ?? []).length
  const gain = (text.match(/\{\+/g) ?? []).length
  if (prefer === 'gain' && gain) return 'gain'
  if (prefer === 'loss' && loss) return 'loss'
  if (loss && !gain) return 'loss'
  if (gain && !loss) return 'gain'
  return null
}

/** Secciones del detail que solo repiten la bibliografía ya en `sources`. */
export function isSourcesBibliographySection(title: string): boolean {
  const t = title.trim().toLowerCase()
  return t === 'fuentes'
    || t.startsWith('fuentes ')
    || t.startsWith('bibliograf')
}

export function narrativeDetailSections(sections: TopicSection[]): TopicSection[] {
  return sections.filter((section) => !isSourcesBibliographySection(section.title))
}

export function parseDetailSections(detail: string): TopicSection[] {
  const text = detail.trim()
  if (!text) return []

  if (!/^## /m.test(text)) {
    const paragraphs = text.split(/\n\n+/).map((part) => part.trim()).filter(Boolean)
    return [{
      title: 'En detalle',
      lead: paragraphs[0] ?? '',
      body: paragraphs.slice(1),
    }]
  }

  return text
    .split(/^## /m)
    .map((chunk) => chunk.trim())
    .filter(Boolean)
    .map((chunk) => {
      const newline = chunk.indexOf('\n')
      const title = (newline === -1 ? chunk : chunk.slice(0, newline)).trim()
      const rest = newline === -1 ? '' : chunk.slice(newline).trim()
      const paragraphs = rest.split(/\n\n+/).map((part) => part.trim()).filter(Boolean)
      return {
        title,
        lead: paragraphs[0] ?? '',
        body: paragraphs.slice(1),
      }
    })
}

export function stripMarkedText(text: string | null | undefined): string {
  if (!text) return ''
  return normalizeMarks(text)
    .replace(/\{\+([^}]+)\}/g, '$1')
    .replace(/\{-([^}]+)\}/g, '$1')
    .replace(/\*\*([^*]+)\*\*/g, '$1')
    .replace(/\[\[([a-z0-9][a-z0-9-]*)(?:\|([^\]]+))?\]\]/g, (_, slug: string, label?: string) => label || slug)
    .replace(/\[(\d+)\]/g, '')
    .replace(/^##\s+/gm, '')
    .replace(/\s+/g, ' ')
    .trim()
}

export function sourceChipLabel(title: string): string {
  const parts = splitSourceTitle(title)
  const label = parts.detail
  return label.length > 42 ? `${label.slice(0, 40).trimEnd()}…` : label
}

/** Títulos tipo «DIPRES — Ley…» o «Emol: alerta…». */
export function splitSourceTitle(title: string): { publisher: string | null; headline: string } {
  const trimmed = title.trim()
  const em = trimmed.match(/^(.+?)\s[—–]\s+(.+)$/)
  if (em) {
    return { publisher: em[1].trim(), headline: em[2].trim() }
  }
  const colon = trimmed.match(/^([^:]{2,48}):\s+(.+)$/)
  if (colon) {
    return { publisher: colon[1].trim(), headline: colon[2].trim() }
  }
  return { publisher: null, headline: trimmed }
}

export function sourceHost(url: string): string {
  try {
    return new URL(url).hostname.replace(/^www\./i, '')
  }
  catch {
    return ''
  }
}
