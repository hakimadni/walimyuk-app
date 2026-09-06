<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'

const props = defineProps({
  wedding: { type: Object, required: true },
  event: { type: Object, required: true },
})

const form = useForm({
  title: props.event.title || '',
  date: props.event.date ? props.event.date.substring(0, 10) : '',
  start_time: props.event.start_time || '',
  end_time: props.event.end_time || '',
  venue_name: props.event.venue_name || '',
  address: props.event.address || '',
  google_maps_url: props.event.google_maps_url || '',
  sort_order: props.event.sort_order || 1,
})

function submit() {
  form.put(`/weddings/${props.wedding.id}/events/${props.event.id}`)
}
</script>

<template>
  <Head :title="`Edit Acara ${event.title} - ${wedding.cover_subtitle || wedding.cover_title}`" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-2">
        <Link :href="`/weddings/${wedding.id}/events`" class="text-xs text-emerald-700 hover:underline">&larr; Kembali ke Daftar Acara</Link>
      </div>
      <h2 class="font-serif text-3xl font-bold text-emerald-950 mt-1">Edit Rangkaian Acara</h2>
      <p class="text-sm text-slate-500">{{ wedding.cover_title }} • Perbarui jadwal dan lokasi acara.</p>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
        <form @submit.prevent="submit" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 space-y-6">
          <!-- Title & Presets -->
          <div class="space-y-2">
            <label class="block text-xs font-bold text-slate-700">Nama / Judul Sesi Acara</label>
            <Input v-model="form.title" placeholder="Contoh: Akad Nikah & Ijab Qabul" required class="text-sm" />
            <p v-if="form.errors.title" class="text-xs text-rose-500">{{ form.errors.title }}</p>
          </div>

          <!-- Date & Time -->
          <div class="grid gap-4 sm:grid-cols-3">
            <div class="space-y-1.5 sm:col-span-1">
              <label class="block text-xs font-bold text-slate-700">Tanggal Acara</label>
              <Input type="date" v-model="form.date" required class="text-sm" />
              <p v-if="form.errors.date" class="text-xs text-rose-500">{{ form.errors.date }}</p>
            </div>

            <div class="space-y-1.5 sm:col-span-1">
              <label class="block text-xs font-bold text-slate-700">Jam Mulai (WIB)</label>
              <Input v-model="form.start_time" placeholder="08:00" class="text-sm" />
              <p v-if="form.errors.start_time" class="text-xs text-rose-500">{{ form.errors.start_time }}</p>
            </div>

            <div class="space-y-1.5 sm:col-span-1">
              <label class="block text-xs font-bold text-slate-700">Jam Selesai (WIB)</label>
              <Input v-model="form.end_time" placeholder="Selesai / 11:00" class="text-sm" />
              <p v-if="form.errors.end_time" class="text-xs text-rose-500">{{ form.errors.end_time }}</p>
            </div>
          </div>

          <!-- Venue & Address -->
          <div class="space-y-4 pt-2 border-t border-slate-100">
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-slate-700">Nama Gedung / Masjid / Tempat</label>
              <Input v-model="form.venue_name" placeholder="Contoh: Masjid Agung Al-Azhar / Ballroom Hotel Royal" class="text-sm" />
              <p v-if="form.errors.venue_name" class="text-xs text-rose-500">{{ form.errors.venue_name }}</p>
            </div>

            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-slate-700">Alamat Lengkap</label>
              <textarea
                v-model="form.address"
                rows="3"
                placeholder="Jl. Sisingamangaraja No. 1, Kebayoran Baru, Jakarta Selatan"
                class="w-full rounded-xl border border-slate-200 p-3 text-xs focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
              ></textarea>
              <p v-if="form.errors.address" class="text-xs text-rose-500">{{ form.errors.address }}</p>
            </div>

            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-slate-700">Tautan Google Maps</label>
              <Input v-model="form.google_maps_url" placeholder="https://maps.app.goo.gl/..." class="text-sm" />
              <p v-if="form.errors.google_maps_url" class="text-xs text-rose-500">{{ form.errors.google_maps_url }}</p>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <Link :href="`/weddings/${wedding.id}/events`">
              <Button type="button" variant="outline" class="rounded-xl text-xs">Batal</Button>
            </Link>
            <Button
              type="submit"
              :disabled="form.processing"
              class="rounded-xl bg-emerald-700 px-6 text-xs font-semibold text-white hover:bg-emerald-800"
            >
              {{ form.processing ? 'Menyimpan...' : 'Perbarui Jadwal Acara' }}
            </Button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
