/** Dos letras para avatar sin foto (nombre y apellido si hay). */
export function socialProfileUrl(platform: string, handle: string): string {
  const user = handle.replace(/^@/, '').trim()
  if (!user) return '#'
  switch (platform) {
    case 'x':
    case 'twitter':
      return `https://x.com/${user}`
    case 'instagram':
      return `https://www.instagram.com/${user}/`
    case 'tiktok':
      return `https://www.tiktok.com/@${user}`
    case 'threads':
      return `https://www.threads.net/@${user}`
    case 'reddit':
      return `https://www.reddit.com/user/${user}`
    default:
      return '#'
  }
}

export function profileInitials(displayName: string): string {
  const parts = displayName.trim().split(/\s+/).filter(Boolean)
  if (parts.length >= 2) {
    const first = parts[0]?.[0] ?? ''
    const last = parts[parts.length - 1]?.[0] ?? ''
    return `${first}${last}`.toUpperCase()
  }
  return displayName.trim().slice(0, 2).toUpperCase()
}
