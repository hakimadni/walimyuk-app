<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Textarea } from '@/components/ui/textarea'

const props = defineProps({
  guest: { type: Object, default: null },
  wedding: { type: Object, required: true },
  existingRsvp: { type: Object, default: null },
})

const token = computed(() => {
  if (typeof window !== 'undefined') {
    return new URLSearchParams(window.location.search).get('token') || ''
  }
  return ''
})

const form = useForm({
  attendance_status: props.existingRsvp?.attendance_status || '',
  pax_count: props.existingRsvp?.pax_count || 1,
  notes: props.existingRsvp?.notes || '',
})

const maxPax = computed(() => props.guest?.max_pax || 5)
const isSubmitted = computed(() => !!props.existingRsvp)
const isAttending = computed(() => form.attendance_status === 'attending')

function submit() {
  form.post(`/w/${props.wedding.slug}/rsvp?token=${token.value}`, {
    preserveScroll: true,
  })
}
</script>

<template>
  <div class="flex h-full min-h-0 flex-col px-5 py-6 overflow-y-auto">
    <h2 class="mb-1 text-center font-serif text-2xl font-bold text-emerald-900">RSVP</h2>
    <p class="mb-5 text-center text-xs text-slate-500 leading-relaxed">
      Mohon konfirmasi kehadiran agar kami dapat mempersiapkan dengan lebih baik.
    </p>

    <!-- Already submitted banner -->
    <div v-if="isSubmitted" class="mb-4 rounded-2xl bg-emerald-50 px-4 py-3 text-center border border-emerald-200">
      <p class="text-sm font-semibold text-emerald-800">
        {{ existingRsvp.attendance_status === 'attending' ? '✓ Anda sudah konfirmasi hadir' : '✓ Anda sudah menyampaikan konfirmasi' }}
      </p>
      <p class="text-xs text-emerald-600 mt-0.5">Anda dapat memperbarui di bawah ini</p>
    </div>

    <!-- Success flash -->
    <div v-if="$page.props.flash?.success" class="mb-4 rounded-2xl bg-emerald-50 px-4 py-3 text-center border border-emerald-200">
      <p class="text-sm font-semibold text-emerald-800">{{ $page.props.flash.success }}</p>
    </div>

    <form @submit.prevent="submit" class="flex-1 flex flex-col gap-4 text-center">
      <!-- Attendance toggle -->
      <div class="flex flex-col items-center">
        <p class="mb-2 text-sm font-semibold text-slate-700">Apakah akan hadir?</p>
        <div class="grid grid-cols-2 gap-2">
          <button
            type="button"
            class="rounded-2xl border-2 py-4 text-sm font-bold transition-all"
            :class="form.attendance_status === 'attending'
              ? 'border-emerald-600 bg-emerald-600 text-white shadow-md'
              : 'border-slate-200 bg-white text-slate-600'"
            @click="form.attendance_status = 'attending'"
          >
            <span class="block text-xl mb-1">✅</span>
            InsyaAllah Hadir
          </button>
          <button
            type="button"
            class="rounded-2xl border-2 py-4 text-sm font-bold transition-all"
            :class="form.attendance_status === 'declined'
              ? 'border-rose-500 bg-rose-500 text-white shadow-md'
              : 'border-slate-200 bg-white text-slate-600'"
            @click="form.attendance_status = 'declined'"
          >
            <span class="block text-xl mb-1">🙏</span>
            Berhalangan
          </button>
        </div>
        <p v-if="form.errors.attendance_status" class="mt-1 text-xs text-rose-500">{{ form.errors.attendance_status }}</p>
      </div>

      <!-- Pax stepper -->
      <Transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-2"
      >
        <div v-if="isAttending" class="flex flex-col items-center">
          <p class="mb-2 text-sm font-semibold text-slate-700">Jumlah yang hadir</p>
          <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-3 w-full max-w-[200px]">
            <button
              type="button"
              class="flex h-9 w-9 items-center justify-center rounded-xl text-xl font-bold transition"
              :class="form.pax_count > 1 ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-400'"
              :disabled="form.pax_count <= 1"
              @click="form.pax_count = Math.max(1, form.pax_count - 1)"
            >−</button>
            <div class="text-center">
              <span class="text-2xl font-bold text-emerald-900">{{ form.pax_count }}</span>
              <p class="text-[11px] text-slate-400">orang (maks. {{ maxPax }})</p>
            </div>
            <button
              type="button"
              class="flex h-9 w-9 items-center justify-center rounded-xl text-xl font-bold transition"
              :class="form.pax_count < maxPax ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-400'"
              :disabled="form.pax_count >= maxPax"
              @click="form.pax_count = Math.min(maxPax, form.pax_count + 1)"
            >+</button>
          </div>
        </div>
      </Transition>

      <!-- Notes / wishes -->
      <div class="flex-1 flex flex-col items-center">
        <p class="mb-2 text-sm font-semibold text-slate-700">Ucapan & Doa <span class="font-normal text-slate-400">(opsional)</span></p>
        <Textarea
          v-model="form.notes"
          rows="3"
          class="w-full rounded-2xl border-slate-200 bg-white text-sm text-center"
          placeholder="Tulis doa dan ucapan terbaik Anda…"
        />
      </div>

      <!-- Submit -->
      <button
        type="submit"
        :disabled="form.processing || !form.attendance_status"
        class="w-full rounded-2xl py-4 text-sm font-bold text-white shadow-md transition active:scale-95 disabled:opacity-50"
        :class="form.attendance_status ? 'bg-emerald-700' : 'bg-slate-300'"
      >
        {{ form.processing ? 'Mengirim...' : (isSubmitted ? 'Perbarui Konfirmasi' : 'Kirim Konfirmasi') }}
      </button>
    </form>
  </div>
</template>
