<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'

const props = defineProps({
  wedding: { type: Object, required: true },
  events: { type: Array, default: () => [] },
})

function deleteEvent(event) {
  if (confirm(`Apakah Anda yakin ingin menghapus jadwal acara "${event.title}"?`)) {
    router.delete(`/dashboard/weddings/${props.wedding.id}/events/${event.id}`, {
      preserveScroll: true,
    })
  }
}

function formatDate(str) {
  if (!str) return '-'
  const d = new Date(str)
  return d.toLocaleDateString('id-ID', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
}
</script>

<template>
  <Head :title="`Jadwal Acara - ${wedding.cover_subtitle || wedding.cover_title}`" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <div class="flex items-center gap-2">
            <Link :href="`/dashboard/weddings/${wedding.id}`" class="text-xs text-emerald-700 hover:underline">&larr; Kembali ke Detail</Link>
          </div>
          <h2 class="font-serif text-3xl font-bold text-emerald-950">Jadwal &amp; Rangkaian Acara</h2>
          <p class="mt-1 text-sm text-slate-500">{{ wedding.cover_title }} • Kelola waktu akad, resepsi, dan lokasi venue.</p>
        </div>
        <Link :href="`/dashboard/weddings/${wedding.id}/events/create`">
          <Button class="rounded-xl bg-emerald-700 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-800 flex items-center gap-1.5">
            <span>➕</span> Tambah Jadwal Acara
          </Button>
        </Link>
      </div>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
        <div v-if="!events.length" class="rounded-3xl border border-slate-200 bg-white p-12 text-center shadow-sm">
          <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
            📅
          </div>
          <p class="text-sm font-medium text-slate-700">Belum ada rangkaian acara yang ditambahkan.</p>
          <p class="mt-1 text-xs text-slate-400">Tambahkan Akad Nikah, Walimatul 'Ursy / Resepsi, atau agenda lainnya.</p>
          <div class="mt-6">
            <Link :href="`/dashboard/weddings/${wedding.id}/events/create`">
              <Button class="rounded-xl bg-emerald-700 text-xs font-semibold text-white hover:bg-emerald-800">
                Tambah Acara Pertama
              </Button>
            </Link>
          </div>
        </div>

        <div v-else class="grid gap-6 md:grid-cols-2">
          <div
            v-for="event in events"
            :key="event.id"
            class="flex flex-col justify-between rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:shadow-md"
          >
            <div class="space-y-4">
              <div class="flex items-start justify-between gap-2">
                <div>
                  <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                    {{ event.title }}
                  </span>
                  <h3 class="mt-3 font-serif text-xl font-bold text-slate-900">{{ formatDate(event.date) }}</h3>
                </div>
                <div class="flex items-center gap-1">
                  <Link :href="`/dashboard/weddings/${wedding.id}/events/${event.id}/edit`">
                    <Button size="sm" variant="ghost" class="h-8 w-8 p-0 rounded-lg text-slate-500 hover:text-slate-900" title="Edit">
                      ✏️
                    </Button>
                  </Link>
                  <Button
                    size="sm"
                    variant="ghost"
                    class="h-8 w-8 p-0 rounded-lg text-rose-500 hover:bg-rose-50 hover:text-rose-700"
                    @click="deleteEvent(event)"
                    title="Hapus"
                  >
                    🗑
                  </Button>
                </div>
              </div>

              <!-- Time & Venue -->
              <div class="space-y-2 rounded-2xl bg-slate-50 p-4 text-xs text-slate-600 border border-slate-100">
                <div class="flex items-center gap-2">
                  <span class="text-emerald-700 font-bold">⏰ Waktu:</span>
                  <span class="font-medium text-slate-800">
                    {{ event.start_time || '08:00' }} {{ event.end_time ? ` - ${event.end_time} WIB` : ' - Selesai' }}
                  </span>
                </div>
                <div class="flex items-start gap-2">
                  <span class="text-emerald-700 font-bold">📍 Lokasi:</span>
                  <div>
                    <p class="font-semibold text-slate-900">{{ event.venue_name || 'Lokasi Acara' }}</p>
                    <p class="mt-0.5 text-slate-500 leading-relaxed">{{ event.address || '-' }}</p>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="event.google_maps_url" class="mt-4 pt-3 border-t border-slate-100">
              <a
                :href="event.google_maps_url"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 hover:underline"
              >
                <span>🗺️</span> Buka di Google Maps &rarr;
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
