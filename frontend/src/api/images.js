import { api } from './http'

// Image = { id, owner_id, title|null, url, clicks_count, value, created_at }
export const imagesApi = {
  list: () => api.get('/images'), // public, top 50 by value (bare array)
  get: (id) => api.get(`/images/${id}`), // public
  mine: () => api.get('/my-images'), // auth, bare array
}
