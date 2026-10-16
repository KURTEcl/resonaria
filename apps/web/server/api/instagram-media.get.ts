import { resolveInstagramMedia } from '../utils/instagramMedia'

export default defineEventHandler(async (event) => {
  const query = getQuery(event)
  const url = String(query.url ?? '').trim()

  if (!url) {
    throw createError({ statusCode: 400, message: 'url es obligatorio' })
  }

  return resolveInstagramMedia(url)
})
