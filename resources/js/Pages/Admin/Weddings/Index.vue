<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Button } from '@/components/ui/button'
import { formatDate } from '@/lib/date'

const props = defineProps({
  weddings: { type: Array, default: () => [] }
})

const searchQuery = ref('')
const filterStatus = ref('all')
const sortBy = ref('newest')

const filteredWeddings = computed(() => {
  let result = props.weddings

  // Filter Status
  if (filterStatus.value !== 'all') {
    result = result.filter(w => (w.status || 'draft') === filterStatus.value)
  }

  // Search
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    result = result.filter(w => {
      const couple = getCoupleNames(w).toLowerCase()
      const title = (w.cover_title || '').toLowerCase()
      const subtitle = (w.cover_subtitle || '').toLowerCase()
      const owner = (w.user?.name || '').toLowerCase()
      return couple.includes(q) || title.includes(q) || subtitle.includes(q) || owner.includes(q)
    })
  }

  // Sort
  return result.slice().sort((a, b) => {
    if (sortBy.value === 'newest') return (b.id || 0) - (a.id || 0)
    if (sortBy.value === 'oldest') return (a.id || 0) - (b.id || 0)
    
    const dateA = a.wedding_date ? new Date(a.wedding_date).getTime() : 0
    const dateB = b.wedding_date ? new Date(b.wedding_date).getTime() : 0
    
    if (sortBy.value === 'date_asc') return dateA - dateB
    if (sortBy.value === 'date_desc') return dateB - dateA
    return 0
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
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 class="font-serif text-xl sm:text-2xl lg:text-3xl font-bold text-emerald-950">Daftar Undangan Pernikahan</h2>
          <p class="mt-1 text-xs sm:text-sm text-slate-500">Kelola seluruh undangan, kustomisasi builder tema, dan manajemen tamu undangan.</p>
        </div>
        <Link href="/weddings/create" class="w-full sm:w-auto mt-2 sm:mt-0">
          <Button class="w-full sm:w-auto rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800 transition flex items-center justify-center gap-2">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Buat Undangan
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
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Tamu</p>
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
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total RSVP</p>
                <p class="font-serif text-2xl font-bold text-emerald-950">
                  {{ weddings.reduce((acc, w) => acc + (w.rsvps_count || 0), 0) }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Controls: Search, Filter, Sort -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
          <!-- Search -->
          <div class="relative w-full sm:max-w-sm shrink-0">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </span>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari mempelai atau judul..."
              class="w-full rounded-xl border border-slate-200 py-2.5 pl-9 pr-4 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            />
          </div>
          
          <!-- Filter & Sort -->
          <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:w-auto">
            <select v-model="filterStatus" class="rounded-xl border border-slate-200 py-2.5 pl-3 pr-8 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 bg-white">
              <option value="all">Semua Status</option>
              <option value="published">Published</option>
              <option value="draft">Draft</option>
            </select>

            <select v-model="sortBy" class="rounded-xl border border-slate-200 py-2.5 pl-3 pr-8 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 bg-white">
              <option value="newest">Terbaru Ditambahkan</option>
              <option value="oldest">Terlama Ditambahkan</option>
              <option value="date_asc">Tanggal Akad Dekat</option>
              <option value="date_desc">Tanggal Akad Jauh</option>
            </select>
          </div>
        </div>

        <!-- Cards List -->
        <div v-if="!filteredWeddings.length" class="py-16 text-center bg-white rounded-2xl border border-slate-200 shadow-sm">
          <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
          </div>
          <p class="text-sm font-medium text-slate-700">Tidak ada undangan ditemukan.</p>
          <p class="mt-1 text-xs text-slate-400">Ubah filter pencarian atau buat undangan baru.</p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <!-- Card Template -->
          <div v-for="w in filteredWeddings" :key="w.id" class="group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-all hover:-translate-y-1 hover:shadow-md hover:border-emerald-300">
            <!-- Content -->
            <div class="p-5 flex-1 flex flex-col">
              <!-- Date & Status -->
              <div class="mb-4 flex items-start justify-between gap-2">
                <div class="flex items-center gap-1.5 text-[11px] font-semibold text-slate-500 uppercase tracking-wide">
                  <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  {{ w.wedding_date ? formatDate(w.wedding_date) : 'Belum diatur' }}
                </div>
                <span
                  class="inline-flex shrink-0 items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider"
                  :class="w.status === 'published' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'"
                >
                  <span class="h-1.5 w-1.5 rounded-full" :class="w.status === 'published' ? 'bg-emerald-600' : 'bg-amber-600'" />
                  {{ w.status || 'Draft' }}
                </span>
              </div>

              <!-- Title & Subtitle -->
              <Link :href="`/weddings/${w.id}`" class="block focus:outline-none mb-4">
                <h3 class="font-serif text-xl font-bold text-emerald-950 transition-colors group-hover:text-emerald-700 line-clamp-2 leading-tight">
                  {{ getCoupleNames(w) }}
                </h3>
                <p class="mt-1.5 text-sm text-slate-500 line-clamp-1" :title="w.cover_title">{{ w.cover_title || 'Tanpa Judul' }}</p>
              </Link>

              <!-- Meta Footer -->
              <div class="mt-auto pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-600">
                <div class="flex items-center gap-3">
                  <Link :href="`/weddings/${w.id}/guests`" class="flex items-center gap-1 hover:text-emerald-600 transition" title="Tamu Terdaftar">
                    👥 <span class="font-semibold">{{ w.guests_count || 0 }}</span>
                  </Link>
                  <Link :href="`/weddings/${w.id}/rsvps`" class="flex items-center gap-1 hover:text-emerald-600 transition" title="RSVP Masuk">
                    ✉️ <span class="font-semibold">{{ w.rsvps_count || 0 }}</span>
                  </Link>
                </div>
                <p v-if="w.user" class="text-[11px] font-medium text-slate-400 truncate max-w-[90px]" :title="w.user.name">
                  👤 {{ w.user.name.split(' ')[0] }}
                </p>
              </div>
            </div>

            <!-- Action Grid (Footer) -->
            <div class="grid grid-cols-4 divide-x divide-slate-200 border-t border-slate-200 bg-slate-50/50">
              <Link :href="`/weddings/${w.id}`" class="flex flex-col items-center justify-center gap-1 py-3 text-slate-500 hover:bg-emerald-50 hover:text-emerald-700 transition" title="Detail">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                <span class="text-[10px] font-semibold">Detail</span>
              </Link>
              <Link :href="`/weddings/${w.id}/builder`" class="flex flex-col items-center justify-center gap-1 py-3 text-emerald-600 hover:bg-emerald-100 hover:text-emerald-800 transition bg-emerald-50/30" title="Builder">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" /></svg>
                <span class="text-[10px] font-bold">Builder</span>
              </Link>
              <Link :href="`/weddings/${w.id}/guests`" class="flex flex-col items-center justify-center gap-1 py-3 text-slate-500 hover:bg-emerald-50 hover:text-emerald-700 transition" title="Tamu">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                <span class="text-[10px] font-semibold">Tamu</span>
              </Link>
              <Link :href="`/weddings/${w.id}/edit`" class="flex flex-col items-center justify-center gap-1 py-3 text-slate-500 hover:bg-amber-50 hover:text-amber-700 transition" title="Edit">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                <span class="text-[10px] font-semibold">Edit</span>
              </Link>
            </div>
          </div>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>
