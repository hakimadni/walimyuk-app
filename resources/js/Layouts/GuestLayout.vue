<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-[#fdfaf5] text-stone-800 antialiased selection:bg-emerald-600 selection:text-white dark:bg-stone-950 dark:text-stone-100">
        <div class="grid min-h-screen lg:grid-cols-12">
            <!-- Left Hero Panel (Desktop) -->
            <div class="relative hidden flex-col justify-between overflow-hidden bg-gradient-to-br from-emerald-950 via-emerald-900 to-emerald-850 p-12 text-white lg:col-span-5 lg:flex xl:col-span-5">
                <!-- Islamic Geometric Pattern Overlay -->
                <div class="pointer-events-none absolute inset-0 opacity-10">
                    <svg class="h-full w-full" xmlns="http://www.w3.org/2000/svg" width="100%" height="100%">
                        <defs>
                            <pattern id="islamic-grid" width="60" height="60" patternUnits="userSpaceOnUse">
                                <path d="M30 0 L60 30 L30 60 L0 30 Z" fill="none" stroke="currentColor" stroke-width="1.2" />
                                <circle cx="30" cy="30" r="12" fill="none" stroke="currentColor" stroke-width="1" />
                                <circle cx="30" cy="30" r="4" fill="currentColor" opacity="0.4" />
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" fill="url(#islamic-grid)" />
                    </svg>
                </div>

                <!-- Subtle Ambient Glows -->
                <div class="pointer-events-none absolute -left-20 -top-20 h-72 w-72 rounded-full bg-emerald-500/20 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-20 -right-20 h-80 w-80 rounded-full bg-amber-400/15 blur-3xl"></div>

                <!-- Brand Header -->
                <div class="relative z-10">
                    <Link :href="route('home')" class="inline-flex items-center gap-3 transition-opacity hover:opacity-90">
                        <ApplicationLogo :with-text="true" class="[&_span]:text-white [&_span.text-emerald-700]:text-amber-300" />
                    </Link>
                </div>

                <!-- Center Value Proposition -->
                <div class="relative z-10 my-auto py-10">
                    <div class="inline-flex items-center gap-2 rounded-full border border-amber-400/30 bg-amber-400/10 px-3.5 py-1 text-xs font-medium text-amber-300 backdrop-blur-sm">
                        <span>✨</span> Platform Undangan Digital & RSVP Islami
                    </div>

                    <h1 class="mt-5 font-serif text-3xl font-bold leading-snug tracking-tight xl:text-4xl">
                        Rencanakan Walimah Berkah, <br class="hidden xl:inline" />
                        <span class="bg-gradient-to-r from-amber-200 via-amber-300 to-yellow-400 bg-clip-text text-transparent">
                            Katering Presisi Tanpa Boncos
                        </span>
                    </h1>

                    <p class="mt-4 text-sm leading-relaxed text-emerald-100/80">
                        Bebaskan diri dari estimasi prasmanan yang meleset. Batasi porsi setiap tamu dengan tautan undangan unik, animasi swipe mobile app-like, dan kalkulasi catering otomatis.
                    </p>

                    <!-- Feature Bullet Points -->
                    <div class="mt-8 space-y-3.5 text-sm text-emerald-50/90">
                        <div class="flex items-center gap-3">
                            <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-700/60 text-amber-300">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span><strong>Strict Pax Limit:</strong> Tamu tidak bisa melebihi jatah pax undangan.</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-700/60 text-amber-300">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span><strong>Live Theme Builder:</strong> Kustomisasi warna, font, dan urutan seksi.</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-700/60 text-amber-300">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span><strong>Kalkulasi Real-time:</strong> Rekomendasi porsi katering + safety buffer.</span>
                        </div>
                    </div>

                    <!-- Mini Testimonial Quote -->
                    <div class="mt-10 rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-md">
                        <p class="text-xs italic leading-relaxed text-emerald-100">
                            "Alhamdulillah berkat WalimYuk, katering kami pas tanpa sisa berlebih. Tamu merasa dihargai dengan sapaan nama dan jatah pax khusus."
                        </p>
                        <div class="mt-3 flex items-center gap-2.5">
                            <div class="flex h-7 w-7 items-center justify-center rounded-full bg-amber-400 text-xs font-bold text-emerald-950">
                                FA
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-white">Farhan & Aisyah</p>
                                <p class="text-[10px] text-emerald-300">Pernikahan di Jakarta • 350 Tamu Terkonfirmasi</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Excerpt -->
                <div class="relative z-10 text-xs text-emerald-300/70">
                    &copy; 2026 WalimYuk. Platform Undangan Digital & RSVP Islami.
                </div>
            </div>

            <!-- Right Content / Form Panel -->
            <div class="relative flex flex-col justify-between p-6 sm:p-10 lg:col-span-7 lg:p-14 xl:col-span-7">
                <!-- Top Navigation for Mobile & Back to Home -->
                <div class="flex items-center justify-between">
                    <Link :href="route('home')" class="lg:hidden">
                        <ApplicationLogo :with-text="true" />
                    </Link>
                    <Link
                        :href="route('home')"
                        class="ms-auto inline-flex items-center gap-1.5 rounded-full border border-stone-200 bg-white px-3.5 py-1.5 text-xs font-medium text-stone-600 shadow-sm transition hover:border-emerald-500 hover:text-emerald-700 dark:border-stone-800 dark:bg-stone-900 dark:text-stone-300 dark:hover:text-emerald-400"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Kembali ke Beranda</span>
                    </Link>
                </div>

                <!-- Form Card Center -->
                <div class="mx-auto my-auto w-full max-w-md py-8">
                    <slot />
                </div>

                <!-- Bottom Helper -->
                <div class="text-center text-xs text-stone-500 dark:text-stone-400">
                    Butuh bantuan teknis? Hubungi <a href="mailto:support@walimyuk.com" class="font-medium text-emerald-600 underline hover:text-emerald-700 dark:text-emerald-400">Tim Support WalimYuk</a>
                </div>
            </div>
        </div>
    </div>
</template>
