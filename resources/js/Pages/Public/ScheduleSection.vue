<script setup>
import { formatDate, formatTime } from '@/lib/date'

defineProps({
  events: { type: Array, default: () => [] }
})
</script>

<template>
  <div class="flex h-full min-h-0 flex-col px-5 py-8">
    <h2 class="mb-5 text-center font-serif text-2xl font-bold text-emerald-900">Jadwal Acara</h2>

    <div v-if="events && events.length" class="flex-1 space-y-4 overflow-y-auto pb-2">
      <div
        v-for="event in events"
        :key="event.id"
        class="px-5 py-5 border-b border-slate-200/50 last:border-0"
      >
        <!-- Title badge -->
        <div class="mb-3 flex items-center justify-center gap-2">
          <div class="h-2.5 w-2.5 rounded-full bg-emerald-600 flex-shrink-0" />
          <h3 class="font-serif text-lg font-bold text-emerald-900">{{ event.title }}</h3>
        </div>

        <!-- Date & time -->
        <div class="mb-3 flex flex-col items-center justify-center gap-3 text-sm text-slate-600 text-center">
          <div class="flex-shrink-0 rounded-xl bg-emerald-50 px-3 py-2 text-center min-w-[60px] border border-emerald-100">
            <p class="text-[10px] font-bold uppercase text-emerald-700">
              {{ event.date ? new Intl.DateTimeFormat('id-ID', { month: 'short' }).format(new Date(event.date)) : '—' }}
            </p>
            <p class="text-xl font-bold text-emerald-900 leading-none mt-0.5">
              {{ event.date ? new Date(event.date).getDate() : '—' }}
            </p>
          </div>
          <div class="pt-1">
            <p class="font-semibold text-slate-700 text-sm">
              {{ event.start_time ? formatTime(event.start_time) : '' }}
              <span v-if="event.end_time">— {{ formatTime(event.end_time) }}</span>
              <span v-else-if="event.start_time"> WIB</span>
            </p>
            <p class="text-xs text-slate-500 mt-0.5">{{ event.date ? formatDate(event.date) : 'Tanggal belum diisi' }}</p>
          </div>
        </div>

        <!-- Venue -->
        <div v-if="event.venue_name" class="flex flex-col items-center gap-1 text-xs text-slate-600 text-center">
          <svg class="mt-0.5 h-3.5 w-3.5 flex-shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
          </svg>
          <div>
            <p class="font-semibold text-slate-700">{{ event.venue_name }}</p>
            <p v-if="event.address" class="text-slate-500 mt-0.5 leading-relaxed">{{ event.address }}</p>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="flex flex-1 items-center justify-center">
      <div class="text-center">
        <p class="text-2xl mb-2">📅</p>
        <p class="text-sm text-slate-400 italic">Jadwal acara belum diisi</p>
      </div>
    </div>
  </div>
</template>
