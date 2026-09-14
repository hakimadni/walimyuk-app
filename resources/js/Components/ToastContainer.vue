<script setup>
import { useToast } from '@/Composables/useToast'

const { toasts, removeToast } = useToast()
</script>

<template>
  <div
    class="pointer-events-none fixed top-4 right-4 z-50 flex flex-col gap-2.5 max-w-sm w-full"
    aria-live="polite"
  >
    <TransitionGroup
      enter-active-class="transform ease-out duration-300 transition"
      enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-4"
      enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="pointer-events-auto flex items-start gap-3 rounded-xl p-3.5 shadow-lg ring-1 ring-black/5 bg-white border"
        :class="[
          toast.type === 'error'
            ? 'border-rose-200 bg-rose-50/90 text-rose-900'
            : toast.type === 'warning'
              ? 'border-amber-200 bg-amber-50/90 text-amber-900'
              : 'border-emerald-200 bg-emerald-50/90 text-emerald-950'
        ]"
      >
        <!-- Icon -->
        <div class="shrink-0 mt-0.5">
          <svg
            v-if="toast.type === 'error'"
            class="h-5 w-5 text-rose-600"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <svg
            v-else-if="toast.type === 'warning'"
            class="h-5 w-5 text-amber-600"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
          <svg
            v-else
            class="h-5 w-5 text-emerald-600"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>

        <!-- Content -->
        <div class="flex-1 text-xs font-medium leading-relaxed">
          {{ toast.message }}
        </div>

        <!-- Close button -->
        <button
          type="button"
          class="shrink-0 text-slate-400 hover:text-slate-600 transition"
          @click="removeToast(toast.id)"
        >
          <span class="sr-only">Tutup</span>
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>
