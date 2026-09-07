<script setup>
import { computed } from 'vue'
import { formatDate } from '@/lib/date'
import {
  resolveBuilder,
  resolveAssetUrl,
  resolveCharacterVisual,
  resolveBackgroundVisual,
  animationClass,
} from '@/lib/invitationTheme'

const props = defineProps({
  wedding: { type: Object, required: true },
  guest: { type: Object, default: null },
  coupleProfiles: { type: Array, default: () => [] },
  themeConfig: { type: Object, default: () => ({}) },
})

defineEmits(['open'])

const groom = computed(() => props.coupleProfiles.find(p => p.role === 'groom') || props.coupleProfiles[0] || {})
const bride  = computed(() => props.coupleProfiles.find(p => p.role === 'bride')  || props.coupleProfiles[1] || {})

const groomNickname = computed(() => {
  if (groom.value?.nickname && groom.value.nickname.trim()) {
    return groom.value.nickname.trim()
  }
  if (groom.value?.full_name && groom.value.full_name.trim()) {
    const clean = groom.value.full_name.split(',')[0].trim()
    return clean.split(' ')[0] || clean
  }
  return 'Hakim'
})

const brideNickname = computed(() => {
  if (bride.value?.nickname && bride.value.nickname.trim()) {
    return bride.value.nickname.trim()
  }
  if (bride.value?.full_name && bride.value.full_name.trim()) {
    const clean = bride.value.full_name.split(',')[0].trim()
    return clean.split(' ')[0] || clean
  }
  return 'Dhanya'
})

const builder     = computed(() => resolveBuilder(props.themeConfig))
const palette     = computed(() => builder.value.content.palette)
const customText  = computed(() => builder.value.content.custom_text || {})
const coverBg     = computed(() => resolveBackgroundVisual(builder.value.content.cover_background_image))
const coverDecos  = computed(() => builder.value.content.cover_decorations || {})
const charItem    = computed(() => builder.value.content.character_image || {})
const character   = computed(() => resolveCharacterVisual(charItem.value))

const SLOTS = ['top', 'bottom', 'left', 'right']

const textEffectStyle = computed(() => {
  const effect = builder.value.content.cover_text_effect
  if (effect === 'shadow') {
    return { textShadow: '2px 2px 4px rgba(0,0,0,0.8), 0 0 15px rgba(0,0,0,0.6)' }
  } else if (effect === 'stroke') {
    return { textShadow: '-1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000, 1px 1px 0 #000, 0px 4px 8px rgba(0,0,0,0.6)' }
  }
  return {}
})

const guestCardBgStyle = computed(() => {
  const bg = palette.value.guest_card_background || '#000000'
  if (bg.startsWith('#') && bg.length === 7) {
    return bg + 'E6' // Append ~90% opacity to maintain color but allow slight frost effect
  }
  return bg
})
</script>

<template>
  <div
    class="fixed inset-0 z-50 overflow-hidden"
    :class="builder.content.font_family || 'font-sans'"
    :style="{
      background: coverBg.type === 'image'
        ? `url(${coverBg.value}) center/cover no-repeat`
        : `linear-gradient(160deg, ${palette.primary} 0%, ${palette.text} 100%)`,
      color: '#fff',
    }"
  >
    <!-- ── 4-Slot Cover Decorations ─────────────────────────────── -->
    <div class="pointer-events-none absolute inset-0 overflow-hidden">
      <template v-for="slot in SLOTS" :key="slot">
        <div
          v-if="resolveAssetUrl(coverDecos[slot])"
          class="absolute"
          :style="{
            left:    (coverDecos[slot]?.x ?? 50) + '%',
            top:     (coverDecos[slot]?.y ?? 50) + '%',
            transform: 'translate(-50%, -50%)',
            width:   (coverDecos[slot]?.size ?? 150) + 'px',
            opacity: (coverDecos[slot]?.opacity ?? 90) / 100,
          }"
        >
          <div :class="animationClass(coverDecos[slot]?.animation)" class="w-full h-full">
            <img :src="resolveAssetUrl(coverDecos[slot])" class="h-auto w-full object-contain" alt="" />
          </div>
        </div>
      </template>
    </div>

    <!-- ── Top Area: The Wedding Of & Nama Mempelai ────────── -->
    <div
      class="pointer-events-none absolute top-0 left-0 right-0 z-10 flex flex-col items-center px-6 text-center anim-fade-down"
      :style="{ paddingTop: `calc(env(safe-area-inset-top, 0px) + ${(builder.content.cover_top_spacing ?? 175)}px)` }"
    >
      <!-- The Wedding Of -->
      <p class="font-serif italic text-xs sm:text-sm tracking-wider opacity-90 mb-1 anim-fade-down delay-100" :style="{ color: palette.text, ...textEffectStyle }">
        {{ customText.cover_intro || 'The Wedding Of' }}
      </p>

      <!-- Nama Mempelai (Panggilan) -->
      <div class="flex flex-col items-center anim-zoom-in delay-150">
        <h1 class="font-serif italic text-2xl sm:text-3xl font-bold leading-tight tracking-wide" :style="{ color: palette.secondary, ...textEffectStyle }">
          {{ groomNickname }}
        </h1>
        <span class="font-serif italic text-lg sm:text-xl font-bold -my-0.5 leading-none select-none" :style="{ color: palette.secondary, ...textEffectStyle }">&amp;</span>
        <h1 class="font-serif italic text-2xl sm:text-3xl font-bold leading-tight tracking-wide" :style="{ color: palette.secondary, ...textEffectStyle }">
          {{ brideNickname }}
        </h1>
      </div>
    </div>

    <!-- ── Character / Illustration ───────────────────────────────── -->
    <div
      v-if="character.type !== 'none'"
      class="pointer-events-none absolute flex items-center justify-center"
      :style="{
        left:      (charItem.x ?? 50) + '%',
        top:       (charItem.y ?? 55) + '%',
        transform: 'translate(-50%, -50%)',
        width:     (charItem.size ?? 280) + 'px',
        maxWidth:  '90vw',
        zIndex:    5,
      }"
    >
      <img
        v-if="character.type === 'image'"
        :src="character.value"
        class="h-auto w-full max-h-[48vh] object-contain anim-fade-in delay-200 drop-shadow-md"
        alt=""
      />
      <span v-else class="text-7xl leading-none anim-fade-in delay-200 inline-block select-none">{{ character.value }}</span>
    </div>

    <!-- ── Bottom Panel ───────────────────────────────────────────── -->
    <div class="absolute bottom-0 left-0 right-0 z-20 flex flex-col items-center px-6 pb-8 pt-2 gap-2.5">
      <!-- Card Tamu (Lebih Ringkas & Proporsional) -->
      <div
        class="w-full max-w-[270px] rounded-2xl border px-3.5 py-2.5 shadow-lg backdrop-blur-sm text-center anim-fade-up delay-300"
        :style="{ borderColor: `${palette.secondary}66`, backgroundColor: guestCardBgStyle }"
      >
        <p class="text-[10px] font-medium tracking-wide mb-0.5" :style="{ color: palette.text }">Kepada Yth.</p>
        <p class="font-serif text-base font-bold leading-snug" :style="{ color: palette.text }">
          {{ guest?.name || 'Tamu Undangan' }}
        </p>

        <!-- Tanggal & Hashtag di dalam card nama undangan -->
        <div class="mt-2 pt-1.5 border-t border-white/15">
          <p class="text-[11px] sm:text-xs font-semibold tracking-wide" :style="{ color: palette.text, ...textEffectStyle }">
            {{ wedding.wedding_date ? formatDate(wedding.wedding_date) : 'Tanggal akan diumumkan' }}
          </p>
          <p class="text-[10px] sm:text-[11px] font-semibold tracking-wider mt-0.5 opacity-90" :style="{ color: palette.secondary, ...textEffectStyle }">
            {{ customText.cover_hashtag || '#selamANYAuntukHAKIM' }}
          </p>
        </div>
      </div>

      <!-- Tombol Buka Undangan (Outline Style) -->
      <button
        class="w-full max-w-[270px] rounded-full border-2 py-2.5 sm:py-3 text-xs sm:text-sm font-bold shadow-lg backdrop-blur-md transition-all duration-200 active:scale-95 hover:opacity-90 anim-fade-up delay-400 anim-pulse-soft"
        :style="{
          borderColor: palette.secondary,
          backgroundColor: guestCardBgStyle,
          color: palette.secondary
        }"
        @click="$emit('open')"
      >
        {{ customText.cover_button_label || '💌 Buka Undangan' }}
      </button>
    </div>
  </div>
</template>

