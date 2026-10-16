export function usePortalSite() {
  const config = useRuntimeConfig().public

  const name = computed(() => config.portalName || 'Resonaria')
  const tagline = computed(() => config.portalTagline || '')
  const description = computed(() => config.portalDescription || '')
  const githubUrl = computed(() => config.portalGithubUrl || 'https://github.com/kurtecl/resonaria')
  const supportUrl = computed(() => config.portalSupportUrl || 'https://ko-fi.com/kurtecl')
  const siteLang = computed(() => config.siteLang || 'es')
  const socialInstagram = computed(() => config.portalSocialInstagram || '')
  const socialX = computed(() => config.portalSocialX || '')

  return {
    name,
    tagline,
    description,
    githubUrl,
    supportUrl,
    siteLang,
    socialInstagram,
    socialX,
  }
}
