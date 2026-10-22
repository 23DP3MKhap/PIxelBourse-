<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import AppIcon from '../components/AppIcon.vue'
import { adminApi, listingsApi } from '../api/endpoints'
import { session } from '../session'
import { showToast } from '../toast'
import { fmt } from '../data'

const tab = ref('users')
const tabs = [
  { key: 'users', label: 'Lietotāji' },
  { key: 'images', label: 'Attēli' },
  { key: 'listings', label: 'Sludinājumi' },
  { key: 'stats', label: 'Statistika' },
]

/* ---------- Lietotāji ---------- */
const users = ref([])
const usersMeta = ref({ current_page: 1, last_page: 1, total: 0 })
const usersLoading = ref(true)
const userSearch = ref('')
const userRole = ref('')
const userPage = ref(1)
const roleOptions = [
  { value: '', label: 'Visas lomas' },
  { value: 'user', label: 'Lietotājs' },
  { value: 'admin', label: 'Administrators' },
]

async function loadUsers() {
  usersLoading.value = true
  try {
    const res = await adminApi.list({ search: userSearch.value, role: userRole.value, page: userPage.value })
    users.value = res.data
    usersMeta.value = res.meta
  } catch {
    showToast('Neizdevās ielādēt lietotājus', 'error')
  } finally {
    usersLoading.value = false
  }
}

let userSearchTimer = null
watch(userSearch, () => {
  clearTimeout(userSearchTimer)
  userSearchTimer = setTimeout(() => { userPage.value = 1; loadUsers() }, 350)
})
watch(userRole, () => { userPage.value = 1; loadUsers() })

function goUserPage(p) {
  if (p < 1 || p > usersMeta.value.last_page) return
  userPage.value = p
  loadUsers()
}

async function changeRole(user, newRole) {
  const prev = user.role
  user.role = newRole
  try {
    await adminApi.update(user.id, { role: newRole })
    showToast('Loma mainīta', 'success')
  } catch (e) {
    user.role = prev
    showToast(e.message, 'error')
  }
}

async function removeUser(user) {
  if (!confirm(`Dzēst lietotāju "${user.username}"? Tas neatgriezeniski izdzēsīs kontu.`)) return
  try {
    await adminApi.remove(user.id)
    users.value = users.value.filter((u) => u.id !== user.id)
    showToast('Lietotājs dzēsts', 'success')
  } catch (e) {
    showToast(e.message, 'error')
  }
}

/* ---------- Attēli ---------- */
const images = ref([])
const imagesMeta = ref({ current_page: 1, last_page: 1, total: 0 })
const imagesLoading = ref(true)
const imageSearch = ref('')
const imagePage = ref(1)

async function loadImages() {
  imagesLoading.value = true
  try {
    const res = await adminApi.images({ search: imageSearch.value, page: imagePage.value })
    images.value = res.data
    imagesMeta.value = res.meta
  } catch {
    showToast('Neizdevās ielādēt attēlus', 'error')
  } finally {
    imagesLoading.value = false
  }
}

let imageSearchTimer = null
watch(imageSearch, () => {
  clearTimeout(imageSearchTimer)
  imageSearchTimer = setTimeout(() => { imagePage.value = 1; loadImages() }, 350)
})

function goImagePage(p) {
  if (p < 1 || p > imagesMeta.value.last_page) return
  imagePage.value = p
  loadImages()
}

async function removeImage(img) {
  if (!confirm(`Dzēst attēlu "${img.title || 'Bez nosaukuma'}" (īpašnieks: ${img.owner.username})? Tiks dzēsti arī saistītie sludinājumi un darījumi.`)) return
  try {
    await adminApi.removeImage(img.id)
    images.value = images.value.filter((i) => i.id !== img.id)
    showToast('Attēls dzēsts', 'success')
  } catch (e) {
    showToast(e.message, 'error')
  }
}

/* ---------- Sludinājumi ---------- */
const listings = ref([])
const listingsMeta = ref({ current_page: 1, last_page: 1, total: 0 })
const listingsLoading = ref(true)
const listingSearch = ref('')
const listingPage = ref(1)

async function loadListings() {
  listingsLoading.value = true
  try {
    const res = await listingsApi.list({ search: listingSearch.value, page: listingPage.value })
    listings.value = res.data
    listingsMeta.value = res.meta
  } catch {
    showToast('Neizdevās ielādēt sludinājumus', 'error')
  } finally {
    listingsLoading.value = false
  }
}

let listingSearchTimer = null
watch(listingSearch, () => {
  clearTimeout(listingSearchTimer)
  listingSearchTimer = setTimeout(() => { listingPage.value = 1; loadListings() }, 350)
})

function goListingPage(p) {
  if (p < 1 || p > listingsMeta.value.last_page) return
  listingPage.value = p
  loadListings()
}

async function cancelListing(listing) {
  if (!confirm(`Noņemt sludinājumu "${listing.image.title || 'Bez nosaukuma'}"?`)) return
  try {
    await adminApi.cancelListing(listing.id)
    listings.value = listings.value.filter((l) => l.id !== listing.id)
    showToast('Sludinājums noņemts', 'success')
  } catch (e) {
    showToast(e.message, 'error')
  }
}

/* ---------- Statistika ---------- */
const stats = ref(null)
const statsLoading = ref(true)

async function loadStats() {
  statsLoading.value = true
  try {
    stats.value = await adminApi.stats()
  } catch {
    showToast('Neizdevās ielādēt statistiku', 'error')
  } finally {
    statsLoading.value = false
  }
}

const statGroups = computed(() => {
  if (!stats.value) return []
  const s = stats.value
  return [
    { title: 'Lietotāji', items: [
      { label: 'Kopā', value: s.users.total },
      { label: 'Administratori', value: s.users.admins },
      { label: 'Jauni (7 dienas)', value: s.users.new_last_7_days },
    ] },
    { title: 'Attēli', items: [
      { label: 'Kopā', value: s.images.total },
      { label: 'Sludinājumā', value: s.images.listed },
      { label: 'Kopā klikšķi', value: s.images.total_clicks },
      { label: 'Kopā vērtība', value: s.images.total_value },
    ] },
    { title: 'Sludinājumi', items: [
      { label: 'Kopā', value: s.listings.total },
      { label: 'Aktīvi', value: s.listings.active },
      { label: 'Pārdoti', value: s.listings.sold },
      { label: 'Apmainīti', value: s.listings.traded },
      { label: 'Atcelti', value: s.listings.cancelled },
    ] },
    { title: 'Darījumi', items: [
      { label: 'Kopā', value: s.trades.total },
      { label: 'Gaida', value: s.trades.pending },
      { label: 'Pieņemti', value: s.trades.accepted },
      { label: 'Noraidīti', value: s.trades.declined },
      { label: 'Atcelti', value: s.trades.cancelled },
    ] },
    { title: 'Monētas', items: [
      { label: 'Apgrozībā', value: s.coins.in_circulation },
      { label: 'Pārdošanu skaits', value: s.coins.sales_count },
      { label: 'Apgrozījums', value: s.coins.turnover },
      { label: 'Komisijas', value: s.coins.fees },
    ] },
  ]
})

/* ---------- Cilnes ---------- */
const loadedTabs = new Set(['users'])
function selectTab(key) {
  tab.value = key
  if (loadedTabs.has(key)) return
  loadedTabs.add(key)
  if (key === 'images') loadImages()
  else if (key === 'listings') loadListings()
  else if (key === 'stats') loadStats()
}

onMounted(loadUsers)
</script>

<template>
  <header class="head">
    <h1>Admin panelis</h1>
  </header>

  <div class="tabs">
    <button v-for="t in tabs" :key="t.key" :class="{ on: tab === t.key }" @click="selectTab(t.key)">{{ t.label }}</button>
  </div>

  <!-- Lietotāji -->
  <section v-if="tab === 'users'">
    <div class="toolbar">
      <label class="search"><AppIcon name="search" :size="14" /><input v-model="userSearch" placeholder="Meklēt..." /></label>
      <select v-model="userRole" class="sort">
        <option v-for="r in roleOptions" :key="r.value" :value="r.value">{{ r.label }}</option>
      </select>
    </div>

    <p v-if="usersLoading" class="muted">Ielādē...</p>
    <p v-else-if="!users.length" class="muted">Nekas nav atrasts.</p>
    <template v-else>
      <div class="list">
        <div v-for="u in users" :key="u.id" class="card row">
          <div class="who">
            <b>{{ u.username }}</b>
            <span class="muted">{{ u.email }}</span>
          </div>
          <div class="ops">
            <select class="role-select" :value="u.role" :disabled="u.id === session.user?.id" @change="changeRole(u, $event.target.value)">
              <option value="user">Lietotājs</option>
              <option value="admin">Administrators</option>
            </select>
            <button class="icon-btn sm danger" :disabled="u.id === session.user?.id" aria-label="Dzēst" @click="removeUser(u)">
              <AppIcon name="trash" :size="13" />
            </button>
          </div>
        </div>
      </div>
      <div class="pager">
        <button class="icon-btn sm" :disabled="usersMeta.current_page <= 1" @click="goUserPage(usersMeta.current_page - 1)"><AppIcon name="back" :size="14" /></button>
        <span class="muted">{{ usersMeta.current_page }} / {{ usersMeta.last_page }} ({{ usersMeta.total }})</span>
        <button class="icon-btn sm" :disabled="usersMeta.current_page >= usersMeta.last_page" @click="goUserPage(usersMeta.current_page + 1)"><AppIcon name="back" :size="14" class="rotate" /></button>
      </div>
    </template>
  </section>

  <!-- Attēli -->
  <section v-else-if="tab === 'images'">
    <div class="toolbar">
      <label class="search"><AppIcon name="search" :size="14" /><input v-model="imageSearch" placeholder="Meklēt pēc nosaukuma vai īpašnieka..." /></label>
    </div>

    <p v-if="imagesLoading" class="muted">Ielādē...</p>
    <p v-else-if="!images.length" class="muted">Nekas nav atrasts.</p>
    <template v-else>
      <div class="list">
        <div v-for="img in images" :key="img.id" class="card row">
          <div class="thumb-row">
            <div class="thumb-sm" :style="{ backgroundImage: `url(${img.url})` }" />
            <div class="who">
              <b>{{ img.title || 'Bez nosaukuma' }}</b>
              <span class="muted">{{ img.owner.username }} · {{ fmt(img.clicks_count) }} klikšķi · {{ fmt(img.value) }} $BORIS</span>
            </div>
          </div>
          <div class="ops">
            <span v-if="img.listed" class="badge">Sludinājumā</span>
            <button class="icon-btn sm danger" aria-label="Dzēst" @click="removeImage(img)"><AppIcon name="trash" :size="13" /></button>
          </div>
        </div>
      </div>
      <div class="pager">
        <button class="icon-btn sm" :disabled="imagesMeta.current_page <= 1" @click="goImagePage(imagesMeta.current_page - 1)"><AppIcon name="back" :size="14" /></button>
        <span class="muted">{{ imagesMeta.current_page }} / {{ imagesMeta.last_page }} ({{ imagesMeta.total }})</span>
        <button class="icon-btn sm" :disabled="imagesMeta.current_page >= imagesMeta.last_page" @click="goImagePage(imagesMeta.current_page + 1)"><AppIcon name="back" :size="14" class="rotate" /></button>
      </div>
    </template>
  </section>

  <!-- Sludinājumi -->
  <section v-else-if="tab === 'listings'">
    <div class="toolbar">
      <label class="search"><AppIcon name="search" :size="14" /><input v-model="listingSearch" placeholder="Meklēt..." /></label>
    </div>

    <p v-if="listingsLoading" class="muted">Ielādē...</p>
    <p v-else-if="!listings.length" class="muted">Nav aktīvu sludinājumu.</p>
    <template v-else>
      <div class="list">
        <div v-for="l in listings" :key="l.id" class="card row">
          <div class="thumb-row">
            <div class="thumb-sm" :style="{ backgroundImage: `url(${l.image.url})` }" />
            <div class="who">
              <b>{{ l.image.title || 'Bez nosaukuma' }}</b>
              <span class="muted">{{ l.seller.username }} · {{ fmt(l.price) }} $BORIS</span>
            </div>
          </div>
          <div class="ops">
            <span v-if="l.coin_purchase_available" class="badge">Monētas</span>
            <span v-if="l.trade_available" class="badge violet">Apmaiņa</span>
            <button class="icon-btn sm danger" aria-label="Noņemt" @click="cancelListing(l)"><AppIcon name="trash" :size="13" /></button>
          </div>
        </div>
      </div>
      <div class="pager">
        <button class="icon-btn sm" :disabled="listingsMeta.current_page <= 1" @click="goListingPage(listingsMeta.current_page - 1)"><AppIcon name="back" :size="14" /></button>
        <span class="muted">{{ listingsMeta.current_page }} / {{ listingsMeta.last_page }} ({{ listingsMeta.total }})</span>
        <button class="icon-btn sm" :disabled="listingsMeta.current_page >= listingsMeta.last_page" @click="goListingPage(listingsMeta.current_page + 1)"><AppIcon name="back" :size="14" class="rotate" /></button>
      </div>
    </template>
  </section>

  <!-- Statistika -->
  <section v-else-if="tab === 'stats'">
    <p v-if="statsLoading" class="muted">Ielādē...</p>
    <div v-else class="stats-grid">
      <div v-for="g in statGroups" :key="g.title" class="card stat-group">
        <h3>{{ g.title }}</h3>
        <div class="stat-items">
          <div v-for="it in g.items" :key="it.label" class="stat-item">
            <span class="muted">{{ it.label }}</span>
            <b>{{ fmt(it.value) }}</b>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.head { margin-bottom: 20px; }
h1 { font-size: 22px; font-weight: 800; }

.tabs { display: flex; padding: 4px; background: var(--panel); border-radius: 12px; margin-bottom: 20px; max-width: 440px; overflow-x: auto; }
.tabs button { flex: 1; height: 36px; border: 0; border-radius: 8px; background: none; color: var(--muted); font-size: 13px; white-space: nowrap; padding: 0 10px; }
.tabs .on { background: var(--surface); color: #fff; font-weight: 700; }

.toolbar { display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap; }
.search { display: flex; align-items: center; gap: 8px; padding: 10px 14px; background: var(--surface); border: 1px solid var(--border); border-radius: 12px; max-width: 280px; flex: 1; }
.search input { background: none; border: 0; outline: 0; color: #fff; font: 400 13px 'Sora', sans-serif; width: 100%; }
.sort { padding: 10px 14px; background: var(--surface); border: 1px solid var(--border); border-radius: 12px; color: #fff; font: 600 12px 'Sora', sans-serif; }

.list { display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px; }
.row { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 16px; flex-wrap: wrap; }
.who { display: flex; flex-direction: column; gap: 2px; }
.who span { font-size: 12px; }
.ops { display: flex; align-items: center; gap: 8px; }
.thumb-row { display: flex; align-items: center; gap: 12px; }
.thumb-sm { width: 44px; height: 44px; border-radius: 10px; background-size: cover; background-position: center; background-color: var(--surface); flex-shrink: 0; }
.role-select { padding: 8px 10px; background: var(--input); border: 1px solid var(--border); border-radius: 10px; color: #fff; font: 600 12px 'Sora', sans-serif; }
.icon-btn.sm { width: 32px; height: 32px; border-radius: 10px; }
.icon-btn.danger { color: #ef4444; }
.icon-btn:disabled { opacity: .35; cursor: not-allowed; }
.role-select:disabled { opacity: .5; }
.rotate { transform: rotate(180deg); }
.badge { font-size: 10px; font-weight: 700; padding: 4px 8px; border-radius: 8px; background: rgba(6, 182, 212, .12); color: var(--cyan); }
.badge.violet { background: rgba(139, 92, 246, .12); color: var(--violet); }

.pager { display: flex; align-items: center; justify-content: center; gap: 16px; }

.stats-grid { display: grid; grid-template-columns: 1fr; gap: 12px; }
.stat-group { padding: 16px; border-radius: 16px; }
.stat-group h3 { font-size: 13px; font-weight: 800; margin-bottom: 10px; color: var(--cyan); }
.stat-items { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
.stat-item { display: flex; flex-direction: column; gap: 2px; }
.stat-item span { font-size: 10px; }
.stat-item b { font-size: 16px; font-weight: 800; }

@media (min-width: 900px) {
  .head { margin-bottom: 32px; } h1 { font-size: 28px; }
  .stats-grid { grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); }
}
</style>