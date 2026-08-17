<script setup>
defineProps({
  label: { type: String, required: true },
  value: { type: [String, Number], required: true },
  color: { type: String, default: 'emerald' },
})

const colorClasses = {
  emerald: 'border-emerald-200 bg-emerald-50 text-emerald-900',
  sage: 'border-lime-200 bg-lime-50 text-lime-900',
  gold: 'border-amber-200 bg-amber-50 text-amber-900',
  red: 'border-rose-200 bg-rose-50 text-rose-900',
}
</script>

<template>
  <div class="rounded-2xl border p-5 shadow-sm" :class="colorClasses[color] || colorClasses.emerald">
    <p class="text-sm font-medium opacity-80">{{ label }}</p>
    <p class="mt-3 text-3xl font-bold">{{ value }}</p>
  </div>
</template>
