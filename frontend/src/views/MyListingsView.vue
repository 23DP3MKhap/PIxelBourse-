<script setup>
import { ref, reactive, computed } from 'vue'
import AppIcon from '../components/AppIcon.vue'
import ListingCard from '../components/ListingCard.vue'
import { fmt } from '../data'
import { useLoad } from '../composables/useLoad'
import { imagesApi } from '../api/images'
import { listingsApi, apiMessage } from '../api/listings'
import { showToast } from '../toast'
import { confirmDialog } from '../confirm'

const { data: listings, loading, error, reload } = useLoad(listingsApi.mine, [])
const { data: myImages, reload: reloadImages } = useLoad(imagesApi.mine, [])

//hide images that are already listed
const listedIds = computed(() => new Set(listings.value.filter((l) => l.status === 'active').map((l) => l.imageId)))
const available = computed(() => myImages.value.filter((i) => !listedIds.value.has(i.id)))

const form = reactive({ image_id: '', coin: true, price: '', trade: false })
const saving = ref(false)
const net = computed(() => (form.coin && Number(form.price) > 0 ? Number(form.price) : null))

async function create() {
  if (!form.image_id) return showToast('Izvēlies attēlu.', 'error')
  if (!form.coin && !form.trade) return showToast('Atļauj vismaz pirkšanu par monētām vai apmaiņu.', 'error')
  if (form.coin && !(Number(form.price) > 0)) return showToast('Norādi cenu.', 'error')

  const body = { image_id: form.image_id, coin_purchase_available: form.coin, trade_available: form.trade }
  if (form.coin) body.price = Number(form.price)

  saving.value = true
  try {
    await listingsApi.create(body)
    showToast('Sludinājums izveidots.', 'success')
    Object.assign(form, { image_id: '', coin: true, price: '', trade: false })
    await Promise.all([reload(), reloadImages()])
  } catch (e) {
    showToast(apiMessage(e), 'error')
  } finally {
    saving.value = false
  }
}

const editingId = ref(null)
const editPrice = ref('')

function startEdit(l) { editingId.value = l.id; editPrice.value = l.price }

async function savePrice(l) {
  if (!(Number(editPrice.value) > 0)) return showToast('Norādi cenu.', 'error')
  try {
    await listingsApi.update(l.id, Number(editPrice.value))
    l.price = Number(editPrice.value)
    editingId.value = null
    showToast('Cena atjaunināta.', 'success')
  } catch (e) {
    showToast(apiMessage(e), 'error')
    if (e.status === 409) reload()
  }
}

async function cancel(l) {
  const ok = await confirmDialog({ title: 'Atcelt sludinājumu?', message: `"${l.title || 'Bez nosaukuma'}" vairs netiks piedāvāts tirgū.`, confirmText: 'Atcelt sludinājumu', danger: true })
  if (!ok) return
  try {
    await listingsApi.cancel(l.id)
    showToast('Sludinājums atcelts.', 'success')
    await Promise.all([reload(), reloadImages()])
  } catch (e) {
    showToast(apiMessage(e), 'error')
    if (e.status === 409) reload()
  }
}
</script>

<template>
  <div class="nav">
    <RouterLink to="/market" class="back"><span class="icon-btn"><AppIcon name="back" :size="16" /></span><span class="muted lbl">Atpakaļ uz tirgu</span></RouterLink>
  </div>
  <h1>Mani sludinājumi</h1>

  <section class="card sell">
    <h2>Izlikt pārdošanai</h2>
    <p v-if="!available.length" class="muted small">Nav attēlu, ko izlikt: kolekcija ir tukša vai visi attēli jau ir izlikti.</p>
    <form v-else class="form" @submit.prevent="create">
      <select v-model="form.image_id">
        <option value="" disabled>Izvēlies attēlu</option>
        <option v-for="i in available" :key="i.id" :value="i.id">{{ i.title || 'Bez nosaukuma' }} · vērtība {{ fmt(i.value) }}</option>
      </select>
      <label class="chk"><input v-model="form.coin" type="checkbox" /> Pārdot par monētām</label>
      <input v-if="form.coin" v-model.number="form.price" type="number" min="1" placeholder="Cena ($BORIS)" />
      <p v-if="net !== null" class="muted small">Pēc pārdošanas saņemsi {{ fmt(net) }} $BORIS (tirgū komisijas nav).</p>
      <label class="chk"><input v-model="form.trade" type="checkbox" /> Atļaut apmaiņu</label>
      <button class="grad-btn" type="submit" :disabled="saving">{{ saving ? 'Saglabā...' : 'IZLIKT PĀRDOŠANAI' }}</button>
    </form>
  </section>

  <h2 class="sec">Visi sludinājumi</h2>
  <p v-if="loading" class="state">Ielādē...</p>
  <p v-else-if="error" class="state">{{ error }} <button class="retry" @click="reload">Mēģināt vēlreiz</button></p>
  <p v-else-if="!listings.length" class="state">Tev vēl nav neviena sludinājuma.</p>
  <section v-else class="grid">
    <div v-for="l in listings" :key="l.id" class="wrap">
      <ListingCard :listing="l" :to="`/listing/${l.id}`" />
      <div v-if="l.status === 'active'" class="ops">
        <template v-if="editingId === l.id">
          <input v-model.number="editPrice" type="number" min="1" @keyup.enter="savePrice(l)" @keyup.esc="editingId = null" />
          <button class="btn-sm" aria-label="Saglabāt" @click="savePrice(l)"><AppIcon name="check" :size="14" /></button>
        </template>
        <template v-else>
          <button v-if="l.coinPurchase" class="btn-sm" @click="startEdit(l)"><AppIcon name="edit" :size="13" /> Cena</button>
          <button class="btn-sm danger" @click="cancel(l)"><AppIcon name="trash" :size="13" /> Atcelt</button>
        </template>
      </div>
    </div>
  </section>
</template>

<style scoped>
.nav { margin-bottom: 20px; }
.back { display: inline-flex; align-items: center; gap: 12px; }
.lbl { font-weight: 600; font-size: 14px; display: none; }
h1 { font-size: 22px; font-weight: 800; margin-bottom: 20px; }
h2 { font-size: 16px; font-weight: 700; }
.sec { margin: 28px 0 16px; }
.small { font-size: 12px; }

.sell { padding: 20px; display: flex; flex-direction: column; gap: 14px; max-width: 460px; }
.form { display: flex; flex-direction: column; gap: 12px; }
.form select, .form input[type='number'], .ops input { padding: 12px 14px; background: var(--input); border: 1px solid var(--border); border-radius: 12px; color: #fff; font: 400 13px 'Sora', sans-serif; outline: none; }
.chk { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; }
.grad-btn:disabled { opacity: .6; cursor: default; }

.wrap { display: flex; flex-direction: column; gap: 8px; }
.ops { display: flex; gap: 8px; }
.ops input { flex: 1; min-width: 0; padding: 8px 10px; }
.btn-sm { display: flex; align-items: center; justify-content: center; gap: 6px; flex: 1; padding: 8px 10px; background: var(--surface); border: 1px solid var(--border); border-radius: 10px; font-size: 12px; font-weight: 700; }
.btn-sm.danger { color: #f87171; }

@media (min-width: 900px) { .lbl { display: block; } h1 { font-size: 28px; } }
</style>
