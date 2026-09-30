import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  {
    path: '/',
    redirect: "/home",
  },
  {
    path: "/home",
    name: "home",
    component: () => import("@/views/home.vue"),
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router
