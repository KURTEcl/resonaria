export default defineNuxtPlugin(() => {
  const id = gaMeasurementId()
  if (!id) return

  window.dataLayer = window.dataLayer || []
  window.gtag = function gtag() {
    // gtag.js lee `arguments`, no un arreglo.
    // eslint-disable-next-line prefer-rest-params
    window.dataLayer?.push(arguments)
  }
  window.gtag('js', new Date())
  window.gtag('config', id, {
    send_page_view: false,
    debug_mode: import.meta.dev,
  })

  const script = document.createElement('script')
  script.async = true
  script.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(id)}`
  document.head.appendChild(script)

  const router = useRouter()
  let lastPath = ''

  function track(path: string) {
    const clean = path.split('#')[0] ?? ''
    const pathname = clean.split('?')[0] ?? clean
    if (!pathname || pathname === lastPath) return
    lastPath = pathname
    // La ficha espera el título real y manda su propia vista.
    if (/^\/casos\/[^/]+$/.test(pathname)) return
    nextTick(() => gaPageView(clean))
  }

  router.afterEach((to) => track(to.fullPath))
  router.isReady().then(() => track(router.currentRoute.value.fullPath))
})
