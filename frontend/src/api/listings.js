import { api } from './http'

const qs = (params = {}) => {
  const q = new URLSearchParams(Object.entries(params).filter(([, v]) => v !== '' && v != null)).toString()
  return q ? `?${q}` : ''
}

// The docs don't show the listing JSON, so this accepts a few shapes
// (nested image/seller or flat). If the backend differs, adjust only here.
export function normalizeListing(l) {
  const img = l.image ?? {}
  const seller = l.seller ?? l.user ?? {}
  return {
    id: l.id,
    status: l.status ?? 'active',
    price: l.price == null ? null : Number(l.price),
    coinPurchase: !!(l.coin_purchase_available ?? l.price != null),
    trade: !!l.trade_available,
    sellerId: l.seller_id ?? seller.id ?? null,
    seller: seller.username ?? l.seller_username ?? null,
    imageId: l.image_id ?? img.id ?? null,
    url: img.url ?? l.image_url ?? l.url ?? '',
    title: img.title ?? l.title ?? null,
    value: img.value ?? l.value ?? 0,
    clicks: img.clicks_count ?? img.click_count ?? l.clicks_count ?? l.click_count ?? 0,
  }
}

// Handles a bare array, { data, meta } and Laravel's plain paginator
function toPage(res) {
  const raw = Array.isArray(res) ? res : (res?.data ?? [])
  return {
    items: raw.map(normalizeListing),
    page: res?.meta?.current_page ?? res?.current_page ?? 1,
    lastPage: res?.meta?.last_page ?? res?.last_page ?? 1,
  }
}

const unwrap = (res) => (res && res.data !== undefined && !Array.isArray(res.data) ? res.data : res)

export const listingsApi = {
  // params: search, type (coins|trade), min_price, max_price, sort, page
  list: async (params) => toPage(await api.get(`/listings${qs(params)}`)),
  get: async (id) => normalizeListing(unwrap(await api.get(`/listings/${id}`))),
  mine: async () => toPage(await api.get('/my-listings')).items,
  create: (b) => api.post('/listings', b), // image_id, price, coin_purchase_available, trade_available
  update: (id, price) => api.patch(`/listings/${id}`, { price }),
  cancel: (id) => api.delete(`/listings/${id}`),
  buy: (id) => api.post(`/listings/${id}/buy`),
}

// First validation message if there is one (Laravel 422), else the error message
export const apiMessage = (e) => Object.values(e.errors || {})[0]?.[0] || e.message
