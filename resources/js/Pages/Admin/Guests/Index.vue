<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from '@/components/ui/table'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { useConfirm } from '@/Composables/useConfirm'
import { useToast } from '@/Composables/useToast'
import BulkEditModal from '@/Components/Guests/BulkEditModal.vue'
import QuickGuestModal from '@/Components/Guests/QuickGuestModal.vue'

const props = defineProps({
  wedding: { type: Object, required: true },
  guests: { type: [Object, Array], default: () => [] },
  availableGroups: { type: Array, default: () => [] },
  stats: { type: Object, default: () => null },
  filters: { type: Object, default: () => ({}) },
})

const { confirm } = useConfirm()
const toast = useToast()

const guestList = computed(() => {
  if (Array.isArray(props.guests)) return props.guests
  return props.guests?.data || []
})

// Search & Filter State
const searchQuery = ref(props.filters?.search || '')
const filterStatus = ref(props.filters?.status || 'all')
const filterSent = ref(props.filters?.sent || 'all')
const filterGroup = ref(props.filters?.group || 'all')

const copiedId = ref(null)
const isImportModalOpen = ref(false)
const isBulkEditOpen = ref(false)
const isQuickModalOpen = ref(false)
const editingGuest = ref(null)

// Multi-selection state
const selectedGuestIds = ref([])

const groupOptions = computed(() => {
  if (props.availableGroups && props.availableGroups.length) {
    return props.availableGroups
  }
  const groups = new Set()
  guestList.value.forEach(g => {
    if (g.group_name) groups.add(g.group_name)
  })
  return Array.from(groups).sort()
})

const filteredGuests = computed(() => {
  return guestList.value.filter(g => {
    // Search
    if (searchQuery.value.trim()) {
      const q = searchQuery.value.toLowerCase()
      const matchName = (g.name || '').toLowerCase().includes(q)
      const matchPhone = (g.phone_number || '').toLowerCase().includes(q)
      const matchGroup = (g.group_name || '').toLowerCase().includes(q)
      const matchNotes = (g.notes || '').toLowerCase().includes(q)
      if (!matchName && !matchPhone && !matchGroup && !matchNotes) return false
    }

    // Filter Group
    if (filterGroup.value !== 'all') {
      if (filterGroup.value === '_none_') {
        if (g.group_name && g.group_name.trim() !== '') return false
      } else if (g.group_name !== filterGroup.value) {
        return false
      }
    }

    // Filter RSVP
    if (filterStatus.value !== 'all') {
      const status = g.rsvp?.attendance_status || 'pending'
      if (filterStatus.value === 'attending' && status !== 'attending') return false
      if (filterStatus.value === 'declined' && status !== 'declined') return false
      if (filterStatus.value === 'pending' && g.rsvp) return false
    }

    // Filter Sent
    if (filterSent.value === 'sent' && !g.is_invitation_sent) return false
    if (filterSent.value === 'unsent' && g.is_invitation_sent) return false

    return true
  })
})

const hasActiveFilters = computed(() => {
  return searchQuery.value.trim() !== '' || filterStatus.value !== 'all' || filterSent.value !== 'all' || filterGroup.value !== 'all'
})

function clearFilters() {
  searchQuery.value = ''
  filterStatus.value = 'all'
  filterSent.value = 'all'
  filterGroup.value = 'all'
}

function filterByStat(status) {
  if (filterStatus.value === status) {
    filterStatus.value = 'all'
  } else {
    filterStatus.value = status
  }
}

// Selection helpers
const isAllSelected = computed(() => {
  if (!filteredGuests.value.length) return false
  return filteredGuests.value.every(g => selectedGuestIds.value.includes(g.id))
})

const isSomeSelected = computed(() => {
  return filteredGuests.value.some(g => selectedGuestIds.value.includes(g.id)) && !isAllSelected.value
})

function toggleSelectAll() {
  if (isAllSelected.value) {
    const visibleIds = new Set(filteredGuests.value.map(g => g.id))
    selectedGuestIds.value = selectedGuestIds.value.filter(id => !visibleIds.has(id))
  } else {
    const visibleIds = filteredGuests.value.map(g => g.id)
    const set = new Set([...selectedGuestIds.value, ...visibleIds])
    selectedGuestIds.value = Array.from(set)
  }
}

function toggleGuest(id) {
  const idx = selectedGuestIds.value.indexOf(id)
  if (idx !== -1) {
    selectedGuestIds.value.splice(idx, 1)
  } else {
    selectedGuestIds.value.push(id)
  }
}

const selectedGuests = computed(() => {
  return guestList.value.filter(g => selectedGuestIds.value.includes(g.id))
})

// Quick Modal Helpers
function openCreateModal() {
  editingGuest.value = null
  isQuickModalOpen.value = true
}

function openEditModal(guest) {
  editingGuest.value = guest
  isQuickModalOpen.value = true
}

// Personal link & WhatsApp
function getPersonalLink(guest) {
  const origin = typeof window !== 'undefined' ? window.location.origin : ''
  if (guest.short_code) {
    return `${origin}/s/${guest.short_code}`
  }
  return `${origin}/w/${props.wedding.slug}?token=${guest.token}`
}

function copyPersonalLink(guest) {
  const url = getPersonalLink(guest)
  navigator.clipboard.writeText(url)
  copiedId.value = guest.id
  toast.success(`Tautan untuk "${guest.name}" berhasil disalin!`)
  setTimeout(() => {
    copiedId.value = null
  }, 2000)
}

function openWhatsApp(guest) {
  const url = getPersonalLink(guest)
  const couple = props.wedding.cover_subtitle || props.wedding.cover_title || 'Pernikahan Kami'
  const text = encodeURIComponent(
    `Kepada Yth. *${guest.name}*,\n\n` +
    `Tanpa mengurangi rasa hormat, perkenankan kami mengundang Bapak/Ibu/Saudara/i untuk menghadiri acara pernikahan kami:\n\n` +
    `*${couple}*\n\n` +
    `Informasi lengkap & konfirmasi kehadiran (RSVP) dapat diakses melalui tautan undangan personal berikut:\n` +
    `${url}\n\n` +
    `Merupakan suatu kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir dan memberikan doa restu.\n\n` +
    `Terima kasih.`
  )

  const phone = (guest.phone_number || '').replace(/[^0-9]/g, '')
  const waUrl = phone ? `https://wa.me/${phone}?text=${text}` : `https://wa.me/?text=${text}`
  window.open(waUrl, '_blank')
}

// Single Actions
function markSent(guest) {
  router.post(`/weddings/${props.wedding.id}/guests/${guest.id}/mark-sent`, {}, {
    preserveScroll: true,
  })
}

async function deleteGuest(guest) {
  const confirmed = await confirm({
    title: 'Hapus Data Tamu?',
    description: `Apakah Anda yakin ingin menghapus data tamu "${guest.name}"? Data kehadiran (RSVP) dan ucapan terkait juga akan dihapus.`,
    confirmText: 'Hapus Tamu',
    variant: 'danger',
  })

  if (confirmed) {
    router.delete(`/weddings/${props.wedding.id}/guests/${guest.id}`, {
      preserveScroll: true,
      onSuccess: () => {
        selectedGuestIds.value = selectedGuestIds.value.filter(id => id !== guest.id)
      },
    })
  }
}

// Bulk Actions
async function handleBulkDelete() {
  if (!selectedGuests.value.length) return

  const count = selectedGuests.value.length
  const guestNames = selectedGuests.value.map(g => g.name)

  const confirmed = await confirm({
    title: `Hapus ${count} Tamu Terpilih?`,
    description: `Apakah Anda yakin ingin menghapus ${count} tamu yang dipilih secara permanen? Data RSVP dan tautan personal mereka akan dihapus.`,
    confirmText: `Hapus ${count} Tamu`,
    variant: 'danger',
    items: guestNames,
  })

  if (confirmed) {
    router.post(`/weddings/${props.wedding.id}/guests/bulk-delete`, {
      guest_ids: selectedGuestIds.value,
    }, {
      preserveScroll: true,
      onSuccess: () => {
        selectedGuestIds.value = []
      },
    })
  }
}

async function handleBulkMarkSent() {
  if (!selectedGuests.value.length) return

  const count = selectedGuests.value.length
  const confirmed = await confirm({
    title: `Tandai ${count} Undangan Terkirim?`,
    description: `Status pengiriman untuk ${count} tamu yang dipilih akan diperbarui menjadi "Terkirim".`,
    confirmText: 'Tandai Terkirim',
    variant: 'primary',
  })

  if (confirmed) {
    router.post(`/weddings/${props.wedding.id}/guests/bulk-mark-sent`, {
      guest_ids: selectedGuestIds.value,
    }, {
      preserveScroll: true,
      onSuccess: () => {
        selectedGuestIds.value = []
      },
    })
  }
}

function handleBulkCopyLinks() {
  if (!selectedGuests.value.length) return

  const lines = selectedGuests.value.map(g => `${g.name}: ${getPersonalLink(g)}`).join('\n')
  navigator.clipboard.writeText(lines)
  toast.success(`${selectedGuests.value.length} tautan personal berhasil disalin ke clipboard!`)
}

// Import Form
const importForm = useForm({
  file: null,
})

function submitImport() {
  if (!importForm.file) return
  importForm.post(`/weddings/${props.wedding.id}/guests/import`, {
    preserveScroll: true,
    onSuccess: () => {
      isImportModalOpen.value = false
      importForm.reset()
    },
  })
}

// Stats computed
const totalGuests = computed(() => props.stats?.total ?? guestList.value.length)
const totalSent = computed(() => props.stats?.sent ?? guestList.value.filter(g => g.is_invitation_sent).length)
const totalAttending = computed(() => props.stats?.attending ?? guestList.value.filter(g => g.rsvp?.attendance_status === 'attending').length)
const totalDeclined = computed(() => props.stats?.declined ?? guestList.value.filter(g => g.rsvp?.attendance_status === 'declined').length)
const totalConfirmedPax = computed(() => props.stats?.confirmed_pax ?? guestList.value.reduce((acc, g) => acc + (g.rsvp?.attendance_status === 'attending' ? (g.rsvp?.pax_count || 1) : 0), 0))
</script>

<template>
  <Head :title="`Manajemen Tamu - ${wedding.cover_subtitle || wedding.cover_title}`" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <div class="flex items-center gap-2">
            <Link :href="`/weddings/${wedding.id}`" class="text-xs text-emerald-700 hover:underline">&larr; Kembali ke Detail</Link>
          </div>
          <h2 class="font-serif text-3xl font-bold text-emerald-950">Manajemen Tamu Undangan</h2>
          <p class="mt-1 text-sm text-slate-500">{{ wedding.cover_title }} • Kelola daftar penerima &amp; link personal.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <!-- Import CSV Button -->
          <Button
            type="button"
            variant="outline"
            class="rounded-xl border-emerald-300 text-xs font-semibold text-emerald-800 hover:bg-emerald-50 shadow-2xs flex items-center gap-1.5"
            @click="isImportModalOpen = true"
          >
            <span>📥</span> Impor Excel / CSV
          </Button>

          <!-- Export CSV Link -->
          <a :href="`/weddings/${wedding.id}/guests/export`" download>
            <Button
              type="button"
              variant="outline"
              class="rounded-xl border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50 shadow-2xs"
            >
              📤 Ekspor CSV
            </Button>
          </a>

          <!-- Quick Add Guest -->
          <Button
            type="button"
            class="rounded-xl bg-emerald-700 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-800 transition flex items-center gap-1.5"
            @click="openCreateModal"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Tamu
          </Button>
        </div>
      </div>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
        <!-- Quick Metric Stats with Interactive Click Filtering -->
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
          <!-- Total Tamu -->
          <button
            type="button"
            class="rounded-2xl border bg-white p-4 text-left shadow-sm transition hover:border-emerald-300"
            :class="filterStatus === 'all' ? 'border-emerald-400 ring-2 ring-emerald-100' : 'border-slate-200'"
            @click="filterStatus = 'all'"
          >
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Tamu</p>
            <p class="font-serif text-2xl font-bold text-emerald-950 mt-1">{{ totalGuests }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">{{ totalSent }} terkirim</p>
          </button>

          <!-- Hadir -->
          <button
            type="button"
            class="rounded-2xl border bg-white p-4 text-left shadow-sm transition hover:border-emerald-300"
            :class="filterStatus === 'attending' ? 'border-emerald-500 ring-2 ring-emerald-200 bg-emerald-50/20' : 'border-emerald-100'"
            @click="filterByStat('attending')"
          >
            <div class="flex items-center justify-between">
              <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Hadir</p>
              <span v-if="filterStatus === 'attending'" class="rounded-full bg-emerald-100 px-1.5 py-0.5 text-[9px] font-bold text-emerald-700">Aktif</span>
            </div>
            <p class="font-serif text-2xl font-bold text-emerald-700 mt-1">{{ totalAttending }}</p>
            <p class="text-[11px] text-emerald-600 mt-0.5">{{ totalConfirmedPax }} Total Pax</p>
          </button>

          <!-- Tidak Hadir -->
          <button
            type="button"
            class="rounded-2xl border bg-white p-4 text-left shadow-sm transition hover:border-rose-300"
            :class="filterStatus === 'declined' ? 'border-rose-500 ring-2 ring-rose-200 bg-rose-50/20' : 'border-slate-200'"
            @click="filterByStat('declined')"
          >
            <div class="flex items-center justify-between">
              <p class="text-xs font-semibold uppercase tracking-wider text-rose-500">Tidak Hadir</p>
              <span v-if="filterStatus === 'declined'" class="rounded-full bg-rose-100 px-1.5 py-0.5 text-[9px] font-bold text-rose-700">Aktif</span>
            </div>
            <p class="font-serif text-2xl font-bold text-rose-700 mt-1">{{ totalDeclined }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Berhalangan</p>
          </button>

          <!-- Belum RSVP -->
          <button
            type="button"
            class="rounded-2xl border bg-white p-4 text-left shadow-sm transition hover:border-amber-300"
            :class="filterStatus === 'pending' ? 'border-amber-500 ring-2 ring-amber-200 bg-amber-50/20' : 'border-slate-200'"
            @click="filterByStat('pending')"
          >
            <div class="flex items-center justify-between">
              <p class="text-xs font-semibold uppercase tracking-wider text-amber-500">Belum RSVP</p>
              <span v-if="filterStatus === 'pending'" class="rounded-full bg-amber-100 px-1.5 py-0.5 text-[9px] font-bold text-amber-700">Aktif</span>
            </div>
            <p class="font-serif text-2xl font-bold text-amber-700 mt-1">{{ totalGuests - totalAttending - totalDeclined }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Menunggu konfirmasi</p>
          </button>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm space-y-3">
          <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <!-- Search -->
            <div class="relative w-full sm:w-72">
              <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </span>
              <Input
                v-model="searchQuery"
                placeholder="Cari nama, grup, telepon, catatan..."
                class="pl-9 text-xs"
              />
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap items-center gap-2">
              <!-- Group Filter -->
              <select
                v-model="filterGroup"
                class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs focus:border-emerald-500 focus:outline-none"
              >
                <option value="all">Semua Kategori/Grup</option>
                <option v-for="group in groupOptions" :key="group" :value="group">
                  {{ group }}
                </option>
                <option value="_none_">Tanpa Kategori</option>
              </select>

              <!-- RSVP Status Filter -->
              <select
                v-model="filterStatus"
                class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs focus:border-emerald-500 focus:outline-none"
              >
                <option value="all">Semua Status RSVP</option>
                <option value="attending">Hadir</option>
                <option value="declined">Tidak Hadir</option>
                <option value="pending">Belum Konfirmasi</option>
              </select>

              <!-- Sent Status Filter -->
              <select
                v-model="filterSent"
                class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs focus:border-emerald-500 focus:outline-none"
              >
                <option value="all">Semua Status Kirim</option>
                <option value="sent">Sudah Dikirim</option>
                <option value="unsent">Belum Dikirim</option>
              </select>

              <!-- Reset filter button -->
              <button
                v-if="hasActiveFilters"
                type="button"
                class="rounded-xl border border-rose-200 bg-rose-50 px-2.5 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100 transition"
                @click="clearFilters"
              >
                Reset Filter
              </button>
            </div>
          </div>
        </div>

        <!-- Guests Table -->
        <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm pb-12">
          <div class="p-6">
            <div v-if="!filteredGuests.length" class="py-12 text-center">
              <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                👥
              </div>
              <p class="text-sm font-medium text-slate-700">Belum ada tamu ditemukan.</p>
              <p class="mt-1 text-xs text-slate-400">
                {{ hasActiveFilters ? 'Coba sesuaikan filter pencarian di atas.' : 'Klik "Tambah Tamu" atau "Impor CSV" untuk menambahkan data tamu undangan baru.' }}
              </p>
            </div>

            <div v-else class="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow class="bg-slate-50/50">
                    <!-- Checkbox Master Column -->
                    <TableHead class="w-10 px-3">
                      <input
                        type="checkbox"
                        :checked="isAllSelected"
                        :indeterminate.prop="isSomeSelected"
                        @change="toggleSelectAll"
                        class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer"
                        title="Pilih Semua Tamu"
                      />
                    </TableHead>
                    <TableHead class="font-semibold text-slate-700">Nama &amp; Grup</TableHead>
                    <TableHead class="font-semibold text-slate-700">Kontak</TableHead>
                    <TableHead class="font-semibold text-slate-700">Status Undangan</TableHead>
                    <TableHead class="font-semibold text-slate-700">Konfirmasi Kehadiran (RSVP)</TableHead>
                    <TableHead class="font-semibold text-slate-700">Maks Pax</TableHead>
                    <TableHead class="text-right font-semibold text-slate-700">Aksi &amp; Kirim</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow
                    v-for="guest in filteredGuests"
                    :key="guest.id"
                    class="transition"
                    :class="selectedGuestIds.includes(guest.id) ? 'bg-emerald-50/60 hover:bg-emerald-50/90' : 'hover:bg-slate-50/80'"
                  >
                    <!-- Row Checkbox -->
                    <TableCell class="w-10 px-3">
                      <input
                        type="checkbox"
                        :checked="selectedGuestIds.includes(guest.id)"
                        @change="toggleGuest(guest.id)"
                        class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer"
                      />
                    </TableCell>

                    <!-- Name & Group -->
                    <TableCell>
                      <div>
                        <Link :href="`/weddings/${wedding.id}/guests/${guest.id}`" class="font-bold text-slate-900 hover:text-emerald-700">
                          {{ guest.name }}
                        </Link>
                        <div class="flex items-center gap-1.5 mt-0.5">
                          <span v-if="guest.group_name" class="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold text-slate-600">
                            {{ guest.group_name }}
                          </span>
                          <span v-if="guest.notes" class="text-[10px] text-slate-400 truncate max-w-xs" :title="guest.notes">
                            💬 {{ guest.notes }}
                          </span>
                        </div>
                      </div>
                    </TableCell>

                    <!-- Contact -->
                    <TableCell class="text-xs text-slate-600 font-mono">
                      {{ guest.phone_number || '-' }}
                    </TableCell>

                    <!-- Status Undangan (Sent) -->
                    <TableCell>
                      <div class="flex items-center gap-1.5">
                        <span
                          class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold"
                          :class="guest.is_invitation_sent ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600'"
                        >
                          <span class="h-1.5 w-1.5 rounded-full" :class="guest.is_invitation_sent ? 'bg-emerald-600' : 'bg-slate-400'" />
                          {{ guest.is_invitation_sent ? 'Terkirim' : 'Belum Dikirim' }}
                        </span>
                      </div>
                    </TableCell>

                    <!-- RSVP Status -->
                    <TableCell>
                      <div v-if="guest.rsvp" class="space-y-0.5">
                        <span
                          class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                          :class="guest.rsvp.attendance_status === 'attending' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'"
                        >
                          {{ guest.rsvp.attendance_status === 'attending' ? `✓ Hadir (${guest.rsvp.pax_count} orang)` : '✕ Berhalangan' }}
                        </span>
                      </div>
                      <span v-else class="text-xs text-slate-400 italic">Belum konfirmasi</span>
                    </TableCell>

                    <!-- Max Pax -->
                    <TableCell class="text-xs text-slate-700 font-medium">
                      {{ guest.max_pax }} Pax
                    </TableCell>

                    <!-- Actions -->
                    <TableCell class="text-right">
                      <div class="flex items-center justify-end gap-1.5">
                        <!-- WhatsApp Share -->
                        <Button
                          type="button"
                          size="sm"
                          class="h-7 rounded-lg bg-emerald-600 px-2 text-[11px] font-semibold text-white hover:bg-emerald-700 flex items-center gap-1 shadow-2xs"
                          @click="openWhatsApp(guest)"
                          title="Kirim undangan via WhatsApp"
                        >
                          <span>WA</span>
                        </Button>

                        <!-- Copy Link -->
                        <Button
                          type="button"
                          size="sm"
                          variant="outline"
                          class="h-7 rounded-lg px-2 text-[11px] font-medium text-slate-700 hover:bg-slate-100 shadow-2xs"
                          :class="{ 'border-emerald-500 text-emerald-700 bg-emerald-50': copiedId === guest.id }"
                          @click="copyPersonalLink(guest)"
                          title="Salin tautan personal"
                        >
                          {{ copiedId === guest.id ? 'Tersalin!' : 'Salin Link' }}
                        </Button>

                        <!-- Mark Sent -->
                        <Button
                          v-if="!guest.is_invitation_sent"
                          type="button"
                          size="sm"
                          variant="ghost"
                          class="h-7 rounded-lg px-1.5 text-[11px] font-medium text-emerald-700 hover:bg-emerald-50"
                          @click="markSent(guest)"
                          title="Tandai sudah dikirim"
                        >
                          ✓ Kirim
                        </Button>

                        <!-- Quick Edit -->
                        <Button
                          type="button"
                          size="sm"
                          variant="ghost"
                          class="h-7 w-7 p-0 rounded-lg text-slate-500 hover:text-slate-900"
                          @click="openEditModal(guest)"
                          title="Quick Edit Tamu"
                        >
                          ✏️
                        </Button>

                        <!-- Delete -->
                        <Button
                          type="button"
                          size="sm"
                          variant="ghost"
                          class="h-7 w-7 p-0 rounded-lg text-rose-500 hover:bg-rose-50 hover:text-rose-700"
                          @click="deleteGuest(guest)"
                          title="Hapus Tamu"
                        >
                          🗑
                        </Button>
                      </div>
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Floating Sticky Bulk Action Toolbar -->
    <Transition
      enter-active-class="transform ease-out duration-200 transition"
      enter-from-class="translate-y-12 opacity-0"
      enter-to-class="translate-y-0 opacity-100"
      leave-active-class="transform ease-in duration-150 transition"
      leave-from-class="translate-y-0 opacity-100"
      leave-to-class="translate-y-12 opacity-0"
    >
      <div
        v-if="selectedGuestIds.length > 0"
        class="fixed bottom-6 inset-x-0 z-40 mx-auto max-w-2xl px-4"
      >
        <div class="flex items-center justify-between gap-3 rounded-2xl bg-slate-900/95 p-3.5 shadow-2xl backdrop-blur-md text-white border border-slate-700/60 ring-1 ring-white/10">
          <div class="flex items-center gap-2.5 pl-2">
            <span class="inline-flex h-6 items-center justify-center rounded-full bg-emerald-500/20 px-2.5 text-xs font-bold text-emerald-400">
              {{ selectedGuestIds.length }}
            </span>
            <span class="text-xs font-semibold text-slate-200">
              Tamu Dipilih
            </span>
          </div>

          <div class="flex items-center gap-2">
            <!-- Bulk Edit Button -->
            <button
              type="button"
              class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-500 transition shadow-sm"
              @click="isBulkEditOpen = true"
            >
              <span>⚙️</span>
              Bulk Edit
            </button>

            <!-- Bulk Mark Sent -->
            <button
              type="button"
              class="inline-flex items-center gap-1 rounded-xl bg-slate-800 px-3 py-1.5 text-xs font-medium text-slate-200 hover:bg-slate-700 hover:text-white transition"
              @click="handleBulkMarkSent"
            >
              <span>✓</span>
              Tandai Terkirim
            </button>

            <!-- Bulk Copy Links -->
            <button
              type="button"
              class="inline-flex items-center gap-1 rounded-xl bg-slate-800 px-3 py-1.5 text-xs font-medium text-slate-200 hover:bg-slate-700 hover:text-white transition"
              @click="handleBulkCopyLinks"
              title="Salin seluruh link personal terpilih"
            >
              <span>🔗</span>
              Salin Link
            </button>

            <!-- Bulk Delete -->
            <button
              type="button"
              class="inline-flex items-center gap-1 rounded-xl bg-rose-600/90 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-600 transition shadow-sm"
              @click="handleBulkDelete"
            >
              <span>🗑</span>
              Hapus
            </button>

            <!-- Deselect -->
            <button
              type="button"
              class="rounded-xl px-2 py-1.5 text-xs text-slate-400 hover:text-white transition"
              @click="selectedGuestIds = []"
              title="Batal Pilihan"
            >
              &times;
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Bulk Edit Modal Component -->
    <BulkEditModal
      v-model:open="isBulkEditOpen"
      :selected-guests="selectedGuests"
      :wedding-id="wedding.id"
      :available-groups="groupOptions"
      @success="selectedGuestIds = []"
    />

    <!-- Quick Guest Modal Component (Create & Quick Edit) -->
    <QuickGuestModal
      v-model:open="isQuickModalOpen"
      :guest="editingGuest"
      :wedding-id="wedding.id"
      :available-groups="groupOptions"
    />

    <!-- Modal Impor Tamu Excel / CSV -->
    <div
      v-if="isImportModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm"
      @click.self="isImportModalOpen = false"
    >
      <div class="w-full max-w-xl rounded-2xl bg-white p-6 shadow-2xl space-y-4 border border-slate-100">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <div class="flex items-center gap-2.5">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-800 text-base">
              📊
            </div>
            <div>
              <h3 class="font-serif text-lg font-bold text-emerald-950">Impor Data Tamu Undangan</h3>
              <p class="text-xs text-slate-500">Unggah file Excel (.xlsx) atau CSV untuk menambahkan data sekaligus.</p>
            </div>
          </div>
          <button @click="isImportModalOpen = false" class="text-slate-400 hover:text-slate-600 text-xl font-light">&times;</button>
        </div>

        <!-- Download Template Section -->
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50/60 p-4 space-y-2.5">
          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="text-xs font-bold text-emerald-950 flex items-center gap-1.5">
                <span>📄</span> Belum memiliki template Excel?
              </p>
              <p class="mt-1 text-[11px] text-emerald-800 leading-relaxed">
                Unduh template resmi berformat tabel siap pakai. Kolom nomor telepon sudah diformat khusus agar angka <code>0</code> di awal tidak hilang.
              </p>
            </div>
          </div>

          <div class="flex flex-wrap items-center gap-2 pt-1">
            <a :href="`/weddings/${wedding.id}/guests/template`" download>
              <Button
                type="button"
                size="sm"
                class="rounded-xl bg-emerald-700 px-3.5 text-xs font-semibold text-white hover:bg-emerald-800 shadow-sm flex items-center gap-1.5"
              >
                <span>📥</span> Unduh Template Excel (.xlsx)
              </Button>
            </a>
            <a :href="`/weddings/${wedding.id}/guests/template?format=csv`" download>
              <Button
                type="button"
                size="sm"
                variant="outline"
                class="rounded-xl border-emerald-300 bg-white text-xs font-medium text-emerald-800 hover:bg-emerald-100/50 flex items-center gap-1"
              >
                <span>📄</span> Format CSV
              </Button>
            </a>
          </div>
        </div>

        <!-- Form Upload -->
        <form @submit.prevent="submitImport" class="space-y-4 pt-1">
          <div class="space-y-1.5">
            <label class="block text-xs font-semibold text-slate-700">Pilih File Excel / CSV Hasil Pengisian</label>
            <div class="relative flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/70 p-5 hover:border-emerald-400 hover:bg-emerald-50/20 transition cursor-pointer">
              <input
                type="file"
                accept=".xlsx,.xls,.csv,.txt,.json"
                @change="importForm.file = $event.target.files[0]"
                class="absolute inset-0 h-full w-full opacity-0 cursor-pointer"
                required
              />
              <div class="text-center space-y-1 pointer-events-none">
                <div class="text-2xl">📁</div>
                <p class="text-xs font-semibold text-slate-800">
                  {{ importForm.file ? importForm.file.name : 'Klik atau seret file Excel/CSV ke sini' }}
                </p>
                <p class="text-[11px] text-slate-400">
                  {{ importForm.file ? `${(importForm.file.size / 1024).toFixed(1)} KB` : 'Format didukung: .xlsx, .xls, .csv, .json (Maks 5MB)' }}
                </p>
              </div>
            </div>
            <p v-if="importForm.errors.file" class="text-xs text-rose-500 font-medium">{{ importForm.errors.file }}</p>
          </div>

          <div class="rounded-xl bg-slate-100/70 p-3 text-[11px] text-slate-600 space-y-1">
            <p class="font-semibold text-slate-700">Petunjuk Kolom Template:</p>
            <p>1. <strong>Nama Tamu:</strong> Wajib diisi (contoh: <code>Bpk. H. Rahmat & Keluarga</code>).</p>
            <p>2. <strong>Nomor WhatsApp:</strong> Nomor HP aktif (contoh: <code>08123456789</code>).</p>
            <p>3. <strong>Kategori / Grup:</strong> Contoh: <code>Keluarga</code>, <code>VIP</code>, <code>Teman Kantor</code>.</p>
            <p>4. <strong>Maks Pax:</strong> Jumlah kuota orang (default <code>2</code>).</p>
          </div>

          <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
            <Button type="button" variant="outline" class="rounded-xl text-xs" @click="isImportModalOpen = false">
              Batal
            </Button>
            <Button
              type="submit"
              :disabled="importForm.processing || !importForm.file"
              class="rounded-xl bg-emerald-700 px-5 text-xs font-semibold text-white hover:bg-emerald-800 shadow-sm"
            >
              <svg
                v-if="importForm.processing"
                class="mr-1.5 h-3.5 w-3.5 animate-spin text-white"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
              </svg>
              {{ importForm.processing ? 'Mengunggah...' : 'Mulai Impor Tamu' }}
            </Button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>