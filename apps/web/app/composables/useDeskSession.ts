import { deskGateFromRoute } from '~/utils/deskGate'

const TOKEN_KEY = 'qp.desk'

export function useDeskSession(onDeskReady: () => void | Promise<void>) {
  const route = useRoute()
  const { apiUrl } = usePublicApi()

  const phase = ref<'closed' | 'login' | 'desk'>('closed')
  const name = ref('')
  const email = ref('')
  const password = ref('')
  const error = ref('')
  const pending = ref(false)

  function token() {
    if (!import.meta.client) return ''
    return sessionStorage.getItem(TOKEN_KEY) ?? ''
  }

  function gateValue() {
    return deskGateFromRoute(route)
  }

  function authHeaders() {
    return { Authorization: `Bearer ${token()}` }
  }

  async function bootstrapDesk() {
    await onDeskReady()
    phase.value = 'desk'
  }

  onMounted(async () => {
    const gate = gateValue()
    if (!gate) return

    try {
      await $fetch(apiUrl('/v1/desk/gate'), { method: 'POST', body: { gate } })
    } catch {
      return
    }

    if (token()) {
      try {
        await bootstrapDesk()
        return
      } catch {
        sessionStorage.removeItem(TOKEN_KEY)
      }
    }

    phase.value = 'login'
  })

  async function enter() {
    error.value = ''
    pending.value = true
    try {
      const response = await $fetch<{ token: string; name: string }>(apiUrl('/v1/desk/session'), {
        method: 'POST',
        body: { gate: gateValue(), email: email.value, password: password.value },
      })
      sessionStorage.setItem(TOKEN_KEY, response.token)
      name.value = response.name
      password.value = ''
      await bootstrapDesk()
    } catch {
      error.value = 'No se pudo entrar. Revisa correo y clave.'
    } finally {
      pending.value = false
    }
  }

  function leave() {
    sessionStorage.removeItem(TOKEN_KEY)
    phase.value = 'login'
    name.value = ''
  }

  const gateQuery = computed(() => route.query)

  return {
    phase,
    name,
    email,
    password,
    error,
    pending,
    enter,
    leave,
    token,
    authHeaders,
    apiUrl,
    gateQuery,
  }
}
