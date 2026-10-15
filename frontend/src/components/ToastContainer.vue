<script setup>
import AppIcon from './AppIcon.vue'
import { toasts, dismissToast } from '../toast'
</script>

<template>
  <div class="toasts">
    <TransitionGroup name="toast">
      <div v-for="t in toasts" :key="t.id" class="toast" :class="t.type" @click="dismissToast(t.id)">
        <AppIcon :name="t.type === 'error' ? 'alert' : 'check'" :size="18" />
        <span>{{ t.message }}</span>
      </div>
    </TransitionGroup>
  </div>
</template>

<style scoped>
.toasts { position: fixed; top: 20px; right: 20px; z-index: 999; display: flex; flex-direction: column; gap: 10px; width: min(320px, calc(100vw - 40px)); }
.toast { display: flex; align-items: center; gap: 10px; padding: 14px 16px; border-radius: 14px; background: var(--panel); border: 1px solid var(--border); font-size: 13px; font-weight: 600; cursor: pointer; box-shadow: 0 10px 30px rgba(0,0,0,.4); }
.toast.success { border-color: rgba(6,182,212,.4); }
.toast.success :deep(svg) { color: var(--cyan); }
.toast.error { border-color: rgba(239,68,68,.4); }
.toast.error :deep(svg) { color: #ef4444; }
.toast-enter-active, .toast-leave-active { transition: all .25s ease; }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateX(30px); }
@media (max-width: 899px) { .toasts { top: auto; bottom: calc(var(--nav-h) + 16px); right: 16px; left: 16px; width: auto; } }
</style>