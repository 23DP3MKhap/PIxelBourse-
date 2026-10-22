import { createRouter, createWebHistory } from 'vue-router'
import { session, loadUser, clearSession } from './session'
import { setUnauthorizedHandler } from './api/http'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', redirect: '/market' },
    { path: '/home', component: () => import('./views/HomeView.vue') },
    { path: '/click', component: () => import('./views/ClickerView.vue'), meta: { auth: true } },
    { path: '/collection', component: () => import('./views/CollectionView.vue'), meta: { auth: true } },
    { path: '/market', component: () => import('./views/MarketView.vue') },
    { path: '/market/mine', component: () => import('./views/MyListingsView.vue'), meta: { auth: true } },
    { path: '/trades', component: () => import('./views/TradesView.vue'), meta: { auth: true } },
    { path: '/listing/:id', component: () => import('./views/ListingDetailView.vue'), props: true },
    { path: '/admin', component: () => import('./views/AdminView.vue'), meta: { auth: true, role: 'admin' } },
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

