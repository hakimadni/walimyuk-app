<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

const props = defineProps({
  open: { type: Boolean, default: false },
  guest: { type: Object, default: null }, // null = create mode, object = edit mode
  weddingId: { type: [Number, String], required: true },
  availableGroups: { type: Array, default: () => [] },
  availableSessions: { type: Array, default: () => [] },
})

const emit = defineEmits(['update:open', 'saved'])

const form = ref({
  name: '',
  phone_number: '',
  group_name: '',
  session_name: '',
  max_pax: 2,
  is_physical_invitation: false,
  notes: '',
})

const errors = ref({})
const isSubmitting = ref(false)

watch(
  () => [props.open, props.guest],
  ([isOpen, guest]) => {
    if (isOpen) {
      errors.value = {}
      if (guest) {
        form.value = {
          name: guest.name || '',
          phone_number: guest.phone_number || '',
          group_name: guest.group_name || '',
          session_name: guest.session_name || '',
          max_pax: guest.max_pax || 2,
          is_physical_invitation: !!guest.is_physical_invitation,
          notes: guest.notes || '',
        }
      } else {
        form.value = {
          name: '',
          phone_number: '',
          group_name: '',
          session_name: '',
          max_pax: 2,
          is_physical_invitation: false,
          notes: '',
        }
      }
    }
  },
  { immediate: true }
)

function close() {
  emit('update:open', false)
}

function submit() {
  isSubmitting.value = true
  errors.value = {}

  if (props.guest) {
    // Edit mode
    router.put(`/weddings/${props.weddingId}/guests/${props.guest.id}`, form.value, {
      preserveScroll: true,
      onSuccess: () => {
        isSubmitting.value = false
        close()
        emit('saved')
      },
      onError: (err) => {
        isSubmitting.value = false
        errors.value = err
      },
    })
  } else {
    // Create mode
    router.post(`/weddings/${props.weddingId}/guests`, form.value, {
      preserveScroll: true,
      onSuccess: () => {
        isSubmitting.value = false
        close()
        emit('saved')
      },
      onError: (err) => {
        isSubmitting.value = false
        errors.value = err
      },
    })
  }
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
          class="relative w-full max-w-md overflow-hidden rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5"
          role="dialog"
          aria-modal="true"
        >
          <!-- Header -->
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="font-serif text-lg font-bold text-slate-900">
              {{ guest ? 'Edit Data Tamu' : 'Tambah Tamu Baru' }}
            </h3>
            <button
              type="button"
              class="text-slate-400 hover:text-slate-600 transition text-lg"
              @click="close"
            >
              &times;
            </button>
          </div>

          <form @submit.prevent="submit" class="mt-4 space-y-3.5">
            <!-- Nama Tamu -->
            <div class="space-y-1">
              <Label class="text-xs font-semibold text-slate-700">Nama Tamu <span class="text-rose-500">*</span></Label>
              <Input
                v-model="form.name"
                placeholder="Contoh: Bpk. Bambang & Keluarga"
                class="text-xs"
                required
                autofocus
              />
              <p v-if="errors.name" class="text-[11px] text-rose-500">{{ errors.name }}</p>
            </div>

            <!-- WhatsApp -->
            <div class="space-y-1">
              <Label class="text-xs font-semibold text-slate-700">Nomor WhatsApp</Label>
              <Input
                v-model="form.phone_number"
                placeholder="Contoh: 08123456789"
                class="text-xs font-mono"
              />
              <p v-if="errors.phone_number" class="text-[11px] text-rose-500">{{ errors.phone_number }}</p>
            </div>

            <!-- Grid: Kategori / Grup & Pax -->
            <div class="grid grid-cols-2 gap-3">
              <div class="space-y-1">
                <Label class="text-xs font-semibold text-slate-700">Kategori / Grup</Label>
                <Input
                  v-model="form.group_name"
                  placeholder="Keluarga, VIP, Teman..."
                  list="quick-groups-list"
                  class="text-xs"
                />
                <datalist id="quick-groups-list">
                  <option v-for="g in availableGroups" :key="g" :value="g" />
                </datalist>
                <p v-if="errors.group_name" class="text-[11px] text-rose-500">{{ errors.group_name }}</p>
              </div>

              <div class="space-y-1">
                <Label class="text-xs font-semibold text-slate-700">Maks Pax (Kuota) <span class="text-rose-500">*</span></Label>
                <Input
                  type="number"
                  v-model.number="form.max_pax"
                  min="1"
                  max="50"
                  class="text-xs"
                  required
                />
                <p v-if="errors.max_pax" class="text-[11px] text-rose-500">{{ errors.max_pax }}</p>
              </div>
            </div>

            <!-- Sesi Acara -->
            <div class="space-y-1">
              <Label class="text-xs font-semibold text-slate-700">Sesi Acara</Label>
              <Input
                v-model="form.session_name"
                placeholder="Contoh: Sesi Akad (08.00-10.00) atau Sesi Resepsi..."
                list="quick-sessions-list"
                class="text-xs"
              />
              <datalist id="quick-sessions-list">
                <option v-for="s in availableSessions" :key="s" :value="s" />
              </datalist>
              <p v-if="errors.session_name" class="text-[11px] text-rose-500">{{ errors.session_name }}</p>
            </div>

            <!-- Undangan Fisik / Cetak -->
            <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50/70 p-3">
              <div>
                <Label for="quick_is_physical" class="text-xs font-semibold text-slate-800 cursor-pointer flex items-center gap-1.5">
                  <span>💌</span> Undangan Fisik / Cetak
                </Label>
                <p class="text-[11px] text-slate-400 mt-0.5">
                  Tandai jika tamu ini juga diberikan atau membutuhkan undangan cetak fisik.
                </p>
              </div>
              <input
                id="quick_is_physical"
                type="checkbox"
                v-model="form.is_physical_invitation"
                class="h-4 w-4 rounded border-slate-300 text-purple-600 focus:ring-purple-500 cursor-pointer"
              />
            </div>

            <!-- Catatan -->
            <div class="space-y-1">
              <Label class="text-xs font-semibold text-slate-700">Catatan Internal</Label>
              <Input
                v-model="form.notes"
                placeholder="Catatan meja, VIP, dsb."
                class="text-xs"
              />
              <p v-if="errors.notes" class="text-[11px] text-rose-500">{{ errors.notes }}</p>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
              <Button type="button" variant="outline" class="rounded-xl text-xs" @click="close">
                Batal
              </Button>
              <Button
                type="submit"
                :disabled="isSubmitting"
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
                {{ guest ? 'Simpan Perubahan' : 'Tambah Tamu' }}
              </Button>
            </div>
          </form>
        </div>
      </Transition>
    </div>
  </Transition>
</template>
