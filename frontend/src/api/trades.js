import { api } from './http'

const qs = (params = {}) => {
  const q = new URLSearchParams(Object.entries(params).filter(([, v]) => v !== '' && v != null)).toString()
  return q ? `?${q}` : ''
}
const rows = (res) => (Array.isArray(res) ? res : (res?.data ?? []))
const img = (i) => (i ? { id: i.id, url: i.url ?? '', title: i.title ?? null, value: i.value ?? 0 } : null)

// The docs don't show the JSON, so these accept a few shapes. Adjust here if the backend differs.
export function normalizeTrade(t) {
  const listing = t.listing ?? {}
  return {
    id: t.id,
    status: t.status ?? 'pending',
    listingId: t.listing_id ?? listing.id ?? null,
    offered: img(t.offered_image ?? t.offeredImage), // sender's image
    wanted: img(t.listing_image ?? listing.image ?? t.requested_image), // image from the listing
    sender: t.sender?.username ?? t.offerer?.username ?? t.sender_username ?? null,
    receiver: t.receiver?.username ?? t.seller?.username ?? t.receiver_username ?? listing.seller?.username ?? null,
    createdAt: t.created_at ?? null,
  }
}

export function normalizeHistory(h) {
  const i = h.image ?? h.offered_image ?? {}
  return {
    id: h.id,
    type: h.type ?? h.kind ?? null, // bought | sold | traded
    url: i.url ?? h.image_url ?? '',
    title: i.title ?? h.title ?? null,
    amount: h.price ?? h.amount ?? h.coins ?? null,
    counterpart: h.counterparty?.username ?? h.counterpart?.username ?? h.buyer?.username ?? h.seller?.username ?? null,
    createdAt: h.created_at ?? null,
  }
}

export const tradesApi = {
  list: async (params) => rows(await api.get(`/trades${qs(params)}`)).map(normalizeTrade), // box=incoming|outgoing, status=pending...
  create: (b) => api.post('/trades', b), // listing_id, offered_image_id
  accept: (id) => api.post(`/trades/${id}/accept`),
  decline: (id) => api.post(`/trades/${id}/decline`),
  cancel: (id) => api.post(`/trades/${id}/cancel`),
}

export const historyApi = {
  list: async (params) => rows(await api.get(`/history${qs(params)}`)).map(normalizeHistory), // type=bought|sold|traded
}
