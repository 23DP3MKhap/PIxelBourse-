import { api } from './http'

export const authApi = {
  register: (b) => api.post('/auth/register', b), // username, email, password, password_confirmation
  login: (b) => api.post('/auth/login', b), // email, password
  logout: () => api.post('/auth/logout'),
}

export const userApi = {
  me: () => api.get('/user'),
  update: (b) => api.patch('/user', b), // username, email
  changePassword: (b) => api.put('/user/password', b), // current_password, password, password_confirmation
  remove: (password) => api.delete('/user', { password }),
}

const qs = (params = {}) => {
  const q = new URLSearchParams(Object.entries(params).filter(([, v]) => v)).toString()
  return q ? `?${q}` : ''
}

export const adminApi = {
  list: (params) => api.get(`/admin/users${qs(params)}`), // { search, role }
  get: (id) => api.get(`/admin/users/${id}`),
  update: (id, b) => api.patch(`/admin/users/${id}`, b), // username, email, role
  remove: (id) => api.delete(`/admin/users/${id}`),
}
