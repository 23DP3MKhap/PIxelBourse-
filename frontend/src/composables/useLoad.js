import { ref } from 'vue'

// Runs an async loader right away; exposes data / loading / error and reload()
export function useLoad(fn, initial = null) {
  const data = ref(initial)
  const loading = ref(true)
  const error = ref('')

  async function load() {
    loading.value = true
    error.value = ''
    try {
      data.value = await fn()
    } catch (e) {
      error.value = e.status === 404 ? 'Nekas netika atrasts.' : 'Neizdevās ielādēt datus. Mēģini vēlāk.'
    } finally {
      loading.value = false
    }
  }

  load()
  return { data, loading, error, reload: load }
}
