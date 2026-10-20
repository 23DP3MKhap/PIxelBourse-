<script setup>
import AppIcon from './AppIcon.vue'
import { fmt } from '../data'
// variant "market": diamond + "4,102 $BORIS"; variant "owned": "$BORIS 3,520"
defineProps({ nft: Object, to: String, variant: { type: String, default: 'market' } })
</script>

<template>
  <component :is="to ? 'RouterLink' : 'div'" :to="to" class="card tile">
    <img class="art" :src="nft.url" :alt="nft.title || ''" loading="lazy" />
    <b class="title">{{ nft.title || 'Bez nosaukuma' }}</b>
    <span v-if="variant === 'market'" class="price"><AppIcon name="diamond" :size="12" /> {{ fmt(nft.value) }} $BORIS</span>
    <span v-else class="owned"><small>$BORIS</small> {{ fmt(nft.value) }}</span>
  </component>
</template>

<style scoped>
.tile { display: flex; flex-direction: column; gap: 6px; padding: 10px; }
.art { width: 100%; aspect-ratio: 1; object-fit: cover; border-radius: 12px; margin-bottom: 4px; background: var(--input); }
.title { font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.price { display: flex; align-items: center; gap: 4px; color: var(--cyan); font-size: 13px; font-weight: 600; }
.owned { color: var(--cyan); font-size: 12px; font-weight: 700; }
.owned small { color: var(--muted); font-size: 10px; font-weight: 600; margin-right: 4px; }
@media (min-width: 900px) { .tile { padding: 12px; } }
</style>
