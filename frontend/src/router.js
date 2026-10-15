import { createRouter, createWebHistory } from 'vue-router'
import { session, loadUser, clearSession } from './session'
import { setUnauthorizedHandler } from './api/http'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', redirect: '/market' },
    { path: '/home', component: () => import('./views/HomeView.vue') },
    { path: '/click', component: () => import('./views/ClickerView.vue') },
    { path: '/market', component: () => import('./views/MarketView.vue') },
    { path: '/nft/:id', component: () => import('./views/NftDetailView.vue'), props: true },
    { path: '/403', component: () => import('./views/ForbiddenView.vue') },
    { path: '/profile', component: () => import('./views/ProfileView.vue'), meta: { auth: true } },
    { path: '/login', component: () => import('./views/LoginView.vue'), meta: { guest: true } },
    { path: '/register', component: () => import('./views/RegisterView.vue'), meta: { guest: true } },
  ],
})

router.beforeEach(async (to) => {
  if (!session.loaded) await loadUser()

  if (to.meta.auth && !session.user) return { path: '/login', query: { redirect: to.fullPath } }
  if (to.meta.guest && session.user) return '/market'
  if (to.meta.role && session.user?.role !== to.meta.role) return '/market'
})

setUnauthorizedHandler(() => {
  clearSession()
  const { fullPath, meta } = router.currentRoute.value
  router.push(meta.auth ? { path: '/login', query: { redirect: fullPath } } : '/login')
})

export default router

