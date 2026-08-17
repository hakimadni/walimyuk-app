<script setup>
import { computed } from 'vue'

const props = defineProps({
  wedding: { type: Object, required: true },
  coupleProfiles: { type: Array, default: () => [] },
})

const groom = computed(() => props.coupleProfiles.find(p => p.role === 'groom') || props.coupleProfiles[0] || null)
const bride = computed(() => props.coupleProfiles.find(p => p.role === 'bride') || props.coupleProfiles[1] || null)
</script>

<template>
  <div class="flex h-full min-h-0 flex-col items-center justify-center px-6 py-8 text-center">
    <!-- Ornament top -->
    <div class="mb-6 text-4xl">💍</div>

    <p class="mb-4 text-sm text-slate-600 leading-relaxed max-w-xs mx-auto">
      {{ wedding.closing_text || 'Merupakan suatu kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir dan memberikan doa restu.' }}
    </p>

    <p class="mb-2 text-sm text-slate-600 leading-relaxed max-w-xs mx-auto">
      Atas kehadiran dan doa restunya, kami mengucapkan terima kasih.
    </p>

    <p class="mb-6 text-sm font-medium italic text-emerald-700">
      Wassalamu'alaikum Warahmatullahi Wabarakatuh
    </p>

    <!-- Divider -->
    <div class="mb-5 flex w-full items-center gap-3 px-4">
      <div class="flex-1 h-px bg-emerald-100" />
      <span class="text-emerald-300 text-lg">✦</span>
      <div class="flex-1 h-px bg-emerald-100" />
    </div>

    <p class="text-xs text-slate-400 mb-2">Kami yang berbahagia,</p>

    <div class="space-y-1">
      <h3 class="font-serif text-xl font-bold text-emerald-900">
        {{ groom?.full_name || '—' }}
      </h3>
      <p class="text-slate-400 text-sm">&</p>
      <h3 class="font-serif text-xl font-bold text-emerald-900">
        {{ bride?.full_name || '—' }}
      </h3>
    </div>

    <!-- Powered by subtle note -->
    <p class="mt-8 text-[10px] text-slate-300">
      Dibuat dengan ❤ menggunakan WalimYuk
    </p>
  </div>
</template>
