<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import AppIcon from '../components/AppIcon.vue'
import StatBox from '../components/StatBox.vue'
import { nfts, fmt } from '../data'

const props = defineProps({ id: String })
const router = useRouter()
const nft = computed(() => nfts.find((n) => n.id === Number(props.id)) ?? nfts[0])
const price = computed(() => `${fmt(nft.value.price)} $BORIS`)
const buy = () => alert(`Nopirkts: ${nft.value.title} (imitācija)`)
</script>

<template>
  <div class="nav">
    <button class="back" @click="router.push('/market')"><span class="icon-btn"><AppIcon name="back" :size="16" /></span><span class="muted lbl">Atpakaļ uz tirgu</span></button>
  </div>

  <div class="split">
    <section class="left">
      <div class="art" :style="{ background: `radial-gradient(circle at 50% 40%, hsl(${nft.hue} 90% 45% / .55), #05030a 70%)` }" />
      <div class="ident">
        <div><h1>{{ nft.title }}</h1><p class="muted">Pieder @{{ nft.owner }}</p></div>
        <StatBox label="KOPĒJĀ VĒRTĪBA" :value="price" />
      </div>
    </section>

    <section class="right">
      <div class="card pad">
        <span class="muted cap">CENU VĒSTURE</span>
        <b class="big">{{ price }}</b>
        <svg viewBox="0 0 320 80" class="spark">
          <defs><linearGradient id="g" x1="0" x2="1"><stop offset="0" stop-color="#8b5cf6" /><stop offset="1" stop-color="#06b6d4" /></linearGradient></defs>
          <polyline points="0,60 40,50 80,55 120,30 160,38 200,20 240,28 280,12 320,18" fill="none" stroke="url(#g)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </div>
      <div class="card pad">
        <div class="row"><span class="muted">Pārdošanas cena</span><b class="cy"><AppIcon name="diamond" /> {{ price }}</b></div>
        <button class="grad-btn" @click="buy"><AppIcon name="zap" /> PIRKT TAGAD</button>
      </div>
    </section>
  </div>
</template>

<style scoped>
.nav { display: flex; justify-content: space-between; margin-bottom: 24px; }
.back { display: flex; align-items: center; gap: 12px; background: none; border: 0; padding: 0; }
.lbl { font-weight: 600; font-size: 14px; display: none; }
.split { display: flex; flex-direction: column; gap: 24px; }
.left, .right { display: flex; flex-direction: column; gap: 24px; }
.art { height: 260px; border-radius: 24px; border: 2px solid var(--violet); }
.ident { display: flex; justify-content: space-between; align-items: center; gap: 12px; }
h1 { font-size: 22px; font-weight: 800; margin-bottom: 4px; }
.pad { padding: 16px; display: flex; flex-direction: column; gap: 16px; }
.cap { font-size: 11px; font-weight: 700; } .big { color: var(--cyan); font-size: 20px; font-weight: 800; margin-top: -12px; }
.spark { width: 100%; height: 80px; }
.row { display: flex; justify-content: space-between; align-items: center; }
.cy { display: flex; gap: 6px; align-items: center; color: var(--cyan); font-size: 18px; font-weight: 800; }
@media (min-width: 900px) {
  .lbl { display: block; } .nav { margin-bottom: 24px; }
  .split { flex-direction: row; gap: 32px; } .left { flex: 1; } .right { width: 420px; }
  .art { height: 380px; } h1 { font-size: 28px; } .pad { padding: 24px; } .big { font-size: 22px; }
}
</style>
