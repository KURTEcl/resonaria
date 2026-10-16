/** Gate `c` from the URL. Static prerender drops query from route; read the browser on client. */
export function deskGateFromRoute(route: { query: Record<string, unknown> }): string {
  const fromRoute = route.query.c
  if (typeof fromRoute === 'string' && fromRoute !== '') {
    return fromRoute
  }
  if (import.meta.client) {
    return new URLSearchParams(window.location.search).get('c') ?? ''
  }
  return ''
}
