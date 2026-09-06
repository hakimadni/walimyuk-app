<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'

const props = defineProps({
  wedding: { type: Object, required: true },
  wishes: { type: [Object, Array], default: () => [] },
  stats: { type: Object, default: () => ({ total: 0, approved: 0, pending: 0, rejected: 0 }) },
  filters: { type: Object, default: () => ({ status: '' }) },
})

const wishList = computed(() => {
  if (Array.isArray(props.wishes)) return props.wishes
  return props.wishes?.data || []
})

const statusFilter = ref(props.filters.status || '')
const searchQuery = ref('')

function filterStatus(status) {
  statusFilter.value = status
  router.get(
    `/weddings/${props.wedding.id}/wishes`,
    { status: statusFilter.value },
    { preserveState: true, preserveScroll: true }
  )
}

const filteredWishes = computed(() => {
  if (!searchQuery.value.trim()) return wishList.value
  const q = searchQuery.value.toLowerCase()
  return wishList.value.filter(w => {
    const name = (w.name || w.guest?.name || '').toLowerCase()
    const msg = (w.message || '').toLowerCase()
    return name.includes(q) || msg.includes(q)
  })
})

function approve(wish) {
  router.put(`/weddings/${props.wedding.id}/wishes/${wish.id}/approve`, {}, {
    preserveScroll: true,
  })
}

function reject(wish) {
  router.put(`/weddings/${props.wedding.id}/wishes/${wish.id}/reject`, {}, {
    preserveScroll: true,
  })
}

function deleteWish(wish) {
  if (confirm(`Hapus ucapan dan doa dari "${wish.name || wish.guest?.name || 'Tamu'}"?`)) {
    router.delete(`/weddings/${props.wedding.id}/wishes/${wish.id}`, {
      preserveScroll: true,
    })
  }
}

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
  <Head :title="`Moderasi Ucapan & Doa - ${wedding.cover_subtitle || wedding.cover_title}`" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <div class="flex items-center gap-2">
            <Link :href="`/weddings/${wedding.id}`" class="text-xs text-emerald-700 hover:underline">&larr; Kembali ke Detail</Link>
          </div>
          <h2 class="font-serif text-3xl font-bold text-emerald-950">Moderasi Dinding Ucapan &amp; Doa</h2>
          <p class="mt-1 text-sm text-slate-500">{{ wedding.cover_title }} • Filter dan kelola doa kebaikan dari para tamu.</p>
        </div>
      </div>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
        <!-- Stats Summary -->
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
          <div
            @click="filterStatus('')"
            class="cursor-pointer rounded-2xl border p-4 shadow-sm transition hover:shadow-md"
            :class="statusFilter === '' ? 'border-emerald-600 bg-emerald-50/50' : 'border-slate-200 bg-white'"
          >
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Ucapan</p>
            <p class="font-serif text-2xl font-bold text-slate-900 mt-1">{{ stats.total || 0 }}</p>
            <p class="text-[11px] text-slate-500 mt-0.5">Semua pesan masuk</p>
          </div>

          <div
            @click="filterStatus('approved')"
            class="cursor-pointer rounded-2xl border p-4 shadow-sm transition hover:shadow-md"
            :class="statusFilter === 'approved' ? 'border-emerald-600 bg-emerald-50/50' : 'border-slate-200 bg-white'"
          >
            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Disetujui</p>
            <p class="font-serif text-2xl font-bold text-emerald-700 mt-1">{{ stats.approved || 0 }}</p>
            <p class="text-[11px] text-emerald-600 mt-0.5">Tampil di undangan</p>
          </div>

          <div
            @click="filterStatus('pending')"
            class="cursor-pointer rounded-2xl border p-4 shadow-sm transition hover:shadow-md"
            :class="statusFilter === 'pending' ? 'border-amber-500 bg-amber-50/50' : 'border-slate-200 bg-white'"
          >
            <p class="text-xs font-semibold uppercase tracking-wider text-amber-600">Menunggu</p>
            <p class="font-serif text-2xl font-bold text-amber-700 mt-1">{{ stats.pending || 0 }}</p>
            <p class="text-[11px] text-amber-600 mt-0.5">Perlu ditinjau</p>
          </div>

          <div
            @click="filterStatus('rejected')"
            class="cursor-pointer rounded-2xl border p-4 shadow-sm transition hover:shadow-md"
            :class="statusFilter === 'rejected' ? 'border-rose-500 bg-rose-50/50' : 'border-slate-200 bg-white'"
          >
            <p class="text-xs font-semibold uppercase tracking-wider text-rose-500">Ditolak</p>
            <p class="font-serif text-2xl font-bold text-rose-700 mt-1">{{ stats.rejected || 0 }}</p>
            <p class="text-[11px] text-rose-500 mt-0.5">Disembunyikan</p>
          </div>
        </div>

        <!-- Search Bar -->
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
          <div class="relative max-w-md">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </span>
            <Input
              v-model="searchQuery"
              placeholder="Cari berdasarkan nama pengirim atau isi doa..."
              class="pl-9 text-xs"
            />
          </div>
        </div>

        <!-- Wishes Cards Grid -->
        <div class="space-y-4">
          <div v-if="!filteredWishes.length" class="rounded-2xl border border-slate-200 bg-white p-12 text-center shadow-sm">
            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
              💌
            </div>
            <p class="text-sm font-medium text-slate-700">Belum ada ucapan yang sesuai.</p>
            <p class="mt-1 text-xs text-slate-400">Ucapan yang dikirimkan tamu dari halaman undangan akan tampil di sini.</p>
          </div>

          <div
            v-for="wish in filteredWishes"
            :key="wish.id"
            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-slate-300"
          >
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
              <div class="flex-1 space-y-2">
                <div class="flex items-center gap-2">
                  <span class="font-bold text-slate-900">{{ wish.name || wish.guest?.name || 'Tamu Undangan' }}</span>
                  <span
                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold"
                    :class="{
                      'bg-emerald-100 text-emerald-800': wish.moderation_status === 'approved',
                      'bg-amber-100 text-amber-800': wish.moderation_status === 'pending',
                      'bg-rose-100 text-rose-800': wish.moderation_status === 'rejected',
                    }"
                  >
                    {{
                      wish.moderation_status === 'approved'
                        ? '✓ Disetujui (Tampil)'
                        : wish.moderation_status === 'rejected'
                        ? '✕ Ditolak (Sembunyi)'
                        : '⏳ Menunggu Moderasi'
                    }}
                  </span>
                </div>

                <p class="text-sm text-slate-700 leading-relaxed break-words bg-slate-50 p-3 rounded-xl border border-slate-100">
                  "{{ wish.message }}"
                </p>

                <p class="text-xs text-slate-400">
                  Dikirim pada {{ formatDateTime(wish.created_at) }}
                </p>
              </div>

              <!-- Action Buttons -->
              <div class="flex sm:flex-col items-center gap-2 shrink-0">
                <Button
                  v-if="wish.moderation_status !== 'approved'"
                  size="sm"
                  class="rounded-xl bg-emerald-700 px-3 text-xs font-semibold text-white hover:bg-emerald-800"
                  @click="approve(wish)"
                >
                  ✓ Setujui
                </Button>

                <Button
                  v-if="wish.moderation_status !== 'rejected'"
                  size="sm"
                  variant="outline"
                  class="rounded-xl border-amber-300 px-3 text-xs font-semibold text-amber-800 hover:bg-amber-50"
                  @click="reject(wish)"
                >
                  ✕ Sembunyikan
                </Button>

                <Button
                  size="sm"
                  variant="ghost"
                  class="rounded-xl text-xs text-rose-600 hover:bg-rose-50"
                  @click="deleteWish(wish)"
                >
                  🗑 Hapus
                </Button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
