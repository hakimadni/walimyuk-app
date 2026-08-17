<script setup>
defineProps({
  wishes: { type: Array, default: () => [] }
})
</script>

<template>
  <div class="flex h-full min-h-0 flex-col px-5 py-8">
    <h2 class="mb-1 text-center font-serif text-2xl font-bold text-emerald-900">Ucapan & Doa</h2>
    <p class="mb-5 text-center text-xs text-slate-500">Terima kasih atas doa terbaik untuk kami.</p>

    <div v-if="wishes.length" class="flex-1 space-y-3 overflow-y-auto pb-2">
      <div
        v-for="(wish, i) in wishes"
        :key="i"
        class="flex flex-col items-center gap-2 px-4 py-4 border-b border-emerald-50/50 last:border-0 text-center"
      >
        <!-- Avatar -->
        <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100 text-sm font-bold text-emerald-800">
          {{ (wish.name || wish.guest?.name || 'T')[0].toUpperCase() }}
        </div>
        <div class="w-full min-w-0">
          <p class="text-sm font-semibold text-emerald-900 truncate">
            {{ wish.name || wish.guest?.name || 'Tamu Undangan' }}
          </p>
          <p class="mt-1 text-[13px] text-slate-600 leading-relaxed">{{ wish.message }}</p>
        </div>
      </div>
    </div>

    <div v-else class="flex flex-1 items-center justify-center py-10 text-center">
      <div>
        <p class="text-4xl mb-3">💌</p>
        <p class="text-sm text-slate-400 italic">Belum ada ucapan</p>
      </div>
    </div>
  </div>
</template>
