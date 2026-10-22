<script setup>
import AppSidebar from './components/AppSidebar.vue'
import BottomNav from './components/BottomNav.vue'
import ToastContainer from './components/ToastContainer.vue'
import ConfirmHost from './components/ConfirmHost.vue'
</script>

<template>
  <AppSidebar />
  <main class="main"><RouterView /></main>
  <BottomNav />
  <ToastContainer />
  <ConfirmHost />
</template>

<style>
.main { padding: 24px 24px calc(var(--nav-h) + 32px); }
@media (min-width: 900px) { .main { margin-left: var(--sidebar); padding: 40px; } }
</style>