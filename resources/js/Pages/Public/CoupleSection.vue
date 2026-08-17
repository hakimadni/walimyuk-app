<script setup>
import { computed } from 'vue'

const props = defineProps({
  profiles: { type: Array, default: () => [] }
})

const groom = computed(() => props.profiles.find(p => p.role === 'groom') || props.profiles[0] || null)
const bride = computed(() => props.profiles.find(p => p.role === 'bride') || props.profiles[1] || null)
</script>

<template>
  <div class="flex h-full min-h-0 flex-col items-center justify-center px-5 py-8 text-center">
    <p class="mb-5 text-[11px] text-slate-500 leading-relaxed max-w-xs">
      Dengan memohon rahmat dan ridha Allah SWT, insyaAllah kami akan melangsungkan pernikahan:
    </p>

    <!-- Groom -->
    <div v-if="groom" class="mb-4 w-full px-5 py-5">
      <div class="mb-2 inline-flex items-center gap-1.5 rounded-full bg-emerald-700 px-3 py-0.5">
        <span class="text-[10px] font-bold uppercase tracking-wide text-white">Mempelai Pria</span>
      </div>
      <h3 class="font-serif text-2xl font-bold text-emerald-900 leading-tight">{{ groom.full_name }}</h3>
      <p v-if="groom.father_name || groom.mother_name" class="mt-2 text-xs text-slate-500 leading-relaxed">
        {{ groom.child_order_text || 'Putra dari' }}<br>
        Bapak <span class="font-semibold text-slate-700">{{ groom.father_name || '—' }}</span>
        &amp; Ibu <span class="font-semibold text-slate-700">{{ groom.mother_name || '—' }}</span>
      </p>
    </div>

    <!-- Divider -->
    <div class="mb-4 flex items-center gap-3 w-full px-4">
      <div class="flex-1 h-px bg-emerald-100" />
      <span class="font-serif text-2xl text-emerald-700">&</span>
      <div class="flex-1 h-px bg-emerald-100" />
    </div>

    <!-- Bride -->
    <div v-if="bride" class="w-full px-5 py-5">
      <div class="mb-2 inline-flex items-center gap-1.5 rounded-full bg-emerald-700 px-3 py-0.5">
        <span class="text-[10px] font-bold uppercase tracking-wide text-white">Mempelai Wanita</span>
      </div>
      <h3 class="font-serif text-2xl font-bold text-emerald-900 leading-tight">{{ bride.full_name }}</h3>
      <p v-if="bride.father_name || bride.mother_name" class="mt-2 text-xs text-slate-500 leading-relaxed">
        {{ bride.child_order_text || 'Putri dari' }}<br>
        Bapak <span class="font-semibold text-slate-700">{{ bride.father_name || '—' }}</span>
        &amp; Ibu <span class="font-semibold text-slate-700">{{ bride.mother_name || '—' }}</span>
      </p>
    </div>

    <!-- Empty state -->
    <div v-if="!groom && !bride" class="text-slate-400 text-sm italic">
      Data mempelai belum diisi
    </div>
  </div>
</template>
