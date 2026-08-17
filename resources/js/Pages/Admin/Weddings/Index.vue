<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from '@/components/ui/table'
import { Button } from '@/components/ui/button'
import { formatDate } from '@/lib/date'

const props = defineProps({
  weddings: { type: Array, default: () => [] }
})

const searchQuery = ref('')

const filteredWeddings = computed(() => {
  if (!searchQuery.value.trim()) return props.weddings
  const q = searchQuery.value.toLowerCase()
  return props.weddings.filter(w => {
    const couple = getCoupleNames(w).toLowerCase()
    const title = (w.cover_title || '').toLowerCase()
    const subtitle = (w.cover_subtitle || '').toLowerCase()
    const owner = (w.user?.name || '').toLowerCase()
    return couple.includes(q) || title.includes(q) || subtitle.includes(q) || owner.includes(q)
  })
})

function getCoupleNames(wedding) {
  const profiles = wedding.couple_profiles || wedding.coupleProfiles || []

  const groom = profiles.find((p) => p.role === 'groom') || profiles[0]
  const bride = profiles.find((p) => p.role === 'bride') || profiles[1]

  const groomName = groom?.full_name || groom?.name || 'Mempelai Pria'
  const brideName = bride?.full_name || bride?.name || 'Mempelai Wanita'

  return `${groomName} & ${brideName}`
}
</script>

<template>
  <Head title="Daftar Undangan Pernikahan" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 class="font-serif text-3xl font-bold text-emerald-950">Daftar Undangan Pernikahan</h2>
          <p class="mt-1 text-sm text-slate-500">Kelola seluruh undangan, kustomisasi builder tema, dan manajemen tamu undangan.</p>
        </div>
        <Link href="/dashboard/weddings/create">
          <Button class="rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800 transition flex items-center gap-2">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Buat Undangan Baru
          </Button>
        </Link>
      </div>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
        <!-- Quick Stats Banner -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
          <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-3">
              <div class="rounded-xl bg-emerald-50 p-3 text-emerald-700">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Undangan</p>
                <p class="font-serif text-2xl font-bold text-emerald-950">{{ weddings.length }}</p>
              </div>
            </div>
          </div>

          <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-3">
              <div class="rounded-xl bg-amber-50 p-3 text-amber-700">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
              <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Tamu Terdaftar</p>
                <p class="font-serif text-2xl font-bold text-emerald-950">
                  {{ weddings.reduce((acc, w) => acc + (w.guests_count || 0), 0) }}
                </p>
              </div>
            </div>
          </div>

          <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-3">
              <div class="rounded-xl bg-emerald-50 p-3 text-emerald-700">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total RSVP Masuk</p>
                <p class="font-serif text-2xl font-bold text-emerald-950">
                  {{ weddings.reduce((acc, w) => acc + (w.rsvps_count || 0), 0) }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Table Container -->
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
          <!-- Search & Filter Bar -->
          <div class="border-b border-slate-100 p-4 sm:flex sm:items-center sm:justify-between">
            <div class="relative w-full max-w-sm">
              <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </span>
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Cari mempelai atau judul undangan..."
                class="w-full rounded-xl border border-slate-200 py-2 pl-9 pr-4 text-xs shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
              />
            </div>
          </div>

          <div class="p-6">
            <div v-if="!filteredWeddings.length" class="py-12 text-center">
              <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
              </div>
              <p class="text-sm font-medium text-slate-700">Tidak ada undangan ditemukan.</p>
              <p class="mt-1 text-xs text-slate-400">Buat undangan pernikahan baru untuk memulai.</p>
            </div>
            
            <div v-else class="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow class="bg-slate-50/50">
                    <TableHead class="font-semibold text-slate-700">Mempelai &amp; Judul</TableHead>
                    <TableHead class="font-semibold text-slate-700">Tanggal Akad</TableHead>
                    <TableHead class="font-semibold text-slate-700">Status</TableHead>
                    <TableHead class="font-semibold text-slate-700">Ringkasan Tamu</TableHead>
                    <TableHead class="text-right font-semibold text-slate-700">Aksi &amp; Menu</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-for="w in filteredWeddings" :key="w.id" class="hover:bg-slate-50/80 transition">
                    <!-- Mempelai & Judul -->
                    <TableCell>
                      <div>
                        <Link :href="`/dashboard/weddings/${w.id}`" class="font-semibold text-emerald-950 hover:text-emerald-700 transition">
                          {{ getCoupleNames(w) }}
                        </Link>
                        <p class="text-xs text-slate-500">{{ w.cover_title }}</p>
                        <p v-if="w.user" class="mt-0.5 text-[11px] text-slate-400">Owner: {{ w.user.name }}</p>
                      </div>
                    </TableCell>

                    <!-- Tanggal Akad -->
                    <TableCell class="text-xs text-slate-600">
                      {{ w.wedding_date ? formatDate(w.wedding_date) : 'Belum diatur' }}
                    </TableCell>

                    <!-- Status -->
                    <TableCell>
                      <span
                        class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize"
                        :class="w.status === 'published' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'"
                      >
                        <span class="h-1.5 w-1.5 rounded-full" :class="w.status === 'published' ? 'bg-emerald-600' : 'bg-amber-600'" />
                        {{ w.status || 'Draft' }}
                      </span>
                    </TableCell>

                    <!-- Ringkasan Tamu -->
                    <TableCell>
                      <div class="flex items-center gap-2 text-xs">
                        <Link
                          :href="`/dashboard/weddings/${w.id}/guests`"
                          class="rounded-lg bg-slate-100 px-2 py-1 font-medium text-slate-700 hover:bg-emerald-100 hover:text-emerald-800 transition"
                          title="Kelola Tamu Undangan"
                        >
                          👥 {{ w.guests_count || 0 }} Tamu
                        </Link>
                        <Link
                          :href="`/dashboard/weddings/${w.id}/rsvps`"
                          class="rounded-lg bg-slate-100 px-2 py-1 font-medium text-slate-700 hover:bg-emerald-100 hover:text-emerald-800 transition"
                          title="Lihat RSVP"
                        >
                          ✉️ {{ w.rsvps_count || 0 }} RSVP
                        </Link>
                      </div>
                    </TableCell>

                    <!-- Aksi & Menu -->
                    <TableCell class="text-right">
                      <div class="flex items-center justify-end gap-1.5">
                        <!-- Detail Action Button -->
                        <Link :href="`/dashboard/weddings/${w.id}`">
                          <Button size="sm" variant="outline" class="h-8 rounded-lg border-emerald-200 text-xs font-semibold text-emerald-800 hover:bg-emerald-50">
                            Detail
                          </Button>
                        </Link>

                        <!-- Builder Button -->
                        <Link :href="`/dashboard/weddings/${w.id}/builder`">
                          <Button size="sm" class="h-8 rounded-lg bg-emerald-700 text-xs font-semibold text-white hover:bg-emerald-800">
                            Builder
                          </Button>
                        </Link>

                        <!-- Kelola Tamu Button -->
                        <Link :href="`/dashboard/weddings/${w.id}/guests`">
                          <Button size="sm" variant="ghost" class="h-8 rounded-lg text-xs font-medium text-slate-600 hover:bg-slate-100">
                            Tamu
                          </Button>
                        </Link>

                        <!-- Edit Button -->
                        <Link :href="`/dashboard/weddings/${w.id}/edit`">
                          <Button size="sm" variant="ghost" class="h-8 rounded-lg text-xs font-medium text-slate-600 hover:bg-slate-100">
                            Edit
                          </Button>
                        </Link>
                      </div>
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