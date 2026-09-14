<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

const props = defineProps({
  open: { type: Boolean, default: false },
  selectedGuests: { type: Array, default: () => [] },
  weddingId: { type: [Number, String], required: true },
  availableGroups: { type: Array, default: () => [] },
  availableSessions: { type: Array, default: () => [] },
})

const emit = defineEmits(['update:open', 'success'])

const isSubmitting = ref(false)

// Form fields
const applyGroup = ref(false)
const groupMode = ref('existing') // 'existing' | 'new' | 'clear'
const existingGroup = ref('')
const newGroup = ref('')

const applySession = ref(false)
const sessionMode = ref('existing') // 'existing' | 'new' | 'clear'
const existingSession = ref('')
const newSession = ref('')

const applyMaxPax = ref(false)
const maxPax = ref(2)

const applyStatusKirim = ref(false)
const isInvitationSent = ref(true)

const applyNotes = ref(false)
const notes = ref('')

watch(
  () => props.open,
  (val) => {
    if (val) {
      applyGroup.value = false
      groupMode.value = props.availableGroups.length ? 'existing' : 'new'
      existingGroup.value = props.availableGroups[0] || ''
      newGroup.value = ''

      applySession.value = false
      sessionMode.value = props.availableSessions.length ? 'existing' : 'new'
      existingSession.value = props.availableSessions[0] || ''
      newSession.value = ''

      applyMaxPax.value = false
      maxPax.value = 2
      applyStatusKirim.value = false
      isInvitationSent.value = true
      applyNotes.value = false
      notes.value = ''
    }
  }
)

function close() {
  emit('update:open', false)
}

function submit() {
  if (!applyGroup.value && !applySession.value && !applyMaxPax.value && !applyStatusKirim.value && !applyNotes.value) {
    return
  }

  isSubmitting.value = true

  let resolvedGroup = ''
  if (applyGroup.value) {
    if (groupMode.value === 'existing') {
      resolvedGroup = existingGroup.value
    } else if (groupMode.value === 'new') {
      resolvedGroup = newGroup.value.trim()
    } else if (groupMode.value === 'clear') {
      resolvedGroup = ''
    }
  }

  let resolvedSession = ''
  if (applySession.value) {
    if (sessionMode.value === 'existing') {
      resolvedSession = existingSession.value
    } else if (sessionMode.value === 'new') {
      resolvedSession = newSession.value.trim()
    } else if (sessionMode.value === 'clear') {
      resolvedSession = ''
    }
  }

  const payload = {
    guest_ids: props.selectedGuests.map(g => g.id),
    apply_group_name: applyGroup.value,
    group_name: resolvedGroup,
    apply_session_name: applySession.value,
    session_name: resolvedSession,
    apply_max_pax: applyMaxPax.value,
    max_pax: maxPax.value,
    apply_is_invitation_sent: applyStatusKirim.value,
    is_invitation_sent: isInvitationSent.value,
    apply_notes: applyNotes.value,
    notes: notes.value,
  }

  router.post(`/weddings/${props.weddingId}/guests/bulk-edit`, payload, {
    preserveScroll: true,
    onSuccess: () => {
      isSubmitting.value = false
      close()
      emit('success')
    },
    onError: () => {
      isSubmitting.value = false
    },
  })
}
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
      v-if="open"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
      @click.self="close"
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
          v-if="open"
          class="relative w-full max-w-lg overflow-hidden rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5"
          role="dialog"
          aria-modal="true"
        >
          <!-- Header -->
          <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div>
              <h3 class="font-serif text-lg font-bold text-slate-900">
                Bulk Edit Tamu Undangan
              </h3>
              <p class="text-xs text-slate-500 mt-0.5">
                Mengubah data untuk <strong>{{ selectedGuests.length }}</strong> tamu yang dipilih.
              </p>
            </div>
            <button
              type="button"
              class="text-slate-400 hover:text-slate-600 transition text-lg"
              @click="close"
            >
              &times;
            </button>
          </div>

          <!-- Preview chips -->
          <div class="mt-3.5 max-h-20 overflow-y-auto rounded-xl bg-slate-50 p-2.5 flex flex-wrap gap-1.5 border border-slate-100">
            <span
              v-for="guest in selectedGuests"
              :key="guest.id"
              class="inline-flex items-center rounded-md bg-white px-2 py-0.5 text-[11px] font-medium text-slate-700 border border-slate-200 shadow-2xs"
            >
              {{ guest.name }}
            </span>
          </div>

          <p class="mt-3 text-[11px] text-slate-500 italic">
            Centang bidang di bawah ini yang ingin Anda perbarui serentak:
          </p>

          <!-- Form sections -->
          <form @submit.prevent="submit" class="mt-3.5 space-y-4 max-h-[50vh] overflow-y-auto px-0.5">
            <!-- 1. Kategori / Grup -->
            <div class="rounded-xl border border-slate-200 p-3.5 transition" :class="{ 'bg-emerald-50/40 border-emerald-300': applyGroup }">
              <label class="flex items-center gap-2 cursor-pointer select-none">
                <input
                  type="checkbox"
                  v-model="applyGroup"
                  class="rounded text-emerald-600 focus:ring-emerald-500 h-4 w-4"
                />
                <span class="text-xs font-semibold text-slate-800">Ubah Kategori / Grup Undangan</span>
              </label>

              <div v-if="applyGroup" class="mt-3 space-y-2 pl-6 pt-1">
                <div class="flex flex-wrap gap-3 text-xs text-slate-700">
                  <label v-if="availableGroups.length" class="flex items-center gap-1.5 cursor-pointer">
                    <input type="radio" v-model="groupMode" value="existing" class="text-emerald-600" />
                    Pilih yang ada
                  </label>
                  <label class="flex items-center gap-1.5 cursor-pointer">
                    <input type="radio" v-model="groupMode" value="new" class="text-emerald-600" />
                    Buat Baru
                  </label>
                  <label class="flex items-center gap-1.5 cursor-pointer text-rose-600">
                    <input type="radio" v-model="groupMode" value="clear" class="text-rose-600" />
                    Kosongkan Grup
                  </label>
                </div>

                <div v-if="groupMode === 'existing' && availableGroups.length" class="mt-2">
                  <select
                    v-model="existingGroup"
                    class="w-full rounded-xl border border-slate-200 px-3 py-1.5 text-xs focus:border-emerald-500 focus:outline-none bg-white"
                  >
                    <option v-for="grp in availableGroups" :key="grp" :value="grp">
                      {{ grp }}
                    </option>
                  </select>
                </div>

                <div v-else-if="groupMode === 'new'" class="mt-2">
                  <Input
                    v-model="newGroup"
                    placeholder="Contoh: Keluarga Pria, VIP, Teman Kantor..."
                    class="text-xs"
                    required
                  />
                </div>
              </div>
            </div>

            <!-- 2. Sesi Acara -->
            <div class="rounded-xl border border-slate-200 p-3.5 transition" :class="{ 'bg-emerald-50/40 border-emerald-300': applySession }">
              <label class="flex items-center gap-2 cursor-pointer select-none">
                <input
                  type="checkbox"
                  v-model="applySession"
                  class="rounded text-emerald-600 focus:ring-emerald-500 h-4 w-4"
                />
                <span class="text-xs font-semibold text-slate-800">Ubah Sesi Acara Undangan</span>
              </label>

              <div v-if="applySession" class="mt-3 space-y-2 pl-6 pt-1">
                <div class="flex flex-wrap gap-3 text-xs text-slate-700">
                  <label v-if="availableSessions.length" class="flex items-center gap-1.5 cursor-pointer">
                    <input type="radio" v-model="sessionMode" value="existing" class="text-emerald-600" />
                    Pilih yang ada
                  </label>
                  <label class="flex items-center gap-1.5 cursor-pointer">
                    <input type="radio" v-model="sessionMode" value="new" class="text-emerald-600" />
                    Buat Baru
                  </label>
                  <label class="flex items-center gap-1.5 cursor-pointer text-rose-600">
                    <input type="radio" v-model="sessionMode" value="clear" class="text-rose-600" />
                    Kosongkan Sesi
                  </label>
                </div>

                <div v-if="sessionMode === 'existing' && availableSessions.length" class="mt-2">
                  <select
                    v-model="existingSession"
                    class="w-full rounded-xl border border-slate-200 px-3 py-1.5 text-xs focus:border-emerald-500 focus:outline-none bg-white"
                  >
                    <option v-for="ses in availableSessions" :key="ses" :value="ses">
                      {{ ses }}
                    </option>
                  </select>
                </div>

                <div v-else-if="sessionMode === 'new'" class="mt-2">
                  <Input
                    v-model="newSession"
                    placeholder="Contoh: Sesi Akad (08.00-10.00), Sesi Resepsi..."
                    class="text-xs"
                    required
                  />
                </div>
              </div>
            </div>

            <!-- 3. Kuota Maksimal Pax -->
            <div class="rounded-xl border border-slate-200 p-3.5 transition" :class="{ 'bg-emerald-50/40 border-emerald-300': applyMaxPax }">
              <label class="flex items-center gap-2 cursor-pointer select-none">
                <input
                  type="checkbox"
                  v-model="applyMaxPax"
                  class="rounded text-emerald-600 focus:ring-emerald-500 h-4 w-4"
                />
                <span class="text-xs font-semibold text-slate-800">Ubah Kuota Kehadiran (Maks Pax)</span>
              </label>

              <div v-if="applyMaxPax" class="mt-3 pl-6 pt-1">
                <div class="flex items-center gap-3">
                  <Input
                    type="number"
                    v-model.number="maxPax"
                    min="1"
                    max="50"
                    class="w-24 text-xs"
                    required
                  />
                  <span class="text-xs text-slate-500">Orang / Tamu</span>
                </div>
              </div>
            </div>

            <!-- 3. Status Kirim Undangan -->
            <div class="rounded-xl border border-slate-200 p-3.5 transition" :class="{ 'bg-emerald-50/40 border-emerald-300': applyStatusKirim }">
              <label class="flex items-center gap-2 cursor-pointer select-none">
                <input
                  type="checkbox"
                  v-model="applyStatusKirim"
                  class="rounded text-emerald-600 focus:ring-emerald-500 h-4 w-4"
                />
                <span class="text-xs font-semibold text-slate-800">Ubah Status Pengiriman Undangan</span>
              </label>

              <div v-if="applyStatusKirim" class="mt-3 pl-6 pt-1">
                <div class="flex items-center gap-4 text-xs">
                  <label class="flex items-center gap-1.5 cursor-pointer text-emerald-800 font-medium">
                    <input
                      type="radio"
                      :value="true"
                      v-model="isInvitationSent"
                      class="text-emerald-600"
                    />
                    Tandai Sudah Dikirim
                  </label>
                  <label class="flex items-center gap-1.5 cursor-pointer text-slate-600">
                    <input
                      type="radio"
                      :value="false"
                      v-model="isInvitationSent"
                      class="text-slate-500"
                    />
                    Tandai Belum Dikirim
                  </label>
                </div>
              </div>
            </div>

            <!-- 4. Catatan Internal -->
            <div class="rounded-xl border border-slate-200 p-3.5 transition" :class="{ 'bg-emerald-50/40 border-emerald-300': applyNotes }">
              <label class="flex items-center gap-2 cursor-pointer select-none">
                <input
                  type="checkbox"
                  v-model="applyNotes"
                  class="rounded text-emerald-600 focus:ring-emerald-500 h-4 w-4"
                />
                <span class="text-xs font-semibold text-slate-800">Perbarui Catatan Internal</span>
              </label>

              <div v-if="applyNotes" class="mt-3 pl-6 pt-1">
                <Input
                  v-model="notes"
                  placeholder="Catatan untuk seluruh tamu yang dipilih (opsional)"
                  class="text-xs"
                />
              </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
              <Button type="button" variant="outline" class="rounded-xl text-xs" @click="close">
                Batal
              </Button>
              <Button
                type="submit"
                :disabled="isSubmitting || (!applyGroup && !applySession && !applyMaxPax && !applyStatusKirim && !applyNotes)"
                class="rounded-xl bg-emerald-700 px-4 text-xs font-semibold text-white hover:bg-emerald-800 shadow-sm"
              >
                <svg
                  v-if="isSubmitting"
                  class="mr-1.5 h-3.5 w-3.5 animate-spin text-white"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                Terapkan Perubahan ({{ selectedGuests.length }})
              </Button>
            </div>
          </form>
        </div>
      </Transition>
    </div>
  </Transition>
</template>
