<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'

const props = defineProps({
  wedding: { type: Object, required: true },
  account: { type: Object, required: true },
})

const bankPresets = [
  'Bank Syariah Indonesia (BSI)',
  'BCA',
  'Bank Mandiri',
  'BRI',
  'BNI',
  'BCA Syariah',
  'Bank Muamalat',
  'Bank Jago',
  'GoPay / OVO / Dana',
]

const form = useForm({
  bank_name: props.account.bank_name || '',
  account_number: props.account.account_number || '',
  account_holder: props.account.account_holder || '',
  sort_order: props.account.sort_order || 1,
  is_active: Boolean(props.account.is_active),
})

function submit() {
  form.put(`/dashboard/weddings/${props.wedding.id}/gift-bank-accounts/${props.account.id}`)
}
</script>

<template>
  <Head :title="`Edit Rekening Bank - ${wedding.cover_subtitle || wedding.cover_title}`" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-2">
        <Link :href="`/dashboard/weddings/${wedding.id}/gift-bank-accounts`" class="text-xs text-emerald-700 hover:underline">&larr; Kembali ke Daftar Rekening</Link>
      </div>
      <h2 class="font-serif text-3xl font-bold text-emerald-950 mt-1">Edit Rekening Bank</h2>
      <p class="text-sm text-slate-500">{{ wedding.cover_title }} • Perbarui nomor rekening atau nama pemilik.</p>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
        <form @submit.prevent="submit" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 space-y-6">
          <!-- Bank Presets -->
          <div class="space-y-2">
            <label class="block text-xs font-bold text-slate-700">Pilihan Nama Bank / Dompet Digital</label>
            <div class="flex flex-wrap gap-1.5 mb-2">
              <button
                v-for="b in bankPresets"
                :key="b"
                type="button"
                class="rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-800 hover:bg-emerald-100"
                @click="form.bank_name = b"
              >
                {{ b }}
              </button>
            </div>
            <Input v-model="form.bank_name" placeholder="Contoh: Bank Syariah Indonesia (BSI)" required class="text-sm" />
            <p v-if="form.errors.bank_name" class="text-xs text-rose-500">{{ form.errors.bank_name }}</p>
          </div>

          <!-- Account Number -->
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-700">Nomor Rekening / No. HP E-Wallet</label>
            <Input v-model="form.account_number" placeholder="Contoh: 7123456789" required class="text-sm font-mono" />
            <p v-if="form.errors.account_number" class="text-xs text-rose-500">{{ form.errors.account_number }}</p>
          </div>

          <!-- Account Holder -->
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-700">Nama Pemilik Rekening (Atas Nama)</label>
            <Input v-model="form.account_holder" placeholder="Contoh: Farhan Ramadhan" required class="text-sm" />
            <p v-if="form.errors.account_holder" class="text-xs text-rose-500">{{ form.errors.account_holder }}</p>
          </div>

          <!-- Is Active Toggle -->
          <div class="flex items-center gap-2 pt-2">
            <input type="checkbox" id="is_active" v-model="form.is_active" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
            <label for="is_active" class="text-xs font-medium text-slate-700">Tampilkan rekening ini di undangan online</label>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <Link :href="`/dashboard/weddings/${wedding.id}/gift-bank-accounts`">
              <Button type="button" variant="outline" class="rounded-xl text-xs">Batal</Button>
            </Link>
            <Button
              type="submit"
              :disabled="form.processing"
              class="rounded-xl bg-emerald-700 px-6 text-xs font-semibold text-white hover:bg-emerald-800"
            >
              {{ form.processing ? 'Menyimpan...' : 'Perbarui Rekening' }}
            </Button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
