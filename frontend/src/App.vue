<script setup>
import AppSidebar from './components/AppSidebar.vue'
import BottomNav from './components/BottomNav.vue'
</script>

<template>
  <AppSidebar />
  <main class="main"><RouterView /></main>
  <BottomNav />
</template>

<style>
.main { padding: 24px 24px calc(var(--nav-h) + 32px); }
@media (min-width: 900px) { .main { margin-left: var(--sidebar); padding: 40px; } }
</style>
