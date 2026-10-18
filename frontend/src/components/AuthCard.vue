<script setup>
import { reactive, computed } from 'vue'
// fields: [{ key, label, type }]
const props = defineProps({
  title: String, subtitle: String, fields: Array, cta: String,
  altText: String, altLink: String, altTo: String,
  loading: Boolean, error: String, fieldErrors: { type: Object, default: () => ({}) },
})
const emit = defineEmits(['submit'])
const form = reactive(Object.fromEntries(props.fields.map((f) => [f.key, ''])))
const firstError = (key) => props.fieldErrors?.[key]?.[0]
const hasFieldErrors = computed(() => Object.keys(props.fieldErrors || {}).length > 0)
</script>

<template>
  <form class="card auth" @submit.prevent="emit('submit', { ...form })">
    <h1>{{ title }}</h1>
    <p class="muted sub">{{ subtitle }}</p>
    <div v-for="f in fields" :key="f.key" class="field">
      <input v-model="form[f.key]" :type="f.type" :placeholder="f.label" :class="{ bad: firstError(f.key) }" />
      <small v-if="firstError(f.key)" class="err">{{ firstError(f.key) }}</small>
    </div>
    <p v-if="error && !hasFieldErrors" class="err">{{ error }}</p>
    <button class="grad-btn" type="submit" :disabled="loading">{{ loading ? 'Lūdzu, uzgaidi...' : cta }}</button>
    <p class="alt"><span class="muted">{{ altText }}</span> <RouterLink :to="altTo">{{ altLink }}</RouterLink></p>
  </form>
</template>

<style scoped>
.auth { background: var(--panel); border-radius: 24px; padding: 36px 32px; display: flex; flex-direction: column; gap: 14px; max-width: 402px; margin: 0 auto; }
@media (min-width: 900px) { .auth { margin: 0; } }
h1 { font-size: 22px; font-weight: 700; }
.sub { font-size: 13px; }
.field { display: flex; flex-direction: column; gap: 6px; }
input { background: var(--input); border: 1px solid rgba(255,255,255,.1); border-radius: 14px; padding: 16px 20px; color: #fff; font: 400 14px 'Sora', sans-serif; outline: none; }
input:focus { border-color: var(--cyan); }
input.bad { border-color: #f87171; }
.err { color: #f87171; font-size: 12px; }
.grad-btn:disabled { opacity: .6; cursor: default; }
.alt { text-align: center; font-size: 13px; }
.alt a { color: var(--cyan); font-weight: 600; }
</style>
