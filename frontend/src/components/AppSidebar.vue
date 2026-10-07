<script setup>
import { useRouter } from 'vue-router'
import AppIcon from './AppIcon.vue'
import { navItems } from './navItems'
import { session, signOut } from '../session'

const router = useRouter()
const logout = () => { signOut(); router.push('/login') }
</script>

<template>
  <aside class="sidebar">
    <div>
      <div class="brand"><i class="dot" />PixelBoris</div>
      <nav class="list">
        <RouterLink v-for="n in navItems" :key="n.to" :to="n.to" class="item" active-class="active">
          <AppIcon :name="n.icon" /> {{ n.label }}
        </RouterLink>
      </nav>
    </div>
    <div class="card widget">
      <template v-if="session.user">
        <span class="lbl">SESIJA</span>
        <b>{{ session.user }}</b>
        <button class="btn" @click="logout"><AppIcon name="logout" :size="14" /> Iziet</button>
      </template>
      <RouterLink v-else to="/login" class="btn">Pieslēgties</RouterLink>
    </div>
  </aside>
</template>

<style scoped>
.sidebar { display: none; }
@media (min-width: 900px) {
  .sidebar { display: flex; flex-direction: column; justify-content: space-between; position: fixed; inset: 0 auto 0 0; width: var(--sidebar); padding: 32px 24px; background: var(--panel); border-right: 1px solid var(--border); }
}
.brand { display: flex; align-items: center; gap: 8px; font-weight: 800; font-size: 18px; margin-bottom: 40px; }
.dot { width: 12px; height: 12px; border-radius: 6px; background: var(--cyan); filter: blur(2px); }
.list { display: flex; flex-direction: column; gap: 8px; }
.item { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border: 1px solid transparent; border-radius: 12px; color: var(--muted); font-weight: 600; font-size: 14px; }
.item.active { background: var(--surface); border-color: var(--border); color: var(--cyan); font-weight: 700; }
.widget { padding: 16px; border-radius: 16px; display: flex; flex-direction: column; gap: 12px; font-size: 13px; }
.lbl { color: var(--muted); font-size: 11px; font-weight: 600; }
.btn { display: flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 14px; background: var(--surface); border: 1px solid var(--border); border-radius: 12px; color: var(--cyan); font-weight: 700; font-size: 13px; }
</style>
