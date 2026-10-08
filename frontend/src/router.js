import { createRouter, createWebHistory } from 'vue-router'

export default createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', redirect: '/market' },
    { path: '/home', component: () => import('./views/HomeView.vue') },
    { path: '/click', component: () => import('./views/ComingSoonView.vue'), props: { title: 'Klikšķis' } },
    { path: '/market', component: () => import('./views/MarketView.vue') },
    { path: '/nft/:id', component: () => import('./views/NftDetailView.vue'), props: true },
    { path: '/profile', component: () => import('./views/ProfileView.vue') },
    { path: '/login', component: () => import('./views/LoginView.vue') },
    { path: '/register', component: () => import('./views/RegisterView.vue') },
  ],
})
