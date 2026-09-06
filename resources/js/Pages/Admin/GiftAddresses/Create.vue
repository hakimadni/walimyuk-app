<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'

const props = defineProps({
  wedding: { type: Object, required: true },
})

const form = useForm({
  recipient_name: '',
  phone_number: '',
  address: '',
  notes: '',
  is_active: true,
})

function submit() {
  form.post(`/weddings/${props.wedding.id}/gift-addresses`)
}
</script>

<template>
  <Head :title="`Tambah Alamat Kado - ${wedding.cover_subtitle || wedding.cover_title}`" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-2">
        <Link :href="`/weddings/${wedding.id}/gift-addresses`" class="text-xs text-emerald-700 hover:underline">&larr; Kembali ke Daftar Alamat</Link>
      </div>
      <h2 class="font-serif text-3xl font-bold text-emerald-950 mt-1">Tambah Alamat Kirim Kado Fisik</h2>
      <p class="text-sm text-slate-500">{{ wedding.cover_title }} • Alamat tempat penerimaan bingkisan / kado dari para undangan.</p>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
        <form @submit.prevent="submit" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 space-y-6">
          <!-- Recipient Name -->
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-700">Nama Penerima Paket</label>
            <Input v-model="form.recipient_name" placeholder="Contoh: Farhan / Aisyah" required class="text-sm" />
            <p v-if="form.errors.recipient_name" class="text-xs text-rose-500">{{ form.errors.recipient_name }}</p>
          </div>

          <!-- Phone Number -->
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-700">Nomor HP / WhatsApp Aktif Penerima</label>
            <Input v-model="form.phone_number" placeholder="Contoh: 081234567890" required class="text-sm font-mono" />
            <p v-if="form.errors.phone_number" class="text-xs text-rose-500">{{ form.errors.phone_number }}</p>
          </div>

          <!-- Address -->
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-700">Alamat Lengkap Pengiriman</label>
            <textarea
              v-model="form.address"
              rows="4"
              placeholder="Jl. Melati No. 45, RT 02 / RW 05, Kel. Pondok Indah, Kec. Kebayoran Lama, Kota Jakarta Selatan, DKI Jakarta 12310"
              required
              class="w-full rounded-2xl border border-slate-200 p-3 text-xs focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 leading-relaxed"
            ></textarea>
            <p v-if="form.errors.address" class="text-xs text-rose-500">{{ form.errors.address }}</p>
          </div>

          <!-- Notes -->
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-700">Catatan / Patokan Lokasi (Opsional)</label>
            <Input v-model="form.notes" placeholder="Contoh: Pagar hitam, samping Masjid Nurul Iman" class="text-sm" />
            <p v-if="form.errors.notes" class="text-xs text-rose-500">{{ form.errors.notes }}</p>
          </div>

          <!-- Is Active Toggle -->
          <div class="flex items-center gap-2 pt-2">
            <input type="checkbox" id="is_active" v-model="form.is_active" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
            <label for="is_active" class="text-xs font-medium text-slate-700">Tampilkan alamat ini di halaman undangan online</label>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <Link :href="`/weddings/${wedding.id}/gift-addresses`">
              <Button type="button" variant="outline" class="rounded-xl text-xs">Batal</Button>
            </Link>
            <Button
              type="submit"
              :disabled="form.processing"
              class="rounded-xl bg-emerald-700 px-6 text-xs font-semibold text-white hover:bg-emerald-800"
            >
              {{ form.processing ? 'Menyimpan...' : 'Simpan Alamat' }}
            </Button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
