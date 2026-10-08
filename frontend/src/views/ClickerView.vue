<script setup>
import { reactive, ref, computed } from 'vue'
import AppIcon from '../components/AppIcon.vue'
import { profile as p, fmt } from '../data'

const collection = reactive([...p.earned, ...p.purchased].map((n) => ({ ...n, clicks: 0 })))
const activeId = ref(collection[0]?.id)
const active = computed(() => collection.find((n) => n.id === activeId.value))

const onClick = () => {
  if (!active.value) return
  active.value.clicks++
  active.value.price++
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
      <b class="val"><AppIcon name="diamond" :size="14" /> {{ p.stats[0].value }}</b>
    </div>
  </header>

  <section class="picker">
    <button
      v-for="n in collection"
      :key="n.id"
      class="thumb"
      :class="{ on: n.id === activeId }"
      :style="{ background: `radial-gradient(circle at 50% 40%, hsl(${n.hue} 90% 45% / .55), #05030a 70%)` }"
      @click="activeId = n.id"
    >
      <span class="name">{{ n.title }}</span>
    </button>
  </section>

  <section v-if="active" class="stage">
    <button
      class="art-btn"
      @click="onClick"
      :style="{
        background: `radial-gradient(circle at 50% 40%, hsl(${active.hue} 90% 45% / .6), #05030a 70%)`,
        boxShadow: `0 0 70px -12px hsl(${active.hue} 90% 55% / .55)`,
      }"
    >
      <AppIcon name="zap" :size="40" />
    </button>

    <div class="stats">
      <div class="card stat clicks">
        <span class="l">KLIKŠĶI</span>
        <b>{{ fmt(active.clicks) }}</b>
      </div>
      <div class="card stat value">
        <span class="l">VĒRTĪBA</span>
        <b><AppIcon name="diamond" :size="14" /> {{ fmt(active.price) }} $BORIS</b>
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
.thumb { flex: 0 0 72px; aspect-ratio: 1; border-radius: 14px; border: 2px solid transparent; position: relative; overflow: hidden; }
.thumb.on { border-color: var(--cyan); }
.thumb .name { position: absolute; inset: auto 0 0 0; padding: 4px 6px; font-size: 9px; font-weight: 700; background: rgba(0,0,0,.55); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.stage { display: flex; flex-direction: column; align-items: center; gap: 20px; }
.art-btn { width: 220px; aspect-ratio: 1; border-radius: 28px; border: 1px solid var(--border); display: grid; place-items: center; color: #fff; transition: transform .08s ease; }
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