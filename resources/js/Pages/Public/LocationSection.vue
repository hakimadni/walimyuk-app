<script setup>
import { formatDate, formatTime } from '@/lib/date'

defineProps({
  events: { type: Array, default: () => [] }
})
</script>

<template>
  <div class="flex h-full min-h-0 flex-col px-5 py-8">
    <h2 class="mb-5 text-center font-serif text-2xl font-bold text-emerald-900">Lokasi Acara</h2>

    <div v-if="events && events.length" class="flex-1 space-y-4 overflow-y-auto pb-2">
      <div
        v-for="event in events"
        :key="event.id"
        class="px-5 py-5 border-b border-slate-200/50 last:border-0 text-center"
      >
        <h3 class="mb-1 font-serif text-base font-bold text-emerald-900">{{ event.title }}</h3>
        <p v-if="event.venue_name" class="mb-1 text-sm font-semibold text-slate-700">{{ event.venue_name }}</p>
        <p v-if="event.address" class="mb-4 text-xs text-slate-500 leading-relaxed">{{ event.address }}</p>

        <a
          v-if="event.maps_url"
          :href="event.maps_url"
          target="_blank"
          rel="noopener"
          class="flex items-center justify-center gap-2 rounded-xl bg-emerald-700 py-2.5 text-sm font-semibold text-white shadow-sm transition active:scale-95"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
          Buka Google Maps
        </a>
        <p v-else class="text-xs italic text-slate-400 text-center">Link Google Maps belum tersedia</p>
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
