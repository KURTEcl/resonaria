const STORAGE_KEY = 'qp-follower-key'

function followerKey() {
  if (!import.meta.client) return null
  let key = localStorage.getItem(STORAGE_KEY)
  if (!key) {
    key = crypto.randomUUID().replace(/-/g, '')
    localStorage.setItem(STORAGE_KEY, key)
  }
  return key
}

export function useTopicFollow(slug: () => string) {
  const { apiUrl } = usePublicApi()
  const following = ref(false)
  const followersCount = ref<number | null>(null)
  const busy = ref(false)

  async function follow() {
    const key = followerKey()
    if (!key) return
    busy.value = true
    try {
      const res = await $fetch<{ data: { following: boolean; followers_count: number } }>(
        apiUrl(`/v1/cases/${slug()}/follow`),
        { method: 'POST', body: { follower_key: key } },
      )
      following.value = res.data.following
      followersCount.value = res.data.followers_count
    } finally {
      busy.value = false
    }
  }

  async function unfollow() {
    const key = followerKey()
    if (!key) return
    busy.value = true
    try {
      const res = await $fetch<{ data: { following: boolean; followers_count: number } }>(
        apiUrl(`/v1/cases/${slug()}/follow`),
        { method: 'DELETE', body: { follower_key: key } },
      )
      following.value = res.data.following
      followersCount.value = res.data.followers_count
    } finally {
      busy.value = false
    }
  }

  return { following, followersCount, busy, follow, unfollow }
}
