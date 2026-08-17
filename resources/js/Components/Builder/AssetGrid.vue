<script setup>
const props = defineProps({
  modelValue: { type: String, default: 'none' },
  assets: { type: Array, default: () => [] },
  showNone: { type: Boolean, default: true },
  disabled: { type: Boolean, default: false },
})
const emit = defineEmits(['update:modelValue'])

function select(key) {
  if (!props.disabled) emit('update:modelValue', key)
}
</script>

<template>
  <div class="grid grid-cols-4 gap-1.5">
    <!-- None option -->
    <button
      v-if="showNone"
      type="button"
      @click="select('none')"
      :disabled="disabled"
      :class="[
        'col-span-1 flex aspect-square flex-col items-center justify-center rounded-xl border-2 text-[10px] font-semibold transition',
        modelValue === 'none'
          ? 'border-emerald-500 bg-emerald-50 text-emerald-700'
          : 'border-slate-200 bg-white text-slate-400 hover:border-slate-300'
      ]"
    >
      <span class="text-lg mb-0.5">✕</span>
      <span>Tidak Ada</span>
    </button>

    <!-- Asset thumbnails -->
    <button
      v-for="asset in assets"
      :key="asset.key"
      type="button"
      @click="select(asset.key)"
      :disabled="disabled"
      :title="asset.label"
      :class="[
        'col-span-1 aspect-square overflow-hidden rounded-xl border-2 transition',
        modelValue === asset.key
          ? 'border-emerald-500 ring-2 ring-emerald-300 shadow-sm'
          : 'border-slate-200 hover:border-emerald-300'
      ]"
    >
      <img :src="asset.path" :alt="asset.label" class="h-full w-full object-cover" />
    </button>
  </div>
</template>
