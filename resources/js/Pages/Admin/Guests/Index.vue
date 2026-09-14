<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from '@/components/ui/table'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { useConfirm } from '@/Composables/useConfirm'
import { useToast } from '@/Composables/useToast'
import BulkEditModal from '@/Components/Guests/BulkEditModal.vue'
import QuickGuestModal from '@/Components/Guests/QuickGuestModal.vue'
import GuestSessionSelect from '@/Components/Guests/GuestSessionSelect.vue'

const props = defineProps({
  wedding: { type: Object, required: true },
  guests: { type: [Object, Array], default: () => [] },
  availableGroups: { type: Array, default: () => [] },
  availableSessions: { type: Array, default: () => [] },
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
const filterPhysical = ref(props.filters?.physical || 'all')
const filterGroup = ref(props.filters?.group || 'all')
const filterSession = ref(props.filters?.session || 'all')

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

const extraSessions = ref([])

const sessionOptions = computed(() => {
  const set = new Set([...props.availableSessions, ...extraSessions.value])
  guestList.value.forEach(g => {
    if (g.session_name) set.add(g.session_name)
  })
  return Array.from(set).filter(Boolean).sort()
})

function handleNewSessionAdded(newSession) {
  if (newSession && !extraSessions.value.includes(newSession)) {
    extraSessions.value.push(newSession)
  }
}

const filteredGuests = computed(() => {
  return guestList.value.filter(g => {
    // Search
    if (searchQuery.value.trim()) {
      const q = searchQuery.value.toLowerCase()
      const matchName = (g.name || '').toLowerCase().includes(q)
      const matchPhone = (g.phone_number || '').toLowerCase().includes(q)
      const matchGroup = (g.group_name || '').toLowerCase().includes(q)
      const matchSession = (g.session_name || '').toLowerCase().includes(q)
      const matchNotes = (g.notes || '').toLowerCase().includes(q)
      if (!matchName && !matchPhone && !matchGroup && !matchSession && !matchNotes) return false
    }

    // Filter Group
    if (filterGroup.value !== 'all') {
      if (filterGroup.value === '_none_') {
        if (g.group_name && g.group_name.trim() !== '') return false
      } else if (g.group_name !== filterGroup.value) {
        return false
      }
    }

    // Filter Session
    if (filterSession.value !== 'all') {
      if (filterSession.value === '_none_') {
        if (g.session_name && g.session_name.trim() !== '') return false
      } else if (g.session_name !== filterSession.value) {
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

    // Filter Physical
    if (filterPhysical.value !== 'all') {
      if (filterPhysical.value === 'physical' && !g.is_physical_invitation) return false
      if (filterPhysical.value === 'digital' && g.is_physical_invitation) return false
    }

    return true
  })
})

const hasActiveFilters = computed(() => {
  return searchQuery.value.trim() !== '' || filterStatus.value !== 'all' || filterSent.value !== 'all' || filterPhysical.value !== 'all' || filterGroup.value !== 'all' || filterSession.value !== 'all'
})

function clearFilters() {
  searchQuery.value = ''
  filterStatus.value = 'all'
  filterSent.value = 'all'
  filterPhysical.value = 'all'
  filterGroup.value = 'all'
  filterSession.value = 'all'
}

const togglingPhysicalId = ref(null)

async function togglePhysical(guest) {
  togglingPhysicalId.value = guest.id
  const oldVal = guest.is_physical_invitation
  const newVal = !oldVal
  guest.is_physical_invitation = newVal

  try {
    await window.axios.patch(`/weddings/${props.wedding.id}/guests/${guest.id}/physical`, {
      is_physical_invitation: newVal,
    })
    toast.success(
      newVal
        ? `Undangan fisik untuk "${guest.name}" diaktifkan.`
        : `Undangan fisik untuk "${guest.name}" dinonaktifkan.`
    )
  } catch (err) {
    guest.is_physical_invitation = oldVal
    const msg = err.response?.data?.message || err.message || 'Terjadi kesalahan'
    toast.error('Gagal memperbarui status undangan fisik: ' + msg)
  } finally {
    togglingPhysicalId.value = null
  }
}

function filterByStat(status) {
  if (filterStatus.value === status) {
    filterStatus.value = 'all'
  } else {
    filterStatus.value = status
  }
}

// Pagination State
const getInitialPage = () => {
  if (typeof window === 'undefined') return 1
  const params = new URLSearchParams(window.location.search)
  const p = parseInt(params.get('page'))
  return !isNaN(p) && p > 0 ? p : 1
}

const getInitialPerPage = () => {
  if (typeof window === 'undefined') return 25
  const params = new URLSearchParams(window.location.search)
  const pp = params.get('per_page')
  if (pp === 'all') return 'all'
  const n = parseInt(pp)
  return [10, 25, 50, 100].includes(n) ? n : 25
}

const currentPage = ref(getInitialPage())
const perPage = ref(getInitialPerPage())

const totalFiltered = computed(() => filteredGuests.value.length)

const totalPages = computed(() => {
  if (perPage.value === 'all') return 1
  return Math.max(1, Math.ceil(totalFiltered.value / Number(perPage.value)))
})

const paginatedGuests = computed(() => {
  if (perPage.value === 'all') {
    return filteredGuests.value
  }
  const size = Number(perPage.value)
  const start = (currentPage.value - 1) * size
  return filteredGuests.value.slice(start, start + size)
})

const paginationFrom = computed(() => {
  if (totalFiltered.value === 0) return 0
  if (perPage.value === 'all') return 1
  return (currentPage.value - 1) * Number(perPage.value) + 1
})

const paginationTo = computed(() => {
  if (totalFiltered.value === 0) return 0
  if (perPage.value === 'all') return totalFiltered.value
  return Math.min(currentPage.value * Number(perPage.value), totalFiltered.value)
})

function syncUrlParams() {
  if (typeof window === 'undefined') return
  const url = new URL(window.location.href)
  if (currentPage.value > 1) {
    url.searchParams.set('page', currentPage.value)
  } else {
    url.searchParams.delete('page')
  }
  if (perPage.value !== 25) {
    url.searchParams.set('per_page', perPage.value)
  } else {
    url.searchParams.delete('per_page')
  }
  window.history.replaceState({}, '', url.toString())
}

watch([currentPage, perPage], () => {
  syncUrlParams()
})

watch(totalPages, (newTotal) => {
  if (currentPage.value > newTotal && newTotal > 0) {
    currentPage.value = newTotal
  }
})

// Reset to page 1 whenever filters change
watch(
  [searchQuery, filterStatus, filterSent, filterPhysical, filterGroup, filterSession],
  () => {
    currentPage.value = 1
  }
)

function goToPage(page) {
  if (page < 1 || page > totalPages.value) return
  currentPage.value = page
}

// Generate smart pagination page numbers (e.g. [1, 2, 3, '...', 10])
const visiblePageNumbers = computed(() => {
  const total = totalPages.value
  const current = currentPage.value
  if (total <= 7) {
    return Array.from({ length: total }, (_, i) => i + 1)
  }

  const pages = []
  pages.push(1)

  if (current > 3) {
    pages.push('...')
  }

  const start = Math.max(2, current - 1)
  const end = Math.min(total - 1, current + 1)

  for (let i = start; i <= end; i++) {
    pages.push(i)
  }

  if (current < total - 2) {
    pages.push('...')
  }

  pages.push(total)
  return pages
})

// Selection helpers (based on current page)
const isAllSelected = computed(() => {
  if (!paginatedGuests.value.length) return false
  return paginatedGuests.value.every(g => selectedGuestIds.value.includes(g.id))
})

const isSomeSelected = computed(() => {
  return paginatedGuests.value.some(g => selectedGuestIds.value.includes(g.id)) && !isAllSelected.value
})

function toggleSelectAll() {
  const pageIds = paginatedGuests.value.map(g => g.id)
  if (isAllSelected.value) {
    const pageIdSet = new Set(pageIds)
    selectedGuestIds.value = selectedGuestIds.value.filter(id => !pageIdSet.has(id))
  } else {
    const set = new Set([...selectedGuestIds.value, ...pageIds])
    selectedGuestIds.value = Array.from(set)
  }
}

function selectAllFiltered() {
  selectedGuestIds.value = filteredGuests.value.map(g => g.id)
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

// Quick Modal Helpers (Create new guest)
function openCreateModal() {
  editingGuest.value = null
  isQuickModalOpen.value = true
}

// Inline Row Editing State & Methods
const editingRowId = ref(null)
const isSavingRow = ref(false)
const rowForm = ref({
  name: '',
  phone_number: '',
  group_name: '',
  session_name: '',
  max_pax: 1,
  is_physical_invitation: false,
  notes: '',
})

function startInlineEdit(guest) {
  editingRowId.value = guest.id
  rowForm.value = {
    name: guest.name || '',
    phone_number: guest.phone_number || '',
    group_name: guest.group_name || '',
    session_name: guest.session_name || '',
    max_pax: guest.max_pax || 1,
    is_physical_invitation: Boolean(guest.is_physical_invitation),
    notes: guest.notes || '',
  }
}

function cancelInlineEdit() {
  editingRowId.value = null
}

async function saveInlineEdit(guest) {
  if (!rowForm.value.name || !rowForm.value.name.trim()) {
    toast.error('Nama tamu wajib diisi.')
    return
  }

  isSavingRow.value = true
  try {
    const payload = {
      name: rowForm.value.name.trim(),
      phone_number: rowForm.value.phone_number?.trim() || null,
      group_name: rowForm.value.group_name?.trim() || null,
      session_name: rowForm.value.session_name?.trim() || null,
      max_pax: Number(rowForm.value.max_pax) || 1,
      is_physical_invitation: Boolean(rowForm.value.is_physical_invitation),
      notes: rowForm.value.notes?.trim() || null,
    }

    await window.axios.put(`/weddings/${props.wedding.id}/guests/${guest.id}`, payload)

    // Update guest in memory
    guest.name = payload.name
    guest.phone_number = payload.phone_number
    guest.group_name = payload.group_name
    guest.session_name = payload.session_name
    guest.max_pax = payload.max_pax
    guest.is_physical_invitation = payload.is_physical_invitation
    guest.notes = payload.notes

    if (payload.session_name && !extraSessions.value.includes(payload.session_name)) {
      handleNewSessionAdded(payload.session_name)
    }

    toast.success(`Data tamu "${guest.name}" berhasil disimpan.`)
    editingRowId.value = null
  } catch (err) {
    const msg = err.response?.data?.message || err.message || 'Terjadi kesalahan saat menyimpan data.'
    toast.error('Gagal menyimpan data: ' + msg)
  } finally {
    isSavingRow.value = false
  }
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
  const sessionInfo = guest.session_name ? `\n*Sesi / Waktu Acara:*\n${guest.session_name}\n` : ''
  const text = encodeURIComponent(
    `Kepada Yth. *${guest.name}*,\n\n` +
    `Tanpa mengurangi rasa hormat, perkenankan kami mengundang Bapak/Ibu/Saudara/i untuk menghadiri acara pernikahan kami:\n\n` +
    `*${couple}*\n` +
    sessionInfo + `\n` +
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
async function markSent(guest) {
  const oldVal = guest.is_invitation_sent
  guest.is_invitation_sent = true
  try {
    await window.axios.post(`/weddings/${props.wedding.id}/guests/${guest.id}/mark-sent`, {})
    toast.success(`Undangan untuk "${guest.name}" ditandai sudah dikirim.`)
  } catch (err) {
    guest.is_invitation_sent = oldVal
    const msg = err.response?.data?.message || err.message || 'Terjadi kesalahan'
    toast.error('Gagal memperbarui status pengiriman: ' + msg)
  }
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
      preserveState: true,
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
      preserveState: true,
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
      preserveState: true,
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
const totalPhysical = computed(() => props.stats?.total_physical ?? guestList.value.filter(g => g.is_physical_invitation).length)
const totalAttending = computed(() => props.stats?.attending ?? guestList.value.filter(g => g.rsvp?.attendance_status === 'attending').length)
const totalDeclined = computed(() => props.stats?.declined ?? guestList.value.filter(g => g.rsvp?.attendance_status === 'declined').length)
const totalConfirmedPax = computed(() => props.stats?.confirmed_pax ?? guestList.value.reduce((acc, g) => acc + (g.rsvp?.attendance_status === 'attending' ? (g.rsvp?.pax_count || 1) : 0), 0))
const totalPax = computed(() => props.stats?.total_pax ?? guestList.value.reduce((acc, g) => acc + (g.max_pax || 1), 0))
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
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
          <!-- Total Undangan (Dulu Total Tamu) -->
          <button
            type="button"
            class="rounded-2xl border bg-white p-4 text-left shadow-sm transition hover:border-emerald-300"
            :class="filterStatus === 'all' ? 'border-emerald-400 ring-2 ring-emerald-100' : 'border-slate-200'"
            @click="filterStatus = 'all'"
          >
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Undangan</p>
            <p class="font-serif text-2xl font-bold text-emerald-950 mt-1">{{ totalGuests }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">{{ totalSent }} terkirim • {{ totalPhysical }} fisik</p>
          </button>

          <!-- Total Pax -->
          <div
            class="rounded-2xl border border-indigo-100 bg-white p-4 text-left shadow-sm hover:border-indigo-300 transition"
          >
            <div class="flex items-center justify-between">
              <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">Total Pax</p>
              <span class="rounded-full bg-indigo-50 px-1.5 py-0.5 text-[9px] font-bold text-indigo-700">Kuota</span>
            </div>
            <p class="font-serif text-2xl font-bold text-indigo-950 mt-1">{{ totalPax }}</p>
            <p class="text-[11px] text-indigo-600/80 mt-0.5">{{ totalConfirmedPax }} Pax Hadir</p>
          </div>

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
            class="rounded-2xl border bg-white p-4 text-left shadow-sm transition hover:border-amber-300 col-span-2 sm:col-span-1"
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

              <!-- Session Filter -->
              <select
                v-model="filterSession"
                class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs focus:border-emerald-500 focus:outline-none"
              >
                <option value="all">Semua Sesi Acara</option>
                <option v-for="session in sessionOptions" :key="session" :value="session">
                  {{ session }}
                </option>
                <option value="_none_">Tanpa Sesi</option>
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

              <!-- Physical Invitation Filter -->
              <select
                v-model="filterPhysical"
                class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs focus:border-emerald-500 focus:outline-none"
              >
                <option value="all">Semua Tipe Undangan</option>
                <option value="physical">💌 Undangan Fisik</option>
                <option value="digital">🌐 Digital Saja</option>
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
              <p class="text-sm font-medium text-slate-700">Belum ada undangan ditemukan.</p>
              <p class="mt-1 text-xs text-slate-400">
                {{ hasActiveFilters ? 'Coba sesuaikan filter pencarian di atas.' : 'Klik "Tambah Tamu" atau "Impor Excel / CSV" untuk menambahkan data tamu undangan baru.' }}
              </p>
            </div>

            <div v-else class="space-y-3">
              <!-- Selection helper banner across pagination pages -->
              <div
                v-if="selectedGuestIds.length > 0 && selectedGuestIds.length < filteredGuests.length"
                class="rounded-xl bg-emerald-50/90 px-3.5 py-2 text-xs text-emerald-900 border border-emerald-200 flex items-center justify-between"
              >
                <span>
                  <strong>{{ selectedGuestIds.length }}</strong> undangan terpilih di halaman ini.
                </span>
                <button
                  type="button"
                  class="font-bold text-emerald-800 hover:text-emerald-950 hover:underline cursor-pointer"
                  @click="selectAllFiltered"
                >
                  Pilih seluruh {{ filteredGuests.length }} undangan yang cocok
                </button>
              </div>
              <div
                v-else-if="selectedGuestIds.length > 0 && selectedGuestIds.length === filteredGuests.length && filteredGuests.length > paginatedGuests.length"
                class="rounded-xl bg-emerald-50/90 px-3.5 py-2 text-xs text-emerald-900 border border-emerald-200 flex items-center justify-between"
              >
                <span>
                  Seluruh <strong>{{ filteredGuests.length }}</strong> undangan telah dipilih.
                </span>
                <button
                  type="button"
                  class="font-bold text-emerald-800 hover:text-emerald-950 hover:underline cursor-pointer"
                  @click="selectedGuestIds = []"
                >
                  Batalkan pilihan
                </button>
              </div>

              <div>
                <!-- Datalists for inline row editing -->
                <datalist id="inline-group-options">
                  <option v-for="g in groupOptions" :key="g" :value="g" />
                </datalist>
                <datalist id="inline-session-options">
                  <option v-for="s in sessionOptions" :key="s" :value="s" />
                </datalist>

                <Table>
                  <TableHeader>
                    <TableRow class="bg-slate-50/90">
                      <!-- Checkbox Master Column (Frozen) -->
                      <TableHead class="sticky left-0 z-20 w-[44px] min-w-[44px] max-w-[44px] px-3 bg-slate-50 text-center">
                        <input
                          type="checkbox"
                          :checked="isAllSelected"
                          :indeterminate.prop="isSomeSelected"
                          @change="toggleSelectAll"
                          class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer"
                          title="Pilih Semua di Halaman Ini"
                        />
                      </TableHead>

                      <!-- Nama & Grup (Frozen) -->
                      <TableHead class="sticky left-[44px] z-20 min-w-[220px] sm:min-w-[260px] bg-slate-50 font-semibold text-slate-700 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.08),1px_0_0_0_#e2e8f0]">
                        Nama &amp; Grup
                      </TableHead>

                      <TableHead class="min-w-[180px] font-semibold text-slate-700">Sesi Undangan</TableHead>
                      <TableHead class="min-w-[130px] font-semibold text-slate-700 text-center">Undangan Fisik</TableHead>
                      <TableHead class="min-w-[130px] font-semibold text-slate-700">Kontak</TableHead>
                      <TableHead class="min-w-[120px] font-semibold text-slate-700">Status Undangan</TableHead>
                      <TableHead class="min-w-[170px] font-semibold text-slate-700">Konfirmasi Kehadiran (RSVP)</TableHead>
                      <TableHead class="min-w-[90px] font-semibold text-slate-700">Maks Pax</TableHead>
                      <TableHead class="min-w-[190px] text-right font-semibold text-slate-700">Aksi &amp; Kirim</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    <TableRow
                      v-for="guest in paginatedGuests"
                      :key="guest.id"
                      class="group transition"
                      :class="editingRowId === guest.id
                        ? 'bg-amber-50/50 ring-2 ring-emerald-500/50 shadow-xs z-10'
                        : (selectedGuestIds.includes(guest.id) ? 'bg-emerald-50/60 hover:bg-emerald-50/90' : 'hover:bg-slate-50/80')"
                      @dblclick="startInlineEdit(guest)"
                    >
                    <!-- Row Checkbox (Frozen) -->
                    <TableCell
                      class="sticky left-0 z-10 w-[44px] min-w-[44px] max-w-[44px] px-3 text-center transition-colors"
                      :class="editingRowId === guest.id
                        ? 'bg-amber-50/80'
                        : (selectedGuestIds.includes(guest.id) ? 'bg-emerald-50 group-hover:bg-emerald-100/70' : 'bg-white group-hover:bg-slate-50')"
                    >
                      <input
                        type="checkbox"
                        :checked="selectedGuestIds.includes(guest.id)"
                        @change="toggleGuest(guest.id)"
                        class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer"
                      />
                    </TableCell>

                    <!-- Name & Group (Frozen) -->
                    <TableCell
                      class="sticky left-[44px] z-10 min-w-[240px] sm:min-w-[280px] shadow-[2px_0_5px_-2px_rgba(0,0,0,0.08),1px_0_0_0_#e2e8f0] transition-colors"
                      :class="editingRowId === guest.id
                        ? 'bg-amber-50/80'
                        : (selectedGuestIds.includes(guest.id) ? 'bg-emerald-50 group-hover:bg-emerald-100/70' : 'bg-white group-hover:bg-slate-50')"
                    >
                      <!-- Inline Edit Inputs for Name, Group & Notes -->
                      <div v-if="editingRowId === guest.id" class="space-y-1.5 py-1">
                        <div>
                          <input
                            v-model="rowForm.name"
                            type="text"
                            placeholder="Nama Tamu (wajib)..."
                            class="h-7 w-full rounded-md border border-slate-300 bg-white px-2 text-xs font-bold text-slate-900 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none"
                            required
                            autofocus
                            @keydown.enter.prevent="saveInlineEdit(guest)"
                            @keydown.esc.prevent="cancelInlineEdit"
                          />
                        </div>
                        <div class="flex items-center gap-1.5">
                          <input
                            v-model="rowForm.group_name"
                            type="text"
                            list="inline-group-options"
                            placeholder="Kategori / Grup..."
                            class="h-6 w-1/2 rounded-md border border-slate-200 bg-white px-2 text-[11px] text-slate-700 focus:border-emerald-500 focus:outline-none"
                            @keydown.enter.prevent="saveInlineEdit(guest)"
                            @keydown.esc.prevent="cancelInlineEdit"
                          />
                          <input
                            v-model="rowForm.notes"
                            type="text"
                            placeholder="Catatan..."
                            class="h-6 w-1/2 rounded-md border border-slate-200 bg-white px-2 text-[11px] text-slate-500 focus:border-emerald-500 focus:outline-none"
                            @keydown.enter.prevent="saveInlineEdit(guest)"
                            @keydown.esc.prevent="cancelInlineEdit"
                          />
                        </div>
                      </div>

                      <!-- Display Mode for Name, Group & Notes -->
                      <div v-else>
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

                    <!-- Sesi Undangan Dropdown (Select2 Style) -->
                    <TableCell class="min-w-[180px]">
                      <div v-if="editingRowId === guest.id" class="py-1">
                        <input
                          v-model="rowForm.session_name"
                          type="text"
                          list="inline-session-options"
                          placeholder="Pilih atau ketik sesi..."
                          class="h-7 w-full rounded-md border border-slate-300 bg-white px-2 text-xs text-slate-800 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none"
                          @keydown.enter.prevent="saveInlineEdit(guest)"
                          @keydown.esc.prevent="cancelInlineEdit"
                        />
                      </div>
                      <GuestSessionSelect
                        v-else
                        :guest="guest"
                        :wedding-id="wedding.id"
                        :available-sessions="sessionOptions"
                        @new-session-added="handleNewSessionAdded"
                      />
                    </TableCell>

                    <!-- Undangan Fisik Toggle -->
                    <TableCell class="min-w-[130px] text-center">
                      <button
                        v-if="editingRowId === guest.id"
                        type="button"
                        @click="rowForm.is_physical_invitation = !rowForm.is_physical_invitation"
                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium transition cursor-pointer"
                        :class="rowForm.is_physical_invitation
                          ? 'bg-amber-100 text-amber-900 border border-amber-300 shadow-2xs'
                          : 'bg-slate-100 text-slate-500 border border-slate-200'"
                        title="Klik untuk ubah jenis undangan fisik/digital"
                      >
                        <span>{{ rowForm.is_physical_invitation ? '💌' : '✉️' }}</span>
                        <span>{{ rowForm.is_physical_invitation ? 'Fisik' : 'Digital' }}</span>
                      </button>

                      <button
                        v-else
                        type="button"
                        :disabled="togglingPhysicalId === guest.id"
                        @click="togglePhysical(guest)"
                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium transition cursor-pointer disabled:opacity-50"
                        :class="guest.is_physical_invitation
                          ? 'bg-amber-100 text-amber-900 hover:bg-amber-200 border border-amber-300 shadow-2xs'
                          : 'bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-700 border border-slate-200'"
                        :title="guest.is_physical_invitation ? 'Klik untuk ubah jadi Digital' : 'Klik untuk tandai Undangan Fisik'"
                      >
                        <span v-if="togglingPhysicalId === guest.id" class="inline-block animate-spin text-[10px]">⏳</span>
                        <span v-else>{{ guest.is_physical_invitation ? '💌' : '✉️' }}</span>
                        <span>{{ guest.is_physical_invitation ? 'Fisik' : 'Digital' }}</span>
                      </button>
                    </TableCell>

                    <!-- Contact -->
                    <TableCell class="min-w-[130px]">
                      <div v-if="editingRowId === guest.id" class="py-1">
                        <input
                          v-model="rowForm.phone_number"
                          type="text"
                          placeholder="08123456789"
                          class="h-7 w-full rounded-md border border-slate-300 bg-white px-2 text-xs font-mono text-slate-800 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none"
                          @keydown.enter.prevent="saveInlineEdit(guest)"
                          @keydown.esc.prevent="cancelInlineEdit"
                        />
                      </div>
                      <span v-else class="text-xs text-slate-600 font-mono">
                        {{ guest.phone_number || '-' }}
                      </span>
                    </TableCell>

                    <!-- Status Undangan (Sent) -->
                    <TableCell class="min-w-[120px]">
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
                    <TableCell class="min-w-[170px]">
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
                    <TableCell class="min-w-[90px]">
                      <div v-if="editingRowId === guest.id" class="py-1">
                        <input
                          v-model.number="rowForm.max_pax"
                          type="number"
                          min="1"
                          max="50"
                          class="h-7 w-16 rounded-md border border-slate-300 bg-white px-1.5 text-center text-xs font-bold text-slate-800 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none"
                          @keydown.enter.prevent="saveInlineEdit(guest)"
                          @keydown.esc.prevent="cancelInlineEdit"
                        />
                      </div>
                      <span v-else class="text-xs text-slate-700 font-medium">
                        {{ guest.max_pax }} Pax
                      </span>
                    </TableCell>

                    <!-- Actions -->
                    <TableCell class="min-w-[190px] text-right">
                      <!-- Inline Edit Buttons -->
                      <div v-if="editingRowId === guest.id" class="flex items-center justify-end gap-1.5 py-1">
                        <Button
                          type="button"
                          size="sm"
                          :disabled="isSavingRow"
                          class="h-7 rounded-lg bg-emerald-700 px-2.5 text-xs font-semibold text-white hover:bg-emerald-800 flex items-center gap-1 shadow-xs cursor-pointer"
                          @click="saveInlineEdit(guest)"
                          title="Simpan Perubahan (Enter)"
                        >
                          <svg v-if="isSavingRow" class="h-3 w-3 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                          </svg>
                          <span v-else>✓</span>
                          <span>Simpan</span>
                        </Button>
                        <Button
                          type="button"
                          size="sm"
                          variant="outline"
                          :disabled="isSavingRow"
                          class="h-7 rounded-lg px-2 text-xs font-medium text-slate-600 hover:bg-slate-100 cursor-pointer"
                          @click="cancelInlineEdit"
                          title="Batal (Esc)"
                        >
                          <span>Batal</span>
                        </Button>
                      </div>

                      <!-- Normal Action Buttons -->
                      <div v-else class="flex items-center justify-end gap-1.5">
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

                        <!-- Inline Edit Trigger -->
                        <Button
                          type="button"
                          size="sm"
                          variant="ghost"
                          class="h-7 w-7 p-0 rounded-lg text-slate-500 hover:text-slate-900"
                          @click="startInlineEdit(guest)"
                          title="Edit Tamu Langsung di Baris"
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

            <!-- Pagination Control Bar -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-slate-100 text-xs text-slate-500">
              <div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-start">
                <span>
                  Menampilkan <strong class="text-slate-700 font-semibold">{{ paginationFrom }}</strong> - <strong class="text-slate-700 font-semibold">{{ paginationTo }}</strong> dari <strong class="text-slate-700 font-semibold">{{ totalFiltered }}</strong> undangan
                </span>

                <!-- Per Page Selector -->
                <div class="flex items-center gap-1.5 pl-2 border-l border-slate-200">
                  <span class="text-slate-400">Baris:</span>
                  <select
                    v-model="perPage"
                    class="h-7 rounded-lg border border-slate-200 bg-white px-2 py-0.5 text-xs text-slate-700 font-medium focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 cursor-pointer shadow-2xs"
                  >
                    <option :value="10">10</option>
                    <option :value="25">25</option>
                    <option :value="50">50</option>
                    <option :value="100">100</option>
                    <option value="all">Semua</option>
                  </select>
                </div>
              </div>

              <!-- Page Navigation Buttons -->
              <div v-if="totalPages > 1" class="flex items-center gap-1">
                <!-- First Page -->
                <button
                  type="button"
                  class="h-7 w-7 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 disabled:opacity-30 disabled:cursor-not-allowed flex items-center justify-center transition shadow-2xs cursor-pointer font-bold text-xs"
                  :disabled="currentPage === 1"
                  @click="goToPage(1)"
                  title="Halaman Pertama"
                >
                  «
                </button>
                <!-- Previous Page -->
                <button
                  type="button"
                  class="h-7 w-7 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 disabled:opacity-30 disabled:cursor-not-allowed flex items-center justify-center transition shadow-2xs cursor-pointer text-xs"
                  :disabled="currentPage === 1"
                  @click="goToPage(currentPage - 1)"
                  title="Halaman Sebelumnya"
                >
                  ‹
                </button>

                <!-- Numeric Pages -->
                <template v-for="(p, idx) in visiblePageNumbers" :key="idx">
                  <span v-if="p === '...'" class="px-1 text-slate-400 select-none">…</span>
                  <button
                    v-else
                    type="button"
                    class="h-7 min-w-7 px-2 rounded-lg text-xs font-semibold transition cursor-pointer"
                    :class="currentPage === p ? 'bg-emerald-600 text-white shadow-2xs' : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 shadow-2xs'"
                    @click="goToPage(p)"
                  >
                    {{ p }}
                  </button>
                </template>

                <!-- Next Page -->
                <button
                  type="button"
                  class="h-7 w-7 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 disabled:opacity-30 disabled:cursor-not-allowed flex items-center justify-center transition shadow-2xs cursor-pointer text-xs"
                  :disabled="currentPage === totalPages"
                  @click="goToPage(currentPage + 1)"
                  title="Halaman Selanjutnya"
                >
                  ›
                </button>
                <!-- Last Page -->
                <button
                  type="button"
                  class="h-7 w-7 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 disabled:opacity-30 disabled:cursor-not-allowed flex items-center justify-center transition shadow-2xs cursor-pointer font-bold text-xs"
                  :disabled="currentPage === totalPages"
                  @click="goToPage(totalPages)"
                  title="Halaman Terakhir"
                >
                  »
                </button>
              </div>
            </div>
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
      :available-sessions="sessionOptions"
      @success="selectedGuestIds = []"
    />

    <!-- Quick Guest Modal Component (Create & Quick Edit) -->
    <QuickGuestModal
      v-model:open="isQuickModalOpen"
      :guest="editingGuest"
      :wedding-id="wedding.id"
      :available-groups="groupOptions"
      :available-sessions="sessionOptions"
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
            <p>5. <strong>Sesi:</strong> Sesi atau jam kedatangan (contoh: <code>Sesi Akad (08.00-10.00)</code> atau <code>Sesi Resepsi</code>).</p>
            <p>6. <strong>Undangan Fisik:</strong> Diisi <code>Ya</code> jika ada kartu undangan fisik, atau <code>Tidak</code> jika hanya online (opsional).</p>
            <p>7. <strong>Catatan:</strong> Catatan khusus/VIP/meja (opsional).</p>
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