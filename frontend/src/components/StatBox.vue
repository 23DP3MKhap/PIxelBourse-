<script setup>
defineProps({ label: String, value: String, tone: { type: String, default: 'cyan' } })
</script>

<template>
  <div class="card stat">
    <span class="l">{{ label }}</span>
    <b :class="tone">{{ value }}</b>
  </div>
</template>

<style scoped>
.stat { display: flex; flex-direction: column; gap: 4px; padding: 12px 16px; border-radius: 16px; align-items: flex-start; text-align: left; }
.l { color: var(--muted); font-size: 10px; font-weight: 700; }
b { font-weight: 800; font-size: 18px; }
.cyan { color: var(--cyan); } .violet { color: var(--violet); } .white { color: #fff; }
</style>
