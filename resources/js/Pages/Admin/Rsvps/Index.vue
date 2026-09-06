<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from '@/components/ui/table'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'

const props = defineProps({
  wedding: { type: Object, required: true },
  rsvps: { type: [Object, Array], default: () => [] },
  analytics: { type: Object, default: () => ({}) },
  filters: { type: Object, default: () => ({ status: '' }) },
})

const rsvpList = computed(() => {
  if (Array.isArray(props.rsvps)) return props.rsvps
  return props.rsvps?.data || []
})

const statusFilter = ref(props.filters.status || '')
const searchQuery = ref('')

function filterStatus() {
  router.get(
    `/weddings/${props.wedding.id}/rsvps`,
    { status: statusFilter.value },
    { preserveState: true, preserveScroll: true }
  )
}

const filteredRsvps = computed(() => {
  if (!searchQuery.value.trim()) return rsvpList.value
  const q = searchQuery.value.toLowerCase()
  return rsvpList.value.filter(r => {
    const name = (r.guest_name_confirmed || r.guest?.name || '').toLowerCase()
    const phone = (r.phone_number || r.guest?.phone_number || '').toLowerCase()
    const comment = (r.comment || '').toLowerCase()
    return name.includes(q) || phone.includes(q) || comment.includes(q)
  })
})

function formatDateTime(str) {
  if (!str) return '-'
  const d = new Date(str)
  return d.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>

<template>
  <Head :title="`Data RSVP & Katering - ${wedding.cover_subtitle || wedding.cover_title}`" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <div class="flex items-center gap-2">
            <Link :href="`/weddings/${wedding.id}`" class="text-xs text-emerald-700 hover:underline">&larr; Kembali ke Detail</Link>
          </div>
          <h2 class="font-serif text-3xl font-bold text-emerald-950">Rekap RSVP &amp; Kalkulator Katering</h2>
          <p class="mt-1 text-sm text-slate-500">{{ wedding.cover_title }} • Pantau konfirmasi kehadiran real-time.</p>
        </div>
        <div class="flex items-center gap-2">
          <Link :href="`/weddings/${wedding.id}/rsvps/analytics`">
            <Button variant="outline" class="rounded-xl border-emerald-300 text-xs font-semibold text-emerald-800 hover:bg-emerald-50">
              📊 Analisis Katering Detail
            </Button>
          </Link>

          <a :href="`/weddings/${wedding.id}/rsvps/export`" download>
            <Button class="rounded-xl bg-emerald-700 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-800 transition flex items-center gap-1.5">
              <span>📤</span> Unduh Rekap (CSV/Excel)
            </Button>
          </a>
        </div>
      </div>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
        <!-- Catering Recommendation Banner -->
        <div class="relative overflow-hidden rounded-3xl border border-emerald-800 bg-gradient-to-br from-emerald-950 via-emerald-900 to-emerald-950 p-6 text-white shadow-xl sm:p-8">
          <div class="grid gap-6 md:grid-cols-12 items-center">
            <div class="md:col-span-8 space-y-2">
              <div class="inline-flex items-center gap-2 rounded-full bg-amber-400/20 px-3 py-1 text-xs font-semibold text-amber-300">
                <span>🍛</span> Rekomendasi Porsi Katering Cerdas
              </div>
              <h3 class="font-serif text-2xl font-bold text-white sm:text-3xl">
                {{ analytics.recommended_catering_pax || analytics.confirmed_pax || 0 }} Porsi Prasmanan
              </h3>
              <p class="text-xs text-emerald-100/90 leading-relaxed max-w-2xl">
                Dihitung dari <strong>{{ analytics.confirmed_pax || 0 }} Pax terkonfirmasi hadir</strong> + Safety Buffer cadangan <strong>{{ analytics.buffer_percentage || 10 }}%</strong>. Mencegah makanan mubazir sekaligus menghindari kekurangan porsi saat acara.
              </p>
            </div>

            <div class="md:col-span-4 rounded-2xl bg-white/10 p-4 backdrop-blur-sm border border-white/15 text-center">
              <p class="text-xs text-emerald-200 uppercase tracking-wider font-semibold">Tingkat Respon Tamu</p>
              <div class="mt-2 font-serif text-3xl font-bold text-amber-300">
                {{ analytics.invited_guest_count ? Math.round((analytics.rsvp_submitted_count / analytics.invited_guest_count) * 100) : 0 }}%
              </div>
              <p class="text-[11px] text-emerald-200 mt-1">
                {{ analytics.rsvp_submitted_count || 0 }} dari {{ analytics.invited_guest_count || 0 }} tamu telah merespon
              </p>
            </div>
          </div>
        </div>

        <!-- 4 Metric Cards -->
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
          <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Pax Hadir</p>
            <p class="font-serif text-2xl font-bold text-emerald-800 mt-1">{{ analytics.confirmed_pax || 0 }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">{{ analytics.attending_guest_count || 0 }} undangan konfirmasi</p>
          </div>

          <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-rose-500">Tidak Hadir</p>
            <p class="font-serif text-2xl font-bold text-rose-700 mt-1">{{ analytics.declined_guest_count || 0 }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Tamu berhalangan</p>
          </div>

          <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-amber-500">Ragu-ragu</p>
            <p class="font-serif text-2xl font-bold text-amber-700 mt-1">{{ analytics.maybe_guest_count || 0 }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Belum pasti hadir</p>
          </div>

          <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Belum Respon</p>
            <p class="font-serif text-2xl font-bold text-slate-700 mt-1">{{ analytics.pending_rsvp_count || 0 }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Perlu difollow-up via WA</p>
          </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
          <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="relative w-full sm:w-72">
              <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </span>
              <Input
                v-model="searchQuery"
                placeholder="Cari nama atau pesan..."
                class="pl-9 text-xs"
              />
            </div>

            <div class="flex items-center gap-2">
              <select
                v-model="statusFilter"
                @change="filterStatus"
                class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs focus:border-emerald-500 focus:outline-none"
              >
                <option value="">Semua Status Kehadiran</option>
                <option value="attending">Hadir</option>
                <option value="not_attending">Tidak Hadir</option>
                <option value="maybe">Ragu-ragu</option>
              </select>
            </div>
          </div>
        </div>

        <!-- RSVP Responses Table -->
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
          <div class="p-6">
            <div v-if="!filteredRsvps.length" class="py-12 text-center">
              <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                💌
              </div>
              <p class="text-sm font-medium text-slate-700">Belum ada data konfirmasi RSVP.</p>
              <p class="mt-1 text-xs text-slate-400">Data akan muncul otomatis ketika tamu membuka link undangan dan mengisi form RSVP.</p>
            </div>

            <div v-else class="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow class="bg-slate-50/50">
                    <TableHead class="font-semibold text-slate-700">Nama Tamu</TableHead>
                    <TableHead class="font-semibold text-slate-700">Status Kehadiran</TableHead>
                    <TableHead class="font-semibold text-slate-700">Jumlah Pax</TableHead>
                    <TableHead class="font-semibold text-slate-700">Pesan / Catatan</TableHead>
                    <TableHead class="font-semibold text-slate-700">Waktu Submit</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-for="rsvp in filteredRsvps" :key="rsvp.id" class="hover:bg-slate-50/80 transition">
                    <!-- Guest Name -->
                    <TableCell>
                      <div>
                        <p class="font-bold text-slate-900">{{ rsvp.guest_name_confirmed || rsvp.guest?.name || 'Tamu' }}</p>
                        <p v-if="rsvp.phone_number || rsvp.guest?.phone_number" class="text-[11px] text-slate-400 font-mono">
                          {{ rsvp.phone_number || rsvp.guest?.phone_number }}
                        </p>
                      </div>
                    </TableCell>

                    <!-- Status -->
                    <TableCell>
                      <span
                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                        :class="{
                          'bg-emerald-100 text-emerald-800': rsvp.attendance_status === 'attending',
                          'bg-rose-100 text-rose-800': rsvp.attendance_status === 'not_attending',
                          'bg-amber-100 text-amber-800': rsvp.attendance_status === 'maybe',
                        }"
                      >
                        {{
                          rsvp.attendance_status === 'attending'
                            ? '✓ Hadir'
                            : rsvp.attendance_status === 'not_attending'
                            ? '✕ Tidak Hadir'
                            : '❓ Ragu-ragu'
                        }}
                      </span>
                    </TableCell>

                    <!-- Pax -->
                    <TableCell class="text-xs font-medium text-slate-800">
                      <span v-if="rsvp.attendance_status === 'attending'" class="font-bold text-emerald-700">
                        {{ rsvp.pax_count }} Orang
                      </span>
                      <span v-else class="text-slate-400">0 Pax</span>
                    </TableCell>

                    <!-- Comment -->
                    <TableCell class="text-xs text-slate-600 max-w-sm">
                      <p class="truncate" :title="rsvp.comment">{{ rsvp.comment || '-' }}</p>
                    </TableCell>

                    <!-- Time -->
                    <TableCell class="text-xs text-slate-400 whitespace-nowrap">
                      {{ formatDateTime(rsvp.submitted_at) }}
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
