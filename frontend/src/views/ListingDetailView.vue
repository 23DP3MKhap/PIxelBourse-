<script setup>
import { ref, computed, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import AppIcon from '../components/AppIcon.vue'
import StatBox from '../components/StatBox.vue'
import BuyModal from '../components/BuyModal.vue'
import TradeModal from '../components/TradeModal.vue'
import { session } from '../session'
import { fmt } from '../data'
import { useLoad } from '../composables/useLoad'
import { listingsApi } from '../api/listings'

const props = defineProps({ id: String })
const router = useRouter()
const route = useRoute()

const { data: listing, loading, error, reload } = useLoad(() => listingsApi.get(props.id))
watch(() => props.id, reload)

const STATUS = { sold: 'pārdots', cancelled: 'atcelts' }
const isActive = computed(() => listing.value?.status === 'active')
const isSeller = computed(() => !!session.user && session.user.id === listing.value?.sellerId)
const canBuy = computed(() => isActive.value && !isSeller.value && listing.value?.coinPurchase)
const canTrade = computed(() => isActive.value && !isSeller.value && listing.value?.trade)

const showBuy = ref(false)
const showTrade = ref(false)
const bought = ref(false)

const needLogin = () => router.push({ path: '/login', query: { redirect: route.fullPath } })
const openBuy = () => (session.user ? (showBuy.value = true) : needLogin())
const openTrade = () => (session.user ? (showTrade.value = true) : needLogin())
function closeBuy() {
  showBuy.value = false
  if (bought.value) { bought.value = false; reload() } // listing is now sold
}
</script>

<template>
  <div class="nav">
    <button class="back" @click="router.push('/market')"><span class="icon-btn"><AppIcon name="back" :size="16" /></span><span class="muted lbl">Atpakaļ uz tirgu</span></button>
  </div>

  <p v-if="loading" class="state">Ielādē...</p>
  <p v-else-if="error" class="state">{{ error }} <button class="retry" @click="reload">Mēģināt vēlreiz</button></p>
  <div v-else class="split">
    <section class="left">
      <img class="art" :src="listing.url" :alt="listing.title || ''" />
      <div class="ident">
        <div>
          <h1>{{ listing.title || 'Bez nosaukuma' }}</h1>
          <p class="muted">{{ isSeller ? 'Tavs sludinājums' : `Pārdevējs: ${listing.seller ? '@' + listing.seller : '#' + listing.sellerId}` }}</p>
        </div>
        <StatBox label="VĒRTĪBA" :value="`${fmt(listing.value)} $BORIS`" />
      </div>
    </section>

    <section class="right">
      <div class="card pad">
        <span class="muted cap">KLIKŠĶI</span>
        <b class="big">{{ fmt(listing.clicks) }}</b>
      </div>

      <div class="card pad">
        <p v-if="!isActive" class="notice">Šis sludinājums vairs nav aktīvs ({{ STATUS[listing.status] || listing.status }}).</p>
        <div v-if="listing.coinPurchase" class="row"><span class="muted">Cena</span><b class="cy"><AppIcon name="diamond" /> {{ fmt(listing.price) }} $BORIS</b></div>
        <p v-if="listing.trade" class="muted small">Pārdevējs piedāvā arī apmaiņu.</p>

        <button v-if="canBuy" class="grad-btn" @click="openBuy"><AppIcon name="zap" /> PIRKT TAGAD</button>
        <button v-if="canTrade" class="outline-btn" @click="openTrade">PIEDĀVĀT APMAIŅU</button>
        <p v-else-if="isActive && isSeller" class="muted small">Pārvaldi šo sludinājumu sadaļā <RouterLink to="/market/mine" class="lnk">Mani sludinājumi</RouterLink>.</p>
        <p v-if="canBuy && session.user" class="muted small">Tavs atlikums: {{ fmt(session.user.coin_balance) }} $BORIS</p>
      </div>
    </section>
  </div>

  <BuyModal v-if="showBuy && listing" :listing="listing" @close="closeBuy" @done="bought = true" />
  <TradeModal v-if="showTrade && listing" :listing="listing" @close="showTrade = false" />
</template>

<style scoped>
.nav { display: flex; justify-content: space-between; margin-bottom: 24px; }
.back { display: flex; align-items: center; gap: 12px; background: none; border: 0; padding: 0; }
.lbl { font-weight: 600; font-size: 14px; display: none; }
.split { display: flex; flex-direction: column; gap: 24px; }
.left, .right { display: flex; flex-direction: column; gap: 24px; }
.art { width: 100%; height: 260px; object-fit: contain; background: var(--panel); border-radius: 24px; border: 2px solid var(--violet); }
.ident { display: flex; justify-content: space-between; align-items: center; gap: 12px; }
h1 { font-size: 22px; font-weight: 800; margin-bottom: 4px; }
.pad { padding: 16px; display: flex; flex-direction: column; gap: 16px; }
.cap { font-size: 11px; font-weight: 700; }
.big { color: var(--cyan); font-size: 20px; font-weight: 800; margin-top: -12px; }
.row { display: flex; justify-content: space-between; align-items: center; }
.cy { display: flex; gap: 6px; align-items: center; color: var(--cyan); font-size: 18px; font-weight: 800; }
.small { font-size: 12px; }
.notice { padding: 10px 12px; border-radius: 10px; background: rgba(239, 68, 68, .12); color: #f87171; font-size: 13px; font-weight: 600; }
.lnk { color: var(--cyan); font-weight: 700; text-decoration: underline; }
.outline-btn { padding: 14px; background: var(--surface); border: 1px solid var(--cyan); border-radius: 16px; color: var(--cyan); font: 800 14px 'Sora', sans-serif; cursor: pointer; }
@media (min-width: 900px) {
  .lbl { display: block; }
  .split { flex-direction: row; gap: 32px; } .left { flex: 1; } .right { width: 420px; }
  .art { height: 380px; } h1 { font-size: 28px; } .pad { padding: 24px; } .big { font-size: 22px; }
}
</style>
