// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },
  css: ['~/assets/css/main.css', '~/assets/css/desk.css'],
  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://127.0.0.1:8017/api',
      siteUrl: process.env.NUXT_PUBLIC_SITE_URL || 'https://resonaria.cl',
      portalName: process.env.NUXT_PUBLIC_PORTAL_NAME || 'Resonaria',
      portalTagline: process.env.NUXT_PUBLIC_PORTAL_TAGLINE || 'Actualidad con contexto y fuentes trazables.',
      portalDescription: process.env.NUXT_PUBLIC_PORTAL_DESCRIPTION || 'Actualidad, análisis y seguimiento con fuentes trazables.',
      portalGithubUrl: process.env.NUXT_PUBLIC_PORTAL_GITHUB_URL || 'https://github.com/kurtecl/resonaria',
      portalSupportUrl: process.env.NUXT_PUBLIC_PORTAL_SUPPORT_URL || 'https://ko-fi.com/kurtecl',
      portalSocialInstagram: process.env.NUXT_PUBLIC_PORTAL_SOCIAL_INSTAGRAM || 'https://www.instagram.com/resonaria.cl/',
      portalSocialX: process.env.NUXT_PUBLIC_PORTAL_SOCIAL_X || 'https://x.com/resonariacl',
      siteLang: process.env.NUXT_PUBLIC_SITE_LANG || 'es-CL',
      gaMeasurementId: process.env.NUXT_PUBLIC_GA_MEASUREMENT_ID || 'G-XEG6FF1Q1Z',
      googleSiteVerification: process.env.NUXT_PUBLIC_GOOGLE_SITE_VERIFICATION || '',
    },
  },
  // ponytail: static host + live API. New MCP topics must not wait for generate.
  routeRules: {
    '/casos/**': { prerender: false },
    '/cuaderno': { prerender: false },
    '/cuaderno/**': { prerender: false },
  },
  app: {
    head: {
      htmlAttrs: {
        lang: process.env.NUXT_PUBLIC_SITE_LANG || 'es-CL',
        'data-design': 'resonaria-2026-10',
      },
      titleTemplate: `%s | ${process.env.NUXT_PUBLIC_PORTAL_NAME || 'Resonaria'}`,
      meta: [
        {
          name: 'description',
          content: process.env.NUXT_PUBLIC_PORTAL_DESCRIPTION
            || 'Actualidad, análisis y seguimiento con fuentes trazables.',
        },
      ],
      link: [
        { rel: 'icon', type: 'image/svg+xml', href: '/favicon.svg?v=resonaria-conversation-1' },
        { rel: 'apple-touch-icon', sizes: '180x180', href: '/apple-touch-icon.png?v=resonaria-conversation-1' },
        { rel: 'manifest', href: '/site.webmanifest?v=resonaria-conversation-1' },
        { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
        { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' },
        {
          rel: 'stylesheet',
          href: 'https://fonts.googleapis.com/css2?family=Source+Serif+4:ital,opsz,wght@0,8..60,500..700;1,8..60,560..650&family=Schibsted+Grotesk:wght@400..700&display=swap',
        },
      ],
    },
  },
})
