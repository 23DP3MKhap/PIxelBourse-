<script setup>
import AuthCard from '../components/AuthCard.vue'
import { useAuthForm } from '../composables/useAuthForm'
import { login } from '../session'

const fields = [{ key: 'email', label: 'E-pasta adrese', type: 'email' }, { key: 'password', label: 'Parole', type: 'password' }]
const { loading, error, errors, submit } = useAuthForm(login)
</script>

<template>
  <AuthCard title="Laipni lūdzam atpakaļ" subtitle="Piesakies, lai turpinātu savu kolekciju" :fields="fields" cta="PIESLĒGTIES"
    alt-text="Nav konta?" alt-link="Reģistrēties" alt-to="/register"
    :loading="loading" :error="error" :field-errors="errors" @submit="submit" />
</template>
