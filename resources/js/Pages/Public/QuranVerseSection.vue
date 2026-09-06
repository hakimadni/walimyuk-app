<script setup>
import { computed } from 'vue'
import { resolveBuilder } from '@/lib/invitationTheme'

const props = defineProps({
  verse: { type: Object, default: null },
  themeConfig: { type: Object, default: () => ({}) },
})

const builder = computed(() => resolveBuilder(props.themeConfig))
const palette = computed(() => builder.value.content.palette)
</script>

<template>
  <div class="flex h-full min-h-0 items-center justify-center px-5 py-8">
    <div
      class="w-full rounded-3xl border px-6 py-8 text-center shadow-lg backdrop-blur-sm aos-item aos-zoom-in"
      :style="{ backgroundColor: `${palette.secondary}12`, borderColor: `${palette.secondary}44` }"
    >
      <template v-if="verse">
        <p class="mb-3 text-[10px] font-bold uppercase tracking-[0.25em] aos-item aos-fade-down aos-delay-100" :style="{ color: palette.secondary }">
          {{ verse.surah_name }} : {{ verse.ayah_number }}
        </p>
        <p class="mb-5 text-xl leading-loose font-serif aos-item aos-fade-up aos-delay-200" dir="rtl" style="font-family: 'Amiri', serif" :style="{ color: palette.primary }">
          {{ verse.arabic_text }}
        </p>
        <p v-if="verse.transliteration" class="mb-3 text-[11px] italic leading-relaxed opacity-85 aos-item aos-fade-up aos-delay-250" :style="{ color: palette.secondary }">
          {{ verse.transliteration }}
        </p>
        <p class="text-sm leading-relaxed font-medium aos-item aos-fade-up aos-delay-300" :style="{ color: palette.primary }">
          "{{ verse.translation }}"
        </p>
      </template>

      <template v-else>
        <p class="mb-3 text-[10px] font-bold uppercase tracking-[0.25em] aos-item aos-fade-down aos-delay-100" :style="{ color: palette.secondary }">
          QS Ar-Rum : 21
        </p>
        <p class="mb-5 text-xl leading-loose font-serif aos-item aos-fade-up aos-delay-200" dir="rtl" :style="{ color: palette.primary }">
          وَمِنْ آيَاتِهِ أَنْ خَلَقَ لَكُمْ مِنْ أَنْفُسِكُمْ أَزْوَاجًا لِتَسْكُنُوا إِلَيْهَا
        </p>
        <p class="text-sm leading-relaxed font-medium aos-item aos-fade-up aos-delay-300" :style="{ color: palette.primary }">
          "Dan di antara tanda-tanda kekuasaan-Nya ialah Dia menciptakan untukmu pasangan-pasangan dari jenismu sendiri, supaya kamu cenderung dan merasa tenteram kepadanya."
        </p>
      </template>
    </div>
  </div>
</template>
