import { reactive } from 'vue'

export const toasts = reactive([])
let nextId = 0

export function showToast(message, type = 'success', duration = 3000) {
  const id = ++nextId
  toasts.push({ id, message, type })
  setTimeout(() => dismissToast(id), duration)
}

export function dismissToast(id) {
  const i = toasts.findIndex((t) => t.id === id)
  if (i !== -1) toasts.splice(i, 1)
}