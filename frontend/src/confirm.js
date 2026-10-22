import { reactive } from 'vue'

// Promise-based replacement for window.confirm(): await confirmDialog({ title, message, ... }) -> true/false
export const dialog = reactive({ open: false, title: '', message: '', confirmText: 'Labi', cancelText: 'Atcelt', danger: false })
let resolver = null

export function confirmDialog(opts) {
  resolver?.(false)
  Object.assign(dialog, { message: '', confirmText: 'Labi', cancelText: 'Atcelt', danger: false, ...opts, open: true })
  return new Promise((resolve) => { resolver = resolve })
}

export function answer(value) {
  dialog.open = false
  resolver?.(value)
  resolver = null
}
