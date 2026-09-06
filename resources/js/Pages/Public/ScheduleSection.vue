<script setup>
import { computed } from 'vue'
import { formatDate, formatTime } from '@/lib/date'
import { resolveBuilder } from '@/lib/invitationTheme'

const props = defineProps({
  events: { type: Array, default: () => [] },
  themeConfig: { type: Object, default: () => ({}) },
})

const builder = computed(() => resolveBuilder(props.themeConfig))
const palette = computed(() => builder.value.content.palette)
</script>

<template>
  <div class="flex h-full min-h-0 flex-col px-5 py-8">
    <h2 class="mb-5 text-center font-serif text-2xl font-bold aos-item aos-fade-down" :style="{ color: palette.primary }">
      Jadwal Acara
    </h2>

    <div v-if="events && events.length" class="flex-1 space-y-4 overflow-y-auto pb-2">
      <div
        v-for="(event, idx) in events"
        :key="event.id"
        class="rounded-2xl border px-5 py-5 shadow-sm backdrop-blur-sm aos-item aos-fade-up"
        :class="idx === 0 ? 'aos-delay-150' : 'aos-delay-300'"
        :style="{ backgroundColor: `${palette.secondary}12`, borderColor: `${palette.secondary}44` }"
      >
        <!-- Title & hint indicator -->
        <div class="mb-3 flex items-center justify-center gap-2">
          <div class="h-2.5 w-2.5 rounded-full flex-shrink-0 anim-pulse-soft" :style="{ backgroundColor: palette.secondary }" />
          <h3 class="font-serif text-lg font-bold" :style="{ color: palette.primary }">{{ event.title }}</h3>
        </div>

        <!-- Date & time card/badge -->
        <div class="mb-3 flex flex-col items-center justify-center gap-3 text-sm text-center">
          <div
            class="flex-shrink-0 rounded-xl px-4 py-2 text-center min-w-[70px] border shadow-xs aos-item aos-zoom-in aos-delay-200"
            :style="{ backgroundColor: `${palette.secondary}20`, borderColor: `${palette.secondary}60` }"
          >
            <p class="text-[10px] font-bold uppercase tracking-wider" :style="{ color: palette.secondary }">
              {{ event.date ? new Intl.DateTimeFormat('id-ID', { month: 'short' }).format(new Date(event.date)) : '—' }}
            </p>
            <p class="text-2xl font-bold leading-none mt-0.5" :style="{ color: palette.primary }">
              {{ event.date ? new Date(event.date).getDate() : '—' }}
            </p>
          </div>
          <div class="pt-0.5">
            <p class="font-bold text-sm tracking-wide" :style="{ color: palette.primary }">
              {{ event.start_time ? formatTime(event.start_time) : '' }}
              <span v-if="event.end_time">— {{ formatTime(event.end_time) }}</span>
              <span v-else-if="event.start_time"> WIB</span>
            </p>
            <p class="text-xs mt-0.5 opacity-80" :style="{ color: palette.secondary }">
              {{ event.date ? formatDate(event.date) : 'Tanggal belum diisi' }}
            </p>
          </div>
        </div>

        <!-- Venue -->
        <div v-if="event.venue_name" class="flex flex-col items-center gap-1 text-xs text-center pt-1 border-t aos-item aos-fade-up aos-delay-350" :style="{ borderColor: `${palette.secondary}33` }">
          <p class="font-semibold text-sm mt-1" :style="{ color: palette.primary }">{{ event.venue_name }}</p>
          <p v-if="event.address" class="leading-relaxed opacity-85" :style="{ color: palette.primary }">{{ event.address }}</p>
        </div>
      </div>
    </div>

    <div v-else class="flex flex-1 items-center justify-center">
      <div class="text-center">
        <p class="text-2xl mb-2">📅</p>
        <p class="text-sm italic opacity-60" :style="{ color: palette.primary }">Jadwal acara belum diisi</p>
      </div>
    </div>
  </div>
</template>
