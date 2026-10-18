import { reactive } from 'vue'
import { tokenStore } from './api/http'
import { authApi, userApi } from './api/endpoints'

export const session = reactive({ user: null, loaded: false })

// user, data or ""
const pickUser = (d) => d?.user ?? d?.data ?? d

export const isAdmin = () => session.user?.role === 'admin'

export function clearSession() {
  tokenStore.clear()
  session.user = null
}

export async function loadUser() {
  if (tokenStore.get()) {
    try { session.user = pickUser(await userApi.me()) } catch { clearSession() }
  }
  session.loaded = true
  return session.user
}

async function authenticate(request) {
  const data = await request()
  const token = data?.token ?? data?.access_token
  if (!token) throw new Error('Serveris neatgrieza tokenu.')
  tokenStore.set(token)
  session.user = data.user ?? data.data ?? null
  if (!session.user) await loadUser()
  session.loaded = true
}

export const login = (payload) => authenticate(() => authApi.login(payload))
export const register = (payload) => authenticate(() => authApi.register(payload))

export async function logout() {
  try { await authApi.logout() } catch { /* token invalid mb */ }
  clearSession()
}
