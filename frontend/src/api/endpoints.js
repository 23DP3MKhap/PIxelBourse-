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
  images: (params) => api.get(`/admin/images${qs(params)}`), // { search, owner_id }
  removeImage: (id) => api.delete(`/admin/images/${id}`),
  cancelListing: (id) => api.delete(`/admin/listings/${id}`),
  stats: () => api.get('/admin/stats'),
}

export const listingsApi = {
  list: (params) => api.get(`/listings${qs(params)}`), // { search, type, min_price, max_price, sort }
}

export const imagesApi = {
  top: () => api.get('/images'), // top-50, publiski pieejams
  get: (id) => api.get(`/images/${id}`),
  mine: () => api.get('/my-images'),
  upload: (form) => api.post('/images', form), // FormData
  rename: (id, title) => api.post(`/images/${id}`, { title }),
  sell: (id) => api.post(`/images/${id}/sell`), // { coins_received, fee, coin_balance }
}

export const clickerApi = {
  sync: (imageId, count, opts) => api.post('/clicker/sync', { image_id: imageId, count }, opts),
}