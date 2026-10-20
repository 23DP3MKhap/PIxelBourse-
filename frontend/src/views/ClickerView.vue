<script setup>
import { ref, computed } from 'vue'
import AppIcon from '../components/AppIcon.vue'
import { fmt } from '../data'
import { session } from '../session'
import { useLoad } from '../composables/useLoad'
import { imagesApi } from '../api/images'

const { data: collection, loading, error, reload } = useLoad(imagesApi.mine, [])
const activeId = ref(null)
const active = computed(() => collection.value.find((n) => n.id === activeId.value) ?? collection.value[0])

const onClick = () => {
  if (!active.value) return
  active.value.clicks_count++ // local only, not synced to the server yet
  active.value.value++
}
</script>

<template>
  <header class="head">
    <div>
      <h1>Klikšķis</h1>
      <p class="muted desk">Izvēlies attēlu un palielini tā vērtību ar katru klikšķi</p>
    </div>
    <div class="card balance">
      <span class="l">TAVS ATLIKUMS</span>
      <b class="val"><AppIcon name="diamond" :size="14" /> {{ fmt(session.user?.coin_balance) }}</b>
    </div>
  </header>

  <p v-if="loading" class="state">Ielādē...</p>
  <p v-else-if="error" class="state">{{ error }} <button class="retry" @click="reload">Mēģināt vēlreiz</button></p>
  <p v-else-if="!collection.length" class="state">Tev vēl nav neviena attēla.</p>
  <section v-else class="picker">
    <button
      v-for="n in collection"
      :key="n.id"
      class="thumb"
      :class="{ on: n.id === active?.id }"
      :style="{ backgroundImage: `url(${n.url})` }"
      @click="activeId = n.id"
    >
      <span class="name">{{ n.title || 'Bez nosaukuma' }}</span>
    </button>
  </section>

  <section v-if="active" class="stage">
    <button
      class="art-btn"
      @click="onClick"
      :style="{
        backgroundImage: `url(${active.url})`,
        boxShadow: '0 0 70px -12px rgba(6, 182, 212, .55)',
      }"
    >
      <AppIcon name="zap" :size="40" />
    </button>

    <div class="stats">
      <div class="card stat clicks">
        <span class="l">KLIKŠĶI</span>
        <b>{{ fmt(active.clicks_count) }}</b>
      </div>
      <div class="card stat value">
        <span class="l">VĒRTĪBA</span>
        <b><AppIcon name="diamond" :size="14" /> {{ fmt(active.value) }} $BORIS</b>
      </div>
    </div>
  </section>
</template>

<style scoped>
.head { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 20px; }
h1 { font-size: 22px; font-weight: 800; }
.desk { display: none; font-size: 14px; margin-top: 4px; }
.balance { display: flex; flex-direction: column; gap: 4px; padding: 12px 16px; border-radius: 16px; align-items: flex-end; text-align: right; }
.balance .l { color: var(--muted); font-size: 10px; font-weight: 700; }
.balance .val { display: flex; align-items: center; gap: 6px; font-size: 16px; font-weight: 800; color: var(--cyan); }

.picker { display: flex; gap: 10px; overflow-x: auto; padding-bottom: 4px; margin-bottom: 24px; }
.thumb { flex: 0 0 72px; aspect-ratio: 1; background: var(--input) center / cover; border-radius: 14px; border: 2px solid transparent; position: relative; overflow: hidden; }
.thumb.on { border-color: var(--cyan); }
.thumb .name { position: absolute; inset: auto 0 0 0; padding: 4px 6px; font-size: 9px; font-weight: 700; background: rgba(0,0,0,.55); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.stage { display: flex; flex-direction: column; align-items: center; gap: 20px; }
.art-btn { width: 220px; aspect-ratio: 1; background: var(--input) center / cover; border-radius: 28px; border: 1px solid var(--border); display: grid; place-items: center; color: #fff; transition: transform .08s ease; }
.art-btn:active { transform: scale(.96); }

.stats { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; width: 100%; max-width: 340px; }
.stat { display: flex; flex-direction: column; align-items: center; gap: 6px; padding: 18px; }
.stat .l { color: var(--muted); font-size: 10px; font-weight: 700; letter-spacing: .04em; }
.stat b { font-size: 26px; font-weight: 800; }
.stat.clicks { border: 1px solid rgba(139, 92, 246, .35); background: rgba(139, 92, 246, .08); }
.stat.clicks b { color: var(--violet); }
.stat.value { border: 1px solid rgba(6, 182, 212, .35); background: rgba(6, 182, 212, .08); }
.stat.value b { display: flex; align-items: center; gap: 6px; color: var(--cyan); }

@media (min-width: 900px) {
  .head { margin-bottom: 32px; } h1 { font-size: 28px; } .desk { display: block; }
  .picker { flex-wrap: wrap; overflow: visible; }
  .thumb { flex: 0 0 90px; }
  .art-btn { width: 280px; }
  .stats { max-width: 380px; }
}
</style>