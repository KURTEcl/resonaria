import { resolveSocialPreview } from '../utils/socialPreview'

export default defineEventHandler(async (event) => {
  const query = getQuery(event)
  const platform = String(query.platform ?? '').trim()
  const url = String(query.url ?? '').trim()

  if (!platform || !url) {
    throw createError({ statusCode: 400, message: 'platform y url son obligatorios' })
  }

  return resolveSocialPreview(platform, url)
})
