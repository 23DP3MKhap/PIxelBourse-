<script setup>
import { ref, computed } from 'vue'
import NftCard from '../components/NftCard.vue'
import StatBox from '../components/StatBox.vue'
import { profile as p } from '../data'

const tab = ref('earned')
const tabs = computed(() => [
  { key: 'earned', label: `Nopelnīti (${p.earned.length})` },
  { key: 'purchased', label: `Nopirkti (${p.purchased.length})` },
])
</script>

<template>
  <section class="card head">
    <div class="ring"><div class="avatar" /></div>
    <div class="who">
      <h1>{{ p.name }}</h1>
      <p><span class="cy">{{ p.handle }}</span></p>
    </div>
    <div class="stats">
      <StatBox v-for="s in p.stats" :key="s.label" :label="s.label" :value="s.value" :tone="s.accent ? 'violet' : 'white'" />
    </div>
  </section>

  <div class="tabs">
    <button v-for="t in tabs" :key="t.key" :class="{ on: tab === t.key }" @click="tab = t.key">{{ t.label }}</button>
  </div>
  <section class="grid"><NftCard v-for="n in p[tab]" :key="n.id" :nft="n" variant="owned" /></section>
</template>

<style scoped>
.head { background: var(--panel); border-radius: 24px; padding: 24px; display: flex; flex-direction: column; align-items: center; gap: 16px; margin-bottom: 24px; text-align: center; }
.ring { width: 92px; height: 92px; border: 3px solid var(--cyan); border-radius: 50%; display: grid; place-items: center; }
.avatar { width: 80px; height: 80px; border-radius: 50%; background: conic-gradient(var(--violet), var(--cyan), var(--violet)); }
h1 { font-size: 22px; font-weight: 800; margin-bottom: 4px; }
.who p { display: flex; gap: 8px; align-items: center; justify-content: center; font-size: 13px; }
.cy { color: var(--cyan); font-weight: 600; }
.stats { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; width: 100%; text-align: left; }
.stats :deep(b) { font-size: 14px; }
.tabs { display: flex; padding: 4px; background: var(--panel); border-radius: 12px; margin-bottom: 24px; max-width: 320px; }
.tabs button { flex: 1; height: 36px; border: 0; border-radius: 8px; background: none; color: var(--muted); font-size: 13px; }
.tabs .on { background: var(--surface); color: #fff; font-weight: 700; }
@media (min-width: 900px) {
  .head { flex-direction: row; padding: 32px; gap: 32px; text-align: left; margin-bottom: 32px; }
  .who { flex: 0 0 auto; } .who p { justify-content: flex-start; font-size: 14px; } h1 { font-size: 26px; }
  .stats { flex: 1; width: auto; } .stats :deep(b) { font-size: 16px; } .stats :deep(.card) { padding: 16px; }
  .ring { width: 100px; height: 100px; } .avatar { width: 88px; height: 88px; }
}
</style>
