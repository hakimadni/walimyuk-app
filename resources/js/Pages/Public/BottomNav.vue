<script setup>
defineProps({
  activeSection: { type: String, default: 'cover' },
  sections: { type: Array, default: () => [] },
})

defineEmits(['navigate'])
</script>

<template>
  <nav class="fixed bottom-0 left-0 right-0 z-40 mx-auto max-w-[480px] border-t border-sage-200 bg-white/90 shadow-lg backdrop-blur-md">
    <div class="flex gap-1 overflow-x-auto px-2 py-2 scrollbar-hide">
      <button
        v-for="section in sections"
        :key="section.id"
        class="flex-shrink-0 rounded-lg px-3 py-2 text-xs font-medium transition"
        :class="[
          activeSection === section.id
            ? 'bg-emerald-700 text-white shadow-sm'
            : 'text-slate-500 hover:bg-sage-50'
        ]"
        @click="$emit('navigate', section.id)"
      >
        {{ section.label }}
      </button>
    </div>
  </nav>
</template>
