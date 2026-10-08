<script setup>
import AppIcon from '../components/AppIcon.vue'
import NftCard from '../components/NftCard.vue'
import { profile as p } from '../data'
import { session } from '../session'

const collection = [...p.earned, ...p.purchased].slice(0, 4)
</script>

<template>
  <header class="head">
    <div>
      <h1>Sveicināts, {{ session.user || p.name }}</h1>
      <p class="muted desk">Seko savai kolekcijai, klikšķini un pelni $BORIS</p>
    </div>
  </header>

  <section class="card balance">
    <span class="l">KOPĀ $BORIS</span>
    <b class="val"><AppIcon name="diamond" :size="18" /> {{ p.stats[0].value }}</b>
  </section>

  <section class="actions">
    <RouterLink to="/click" class="card action">
      <span class="ic click"><AppIcon name="gamepad" :size="22" /></span>
      <b>Klikšķis</b>
      <span class="muted">Palielini attēlu vērtību</span>
    </RouterLink>
    <RouterLink to="/market" class="card action">
      <span class="ic market"><AppIcon name="bag" :size="22" /></span>
      <b>Tirgus</b>
      <span class="muted">Pērc un apmaini attēlus</span>
    </RouterLink>
  </section>

  <section class="collection">
    <div class="row">
      <h2>Tava kolekcija</h2>
      <RouterLink to="/profile" class="muted">Skatīt visu</RouterLink>
    </div>
    <div class="grid"><NftCard v-for="n in collection" :key="n.id" :nft="n" variant="owned" /></div>
  </section>
</template>

<style scoped>
.head { margin-bottom: 20px; }
h1 { font-size: 22px; font-weight: 800; }
.desk { display: none; font-size: 14px; margin-top: 4px; }
.balance { display: flex; flex-direction: column; gap: 6px; padding: 20px; border-radius: 20px; margin-bottom: 20px; background: var(--panel); }
.balance .l { color: var(--muted); font-size: 11px; font-weight: 700; }
.balance .val { display: flex; align-items: center; gap: 8px; font-size: 26px; font-weight: 800; color: var(--cyan); }
.actions { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 28px; }
.action { display: flex; flex-direction: column; align-items: flex-start; gap: 8px; padding: 18px; }
.action b { font-size: 15px; }
.action .muted { font-size: 12px; }
.ic { width: 44px; height: 44px; border-radius: 12px; display: grid; place-items: center; background: var(--surface); }
.ic.click { color: var(--violet); }
.ic.market { color: var(--cyan); }
.row { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
h2 { font-size: 16px; font-weight: 800; }
.row a { font-size: 12px; }
.row a:hover { text-decoration: underline; }
@media (min-width: 900px) {
  .head { margin-bottom: 32px; } h1 { font-size: 28px; } .desk { display: block; }
  .balance { padding: 28px 32px; margin-bottom: 28px; } .balance .val { font-size: 32px; }
  .actions { grid-template-columns: repeat(2, minmax(220px, 320px)); margin-bottom: 36px; }
  .action { padding: 24px; }
}
</style>