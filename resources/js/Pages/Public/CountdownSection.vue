<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { resolveBuilder } from '@/lib/invitationTheme'

const props = defineProps({
  wedding: { type: Object, default: () => ({}) },
  events: { type: Array, default: () => [] },
  weddingDate: { type: String, default: null },
  themeConfig: { type: Object, default: () => ({}) },
})

const builder = computed(() => resolveBuilder(props.themeConfig))
const palette = computed(() => builder.value.content.palette)

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

const calendarUrl = computed(() => {
  if (!props.weddingDate) return null
  try {
    const start = new Date(props.weddingDate)
    const end = new Date(start.getTime() + 4 * 60 * 60 * 1000) // 4 jam
    
    const padNum = n => String(n).padStart(2, '0')
    const toIso = d => d.getUTCFullYear() + padNum(d.getUTCMonth() + 1) + padNum(d.getUTCDate()) + 'T' + padNum(d.getUTCHours()) + padNum(d.getUTCMinutes()) + '00Z'
    
    const title = encodeURIComponent(props.wedding?.cover_title || 'Undangan Pernikahan')
    const venue = props.events?.[0]?.venue_name || ''
    const address = props.events?.[0]?.address || ''
    const location = encodeURIComponent(`${venue} ${address}`.trim())
    const details = encodeURIComponent(`Undangan Pernikahan: ${props.wedding?.cover_title || ''}\nTanggal: ${formattedDate.value || ''}\nLokasi: ${venue}`)
    
    return `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${title}&dates=${toIso(start)}/${toIso(end)}&details=${details}&location=${location}`
  } catch {
    return null
  }
})

onMounted(() => { updateCountdown(); timer = setInterval(updateCountdown, 1000) })
onUnmounted(() => clearInterval(timer))
</script>

<template>
  <div class="flex h-full min-h-0 flex-col items-center justify-center px-5 py-8 text-center">
    <!-- Header Hint -->
    <p class="mb-1 text-[10px] font-bold uppercase tracking-[0.25em] aos-item aos-fade-down" :style="{ color: palette.secondary }">Insya Allah</p>
    <!-- Header Title -->
    <h2 class="mb-2 font-serif text-2xl font-bold aos-item aos-fade-down aos-delay-100" :style="{ color: palette.primary }">Menuju Hari Bahagia</h2>
    <p v-if="formattedDate" class="mb-6 text-sm font-medium aos-item aos-fade-up aos-delay-150" :style="{ color: palette.primary }">{{ formattedDate }}</p>
    <p v-else class="mb-6 text-sm italic aos-item aos-fade-up aos-delay-150" :style="{ color: palette.secondary }">Tanggal belum diatur</p>

    <!-- Countdown grid with Secondary Cards -->
    <div v-if="!isPast && weddingDate" class="mb-5 grid grid-cols-4 gap-2.5 w-full max-w-xs aos-item aos-zoom-in aos-delay-200">
      <div
        v-for="(val, label) in [
          [pad(days), 'Hari'],
          [pad(hours), 'Jam'],
          [pad(minutes), 'Menit'],
          [pad(seconds), 'Detik']
        ]"
        :key="label"
        class="flex flex-col items-center justify-center py-3.5 rounded-2xl border shadow-sm backdrop-blur-sm transition-transform duration-300 hover:scale-105"
        :style="{ backgroundColor: `${palette.secondary}15`, borderColor: `${palette.secondary}44` }"
      >
        <span class="text-2xl font-bold tabular-nums" :style="{ color: palette.primary }">{{ val[0] }}</span>
        <span class="mt-0.5 text-[10px] font-bold uppercase tracking-wider" :style="{ color: palette.secondary }">{{ val[1] }}</span>
      </div>
    </div>

    <!-- Add to Calendar Button -->
    <a
      v-if="calendarUrl && !isPast"
      :href="calendarUrl"
      target="_blank"
      rel="noopener noreferrer"
      class="mb-6 inline-flex items-center gap-2 rounded-full border px-4 py-2 text-xs font-bold shadow-sm transition active:scale-95 aos-item aos-fade-up aos-delay-300 anim-pulse-soft"
      :style="{ backgroundColor: `${palette.secondary}20`, borderColor: `${palette.secondary}66`, color: palette.primary }"
    >
      <svg class="h-4 w-4" :style="{ color: palette.secondary }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
      </svg>
      Simpan ke Google Calendar
    </a>

    <!-- Already passed -->
    <div
      v-else-if="isPast"
      class="mb-6 w-full max-w-xs py-5 rounded-2xl border px-4 aos-item aos-zoom-in"
      :style="{ backgroundColor: `${palette.secondary}15`, borderColor: `${palette.secondary}44` }"
    >
      <p class="text-2xl">🎊</p>
      <p class="mt-1 text-sm font-bold" :style="{ color: palette.primary }">Alhamdulillah</p>
      <p class="text-xs mt-0.5" :style="{ color: palette.secondary }">Semoga menjadi keluarga sakinah mawaddah warahmah</p>
    </div>

    <p class="text-[11px] italic px-4 leading-relaxed opacity-80 aos-item aos-fade-up aos-delay-400" :style="{ color: palette.secondary }">
      Barakallahu lakuma wa baraka 'alaikuma wa jama'a bainakuma fi khair
    </p>
  </div>
</template>
