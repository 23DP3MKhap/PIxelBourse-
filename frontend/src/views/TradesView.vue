<script setup>
import { ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import AppIcon from '../components/AppIcon.vue'
import { useLoad } from '../composables/useLoad'
import { tradesApi, historyApi } from '../api/trades'
import { apiMessage } from '../api/listings'
import { confirmDialog } from '../confirm'
import { showToast } from '../toast'
import { fmt } from '../data'

const route = useRoute()
const TABS = [{ key: 'incoming', label: 'Ienākošie' }, { key: 'outgoing', label: 'Izejošie' }, { key: 'history', label: 'Vēsture' }]
const STATUSES = [{ key: '', label: 'Visi' }, { key: 'pending', label: 'Gaida' }, { key: 'accepted', label: 'Pieņemti' }, { key: 'declined', label: 'Noraidīti' }, { key: 'cancelled', label: 'Atcelti' }]
const TYPES = [{ key: '', label: 'Visi' }, { key: 'bought', label: 'Pirkumi' }, { key: 'sold', label: 'Pārdotie' }, { key: 'traded', label: 'Apmaiņas' }]
const STATUS_LABEL = { pending: 'Gaida', accepted: 'Pieņemts', declined: 'Noraidīts', cancelled: 'Atcelts' }
const TYPE_LABEL = { bought: 'Nopirkts', sold: 'Pārdots', traded: 'Apmainīts' }

const tab = ref(TABS.some((t) => t.key === route.query.tab) ? route.query.tab : 'incoming')
const status = ref('')
const type = ref('')

const fetchRows = () =>
  tab.value === 'history' ? historyApi.list({ type: type.value }) : tradesApi.list({ box: tab.value, status: status.value })
const { data: rows, loading, error, reload } = useLoad(fetchRows, [])
watch([tab, status, type], reload)

const date = (d) => (d ? new Date(d).toLocaleDateString('lv-LV') : '')
const name = (i) => i?.title || 'Bez nosaukuma'
const busyId = ref(null)

async function act(t, kind) {
  const texts = {
    accept: { title: 'Pieņemt apmaiņu?', message: `Tavs attēls "${name(t.wanted)}" tiks apmainīts pret "${name(t.offered)}". Šo darbību nevar atsaukt.`, confirmText: 'Pieņemt', done: 'Apmaiņa pieņemta.' },
    decline: { title: 'Noraidīt piedāvājumu?', message: `Piedāvājums par "${name(t.wanted)}" tiks noraidīts.`, confirmText: 'Noraidīt', danger: true, done: 'Piedāvājums noraidīts.' },
    cancel: { title: 'Atcelt piedāvājumu?', message: `Tavs piedāvājums par "${name(t.wanted)}" tiks atcelts.`, confirmText: 'Atcelt piedāvājumu', danger: true, done: 'Piedāvājums atcelts.' },
  }[kind]
  if (!(await confirmDialog(texts))) return
  busyId.value = t.id
  try {
    await tradesApi[kind](t.id)
    showToast(texts.done, 'success')
  } catch (e) {
    showToast(apiMessage(e), 'error')
  } finally {
    busyId.value = null
    reload()
  }
}
</script>

<template>
  <h1>Apmaiņas</h1>
  <div class="tabs">
    <button v-for="t in TABS" :key="t.key" :class="{ on: tab === t.key }" @click="tab = t.key">{{ t.label }}</button>
  </div>

  <div v-if="tab !== 'history'" class="pills">
    <button v-for="s in STATUSES" :key="s.key" class="pill" :class="{ on: status === s.key }" @click="status = s.key">{{ s.label }}</button>
  </div>
  <div v-else class="pills">
    <button v-for="s in TYPES" :key="s.key" class="pill" :class="{ on: type === s.key }" @click="type = s.key">{{ s.label }}</button>
  </div>

  <p v-if="loading" class="state">Ielādē...</p>
  <p v-else-if="error" class="state">{{ error }} <button class="retry" @click="reload">Mēģināt vēlreiz</button></p>
  <p v-else-if="!rows.length" class="state">Nekā nav.</p>

  <section v-else-if="tab !== 'history'" class="list">
    <article v-for="t in rows" :key="t.id" class="card trade">
      <div class="pair">
        <div class="side">
          <img :src="t.offered?.url" :alt="name(t.offered)" />
          <small class="cap">{{ tab === 'incoming' ? 'Piedāvā' : 'Tu piedāvā' }}</small>
          <b>{{ name(t.offered) }}</b>
          <small class="val">Vērtība {{ fmt(t.offered?.value) }}</small>
        </div>
        <span class="arrow">⇄</span>
        <div class="side">
          <img :src="t.wanted?.url" :alt="name(t.wanted)" />
          <small class="cap">{{ tab === 'incoming' ? 'Pret tavu' : 'Pret' }}</small>
          <b>{{ name(t.wanted) }}</b>
          <small class="val">Vērtība {{ fmt(t.wanted?.value) }}</small>
        </div>
      </div>
      <div class="info">
        <span class="badge" :class="t.status">{{ STATUS_LABEL[t.status] || t.status }}</span>
        <span class="muted small">{{ tab === 'incoming' ? 'No' : 'Kam' }}: {{ (tab === 'incoming' ? t.sender : t.receiver) ? '@' + (tab === 'incoming' ? t.sender : t.receiver) : '—' }}</span>
        <span class="muted small">{{ date(t.createdAt) }}</span>
      </div>
      <div v-if="t.status === 'pending'" class="ops">
        <template v-if="tab === 'incoming'">
          <button class="mbtn primary" :disabled="busyId === t.id" @click="act(t, 'accept')"><AppIcon name="check" :size="14" /> Pieņemt</button>
          <button class="mbtn" :disabled="busyId === t.id" @click="act(t, 'decline')">Noraidīt</button>
        </template>
        <button v-else class="mbtn" :disabled="busyId === t.id" @click="act(t, 'cancel')">Atcelt piedāvājumu</button>
      </div>
    </article>
  </section>

  <section v-else class="list">
    <article v-for="h in rows" :key="h.id" class="card hist">
      <img :src="h.url" :alt="name(h)" />
      <div class="hmid"><b>{{ name(h) }}</b><span class="muted small">{{ h.counterpart ? '@' + h.counterpart : '' }} {{ date(h.createdAt) }}</span></div>
      <div class="hend">
        <span class="badge" :class="h.type">{{ TYPE_LABEL[h.type] || h.type }}</span>
        <b v-if="h.amount != null" class="amt">{{ fmt(h.amount) }} $BORIS</b>
      </div>
    </article>
  </section>
</template>

<style scoped>
h1 { font-size: 22px; font-weight: 800; margin-bottom: 20px; }
.tabs { display: flex; padding: 4px; background: var(--panel); border-radius: 12px; margin-bottom: 16px; max-width: 420px; }
.tabs button { flex: 1; height: 36px; border: 0; border-radius: 8px; background: none; color: var(--muted); font: 600 13px 'Sora', sans-serif; }
.tabs .on { background: var(--surface); color: #fff; font-weight: 700; }
.pills { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 20px; }
.pill { padding: 6px 12px; border-radius: 20px; background: var(--surface); border: 1px solid var(--border); color: var(--muted); font: 700 12px 'Sora', sans-serif; }
.pill.on { background: var(--cyan); border-color: var(--cyan); color: var(--bg); }

.list { display: grid; gap: 14px; grid-template-columns: 1fr; }
.trade { padding: 16px; display: flex; flex-direction: column; gap: 14px; }
.pair { display: grid; grid-template-columns: 1fr auto 1fr; gap: 10px; align-items: center; }
.side { display: flex; flex-direction: column; gap: 4px; min-width: 0; }
.side img { width: 100%; aspect-ratio: 1; object-fit: cover; border-radius: 12px; background: var(--input); }
.side b { font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cap { color: var(--muted); font-size: 10px; font-weight: 700; margin-top: 4px; }
.val { color: var(--cyan); font-size: 11px; font-weight: 700; }
.arrow { color: var(--cyan); font-size: 22px; }
.info { display: flex; align-items: center; flex-wrap: wrap; gap: 10px; }
.small { font-size: 12px; }
.badge { padding: 3px 9px; border-radius: 8px; font-size: 11px; font-weight: 700; background: var(--surface); }
.badge.pending { background: rgba(251, 191, 36, .15); color: var(--gold); }
.badge.accepted, .badge.bought { background: rgba(6, 182, 212, .15); color: var(--cyan); }
.badge.declined, .badge.cancelled { background: rgba(239, 68, 68, .15); color: #f87171; }
.badge.sold, .badge.traded { background: rgba(139, 92, 246, .18); color: var(--violet); }
.ops { display: flex; gap: 10px; }
.ops .mbtn { flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px; }

.hist { display: flex; align-items: center; gap: 12px; padding: 12px; }
.hist img { width: 52px; height: 52px; border-radius: 10px; object-fit: cover; background: var(--input); }
.hmid { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 4px; }
.hmid b { font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.hend { display: flex; flex-direction: column; align-items: flex-end; gap: 6px; }
.amt { color: var(--cyan); font-size: 13px; }

@media (min-width: 900px) {
  h1 { font-size: 28px; }
  .list { grid-template-columns: repeat(auto-fill, minmax(420px, 1fr)); }
}
</style>
