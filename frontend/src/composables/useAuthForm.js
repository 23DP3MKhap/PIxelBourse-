import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'

export function useAuthForm(action) {
  const router = useRouter()
  const route = useRoute()
  const loading = ref(false)
  const error = ref('')
  const errors = ref({})

  async function submit(form) {
    loading.value = true
    error.value = ''
    errors.value = {}
    try {
      await action(form)
      const to = String(route.query.redirect || '')
      router.push(to.startsWith('/') && !to.startsWith('//') ? to : '/market')
    } catch (e) {
      error.value = e.message
      errors.value = e.errors || {}
    } finally {
      loading.value = false
    }
  }

  return { loading, error, errors, submit }
}
