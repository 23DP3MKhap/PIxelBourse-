<script setup>
import { onMounted, onBeforeUnmount } from 'vue'

defineProps({ title: String, small: Boolean })
const emit = defineEmits(['close'])
const onKey = (e) => e.key === 'Escape' && emit('close')

onMounted(() => { window.addEventListener('keydown', onKey); document.body.style.overflow = 'hidden' })
onBeforeUnmount(() => { window.removeEventListener('keydown', onKey); document.body.style.overflow = '' })
</script>

<template>
  <Teleport to="body">
    <div class="overlay" @mousedown.self="emit('close')">
      <div class="modal" :class="{ small }" role="dialog" aria-modal="true">
        <header class="mh"><h3>{{ title }}</h3><button class="x" aria-label="Aizvērt" @click="emit('close')">×</button></header>
        <div class="mb"><slot /></div>
        <footer v-if="$slots.footer" class="mf"><slot name="footer" /></footer>
      </div>
    </div>
  </Teleport>
</template>

<style>
/* shared modal button styles (used inside modal footers) */
.mbtn { padding: 12px 18px; border-radius: 12px; background: var(--surface); border: 1px solid var(--border); color: #fff; font: 700 13px 'Sora', sans-serif; cursor: pointer; }
.mbtn.primary { background: var(--grad); border-color: transparent; }
.mbtn.danger { background: #dc2626; border-color: transparent; }
.mbtn:disabled { opacity: .5; cursor: default; }
</style>

<style scoped>
.overlay { position: fixed; inset: 0; z-index: 500; display: flex; align-items: center; justify-content: center; padding: 16px; background: rgba(0, 0, 0, .65); backdrop-filter: blur(4px); }
.modal { width: min(460px, 100%); max-height: 90vh; display: flex; flex-direction: column; background: var(--panel); border: 1px solid var(--border); border-radius: 20px; overflow: hidden; }
.modal.small { width: min(380px, 100%); }
.mh { display: flex; align-items: center; justify-content: space-between; padding: 18px 20px 0; }
h3 { font-size: 17px; font-weight: 800; }
.x { background: none; border: 0; color: var(--muted); font-size: 26px; line-height: 1; padding: 0 4px; cursor: pointer; }
.mb { padding: 16px 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 14px; }
.mf { display: flex; justify-content: flex-end; gap: 10px; padding: 0 20px 20px; }
</style>
