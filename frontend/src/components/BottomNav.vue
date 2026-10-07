<script setup>
import AppIcon from './AppIcon.vue'
import { navItems } from './navItems'
</script>

<template>
  <nav class="bottom">
    <RouterLink v-for="n in navItems" :key="n.to" :to="n.to" class="tab" active-class="active">
      <AppIcon :name="n.icon" :size="22" /><span>{{ n.label }}</span>
    </RouterLink>
  </nav>
</template>

<style scoped>
.bottom { position: fixed; inset: auto 0 0 0; height: var(--nav-h); display: flex; justify-content: space-around; padding: 12px 20px 20px; background: var(--panel); border-top: 1px solid var(--border); }
.tab { display: flex; flex-direction: column; align-items: center; gap: 4px; width: 64px; color: var(--muted); font-size: 11px; font-weight: 600; }
.tab.active { color: var(--cyan); }
@media (min-width: 900px) { .bottom { display: none; } }
</style>
