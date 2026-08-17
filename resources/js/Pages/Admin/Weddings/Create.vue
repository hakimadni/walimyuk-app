<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { DateTimePicker } from '@/components/ui/datetime-picker'
import InputError from '@/Components/InputError.vue'

const form = useForm({
  cover_title: '',
  cover_subtitle: '',
  wedding_date: '',
  timezone: 'Asia/Jakarta',
  welcome_text: '',
  closing_text: '',
  rsvp_required: true,
  comments_need_approval: false,
  pax_buffer_percentage: 10,
})

function submit() {
  form.post('/dashboard/weddings')
}
</script>

<template>
  <Head title="Buat Undangan Baru" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between gap-4">
        <div>
          <h2 class="font-serif text-3xl font-bold text-emerald-900">Buat Undangan Baru</h2>
          <p class="mt-1 text-sm text-slate-500">Buat shell undangan dulu. Detail mempelai, acara, gift, dan ayat diisi di halaman lanjutan.</p>
        </div>
        <Link href="/dashboard/weddings" class="text-sm text-emerald-700 hover:text-emerald-800">
          Kembali ke daftar
        </Link>
      </div>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
        <div class="rounded-xl border border-slate-200 bg-white p-8 shadow-sm">
          <form @submit.prevent="submit" class="space-y-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
              <div class="space-y-2 sm:col-span-2">
                <Label for="cover_title">Judul Cover</Label>
                <Input id="cover_title" v-model="form.cover_title" placeholder="The Wedding of Fulan & Fulanah" />
                <InputError :message="form.errors.cover_title" />
              </div>

              <div class="space-y-2 sm:col-span-2">
                <Label for="cover_subtitle">Subjudul Cover</Label>
                <Input id="cover_subtitle" v-model="form.cover_subtitle" placeholder="Ahmad Fulan & Fatimah Fulanah" />
                <InputError :message="form.errors.cover_subtitle" />
              </div>

              <div class="space-y-2">
                <Label for="wedding_date">Tanggal Pernikahan</Label>
                <DateTimePicker id="wedding_date" v-model="form.wedding_date" />
                <InputError :message="form.errors.wedding_date" />
              </div>

              <div class="space-y-2">
                <Label for="timezone">Timezone</Label>
                <Input id="timezone" v-model="form.timezone" placeholder="Asia/Jakarta" />
                <InputError :message="form.errors.timezone" />
              </div>

              <div class="space-y-2 sm:col-span-2">
                <Label for="welcome_text">Teks Pembuka</Label>
                <textarea id="welcome_text" v-model="form.welcome_text" rows="3" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"></textarea>
                <InputError :message="form.errors.welcome_text" />
              </div>

              <div class="space-y-2 sm:col-span-2">
                <Label for="closing_text">Teks Penutup</Label>
                <textarea id="closing_text" v-model="form.closing_text" rows="3" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"></textarea>
                <InputError :message="form.errors.closing_text" />
              </div>

              <div class="space-y-2">
                <Label for="pax_buffer_percentage">Buffer Katering (%)</Label>
                <Input id="pax_buffer_percentage" type="number" min="0" max="100" v-model="form.pax_buffer_percentage" />
                <InputError :message="form.errors.pax_buffer_percentage" />
              </div>

              <div class="space-y-3 rounded-xl border border-slate-200 p-4">
                <label class="flex items-center gap-3 text-sm text-slate-700">
                  <input v-model="form.rsvp_required" type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                  RSVP wajib
                </label>
                <label class="flex items-center gap-3 text-sm text-slate-700">
                  <input v-model="form.comments_need_approval" type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                  Ucapan perlu moderasi
                </label>
              </div>
            </div>

            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
              Setelah undangan dibuat, lanjut isi data di menu detail: profil mempelai, acara, guest management, RSVP, gift, dan ucapan.
            </div>

            <div class="flex justify-end pt-2">
              <Button type="submit" :disabled="form.processing" class="bg-emerald-700 text-white hover:bg-emerald-800">
                Simpan Undangan
              </Button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
