<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card'

const props = defineProps({
  wedding: { type: Object, required: true },
  checklists: { type: Array, default: () => [] },
  config: { type: Object, default: () => ({
    scenario: 'both',
    kua_groom: '',
    kua_bride: '',
    kua_venue: '',
  }) },
  stats: { type: Object, default: () => ({}) },
})

const activeFilter = ref('all')
const isAddingCustom = ref(false)
const showKuaSettings = ref(true)
const showResetConfirm = ref(false)
const editingNotesId = ref(null)
const notesDraft = ref('')

const stageOptions = [
  { key: 'rt_rw', title: '1. RT/RW (Persyaratan yang Dibawa)' },
  { key: 'puskesmas', title: '2. PUSKESMAS (Pemeriksaan Kesehatan & Sertifikat Layak Nikah)' },
  { key: 'kelurahan', title: '3. KELURAHAN (SCAN DOKSLI - Upload di Jakevo)' },
  { key: 'kua_rekomendasi', title: '4. KUA Domisili Asal (Surat Rekomendasi Nikah / Numpang Nikah)' },
  { key: 'kua_venue', title: '5. KUA Venue Pernikahan (Daftar Online SIMKAH Dulu, Baru Bawa Berkas Asli)' },
]

const addForm = useForm({
  stage_key: 'rt_rw',
  stage_title: '1. RT/RW (Persyaratan yang Dibawa)',
  document_name: '',
  notes: '',
})

const configForm = useForm({
  scenario: props.config?.scenario || 'both',
  kua_groom: props.config?.kua_groom || '',
  kua_bride: props.config?.kua_bride || '',
  kua_venue: props.config?.kua_venue || '',
})

function onStageSelectChange(e) {
  const selected = stageOptions.find(s => s.key === e.target.value)
  if (selected) {
    addForm.stage_key = selected.key
    addForm.stage_title = selected.title
  }
}

function selectScenario(scenario) {
  configForm.scenario = scenario
  configForm.post(`/weddings/${props.wedding.id}/document-checklist/config`, {
    preserveScroll: true,
  })
}

function saveKuaNames() {
  configForm.post(`/weddings/${props.wedding.id}/document-checklist/config`, {
    preserveScroll: true,
  })
}

function isGroomExempt(item) {
  if (item.stage_key !== 'kua_rekomendasi') return false
  const sc = props.config?.scenario || 'both'
  return sc === 'bride_only' || sc === 'none'
}

function isBrideExempt(item) {
  if (item.stage_key !== 'kua_rekomendasi') return false
  const sc = props.config?.scenario || 'both'
  return sc === 'groom_only' || sc === 'none'
}

// Group checklists by stage_key
const groupedChecklists = computed(() => {
  const groups = {}
  stageOptions.forEach(opt => {
    groups[opt.key] = {
      key: opt.key,
      title: opt.title,
      items: [],
    }
  })

  props.checklists.forEach(item => {
    if (!groups[item.stage_key]) {
      groups[item.stage_key] = {
        key: item.stage_key,
        title: item.stage_title || item.stage_key,
        items: [],
      }
    }
    groups[item.stage_key].items.push(item)
  })

  return groups
})

const displayedGroups = computed(() => {
  if (activeFilter.value === 'all') {
    return Object.values(groupedChecklists.value).filter(g => g.items.length > 0)
  }
  const group = groupedChecklists.value[activeFilter.value]
  return group && group.items.length > 0 ? [group] : []
})

// Progress calculations
const totalDocuments = computed(() => props.stats.total_items || props.checklists.length)
const groomEligible = computed(() => props.stats.total_groom_eligible ?? totalDocuments.value)
const brideEligible = computed(() => props.stats.total_bride_eligible ?? totalDocuments.value)
const groomCheckedCount = computed(() => props.stats.groom_checked || 0)
const brideCheckedCount = computed(() => props.stats.bride_checked || 0)
const groomPercent = computed(() => props.stats.groom_percentage ?? 0)
const bridePercent = computed(() => props.stats.bride_percentage ?? 0)
const overallPercent = computed(() => props.stats.overall_percentage ?? 0)

function toggleCheck(item, target) {
  const payload = {}
  if (target === 'groom') {
    if (isGroomExempt(item)) return
    payload.is_groom_checked = !item.is_groom_checked
    item.is_groom_checked = !item.is_groom_checked // optimistic
  } else if (target === 'bride') {
    if (isBrideExempt(item)) return
    payload.is_bride_checked = !item.is_bride_checked
    item.is_bride_checked = !item.is_bride_checked // optimistic
  }

  router.patch(`/weddings/${props.wedding.id}/document-checklist/${item.id}`, payload, {
    preserveScroll: true,
    preserveState: true,
  })
}

function startEditNotes(item) {
  editingNotesId.value = item.id
  notesDraft.value = item.notes || ''
}

function saveNotes(item) {
  router.patch(`/weddings/${props.wedding.id}/document-checklist/${item.id}`, {
    notes: notesDraft.value,
  }, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      item.notes = notesDraft.value
      editingNotesId.value = null
    },
  })
}

function cancelEditNotes() {
  editingNotesId.value = null
  notesDraft.value = ''
}

function deleteItem(item) {
  if (confirm(`Hapus "${item.document_name}" dari daftar persyaratan?`)) {
    router.delete(`/weddings/${props.wedding.id}/document-checklist/${item.id}`, {
      preserveScroll: true,
    })
  }
}

function submitNewDocument() {
  addForm.post(`/weddings/${props.wedding.id}/document-checklist`, {
    preserveScroll: true,
    onSuccess: () => {
      addForm.reset('document_name', 'notes')
      isAddingCustom.value = false
    },
  })
}

function resetToDefault() {
  router.post(`/weddings/${props.wedding.id}/document-checklist/reset`, {}, {
    preserveScroll: true,
    onSuccess: () => {
      showResetConfirm.value = false
    },
  })
}

function printChecklist() {
  window.print()
}
</script>

<template>
  <Head :title="`Ceklis Dokumen Pernikahan - ${wedding.cover_title}`" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <div class="flex items-center gap-2">
            <Link :href="`/weddings/${wedding.id}`" class="text-xs text-emerald-700 hover:underline print:hidden">
              &larr; Kembali ke Detail Wedding
            </Link>
          </div>
          <h2 class="font-serif text-3xl font-bold text-emerald-950 mt-1">Ceklis Dokumen Pernikahan</h2>
          <p class="text-sm text-slate-500">
            Persyaratan berkas nikah resmi Catin Pria &amp; Catin Wanita: RT/RW, Puskesmas, Kelurahan (Jakevo), hingga KUA Venue.
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 print:hidden">
          <Button
            variant="outline"
            size="sm"
            @click="printChecklist"
            class="h-9 gap-1.5 rounded-xl border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50"
          >
            🖨️ Cetak Ceklis
          </Button>
          <Button
            variant="outline"
            size="sm"
            @click="isAddingCustom = !isAddingCustom"
            class="h-9 gap-1.5 rounded-xl border-emerald-300 text-xs font-semibold text-emerald-700 hover:bg-emerald-50"
          >
            ➕ Tambah Dokumen
          </Button>
          <Button
            variant="ghost"
            size="sm"
            @click="showResetConfirm = true"
            class="h-9 text-xs text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-xl"
            title="Kembalikan ke susunan template standar"
          >
            ↺ Reset
          </Button>
        </div>
      </div>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

        <!-- Progress Overview Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 print:grid-cols-3">
          <!-- Overall Progress -->
          <div class="rounded-3xl border border-emerald-200/80 bg-gradient-to-br from-emerald-900 to-emerald-950 p-5 text-white shadow-sm">
            <div class="flex items-center justify-between">
              <span class="text-xs font-semibold uppercase tracking-wider text-emerald-300">Total Kelengkapan</span>
              <span class="rounded-full bg-emerald-800/80 px-2.5 py-0.5 text-xs font-bold text-emerald-200">
                {{ overallPercent }}%
              </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
              <span class="font-serif text-3xl font-bold">{{ groomCheckedCount + brideCheckedCount }}</span>
              <span class="text-xs text-emerald-300">/ {{ groomEligible + brideEligible }} Berkas Wajib</span>
            </div>
            <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-emerald-800/60">
              <div
                class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-amber-300 transition-all duration-500"
                :style="{ width: `${overallPercent}%` }"
              />
            </div>
            <p class="mt-2 text-[11px] text-emerald-200/80">
              Perhitungan dinamis menyesuaikan alur rekomendasi KUA Anda.
            </p>
          </div>

          <!-- Catin Pria Progress -->
          <div class="rounded-3xl border border-blue-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="text-lg">🤵</span>
                <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Catin Pria (Groom)</span>
              </div>
              <span class="rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-bold text-blue-700">
                {{ groomPercent }}%
              </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
              <span class="font-serif text-3xl font-bold text-slate-900">{{ groomCheckedCount }}</span>
              <span class="text-xs text-slate-400">/ {{ groomEligible }} Berkas Wajib</span>
            </div>
            <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-slate-100">
              <div
                class="h-full rounded-full bg-blue-600 transition-all duration-500"
                :style="{ width: `${groomPercent}%` }"
              />
            </div>
            <p class="mt-2 text-[11px] text-slate-400">
              {{ groomEligible - groomCheckedCount > 0 ? `${groomEligible - groomCheckedCount} berkas pria belum terceklis.` : 'Semua berkas pria lengkap!' }}
            </p>
          </div>

          <!-- Catin Wanita Progress -->
          <div class="rounded-3xl border border-rose-100 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="text-lg">👰</span>
                <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Catin Wanita (Bride)</span>
              </div>
              <span class="rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-bold text-rose-700">
                {{ bridePercent }}%
              </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
              <span class="font-serif text-3xl font-bold text-slate-900">{{ brideCheckedCount }}</span>
              <span class="text-xs text-slate-400">/ {{ brideEligible }} Berkas Wajib</span>
            </div>
            <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-slate-100">
              <div
                class="h-full rounded-full bg-rose-500 transition-all duration-500"
                :style="{ width: `${bridePercent}%` }"
              />
            </div>
            <p class="mt-2 text-[11px] text-slate-400">
              {{ brideEligible - brideCheckedCount > 0 ? `${brideEligible - brideCheckedCount} berkas wanita belum terceklis.` : 'Semua berkas wanita lengkap!' }}
            </p>
          </div>
        </div>

        <!-- KUA Recommendation & Numpang Nikah Flow Configuration -->
        <div class="rounded-3xl border border-emerald-100 bg-white p-6 shadow-sm sm:p-8 space-y-5 print:border-slate-300">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-4">
            <div>
              <h3 class="font-serif text-lg font-bold text-emerald-950 flex items-center gap-2">
                🏛️ Alur Lokasi Akad &amp; Numpang Nikah (KUA)
              </h3>
              <p class="text-xs text-slate-500 mt-0.5">
                Surat Rekomendasi Nikah dari KUA asal KTP hanya diperlukan jika akad nikah dilaksanakan di luar KUA domisili KTP.
              </p>
            </div>
            <button
              type="button"
              @click="showKuaSettings = !showKuaSettings"
              class="text-xs font-medium text-emerald-700 hover:underline print:hidden self-start"
            >
              {{ showKuaSettings ? 'Sembunyikan Pilihan' : 'Ubah Alur Skenario' }}
            </button>
          </div>

          <div v-show="showKuaSettings" class="space-y-4 print:block">
            <!-- 4 Scenarios Radio Cards -->
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
              <!-- Scenario 1: Both -->
              <button
                type="button"
                @click="selectScenario('both')"
                class="flex flex-col rounded-2xl border p-4 text-left transition-all relative"
                :class="configForm.scenario === 'both'
                  ? 'border-emerald-600 bg-emerald-50/60 ring-2 ring-emerald-600/30'
                  : 'border-slate-200 bg-slate-50/40 hover:border-slate-300 hover:bg-white'"
              >
                <div class="flex items-center justify-between w-full mb-2">
                  <span class="text-xl">🏛️</span>
                  <span
                    v-if="configForm.scenario === 'both'"
                    class="rounded-full bg-emerald-700 px-2 py-0.5 text-[10px] font-bold text-white"
                  >
                    Aktif
                  </span>
                </div>
                <h4 class="text-xs font-bold text-slate-900">Keduanya Numpang Nikah</h4>
                <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                  Venue di luar KUA CPP &amp; CPW (contoh: CPP Pancoran, CPW Tebet, Venue di Pasar Minggu). Keduanya perlu rekomendasi.
                </p>
              </button>

              <!-- Scenario 2: Groom Only (Venue in CPW KUA) -->
              <button
                type="button"
                @click="selectScenario('groom_only')"
                class="flex flex-col rounded-2xl border p-4 text-left transition-all relative"
                :class="configForm.scenario === 'groom_only'
                  ? 'border-emerald-600 bg-emerald-50/60 ring-2 ring-emerald-600/30'
                  : 'border-slate-200 bg-slate-50/40 hover:border-slate-300 hover:bg-white'"
              >
                <div class="flex items-center justify-between w-full mb-2">
                  <span class="text-xl">👰</span>
                  <span
                    v-if="configForm.scenario === 'groom_only'"
                    class="rounded-full bg-emerald-700 px-2 py-0.5 text-[10px] font-bold text-white"
                  >
                    Aktif
                  </span>
                </div>
                <h4 class="text-xs font-bold text-slate-900">Menikah di KUA Asal CPW</h4>
                <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                  Venue sesuai KTP CPW (contoh: Tebet). Hanya CPP yang butuh rekomendasi KUA. CPW tidak perlu rekomendasi.
                </p>
              </button>

              <!-- Scenario 3: Bride Only (Venue in CPP KUA) -->
              <button
                type="button"
                @click="selectScenario('bride_only')"
                class="flex flex-col rounded-2xl border p-4 text-left transition-all relative"
                :class="configForm.scenario === 'bride_only'
                  ? 'border-emerald-600 bg-emerald-50/60 ring-2 ring-emerald-600/30'
                  : 'border-slate-200 bg-slate-50/40 hover:border-slate-300 hover:bg-white'"
              >
                <div class="flex items-center justify-between w-full mb-2">
                  <span class="text-xl">🤵</span>
                  <span
                    v-if="configForm.scenario === 'bride_only'"
                    class="rounded-full bg-emerald-700 px-2 py-0.5 text-[10px] font-bold text-white"
                  >
                    Aktif
                  </span>
                </div>
                <h4 class="text-xs font-bold text-slate-900">Menikah di KUA Asal CPP</h4>
                <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                  Venue sesuai KTP CPP (contoh: Pancoran). Hanya CPW yang butuh rekomendasi KUA. CPP tidak perlu rekomendasi.
                </p>
              </button>

              <!-- Scenario 4: None (Both same as Venue) -->
              <button
                type="button"
                @click="selectScenario('none')"
                class="flex flex-col rounded-2xl border p-4 text-left transition-all relative"
                :class="configForm.scenario === 'none'
                  ? 'border-emerald-600 bg-emerald-50/60 ring-2 ring-emerald-600/30'
                  : 'border-slate-200 bg-slate-50/40 hover:border-slate-300 hover:bg-white'"
              >
                <div class="flex items-center justify-between w-full mb-2">
                  <span class="text-xl">🏠</span>
                  <span
                    v-if="configForm.scenario === 'none'"
                    class="rounded-full bg-emerald-700 px-2 py-0.5 text-[10px] font-bold text-white"
                  >
                    Aktif
                  </span>
                </div>
                <h4 class="text-xs font-bold text-slate-900">Keduanya Sesuai KTP (1 KUA)</h4>
                <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">
                  KTP CPP, CPW, dan Venue dalam 1 kecamatan KUA yang sama. Tahapan Surat Rekomendasi KUA Asal tidak diperlukan.
                </p>
              </button>
            </div>

            <!-- Custom KUA Names Input -->
            <div class="rounded-2xl border border-slate-100 bg-slate-50/60 p-4 space-y-3 print:hidden">
              <p class="text-xs font-bold text-slate-700">Nama Wilayah KUA (Opsional untuk catatan label)</p>
              <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div>
                  <label class="block text-[11px] text-slate-500 mb-1">KUA Asal Pria (CPP)</label>
                  <Input
                    v-model="configForm.kua_groom"
                    placeholder="Contoh: KUA Kec. Pancoran"
                    class="rounded-xl bg-white text-xs"
                    @blur="saveKuaNames"
                  />
                </div>
                <div>
                  <label class="block text-[11px] text-slate-500 mb-1">KUA Asal Wanita (CPW)</label>
                  <Input
                    v-model="configForm.kua_bride"
                    placeholder="Contoh: KUA Kec. Tebet"
                    class="rounded-xl bg-white text-xs"
                    @blur="saveKuaNames"
                  />
                </div>
                <div>
                  <label class="block text-[11px] text-slate-500 mb-1">KUA Venue Pelaksanaan</label>
                  <Input
                    v-model="configForm.kua_venue"
                    placeholder="Contoh: KUA Kec. Pasar Minggu"
                    class="rounded-xl bg-white text-xs"
                    @blur="saveKuaNames"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Add Custom Document Form -->
        <div v-if="isAddingCustom" class="rounded-3xl border border-emerald-200 bg-emerald-50/50 p-6 shadow-sm print:hidden">
          <div class="flex items-center justify-between pb-3">
            <h3 class="font-serif text-lg font-bold text-emerald-950">Tambah Persyaratan Dokumen Baru</h3>
            <button @click="isAddingCustom = false" class="text-xs text-slate-400 hover:text-slate-600">✕ Tutup</button>
          </div>
          <form @submit.prevent="submitNewDocument" class="space-y-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
              <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tahapan Institusi</label>
                <select
                  v-model="addForm.stage_key"
                  @change="onStageSelectChange"
                  class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs focus:border-emerald-500 focus:outline-none"
                >
                  <option v-for="opt in stageOptions" :key="opt.key" :value="opt.key">
                    {{ opt.title }}
                  </option>
                </select>
              </div>
              <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Dokumen Persyaratan</label>
                <Input
                  v-model="addForm.document_name"
                  placeholder="Contoh: Fotocopy Ijazah Legalisir Basah"
                  class="rounded-xl bg-white text-xs"
                  required
                />
              </div>
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
              <Input
                v-model="addForm.notes"
                placeholder="Contoh: Bawa 2 lembar, legalisir cap basah"
                class="rounded-xl bg-white text-xs"
              />
            </div>
            <div class="flex justify-end gap-2">
              <Button type="button" variant="outline" size="sm" @click="isAddingCustom = false" class="rounded-xl text-xs">
                Batal
              </Button>
              <Button type="submit" size="sm" :disabled="addForm.processing" class="rounded-xl bg-emerald-800 text-xs text-white hover:bg-emerald-900">
                Simpan Dokumen
              </Button>
            </div>
          </form>
        </div>

        <!-- Filter Tabs -->
        <div class="flex flex-wrap items-center gap-1.5 border-b border-slate-200 pb-3 print:hidden">
          <button
            type="button"
            @click="activeFilter = 'all'"
            class="rounded-xl px-3 py-1.5 text-xs font-medium transition-all"
            :class="activeFilter === 'all'
              ? 'bg-emerald-800 text-white font-bold shadow-sm'
              : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
          >
            Semua Tahapan ({{ totalDocuments }})
          </button>
          <button
            v-for="stage in stageOptions"
            :key="stage.key"
            type="button"
            @click="activeFilter = stage.key"
            class="rounded-xl px-3 py-1.5 text-xs font-medium transition-all"
            :class="activeFilter === stage.key
              ? 'bg-emerald-800 text-white font-bold shadow-sm'
              : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
          >
            {{ stage.key === 'rt_rw' ? '1. RT/RW' : stage.key === 'puskesmas' ? '2. Puskesmas' : stage.key === 'kelurahan' ? '3. Kelurahan' : stage.key === 'kua_rekomendasi' ? '4. KUA Rekomendasi' : '5. KUA Venue' }}
            <span class="ml-1 text-[10px] opacity-75">
              ({{ groupedChecklists[stage.key]?.items.length || 0 }})
            </span>
          </button>
        </div>

        <!-- Stage Cards List -->
        <div class="space-y-6">
          <div
            v-for="group in displayedGroups"
            :key="group.key"
            class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm"
          >
            <!-- Stage Header -->
            <div class="flex flex-col gap-2 border-b border-slate-100 bg-slate-50/70 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
              <div class="flex items-center gap-3">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-800 text-xs font-bold text-white">
                  {{ group.key === 'rt_rw' ? '1' : group.key === 'puskesmas' ? '2' : group.key === 'kelurahan' ? '3' : group.key === 'kua_rekomendasi' ? '4' : '5' }}
                </span>
                <div>
                  <h3 class="font-serif text-base font-bold text-slate-900">
                    {{ group.key === 'kua_rekomendasi' ? '4. KUA Domisili Asal (Surat Rekomendasi Nikah / Numpang Nikah)' : group.title }}
                  </h3>
                  <p class="text-[11px] text-slate-500">
                    <span v-if="group.key === 'kua_rekomendasi' && config.scenario === 'none'" class="text-amber-700 font-semibold">
                      🎉 Tidak diperlukan karena menikah di KUA yang sesuai dengan KTP kedua mempelai.
                    </span>
                    <span v-else-if="group.key === 'kua_rekomendasi' && config.scenario === 'groom_only'" class="text-blue-700 font-semibold">
                      👰 CPW tidak perlu rekomendasi (Menikah di KUA CPW). Hanya CPP yang butuh rekomendasi.
                    </span>
                    <span v-else-if="group.key === 'kua_rekomendasi' && config.scenario === 'bride_only'" class="text-rose-700 font-semibold">
                      🤵 CPP tidak perlu rekomendasi (Menikah di KUA CPP). Hanya CPW yang butuh rekomendasi.
                    </span>
                    <span v-else>
                      {{ group.items.length }} butir persyaratan yang harus dibawa/dilengkapi
                    </span>
                  </p>
                </div>
              </div>

              <!-- Stage Progress Badge -->
              <div class="flex items-center gap-3 text-xs">
                <div class="flex items-center gap-1.5 text-slate-600 font-medium">
                  <span v-if="group.key === 'kua_rekomendasi' && (config.scenario === 'bride_only' || config.scenario === 'none')" class="text-slate-400">
                    🤵 Bebas Rekomendasi
                  </span>
                  <span v-else>
                    🤵 {{ group.items.filter(i => i.is_groom_checked).length }}/{{ group.items.length }}
                  </span>
                  <span class="text-slate-300">•</span>
                  <span v-if="group.key === 'kua_rekomendasi' && (config.scenario === 'groom_only' || config.scenario === 'none')" class="text-slate-400">
                    👰 Bebas Rekomendasi
                  </span>
                  <span v-else>
                    👰 {{ group.items.filter(i => i.is_bride_checked).length }}/{{ group.items.length }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Stage Table / Checklist Rows -->
            <div class="divide-y divide-slate-100">
              <div
                v-for="(item, idx) in group.items"
                :key="item.id"
                class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between transition hover:bg-slate-50/50"
                :class="{ 'bg-emerald-50/20': item.is_groom_checked && item.is_bride_checked }"
              >
                <!-- Document Info -->
                <div class="flex items-start gap-3 sm:flex-1">
                  <span class="mt-0.5 text-xs font-mono text-slate-400 w-5 text-right">{{ idx + 1 }}.</span>
                  <div class="space-y-1">
                    <p
                      class="text-sm font-semibold transition-all"
                      :class="item.is_groom_checked && item.is_bride_checked ? 'text-emerald-950 font-bold' : 'text-slate-800'"
                    >
                      {{ item.document_name }}
                    </p>

                    <!-- Notes Display / Inline Edit -->
                    <div v-if="editingNotesId === item.id" class="flex items-center gap-2 pt-1 print:hidden">
                      <Input
                        v-model="notesDraft"
                        class="h-7 text-xs rounded-lg"
                        placeholder="Tulis catatan berkas..."
                        @keyup.enter="saveNotes(item)"
                        @keyup.escape="cancelEditNotes"
                      />
                      <Button size="sm" class="h-7 text-[11px] px-2 rounded-lg bg-emerald-700 text-white" @click="saveNotes(item)">
                        Simpan
                      </Button>
                      <Button variant="ghost" size="sm" class="h-7 text-[11px] px-2 rounded-lg" @click="cancelEditNotes">
                        Batal
                      </Button>
                    </div>
                    <div v-else class="flex items-center gap-2 text-xs text-slate-400">
                      <span v-if="item.notes" class="inline-flex items-center gap-1 rounded bg-amber-50 px-2 py-0.5 text-[11px] font-medium text-amber-800 border border-amber-200/60">
                        📝 {{ item.notes }}
                      </span>
                      <button
                        @click="startEditNotes(item)"
                        class="text-[10px] text-slate-400 hover:text-emerald-700 underline print:hidden"
                      >
                        {{ item.notes ? 'Ubah Catatan' : '+ Catatan' }}
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Checkboxes for Groom and Bride -->
                <div class="flex items-center gap-4 pl-8 sm:pl-0">
                  <!-- Catin Pria Checkbox / Exemption Badge -->
                  <div v-if="isGroomExempt(item)">
                    <span class="inline-flex items-center gap-1 rounded-xl border border-dashed border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-400 select-none">
                      ✓ Tidak Perlu (KUA Sesuai)
                    </span>
                  </div>
                  <label
                    v-else
                    @click.prevent="toggleCheck(item, 'groom')"
                    class="group flex cursor-pointer items-center gap-2 rounded-2xl border px-3 py-2 text-xs transition select-none"
                    :class="item.is_groom_checked
                      ? 'border-blue-500 bg-blue-50 text-blue-900 font-bold shadow-xs'
                      : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50'"
                  >
                    <div
                      class="flex h-5 w-5 items-center justify-center rounded-lg border transition text-xs"
                      :class="item.is_groom_checked
                        ? 'border-blue-600 bg-blue-600 text-white font-bold'
                        : 'border-slate-300 bg-white group-hover:border-slate-400'"
                    >
                      <span v-if="item.is_groom_checked">✓</span>
                    </div>
                    <span>Catin Pria 🤵</span>
                  </label>

                  <!-- Catin Wanita Checkbox / Exemption Badge -->
                  <div v-if="isBrideExempt(item)">
                    <span class="inline-flex items-center gap-1 rounded-xl border border-dashed border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-400 select-none">
                      ✓ Tidak Perlu (KUA Sesuai)
                    </span>
                  </div>
                  <label
                    v-else
                    @click.prevent="toggleCheck(item, 'bride')"
                    class="group flex cursor-pointer items-center gap-2 rounded-2xl border px-3 py-2 text-xs transition select-none"
                    :class="item.is_bride_checked
                      ? 'border-rose-500 bg-rose-50 text-rose-900 font-bold shadow-xs'
                      : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50'"
                  >
                    <div
                      class="flex h-5 w-5 items-center justify-center rounded-lg border transition text-xs"
                      :class="item.is_bride_checked
                        ? 'border-rose-600 bg-rose-600 text-white font-bold'
                        : 'border-slate-300 bg-white group-hover:border-slate-400'"
                    >
                      <span v-if="item.is_bride_checked">✓</span>
                    </div>
                    <span>Catin Wanita 👰</span>
                  </label>

                  <!-- Delete Item Action -->
                  <button
                    type="button"
                    @click="deleteItem(item)"
                    class="text-xs text-slate-300 hover:text-red-600 transition print:hidden p-1"
                    title="Hapus persyaratan ini"
                  >
                    🗑️
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Reset Confirmation Modal -->
    <div
      v-if="showResetConfirm"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-xs print:hidden"
    >
      <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl space-y-4">
        <div class="flex items-center gap-3 text-red-600">
          <span class="text-2xl">⚠️</span>
          <h3 class="font-serif text-lg font-bold text-slate-900">Reset Ceklis ke Standar?</h3>
        </div>
        <p class="text-xs text-slate-600 leading-relaxed">
          Tindakan ini akan mengembalikan seluruh daftar ceklis dokumen ke template bawaan awal (RT/RW, Puskesmas, Kelurahan, KUA). Ceklis yang telah ditandai akan di-reset menjadi belum selesai.
        </p>
        <div class="flex justify-end gap-2 pt-2">
          <Button variant="outline" size="sm" @click="showResetConfirm = false" class="rounded-xl text-xs">
            Batal
          </Button>
          <Button size="sm" @click="resetToDefault" class="rounded-xl bg-red-600 text-xs text-white hover:bg-red-700">
            Ya, Reset Sekarang
          </Button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
@media print {
  body {
    background: white !important;
  }
}
</style>
