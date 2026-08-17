<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from '@/components/ui/table'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'

const props = defineProps({
  wedding: { type: Object, required: true },
  guests: { type: [Object, Array], default: () => [] },
})

const guestList = computed(() => {
  if (Array.isArray(props.guests)) return props.guests
  return props.guests?.data || []
})

const searchQuery = ref('')
const filterStatus = ref('all')
const filterSent = ref('all')
const copiedId = ref(null)
const isImportModalOpen = ref(false)

const importForm = useForm({
  file: null,
})

function submitImport() {
  if (!importForm.file) return
  importForm.post(`/dashboard/weddings/${props.wedding.id}/guests/import`, {
    onSuccess: () => {
      isImportModalOpen.value = false
      importForm.reset()
    },
  })
}

const filteredGuests = computed(() => {
  return guestList.value.filter(g => {
    // Search
    if (searchQuery.value.trim()) {
      const q = searchQuery.value.toLowerCase()
      const matchName = (g.name || '').toLowerCase().includes(q)
      const matchPhone = (g.phone_number || '').toLowerCase().includes(q)
      const matchGroup = (g.group_name || '').toLowerCase().includes(q)
      if (!matchName && !matchPhone && !matchGroup) return false
    }

    // Filter RSVP
    if (filterStatus.value !== 'all') {
      const status = g.rsvp?.status || 'pending'
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

function getPersonalLink(guest) {
  const origin = typeof window !== 'undefined' ? window.location.origin : ''
  return `${origin}/w/${props.wedding.slug}?token=${guest.token}`
}

function copyPersonalLink(guest) {
  const url = getPersonalLink(guest)
  navigator.clipboard.writeText(url)
  copiedId.value = guest.id
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

function markSent(guest) {
  router.post(`/dashboard/weddings/${props.wedding.id}/guests/${guest.id}/mark-sent`, {}, {
    preserveScroll: true,
  })
}

function deleteGuest(guest) {
  if (confirm(`Apakah Anda yakin ingin menghapus data tamu "${guest.name}"?`)) {
    router.delete(`/dashboard/weddings/${props.wedding.id}/guests/${guest.id}`, {
      preserveScroll: true,
    })
  }
}

// Stats
const totalGuests = computed(() => guestList.value.length)
const totalSent = computed(() => guestList.value.filter(g => g.is_invitation_sent).length)
const totalAttending = computed(() => guestList.value.filter(g => g.rsvp?.status === 'attending').length)
const totalDeclined = computed(() => guestList.value.filter(g => g.rsvp?.status === 'declined').length)
const totalConfirmedPax = computed(() => guestList.value.reduce((acc, g) => acc + (g.rsvp?.status === 'attending' ? (g.rsvp?.pax_count || 1) : 0), 0))
</script>

<template>
  <Head :title="`Manajemen Tamu - ${wedding.cover_subtitle || wedding.cover_title}`" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <div class="flex items-center gap-2">
            <Link :href="`/dashboard/weddings/${wedding.id}`" class="text-xs text-emerald-700 hover:underline">&larr; Kembali ke Detail</Link>
          </div>
          <h2 class="font-serif text-3xl font-bold text-emerald-950">Manajemen Tamu Undangan</h2>
          <p class="mt-1 text-sm text-slate-500">{{ wedding.cover_title }} • Kelola daftar penerima &amp; link personal.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <!-- Import CSV Button -->
          <Button
            type="button"
            variant="outline"
            class="rounded-xl border-emerald-300 text-xs font-semibold text-emerald-800 hover:bg-emerald-50"
            @click="isImportModalOpen = true"
          >
            📥 Impor CSV
          </Button>

          <!-- Export CSV Link -->
          <a :href="`/dashboard/weddings/${wedding.id}/guests/export`" download>
            <Button
              type="button"
              variant="outline"
              class="rounded-xl border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50"
            >
              📤 Ekspor CSV
            </Button>
          </a>

          <!-- Add Guest -->
          <Link :href="`/dashboard/weddings/${wedding.id}/guests/create`">
            <Button class="rounded-xl bg-emerald-700 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-800 transition flex items-center gap-1.5">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
              </svg>
              Tambah Tamu
            </Button>
          </Link>
        </div>
      </div>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
        <!-- Quick Metric Stats -->
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
          <div class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Tamu</p>
            <p class="font-serif text-2xl font-bold text-emerald-950 mt-1">{{ totalGuests }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">{{ totalSent }} terkirim</p>
          </div>

          <div class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Hadir</p>
            <p class="font-serif text-2xl font-bold text-emerald-700 mt-1">{{ totalAttending }}</p>
            <p class="text-[11px] text-emerald-600 mt-0.5">{{ totalConfirmedPax }} Total Pax</p>
          </div>

          <div class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-rose-500">Tidak Hadir</p>
            <p class="font-serif text-2xl font-bold text-rose-700 mt-1">{{ totalDeclined }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Berhalangan</p>
          </div>

          <div class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-amber-500">Belum RSVP</p>
            <p class="font-serif text-2xl font-bold text-amber-700 mt-1">{{ totalGuests - totalAttending - totalDeclined }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Menunggu konfirmasi</p>
          </div>
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
                placeholder="Cari nama, grup, no telepon..."
                class="pl-9 text-xs"
              />
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap items-center gap-2">
              <select
                v-model="filterStatus"
                class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs focus:border-emerald-500 focus:outline-none"
              >
                <option value="all">Semua Status RSVP</option>
                <option value="attending">Hadir</option>
                <option value="declined">Tidak Hadir</option>
                <option value="pending">Belum Konfirmasi</option>
              </select>

              <select
                v-model="filterSent"
                class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs focus:border-emerald-500 focus:outline-none"
              >
                <option value="all">Semua Status Kirim</option>
                <option value="sent">Sudah Dikirim</option>
                <option value="unsent">Belum Dikirim</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Guests Table -->
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
          <div class="p-6">
            <div v-if="!filteredGuests.length" class="py-12 text-center">
              <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                👥
              </div>
              <p class="text-sm font-medium text-slate-700">Belum ada tamu ditemukan.</p>
              <p class="mt-1 text-xs text-slate-400">Klik "Tambah Tamu" atau "Impor CSV" untuk menambahkan data tamu undangan baru.</p>
            </div>

            <div v-else class="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow class="bg-slate-50/50">
                    <TableHead class="font-semibold text-slate-700">Nama &amp; Grup</TableHead>
                    <TableHead class="font-semibold text-slate-700">Kontak</TableHead>
                    <TableHead class="font-semibold text-slate-700">Status Undangan</TableHead>
                    <TableHead class="font-semibold text-slate-700">Konfirmasi Kehadiran (RSVP)</TableHead>
                    <TableHead class="font-semibold text-slate-700">Maks Pax</TableHead>
                    <TableHead class="text-right font-semibold text-slate-700">Aksi &amp; Kirim</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-for="guest in filteredGuests" :key="guest.id" class="hover:bg-slate-50/80 transition">
                    <!-- Name & Group -->
                    <TableCell>
                      <div>
                        <Link :href="`/dashboard/weddings/${wedding.id}/guests/${guest.id}`" class="font-bold text-slate-900 hover:text-emerald-700">
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
                          :class="guest.rsvp.status === 'attending' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'"
                        >
                          {{ guest.rsvp.status === 'attending' ? `✓ Hadir (${guest.rsvp.pax_count} orang)` : '✕ Berhalangan' }}
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
                          class="h-7 rounded-lg bg-emerald-600 px-2 text-[11px] font-semibold text-white hover:bg-emerald-700 flex items-center gap-1"
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
                          class="h-7 rounded-lg px-2 text-[11px] font-medium text-slate-700 hover:bg-slate-100"
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

                        <!-- Edit -->
                        <Link :href="`/dashboard/weddings/${wedding.id}/guests/${guest.id}/edit`">
                          <Button size="sm" variant="ghost" class="h-7 w-7 p-0 rounded-lg text-slate-500 hover:text-slate-900" title="Edit Data Tamu">
                            ✏️
                          </Button>
                        </Link>

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

    <!-- Modal Impor CSV -->
    <div
      v-if="isImportModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
      @click.self="isImportModalOpen = false"
    >
      <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <h3 class="font-serif text-lg font-bold text-emerald-950">Impor Tamu dari File CSV / Excel</h3>
          <button @click="isImportModalOpen = false" class="text-slate-400 hover:text-slate-600">&times;</button>
        </div>

        <div class="space-y-2 text-xs text-slate-600">
          <p>Format file CSV harus memiliki urutan kolom sebagai berikut:</p>
          <div class="rounded-lg bg-slate-100 p-2.5 font-mono text-[11px] text-slate-800 overflow-x-auto">
            Nama Tamu, Nomor WhatsApp, Grup/Kategori, Max Pax, Catatan
          </div>
          <p class="text-[11px] text-slate-400">Contoh baris: <code>"Bpk. H. Rahmat", "08123456789", "Keluarga Besar", 2, "VIP Depan"</code></p>
        </div>

        <form @submit.prevent="submitImport" class="space-y-4 pt-2">
          <div class="space-y-1.5">
            <label class="block text-xs font-semibold text-slate-700">Pilih File CSV / JSON</label>
            <input
              type="file"
              accept=".csv,.txt,.json"
              @input="importForm.file = $event.target.files[0]"
              class="w-full rounded-xl border border-slate-200 p-2 text-xs file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-100 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-emerald-800"
              required
            />
            <p v-if="importForm.errors.file" class="text-xs text-rose-500">{{ importForm.errors.file }}</p>
          </div>

          <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
            <Button type="button" variant="outline" class="rounded-xl text-xs" @click="isImportModalOpen = false">Batal</Button>
            <Button
              type="submit"
              :disabled="importForm.processing"
              class="rounded-xl bg-emerald-700 px-4 text-xs font-semibold text-white hover:bg-emerald-800"
            >
              {{ importForm.processing ? 'Mengunggah...' : 'Mulai Impor' }}
            </Button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>