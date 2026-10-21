<script setup>
import { ref, computed, onMounted } from 'vue'
import AppIcon from '../components/AppIcon.vue'
import { myImages, ready, clicksFor, valueFor, registerClick, initClicker, syncAll } from '../clicker'
import { fmt } from '../data'

onMounted(initClicker)

const syncing = ref(false)
async function manualSync() {
  syncing.value = true
  await syncAll()
  syncing.value = false
}

const activeId = ref(null)
const active = computed(() => myImages.find((n) => n.id === activeId.value) || myImages[0])

function onClick() {
  if (!active.value) return
  registerClick(active.value)
}
</script>

<template>
  <header class="head">
    <h1>Klikšķis</h1>
    <p class="muted desk">Izvēlies attēlu un palielini tā vērtību ar katru klikšķi</p>
  </header>

  <p v-if="!ready" class="muted">Ielādē kolekciju...</p>
  <p v-else-if="!myImages.length" class="muted">
    Tev vēl nav neviena attēla. Augšupielādē to <RouterLink to="/collection" class="link">Kolekcijas</RouterLink> lapā.
  </p>

  <template v-else>
    <section class="picker">
      <button
        v-for="n in myImages"
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
      <button class="art-btn" :style="{ backgroundImage: `url(${active.url})` }" @click="onClick">
        <span class="overlay"><AppIcon name="zap" :size="18" /></span>
      </button>

            <div class="stats">
              <div class="card stat clicks">
                <span class="l">KLIKŠĶI</span>
                <b>{{ fmt(clicksFor(active)) }}</b>
              </div>
              <div class="card stat value">
                <span class="l">VĒRTĪBA</span>
                <b><AppIcon name="diamond" :size="14" /> {{ fmt(valueFor(active)) }} $BORIS</b>
              </div>
            </div>

            <button class="sync-btn" :disabled="syncing" @click="manualSync">
              {{ syncing ? 'Sinhronizē...' : 'Sinhronizēt tagad' }}
            </button>
          </section>
  </template>
</template>

<style scoped>
.head { margin-bottom: 20px; }
h1 { font-size: 22px; font-weight: 800; }
.desk { display: none; font-size: 14px; margin-top: 4px; }
.link { color: var(--cyan); text-decoration: underline; }

.picker { display: flex; gap: 10px; overflow-x: auto; padding-bottom: 4px; margin-bottom: 24px; }
.thumb { flex: 0 0 72px; aspect-ratio: 1; border-radius: 14px; border: 2px solid transparent; position: relative; overflow: hidden; background-size: cover; background-position: center; background-color: var(--surface); }
.thumb.on { border-color: var(--cyan); }
.thumb .name { position: absolute; inset: auto 0 0 0; padding: 4px 6px; font-size: 9px; font-weight: 700; background: rgba(0,0,0,.55); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.stage { display: flex; flex-direction: column; align-items: center; gap: 20px; }
.art-btn { width: 220px; aspect-ratio: 1; border-radius: 28px; border: 1px solid var(--border); background-size: cover; background-position: center; background-color: var(--surface); position: relative; transition: transform .08s ease; }
.art-btn:active { transform: scale(.96); }
.overlay { position: absolute; right: 10px; bottom: 10px; width: 34px; height: 34px; border-radius: 50%; background: rgba(0,0,0,.55); display: grid; place-items: center; color: var(--cyan); }

.stats { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; width: 100%; max-width: 340px; }
.stat { display: flex; flex-direction: column; align-items: center; gap: 6px; padding: 18px; }
.stat .l { color: var(--muted); font-size: 10px; font-weight: 700; letter-spacing: .04em; }
.stat b { font-size: 26px; font-weight: 800; }
.stat.clicks { border: 1px solid rgba(139, 92, 246, .35); background: rgba(139, 92, 246, .08); }
.stat.clicks b { color: var(--violet); }
.stat.value { border: 1px solid rgba(6, 182, 212, .35); background: rgba(6, 182, 212, .08); }
.stat.value b { display: flex; align-items: center; gap: 6px; color: var(--cyan); }

.sync-btn { padding: 10px 18px; background: var(--surface); border: 1px solid var(--border); border-radius: 12px; color: var(--cyan); font-weight: 700; font-size: 12px; }
.sync-btn:disabled { opacity: .5; }

@media (min-width: 900px) {
  .head { margin-bottom: 32px; } h1 { font-size: 28px; } .desk { display: block; }
  .picker { flex-wrap: wrap; overflow: visible; }
  .thumb { flex: 0 0 90px; }
  .art-btn { width: 280px; }
  .stats { max-width: 380px; }
}
</style>