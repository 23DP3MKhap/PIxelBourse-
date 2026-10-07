<script setup>
import { useRouter } from 'vue-router'
import AuthCard from '../components/AuthCard.vue'
import { signIn } from '../session'

const router = useRouter()
const fields = [
  { key: 'email', label: 'E-pasta adrese', type: 'email' },
  { key: 'username', label: 'Lietotājvārds', type: 'text' },
  { key: 'password', label: 'Parole', type: 'password' },
  { key: 'confirm', label: 'Apstiprini paroli', type: 'password' },
]
const submit = (f) => { signIn(f.username || 'HyperClicker'); router.push('/market') } // mock: no backend
</script>

<template>
  <AuthCard title="Izveidot kontu" subtitle="Pievienojies tirgum" :fields="fields" cta="REĢISTRĒTIES" alt-text="Jau ir konts?" alt-link="Pieslēgties" alt-to="/login" @submit="submit" />
</template>
