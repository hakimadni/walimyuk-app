<script setup>
import { computed, onMounted, onUnmounted, ref, watch, nextTick } from 'vue'
import { Head } from '@inertiajs/vue3'
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
import { enabledBlocks, resolveBuilder, resolveBackgroundVisual, resolveAssetUrl, normalizeStorageUrl, animationClass, DEFAULT_SLOT_COORDS, getContentDecorationStyle } from '@/lib/invitationTheme'

function defaultSlotCoord(slot, axis) {
  return DEFAULT_SLOT_COORDS[slot]?.[axis] ?? 50
}

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

const groom = computed(() => props.coupleProfiles.find(p => p.role === 'groom') || props.coupleProfiles[0] || null)
const bride = computed(() => props.coupleProfiles.find(p => p.role === 'bride') || props.coupleProfiles[1] || null)

const pageTitle = computed(() => {
  if (props.wedding.cover_title) {
    return props.wedding.cover_title
  }
  if (groom.value && bride.value) {
    const groomName = groom.value.nickname || groom.value.full_name
    const brideName = bride.value.nickname || bride.value.full_name
    return `The Wedding of ${groomName} & ${brideName}`
  }
  return props.wedding.title || 'Undangan Pernikahan'
})

const metaDescription = computed(() => {
  if (props.wedding.cover_subtitle) {
    return `${props.wedding.cover_subtitle} • Undangan Pernikahan WalimYuk`
  }
  return `Undangan Pernikahan Online ${pageTitle.value}`
})

const builder = computed(() => resolveBuilder(localThemeConfig.value))
const palette = computed(() => builder.value.content.palette)
const background = computed(() => resolveBackgroundVisual(builder.value.content.background_image))
const contentDecos = computed(() => builder.value.content.content_decorations || {})
const musicUrl = computed(() => builder.value.content.music_uploaded_url || builder.value.content.music_url)
const cleanMusicUrl = computed(() => normalizeStorageUrl(musicUrl.value))
const shouldAutoplay = computed(() => {
  const ap = builder.value.content.music_autoplay
  return ap === true || ap === 1 || ap === '1' || ap === 'true'
})
const isMusicPlaying = ref(false)

// Section registry keyed by block id
const sectionComponents = {
  ayat:      { component: QuranVerseSection,  getProps: () => ({ verse: props.verse, themeConfig: localThemeConfig.value }) },
  countdown: { component: CountdownSection,   getProps: () => ({ wedding: props.wedding, events: props.events, weddingDate: props.wedding.wedding_date, themeConfig: localThemeConfig.value }) },
  mempelai:  { component: CoupleSection,      getProps: () => ({ profiles: props.coupleProfiles, themeConfig: localThemeConfig.value }) },
  acara:     { component: ScheduleSection,    getProps: () => ({ events: props.events, themeConfig: localThemeConfig.value }) },
  lokasi:    { component: LocationSection,    getProps: () => ({ events: props.events, themeConfig: localThemeConfig.value }) },
  gift:      { component: GiftSection,        getProps: () => ({ bankAccounts: props.giftBankAccounts, addresses: props.giftAddresses, themeConfig: localThemeConfig.value }) },
  rsvp:      { component: RsvpSection,        getProps: () => ({ guest: props.guest, wedding: props.wedding, existingRsvp: props.existingRsvp, themeConfig: localThemeConfig.value }) },
  doa:       { component: WishesSection,      getProps: () => ({ wishes: props.approvedWishes, themeConfig: localThemeConfig.value }) },
  penutup:   { component: ClosingSection,     getProps: () => ({ wedding: props.wedding, coupleProfiles: props.coupleProfiles, themeConfig: localThemeConfig.value }) },
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
    
    // 1. Navigation tracking observer
    const navObserver = new IntersectionObserver((entries) => {
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

    // 2. Animate On Scroll (AOS) observer
    const aosObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('aos-animate')
        } else {
          entry.target.classList.remove('aos-animate')
        }
      })
    }, {
      threshold: 0.1,
      rootMargin: '0px 0px -20px 0px'
    })

    activeSections.value.forEach(section => {
      const el = document.getElementById(`section-${section.id}`)
      if (el) {
        navObserver.observe(el)
        aosObserver.observe(el)
      }
    })
  }
})

function openInvitation() {
  isOpened.value = true
  if (cleanMusicUrl.value && shouldAutoplay.value) {
    nextTick(() => {
      const audio = document.getElementById('invitation-music')
      if (audio) {
        audio.load()
        const playPromise = audio.play()
        if (playPromise !== undefined) {
          playPromise
            .then(() => {
              isMusicPlaying.value = true
            })
            .catch((err) => {
              console.warn('Audio autoplay prevented or failed:', err)
              isMusicPlaying.value = false
            })
        }
      }
    })
  }
}

function toggleMusic() {
  const audio = document.getElementById('invitation-music')
  if (!audio) return
  if (isMusicPlaying.value) {
    audio.pause()
    isMusicPlaying.value = false
  } else {
    const playPromise = audio.play()
    if (playPromise !== undefined) {
      playPromise
        .then(() => {
          isMusicPlaying.value = true
        })
        .catch((err) => {
          console.warn('Audio play failed:', err)
          isMusicPlaying.value = false
        })
    }
  }
}

onMounted(() => {
  // Hide document scrollbars for clean app-like invitation presentation
  document.documentElement.classList.add('no-scrollbar')
  document.body.classList.add('no-scrollbar')

  // Bind audio state events so UI button is always in sync with actual audio playback
  const audio = document.getElementById('invitation-music')
  if (audio) {
    audio.addEventListener('play', () => { isMusicPlaying.value = true })
    audio.addEventListener('pause', () => { isMusicPlaying.value = false })
    audio.addEventListener('ended', () => { isMusicPlaying.value = false })
    audio.addEventListener('error', (e) => {
      console.warn('Audio playback error:', e)
      isMusicPlaying.value = false
    })
  }

  // Message listener for live preview in iframe
  window.addEventListener('message', (event) => {
    if (event.data?.type === 'UPDATE_THEME') {
      localThemeConfig.value = { ...localThemeConfig.value, builder: event.data.builder }
    }
  })
})

onUnmounted(() => {
  document.documentElement.classList.remove('no-scrollbar')
  document.body.classList.remove('no-scrollbar')
})
</script>

<template>
  <Head>
    <title>{{ pageTitle }}</title>
    <meta name="description" :content="metaDescription" />
    <meta property="og:title" :content="pageTitle" />
    <meta property="og:description" :content="metaDescription" />
    <meta property="og:type" content="website" />
    <meta name="twitter:title" :content="pageTitle" />
    <meta name="twitter:description" :content="metaDescription" />
  </Head>

  <div
    class="min-h-screen w-full relative no-scrollbar"
    :class="builder.content.font_family || 'font-sans'"
    :style="{
      backgroundColor: palette.background,
      color: palette.primary
    }"
  >
    <!-- Viewport-fixed Background Layer (Fix for iOS Safari & Mobile) -->
    <div
      v-if="background.type === 'image'"
      class="fixed inset-0 pointer-events-none z-0"
      :style="{
        backgroundImage: `url(${background.value})`,
        backgroundSize: 'cover',
        backgroundPosition: 'center',
        backgroundRepeat: 'no-repeat',
      }"
    />

    <audio
      v-if="cleanMusicUrl"
      id="invitation-music"
      :src="cleanMusicUrl"
      loop
      preload="auto"
      playsinline
      webkit-playsinline
      class="hidden"
    />

    <!-- COVER -->
    <Transition name="cover-fade">
      <CoverSection
        v-if="!isOpened"
        :wedding="wedding"
        :guest="guest"
        :coupleProfiles="coupleProfiles"
        :themeConfig="localThemeConfig"
        @open="openInvitation"
        class="fixed inset-0 z-50"
      />
    </Transition>

    <!-- MAIN CONTENT (Vertical Layout) -->
    <div v-if="isOpened" class="relative z-10 min-h-screen">
      
      <!-- Bottom Nav Control -->
      <nav
        class="fixed bottom-0 left-0 right-0 z-40 flex items-center justify-center gap-6 py-3 px-6 border-t backdrop-blur-md shadow-lg anim-fade-up"
        :style="{ backgroundColor: `${palette.background}ee`, borderColor: `${palette.secondary}44` }"
      >
        <button
          @click="goPrev"
          :disabled="currentIndex <= 0"
          class="flex h-10 w-10 items-center justify-center rounded-full border transition-all active:scale-95 disabled:opacity-30 disabled:cursor-not-allowed hover:opacity-80 shadow-sm"
          :style="{ backgroundColor: `${palette.secondary}20`, borderColor: `${palette.secondary}66`, color: palette.primary }"
          aria-label="Seksi Sebelumnya"
        >
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
          </svg>
        </button>
        
        <!-- Indicator context -->
        <span class="text-sm font-bold tracking-wider" :style="{ color: palette.primary }">{{ currentIndex + 1 }} / {{ activeSections.length }}</span>

        <button
          @click="goNext"
          :disabled="currentIndex >= activeSections.length - 1"
          class="flex h-10 w-10 items-center justify-center rounded-full border transition-all active:scale-95 disabled:opacity-30 disabled:cursor-not-allowed hover:opacity-80 shadow-sm"
          :style="{ backgroundColor: `${palette.secondary}20`, borderColor: `${palette.secondary}66`, color: palette.primary }"
          aria-label="Seksi Selanjutnya"
        >
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
          </svg>
        </button>
      </nav>

      <!-- Music Toggle Button (Floating above bottom nav) -->
      <button
        v-if="cleanMusicUrl"
        @click="toggleMusic"
        :aria-label="isMusicPlaying ? 'Pause musik' : 'Play musik'"
        class="music-btn fixed bottom-20 left-4 z-50 flex h-11 w-11 items-center justify-center rounded-full border shadow-lg backdrop-blur-md transition-all duration-200 active:scale-90 anim-zoom-in"
        :class="isMusicPlaying ? 'music-btn--playing' : ''"
        :style="{
          backgroundColor: `${palette.primary}20`,
          borderColor: `${palette.primary}66`,
          color: palette.secondary,
        }"
      >
        <!-- Playing state: animated equalizer bars, hover shows pause -->
        <span v-if="isMusicPlaying" class="music-btn__inner">
          <!-- Equalizer bars (shown by default when playing) -->
          <span class="music-btn__bars" aria-hidden="true">
            <span class="music-btn__bar" style="animation-delay: 0s" />
            <span class="music-btn__bar" style="animation-delay: 0.2s" />
            <span class="music-btn__bar" style="animation-delay: 0.1s" />
            <span class="music-btn__bar" style="animation-delay: 0.3s" />
          </span>
          <!-- Pause icon (shown on hover) -->
          <svg class="music-btn__pause h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
            <rect x="6" y="4" width="4" height="16" rx="1" />
            <rect x="14" y="4" width="4" height="16" rx="1" />
          </svg>
        </span>

        <!-- Paused / stopped state: play icon -->
        <svg v-else class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
          <circle cx="12" cy="12" r="11" fill="none" stroke="currentColor" stroke-width="1.5" opacity="0.6" />
          <!-- Segitiga play: center di (12,12), optically nudged +1px ke kanan -->
          <polygon points="10,8 10,16 17,12" />
        </svg>
      </button>

      <!-- Sections Container -->
      <div class="flex w-full max-w-[480px] mx-auto flex-col shadow-2xl relative pb-20">
        <section
          v-for="section in activeSections"
          :key="section.id"
          :id="`section-${section.id}`"
          class="relative w-full min-h-screen py-12 flex flex-col items-center justify-center overflow-x-clip"
        >
          <!-- Card Container with Overlaid Decorations -->
          <div class="relative w-[92%] max-w-[420px] mx-auto my-auto">
            <!-- White Frosted Card Content -->
            <div 
              class="relative z-10 w-full rounded-[2rem] shadow-xl backdrop-blur-md border border-white/20 overflow-hidden aos-item aos-zoom-in"
              :style="{ backgroundColor: 'rgba(255, 255, 255, 0.94)' }"
            >
              <component :is="section.component" v-bind="section.getProps()" class="w-full relative z-10" />
            </div>

            <!-- Content Decorations Overlay (IN FRONT of card, z-20, overflow-visible) -->
            <div class="pointer-events-none absolute inset-0 z-20 overflow-visible">
              <template v-for="slot in ['top_left', 'top_right', 'bottom_left', 'bottom_right']" :key="slot">
                <div
                  v-if="resolveAssetUrl(contentDecos[slot])"
                  :style="getContentDecorationStyle(slot, contentDecos[slot])"
                >
                  <div :class="animationClass(contentDecos[slot]?.animation)" class="w-full h-full flex items-center justify-center">
                    <img :src="resolveAssetUrl(contentDecos[slot])" class="max-h-full max-w-full h-auto w-auto object-contain" alt="" />
                  </div>
                </div>
              </template>
            </div>
          </div>
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

/* ── Music Button ── */
.music-btn {
  overflow: hidden;
}

/* Pulsing ring saat playing */
.music-btn--playing {
  box-shadow: 0 0 0 0 currentColor;
  animation: music-ring 2s ease-out infinite;
}
@keyframes music-ring {
  0%   { box-shadow: 0 0 0 0 currentColor; }
  70%  { box-shadow: 0 0 0 8px transparent; }
  100% { box-shadow: 0 0 0 0 transparent; }
}

/* Inner wrapper: stack bars & pause atas satu sama lain */
.music-btn__inner {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 1.25rem;
  height: 1.25rem;
}

/* ── Equalizer bars ── */
.music-btn__bars {
  display: flex;
  align-items: flex-end;
  gap: 2px;
  height: 1.1rem;
  transition: opacity 0.2s, transform 0.2s;
  opacity: 1;
  transform: scale(1);
}
.music-btn__bar {
  display: block;
  width: 3px;
  border-radius: 2px;
  background: currentColor;
  animation: eq-bar 0.8s ease-in-out infinite alternate;
}
.music-btn__bar:nth-child(1) { height: 40%; animation-duration: 0.7s; }
.music-btn__bar:nth-child(2) { height: 100%; animation-duration: 0.9s; }
.music-btn__bar:nth-child(3) { height: 60%; animation-duration: 0.65s; }
.music-btn__bar:nth-child(4) { height: 80%; animation-duration: 1.0s; }

@keyframes eq-bar {
  from { height: 20%; }
  to   { height: 100%; }
}

/* ── Pause icon ── */
.music-btn__pause {
  position: absolute;
  inset: 0;
  margin: auto;
  opacity: 0;
  transform: scale(0.6);
  transition: opacity 0.2s, transform 0.2s;
  pointer-events: none;
}

/* Hover: sembunyikan bars, tampilkan pause */
.music-btn--playing:hover .music-btn__bars {
  opacity: 0;
  transform: scale(0.6);
}
.music-btn--playing:hover .music-btn__pause {
  opacity: 1;
  transform: scale(1);
}

/* ── Hide scrollbar for invitation page ── */
:global(html),
:global(body) {
  scrollbar-width: none !important;
  -ms-overflow-style: none !important;
}

:global(html::-webkit-scrollbar),
:global(body::-webkit-scrollbar) {
  display: none !important;
  width: 0 !important;
  height: 0 !important;
}
</style>
