<script setup>
import BaseModal from './BaseModal.vue'
import { dialog, answer } from '../confirm'
</script>

<template>
  <BaseModal v-if="dialog.open" :title="dialog.title" small @close="answer(false)">
    <p v-if="dialog.message" class="msg">{{ dialog.message }}</p>
    <template #footer>
      <button class="mbtn" @click="answer(false)">{{ dialog.cancelText }}</button>
      <button class="mbtn" :class="dialog.danger ? 'danger' : 'primary'" @click="answer(true)">{{ dialog.confirmText }}</button>
    </template>
  </BaseModal>
</template>

<style scoped>
.msg { font-size: 14px; line-height: 1.5; color: var(--muted); }
</style>
