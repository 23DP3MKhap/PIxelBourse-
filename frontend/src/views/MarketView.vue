<script setup>
import { ref, computed, watch } from 'vue'
import AppIcon from '../components/AppIcon.vue'
import ListingCard from '../components/ListingCard.vue'
import { useLoad } from '../composables/useLoad'
import { listingsApi } from '../api/listings'

const query = ref('')
const type = ref('')
const minPrice = ref('')
const maxPrice = ref('')
const sort = ref('newest')
const page = ref(1)
const showFilters = ref(false)

const sortOptions = [
  { value: 'newest', label: 'Jaunākie' },
  { value: 'oldest', label: 'Vecākie' },
  { value: 'price_asc', label: 'Cena: no mazākās' },
  { value: 'price_desc', label: 'Cena: no lielākās' },
  { value: 'value_desc', label: 'Vērtība: lielākā' },
  { value: 'clicks_desc', label: 'Klikšķi: vairāk' },
]

const params = () => ({
  search: query.value.trim(),
  type: type.value,
  min_price: minPrice.value,
  max_price: maxPrice.value,
  sort: sort.value,
  page: page.value,
})

const { data, loading, error, reload } = useLoad(() => listingsApi.list(params()), { items: [], lastPage: 1 })
const items = computed(() => data.value.items)

// filter = page 1
let timer
watch([query, type, minPrice, maxPrice, sort], () => {
  clearTimeout(timer)
  timer = setTimeout(() => { page.value = 1; reload() }, 300)
})

const go = (p) => { page.value = p; reload(); window.scrollTo({ top: 0 }) }
const reset = () => { query.value = ''; type.value = ''; minPrice.value = ''; maxPrice.value = ''; sort.value = 'newest' }
</script>

<template>
  <header class="head">
    <div>
      <h1>PixelBoris</h1>
      <p class="muted desk">Atklāj un iegādājies izcilus digitālos priekšmetus ar augstu mijiedarbību</p>
    </div>
    <div class="actions">
      <label class="search"><AppIcon name="search" :size="14" /><input v-model="query" placeholder="Meklēt priekšmetus..." /></label>
      <button class="icon-btn" :class="{ on: showFilters }" aria-label="Filtri" @click="showFilters = !showFilters"><AppIcon name="sliders" :size="16" /></button>
    </div>
  </header>

  <section v-if="showFilters" class="card filters">
    <label class="f mob"><span>Meklēt</span><input v-model="query" placeholder="Nosaukums vai pārdevējs" /></label>
    <label class="f"><span>Veids</span>
      <select v-model="type">
        <option value="">Visi</option>
        <option value="coins">Par monētām</option>
        <option value="trade">Apmaiņa</option>
      </select>
    </label>
    <label class="f"><span>Cena no</span><input v-model.number="minPrice" type="number" min="0" placeholder="0" /></label>
    <label class="f"><span>Cena līdz</span><input v-model.number="maxPrice" type="number" min="0" placeholder="∞" /></label>
    <label class="f"><span>Kārtot pēc</span>
      <select v-model="sort"><option v-for="o in sortOptions" :key="o.value" :value="o.value">{{ o.label }}</option></select>
    </label>
    <button class="reset" @click="reset">Notīrīt</button>
  </section>

  <div class="bar"><RouterLink to="/market/mine" class="pill">Mani sludinājumi</RouterLink><RouterLink to="/trades" class="pill">Apmaiņas</RouterLink></div>

  <p v-if="loading" class="state">Ielādē...</p>
  <p v-else-if="error" class="state">{{ error }} <button class="retry" @click="reload">Mēģināt vēlreiz</button></p>
  <p v-else-if="!items.length" class="state">Nekas nav atrasts.</p>
  <template v-else>
    <section class="grid"><ListingCard v-for="l in items" :key="l.id" :listing="l" :to="`/listing/${l.id}`" /></section>
    <nav v-if="data.lastPage > 1" class="pager">
      <button :disabled="page <= 1" @click="go(page - 1)">‹ Iepriekšējā</button>
      <span class="muted">{{ page }} / {{ data.lastPage }}</span>
      <button :disabled="page >= data.lastPage" @click="go(page + 1)">Nākamā ›</button>
    </nav>
  </template>
</template>

<style scoped>
.head { display: flex; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 24px; }
h1 { font-size: 22px; font-weight: 800; }
.desk, .search { display: none; font-size: 14px; margin-top: 4px; }
.icon-btn.on { border-color: var(--cyan); color: var(--cyan); }

.filters { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; padding: 16px; margin-bottom: 16px; align-items: end; }
.f { display: flex; flex-direction: column; gap: 6px; font-size: 11px; font-weight: 700; color: var(--muted); }
.f input, .f select { padding: 10px 12px; background: var(--input); border: 1px solid var(--border); border-radius: 10px; color: #fff; font: 400 13px 'Sora', sans-serif; outline: none; }
.f.mob { grid-column: 1 / -1; }
.reset { padding: 10px 12px; background: var(--surface); border: 1px solid var(--border); border-radius: 10px; font-size: 13px; font-weight: 600; }

.bar { display: flex; gap: 8px; margin-bottom: 20px; }
.pill { display: inline-block; padding: 8px 14px; border: 1px solid var(--border); background: var(--surface); border-radius: 20px; color: var(--cyan); font-size: 12px; font-weight: 700; }

.pager { display: flex; align-items: center; justify-content: center; gap: 16px; margin-top: 28px; font-size: 13px; }
.pager button { padding: 8px 14px; background: var(--surface); border: 1px solid var(--border); border-radius: 10px; font-weight: 600; }
.pager button:disabled { opacity: .4; cursor: default; }

@media (min-width: 900px) {
  .head { margin-bottom: 32px; } h1 { font-size: 28px; } .desk { display: block; }
  .search { display: flex; align-items: center; gap: 8px; width: 280px; padding: 12px 16px; margin: 0; background: var(--surface); border: 1px solid var(--border); border-radius: 12px; }
  .search input { background: none; border: 0; outline: 0; color: #fff; font: 400 13px 'Sora', sans-serif; width: 100%; }
  .actions { display: flex; gap: 16px; }
  .filters { grid-template-columns: repeat(4, 1fr) auto; } .f.mob { display: none; }
}
@media (max-width: 899px) { .actions { display: block; } .actions .icon-btn { width: 38px; height: 38px; } }
</style>
