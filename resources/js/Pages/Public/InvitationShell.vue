<script setup>
import { computed, onMounted, onUnmounted, ref, watch, nextTick } from 'vue'
import CoverSection from './CoverSection.vue'
import QuranVerseSection from './QuranVerseSection.vue'
import CountdownSection from './CountdownSection.vue'
import CoupleSection from './CoupleSection.vue'
import ScheduleSection from './ScheduleSection.vue'
import LocationSection from './LocationSection.vue'
import GiftSection from './GiftSection.vue'
import RsvpSection from './RsvpSection.vue'
import WishesSection from './WishesSection.vue'
import ClosingSection from './ClosingSection.vue'
import { enabledBlocks, resolveBuilder, resolveBackgroundVisual, resolveAssetUrl, animationClass } from '@/lib/invitationTheme'

const props = defineProps({
  wedding: { type: Object, required: true },
  coupleProfiles: { type: Array, default: () => [] },
  verse: { type: Object, default: null },
  events: { type: Array, default: () => [] },
  giftBankAccounts: { type: Array, default: () => [] },
  giftAddresses: { type: Array, default: () => [] },
  guest: { type: Object, default: null },
  existingRsvp: { type: Object, default: null },
  approvedWishes: { type: Array, default: () => [] },
  themeConfig: { type: Object, default: () => ({}) }
})

const isOpened = ref(false)
const currentIndex = ref(0)
let isScrolling = false
let scrollTimer = null

const localThemeConfig = ref(props.themeConfig)

const builder = computed(() => resolveBuilder(localThemeConfig.value))
const palette = computed(() => builder.value.content.palette)
const background = computed(() => resolveBackgroundVisual(builder.value.content.background_image))
const contentDecos = computed(() => builder.value.content.content_decorations || {})
const musicUrl = computed(() => builder.value.content.music_uploaded_url || builder.value.content.music_url)
const musicAutoplay = computed(() => builder.value.content.music_autoplay)
const isMusicPlaying = ref(false)

// Section registry keyed by block id
const sectionComponents = {
  ayat:      { component: QuranVerseSection,  getProps: () => ({ verse: props.verse }) },
  countdown: { component: CountdownSection,   getProps: () => ({ weddingDate: props.wedding.wedding_date }) },
  mempelai:  { component: CoupleSection,      getProps: () => ({ profiles: props.coupleProfiles }) },
  acara:     { component: ScheduleSection,    getProps: () => ({ events: props.events }) },
  lokasi:    { component: LocationSection,    getProps: () => ({ events: props.events }) },
  gift:      { component: GiftSection,        getProps: () => ({ bankAccounts: props.giftBankAccounts, addresses: props.giftAddresses }) },
  rsvp:      { component: RsvpSection,        getProps: () => ({ guest: props.guest, wedding: props.wedding, existingRsvp: props.existingRsvp }) },
  doa:       { component: WishesSection,      getProps: () => ({ wishes: props.approvedWishes }) },
  penutup:   { component: ClosingSection,     getProps: () => ({ wedding: props.wedding, coupleProfiles: props.coupleProfiles }) },
}

const activeSections = computed(() =>
  enabledBlocks(builder.value)
    .map(block => ({ id: block.id, label: block.label, ...sectionComponents[block.id] }))
    .filter(s => s.component)
)

function goNext() {
  if (currentIndex.value < activeSections.value.length - 1) {
    currentIndex.value++
    scrollToCurrent()
  }
}

function goPrev() {
  if (currentIndex.value > 0) {
    currentIndex.value--
    scrollToCurrent()
  }
}

function scrollToCurrent() {
  isScrolling = true
  clearTimeout(scrollTimer)
  scrollTimer = setTimeout(() => { isScrolling = false }, 800)
  
  const id = activeSections.value[currentIndex.value]?.id
  if (id) {
    const el = document.getElementById(`section-${id}`)
    if (el) el.scrollIntoView({ behavior: 'smooth' })
  }
}

watch(isOpened, async (val) => {
  if (val) {
    await nextTick()
    const observer = new IntersectionObserver((entries) => {
      if (isScrolling) return
      let maxRatio = 0
      let mostVisibleId = null
      entries.forEach(entry => {
        if (entry.isIntersecting && entry.intersectionRatio > maxRatio) {
          maxRatio = entry.intersectionRatio
          mostVisibleId = entry.target.id.replace('section-', '')
        }
      })
      if (mostVisibleId) {
        const idx = activeSections.value.findIndex(s => s.id === mostVisibleId)
        if (idx !== -1) currentIndex.value = idx
      }
    }, { threshold: [0.1, 0.5, 0.9] })

    activeSections.value.forEach(section => {
      const el = document.getElementById(`section-${section.id}`)
      if (el) observer.observe(el)
    })
  }
})

function openInvitation() {
  isOpened.value = true
  if (musicUrl.value && musicAutoplay.value) {
    const audio = document.getElementById('invitation-music')
    if (audio) {
      audio.play().catch(() => {})
      isMusicPlaying.value = true
    }
  }
}

function toggleMusic() {
  const audio = document.getElementById('invitation-music')
  if (!audio) return
  if (isMusicPlaying.value) {
    audio.pause()
  } else {
    audio.play().catch(() => {})
  }
  isMusicPlaying.value = !isMusicPlaying.value
}

onMounted(() => {
  // Message listener for live preview in iframe
  window.addEventListener('message', (event) => {
    if (event.data?.type === 'UPDATE_THEME') {
      localThemeConfig.value = { ...localThemeConfig.value, builder: event.data.builder }
    }
  })
})
</script>

<template>
  <div
    class="min-h-screen w-full"
    :class="builder.content.font_family || 'font-sans'"
    :style="{
      backgroundColor: palette.background,
      backgroundImage: background.type === 'image' ? `url(${background.value})` : 'none',
      backgroundSize: 'cover',
      backgroundPosition: 'center',
      backgroundAttachment: 'fixed',
      color: palette.text
    }"
  >
    <audio v-if="musicUrl" id="invitation-music" :src="musicUrl" loop preload="none" class="hidden" />

    <!-- COVER -->
    <Transition name="cover-fade">
      <CoverSection
        v-if="!isOpened"
        :wedding="wedding"
        :guest="guest"
        :coupleProfiles="coupleProfiles"
        :themeConfig="themeConfig"
        @open="openInvitation"
        class="fixed inset-0 z-50"
      />
    </Transition>

    <!-- MAIN CONTENT (Vertical Layout) -->
    <div v-if="isOpened" class="relative min-h-screen pb-20 pl-16">
      
      <!-- Left Side Simple Navigation (Up/Down) -->
      <nav
        class="fixed left-0 top-0 bottom-0 z-40 flex w-16 flex-col items-center justify-center gap-3 border-r backdrop-blur-md"
        :style="{ backgroundColor: `${palette.background}dd`, borderColor: `${palette.primary}18` }"
      >
        <button
          @click="goPrev"
          :disabled="currentIndex <= 0"
          class="flex h-10 w-10 items-center justify-center rounded-full transition-all active:scale-95 disabled:opacity-30 disabled:cursor-not-allowed hover:opacity-80"
          :style="{ backgroundColor: `${palette.primary}15`, color: palette.primary }"
          aria-label="Seksi Sebelumnya"
        >
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
          </svg>
        </button>
        
        <!-- Dot indicator context -->
        <span class="text-[10px] font-bold" :style="{ color: palette.primary }">{{ currentIndex + 1 }} / {{ activeSections.length }}</span>

        <button
          @click="goNext"
          :disabled="currentIndex >= activeSections.length - 1"
          class="flex h-10 w-10 items-center justify-center rounded-full transition-all active:scale-95 disabled:opacity-30 disabled:cursor-not-allowed hover:opacity-80"
          :style="{ backgroundColor: `${palette.primary}15`, color: palette.primary }"
          aria-label="Seksi Selanjutnya"
        >
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
          </svg>
        </button>
      </nav>

      <!-- Music Toggle Button (Bottom Left) -->
      <button
        v-if="musicUrl"
        @click="toggleMusic"
        class="fixed bottom-4 left-3 z-50 flex h-10 w-10 items-center justify-center rounded-full shadow-lg transition-transform active:scale-90"
        :style="{ backgroundColor: palette.primary, color: '#fff' }"
      >
        <svg v-if="isMusicPlaying" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c-1.105 0-2-.895-2-2s.895-2 2-2 2 .895 2 2-.895 2-2 2zm12-3c-1.105 0-2-.895-2-2s.895-2 2-2 2 .895 2 2-.895 2-2 2z" />
        </svg>
        <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" clip-rule="evenodd" />
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" />
        </svg>
      </button>

      <!-- Sections Container -->
      <div class="flex w-full max-w-[480px] mx-auto flex-col shadow-2xl relative backdrop-blur-md" :style="{ backgroundColor: `${palette.background}ee` }">
        <section
          v-for="section in activeSections"
          :key="section.id"
          :id="`section-${section.id}`"
          class="relative w-full min-h-screen py-12 flex flex-col items-center justify-center overflow-hidden"
        >
          <!-- Content Decorations Overlay (4-slot) -->
          <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <template v-for="slot in ['top', 'bottom', 'left', 'right']" :key="slot">
              <div
                v-if="resolveAssetUrl(contentDecos[slot])"
                class="absolute"
                :class="animationClass(contentDecos[slot]?.animation)"
                :style="{
                  left:    (contentDecos[slot]?.x ?? 50) + '%',
                  top:     (contentDecos[slot]?.y ?? 50) + '%',
                  transform: 'translate(-50%, -50%)',
                  width:   (contentDecos[slot]?.size ?? 150) + 'px',
                  opacity: (contentDecos[slot]?.opacity ?? 90) / 100,
                  zIndex: 0,
                }"
              >
                <img :src="resolveAssetUrl(contentDecos[slot])" class="h-auto w-full object-contain" alt="" />
              </div>
            </template>
          </div>
          <component :is="section.component" v-bind="section.getProps()" class="w-full relative z-10" />
        </section>
      </div>
    </div>
  </div>
</template>

<style scoped>
.cover-fade-leave-active {
  transition: opacity 0.5s ease, transform 0.5s ease;
}
.cover-fade-leave-to {
  opacity: 0;
  transform: scale(1.04);
}
</style>
