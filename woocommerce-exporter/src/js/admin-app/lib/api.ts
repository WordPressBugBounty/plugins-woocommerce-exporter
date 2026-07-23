import type { WseAdminData } from './types'

function wpData (): Partial<WseAdminData> {
  return window.wseAdminData ?? {}
}

/**
 * Build a final URL by merging `path` onto wpData.restUrl.
 *
 * Handles both pretty permalinks (/wp-json/wse/v1/) and plain permalinks
 * (?rest_route=/wse/v1/), where naive concatenation would produce two `?`
 * separators and break query parsing in WordPress.
 */
function buildUrl (path: string): string {
  const base = wpData().restUrl ?? ''
  const [pathOnly, query = ''] = path.split('?')
  let url = base + pathOnly
  if (query) url += (base.includes('?') ? '&' : '?') + query
  return url
}

async function wpFetch<T = unknown> (path: string, options: RequestInit = {}): Promise<T> {
  const response = await fetch(buildUrl(path), {
    ...options,
    headers: {
      'X-WP-Nonce': wpData().nonce ?? '',
      'Content-Type': 'application/json',
      ...options.headers,
    },
  })

  if (!response.ok) {
    const body = await response.json().catch(() => ({})) as { message?: string }
    throw new Error(body.message ?? `HTTP ${response.status}`)
  }

  return response.json() as Promise<T>
}

export const api = {
  get:    <T = unknown>(path: string) =>
    wpFetch<T>(path),
  post:   <T = unknown>(path: string, data: unknown) =>
    wpFetch<T>(path, { method: 'POST', body: JSON.stringify(data) }),
  put:    <T = unknown>(path: string, data: unknown) =>
    wpFetch<T>(path, { method: 'PUT',  body: JSON.stringify(data) }),
  delete: <T = unknown>(path: string) =>
    wpFetch<T>(path, { method: 'DELETE' }),
}
