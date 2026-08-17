<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

defineProps({
    canLogin: {
        type: Boolean,
        default: true,
    },
    canRegister: {
        type: Boolean,
        default: true,
    },
});

// --- Interactive Phone Mockup State ---
const activeMockupTab = ref('cover');
const isAudioPlaying = ref(false);

const mockupScreens = [
    { id: 'cover', label: 'Cover & Bismillah', icon: '✨' },
    { id: 'verse', label: 'Ayat Suci', icon: '📖' },
    { id: 'couple', label: 'Mempelai & Acara', icon: '💍' },
    { id: 'rsvp', label: 'RSVP & Pax Quota', icon: '📝' },
    { id: 'gift', label: 'Amplop & Doa', icon: '🎁' },
];

// --- Interactive Catering Calculator State ---
const guestCount = ref(350);
const pricePerPax = ref(65000);

const withoutSystemCost = computed(() => {
    // Typical unverified event: +25% overcatering buffer to prevent food shortage
    const unverifiedPax = Math.round(guestCount.value * 1.25);
    return unverifiedPax * pricePerPax.value;
});

const withSystemCost = computed(() => {
    // WalimYuk verified RSVP with strict max_pax token + 10% safety buffer
    const verifiedPax = Math.round(guestCount.value * 1.10);
    return verifiedPax * pricePerPax.value;
});

const estimatedSavings = computed(() => {
    return Math.max(0, withoutSystemCost.value - withSystemCost.value);
});

const formatCurrency = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val);
};

// --- Interactive Theme Switcher State ---
const activeThemeKey = ref('emerald');
const themes = [
    {
        id: 'emerald',
        name: 'Emerald Sage',
        subtitle: 'Islami, Teduh & Elegan',
        bg: 'from-emerald-900 to-emerald-950',
        cardBg: 'bg-emerald-800/40 border-emerald-600/30',
        accent: '#34d399',
        tag: 'Terfavorit',
        swatches: ['#065f46', '#34d399', '#f5efcc'],
    },
    {
        id: 'navy',
        name: 'Royal Navy & Gold',
        subtitle: 'Mewah & Berkelas',
        bg: 'from-slate-900 to-indigo-950',
        cardBg: 'bg-indigo-900/40 border-amber-500/30',
        accent: '#f59e0b',
        tag: 'Eksklusif',
        swatches: ['#0f172a', '#f59e0b', '#e2e8f0'],
    },
    {
        id: 'rose',
        name: 'Rose Champagne',
        subtitle: 'Romantis & Lembut',
        bg: 'from-rose-950 to-pink-950',
        cardBg: 'bg-rose-900/40 border-rose-400/30',
        accent: '#f472b6',
        tag: 'Populer',
        swatches: ['#881337', '#f472b6', '#fff1f2'],
    },
    {
        id: 'terracotta',
        name: 'Terracotta Earth',
        subtitle: 'Rustic & Hangat',
        bg: 'from-amber-950 to-stone-900',
        cardBg: 'bg-amber-900/40 border-amber-500/30',
        accent: '#fb923c',
        tag: 'Modern',
        swatches: ['#7c2d12', '#fb923c', '#fef3c7'],
    },
];

// --- Interactive FAQ Accordion State ---
const openFaqIndex = ref(0);
const faqs = [
    {
        q: 'Bagaimana cara WalimYuk mencegah tamu membawa rombongan berlebih?',
        a: 'Setiap tamu undangan mendapatkan link tautan unik dengan token khusus (contoh: /invitation/walimah-farhan-aisyah/tamu-token). Di link tersebut, pengantin menentukan batas maksimal pax (misal: "Khusus untuk 2 Pax"). Saat mengisi RSVP, form terkunci maksimal 2 orang dan tidak bisa diubah melebihi kuota.',
    },
    {
        q: 'Apakah tamu undangan perlu mengunduh aplikasi tertentu?',
        a: 'Sama sekali tidak! Undangan terbuka langsung di browser smartphone tamu dengan tampilan responsif, animasi swipe halus layaknya mobile application, dan pemutar audio otomatis yang syar\'i.',
    },
    {
        q: 'Bagaimana cara mendistribusikan tautan undangan ke tamu?',
        a: 'Sangat mudah. Dari Dashboard WalimYuk, Anda dapat mengklik tombol "Salin Pesan & Link WhatsApp" untuk setiap tamu secara instan, lengkap dengan template pesan sapaan personal.',
    },
    {
        q: 'Bagaimana sistem merekomendasikan jumlah katering prasmanan?',
        a: 'Dashboard WalimYuk secara otomatis merekap total tamu yang konfirmasi "Hadir", mengalikan dengan jumlah pax terdaftar, lalu memberikan perhitungan rekomendasi porsi aman (+10% safety buffer) agar tidak overcatering maupun kekurangan makanan.',
    },
    {
        q: 'Apakah saya bisa memoderasi doa & ucapan yang masuk?',
        a: 'Tentu saja. Anda memiliki kendali penuh di Dashboard untuk menyetujui, menyembunyikan, atau menghapus ucapan tamu sebelum ditampilkan pada dinding doa publik undangan.',
    },
    {
        q: 'Berapa lama undangan digital saya aktif?',
        a: 'Undangan Anda akan aktif selamanya sebagai kenang-kenangan digital walimah Anda tanpa batas kedaluwarsa.',
    },
];

const toggleFaq = (index) => {
    openFaqIndex.value = openFaqIndex.value === index ? null : index;
};
</script>

<template>
    <Head title="WalimYuk - Undangan Digital Islami & Perencanaan Katering Akurat" />

    <div class="min-h-screen bg-[#fcfaf6] text-stone-800 antialiased selection:bg-emerald-600 selection:text-white dark:bg-stone-950 dark:text-stone-100">
        <!-- TOP STICKY NAVBAR -->
        <header class="sticky top-0 z-50 border-b border-stone-200/80 bg-[#fcfaf6]/90 backdrop-blur-md dark:border-stone-800/80 dark:bg-stone-950/90">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3.5 sm:px-6 lg:px-8">
                <!-- Brand Logo -->
                <Link :href="route('home')" class="flex items-center gap-2">
                    <ApplicationLogo :with-text="true" />
                </Link>

                <!-- Navigation Links (Desktop) -->
                <nav class="hidden items-center gap-8 text-sm font-medium text-stone-600 md:flex dark:text-stone-300">
                    <a href="#fitur" class="transition hover:text-emerald-700 dark:hover:text-emerald-400">Keunggulan</a>
                    <a href="#kalkulator" class="transition hover:text-emerald-700 dark:hover:text-emerald-400">Kalkulator Pax</a>
                    <a href="#demo" class="transition hover:text-emerald-700 dark:hover:text-emerald-400">Demo Interaktif</a>
                    <a href="#tema" class="transition hover:text-emerald-700 dark:hover:text-emerald-400">Pilihan Tema</a>
                    <a href="#harga" class="transition hover:text-emerald-700 dark:hover:text-emerald-400">Harga Paket</a>
                    <a href="#faq" class="transition hover:text-emerald-700 dark:hover:text-emerald-400">FAQ</a>
                </nav>

                <!-- Action Buttons -->
                <div class="flex items-center gap-3">
                    <template v-if="$page.props.auth?.user">
                        <Link
                            :href="route('dashboard')"
                            class="inline-flex items-center gap-2 rounded-full bg-emerald-800 px-5 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2"
                        >
                            <span>Dashboard Pengantin</span>
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            v-if="canLogin"
                            :href="route('login')"
                            class="hidden rounded-full px-4 py-2 text-xs font-semibold text-stone-700 transition hover:text-emerald-800 sm:inline-block dark:text-stone-300 dark:hover:text-white"
                        >
                            Masuk
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="inline-flex items-center gap-1.5 rounded-full bg-gradient-to-r from-emerald-800 to-emerald-700 px-5 py-2 text-xs font-semibold text-white shadow-md shadow-emerald-900/10 transition hover:from-emerald-750 hover:to-emerald-650 focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2"
                        >
                            <span>Buat Undangan Gratis</span>
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <!-- HERO SECTION -->
        <section class="relative overflow-hidden pt-12 pb-20 sm:pt-16 sm:pb-28">
            <!-- Background Decorative Blobs -->
            <div class="pointer-events-none absolute -top-40 left-1/2 -z-10 h-[500px] w-[800px] -translate-x-1/2 rounded-full bg-gradient-to-tr from-emerald-100/70 via-amber-100/50 to-emerald-50/20 blur-3xl dark:from-emerald-950/30 dark:via-stone-900 dark:to-emerald-900/20"></div>

            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid items-center gap-12 lg:grid-cols-12 lg:gap-8">
                    <!-- Left Hero Copy -->
                    <div class="text-center lg:col-span-7 lg:text-left">
                        <!-- Badge -->
                        <div class="inline-flex items-center gap-2 rounded-full border border-emerald-300/60 bg-emerald-50/80 px-4 py-1.5 text-xs font-semibold text-emerald-800 shadow-sm backdrop-blur-sm dark:border-emerald-700/60 dark:bg-emerald-950/60 dark:text-emerald-300">
                            <span class="flex h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Sistem Undangan Digital Walimah #1 Anti Overcatering</span>
                        </div>

                        <!-- Main Headline -->
                        <h1 class="mt-6 font-serif text-4xl font-bold tracking-tight text-stone-900 sm:text-5xl lg:text-6xl dark:text-white">
                            Undangan Digital Islami &amp; <br class="hidden sm:inline" />
                            <span class="bg-gradient-to-r from-emerald-800 via-emerald-700 to-amber-600 bg-clip-text text-transparent dark:from-emerald-400 dark:via-emerald-300 dark:to-amber-400">
                                Katering Presisi Tanpa Boncos
                            </span>
                        </h1>

                        <!-- Subtitle -->
                        <p class="mt-6 text-base leading-relaxed text-stone-600 sm:text-lg dark:text-stone-300">
                            Hadirkan pengalaman walimah syar'i yang berkesan dengan animasi mobile horizontal swipe, ayat Al-Qur'an, dan <strong>kuota pax tamu terkunci</strong> untuk kepastian porsi katering prasmanan yang pas.
                        </p>

                        <!-- Key Benefits Pills -->
                        <div class="mt-6 flex flex-wrap items-center justify-center gap-3 text-xs font-medium text-stone-700 lg:justify-start dark:text-stone-300">
                            <div class="flex items-center gap-1.5 rounded-lg bg-stone-100 px-3 py-1.5 dark:bg-stone-900">
                                <span class="text-emerald-600">✓</span> Kuota Pax per Link Tamu
                            </div>
                            <div class="flex items-center gap-1.5 rounded-lg bg-stone-100 px-3 py-1.5 dark:bg-stone-900">
                                <span class="text-emerald-600">✓</span> Estimasi Porsi Otomatis
                            </div>
                            <div class="flex items-center gap-1.5 rounded-lg bg-stone-100 px-3 py-1.5 dark:bg-stone-900">
                                <span class="text-emerald-600">✓</span> Animasi Mobile App-Like
                            </div>
                        </div>

                        <!-- CTA Group -->
                        <div class="mt-8 flex flex-col items-center justify-center gap-3.5 sm:flex-row lg:justify-start">
                            <Link
                                :href="route('register')"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-800 to-emerald-700 px-8 py-4 text-sm font-bold text-white shadow-lg shadow-emerald-900/20 transition hover:from-emerald-750 hover:to-emerald-650 hover:shadow-xl sm:w-auto"
                            >
                                <span>Buat Undangan Saya Sekarang</span>
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </Link>

                            <a
                                href="#demo"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-stone-300 bg-white px-7 py-4 text-sm font-semibold text-stone-700 shadow-sm transition hover:border-emerald-600 hover:text-emerald-700 sm:w-auto dark:border-stone-700 dark:bg-stone-900 dark:text-stone-200 dark:hover:border-emerald-500"
                            >
                                <svg class="h-4 w-4 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                <span>Lihat Demo Interaktif</span>
                            </a>
                        </div>

                        <!-- Social Proof Footnote -->
                        <div class="mt-8 flex items-center justify-center gap-3 text-xs text-stone-500 lg:justify-start dark:text-stone-400">
                            <div class="flex -space-x-2">
                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-600 text-[10px] font-bold text-white ring-2 ring-white dark:ring-stone-900">FA</div>
                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-amber-600 text-[10px] font-bold text-white ring-2 ring-white dark:ring-stone-900">RH</div>
                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-teal-700 text-[10px] font-bold text-white ring-2 ring-white dark:ring-stone-900">AN</div>
                            </div>
                            <span>Dipercaya oleh <strong>1.200+ pasangan</strong> pengantin muslim di Indonesia</span>
                        </div>
                    </div>

                    <!-- Right Hero Visual: Phone Mockup Simulation -->
                    <div class="lg:col-span-5">
                        <div class="relative mx-auto max-w-[340px] rounded-[44px] border-[10px] border-stone-900 bg-stone-900 p-2 shadow-2xl ring-1 ring-stone-900/50 sm:max-w-[360px]">
                            <!-- Speaker / Camera Notch -->
                            <div class="absolute left-1/2 top-4 z-30 h-4 w-28 -translate-x-1/2 rounded-full bg-stone-800"></div>

                            <!-- Screen Container -->
                            <div class="relative min-h-[580px] overflow-hidden rounded-[34px] bg-gradient-to-b from-emerald-950 via-emerald-900 to-emerald-950 text-white shadow-inner">
                                <!-- Top Bar -->
                                <div class="flex items-center justify-between px-6 pt-5 pb-3 text-xs text-emerald-200/80">
                                    <span class="font-semibold text-amber-300">Walimah An-Nur</span>
                                    <!-- Audio Player Widget Simulation -->
                                    <button
                                        @click="isAudioPlaying = !isAudioPlaying"
                                        class="flex items-center gap-1 rounded-full bg-emerald-800/80 px-2.5 py-1 text-[10px] text-amber-300 shadow backdrop-blur transition hover:bg-emerald-700"
                                    >
                                        <span :class="{ 'animate-spin': isAudioPlaying }">🎵</span>
                                        <span>{{ isAudioPlaying ? 'Mute' : 'Play Music' }}</span>
                                    </button>
                                </div>

                                <!-- Screen Content based on Active Mockup Tab -->
                                <div class="px-5 py-4 text-center">
                                    <!-- TAB 1: COVER -->
                                    <div v-if="activeMockupTab === 'cover'" class="space-y-4 animate-fadeIn">
                                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full border border-amber-400/30 bg-amber-400/10 text-xl text-amber-300">
                                            ✨
                                        </div>
                                        <p class="font-serif text-xs uppercase tracking-widest text-amber-300">The Wedding Of</p>
                                        <h3 class="font-serif text-3xl font-bold tracking-wide text-white">Farhan &amp; Aisyah</h3>
                                        <p class="text-xs text-emerald-200">Sabtu, 24 Oktober 2026</p>

                                        <div class="mt-6 rounded-2xl border border-white/10 bg-white/10 p-4 text-left backdrop-blur-md">
                                            <p class="text-[11px] text-emerald-200">Kepada Yth. Tamu Undangan:</p>
                                            <p class="mt-1 font-serif text-base font-bold text-amber-300">Ahmad Zaki &amp; Partner</p>
                                            <div class="mt-2 inline-flex items-center gap-1 rounded-full bg-emerald-800/80 px-2.5 py-0.5 text-[10px] font-semibold text-emerald-200">
                                                <span>🔒 Kuota Undangan: Maks 2 Orang</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- TAB 2: AYAT -->
                                    <div v-else-if="activeMockupTab === 'verse'" class="space-y-3 animate-fadeIn">
                                        <div class="text-2xl text-amber-300">﷽</div>
                                        <p class="font-serif text-xs font-semibold text-amber-300">QS. Ar-Rum: 21</p>
                                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4 text-xs leading-relaxed text-emerald-100 backdrop-blur-md">
                                            "Dan di antara tanda-tanda (kebesaran)-Nya ialah Dia menciptakan pasangan-pasangan untukmu dari jenismu sendiri, agar kamu cenderung dan merasa tenteram kepadanya..."
                                        </div>
                                    </div>

                                    <!-- TAB 3: COUPLE -->
                                    <div v-else-if="activeMockupTab === 'couple'" class="space-y-3 animate-fadeIn">
                                        <div class="rounded-2xl border border-white/10 bg-white/10 p-3.5 backdrop-blur-md">
                                            <p class="font-serif text-sm font-bold text-amber-300">Farhan Al-Ghazi, S.T.</p>
                                            <p class="text-[10px] text-emerald-200">Putra pertama dari Bpk. Ir. H. Bambang &amp; Ibu Hj. Siti</p>
                                        </div>
                                        <div class="text-amber-400 font-serif text-xs">&amp;</div>
                                        <div class="rounded-2xl border border-white/10 bg-white/10 p-3.5 backdrop-blur-md">
                                            <p class="font-serif text-sm font-bold text-amber-300">Aisyah Nurul Izzah, S.Farm.</p>
                                            <p class="text-[10px] text-emerald-200">Putri kedua dari Bpk. Dr. H. Rahman &amp; Ibu Hj. Maryam</p>
                                        </div>
                                    </div>

                                    <!-- TAB 4: RSVP -->
                                    <div v-else-if="activeMockupTab === 'rsvp'" class="space-y-3 text-left animate-fadeIn">
                                        <div class="rounded-2xl border border-amber-400/30 bg-emerald-900/70 p-4 backdrop-blur-md">
                                            <div class="flex items-center justify-between">
                                                <p class="text-xs font-bold text-white">Konfirmasi Kehadiran</p>
                                                <span class="rounded bg-amber-400/20 px-1.5 py-0.5 text-[9px] font-bold text-amber-300">Terkunci: Maks 2 Pax</span>
                                            </div>

                                            <div class="mt-3 grid grid-cols-2 gap-2">
                                                <button class="rounded-xl border border-amber-400 bg-amber-400/20 py-2 text-center text-xs font-bold text-amber-300">
                                                    ✓ Hadir
                                                </button>
                                                <button class="rounded-xl border border-white/10 bg-white/5 py-2 text-center text-xs font-medium text-stone-300">
                                                    Maaf Tidak Hadir
                                                </button>
                                            </div>

                                            <div class="mt-3">
                                                <label class="text-[10px] text-emerald-200">Jumlah Orang (Max 2):</label>
                                                <div class="mt-1 flex items-center justify-between rounded-xl border border-white/20 bg-black/20 px-3 py-1.5 text-xs text-white">
                                                    <span>2 Orang (Lengkap)</span>
                                                    <span class="text-emerald-400">✓ Valid</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- TAB 5: GIFT & WISHES -->
                                    <div v-else-if="activeMockupTab === 'gift'" class="space-y-3 text-left animate-fadeIn">
                                        <div class="rounded-2xl border border-white/10 bg-white/10 p-3.5 backdrop-blur-md">
                                            <p class="text-xs font-bold text-amber-300">💌 Tanda Kasih Digital</p>
                                            <div class="mt-2 flex items-center justify-between rounded-xl bg-black/20 p-2.5 text-xs">
                                                <div>
                                                    <p class="font-bold text-white">Bank Syariah Indonesia (BSI)</p>
                                                    <p class="text-[10px] text-emerald-300">7123-4567-89 a.n Farhan</p>
                                                </div>
                                                <button class="rounded-lg bg-amber-400 px-2 py-1 text-[10px] font-bold text-stone-900">Salin</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Interactive Navigation Bar inside Phone -->
                                <div class="absolute bottom-4 left-4 right-4 z-20 flex items-center justify-around rounded-2xl border border-white/10 bg-black/40 p-1.5 backdrop-blur-md">
                                    <button
                                        v-for="screen in mockupScreens"
                                        :key="screen.id"
                                        @click="activeMockupTab = screen.id"
                                        class="flex flex-col items-center gap-0.5 rounded-xl p-1.5 text-[10px] transition"
                                        :class="activeMockupTab === screen.id ? 'bg-amber-400/20 text-amber-300 font-bold' : 'text-stone-400 hover:text-stone-200'"
                                    >
                                        <span>{{ screen.icon }}</span>
                                        <span class="text-[8px]">{{ screen.id.toUpperCase() }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- PROBLEM VS SOLUTION SECTION -->
        <section id="fitur" class="border-y border-stone-200/80 bg-white py-20 dark:border-stone-800 dark:bg-stone-900/50">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-3xl text-center">
                    <div class="inline-flex items-center gap-2 rounded-full bg-red-100 px-3.5 py-1 text-xs font-semibold text-red-700 dark:bg-red-950/60 dark:text-red-300">
                        <span>⚠️</span> Masalah Klasik Walimah &amp; Katering
                    </div>
                    <h2 class="mt-4 font-serif text-3xl font-bold tracking-tight text-stone-900 sm:text-4xl dark:text-white">
                        Mengapa 78% Pengantin Mengalami Pemborosan Katering?
                    </h2>
                    <p class="mt-4 text-sm leading-relaxed text-stone-600 sm:text-base dark:text-stone-300">
                        Link undangan konvensional yang disebar bebas tanpa token kuota membuat tamu membawa banyak rombongan tak terduga atau tidak hadir tanpa konfirmasi.
                    </p>
                </div>

                <div class="mt-14 grid gap-8 md:grid-cols-2">
                    <!-- The Old Way (Pain Point) -->
                    <div class="rounded-3xl border border-red-200 bg-red-50/40 p-8 dark:border-red-900/50 dark:bg-red-950/20">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-red-500 text-white font-bold">✕</div>
                            <div>
                                <h3 class="font-serif text-xl font-bold text-stone-900 dark:text-white">Undangan Biasa / Konvensional</h3>
                                <p class="text-xs text-red-700 dark:text-red-400">Estimasi Asal-asalan &amp; Risiko Boncos</p>
                            </div>
                        </div>

                        <ul class="mt-6 space-y-3.5 text-sm text-stone-600 dark:text-stone-300">
                            <li class="flex items-start gap-2.5">
                                <span class="text-red-500 font-bold">✕</span>
                                <span>1 Link disebar untuk semua orang; siapapun bisa datang membawa rombongan tak terduga.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="text-red-500 font-bold">✕</span>
                                <span>Form RSVP bebas memasukkan angka pax berapapun tanpa batasan kuota.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="text-red-500 font-bold">✕</span>
                                <span>Pengantin terpaksa over-order porsi katering hingga 30% untuk antisipasi kekurangan.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="text-red-500 font-bold">✕</span>
                                <span>Jutaan rupiah anggaran pernikahan terbuang percuma untuk makanan sisa prasmanan.</span>
                            </li>
                        </ul>
                    </div>

                    <!-- The WalimYuk Way (Solution) -->
                    <div class="rounded-3xl border border-emerald-300 bg-emerald-50/50 p-8 shadow-lg shadow-emerald-950/5 dark:border-emerald-700/60 dark:bg-emerald-950/30">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-700 text-amber-300 font-bold">✓</div>
                            <div>
                                <h3 class="font-serif text-xl font-bold text-stone-900 dark:text-white">Solusi Cerdas WalimYuk</h3>
                                <p class="text-xs text-emerald-700 dark:text-emerald-300">Sistem Token Pax Kuota Terkunci</p>
                            </div>
                        </div>

                        <ul class="mt-6 space-y-3.5 text-sm text-stone-700 dark:text-stone-200">
                            <li class="flex items-start gap-2.5">
                                <span class="text-emerald-700 font-bold dark:text-emerald-400">✓</span>
                                <span><strong>Token Tautan Unik:</strong> Setiap tamu mendapat tautan berdedikasi dengan nama &amp; kuota tertulis.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="text-emerald-700 font-bold dark:text-emerald-400">✓</span>
                                <span><strong>Strict Quota Validation:</strong> Form terkunci otomatis (1 hingga max_pax), mencegah manipulasi.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="text-emerald-700 font-bold dark:text-emerald-400">✓</span>
                                <span><strong>Kalkulasi Otomatis + Buffer:</strong> Rekomendasi porsi katering presisi dihitung real-time.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="text-emerald-700 font-bold dark:text-emerald-400">✓</span>
                                <span><strong>Hemat Jutaan Rupiah:</strong> Anggaran dialihkan untuk tabungan masa depan rumah tangga.</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- INTERACTIVE CATERING CALCULATOR -->
        <section id="kalkulator" class="py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-3xl text-center">
                    <div class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-3.5 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                        <span>📊</span> Simulasi Penghematan Nyata
                    </div>
                    <h2 class="mt-4 font-serif text-3xl font-bold tracking-tight text-stone-900 sm:text-4xl dark:text-white">
                        Kalkulator Anggaran Katering Walimah
                    </h2>
                    <p class="mt-4 text-sm text-stone-600 sm:text-base dark:text-stone-300">
                        Geser slider di bawah ini untuk melihat berapa rupiah yang bisa Anda hemat dengan sistem RSVP terkunci WalimYuk.
                    </p>
                </div>

                <div class="mt-12 rounded-3xl border border-stone-200 bg-white p-6 shadow-xl shadow-stone-200/50 sm:p-10 lg:p-12 dark:border-stone-800 dark:bg-stone-900 dark:shadow-none">
                    <div class="grid gap-10 lg:grid-cols-12 lg:items-center">
                        <!-- Sliders Control (Col 6) -->
                        <div class="space-y-8 lg:col-span-6">
                            <!-- Slider 1: Guest Count -->
                            <div>
                                <div class="flex items-center justify-between">
                                    <label class="text-sm font-semibold text-stone-800 dark:text-stone-200">
                                        Perkiraan Jumlah Undangan Tamu
                                    </label>
                                    <span class="rounded-lg bg-emerald-100 px-3 py-1 text-sm font-bold text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                        {{ guestCount }} Tamu
                                    </span>
                                </div>
                                <input
                                    type="range"
                                    min="100"
                                    max="1000"
                                    step="25"
                                    v-model.number="guestCount"
                                    class="mt-4 h-2.5 w-full cursor-pointer appearance-none rounded-lg bg-stone-200 accent-emerald-700 dark:bg-stone-700"
                                />
                                <div class="mt-2 flex justify-between text-xs text-stone-400">
                                    <span>100 Tamu</span>
                                    <span>500 Tamu</span>
                                    <span>1000 Tamu</span>
                                </div>
                            </div>

                            <!-- Slider 2: Price per Pax -->
                            <div>
                                <div class="flex items-center justify-between">
                                    <label class="text-sm font-semibold text-stone-800 dark:text-stone-200">
                                        Harga Paket Katering per Pax
                                    </label>
                                    <span class="rounded-lg bg-amber-100 px-3 py-1 text-sm font-bold text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                                        {{ formatCurrency(pricePerPax) }}
                                    </span>
                                </div>
                                <input
                                    type="range"
                                    min="35000"
                                    max="150000"
                                    step="5000"
                                    v-model.number="pricePerPax"
                                    class="mt-4 h-2.5 w-full cursor-pointer appearance-none rounded-lg bg-stone-200 accent-amber-600 dark:bg-stone-700"
                                />
                                <div class="mt-2 flex justify-between text-xs text-stone-400">
                                    <span>Rp 35.000</span>
                                    <span>Rp 85.000</span>
                                    <span>Rp 150.000</span>
                                </div>
                            </div>
                        </div>

                        <!-- Savings Result Card (Col 6) -->
                        <div class="rounded-2xl bg-gradient-to-br from-emerald-950 via-emerald-900 to-emerald-850 p-6 text-white shadow-lg lg:col-span-6 sm:p-8">
                            <p class="text-xs font-semibold uppercase tracking-wider text-amber-300">Estimasi Penghematan Anggaran Katering</p>
                            <div class="mt-3 font-serif text-3xl font-bold text-amber-300 sm:text-4xl">
                                {{ formatCurrency(estimatedSavings) }}
                            </div>
                            <p class="mt-2 text-xs text-emerald-200">
                                Berdasarkan pencegahan +15% overcatering liar dari tamu tak terdaftar.
                            </p>

                            <div class="mt-6 space-y-3 border-t border-emerald-700/60 pt-5 text-xs text-emerald-100">
                                <div class="flex items-center justify-between">
                                    <span class="text-stone-300">Estimasi Biaya Katering Tradisional:</span>
                                    <span class="font-bold text-red-300 line-through">{{ formatCurrency(withoutSystemCost) }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-stone-300">Estimasi Biaya dengan WalimYuk:</span>
                                    <span class="font-bold text-emerald-300">{{ formatCurrency(withSystemCost) }}</span>
                                </div>
                            </div>

                            <div class="mt-6">
                                <Link
                                    :href="route('register')"
                                    class="block w-full rounded-xl bg-amber-400 py-3 text-center text-xs font-bold text-stone-950 transition hover:bg-amber-300"
                                >
                                    Amankan Anggaran Walimah Saya &rarr;
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CORE FEATURES SHOWCASE -->
        <section id="demo" class="border-y border-stone-200/80 bg-white py-20 dark:border-stone-800 dark:bg-stone-900/50">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-3xl text-center">
                    <div class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3.5 py-1 text-xs font-semibold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                        <span>⚡</span> Fitur Terlengkap Mini-SaaS
                    </div>
                    <h2 class="mt-4 font-serif text-3xl font-bold tracking-tight text-stone-900 sm:text-4xl dark:text-white">
                        Didesain Khusus untuk Keberkahan Pernikahan
                    </h2>
                    <p class="mt-4 text-sm text-stone-600 sm:text-base dark:text-stone-300">
                        Setiap detail fitur dirancang untuk kemudahan calon pengantin dan kenyamanan para tamu undangan.
                    </p>
                </div>

                <div class="mt-14 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    <!-- Feature 1 -->
                    <div class="rounded-3xl border border-stone-200/80 bg-[#fdfbf7] p-7 transition hover:border-emerald-600 hover:shadow-lg dark:border-stone-800 dark:bg-stone-900">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-2xl text-emerald-800 dark:bg-emerald-950 dark:text-emerald-400">
                            📱
                        </div>
                        <h3 class="mt-5 font-serif text-lg font-bold text-stone-900 dark:text-white">Mobile Snap-Scroll Experience</h3>
                        <p class="mt-2 text-xs leading-relaxed text-stone-600 dark:text-stone-400">
                            Tampilan swipe horizontal layaknya reels atau aplikasi mobile native tanpa lag, nyaman dibuka di semua ukuran layar smartphone.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="rounded-3xl border border-stone-200/80 bg-[#fdfbf7] p-7 transition hover:border-emerald-600 hover:shadow-lg dark:border-stone-800 dark:bg-stone-900">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-2xl text-emerald-800 dark:bg-emerald-950 dark:text-emerald-400">
                            🔒
                        </div>
                        <h3 class="mt-5 font-serif text-lg font-bold text-stone-900 dark:text-white">Strict Pax Quota per Tamu</h3>
                        <p class="mt-2 text-xs leading-relaxed text-stone-600 dark:text-stone-400">
                            Kunci kuota kehadiran (1, 2, atau custom) untuk setiap tamu. Formulir menolak otomatis jika input melebihi jatah.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="rounded-3xl border border-stone-200/80 bg-[#fdfbf7] p-7 transition hover:border-emerald-600 hover:shadow-lg dark:border-stone-800 dark:bg-stone-900">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-2xl text-emerald-800 dark:bg-emerald-950 dark:text-emerald-400">
                            🎨
                        </div>
                        <h3 class="mt-5 font-serif text-lg font-bold text-stone-900 dark:text-white">Live Theme &amp; Color Customizer</h3>
                        <p class="mt-2 text-xs leading-relaxed text-stone-600 dark:text-stone-400">
                            Ubah tema warna primer, aksen, font kaligrafi, dan atur urutan seksi undangan secara instan melalui dashboard builder.
                        </p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="rounded-3xl border border-stone-200/80 bg-[#fdfbf7] p-7 transition hover:border-emerald-600 hover:shadow-lg dark:border-stone-800 dark:bg-stone-900">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-2xl text-emerald-800 dark:bg-emerald-950 dark:text-emerald-400">
                            💬
                        </div>
                        <h3 class="mt-5 font-serif text-lg font-bold text-stone-900 dark:text-white">Dinding Doa dengan Moderasi</h3>
                        <p class="mt-2 text-xs leading-relaxed text-stone-600 dark:text-stone-400">
                            Tampung doa tulus dari keluarga dan sahabat. Pengantin memiliki hak penuh untuk menyetujui ucapan sebelum tayang.
                        </p>
                    </div>

                    <!-- Feature 5 -->
                    <div class="rounded-3xl border border-stone-200/80 bg-[#fdfbf7] p-7 transition hover:border-emerald-600 hover:shadow-lg dark:border-stone-800 dark:bg-stone-900">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-2xl text-emerald-800 dark:bg-emerald-950 dark:text-emerald-400">
                            🎁
                        </div>
                        <h3 class="mt-5 font-serif text-lg font-bold text-stone-900 dark:text-white">Amplop Digital &amp; QR Navigasi</h3>
                        <p class="mt-2 text-xs leading-relaxed text-stone-600 dark:text-stone-400">
                            Rekening bank syariah, QRIS, dan alamat kirim kado fisik dengan tombol satu-klik salin dan Google Maps terintegrasi.
                        </p>
                    </div>

                    <!-- Feature 6 -->
                    <div class="rounded-3xl border border-stone-200/80 bg-[#fdfbf7] p-7 transition hover:border-emerald-600 hover:shadow-lg dark:border-stone-800 dark:bg-stone-900">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-2xl text-emerald-800 dark:bg-emerald-950 dark:text-emerald-400">
                            📊
                        </div>
                        <h3 class="mt-5 font-serif text-lg font-bold text-stone-900 dark:text-white">Ekspor Data Rekap Tamu Excel</h3>
                        <p class="mt-2 text-xs leading-relaxed text-stone-600 dark:text-stone-400">
                            Unduh seluruh daftar tamu terkonfirmasi hadir, jumlah pax, dan status ucapan ke format Excel untuk diserahkan ke vendor katering.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- THEME GALLERY SECTION -->
        <section id="tema" class="py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-3xl text-center">
                    <div class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-3.5 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                        <span>✨</span> Palet Warna Premium
                    </div>
                    <h2 class="mt-4 font-serif text-3xl font-bold tracking-tight text-stone-900 sm:text-4xl dark:text-white">
                        Pilihan Tema Sesuai Selera Anda
                    </h2>
                    <p class="mt-4 text-sm text-stone-600 sm:text-base dark:text-stone-300">
                        Klik tema warna di bawah ini untuk melihat contoh preview nuansa kartu undangan.
                    </p>
                </div>

                <!-- Theme Selector Tabs -->
                <div class="mt-10 flex flex-wrap items-center justify-center gap-3">
                    <button
                        v-for="t in themes"
                        :key="t.id"
                        @click="activeThemeKey = t.id"
                        class="flex items-center gap-2 rounded-full px-5 py-2.5 text-xs font-bold transition"
                        :class="activeThemeKey === t.id ? 'bg-emerald-800 text-white shadow-md' : 'bg-white text-stone-700 border border-stone-200 hover:border-emerald-600 dark:bg-stone-900 dark:border-stone-800 dark:text-stone-300'"
                    >
                        <div class="flex -space-x-1">
                            <span v-for="(hex, i) in t.swatches" :key="i" class="h-3 w-3 rounded-full ring-1 ring-white" :style="{ backgroundColor: hex }"></span>
                        </div>
                        <span>{{ t.name }}</span>
                    </button>
                </div>

                <!-- Theme Preview Card -->
                <div class="mx-auto mt-10 max-w-4xl overflow-hidden rounded-3xl border border-stone-200 bg-white p-6 shadow-xl sm:p-10 dark:border-stone-800 dark:bg-stone-900">
                    <div
                        v-for="t in themes"
                        :key="t.id"
                        v-show="activeThemeKey === t.id"
                        class="grid items-center gap-8 md:grid-cols-12"
                    >
                        <div class="md:col-span-7">
                            <span class="rounded-full bg-amber-400/20 px-3 py-1 text-xs font-bold text-amber-700 dark:text-amber-300">{{ t.tag }}</span>
                            <h3 class="mt-3 font-serif text-2xl font-bold text-stone-900 sm:text-3xl dark:text-white">{{ t.name }}</h3>
                            <p class="mt-2 text-sm text-stone-600 dark:text-stone-300">{{ t.subtitle }}</p>

                            <p class="mt-4 text-xs leading-relaxed text-stone-500 dark:text-stone-400">
                                Sangat serasi untuk konsep pernikahan outdoor taman, gedung adat, maupun intimate syar'i walimah. Disesuaikan dengan tone warna busana pengantin.
                            </p>

                            <div class="mt-6 flex items-center gap-3">
                                <Link
                                    :href="route('register')"
                                    class="rounded-xl bg-emerald-800 px-5 py-2.5 text-xs font-bold text-white transition hover:bg-emerald-700"
                                >
                                    Gunakan Tema Ini &rarr;
                                </Link>
                            </div>
                        </div>

                        <!-- Card Preview Sample -->
                        <div class="md:col-span-5">
                            <div class="rounded-2xl bg-gradient-to-br p-6 text-center text-white shadow-lg" :class="t.bg">
                                <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full border border-white/20 bg-white/10 text-sm">
                                    💍
                                </div>
                                <p class="mt-3 font-serif text-xs uppercase tracking-widest text-amber-300">Walimatul 'Ursy</p>
                                <p class="mt-1 font-serif text-xl font-bold text-white">Farhan &amp; Aisyah</p>
                                <div class="mt-4 rounded-xl border border-white/20 bg-black/20 p-3 text-[11px] text-stone-200">
                                    "Semoga Allah memberkahi engkau dalam segala hal dan mempersatukan kalian berdua dalam kebaikan."
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- PRICING TIERS -->
        <section id="harga" class="border-y border-stone-200/80 bg-white py-20 dark:border-stone-800 dark:bg-stone-900/50">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-3xl text-center">
                    <div class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3.5 py-1 text-xs font-semibold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                        <span>💎</span> Investasi Sekali Bayar
                    </div>
                    <h2 class="mt-4 font-serif text-3xl font-bold tracking-tight text-stone-900 sm:text-4xl dark:text-white">
                        Harga Transparan Tanpa Biaya Tersembunyi
                    </h2>
                    <p class="mt-4 text-sm text-stone-600 sm:text-base dark:text-stone-300">
                        Cukup sekali bayar untuk aktif selamanya. Tidak ada biaya langganan bulanan.
                    </p>
                </div>

                <div class="mt-14 grid gap-8 md:grid-cols-3">
                    <!-- Tier 1: Free Trial -->
                    <div class="flex flex-col justify-between rounded-3xl border border-stone-200 bg-[#fdfbf7] p-8 dark:border-stone-800 dark:bg-stone-900">
                        <div>
                            <span class="rounded-full bg-stone-200 px-3 py-1 text-xs font-bold text-stone-700 dark:bg-stone-800 dark:text-stone-300">Trial</span>
                            <h3 class="mt-4 font-serif text-2xl font-bold text-stone-900 dark:text-white">Paket Bismillah</h3>
                            <p class="mt-1 text-xs text-stone-500">Uji coba fitur dasar pembuatan undangan.</p>
                            
                            <div class="mt-6 font-serif text-3xl font-bold text-stone-900 dark:text-white">
                                Rp 0 <span class="text-xs font-normal text-stone-400">/ selamanya</span>
                            </div>

                            <ul class="mt-6 space-y-3 text-xs text-stone-600 dark:text-stone-300">
                                <li class="flex items-center gap-2"><span>✓</span> Maksimal 50 Tamu Undangan</li>
                                <li class="flex items-center gap-2"><span>✓</span> 1 Pilihan Tema Standar</li>
                                <li class="flex items-center gap-2"><span>✓</span> Ayat Suci &amp; Profil Mempelai</li>
                                <li class="flex items-center gap-2"><span>✓</span> RSVP Standar</li>
                                <li class="flex items-center gap-2 text-stone-400 line-through"><span>✕</span> Strict Pax Quota Lock</li>
                                <li class="flex items-center gap-2 text-stone-400 line-through"><span>✕</span> Kalkulator Katering Otomatis</li>
                            </ul>
                        </div>

                        <div class="mt-8">
                            <Link
                                :href="route('register')"
                                class="block w-full rounded-xl border border-stone-300 bg-white py-3 text-center text-xs font-bold text-stone-800 transition hover:bg-stone-50 dark:border-stone-700 dark:bg-stone-800 dark:text-white"
                            >
                                Coba Gratis Sekarang
                            </Link>
                        </div>
                    </div>

                    <!-- Tier 2: Popular Pro -->
                    <div class="relative flex flex-col justify-between rounded-3xl border-2 border-emerald-600 bg-gradient-to-b from-emerald-950 via-emerald-900 to-emerald-950 p-8 text-white shadow-2xl">
                        <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 rounded-full bg-amber-400 px-4 py-1 text-[11px] font-bold uppercase tracking-wider text-stone-950 shadow">
                            ★ Paling Diminati Pengantin
                        </div>

                        <div>
                            <span class="rounded-full bg-emerald-800 px-3 py-1 text-xs font-bold text-amber-300">Full Features</span>
                            <h3 class="mt-4 font-serif text-2xl font-bold text-white">Paket Barakah</h3>
                            <p class="mt-1 text-xs text-emerald-200">Solusi lengkap anti-boncos katering.</p>
                            
                            <div class="mt-6 font-serif text-3xl font-bold text-amber-300">
                                Rp 99.000 <span class="text-xs font-normal text-emerald-200">/ sekali bayar</span>
                            </div>

                            <ul class="mt-6 space-y-3 text-xs text-emerald-100">
                                <li class="flex items-center gap-2"><span class="text-amber-300 font-bold">✓</span> <strong>Unlimited Tamu Undangan</strong></li>
                                <li class="flex items-center gap-2"><span class="text-amber-300 font-bold">✓</span> <strong>Strict Pax Quota per Tamu</strong></li>
                                <li class="flex items-center gap-2"><span class="text-amber-300 font-bold">✓</span> <strong>Rekomendasi Porsi Katering</strong></li>
                                <li class="flex items-center gap-2"><span class="text-amber-300 font-bold">✓</span> Semua 4 Palet Tema Premium</li>
                                <li class="flex items-center gap-2"><span class="text-amber-300 font-bold">✓</span> Audio Player &amp; Doa Moderasi</li>
                                <li class="flex items-center gap-2"><span class="text-amber-300 font-bold">✓</span> Ekspor Data Rekap ke Excel</li>
                            </ul>
                        </div>

                        <div class="mt-8">
                            <Link
                                :href="route('register')"
                                class="block w-full rounded-xl bg-amber-400 py-3 text-center text-xs font-bold text-stone-950 shadow-md transition hover:bg-amber-300"
                            >
                                Pilih Paket Barakah &rarr;
                            </Link>
                        </div>
                    </div>

                    <!-- Tier 3: Platinum Custom -->
                    <div class="flex flex-col justify-between rounded-3xl border border-stone-200 bg-[#fdfbf7] p-8 dark:border-stone-800 dark:bg-stone-900">
                        <div>
                            <span class="rounded-full bg-stone-200 px-3 py-1 text-xs font-bold text-stone-700 dark:bg-stone-800 dark:text-stone-300">Concierge</span>
                            <h3 class="mt-4 font-serif text-2xl font-bold text-stone-900 dark:text-white">Paket Sakinah</h3>
                            <p class="mt-1 text-xs text-stone-500">Dibantu input data oleh tim WalimYuk.</p>
                            
                            <div class="mt-6 font-serif text-3xl font-bold text-stone-900 dark:text-white">
                                Rp 199.000 <span class="text-xs font-normal text-stone-400">/ sekali bayar</span>
                            </div>

                            <ul class="mt-6 space-y-3 text-xs text-stone-600 dark:text-stone-300">
                                <li class="flex items-center gap-2"><span>✓</span> Semua Fitur Paket Barakah</li>
                                <li class="flex items-center gap-2"><span>✓</span> Input Data Mempelai Dibantu Tim</li>
                                <li class="flex items-center gap-2"><span>✓</span> Custom Domain Sendiri (opsional)</li>
                                <li class="flex items-center gap-2"><span>✓</span> Prioritas WhatsApp Support 24/7</li>
                                <li class="flex items-center gap-2"><span>✓</span> Revisi Tanpa Batas Waktu</li>
                            </ul>
                        </div>

                        <div class="mt-8">
                            <Link
                                :href="route('register')"
                                class="block w-full rounded-xl border border-stone-300 bg-white py-3 text-center text-xs font-bold text-stone-800 transition hover:bg-stone-50 dark:border-stone-700 dark:bg-stone-800 dark:text-white"
                            >
                                Pilih Paket Sakinah
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ ACCORDION -->
        <section id="faq" class="py-20">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <div class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-3.5 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                        <span>❓</span> Tanya Jawab
                    </div>
                    <h2 class="mt-4 font-serif text-3xl font-bold tracking-tight text-stone-900 sm:text-4xl dark:text-white">
                        Pertanyaan yang Sering Diajukan
                    </h2>
                </div>

                <div class="mt-12 space-y-4">
                    <div
                        v-for="(faq, idx) in faqs"
                        :key="idx"
                        class="overflow-hidden rounded-2xl border border-stone-200 bg-white transition dark:border-stone-800 dark:bg-stone-900"
                    >
                        <button
                            @click="toggleFaq(idx)"
                            class="flex w-full items-center justify-between p-5 text-left text-sm font-bold text-stone-900 sm:text-base dark:text-white"
                        >
                            <span>{{ faq.q }}</span>
                            <span class="ms-4 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-stone-100 text-xs text-stone-600 transition dark:bg-stone-800 dark:text-stone-300" :class="{ 'rotate-180': openFaqIndex === idx }">
                                ↓
                            </span>
                        </button>

                        <div
                            v-show="openFaqIndex === idx"
                            class="border-t border-stone-100 px-5 pb-5 pt-3 text-xs leading-relaxed text-stone-600 sm:text-sm dark:border-stone-800 dark:text-stone-300"
                        >
                            {{ faq.a }}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CONVERSION BANNER -->
        <section class="relative overflow-hidden bg-gradient-to-br from-emerald-950 via-emerald-900 to-emerald-850 py-16 text-white sm:py-20">
            <div class="mx-auto max-w-5xl px-4 text-center sm:px-6 lg:px-8">
                <p class="font-serif text-sm uppercase tracking-widest text-amber-300">Bismillahirahmanirahim</p>
                <h2 class="mt-4 font-serif text-3xl font-bold tracking-tight sm:text-4xl lg:text-5xl">
                    Wujudkan Walimah Impian yang Berkah, Rapi, &amp; Hemat
                </h2>
                <p class="mx-auto mt-4 max-w-2xl text-sm text-emerald-100 sm:text-base">
                    Bergabunglah sekarang dan rasakan kemudahan mengontrol tamu serta anggaran katering prasmanan dengan sistem WalimYuk.
                </p>

                <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
                    <Link
                        :href="route('register')"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-amber-400 px-8 py-4 text-sm font-bold text-stone-950 shadow-lg transition hover:bg-amber-300 sm:w-auto"
                    >
                        <span>Mulai Buat Undangan Gratis</span>
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </Link>
                </div>
            </div>
        </section>

        <!-- FOOTER -->
        <footer class="border-t border-stone-200 bg-[#fdfbf7] py-12 text-stone-600 dark:border-stone-800 dark:bg-stone-950 dark:text-stone-400">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col items-center justify-between gap-6 sm:flex-row">
                    <div class="flex items-center gap-2">
                        <ApplicationLogo :with-text="true" />
                    </div>

                    <p class="text-center text-xs sm:text-right">
                        &copy; 2026 <strong>WalimYuk</strong>. Dibuat dengan cinta untuk pernikahan berkah di seluruh Indonesia.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(4px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.animate-fadeIn {
    animation: fadeIn 0.25s ease-out forwards;
}
</style>
