<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'

const props = defineProps({
  wedding: { type: Object, required: true },
  verse: { type: Object, default: () => null },
})

const defaultPresets = [
  {
    label: 'QS. Ar-Rum: 21 (Mawaddah wa Rahmah)',
    source: 'QS. Ar-Rum: 21',
    arabic: 'وَمِنْ آيَاتِهِ أَنْ خَلَقَ لَكُم مِّنْ أَنفُسِكُمْ أَزْوَاجًا لِّتَسْكُنُوا إِلَيْهَا وَجَعَلَ بَيْنَكُم مَّوَدَّةً وَرَحْمَةً ۚ إِنَّ فِي ذَٰلِكَ لَآيَاتٍ لِّقَوْمٍ يَتَفَكَّرُونَ',
    transliteration: 'Wa min aayaatihii an khalaqa lakum min anfusikum azwaajal litaskunuuu ilaihaa wa ja\'ala bainakum mawaddataw wa rahmah...',
    translation: 'Dan di antara tanda-tanda (kebesaran)-Nya ialah Dia menciptakan pasangan-pasangan untukmu dari jenismu sendiri, agar kamu cenderung dan merasa tenteram kepadanya, dan Dia menjadikan di antaramu rasa kasih dan sayang.',
  },
  {
    label: 'QS. An-Nur: 32 (Janji Kecukupan & Keberkahan)',
    source: 'QS. An-Nur: 32',
    arabic: 'وَأَنكِحُوا الْأَيَامَىٰ مِنكُمْ وَالصَّالِحِينَ مِنْ عِبَادِكُمْ وَإِمَائِكُمْ ۚ إِن يَكُونُوا فُقَرَاءَ يُغْنِهِمُ اللَّهُ مِن فَضْلِهِ ۗ وَاللَّهُ وَاسِعٌ عَلِيمٌ',
    transliteration: 'Wa ankihul ayaamaa minkum wassaalihiina min \'ibaadikum wa imaaa\'ikum...',
    translation: 'Dan nikahkanlah orang-orang yang masih membujang di antara kamu, dan juga orang-orang yang layak (menikah) dari hamba-hamba sahayamu yang laki-laki dan perempuan. Jika mereka miskin, Allah akan memberi kemampuan kepada mereka dengan karunia-Nya.',
  },
  {
    label: 'HR. Baihaqi (Menyempurnakan Separuh Agama)',
    source: 'HR. Al-Baihaqi',
    arabic: 'إِذَا تَزَوَّجَ الْعَبْدُ فَقَدِ اسْتَكْمَلَ نِصْفَ الدِّينِ فَلْيَتَّقِ اللَّهَ فِي النِّصْفِ الْبَاقِي',
    transliteration: 'Idzaa tazawwajal \'abdu faqadistakmala nishfad diin, falyattaqillaaha fin nishfil baaqii.',
    translation: 'Jika seorang hamba telah menikah, maka ia telah menyempurnakan separuh agamanya. Maka hendaklah ia bertakwa kepada Allah untuk separuh yang tersisa.',
  },
]

const form = useForm({
  source_label: props.verse?.source_label || defaultPresets[0].source,
  arabic_text: props.verse?.arabic_text || defaultPresets[0].arabic,
  transliteration: props.verse?.transliteration || defaultPresets[0].transliteration,
  translation: props.verse?.translation || defaultPresets[0].translation,
})

function applyPreset(preset) {
  form.source_label = preset.source
  form.arabic_text = preset.arabic
  form.transliteration = preset.transliteration
  form.translation = preset.translation
}

function submit() {
  form.put(`/weddings/${props.wedding.id}/wedding-verses`)
}
</script>

<template>
  <Head :title="`Ayat Suci & Doa - ${wedding.cover_subtitle || wedding.cover_title}`" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-2">
        <Link :href="`/weddings/${wedding.id}`" class="text-xs text-emerald-700 hover:underline">&larr; Kembali ke Detail</Link>
      </div>
      <h2 class="font-serif text-3xl font-bold text-emerald-950 mt-1">Kutipan Ayat Suci &amp; Doa Pernikahan</h2>
      <p class="text-sm text-slate-500">{{ wedding.cover_title }} • Pilih ayat Al-Qur'an atau hadits pembuka undangan.</p>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-4xl space-y-6 sm:px-6 lg:px-8">
        <!-- Presets Bar -->
        <div class="rounded-3xl border border-emerald-100 bg-white p-6 shadow-sm space-y-3">
          <div class="flex items-center gap-2">
            <span class="text-emerald-700">📖</span>
            <h3 class="font-bold text-slate-900 text-sm">Pilih Template Cepat (Presets):</h3>
          </div>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="(p, i) in defaultPresets"
              :key="i"
              type="button"
              class="rounded-xl border border-emerald-200 bg-emerald-50/70 px-4 py-2 text-xs font-semibold text-emerald-900 transition hover:bg-emerald-100"
              @click="applyPreset(p)"
            >
              {{ p.label }}
            </button>
          </div>
        </div>

        <!-- Verse Edit Form -->
        <form @submit.prevent="submit" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 space-y-6">
          <!-- Source Label -->
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-700">Sumber Ayat / Hadits</label>
            <Input v-model="form.source_label" placeholder="Contoh: QS. Ar-Rum: 21" required class="text-sm" />
            <p v-if="form.errors.source_label" class="text-xs text-rose-500">{{ form.errors.source_label }}</p>
          </div>

          <!-- Arabic Text -->
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-700">Teks Bahasa Arab</label>
            <textarea
              v-model="form.arabic_text"
              rows="3"
              dir="rtl"
              placeholder="وَمِنْ آيَاتِهِ أَنْ خَلَقَ لَكُم مِّنْ أَنفُسِكُمْ..."
              class="w-full rounded-2xl border border-slate-200 p-4 font-serif text-lg leading-loose focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            ></textarea>
            <p v-if="form.errors.arabic_text" class="text-xs text-rose-500">{{ form.errors.arabic_text }}</p>
          </div>

          <!-- Transliteration -->
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-700">Transliterasi Latin (Opsional)</label>
            <textarea
              v-model="form.transliteration"
              rows="2"
              placeholder="Wa min aayaatihii..."
              class="w-full rounded-xl border border-slate-200 p-3 text-xs focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 italic text-slate-600"
            ></textarea>
            <p v-if="form.errors.transliteration" class="text-xs text-rose-500">{{ form.errors.transliteration }}</p>
          </div>

          <!-- Translation -->
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-700">Terjemahan Bahasa Indonesia</label>
            <textarea
              v-model="form.translation"
              rows="3"
              placeholder="Dan di antara tanda-tanda kebesaran-Nya..."
              class="w-full rounded-xl border border-slate-200 p-3 text-xs focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 text-slate-700 leading-relaxed"
            ></textarea>
            <p v-if="form.errors.translation" class="text-xs text-rose-500">{{ form.errors.translation }}</p>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <Link :href="`/weddings/${wedding.id}`">
              <Button type="button" variant="outline" class="rounded-xl text-xs">Batal</Button>
            </Link>
            <Button
              type="submit"
              :disabled="form.processing"
              class="rounded-xl bg-emerald-700 px-6 text-xs font-semibold text-white hover:bg-emerald-800"
            >
              {{ form.processing ? 'Menyimpan...' : 'Simpan Kutipan Ayat' }}
            </Button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
