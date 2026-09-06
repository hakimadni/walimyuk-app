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

function getMapEmbedUrl(event) {
  if (event.latitude && event.longitude) {
    const label = encodeURIComponent(event.venue_name || 'Lokasi Acara')
    return `https://maps.google.com/maps?q=${event.latitude},${event.longitude}+(${label})&t=&z=15&ie=UTF8&iwloc=&output=embed`
  }
  const query = encodeURIComponent(`${event.venue_name || ''} ${event.address || ''}`.trim())
  if (query) {
    return `https://maps.google.com/maps?q=${query}&t=&z=15&ie=UTF8&iwloc=&output=embed`
  }
  return null
}
</script>

<template>
  <div class="flex h-full min-h-0 flex-col px-5 py-8">
    <h2 class="mb-5 text-center font-serif text-2xl font-bold aos-item aos-fade-down" :style="{ color: palette.primary }">
      Lokasi Acara
    </h2>

    <div v-if="events && events.length" class="flex-1 space-y-4 overflow-y-auto pb-2">
      <div
        v-for="(event, idx) in events"
        :key="event.id"
        class="rounded-2xl border px-5 py-5 shadow-sm backdrop-blur-sm text-center aos-item aos-fade-up"
        :class="idx === 0 ? 'aos-delay-150' : 'aos-delay-300'"
        :style="{ backgroundColor: `${palette.secondary}12`, borderColor: `${palette.secondary}44` }"
      >
        <h3 class="mb-1 font-serif text-base font-bold aos-item aos-fade-up aos-delay-100" :style="{ color: palette.primary }">{{ event.title }}</h3>
        <p v-if="event.venue_name" class="mb-1 text-sm font-bold aos-item aos-fade-up aos-delay-150" :style="{ color: palette.primary }">{{ event.venue_name }}</p>
        <p v-if="event.address" class="mb-4 text-xs leading-relaxed opacity-85 aos-item aos-fade-up aos-delay-200" :style="{ color: palette.primary }">{{ event.address }}</p>

        <div
          v-if="getMapEmbedUrl(event)"
          class="mb-4 overflow-hidden rounded-xl border shadow-sm aos-item aos-zoom-in aos-delay-250"
          :style="{ borderColor: `${palette.secondary}44` }"
        >
          <iframe
            width="100%"
            height="200"
            frameborder="0"
            style="border:0"
            referrerpolicy="no-referrer-when-downgrade"
            :src="getMapEmbedUrl(event)"
            allowfullscreen
          ></iframe>
        </div>

        <a
          v-if="event.google_maps_url"
          :href="event.google_maps_url"
          target="_blank"
          rel="noopener"
          class="flex items-center justify-center gap-2 rounded-xl py-2.5 text-sm font-bold text-white shadow-md transition active:scale-95 aos-item aos-fade-up aos-delay-300"
          :style="{ backgroundColor: palette.primary, border: `1px solid ${palette.secondary}88` }"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
          Buka Google Maps
        </a>
        <p v-else class="text-xs italic text-center" :style="{ color: palette.secondary }">
          Link Google Maps belum tersedia
        </p>
      </div>
    </div>

    <div v-else class="flex flex-1 items-center justify-center">
      <div class="text-center">
        <p class="text-2xl mb-2">📍</p>
        <p class="text-sm text-slate-400 italic">Lokasi acara belum diisi</p>
      </div>
    </div>
  </div>
</template>
