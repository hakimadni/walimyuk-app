<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'

const props = defineProps({
  weddingDate: { type: String, default: null }
})

const days = ref(0)
const hours = ref(0)
const minutes = ref(0)
const seconds = ref(0)
const isPast = ref(false)
let timer = null

function updateCountdown() {
  if (!props.weddingDate) return
  const target = new Date(props.weddingDate)
  const now = new Date()
  const diff = target.getTime() - now.getTime()
  if (diff <= 0) {
    isPast.value = true
    days.value = hours.value = minutes.value = seconds.value = 0
    return
  }
  isPast.value = false
  days.value = Math.floor(diff / 86400000)
  hours.value = Math.floor((diff % 86400000) / 3600000)
  minutes.value = Math.floor((diff % 3600000) / 60000)
  seconds.value = Math.floor((diff % 60000) / 1000)
}

const formattedDate = computed(() => {
  if (!props.weddingDate) return null
  try {
    return new Intl.DateTimeFormat('id-ID', {
      weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
    }).format(new Date(props.weddingDate))
  } catch { return props.weddingDate }
})

const pad = n => String(n).padStart(2, '0')

onMounted(() => { updateCountdown(); timer = setInterval(updateCountdown, 1000) })
onUnmounted(() => clearInterval(timer))
</script>

<template>
  <div class="flex h-full min-h-0 flex-col items-center justify-center px-5 py-8 text-center">
    <!-- Header -->
    <p class="mb-1 text-[10px] font-bold uppercase tracking-[0.25em] text-emerald-700">Insya Allah</p>
    <h2 class="mb-3 font-serif text-2xl font-bold text-emerald-900">Menuju Hari Bahagia</h2>
    <p v-if="formattedDate" class="mb-6 text-sm text-slate-500">{{ formattedDate }}</p>
    <p v-else class="mb-6 text-sm italic text-slate-400">Tanggal belum diatur</p>

    <!-- Countdown grid -->
    <div v-if="!isPast && weddingDate" class="mb-6 grid grid-cols-4 gap-2 w-full max-w-xs">
      <div v-for="(val, label) in [
        [pad(days), 'Hari'],
        [pad(hours), 'Jam'],
        [pad(minutes), 'Menit'],
        [pad(seconds), 'Detik']
      ]" :key="label" class="flex flex-col items-center justify-center py-4">
        <span class="text-xl font-bold text-emerald-800 tabular-nums">{{ val[0] }}</span>
        <span class="mt-0.5 text-[10px] text-slate-500">{{ val[1] }}</span>
      </div>
    </div>

    <!-- Already passed -->
    <div v-else-if="isPast" class="mb-6 w-full py-6">
      <p class="text-2xl">🎊</p>
      <p class="mt-1 text-sm font-semibold text-emerald-800">Alhamdulillah</p>
      <p class="text-xs text-emerald-600 mt-0.5">Semoga menjadi keluarga sakinah mawaddah warahmah</p>
    </div>

    <p class="text-[11px] text-slate-400 italic px-4 leading-relaxed">
      Barakallahu lakuma wa baraka 'alaikuma wa jama'a bainakuma fi khair
    </p>
  </div>
</template>
