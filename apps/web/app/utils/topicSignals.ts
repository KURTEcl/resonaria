type DecisionFollowUpFields = {
  change_types?: string[] | null
  procedural_status?: string | null
  evidence_status?: string | null
  observed_effect?: string | null
  milestones?: unknown[] | null
}

/**
 * Seguimiento de una decisión pública (ley, presupuesto, trámite).
 * Los hitos solos no activan este modo: también se usan en noticias en desarrollo.
 */
export function isDecisionFollowUp(caseData: DecisionFollowUpFields) {
  if (caseData.change_types?.length) return true
  if (caseData.procedural_status && caseData.procedural_status !== 'en_tramitacion') return true
  if (caseData.evidence_status && caseData.evidence_status !== 'unknown') return true
  if (caseData.observed_effect && caseData.observed_effect !== 'unknown') return true
  return false
}

/** Cobertura de un hecho en curso (clima, emergencia, disputa pública), no un proyecto de ley. */
export function isDevelopingStory(caseData: DecisionFollowUpFields) {
  return Boolean(caseData.milestones?.length) && !isDecisionFollowUp(caseData)
}

export function isReviewOverdue(nextReviewAt: string | null | undefined, now = new Date()) {
  if (!nextReviewAt) return false
  const due = parseDate(nextReviewAt)
  if (!due) return false
  const today = new Date(now)
  today.setHours(0, 0, 0, 0)
  due.setHours(0, 0, 0, 0)
  return due < today
}

function parseDate(value: string) {
  const date = new Date(value.length === 10 ? `${value}T12:00:00` : value)
  return Number.isNaN(date.getTime()) ? null : date
}
