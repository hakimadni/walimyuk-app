<script setup>
import { computed } from 'vue'
import { resolveBuilder } from '@/lib/invitationTheme'

const props = defineProps({
  wishes: { type: Array, default: () => [] },
  themeConfig: { type: Object, default: () => ({}) },
})

const builder = computed(() => resolveBuilder(props.themeConfig))
const palette = computed(() => builder.value.content.palette)
</script>

<template>
  <div class="flex h-full min-h-0 flex-col px-5 py-8">
    <h2 class="mb-1 text-center font-serif text-2xl font-bold aos-item aos-fade-down" :style="{ color: palette.primary }">
      Ucapan &amp; Doa
    </h2>
    <p class="mb-5 text-center text-xs leading-relaxed opacity-85 aos-item aos-fade-down aos-delay-100" :style="{ color: palette.secondary }">
      Terima kasih atas doa terbaik untuk kami.
    </p>

    <div v-if="wishes.length" class="flex-1 space-y-3 overflow-y-auto pb-2 scrollbar-hide">
      <div
        v-for="(wish, i) in wishes"
        :key="i"
        class="flex flex-col items-center gap-2 rounded-2xl border px-4 py-4 text-center shadow-sm backdrop-blur-sm aos-item aos-fade-up"
        :class="i === 0 ? 'aos-delay-150' : i === 1 ? 'aos-delay-250' : 'aos-delay-350'"
        :style="{ backgroundColor: `${palette.secondary}12`, borderColor: `${palette.secondary}44` }"
      >
        <!-- Avatar -->
        <div
          class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full border text-sm font-bold shadow-xs aos-item aos-zoom-in aos-delay-200"
          :style="{ backgroundColor: `${palette.secondary}25`, borderColor: `${palette.secondary}60`, color: palette.primary }"
        >
          {{ (wish.name || wish.guest?.name || 'T')[0].toUpperCase() }}
        </div>
        <div class="w-full min-w-0">
          <p class="text-sm font-bold truncate" :style="{ color: palette.primary }">
            {{ wish.name || wish.guest?.name || 'Tamu Undangan' }}
          </p>
          <p class="mt-1 text-[13px] leading-relaxed opacity-90" :style="{ color: palette.primary }">
            {{ wish.message }}
          </p>
        </div>
      </div>
    </div>

    <div v-else class="flex flex-1 items-center justify-center py-10 text-center">
      <div>
        <p class="text-4xl mb-3">💌</p>
        <p class="text-sm italic opacity-60" :style="{ color: palette.primary }">Belum ada ucapan</p>
      </div>
    </div>
  </div>
</template>
