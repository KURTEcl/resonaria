export type ReactionAuthorKind =
  | 'politico'
  | 'periodista'
  | 'persona_natural'
  | 'medio_oficial'
  | 'medio_comunicacion'
  | 'organizacion'
  | 'sin_clasificar'

export type PublicReaction = {
  platform: string
  url: string
  caption: string | null
  author_handle: string | null
  author_kind: ReactionAuthorKind
  preview_image_url?: string | null
}

export type PublicCategory = {
  slug: string
  title: string
  lead: string
}

export type PublicCaseListItem = {
  slug: string
  title: string
  hook: string | null
  category: PublicCategory | null
  change_types: string[]
  public_status_label: string | null
  evidence_status: string | null
  observed_effect: string | null
  next_review_at: string | null
  published_at: string | null
  reviewed_at: string | null
  reactions_count: number
  sources_count: number
  claims_count: number
  pageviews: number
  has_comparison: boolean
  cover_image_url?: string | null
  profiles?: PublicCaseProfile[]
}

export type PublicCaseSource = {
  title: string
  url: string
  kind: string
  locator: string | null
  published_on: string | null
  is_primary: boolean
}

export type UpdateDirection = 'riesgo' | 'reversion' | 'incierto'

export type ClaimVerdict = 'se_sostiene' | 'no_se_sostiene' | 'a_medias' | 'sin_respaldo'

export type PublicCaseClaim = {
  claim: string
  verdict: ClaimVerdict
  response: string
  heard_on: string | null
  source_url: string | null
  source_title: string | null
}

export type CaseLinkRelation = 'contexto' | 'consecuencia' | 'antecedente' | 'relacionado'

export type PublicCaseLink = {
  slug: string
  title: string
  hook: string | null
  relation: CaseLinkRelation | string
  note: string | null
}

export type PublicCaseProfile = {
  slug: string
  display_name: string
  photo_url: string | null
  role: string
}

export type PublicCaseMilestone = {
  occurred_on: string
  occurred_at?: string | null
  kind: string
  label: string
  note: string | null
  direction: UpdateDirection
  source_url: string | null
  source_title: string | null
  procedural_status: string | null
}

export type PortfolioMovementItem = {
  slug: string
  title: string
  reasons: string[]
}

export type PortfolioStats = {
  summary: {
    total: number
    en_discusion: number
    with_comparison: number
    reviews_overdue: number
    recently_published: number
  }
  movement: PortfolioMovementItem[]
}

export type PublicCase = {
  slug: string
  title: string
  hook: string | null
  summary: string | null
  what_changes: string | null
  who_affected: string | null
  category: PublicCategory | null
  areas: string[]
  change_types: string[]
  procedural_status: string
  public_status_label: string | null
  evidence_status: string | null
  observed_effect: string | null
  content_version: number | null
  sources_count: number
  claims_count: number
  pageviews: number
  before_text: string | null
  after_text: string | null
  detail: string | null
  reactions: PublicReaction[]
  sources: PublicCaseSource[]
  milestones: PublicCaseMilestone[]
  claims: PublicCaseClaim[]
  links?: PublicCaseLink[]
  linked_from?: PublicCaseLink[]
  profiles?: PublicCaseProfile[]
  published_at: string | null
  reviewed_at: string | null
  next_review_at: string | null
  seo: {
    meta_title: string
    meta_description: string | null
    og_image_url: string | null
    robots: string
    canonical_url?: string | null
  }
}

type CasesResponse = {
  data: PublicCaseListItem[]
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
}

export function usePublicApi() {
  const config = useRuntimeConfig()

  function apiUrl(path: string, query?: Record<string, string | string[] | undefined>) {
    const base = config.public.apiBase.replace(/\/$/, '')
    const href = `${base}${path}`
    const url = new URL(href, import.meta.client ? window.location.origin : 'http://127.0.0.1:8017')
    if (query) {
      for (const [key, value] of Object.entries(query)) {
        if (value === undefined || value === '') continue
        if (Array.isArray(value)) {
          value.forEach((v) => url.searchParams.append(key, v))
        } else {
          url.searchParams.set(key, value)
        }
      }
    }
    return url.toString()
  }

  async function fetchCases(query?: Record<string, string | string[] | undefined>) {
    return $fetch<CasesResponse>(apiUrl('/v1/cases', query))
  }

  async function fetchCase(slug: string) {
    return $fetch<{ data: PublicCase }>(apiUrl(`/v1/cases/${slug}`))
  }

  async function fetchPortfolioStats() {
    return $fetch<{
      data: PortfolioStats
      meta: { generated_at: string; methodology: string }
      internal: Record<string, unknown>
    }>(apiUrl('/v1/stats/portfolio'))
  }

  return { fetchCases, fetchCase, fetchPortfolioStats, apiUrl }
}
