<script setup>
import { computed } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import { Textarea } from '@/components/ui/textarea'
import { resolveBuilder } from '@/lib/invitationTheme'

const props = defineProps({
  guest: { type: Object, default: null },
  wedding: { type: Object, required: true },
  existingRsvp: { type: Object, default: null },
  themeConfig: { type: Object, default: () => ({}) },
})

const page = usePage()
const builder = computed(() => resolveBuilder(props.themeConfig))
const palette = computed(() => builder.value.content.palette)

const token = computed(() => {
  if (typeof window !== 'undefined') {
    return new URLSearchParams(window.location.search).get('token') || ''
  }
  return ''
})

const form = useForm({
  attendance_status: props.existingRsvp?.attendance_status || '',
  pax_count: props.existingRsvp?.pax_count || 1,
  comment: props.existingRsvp?.comment || '',
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
  <div class="flex h-full min-h-0 w-full flex-col items-center justify-center px-5 py-5">
    <div class="w-full">
      <div class="mb-3.5 text-center aos-item aos-fade-down">
        <h2 class="mb-1 font-serif text-2xl font-bold tracking-tight" :style="{ color: palette.primary }">RSVP</h2>
        <p class="text-xs leading-relaxed opacity-85" :style="{ color: palette.secondary }">
          Mohon konfirmasi kehadiran agar kami dapat mempersiapkan acara dengan baik.
        </p>
      </div>

      <!-- Already submitted banner -->
      <div
        v-if="isSubmitted && props.existingRsvp"
        class="mb-3 rounded-xl p-2.5 text-center border backdrop-blur-sm aos-item aos-zoom-in"
        :style="{ backgroundColor: `${palette.secondary}15`, borderColor: `${palette.secondary}44` }"
      >
        <p class="text-xs font-bold" :style="{ color: palette.primary }">
          {{ props.existingRsvp.attendance_status === 'attending' ? '✓ Anda sudah konfirmasi hadir' : '✓ Anda sudah menyampaikan konfirmasi' }}
        </p>
        <p class="text-[10px] mt-0.5" :style="{ color: palette.secondary }">Anda dapat memperbarui di bawah ini</p>
      </div>

      <!-- Success flash -->
      <div
        v-if="page.props.flash?.success"
        class="mb-3 rounded-xl p-2.5 text-center border backdrop-blur-sm aos-item aos-zoom-in"
        :style="{ backgroundColor: `${palette.secondary}15`, borderColor: `${palette.secondary}44` }"
      >
        <p class="text-xs font-bold" :style="{ color: palette.primary }">{{ page.props.flash.success }}</p>
      </div>

      <form @submit.prevent="submit" class="flex flex-col gap-3 text-center">
        <!-- Attendance toggle -->
        <div class="flex flex-col items-center gap-1.5 aos-item aos-fade-up aos-delay-150">
          <p class="text-xs font-bold" :style="{ color: palette.primary }">Apakah Anda akan hadir?</p>
          <div class="grid w-full grid-cols-2 gap-2.5">
            <button
              type="button"
              class="rounded-xl border-2 py-2.5 text-xs font-bold transition-all duration-300 flex flex-col items-center justify-center gap-1 shadow-sm active:scale-95"
              :style="form.attendance_status === 'attending' 
                ? { borderColor: palette.secondary, backgroundColor: palette.primary, color: '#fff' }
                : { borderColor: `${palette.secondary}44`, backgroundColor: `${palette.secondary}12`, color: palette.primary }"
              @click="form.attendance_status = 'attending'"
            >
              <span class="text-xl drop-shadow-sm anim-pulse-soft">✅</span>
              <span>Ya, Hadir</span>
            </button>
            <button
              type="button"
              class="rounded-xl border-2 py-2.5 text-xs font-bold transition-all duration-300 flex flex-col items-center justify-center gap-1 shadow-sm active:scale-95"
              :style="form.attendance_status === 'declined'
                ? { borderColor: '#ef4444', backgroundColor: '#ef4444', color: '#fff' }
                : { borderColor: `${palette.secondary}44`, backgroundColor: `${palette.secondary}12`, color: palette.primary }"
              @click="form.attendance_status = 'declined'"
            >
              <span class="text-xl drop-shadow-sm">🙏</span>
              <span>Maaf, Tidak</span>
            </button>
          </div>
          <p v-if="form.errors.attendance_status" class="text-xs text-rose-500">{{ form.errors.attendance_status }}</p>
        </div>

        <!-- Pax stepper -->
        <Transition
          enter-active-class="transition-all duration-300 ease-out"
          enter-from-class="opacity-0 -translate-y-4"
          enter-to-class="opacity-100 translate-y-0"
          leave-active-class="transition-all duration-200 ease-in"
          leave-from-class="opacity-100 translate-y-0"
          leave-to-class="opacity-0 -translate-y-4"
        >
          <div v-if="isAttending" class="flex flex-col items-center gap-1 pt-0.5">
            <p class="text-xs font-bold" :style="{ color: palette.primary }">Jumlah yang hadir</p>
            <div
              class="flex items-center justify-between rounded-full px-2 py-1 w-full max-w-[200px] shadow-sm border"
              :style="{ backgroundColor: `${palette.secondary}15`, borderColor: `${palette.secondary}50` }"
            >
              <button
                type="button"
                class="flex h-8 w-8 items-center justify-center rounded-full text-base font-bold transition-all active:scale-95 border"
                :style="form.pax_count > 1 ? { backgroundColor: `${palette.secondary}25`, borderColor: `${palette.secondary}66`, color: palette.primary } : { backgroundColor: 'transparent', borderColor: 'transparent', color: '#cbd5e1' }"
                :disabled="form.pax_count <= 1"
                @click="form.pax_count = Math.max(1, form.pax_count - 1)"
              >−</button>
              <span class="font-bold text-lg tabular-nums" :style="{ color: palette.primary }">{{ form.pax_count }}</span>
              <button
                type="button"
                class="flex h-8 w-8 items-center justify-center rounded-full text-base font-bold transition-all active:scale-95 border"
                :style="form.pax_count < maxPax ? { backgroundColor: `${palette.secondary}25`, borderColor: `${palette.secondary}66`, color: palette.primary } : { backgroundColor: 'transparent', borderColor: 'transparent', color: '#cbd5e1' }"
                :disabled="form.pax_count >= maxPax"
                @click="form.pax_count = Math.min(maxPax, form.pax_count + 1)"
              >+</button>
            </div>
            <p class="text-[10px]" :style="{ color: palette.secondary }">*Maksimal undangan untuk {{ maxPax }} orang</p>
          </div>
        </Transition>

        <!-- Notes / wishes -->
        <div class="flex flex-col items-center gap-1 aos-item aos-fade-up aos-delay-250">
          <p class="text-xs font-bold" :style="{ color: palette.primary }">
            Ucapan & Doa <span class="font-normal text-[11px]" :style="{ color: palette.secondary }">(opsional)</span>
          </p>
          <Textarea
            v-model="form.comment"
            rows="2"
            class="w-full rounded-xl p-2.5 text-xs text-center shadow-inner placeholder:opacity-50 focus:ring-2 transition-all resize-none border"
            :style="{ backgroundColor: `${palette.secondary}12`, borderColor: `${palette.secondary}44`, color: palette.primary }"
            placeholder="Tulis doa dan ucapan terbaik Anda untuk kedua mempelai…"
          />
        </div>

        <!-- Submit button -->
        <button
          type="submit"
          :disabled="form.processing || !form.attendance_status"
          class="mt-1 w-full rounded-xl py-3 text-xs font-bold tracking-wide text-white shadow-md transition-all duration-200 active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed border aos-item aos-fade-up aos-delay-300 anim-pulse-soft"
          :style="{ backgroundColor: palette.primary, borderColor: `${palette.secondary}88` }"
        >
          <span v-if="form.processing">Mengirim konfirmasi…</span>
          <span v-else>{{ isSubmitted ? 'Perbarui Konfirmasi' : 'Kirim Konfirmasi Kehadiran' }}</span>
        </button>
      </form>
    </div>
  </div>
</template>
