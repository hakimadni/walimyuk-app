<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'

const props = defineProps({
  wedding: { type: Object, required: true },
  addresses: { type: Array, default: () => [] },
})

function deleteAddress(addr) {
  if (confirm(`Hapus alamat kirim kado "${addr.recipient_name}"?`)) {
    router.delete(`/dashboard/weddings/${props.wedding.id}/gift-addresses/${addr.id}`, {
      preserveScroll: true,
    })
  }
}
</script>

<template>
  <Head :title="`Alamat Kirim Kado Fisik - ${wedding.cover_subtitle || wedding.cover_title}`" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <div class="flex items-center gap-2">
            <Link :href="`/dashboard/weddings/${wedding.id}`" class="text-xs text-emerald-700 hover:underline">&larr; Kembali ke Detail</Link>
          </div>
          <h2 class="font-serif text-3xl font-bold text-emerald-950">Alamat Kirim Kado Fisik</h2>
          <p class="mt-1 text-sm text-slate-500">{{ wedding.cover_title }} • Alamat tujuan pengiriman paket / kado dari tamu yang berhalangan hadir.</p>
        </div>
        <div class="flex items-center gap-2">
          <Link :href="`/dashboard/weddings/${wedding.id}/gift-bank-accounts`">
            <Button variant="outline" class="rounded-xl border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50">
              💳 Rekening Amplop Digital
            </Button>
          </Link>
          <Link :href="`/dashboard/weddings/${wedding.id}/gift-addresses/create`">
            <Button class="rounded-xl bg-emerald-700 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-800 flex items-center gap-1.5">
              <span>➕</span> Tambah Alamat
            </Button>
          </Link>
        </div>
      </div>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
        <div v-if="!addresses.length" class="rounded-3xl border border-slate-200 bg-white p-12 text-center shadow-sm">
          <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
            📦
          </div>
          <p class="text-sm font-medium text-slate-700">Belum ada alamat kirim kado yang ditambahkan.</p>
          <p class="mt-1 text-xs text-slate-400">Tambahkan alamat rumah mempelai untuk memudahkan pengiriman hadiah via kurir/ekspedisi.</p>
          <div class="mt-6">
            <Link :href="`/dashboard/weddings/${wedding.id}/gift-addresses/create`">
              <Button class="rounded-xl bg-emerald-700 text-xs font-semibold text-white hover:bg-emerald-800">
                Tambah Alamat Pertama
              </Button>
            </Link>
          </div>
        </div>

        <div v-else class="grid gap-6 md:grid-cols-2">
          <div
            v-for="addr in addresses"
            :key="addr.id"
            class="flex flex-col justify-between rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:shadow-md"
          >
            <div class="space-y-4">
              <div class="flex items-start justify-between">
                <div>
                  <span
                    class="rounded-full px-2.5 py-0.5 text-[10px] font-bold"
                    :class="addr.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500'"
                  >
                    {{ addr.is_active ? 'Aktif' : 'Nonaktif' }}
                  </span>
                  <h3 class="mt-2 font-serif text-xl font-bold text-slate-900">{{ addr.recipient_name }}</h3>
                  <p class="text-xs text-slate-500 font-mono">{{ addr.phone_number }}</p>
                </div>
              </div>

              <div class="rounded-2xl bg-slate-50 p-4 text-xs text-slate-700 border border-slate-100 space-y-2">
                <p class="leading-relaxed whitespace-pre-line">{{ addr.address }}</p>
                <p v-if="addr.notes" class="text-[11px] text-amber-700 font-medium">
                  Catatan: {{ addr.notes }}
                </p>
              </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-2 border-t border-slate-100 pt-3">
              <Link :href="`/dashboard/weddings/${wedding.id}/gift-addresses/${addr.id}/edit`">
                <Button size="sm" variant="ghost" class="h-8 px-2.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900">
                  ✏️ Edit
                </Button>
              </Link>
              <Button
                size="sm"
                variant="ghost"
                class="h-8 px-2.5 rounded-lg text-xs font-semibold text-rose-600 hover:bg-rose-50 hover:text-rose-700"
                @click="deleteAddress(addr)"
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
