<script setup>
import { defineEmits } from 'vue'
import RsvpBadge from './RsvpBadge.vue'

defineProps({
  guests: { type: Array, default: () => [] }
})

const emit = defineEmits(['copy-link', 'mark-sent'])

function copyLink(guest) {
  emit('copy-link', guest)
}
</script>

<template>
  <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
          <tr>
            <th class="px-4 py-3 font-semibold text-slate-700">Nama Tamu</th>
            <th class="px-4 py-3 font-semibold text-slate-700">Kategori</th>
            <th class="px-4 py-3 font-semibold text-slate-700">Status Undangan</th>
            <th class="px-4 py-3 font-semibold text-slate-700">RSVP</th>
            <th class="px-4 py-3 font-semibold text-slate-700">Pax</th>
            <th class="px-4 py-3 font-semibold text-slate-700">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="guest in guests" :key="guest.id" class="hover:bg-slate-50/50">
            <td class="px-4 py-3 font-medium text-slate-900">{{ guest.name }}</td>
            <td class="px-4 py-3 text-slate-600">{{ guest.category || '-' }}</td>
            <td class="px-4 py-3">
              <span v-if="guest.is_sent" class="text-xs text-emerald-600 font-medium">Terkirim</span>
              <span v-else class="text-xs text-slate-400">Belum Dikirim</span>
            </td>
            <td class="px-4 py-3">
              <RsvpBadge :status="guest.rsvp_status" />
            </td>
            <td class="px-4 py-3 text-slate-600">
              <span v-if="guest.rsvp_status === 'attending'">{{ guest.confirmed_pax }} / {{ guest.max_pax }}</span>
              <span v-else class="text-slate-400">- / {{ guest.max_pax }}</span>
            </td>
            <td class="px-4 py-3">
              <div class="flex gap-2">
                <button
                  class="text-xs text-blue-600 hover:text-blue-800 font-medium"
                  @click="copyLink(guest)"
                >
                  Salin Link
                </button>
                <button
                  v-if="!guest.is_sent"
                  class="text-xs text-emerald-600 hover:text-emerald-800 font-medium"
                  @click="$emit('mark-sent', guest)"
                >
                  Tandai Terkirim
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="!guests.length">
            <td colspan="6" class="px-4 py-8 text-center text-slate-500">
              Belum ada tamu yang ditambahkan.
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
