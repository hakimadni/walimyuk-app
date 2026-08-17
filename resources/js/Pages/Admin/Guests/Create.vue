<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

const props = defineProps({
  wedding: { type: Object, required: true },
})

const form = useForm({
  name: '',
  phone_number: '',
  group_name: '',
  max_pax: 2,
  notes: '',
})

function submit() {
  form.post(`/dashboard/weddings/${props.wedding.id}/guests`)
}
</script>

<template>
  <Head :title="`Tambah Tamu - ${wedding.cover_subtitle || wedding.cover_title}`" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <div class="flex items-center gap-2">
            <Link :href="`/dashboard/weddings/${wedding.id}/guests`" class="text-xs text-emerald-700 hover:underline">&larr; Kembali ke Daftar Tamu</Link>
          </div>
          <h2 class="font-serif text-3xl font-bold text-emerald-950">Tambah Tamu Undangan</h2>
          <p class="mt-1 text-sm text-slate-500">{{ wedding.cover_title }}</p>
        </div>
      </div>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
        <Card class="border-slate-200 shadow-sm">
          <CardHeader>
            <CardTitle class="font-serif text-xl font-bold text-emerald-950">Informasi Tamu</CardTitle>
            <CardDescription>
              Tamu yang didaftarkan akan otomatis mendapatkan token personal unik untuk akses RSVP &amp; ucapan.
            </CardDescription>
          </CardHeader>
          <CardContent>
            <form @submit.prevent="submit" class="space-y-4">
              <!-- Nama Tamu -->
              <div class="space-y-1.5">
                <Label for="name" class="font-semibold text-slate-700">Nama Tamu <span class="text-rose-500">*</span></Label>
                <Input
                  id="name"
                  v-model="form.name"
                  type="text"
                  placeholder="Contoh: Bpk. H. Ahmad Dahlan &amp; Keluarga"
                  required
                />
                <p v-if="form.errors.name" class="text-xs text-rose-500">{{ form.errors.name }}</p>
              </div>

              <!-- No. Telepon / WhatsApp -->
              <div class="space-y-1.5">
                <Label for="phone_number" class="font-semibold text-slate-700">Nomor WhatsApp / Telepon</Label>
                <Input
                  id="phone_number"
                  v-model="form.phone_number"
                  type="text"
                  placeholder="Contoh: 081234567890 atau 6281234567890"
                />
                <p class="text-[11px] text-slate-400">Digunakan untuk kemudahan kirim undangan langsung via WhatsApp.</p>
                <p v-if="form.errors.phone_number" class="text-xs text-rose-500">{{ form.errors.phone_number }}</p>
              </div>

              <!-- Grup / Kategori -->
              <div class="space-y-1.5">
                <Label for="group_name" class="font-semibold text-slate-700">Kategori / Grup Tamu</Label>
                <Input
                  id="group_name"
                  v-model="form.group_name"
                  type="text"
                  placeholder="Contoh: Keluarga Pria, Teman SMA, VIP Kantor, Tetangga"
                />
                <p v-if="form.errors.group_name" class="text-xs text-rose-500">{{ form.errors.group_name }}</p>
              </div>

              <!-- Max Pax (Jumlah Kuota Undangan) -->
              <div class="space-y-1.5">
                <Label for="max_pax" class="font-semibold text-slate-700">Jumlah Kuota Pax Undangan <span class="text-rose-500">*</span></Label>
                <Input
                  id="max_pax"
                  v-model="form.max_pax"
                  type="number"
                  min="1"
                  max="10"
                  required
                />
                <p class="text-[11px] text-slate-400">Batas maksimal orang yang dapat dikonfirmasi oleh tamu saat mengisi formulir RSVP.</p>
                <p v-if="form.errors.max_pax" class="text-xs text-rose-500">{{ form.errors.max_pax }}</p>
              </div>

              <!-- Notes / Catatan -->
              <div class="space-y-1.5">
                <Label for="notes" class="font-semibold text-slate-700">Catatan Khusus (Internal)</Label>
                <textarea
                  id="notes"
                  v-model="form.notes"
                  rows="3"
                  class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                  placeholder="Contoh: Meja VIP depan panggung, titip souvenir khusus..."
                ></textarea>
                <p v-if="form.errors.notes" class="text-xs text-rose-500">{{ form.errors.notes }}</p>
              </div>

              <!-- Action Buttons -->
              <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <Link :href="`/dashboard/weddings/${wedding.id}/guests`">
                  <Button type="button" variant="outline" class="rounded-xl">Batal</Button>
                </Link>
                <Button
                  type="submit"
                  :disabled="form.processing"
                  class="rounded-xl bg-emerald-700 px-5 text-white hover:bg-emerald-800"
                >
                  {{ form.processing ? 'Menyimpan...' : 'Simpan Tamu' }}
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
