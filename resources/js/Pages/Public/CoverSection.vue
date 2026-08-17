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

const builder     = computed(() => resolveBuilder(props.themeConfig))
const palette     = computed(() => builder.value.content.palette)
const customText  = computed(() => builder.value.content.custom_text || {})
const coverBg     = computed(() => resolveBackgroundVisual(builder.value.content.cover_background_image))
const coverDecos  = computed(() => builder.value.content.cover_decorations || {})
const charItem    = computed(() => builder.value.content.character_image || {})
const character   = computed(() => resolveCharacterVisual(charItem.value))

const SLOTS = ['top', 'bottom', 'left', 'right']
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
          :class="animationClass(coverDecos[slot]?.animation)"
          :style="{
            left:    (coverDecos[slot]?.x ?? 50) + '%',
            top:     (coverDecos[slot]?.y ?? 50) + '%',
            transform: 'translate(-50%, -50%)',
            width:   (coverDecos[slot]?.size ?? 150) + 'px',
            opacity: (coverDecos[slot]?.opacity ?? 90) / 100,
          }"
        >
          <img :src="resolveAssetUrl(coverDecos[slot])" class="h-auto w-full object-contain" alt="" />
        </div>
      </template>
    </div>

    <!-- ── Character / Illustration ───────────────────────────────── -->
    <div
      v-if="character.type !== 'none'"
      class="pointer-events-none absolute"
      :style="{
        left:      (charItem.x ?? 50) + '%',
        top:       (charItem.y ?? 35) + '%',
        transform: 'translate(-50%, -50%)',
        width:     (charItem.size ?? 120) + 'px',
        zIndex:    5,
      }"
    >
      <img v-if="character.type === 'image'" :src="character.value" class="h-auto w-full object-contain" alt="" />
      <span v-else class="text-6xl leading-none">{{ character.value }}</span>
    </div>

    <!-- ── Main Content ────────────────────────────────────────────── -->
    <div class="relative z-10 flex h-full flex-col items-center justify-center px-8">
      <!-- Salam -->
      <p class="mb-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-white/70">
        Assalamu'alaikum Wr. Wb.
      </p>
      <p class="mb-5 text-xs uppercase tracking-wide" :style="{ color: `${palette.secondary}cc` }">
        {{ customText.cover_intro || 'Undangan Pernikahan' }}
      </p>

      <!-- Names -->
      <h1 class="mb-0.5 font-serif text-3xl font-bold leading-tight drop-shadow-md" :style="{ color: palette.secondary }">
        {{ groom.full_name || 'Mempelai Pria' }}
      </h1>
      <p class="mb-0.5 text-white/60 text-sm">&</p>
      <h1 class="mb-4 font-serif text-3xl font-bold leading-tight drop-shadow-md" :style="{ color: palette.secondary }">
        {{ bride.full_name || 'Mempelai Wanita' }}
      </h1>

      <!-- Date -->
      <p class="mb-6 text-sm text-white/70">
        {{ wedding.wedding_date ? formatDate(wedding.wedding_date) : 'Tanggal akan diumumkan' }}
      </p>

      <!-- Guest card -->
      <div
        v-if="guest"
        class="mb-6 w-full max-w-xs rounded-2xl border px-4 py-3"
        :style="{ borderColor: `${palette.secondary}44`, background: 'rgba(255,255,255,0.08)' }"
      >
        <p class="text-[11px] text-white/60 mb-0.5">Kepada Yth.</p>
        <p class="font-serif text-base font-semibold" :style="{ color: palette.secondary }">{{ guest.name }}</p>
        <p class="mt-1 text-[11px] leading-relaxed text-white/60">
          Kami mengundang Bapak/Ibu/Saudara/i untuk hadir dan memberikan doa restu.
        </p>
      </div>

      <!-- Open button -->
      <button
        class="w-full max-w-xs rounded-full py-3.5 text-sm font-bold shadow-lg transition active:scale-95"
        :style="{ background: palette.secondary, color: palette.primary }"
        @click="$emit('open')"
      >
        {{ customText.cover_button_label || '💌 Buka Undangan' }}
      </button>
    </div>
  </div>
</template>

