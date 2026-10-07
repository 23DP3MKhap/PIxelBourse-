<script setup>
import { ref, computed } from 'vue'
import AppIcon from '../components/AppIcon.vue'
import NftCard from '../components/NftCard.vue'
import { nfts } from '../data'

const query = ref('')
const items = computed(() => nfts.filter((n) => n.title.toLowerCase().includes(query.value.toLowerCase())))
</script>

<template>
  <header class="head">
    <div>
      <h1>Tirgus</h1>
      <p class="muted desk">Atklāj un iegādājies izcilus digitālos priekšmetus ar augstu mijiedarbību</p>
    </div>
    <div class="actions">
      <label class="search"><AppIcon name="search" :size="14" /><input v-model="query" placeholder="Meklēt priekšmetus..." /></label>
      <button class="icon-btn" aria-label="Filtri"><AppIcon name="sliders" :size="16" /></button>
    </div>
  </header>
  <section class="grid"><NftCard v-for="n in items" :key="n.id" :nft="n" :to="`/nft/${n.id}`" /></section>
</template>

<style scoped>
.head { display: flex; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 24px; }
h1 { font-size: 22px; font-weight: 800; }
.desk, .search { display: none; font-size: 14px; margin-top: 4px; }
@media (min-width: 900px) {
  .head { margin-bottom: 32px; } h1 { font-size: 28px; } .desk { display: block; }
  .search { display: flex; align-items: center; gap: 8px; width: 280px; padding: 12px 16px; margin: 0; background: var(--surface); border: 1px solid var(--border); border-radius: 12px; }
  .search input { background: none; border: 0; outline: 0; color: #fff; font: 400 13px 'Sora', sans-serif; width: 100%; }
  .actions { display: flex; gap: 16px; }
}
@media (max-width: 899px) { .actions { display: block; } .actions .icon-btn { width: 38px; height: 38px; } }
</style>
