<script setup>
import { computed } from 'vue'
import { resolveBuilder } from '@/lib/invitationTheme'

const props = defineProps({
  profiles: { type: Array, default: () => [] },
  themeConfig: { type: Object, default: () => ({}) },
})

const builder = computed(() => resolveBuilder(props.themeConfig))
const palette = computed(() => builder.value.content.palette)

const groom = computed(() => props.profiles.find(p => p.role === 'groom') || props.profiles[0] || null)
const bride = computed(() => props.profiles.find(p => p.role === 'bride') || props.profiles[1] || null)

const groomPhoto = computed(() => groom.value?.photo_url || groom.value?.photo_path || null)
const bridePhoto = computed(() => bride.value?.photo_url || bride.value?.photo_path || null)

const frameStyle = computed(() => {
  return builder.value.content?.couple_photo_frame || 'circle'
})

const frameClasses = computed(() => {
  switch (frameStyle.value) {
    case 'portrait':
    case 'portrait_2_3':
      return {
        container: 'w-28 sm:w-32 aspect-[2/3] rounded-2xl sm:rounded-3xl',
        img: 'rounded-xl sm:rounded-2xl',
      }
    case 'rounded_square':
    case 'square':
      return {
        container: 'w-24 h-24 sm:w-28 sm:h-28 aspect-square rounded-2xl sm:rounded-3xl',
        img: 'rounded-xl sm:rounded-2xl',
      }
    case 'arch':
      return {
        container: 'w-28 sm:w-32 aspect-[2/3] rounded-t-full rounded-b-2xl',
        img: 'rounded-t-full rounded-b-xl',
      }
    case 'circle':
    default:
      return {
        container: 'w-24 h-24 sm:w-28 sm:h-28 aspect-square rounded-full',
        img: 'rounded-full',
      }
  }
})

function formatInstagram(url) {
  if (!url) return ''
  try {
    const cleaned = url.replace(/^(https?:\/\/)?(www\.)?instagram\.com\//, '').replace(/\/$/, '')
    return cleaned ? `@${cleaned}` : url
  } catch {
    return url
  }
}
</script>

<template>
  <div class="flex h-full min-h-0 flex-col items-center justify-center px-5 py-8 text-center">
    <!-- Hint pengantar -->
    <p class="mb-5 text-[11px] leading-relaxed max-w-xs aos-item aos-fade-down aos-delay-100" :style="{ color: palette.secondary }">
      Dengan memohon rahmat dan ridha Allah SWT, insyaAllah kami akan melangsungkan pernikahan:
    </p>

    <!-- Groom Card -->
    <div
      v-if="groom"
      class="mb-4 w-full rounded-2xl border px-5 py-5 shadow-sm backdrop-blur-sm aos-item aos-fade-up aos-delay-150"
      :style="{ backgroundColor: `${palette.secondary}12`, borderColor: `${palette.secondary}44` }"
    >
      <!-- Foto Profil Mempelai Pria -->
      <div
        v-if="groomPhoto"
        class="mx-auto mb-3.5 flex items-center justify-center aos-item aos-zoom-in aos-delay-200"
      >
        <div
          class="relative overflow-hidden border-2 p-1 shadow-md transition-transform duration-300 hover:scale-105"
          :class="frameClasses.container"
          :style="{ borderColor: palette.secondary, backgroundColor: `${palette.secondary}20` }"
        >
          <img
            :src="groomPhoto"
            :alt="groom.full_name || 'Mempelai Pria'"
            class="h-full w-full object-cover"
            :class="frameClasses.img"
            loading="lazy"
          />
        </div>
      </div>

      <!-- Hint Badge -->
      <div
        class="mb-2.5 inline-flex items-center gap-1.5 rounded-full px-3 py-0.5 aos-item aos-zoom-in aos-delay-200"
        :style="{ backgroundColor: `${palette.secondary}25`, border: `1px solid ${palette.secondary}66` }"
      >
        <span class="text-[10px] font-bold uppercase tracking-wider" :style="{ color: palette.secondary }">
          Mempelai Pria
        </span>
      </div>
      <!-- Teks Nama Mempelai -->
      <h3 class="font-serif text-xl sm:text-2xl font-bold leading-tight aos-item aos-fade-up aos-delay-250" :style="{ color: palette.primary }">
        {{ groom.full_name }}
      </h3>
      <!-- Detail Orang Tua -->
      <p v-if="groom.father_name || groom.mother_name" class="mt-2 text-xs leading-relaxed aos-item aos-fade-up aos-delay-300" :style="{ color: palette.primary }">
        <span class="opacity-80">{{ groom.child_order_text || 'Putra dari' }}</span><br>
        Bapak <span class="font-bold">{{ groom.father_name || '—' }}</span>
        &amp; Ibu <span class="font-bold">{{ groom.mother_name || '—' }}</span>
      </p>
      <!-- Tautan Instagram -->
      <div v-if="groom.instagram_url" class="mt-3 aos-item aos-fade-up aos-delay-350">
        <a
          :href="groom.instagram_url"
          target="_blank"
          rel="noopener noreferrer"
          class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-medium transition-all hover:scale-105 active:scale-95"
          :style="{ backgroundColor: `${palette.secondary}20`, color: palette.primary, border: `1px solid ${palette.secondary}44` }"
        >
          <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
          </svg>
          <span>{{ formatInstagram(groom.instagram_url) }}</span>
        </a>
      </div>
    </div>

    <!-- Divider Hint -->
    <div class="mb-4 flex items-center gap-3 w-full px-4 aos-item aos-zoom-in aos-delay-250">
      <div class="flex-1 h-px" :style="{ backgroundColor: `${palette.secondary}44` }" />
      <span class="font-serif text-2xl font-bold anim-pulse-soft" :style="{ color: palette.secondary }">&</span>
      <div class="flex-1 h-px" :style="{ backgroundColor: `${palette.secondary}44` }" />
    </div>

    <!-- Bride Card -->
    <div
      v-if="bride"
      class="w-full rounded-2xl border px-5 py-5 shadow-sm backdrop-blur-sm aos-item aos-fade-up aos-delay-300"
      :style="{ backgroundColor: `${palette.secondary}12`, borderColor: `${palette.secondary}44` }"
    >
      <!-- Foto Profil Mempelai Wanita -->
      <div
        v-if="bridePhoto"
        class="mx-auto mb-3.5 flex items-center justify-center aos-item aos-zoom-in aos-delay-350"
      >
        <div
          class="relative overflow-hidden border-2 p-1 shadow-md transition-transform duration-300 hover:scale-105"
          :class="frameClasses.container"
          :style="{ borderColor: palette.secondary, backgroundColor: `${palette.secondary}20` }"
        >
          <img
            :src="bridePhoto"
            :alt="bride.full_name || 'Mempelai Wanita'"
            class="h-full w-full object-cover"
            :class="frameClasses.img"
            loading="lazy"
          />
        </div>
      </div>

      <!-- Hint Badge -->
      <div
        class="mb-2.5 inline-flex items-center gap-1.5 rounded-full px-3 py-0.5 aos-item aos-zoom-in aos-delay-350"
        :style="{ backgroundColor: `${palette.secondary}25`, border: `1px solid ${palette.secondary}66` }"
      >
        <span class="text-[10px] font-bold uppercase tracking-wider" :style="{ color: palette.secondary }">
          Mempelai Wanita
        </span>
      </div>
      <!-- Teks Nama Mempelai -->
      <h3 class="font-serif text-xl sm:text-2xl font-bold leading-tight aos-item aos-fade-up aos-delay-400" :style="{ color: palette.primary }">
        {{ bride.full_name }}
      </h3>
      <!-- Detail Orang Tua -->
      <p v-if="bride.father_name || bride.mother_name" class="mt-2 text-xs leading-relaxed aos-item aos-fade-up aos-delay-450" :style="{ color: palette.primary }">
        <span class="opacity-80">{{ bride.child_order_text || 'Putri dari' }}</span><br>
        Bapak <span class="font-bold">{{ bride.father_name || '—' }}</span>
        &amp; Ibu <span class="font-bold">{{ bride.mother_name || '—' }}</span>
      </p>
      <!-- Tautan Instagram -->
      <div v-if="bride.instagram_url" class="mt-3 aos-item aos-fade-up aos-delay-500">
        <a
          :href="bride.instagram_url"
          target="_blank"
          rel="noopener noreferrer"
          class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-medium transition-all hover:scale-105 active:scale-95"
          :style="{ backgroundColor: `${palette.secondary}20`, color: palette.primary, border: `1px solid ${palette.secondary}44` }"
        >
          <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
          </svg>
          <span>{{ formatInstagram(bride.instagram_url) }}</span>
        </a>
      </div>
    </div>

    <!-- Empty state -->
    <div v-if="!groom && !bride" class="text-sm italic opacity-60" :style="{ color: palette.primary }">
      Data mempelai belum diisi
    </div>
  </div>
</template>
