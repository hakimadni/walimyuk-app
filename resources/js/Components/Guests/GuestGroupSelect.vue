<script setup>
import { ref, computed, nextTick } from 'vue'
import {
  PopoverRoot,
  PopoverTrigger,
  PopoverPortal,
  PopoverContent,
} from 'reka-ui'
import { useToast } from '@/Composables/useToast'

const props = defineProps({
  guest: { type: Object, required: true },
  weddingId: { type: [Number, String], required: true },
  availableGroups: { type: Array, default: () => [] },
})

const emit = defineEmits(['updated', 'newGroupAdded'])

const toast = useToast()
const isOpen = ref(false)
const searchQuery = ref('')
const searchInputRef = ref(null)
const isSaving = ref(false)

// Focus search input when popover opens
function onOpenChange(open) {
  isOpen.value = open
  if (open) {
    searchQuery.value = ''
    nextTick(() => {
      searchInputRef.value?.focus()
    })
  }
}

// Clean trimmed query
const cleanQuery = computed(() => searchQuery.value.trim())

// Filtered existing groups
const filteredGroups = computed(() => {
  if (!cleanQuery.value) {
    return props.availableGroups
  }
  const q = cleanQuery.value.toLowerCase()
  return props.availableGroups.filter(g => g.toLowerCase().includes(q))
})

// Check if exact match already exists in available groups
const isExactMatch = computed(() => {
  if (!cleanQuery.value) return true
  const q = cleanQuery.value.toLowerCase()
  return props.availableGroups.some(g => g.toLowerCase() === q)
})

// Submit group change
function selectGroup(newGroup) {
  const finalGroup = newGroup ? newGroup.trim() : null

  // If selecting the exact same group, just close
  if (finalGroup === (props.guest.group_name || null)) {
    isOpen.value = false
    return
  }

  isSaving.value = true
  const oldGroup = props.guest.group_name

  // Optimistic update
  props.guest.group_name = finalGroup
  isOpen.value = false

  // If this is a newly created option, notify parent to add to availableGroups
  if (finalGroup && !props.availableGroups.includes(finalGroup)) {
    emit('newGroupAdded', finalGroup)
  }

  window.axios
    .patch(`/weddings/${props.weddingId}/guests/${props.guest.id}/group`, {
      group_name: finalGroup,
    })
    .then(() => {
      isSaving.value = false
      emit('updated', finalGroup)
      toast.success(
        finalGroup
          ? `Grup untuk "${props.guest.name}" diatur ke "${finalGroup}".`
          : `Grup untuk "${props.guest.name}" dikosongkan.`
      )
    })
    .catch((err) => {
      isSaving.value = false
      // Revert optimistic update
      props.guest.group_name = oldGroup
      const msg = err.response?.data?.message || err.message || 'Terjadi kesalahan'
      toast.error('Gagal memperbarui grup: ' + msg)
    })
}

function handleAddNew() {
  if (!cleanQuery.value) return
  selectGroup(cleanQuery.value)
}

function handleKeydown(e) {
  if (e.key === 'Enter') {
    e.preventDefault()
    if (!isExactMatch.value && cleanQuery.value) {
      handleAddNew()
    } else if (filteredGroups.value.length === 1) {
      selectGroup(filteredGroups.value[0])
    }
  }
}
</script>

<template>
  <PopoverRoot :open="isOpen" @update:open="onOpenChange">
    <!-- Popover Trigger Button -->
    <PopoverTrigger as-child>
      <button
        type="button"
        :disabled="isSaving"
        class="group inline-flex items-center gap-1.5 rounded-xl px-2.5 py-1 text-xs font-medium transition cursor-pointer select-none text-left max-w-[210px]"
        :class="[
          guest.group_name
            ? 'bg-blue-50/80 text-blue-900 border border-blue-200/90 hover:bg-blue-100 hover:border-blue-300 shadow-2xs'
            : 'bg-white text-slate-500 border border-dashed border-slate-300 hover:border-emerald-400 hover:bg-emerald-50/40 hover:text-emerald-800',
          isSaving ? 'opacity-60 cursor-wait' : ''
        ]"
        :title="guest.group_name ? `Grup: ${guest.group_name} (Klik untuk ubah)` : 'Klik untuk menetapkan grup'"
      >
        <!-- Icon: Spinner if saving, Users if set, Plus if unset -->
        <svg
          v-if="isSaving"
          class="h-3 w-3 animate-spin text-emerald-600 shrink-0"
          fill="none"
          viewBox="0 0 24 24"
        >
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
        </svg>
        <span v-else-if="guest.group_name" class="text-[11px] shrink-0">👥</span>
        <span v-else class="text-[11px] text-slate-400 group-hover:text-emerald-600 shrink-0">＋</span>

        <!-- Label -->
        <span class="truncate font-medium">
          {{ guest.group_name || 'Pilih Grup' }}
        </span>

        <!-- Chevron Down Arrow -->
        <svg
          class="h-3 w-3 shrink-0 text-slate-400 transition group-hover:text-slate-700 ml-auto"
          :class="{ 'rotate-180': isOpen }"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
          stroke-width="2"
        >
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
      </button>
    </PopoverTrigger>

    <!-- Popover Portal to avoid clipping in tables -->
    <PopoverPortal>
      <PopoverContent
        side="bottom"
        align="start"
        :side-offset="6"
        class="z-50 w-72 rounded-2xl border border-slate-200 bg-white p-2.5 shadow-2xl outline-none ring-1 ring-black/5 anim-fade-in"
      >
        <!-- Search & Creatable Input Box -->
        <div class="relative mb-2">
          <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 text-slate-400 pointer-events-none text-xs">
            🔍
          </span>
          <input
            ref="searchInputRef"
            v-model="searchQuery"
            type="text"
            placeholder="Cari atau ketik nama grup..."
            class="w-full rounded-xl border border-slate-200 bg-slate-50/80 py-1.5 pl-8 pr-7 text-xs text-slate-800 placeholder:text-slate-400 focus:border-emerald-500 focus:bg-white focus:outline-none transition"
            @keydown="handleKeydown"
          />
          <button
            v-if="searchQuery"
            type="button"
            class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400 hover:text-slate-600 text-xs"
            @click="searchQuery = ''; searchInputRef?.focus()"
          >
            &times;
          </button>
        </div>

        <!-- Options Container -->
        <div class="max-h-56 overflow-y-auto space-y-1 pr-0.5">
          <!-- Add New Option Item (Select2 style creatable) -->
          <button
            v-if="cleanQuery && !isExactMatch"
            type="button"
            class="w-full rounded-xl bg-emerald-50 px-3 py-2 text-left text-xs font-semibold text-emerald-800 hover:bg-emerald-100 hover:text-emerald-950 transition flex items-center gap-2 border border-emerald-200/80 group"
            @click="handleAddNew"
          >
            <span class="flex h-5 w-5 items-center justify-center rounded-lg bg-emerald-600 text-white text-[10px] font-bold group-hover:bg-emerald-700 shrink-0">
              ＋
            </span>
            <div class="truncate">
              <span class="text-[10px] text-emerald-600 block leading-tight font-normal">Buat Opsi Grup Baru:</span>
              <span class="font-bold text-emerald-900 truncate block">"{{ cleanQuery }}"</span>
            </div>
          </button>

          <!-- List of Existing Filtered Groups -->
          <div v-if="filteredGroups.length > 0" class="space-y-0.5">
            <p v-if="cleanQuery && !isExactMatch" class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 px-2 pt-1 pb-0.5">
              Grup Yang Tersedia
            </p>
            <button
              v-for="group in filteredGroups"
              :key="group"
              type="button"
              class="w-full rounded-xl px-2.5 py-1.5 text-left text-xs transition flex items-center justify-between gap-2 group cursor-pointer"
              :class="[
                guest.group_name === group
                  ? 'bg-emerald-50/80 font-bold text-emerald-900'
                  : 'text-slate-700 hover:bg-slate-100'
              ]"
              @click="selectGroup(group)"
            >
              <div class="flex items-center gap-2 truncate">
                <span class="text-[11px] text-slate-400 group-hover:text-blue-600 shrink-0">👥</span>
                <span class="truncate">{{ group }}</span>
              </div>
              <span v-if="guest.group_name === group" class="text-emerald-600 font-bold text-xs shrink-0">
                ✓
              </span>
            </button>
          </div>

          <!-- Empty State when nothing found -->
          <div
            v-else-if="!cleanQuery"
            class="py-3 px-2 text-center text-slate-400 text-xs italic"
          >
            Belum ada grup yang tersimpan.<br>
            <span class="text-[11px] text-emerald-700 not-italic font-medium">Ketik di kolom atas untuk membuat grup baru.</span>
          </div>

          <div
            v-else-if="isExactMatch && filteredGroups.length === 0"
            class="py-2 px-2 text-center text-slate-400 text-xs italic"
          >
            Tidak ada hasil yang cocok.
          </div>
        </div>

        <!-- Clear / Reset Group Footer -->
        <div v-if="guest.group_name" class="border-t border-slate-100 pt-1.5 mt-1.5">
          <button
            type="button"
            class="w-full rounded-xl px-2.5 py-1.5 text-left text-xs text-rose-600 hover:bg-rose-50 hover:text-rose-700 transition flex items-center gap-2 font-medium"
            @click="selectGroup(null)"
          >
            <span class="text-[11px]">✕</span>
            <span>Kosongkan / Hapus Grup</span>
          </button>
        </div>
      </PopoverContent>
    </PopoverPortal>
  </PopoverRoot>
</template>
