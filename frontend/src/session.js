import { reactive } from 'vue'
// Mock session (frontend only, no backend)
export const session = reactive({ user: null })
export const signIn = (name = 'HyperClicker') => (session.user = name)
export const signOut = () => (session.user = null)
