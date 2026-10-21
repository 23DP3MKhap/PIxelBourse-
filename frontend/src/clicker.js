import { reactive, ref } from 'vue'
import { imagesApi, clickerApi } from './api/endpoints'
import { session } from './session'
import { showToast } from './toast'

export const myImages = reactive([])
export const ready = ref(false)

const pending = reactive({})
const sending = {}
const syncing = new Set()
let timer = null

const lsKey = (imageId) => `clicker_pending_${session.user?.id}_${imageId}`

function loadPending(imageId) {
  pending[imageId] = Number(localStorage.getItem(lsKey(imageId))) || 0
}
function savePending(imageId) {
  localStorage.setItem(lsKey(imageId), String(pending[imageId] || 0))
}

export function clicksFor(image) {
  return image.clicks_count + (pending[image.id] || 0) + (sending[image.id] || 0)
}
export function valueFor(image) {
  return image.value + (pending[image.id] || 0) + (sending[image.id] || 0)
}

export function registerClick(image) {
  pending[image.id] = (pending[image.id] || 0) + 1
  savePending(image.id)
}

function applyServerValues(imageId, data) {
  if (!data || data.clicks_count === undefined) return
  const img = myImages.find((i) => i.id === imageId)
  if (img) {
    img.clicks_count = data.clicks_count
    img.value = data.value
  }
}

function removeImage(imageId) {
  const i = myImages.findIndex((img) => img.id === imageId)
  if (i !== -1) myImages.splice(i, 1)
  delete pending[imageId]
  localStorage.removeItem(lsKey(imageId))
}

async function syncImage(imageId, { keepalive = false } = {}) {
  if (syncing.has(imageId) || !pending[imageId]) return
  syncing.add(imageId)
  sending[imageId] = pending[imageId]
  pending[imageId] = 0
  savePending(imageId)

  try {
    const data = await clickerApi.sync(imageId, sending[imageId], { keepalive })
    applyServerValues(imageId, data)
  } catch (e) {
    if (e.status === 422) {
      if (e.data) applyServerValues(imageId, e.data)
      showToast('Daži klikšķi netika saglabāti — pārāk strauja aktivitāte', 'error')
    } else if (e.status === 404) {
      removeImage(imageId)
    } else {
      // 401, 429, 5xx, tīkla kļūda — atgriež klikšķus, mēģinās vēlreiz nākamajā sinkā
      pending[imageId] += sending[imageId]
      savePending(imageId)
    }
  } finally {
    sending[imageId] = 0
    syncing.delete(imageId)
  }
}

export async function syncAll({ keepalive = false } = {}) {
  const ids = myImages.map((img) => img.id).filter((id) => pending[id] > 0)
  if (!ids.length) return
  if (keepalive) {
    await Promise.all(ids.map((id) => syncImage(id, { keepalive })))
  } else {
    for (const id of ids) await syncImage(id)
  }
}

export async function initClicker() {
  if (!session.user) return
  const images = await imagesApi.mine()
  myImages.splice(0, myImages.length, ...images)
  for (const img of myImages) loadPending(img.id)
  ready.value = true
  syncAll()
  if (!timer) timer = setInterval(syncAll, 5 * 60 * 1000)
}

export function resetClicker() {
  myImages.splice(0, myImages.length)
  ready.value = false
}

if (typeof document !== 'undefined') {
  document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'hidden') syncAll({ keepalive: true })
  })
}