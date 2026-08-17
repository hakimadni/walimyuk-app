<script setup>
defineProps({
  status: { type: String, default: 'pending' }
})

const badgeClasses = {
  pending: 'bg-amber-100 text-amber-800 border-amber-200',
  attending: 'bg-emerald-100 text-emerald-800 border-emerald-200',
  declined: 'bg-rose-100 text-rose-800 border-rose-200',
}

const badgeLabels = {
  pending: 'Belum RSVP',
  attending: 'Hadir',
  declined: 'Tidak Hadir',
}
</script>

<template>
  <span
    class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold"
    :class="badgeClasses[status] || badgeClasses.pending"
  >
    {{ badgeLabels[status] || badgeLabels.pending }}
  </span>
</template>
