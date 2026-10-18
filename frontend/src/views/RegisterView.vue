<script setup>
import AuthCard from '../components/AuthCard.vue'
import { useAuthForm } from '../composables/useAuthForm'
import { register } from '../session'

const fields = [
  { key: 'email', label: 'E-pasta adrese', type: 'email' },
  { key: 'username', label: 'Lietotājvārds', type: 'text' },
  { key: 'password', label: 'Parole', type: 'password' },
  { key: 'password_confirmation', label: 'Apstiprini paroli', type: 'password' },
]
const { loading, error, errors, submit } = useAuthForm(register)
</script>

<template>
  <AuthCard title="Izveidot kontu" subtitle="Pievienojies tirgum" :fields="fields" cta="REĢISTRĒTIES"
    alt-text="Jau ir konts?" alt-link="Pieslēgties" alt-to="/login"
    :loading="loading" :error="error" :field-errors="errors" @submit="submit" />
</template>
