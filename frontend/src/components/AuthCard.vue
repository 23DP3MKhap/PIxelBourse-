<script setup>
import { reactive } from 'vue'
// fields: [{ key, label, type }]
const props = defineProps({ title: String, subtitle: String, fields: Array, cta: String, altText: String, altLink: String, altTo: String })
const emit = defineEmits(['submit'])
const form = reactive(Object.fromEntries(props.fields.map((f) => [f.key, ''])))
</script>

<template>
  <div class="card auth">
    <h1>{{ title }}</h1>
    <p class="muted sub">{{ subtitle }}</p>
    <input v-for="f in fields" :key="f.key" v-model="form[f.key]" :type="f.type" :placeholder="f.label" />
    <button class="grad-btn" @click="emit('submit', { ...form })">{{ cta }}</button>
    <p class="alt"><span class="muted">{{ altText }}</span> <RouterLink :to="altTo">{{ altLink }}</RouterLink></p>
  </div>
</template>

<style scoped>
.auth { background: var(--panel); border-radius: 24px; padding: 36px 32px; display: flex; flex-direction: column; gap: 14px; max-width: 402px; margin: 0 auto; }
@media (min-width: 900px) { .auth { margin: 0; } }
h1 { font-size: 22px; font-weight: 700; }
.sub { font-size: 13px; }
input { background: var(--input); border: 1px solid rgba(255,255,255,.1); border-radius: 14px; padding: 16px 20px; color: #fff; font: 400 14px 'Sora', sans-serif; outline: none; }
input:focus { border-color: var(--cyan); }
.alt { text-align: center; font-size: 13px; }
.alt a { color: var(--cyan); font-weight: 600; }
</style>
