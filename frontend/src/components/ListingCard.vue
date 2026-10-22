<script setup>
import AppIcon from './AppIcon.vue'
import { fmt } from '../data'

defineProps({ listing: Object, to: String })
const STATUS = { sold: 'Pārdots', cancelled: 'Atcelts' }
</script>

<template>
  <RouterLink :to="to" class="card tile">
    <div class="art-wrap">
      <img class="art" :src="listing.url" :alt="listing.title || ''" loading="lazy" />
      <span v-if="listing.status !== 'active'" class="badge">{{ STATUS[listing.status] || listing.status }}</span>
    </div>
    <b class="title">{{ listing.title || 'Bez nosaukuma' }}</b>
    <span v-if="listing.coinPurchase" class="price"><AppIcon name="diamond" :size="12" /> {{ fmt(listing.price) }} $BORIS</span>
    <span v-else class="price">Tikai apmaiņa</span>
    <span class="meta">
      <small>Vērtība {{ fmt(listing.value) }}</small>
      <small v-if="listing.trade && listing.coinPurchase" class="tag">Apmaiņa</small>
    </span>
  </RouterLink>
</template>

<style scoped>
.tile { display: flex; flex-direction: column; gap: 6px; padding: 10px; }
.art-wrap { position: relative; margin-bottom: 4px; }
.art { display: block; width: 100%; aspect-ratio: 1; object-fit: cover; border-radius: 12px; background: var(--input); }
.badge { position: absolute; top: 8px; left: 8px; padding: 3px 8px; border-radius: 8px; background: rgba(0, 0, 0, .7); font-size: 10px; font-weight: 700; }
.title { font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.price { display: flex; align-items: center; gap: 4px; color: var(--cyan); font-size: 13px; font-weight: 600; }
.meta { display: flex; align-items: center; justify-content: space-between; gap: 6px; }
.meta small { color: var(--muted); font-size: 10px; font-weight: 600; }
.tag { padding: 2px 6px; border-radius: 6px; background: rgba(139, 92, 246, .18); color: var(--violet) !important; }
@media (min-width: 900px) { .tile { padding: 12px; } }
</style>
