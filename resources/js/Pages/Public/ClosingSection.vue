<script setup>
import { computed } from 'vue'
import { resolveBuilder } from '@/lib/invitationTheme'

const props = defineProps({
  wedding: { type: Object, required: true },
  coupleProfiles: { type: Array, default: () => [] },
  themeConfig: { type: Object, default: () => ({}) },
})

const groom = computed(() => props.coupleProfiles.find(p => p.role === 'groom') || props.coupleProfiles[0] || null)
const bride = computed(() => props.coupleProfiles.find(p => p.role === 'bride') || props.coupleProfiles[1] || null)

const builder = computed(() => resolveBuilder(props.themeConfig))
const palette = computed(() => builder.value.content.palette)
const customText = computed(() => builder.value.content.custom_text || {})
</script>

<template>
  <div class="flex h-full min-h-0 flex-col items-center justify-center px-6 py-8 text-center">
    <div
      class="w-full rounded-3xl border px-6 py-8 text-center shadow-lg backdrop-blur-sm aos-item aos-zoom-in"
      :style="{ backgroundColor: `${palette.secondary}12`, borderColor: `${palette.secondary}44` }"
    >
      <!-- Ornament top -->
      <div class="mb-5 text-3xl anim-float inline-block">💍</div>

      <p class="mb-4 text-sm leading-relaxed max-w-xs mx-auto font-medium aos-item aos-fade-up aos-delay-150" :style="{ color: palette.primary }">
        {{ customText.closing_note || wedding.closing_text || 'Merupakan suatu kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir dan memberikan doa restu.' }}
      </p>

      <p class="mb-3 text-sm leading-relaxed max-w-xs mx-auto font-medium aos-item aos-fade-up aos-delay-200" :style="{ color: palette.primary }">
        Atas kehadiran dan doa restunya, kami mengucapkan terima kasih.
      </p>

      <p class="mb-6 text-sm font-bold italic aos-item aos-fade-up aos-delay-250" :style="{ color: palette.primary }">
        Wassalamu'alaikum Warahmatullahi Wabarakatuh
      </p>

      <!-- Divider Hint -->
      <div class="mb-5 flex w-full items-center gap-3 px-4 aos-item aos-zoom-in aos-delay-300">
        <div class="flex-1 h-px" :style="{ backgroundColor: `${palette.secondary}44` }" />
        <span class="text-lg anim-pulse-soft" :style="{ color: palette.secondary }">✦</span>
        <div class="flex-1 h-px" :style="{ backgroundColor: `${palette.secondary}44` }" />
      </div>

      <p class="text-xs mb-3 font-semibold tracking-wider uppercase aos-item aos-fade-up aos-delay-350" :style="{ color: palette.secondary }">
        Kami yang berbahagia,
      </p>

      <div class="space-y-1 aos-item aos-fade-up aos-delay-400">
        <h3 class="font-serif text-2xl font-bold" :style="{ color: palette.primary }">
          {{ groom?.full_name || '—' }}
        </h3>
        <p class="text-base font-bold" :style="{ color: palette.secondary }">&amp;</p>
        <h3 class="font-serif text-2xl font-bold" :style="{ color: palette.primary }">
          {{ bride?.full_name || '—' }}
        </h3>
      </div>
    </div>

    <!-- Powered by subtle note -->
    <p class="mt-6 text-[10px] opacity-60" :style="{ color: palette.secondary }">
      Dibuat dengan ❤ menggunakan WalimYuk
    </p>
  </div>
</template>
