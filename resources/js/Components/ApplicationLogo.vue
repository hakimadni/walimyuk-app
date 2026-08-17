<script setup>
defineProps({
    withText: {
        type: Boolean,
        default: false
    }
})
</script>

<template>
    <div class="inline-flex items-center gap-2.5">
        <!-- SVG Emblem: Islamic Arch + Interlocking Golden Rings + Starburst -->
        <svg
            viewBox="0 0 48 48"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            class="h-10 w-10 shrink-0 transition-transform duration-300 hover:scale-105"
        >
            <defs>
                <linearGradient id="emeraldGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#065f46" />
                    <stop offset="100%" stop-color="#047857" />
                </linearGradient>
                <linearGradient id="goldGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#f5efcc" />
                    <stop offset="50%" stop-color="#d4af37" />
                    <stop offset="100%" stop-color="#b47318" />
                </linearGradient>
                <filter id="glow" x="-20%" y="-20%" width="140%" height="140%">
                    <feDropShadow dx="0" dy="2" stdDeviation="2" flood-color="#065f46" flood-opacity="0.25" />
                </filter>
            </defs>
            
            <!-- Rounded Arch Background Badge -->
            <rect x="2" y="2" width="44" height="44" rx="12" fill="url(#emeraldGrad)" filter="url(#glow)" />
            <rect x="3" y="3" width="42" height="42" rx="11" stroke="url(#goldGrad)" stroke-width="1.2" stroke-opacity="0.6" />
            
            <!-- Islamic Dome Arch Contour -->
            <path
                d="M14 36V22C14 18 18 13 24 10C30 13 34 18 34 22V36"
                stroke="url(#goldGrad)"
                stroke-width="1.75"
                stroke-linecap="round"
                stroke-linejoin="round"
            />
            
            <!-- Central Golden Sparkle -->
            <circle cx="24" cy="17" r="2" fill="url(#goldGrad)" />
            
            <!-- Intersecting Wedding Rings at Base of Arch -->
            <ellipse cx="20.5" cy="27.5" rx="5.5" ry="4.5" stroke="#fdf8f0" stroke-width="2" />
            <ellipse cx="27.5" cy="27.5" rx="5.5" ry="4.5" stroke="url(#goldGrad)" stroke-width="2" />
        </svg>

        <span v-if="withText" class="flex flex-col text-left">
            <span class="font-serif text-xl font-bold tracking-tight text-emerald-950 dark:text-emerald-50">
                Walim<span class="text-emerald-700 dark:text-gold-400">Yuk</span>
            </span>
            <span class="text-[10px] font-medium uppercase tracking-widest text-emerald-700/80 dark:text-emerald-300/80">
                Undangan & Katering
            </span>
        </span>
    </div>
</template>
