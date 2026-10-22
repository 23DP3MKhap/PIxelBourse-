<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import BaseModal from './BaseModal.vue'
import AppIcon from './AppIcon.vue'
import { session, loadUser } from '../session'
import { listingsApi, apiMessage } from '../api/listings'
import { fmt } from '../data'

const props = defineProps({ listing: Object })
const emit = defineEmits(['close', 'done'])
const router = useRouter()

const balance = computed(() => session.user?.coin_balance ?? 0)
const after = computed(() => balance.value - props.listing.price)
const enough = computed(() => after.value >= 0)
const step = ref('confirm') // confirm -> done
const busy = ref(false)
const error = ref('')

async function pay() {
  busy.value = true
  error.value = ''
  try {
    await listingsApi.buy(props.listing.id)
    await loadUser() // fresh coin_balance
    step.value = 'done'
    emit('done')
  } catch (e) {
    if (e.errors?.coin_balance) error.value = 'Tev nepietiek monētu šim pirkumam.'
    else if (e.status === 409) error.value = 'Šis sludinājums vairs nav aktīvs.'
    else error.value = apiMessage(e)
  } finally {
    busy.value = false
  }
}

const toCollection = () => { emit('close'); router.push('/collection') }
</script>

<template>
  <BaseModal :title="step === 'done' ? 'Pirkums veiksmīgs' : 'Apstiprini pirkumu'" small @close="emit('close')">
    <template v-if="step === 'confirm'">
      <div class="item">
        <img :src="listing.url" :alt="listing.title || ''" />
        <div><b>{{ listing.title || 'Bez nosaukuma' }}</b><span class="muted">Vērtība {{ fmt(listing.value) }} $BORIS</span></div>
      </div>
      <dl class="sum">
        <div><dt>Cena</dt><dd>{{ fmt(listing.price) }} $BORIS</dd></div>
        <div><dt>Tavs atlikums</dt><dd>{{ fmt(balance) }} $BORIS</dd></div>
        <div class="total"><dt>Atlikums pēc pirkuma</dt><dd :class="{ bad: !enough }">{{ fmt(after) }} $BORIS</dd></div>
      </dl>
      <p v-if="!enough" class="err">Tev nepietiek monētu šim pirkumam.</p>
      <p v-if="error" class="err">{{ error }}</p>
    </template>

    <template v-else>
      <div class="ok"><AppIcon name="check" :size="28" /></div>
      <p class="center"><b>{{ listing.title || 'Attēls' }}</b> tagad ir tavā kolekcijā.</p>
      <p class="center muted">Jaunais atlikums: {{ fmt(balance) }} $BORIS</p>
    </template>

    <template #footer>
      <template v-if="step === 'confirm'">
        <button class="mbtn" @click="emit('close')">Atcelt</button>
        <button class="mbtn primary" :disabled="busy || !enough" @click="pay">{{ busy ? 'Pērk...' : `Pirkt par ${fmt(listing.price)}` }}</button>
      </template>
      <template v-else>
        <button class="mbtn" @click="emit('close')">Aizvērt</button>
        <button class="mbtn primary" @click="toCollection">Uz kolekciju</button>
      </template>
    </template>
  </BaseModal>
</template>

<style scoped>
.item { display: flex; gap: 12px; align-items: center; }
.item img { width: 64px; height: 64px; border-radius: 12px; object-fit: cover; background: var(--input); }
.item div { display: flex; flex-direction: column; gap: 4px; min-width: 0; }
.item span { font-size: 12px; }
.sum { display: flex; flex-direction: column; gap: 10px; margin: 0; padding: 14px; border-radius: 14px; background: var(--surface); border: 1px solid var(--border); }
.sum div { display: flex; justify-content: space-between; font-size: 13px; }
.sum dt { color: var(--muted); } .sum dd { margin: 0; font-weight: 700; }
.total { padding-top: 10px; border-top: 1px solid var(--border); }
.bad { color: #f87171; }
.err { color: #f87171; font-size: 13px; font-weight: 600; }
.ok { width: 56px; height: 56px; margin: 4px auto 0; border-radius: 50%; display: grid; place-items: center; background: var(--grad); }
.center { text-align: center; font-size: 14px; }
</style>
