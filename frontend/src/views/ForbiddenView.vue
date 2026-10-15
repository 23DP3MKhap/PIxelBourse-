<script setup>
import AppIcon from '../components/AppIcon.vue'
</script>

<template>
  <section class="wrap">
    <div class="icon"><AppIcon name="lock" :size="36" /></div>
    <h1>403</h1>
    <p class="muted">Tev nav piekļuves šai lapai.</p>
    <RouterLink to="/home" class="grad-btn">Atpakaļ uz sākumu</RouterLink>
  </section>
</template>

<style scoped>
.wrap { display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; gap: 12px; min-height: 60vh; }
.icon { width: 72px; height: 72px; border-radius: 50%; display: grid; place-items: center; background: var(--surface); border: 1px solid var(--border); color: var(--violet); margin-bottom: 8px; }
h1 { font-size: 48px; font-weight: 800; }
p { font-size: 14px; margin-bottom: 12px; }
.grad-btn { width: auto; padding: 14px 28px; }
</style>