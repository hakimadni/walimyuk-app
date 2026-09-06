<script setup>
import { ref, computed } from 'vue'
import { resolveBuilder } from '@/lib/invitationTheme'

const props = defineProps({
  bankAccounts: { type: Array, default: () => [] },
  addresses: { type: Array, default: () => [] },
  themeConfig: { type: Object, default: () => ({}) },
})

const builder = computed(() => resolveBuilder(props.themeConfig))
const palette = computed(() => builder.value.content.palette)

const copiedId = ref(null)

function copyAccountNumber(account) {
  navigator.clipboard.writeText(account.account_number || '').then(() => {
    copiedId.value = account.id
    setTimeout(() => { copiedId.value = null }, 2000)
  })
}

function copyAddress(addr) {
  navigator.clipboard.writeText(addr.address || '').then(() => {
    copiedId.value = 'addr_' + addr.id
    setTimeout(() => { copiedId.value = null }, 2000)
  })
}
</script>

<template>
  <div class="flex h-full min-h-0 flex-col px-5 py-8">
    <h2 class="mb-1 text-center font-serif text-2xl font-bold aos-item aos-fade-down" :style="{ color: palette.primary }">
      Wedding Gift
    </h2>
    <p class="mb-5 text-center text-xs leading-relaxed px-2 aos-item aos-fade-down aos-delay-100" :style="{ color: palette.secondary }">
      Doa restu Anda adalah hadiah terbaik bagi kami. Namun apabila ingin memberikan tanda kasih:
    </p>

    <div class="flex-1 overflow-y-auto pb-2 space-y-4">
      <!-- Bank Accounts -->
      <template v-if="bankAccounts && bankAccounts.length">
        <div
          v-for="account in bankAccounts"
          :key="account.id"
          class="rounded-2xl border px-5 py-5 shadow-sm backdrop-blur-sm aos-item aos-fade-up aos-delay-150"
          :style="{ backgroundColor: `${palette.secondary}12`, borderColor: `${palette.secondary}44` }"
        >
          <div class="mb-3 flex flex-col items-center justify-center gap-2 text-center">
            <div
              class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl border shadow-xs aos-item aos-zoom-in aos-delay-200"
              :style="{ backgroundColor: `${palette.secondary}25`, borderColor: `${palette.secondary}60` }"
            >
              <svg class="h-4 w-4" :style="{ color: palette.secondary }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
              </svg>
            </div>
            <div>
              <p class="text-[10px] font-bold uppercase tracking-wider" :style="{ color: palette.secondary }">{{ account.bank_name }}</p>
              <p v-if="account.account_holder" class="text-xs mt-0.5 opacity-85" :style="{ color: palette.primary }">a.n. {{ account.account_holder }}</p>
            </div>
          </div>

          <p class="mb-3 font-mono text-xl font-bold tracking-widest text-center aos-item aos-fade-up aos-delay-250" :style="{ color: palette.primary }">
            {{ account.account_number }}
          </p>

          <button
            class="w-full rounded-xl border py-2.5 text-sm font-bold shadow-xs transition active:scale-95 aos-item aos-fade-up aos-delay-300 hover:brightness-105"
            :style="copiedId === account.id
              ? { backgroundColor: palette.primary, borderColor: palette.secondary, color: '#fff' }
              : { backgroundColor: `${palette.secondary}20`, borderColor: `${palette.secondary}66`, color: palette.primary }"
            @click="copyAccountNumber(account)"
          >
            {{ copiedId === account.id ? '✓ Tersalin!' : 'Salin Nomor Rekening' }}
          </button>
        </div>
      </template>

      <!-- Gift Addresses -->
      <template v-if="addresses && addresses.length">
        <div class="text-center mb-1 aos-item aos-fade-down aos-delay-300">
          <p class="text-xs font-bold uppercase tracking-wider" :style="{ color: palette.secondary }">Kirim Hadiah Fisik</p>
        </div>
        <div
          v-for="addr in addresses"
          :key="addr.id"
          class="rounded-2xl border px-5 py-5 shadow-sm backdrop-blur-sm aos-item aos-fade-up aos-delay-350"
          :style="{ backgroundColor: `${palette.secondary}12`, borderColor: `${palette.secondary}44` }"
        >
          <div class="flex flex-col items-center justify-center gap-3 text-center">
            <div
              class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl border shadow-xs"
              :style="{ backgroundColor: `${palette.secondary}25`, borderColor: `${palette.secondary}60` }"
            >
              <svg class="h-4 w-4" :style="{ color: palette.secondary }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
              </svg>
            </div>
            <div>
              <p class="text-[10px] font-bold uppercase tracking-wider" :style="{ color: palette.secondary }">
                Penerima: {{ addr.recipient_name }}
              </p>
              <p v-if="addr.phone_number" class="text-xs mb-2 opacity-85" :style="{ color: palette.primary }">{{ addr.phone_number }}</p>
              <p class="text-sm font-semibold leading-relaxed mb-3" :style="{ color: palette.primary }">{{ addr.address }}</p>
              <p v-if="addr.notes" class="mb-3 text-xs italic" :style="{ color: palette.secondary }">{{ addr.notes }}</p>
              
              <button
                class="w-full rounded-xl border py-2 text-xs font-bold shadow-xs transition active:scale-95"
                :style="copiedId === 'addr_'+addr.id
                  ? { backgroundColor: palette.primary, borderColor: palette.secondary, color: '#fff' }
                  : { backgroundColor: `${palette.secondary}20`, borderColor: `${palette.secondary}66`, color: palette.primary }"
                @click="copyAddress(addr)"
              >
                {{ copiedId === 'addr_'+addr.id ? '✓ Tersalin!' : 'Salin Alamat' }}
              </button>
            </div>
          </div>
        </div>
      </template>

      <!-- Empty state -->
      <div v-if="!bankAccounts?.length && !addresses?.length" class="flex h-full items-center justify-center py-10 text-center">
        <div>
          <p class="text-3xl mb-2">🎁</p>
          <p class="text-sm italic opacity-60" :style="{ color: palette.primary }">Info gift belum diatur</p>
        </div>
      </div>
    </div>
  </div>
</template>
