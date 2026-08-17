<script setup>
const props = defineProps({
  verse: { type: Object, default: null }
})
</script>

<template>
  <div class="flex h-full min-h-0 items-center justify-center px-5 py-8">
    <div class="w-full px-6 py-8 text-center">
      <template v-if="verse">
        <p class="mb-3 text-[10px] font-bold uppercase tracking-[0.25em] text-emerald-700">
          {{ verse.surah_name }} : {{ verse.ayah_number }}
        </p>
        <p class="mb-5 text-xl leading-loose text-emerald-900" dir="rtl" style="font-family: 'Amiri', serif">
          {{ verse.arabic_text }}
        </p>
        <p v-if="verse.transliteration" class="mb-3 text-[11px] italic text-slate-400 leading-relaxed">
          {{ verse.transliteration }}
        </p>
        <p class="text-sm leading-relaxed text-slate-600">
          "{{ verse.translation }}"
        </p>
      </template>

      <template v-else>
        <p class="mb-3 text-[10px] font-bold uppercase tracking-[0.25em] text-emerald-700">
          QS Ar-Rum : 21
        </p>
        <p class="mb-5 text-xl leading-loose text-emerald-900" dir="rtl">
          وَمِنْ آيَاتِهِ أَنْ خَلَقَ لَكُمْ مِنْ أَنْفُسِكُمْ أَزْوَاجًا لِتَسْكُنُوا إِلَيْهَا
        </p>
        <p class="text-sm leading-relaxed text-slate-600">
          "Dan di antara tanda-tanda kekuasaan-Nya ialah Dia menciptakan untukmu pasangan-pasangan dari jenismu sendiri, supaya kamu cenderung dan merasa tenteram kepadanya."
        </p>
      </template>
    </div>
  </div>
</template>
