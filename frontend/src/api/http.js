const BASE = import.meta.env.VITE_API_URL || '/api'
const KEY = 'token'

export const tokenStore = {
  get: () => localStorage.getItem(KEY),
  set: (t) => localStorage.setItem(KEY, t),
  clear: () => localStorage.removeItem(KEY),
}

export class ApiError extends Error {
  constructor(status, data) {
    super(data?.message || `Pieprasījums neizdevās (${status})`)
    this.status = status
    this.errors = data?.errors || {}
  }
}

let onUnauthorized = () => {}
export const setUnauthorizedHandler = (fn) => (onUnauthorized = fn)

export async function http(method, path, body) {
  const token = tokenStore.get()
  const headers = { Accept: 'application/json' }
  if (token) headers.Authorization = `Bearer ${token}`
  if (body !== undefined) headers['Content-Type'] = 'application/json'

  let res
  try {
    res = await fetch(BASE + path, { method, headers, body: body === undefined ? undefined : JSON.stringify(body) })
  } catch {
    throw new ApiError(0, { message: 'Neizdevās savienoties ar serveri.' })
  }

  const data = res.status === 204 ? null : await res.json().catch(() => null)
  if (!res.ok) {
    if (res.status === 401 && token) onUnauthorized()
    throw new ApiError(res.status, data)
  }
  return data
}

export const api = {
  get: (p) => http('GET', p),
  post: (p, b) => http('POST', p, b ?? {}),
  patch: (p, b) => http('PATCH', p, b),
  put: (p, b) => http('PUT', p, b),
  delete: (p, b) => http('DELETE', p, b),
}
