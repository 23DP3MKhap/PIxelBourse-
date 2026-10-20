<script setup>
import { computed } from 'vue'
import NftCard from '../components/NftCard.vue'
import StatBox from '../components/StatBox.vue'
import { session } from '../session'
import { fmt } from '../data'
import { useLoad } from '../composables/useLoad'
import { imagesApi } from '../api/images'

const { data: mine, loading, error, reload } = useLoad(imagesApi.mine, [])
const stats = computed(() => [
  { label: 'KOPĀ $BORIS', value: `${fmt(session.user?.coin_balance)} $BORIS` },
  { label: 'PIEDEROŠIE NFT', value: `${mine.value.length} gab.` },
])
</script>

<template>
  <section class="card head">
    <div class="ring"><div class="avatar" /></div>
    <div class="who">
      <h1>{{ session.user?.username }}</h1>
      <p><span class="cy">{{ session.user?.email }}</span></p>
    </div>
    <div class="stats">
      <StatBox v-for="s in stats" :key="s.label" :label="s.label" :value="s.value" tone="white" />
    </div>
  </section>

  <h2>Mani attēli</h2>
  <p v-if="loading" class="state">Ielādē...</p>
  <p v-else-if="error" class="state">{{ error }} <button class="retry" @click="reload">Mēģināt vēlreiz</button></p>
  <p v-else-if="!mine.length" class="state">Tev vēl nav neviena attēla.</p>
  <section v-else class="grid"><NftCard v-for="n in mine" :key="n.id" :nft="n" :to="`/nft/${n.id}`" variant="owned" /></section>
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
h2 { font-size: 16px; font-weight: 700; margin-bottom: 16px; }
@media (min-width: 900px) {
  .head { flex-direction: row; padding: 32px; gap: 32px; text-align: left; margin-bottom: 32px; }
  .who { flex: 0 0 auto; } .who p { justify-content: flex-start; font-size: 14px; } h1 { font-size: 26px; }
  .stats { flex: 1; width: auto; } .stats :deep(b) { font-size: 16px; } .stats :deep(.card) { padding: 16px; }
  .ring { width: 100px; height: 100px; } .avatar { width: 88px; height: 88px; }
}
</style>
