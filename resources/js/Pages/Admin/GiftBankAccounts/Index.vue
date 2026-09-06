<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'

const props = defineProps({
  wedding: { type: Object, required: true },
  accounts: { type: Array, default: () => [] },
})

function deleteAccount(acc) {
  if (confirm(`Hapus rekening "${acc.bank_name} - ${acc.account_number}"?`)) {
    router.delete(`/weddings/${props.wedding.id}/gift-bank-accounts/${acc.id}`, {
      preserveScroll: true,
    })
  }
}
</script>

<template>
  <Head :title="`Amplop Digital & Rekening Bank - ${wedding.cover_subtitle || wedding.cover_title}`" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <div class="flex items-center gap-2">
            <Link :href="`/weddings/${wedding.id}`" class="text-xs text-emerald-700 hover:underline">&larr; Kembali ke Detail</Link>
          </div>
          <h2 class="font-serif text-3xl font-bold text-emerald-950">Amplop Digital &amp; Rekening Bank</h2>
          <p class="mt-1 text-sm text-slate-500">{{ wedding.cover_title }} • Rekening transfer untuk kado cashless / amplop digital para tamu.</p>
        </div>
        <div class="flex items-center gap-2">
          <Link :href="`/weddings/${wedding.id}/gift-addresses`">
            <Button variant="outline" class="rounded-xl border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50">
              📦 Alamat Kirim Kado Fisik
            </Button>
          </Link>
          <Link :href="`/weddings/${wedding.id}/gift-bank-accounts/create`">
            <Button class="rounded-xl bg-emerald-700 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-800 flex items-center gap-1.5">
              <span>➕</span> Tambah Rekening
            </Button>
          </Link>
        </div>
      </div>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
        <div v-if="!accounts.length" class="rounded-3xl border border-slate-200 bg-white p-12 text-center shadow-sm">
          <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
            💳
          </div>
          <p class="text-sm font-medium text-slate-700">Belum ada rekening bank yang ditambahkan.</p>
          <p class="mt-1 text-xs text-slate-400">Tambahkan rekening BSI, BCA Syariah, Bank Mandiri, atau QRIS untuk kemudahan tamu memberikan tanda kasih.</p>
          <div class="mt-6">
            <Link :href="`/weddings/${wedding.id}/gift-bank-accounts/create`">
              <Button class="rounded-xl bg-emerald-700 text-xs font-semibold text-white hover:bg-emerald-800">
                Tambah Rekening Pertama
              </Button>
            </Link>
          </div>
        </div>

        <div v-else class="grid gap-6 md:grid-cols-3">
          <div
            v-for="acc in accounts"
            :key="acc.id"
            class="flex flex-col justify-between rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:shadow-md"
          >
            <div class="space-y-4">
              <div class="flex items-start justify-between">
                <div class="flex items-center gap-2">
                  <span class="text-xl">💳</span>
                  <h3 class="font-bold text-slate-900 text-base">{{ acc.bank_name }}</h3>
                </div>
                <span
                  class="rounded-full px-2.5 py-0.5 text-[10px] font-bold"
                  :class="acc.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500'"
                >
                  {{ acc.is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
              </div>

              <div class="rounded-2xl bg-slate-50 p-4 font-mono text-sm border border-slate-100 space-y-1">
                <p class="text-xs text-slate-400 font-sans">Nomor Rekening:</p>
                <p class="font-bold text-emerald-900 tracking-wider text-base">{{ acc.account_number }}</p>
                <p class="text-xs text-slate-600 font-sans mt-2">a.n. <strong>{{ acc.account_holder }}</strong></p>
              </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-2 border-t border-slate-100 pt-3">
              <Link :href="`/weddings/${wedding.id}/gift-bank-accounts/${acc.id}/edit`">
                <Button size="sm" variant="ghost" class="h-8 px-2.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900">
                  ✏️ Edit
                </Button>
              </Link>
              <Button
                size="sm"
                variant="ghost"
                class="h-8 px-2.5 rounded-lg text-xs font-semibold text-rose-600 hover:bg-rose-50 hover:text-rose-700"
                @click="deleteAccount(acc)"
              >
                🗑 Hapus
              </Button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
