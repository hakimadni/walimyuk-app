<script setup>
import { onMounted, onUnmounted } from 'vue'
import { useConfirm } from '@/Composables/useConfirm'

const { isOpen, options, loading, handleConfirm, handleCancel } = useConfirm()

function handleKeyDown(e) {
  if (!isOpen.value) return
  if (e.key === 'Escape') {
    e.preventDefault()
    handleCancel()
  } else if (e.key === 'Enter' && !loading.value) {
    e.preventDefault()
    handleConfirm()
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleKeyDown)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown)
})
</script>

<template>
  <Transition
    enter-active-class="ease-out duration-200"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="ease-in duration-150"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div
      v-if="isOpen"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
      @click.self="handleCancel"
    >
      <Transition
        enter-active-class="ease-out duration-200"
        enter-from-class="opacity-0 scale-95 translate-y-2"
        enter-to-class="opacity-100 scale-100 translate-y-0"
        leave-active-class="ease-in duration-150"
        leave-from-class="opacity-100 scale-100 translate-y-0"
        leave-to-class="opacity-0 scale-95 translate-y-2"
      >
        <div
          v-if="isOpen"
          class="relative w-full max-w-md overflow-hidden rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5"
          role="dialog"
          aria-modal="true"
        >
          <!-- Top Icon & Header -->
          <div class="flex items-start gap-4">
            <div
              v-if="options.variant === 'danger'"
              class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-600"
            >
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </div>
            <div
              v-else-if="options.variant === 'warning'"
              class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600"
            >
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <div
              v-else
              class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700"
            >
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>

            <div class="flex-1 min-w-0">
              <h3 class="font-serif text-lg font-bold text-slate-900 leading-snug">
                {{ options.title }}
              </h3>
              <p class="mt-1.5 text-sm text-slate-600 leading-relaxed">
                {{ options.description }}
              </p>
            </div>
          </div>

          <!-- Items preview if provided -->
          <div
            v-if="options.items && options.items.length"
            class="mt-4 max-h-36 overflow-y-auto rounded-xl border border-slate-200 bg-slate-50/80 p-2.5 text-xs text-slate-700 space-y-1"
          >
            <div
              v-for="(item, idx) in options.items"
              :key="idx"
              class="flex items-center gap-1.5 truncate"
            >
              <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-slate-400"></span>
              <span class="truncate">{{ item }}</span>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="mt-6 flex items-center justify-end gap-2.5">
            <button
              type="button"
              class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shadow-sm"
              @click="handleCancel"
            >
              {{ options.cancelText || 'Batal' }}
            </button>

            <button
              type="button"
              :disabled="loading"
              class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-xs font-semibold text-white transition shadow-sm disabled:opacity-50"
              :class="[
                options.variant === 'danger'
                  ? 'bg-rose-600 hover:bg-rose-700'
                  : options.variant === 'warning'
                    ? 'bg-amber-600 hover:bg-amber-700'
                    : 'bg-emerald-700 hover:bg-emerald-800'
              ]"
              @click="handleConfirm"
            >
              <svg
                v-if="loading"
                class="mr-1.5 h-3.5 w-3.5 animate-spin text-white"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
              </svg>
              {{ options.confirmText || 'Ya, Lanjutkan' }}
            </button>
          </div>
        </div>
      </Transition>
    </div>
  </Transition>
</template>
