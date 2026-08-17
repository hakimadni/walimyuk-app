<script setup>
import { ref } from 'vue'

const props = defineProps({
  bankAccounts: { type: Array, default: () => [] },
  addresses: { type: Array, default: () => [] },
})

const copiedId = ref(null)

function copyAccountNumber(account) {
  navigator.clipboard.writeText(account.account_number || '').then(() => {
    copiedId.value = account.id
    setTimeout(() => { copiedId.value = null }, 2000)
  })
}
</script>

<template>
  <div class="flex h-full min-h-0 flex-col px-5 py-8">
    <h2 class="mb-1 text-center font-serif text-2xl font-bold text-emerald-900">Wedding Gift</h2>
    <p class="mb-5 text-center text-xs text-slate-500 leading-relaxed px-2">
      Doa restu Anda adalah hadiah terbaik bagi kami. Namun apabila ingin memberikan tanda kasih:
    </p>

    <div class="flex-1 overflow-y-auto pb-2 space-y-4">
      <!-- Bank Accounts -->
      <template v-if="bankAccounts && bankAccounts.length">
        <div
          v-for="account in bankAccounts"
          :key="account.id"
          class="px-5 py-5 border-b border-slate-200/50 last:border-0"
        >
          <div class="mb-3 flex flex-col items-center justify-center gap-2 text-center">
            <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-xl bg-emerald-100">
              <svg class="h-4 w-4 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
              </svg>
            </div>
            <div>
              <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">{{ account.bank_name }}</p>
              <p v-if="account.account_name" class="text-xs text-slate-500">a.n. {{ account.account_name }}</p>
            </div>
          </div>

          <p class="mb-3 font-mono text-xl font-bold tracking-widest text-slate-800 text-center">
            {{ account.account_number }}
          </p>

          <button
            class="w-full rounded-xl border-2 py-2.5 text-sm font-semibold transition active:scale-95"
            :class="copiedId === account.id
              ? 'border-emerald-600 bg-emerald-50 text-emerald-700'
              : 'border-slate-200 text-slate-600 hover:border-emerald-300'"
            @click="copyAccountNumber(account)"
          >
            {{ copiedId === account.id ? '✓ Tersalin!' : 'Salin Nomor Rekening' }}
          </button>
        </div>
      </template>

      <!-- Gift Addresses -->
      <template v-if="addresses && addresses.length">
        <div class="text-center mb-1">
          <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Kirim Hadiah</p>
        </div>
        <div
          v-for="addr in addresses"
          :key="addr.id"
          class="px-5 py-5 border-b border-slate-200/50 last:border-0"
        >
          <div class="flex flex-col items-center justify-center gap-3 text-center">
            <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-xl bg-emerald-100">
              <svg class="h-4 w-4 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
              </svg>
            </div>
            <div>
              <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">{{ addr.title }}</p>
              <p class="text-xs text-slate-500">{{ addr.recipient_name }}</p>
              <p v-if="addr.phone" class="text-xs text-slate-500">{{ addr.phone }}</p>
            </div>
          </div>
        </div>
      </template>

      <!-- Empty state -->
      <div v-if="!bankAccounts?.length && !addresses?.length" class="flex h-full items-center justify-center py-10 text-center">
        <div>
          <p class="text-3xl mb-2">🎁</p>
          <p class="text-sm text-slate-400 italic">Info gift belum diatur</p>
        </div>
      </div>
    </div>
  </div>
</template>
