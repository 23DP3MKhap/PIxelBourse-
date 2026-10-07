<script setup>
import { useRouter } from 'vue-router'
import AuthCard from '../components/AuthCard.vue'
import { signIn } from '../session'

const router = useRouter()
const fields = [{ key: 'email', label: 'E-pasta adrese', type: 'email' }, { key: 'password', label: 'Parole', type: 'password' }]
const submit = () => { signIn(); router.push('/market') } // mock: no backend
</script>

<template>
  <AuthCard title="Laipni lūdzam atpakaļ" subtitle="Piesakies, lai turpinātu savu kolekciju" :fields="fields" cta="PIESLĒGTIES" alt-text="Nav konta?" alt-link="Reģistrēties" alt-to="/register" @submit="submit" />
</template>
