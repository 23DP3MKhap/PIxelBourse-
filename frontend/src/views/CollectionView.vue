<script setup>
import { ref, computed, onMounted } from 'vue'
import AppIcon from '../components/AppIcon.vue'
import { imagesApi } from '../api/endpoints'
import { showToast } from '../toast'
import { fmt } from '../data'

const images = ref([])
const loading = ref(true)
const query = ref('')
const sort = ref('value_desc')

const sortOptions = [
  { value: 'value_desc', label: 'Vērtība (lielākā)' },
  { value: 'value_asc', label: 'Vērtība (mazākā)' },
  { value: 'new', label: 'Jaunākie' },
  { value: 'old', label: 'Vecākie' },
  { value: 'title', label: 'Nosaukums (A-Z)' },
]

const filtered = computed(() => {
  const list = images.value.filter((n) => (n.title || '').toLowerCase().includes(query.value.toLowerCase()))
  const by = {
    value_desc: (a, b) => b.value - a.value,
    value_asc: (a, b) => a.value - b.value,
    new: (a, b) => new Date(b.created_at) - new Date(a.created_at),
    old: (a, b) => new Date(a.created_at) - new Date(b.created_at),
    title: (a, b) => (a.title || '').localeCompare(b.title || ''),
  }
  return [...list].sort(by[sort.value])
})

async function load() {
  loading.value = true
  try {
    images.value = await imagesApi.mine()
  } catch {
    showToast('Neizdevās ielādēt kolekciju', 'error')
  } finally {
    loading.value = false
  }
}
onMounted(load)

const uploadTitle = ref('')
const uploading = ref(false)
const MAX_SIZE = 5 * 1024 * 1024
const ALLOWED = ['image/jpeg', 'image/png', 'image/webp', 'image/gif']

async function onFileChange(e) {
  const file = e.target.files[0]
  e.target.value = ''
  if (!file) return
  if (!ALLOWED.includes(file.type)) return showToast('Nederīgs faila formāts', 'error')
  if (file.size > MAX_SIZE) return showToast('Fails pārāk liels (maks. 5 MB)', 'error')

  const form = new FormData()
  form.append('file', file)
  if (uploadTitle.value.trim()) form.append('title', uploadTitle.value.trim())

  uploading.value = true
  try {
    const img = await imagesApi.upload(form)
    images.value.unshift(img)
    uploadTitle.value = ''
    showToast('Attēls augšupielādēts', 'success')
  } catch (e) {
    showToast(e.message, 'error')
  } finally {
    uploading.value = false
  }
}

const editingId = ref(null)
const editTitle = ref('')

function startEdit(img) {
  editingId.value = img.id
  editTitle.value = img.title || ''
}

async function saveEdit(img) {
  const title = editTitle.value.trim() || null
  editingId.value = null
  try {
    const updated = await imagesApi.rename(img.id, title)
    img.title = updated.title
    showToast('Nosaukums saglabāts', 'success')
  } catch (e) {
    showToast(e.message, 'error')
  }
}

async function remove(img) {
  if (!confirm(`Dzēst attēlu "${img.title || 'Bez nosaukuma'}"?`)) return
  try {
    await imagesApi.remove(img.id)
    images.value = images.value.filter((n) => n.id !== img.id)
    showToast('Attēls dzēsts', 'success')
  } catch (e) {
    showToast(e.message, 'error')
  }
}
</script>

<template>
  <header class="head">
    <div>
      <h1>Kolekcija</h1>
      <p class="muted desk">Tavi augšupielādētie attēli</p>
    </div>
    <div class="actions">
      <label class="search"><AppIcon name="search" :size="14" /><input v-model="query" placeholder="Meklēt..." /></label>
      <select v-model="sort" class="sort">
        <option v-for="o in sortOptions" :key="o.value" :value="o.value">{{ o.label }}</option>
      </select>
    </div>
  </header>

  <section class="card upload">
    <label class="file-btn">
      <AppIcon name="upload" :size="16" />
      {{ uploading ? 'Augšupielādē...' : 'Augšupielādēt attēlu' }}
      <input type="file" accept="image/jpeg,image/png,image/webp,image/gif" :disabled="uploading" hidden @change="onFileChange" />
    </label>
    <input v-model="uploadTitle" class="title-input" placeholder="Nosaukums (nav obligāti)" :disabled="uploading" />
  </section>

  <p v-if="loading" class="muted">Ielādē...</p>
  <p v-else-if="!filtered.length" class="muted">Nekas nav atrasts.</p>

  <section v-else class="grid">
    <div v-for="img in filtered" :key="img.id" class="card tile">
      <div class="art" :style="{ backgroundImage: `url(${img.url})` }" />
      <div v-if="editingId === img.id" class="edit-row">
        <input v-model="editTitle" class="edit-input" autofocus @keyup.enter="saveEdit(img)" @keyup.esc="editingId = null" />
        <button class="icon-btn sm" @click="saveEdit(img)"><AppIcon name="check" :size="14" /></button>
      </div>
      <b v-else class="title">{{ img.title || 'Bez nosaukuma' }}</b>
      <div class="row">
        <span class="value"><AppIcon name="diamond" :size="12" /> {{ fmt(img.value) }} $BORIS</span>
        <div class="ops">
          <button class="icon-btn sm" aria-label="Pārdēvēt" @click="startEdit(img)"><AppIcon name="edit" :size="13" /></button>
          <button class="icon-btn sm danger" aria-label="Dzēst" @click="remove(img)"><AppIcon name="trash" :size="13" /></button>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.head { display: flex; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 20px; flex-wrap: wrap; }
h1 { font-size: 22px; font-weight: 800; }
.desk { display: none; font-size: 14px; margin-top: 4px; }
.actions { display: flex; gap: 10px; flex: 1; justify-content: flex-end; flex-wrap: wrap; }
.search { display: flex; align-items: center; gap: 8px; padding: 10px 14px; background: var(--surface); border: 1px solid var(--border); border-radius: 12px; max-width: 220px; }
.search input { background: none; border: 0; outline: 0; color: #fff; font: 400 13px 'Sora', sans-serif; width: 100%; }
.sort { padding: 10px 14px; background: var(--surface); border: 1px solid var(--border); border-radius: 12px; color: #fff; font: 600 12px 'Sora', sans-serif; }

.upload { display: flex; gap: 12px; padding: 16px; margin-bottom: 24px; flex-wrap: wrap; align-items: center; }
.file-btn { display: flex; align-items: center; gap: 8px; padding: 12px 18px; background: var(--grad); border-radius: 12px; font-weight: 700; font-size: 13px; cursor: pointer; }
.title-input { flex: 1; min-width: 160px; padding: 12px 14px; background: var(--surface); border: 1px solid var(--border); border-radius: 12px; color: #fff; font: 400 13px 'Sora', sans-serif; }

.tile { display: flex; flex-direction: column; gap: 8px; padding: 10px; }
.art { aspect-ratio: 1; border-radius: 12px; background-size: cover; background-position: center; background-color: var(--surface); }
.title { font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.row { display: flex; align-items: center; justify-content: space-between; gap: 6px; }
.value { display: flex; align-items: center; gap: 4px; color: var(--cyan); font-size: 12px; font-weight: 700; }
.ops { display: flex; gap: 6px; }
.icon-btn.sm { width: 28px; height: 28px; border-radius: 8px; }
.icon-btn.danger { color: #ef4444; }
.edit-row { display: flex; gap: 6px; align-items: center; }
.edit-input { flex: 1; padding: 6px 8px; background: var(--input); border: 1px solid var(--border); border-radius: 8px; color: #fff; font: 600 13px 'Sora', sans-serif; }

@media (min-width: 900px) {
  .head { margin-bottom: 32px; } h1 { font-size: 28px; } .desk { display: block; }
  .search { max-width: 260px; }
}
</style>