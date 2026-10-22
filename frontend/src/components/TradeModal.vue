<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import BaseModal from './BaseModal.vue'
import AppIcon from './AppIcon.vue'
import { useLoad } from '../composables/useLoad'
import { imagesApi } from '../api/images'
import { tradesApi } from '../api/trades'
import { apiMessage } from '../api/listings'
import { fmt } from '../data'

const props = defineProps({ listing: Object })
const emit = defineEmits(['close'])
const router = useRouter()

const { data: mine, loading, error: loadError, reload } = useLoad(imagesApi.mine, [])
const selected = ref(null)
const step = ref('pick') // pick -> done
const busy = ref(false)
const error = ref('')

async function send() {
  busy.value = true
  error.value = ''
  try {
    await tradesApi.create({ listing_id: props.listing.id, offered_image_id: selected.value })
    step.value = 'done'
  } catch (e) {
    error.value = apiMessage(e)
  } finally {
    busy.value = false
  }
}

const toTrades = () => { emit('close'); router.push({ path: '/trades', query: { tab: 'outgoing' } }) }
</script>

<template>
  <BaseModal :title="step === 'done' ? 'Piedāvājums nosūtīts' : 'Piedāvāt apmaiņu'" @close="emit('close')">
    <template v-if="step === 'pick'">
      <p class="muted small">Izvēlies savu attēlu, ko piedāvāt apmaiņā pret <b>{{ listing.title || 'Bez nosaukuma' }}</b>.</p>
      <p v-if="loading" class="muted center">Ielādē...</p>
      <p v-else-if="loadError" class="err center">{{ loadError }} <button class="lnk" @click="reload">Mēģināt vēlreiz</button></p>
      <p v-else-if="!mine.length" class="muted center">Tev nav attēlu, ko piedāvāt.</p>
      <div v-else class="pick">
        <button v-for="i in mine" :key="i.id" class="opt" :class="{ on: selected === i.id }" @click="selected = i.id">
          <img :src="i.url" :alt="i.title || ''" />
          <b>{{ i.title || 'Bez nosaukuma' }}</b>
          <small>{{ fmt(i.value) }}</small>
        </button>
      </div>
      <p v-if="error" class="err">{{ error }}</p>
    </template>

    <template v-else>
      <div class="ok"><AppIcon name="check" :size="28" /></div>
      <p class="center">Pārdevējs saņems tavu piedāvājumu. Tu vari to atcelt, kamēr tas nav izskatīts.</p>
    </template>

    <template #footer>
      <template v-if="step === 'pick'">
        <button class="mbtn" @click="emit('close')">Atcelt</button>
        <button class="mbtn primary" :disabled="busy || !selected" @click="send">{{ busy ? 'Sūta...' : 'Nosūtīt piedāvājumu' }}</button>
      </template>
      <template v-else>
        <button class="mbtn" @click="emit('close')">Aizvērt</button>
        <button class="mbtn primary" @click="toTrades">Skatīt apmaiņas</button>
      </template>
    </template>
  </BaseModal>
</template>

<style scoped>
.small { font-size: 13px; line-height: 1.5; }
.center { text-align: center; font-size: 14px; }
.pick { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; max-height: 300px; overflow-y: auto; }
.opt { display: flex; flex-direction: column; gap: 4px; padding: 6px; text-align: left; background: var(--surface); border: 2px solid transparent; border-radius: 12px; color: #fff; cursor: pointer; }
.opt.on { border-color: var(--cyan); }
.opt img { width: 100%; aspect-ratio: 1; object-fit: cover; border-radius: 8px; background: var(--input); }
.opt b { font-size: 11px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.opt small { color: var(--cyan); font-size: 10px; font-weight: 700; }
.err { color: #f87171; font-size: 13px; font-weight: 600; }
.lnk { background: none; border: 0; color: var(--cyan); text-decoration: underline; font-weight: 700; }
.ok { width: 56px; height: 56px; margin: 4px auto 0; border-radius: 50%; display: grid; place-items: center; background: var(--grad); }
</style>
