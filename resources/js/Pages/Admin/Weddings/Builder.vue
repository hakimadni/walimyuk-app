<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, ref, watch, onMounted, onUnmounted, nextTick } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import PremiumDecorationPanel from '@/Components/Builder/PremiumDecorationPanel.vue'

const props = defineProps({
  wedding: { type: Object, required: true },
  builderConfig: { type: Object, required: true },
  builderCatalog: { type: Object, required: true },
  mode: { type: String, default: 'admin' },
  isPremium: { type: Boolean, default: false },
  previewUrl: { type: String, default: null },
  canEditPermissionMatrix: { type: Boolean, default: false },
})

const form = useForm({
  _method: 'put',
  builder: JSON.parse(JSON.stringify(props.builderConfig)),
})

const previewTab = ref('cover') // 'cover' | 'content'
const draggedIndex = ref(null)
const dragOverIndex = ref(null)
const previewIframe = ref(null)
const previewWrapper = ref(null)
const previewContainer = ref(null)
const previewScale = ref(0.8)
const displayWidth = ref(314)
const displayHeight = ref(681)
const musicFileName = ref('')
const musicFileError = ref('')

let resizeObserver = null

function updateScale() {
  const NATIVE_W = 393
  const NATIVE_H = 852

  const availableWidth = previewWrapper.value?.clientWidth || 340
  // Available viewport height: window.innerHeight minus top sticky offset, card header/footer, padding, and bottom margin
  const availableHeight = Math.max(360, window.innerHeight - 150)

  const scaleByW = availableWidth / NATIVE_W
  const scaleByH = availableHeight / NATIVE_H

  // Fit within BOTH width and height so phone is never cut off
  const scale = Math.min(scaleByW, scaleByH, 0.95)
  previewScale.value = Math.max(0.45, Math.round(scale * 1000) / 1000)

  displayWidth.value = Math.round(NATIVE_W * previewScale.value)
  displayHeight.value = Math.round(NATIVE_H * previewScale.value)
}

onMounted(() => {
  nextTick(() => {
    updateScale()
  })
  window.addEventListener('resize', updateScale)
  if (previewWrapper.value && typeof ResizeObserver !== 'undefined') {
    resizeObserver = new ResizeObserver(() => {
      updateScale()
    })
    resizeObserver.observe(previewWrapper.value)
  }
})

onUnmounted(() => {
  window.removeEventListener('resize', updateScale)
  if (resizeObserver) resizeObserver.disconnect()
})

watch(() => form.builder, (newBuilder) => {
  if (previewIframe.value && previewIframe.value.contentWindow) {
    previewIframe.value.contentWindow.postMessage({
      type: 'UPDATE_THEME',
      builder: JSON.parse(JSON.stringify(newBuilder))
    }, '*')
  }
}, { deep: true })

const permissions = computed(() => form.builder.permissions || {})
const blocks = computed(() => form.builder.content.blocks || [])
const activeBlocks = computed(() => (form.builder.content.blocks || []).filter(b => b.enabled))
const isAdminMode = computed(() => props.mode === 'admin')
const submitUrl = computed(() => isAdminMode.value
  ? `/weddings/${props.wedding.id}/builder`
  : `/dashboard/my-weddings/${props.wedding.id}/builder`
)

const previewMusicUrl = computed(() => form.builder.content.music_uploaded_url || form.builder.content.music_url || '')
const previewTopDecoration = computed(() => form.builder.content.decorations?.top_left?.uploaded_url || form.builder.content.decorations?.top_left?.url || '')
const previewBottomDecoration = computed(() => form.builder.content.decorations?.bottom_right?.uploaded_url || form.builder.content.decorations?.bottom_right?.url || '')
const previewCharacter = computed(() => form.builder.content.character_image?.uploaded_url || form.builder.content.character_image?.url || '')
const palette = computed(() => form.builder.content.palette || {
  primary: '#065f46',
  secondary: '#d4af37',
  background: '#fdf8f0',
  text: '#1f2937',
})

function onMusicFileChange(event) {
  const file = event.target.files[0]
  musicFileName.value = ''
  musicFileError.value = ''
  if (!file) {
    form.builder.content.music_file = null
    return
  }
  const maxBytes = 20 * 1024 * 1024 // 20MB
  if (file.size > maxBytes) {
    musicFileError.value = `Ukuran file terlalu besar (${(file.size / 1024 / 1024).toFixed(1)} MB). Maksimal 20 MB.`
    event.target.value = ''
    form.builder.content.music_file = null
    return
  }
  musicFileName.value = file.name
  form.builder.content.music_file = file
}

function submit() {
  form.post(submitUrl.value, { forceFormData: true })
}

function getBlockEditUrl(blockId) {
  const routes = {
    'ayat': `/weddings/${props.wedding.id}/wedding-verses`,
    'mempelai': `/weddings/${props.wedding.id}/couple-profiles`,
    'acara': `/weddings/${props.wedding.id}/events`,
    'gift': `/weddings/${props.wedding.id}/gift-bank-accounts`,
    'rsvp': `/weddings/${props.wedding.id}/rsvps`,
    'doa': `/weddings/${props.wedding.id}/wishes`,
  }
  return routes[blockId] || null
}

// Drag and Drop handlers
function onDragStart(index, event) {
  if (lockDisabled('block_builder') || !permissions.value.block_order) {
    event.preventDefault()
    return
  }
  draggedIndex.value = index
  event.dataTransfer.effectAllowed = 'move'
  event.dataTransfer.setData('text/plain', index)
}

function onDragOver(index, event) {
  event.preventDefault()
  if (draggedIndex.value !== null && draggedIndex.value !== index) {
    dragOverIndex.value = index
  }
}

function onDragLeave(index) {
  if (dragOverIndex.value === index) {
    dragOverIndex.value = null
  }
}

function onDrop(index) {
  if (draggedIndex.value !== null && draggedIndex.value !== index) {
    const clone = [...blocks.value]
    const [movedItem] = clone.splice(draggedIndex.value, 1)
    clone.splice(index, 0, movedItem)
    form.builder.content.blocks = clone
  }
  draggedIndex.value = null
  dragOverIndex.value = null
}

function onDragEnd() {
  draggedIndex.value = null
  dragOverIndex.value = null
}

function moveBlock(index, direction) {
  const target = index + direction
  if (target < 0 || target >= blocks.value.length) return
  const clone = [...blocks.value]
  ;[clone[index], clone[target]] = [clone[target], clone[index]]
  form.builder.content.blocks = clone
}

function lockDisabled(permissionKey) {
  return !isAdminMode.value && !permissions.value[permissionKey]
}
</script>

<template>
  <Head :title="`Builder - ${wedding.cover_subtitle || wedding.cover_title}`" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 class="font-serif text-3xl font-bold text-emerald-950">Invitation Theme &amp; Block Builder</h2>
          <p class="mt-1 text-sm text-slate-500">
            <span v-if="isAdminMode">Mode Administrator: Atur guardrail izin tenant dan konfigurasi tema undangan.</span>
            <span v-else>Mode Tenant: Kustomisasi tampilan, musik, dan susunan blok seksi undangan Anda.</span>
          </p>
        </div>
        <div class="flex items-center gap-2">
          <Link :href="`/weddings/${wedding.id}`">
            <Button variant="outline" size="sm" class="text-xs font-semibold">Detail</Button>
          </Link>
          <Link :href="`/weddings/${wedding.id}/guests`">
            <Button variant="outline" size="sm" class="text-xs font-semibold">Kelola Tamu</Button>
          </Link>
          <a v-if="previewUrl" :href="previewUrl" target="_blank" rel="noopener noreferrer">
            <Button size="sm" class="bg-emerald-700 text-xs font-semibold text-white hover:bg-emerald-800 flex items-center gap-1">
              <span>Buka di Tab Baru</span>
              <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
              </svg>
            </Button>
          </a>
        </div>
      </div>
    </template>

    <div class="pt-1 pb-6">
      <div class="mx-auto max-w-7xl space-y-4 sm:space-y-6">
        <form @submit.prevent="submit" class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)]">
          <!-- Left Column: Settings Form -->
          <div class="space-y-6">
            <!-- Permission Matrix Card -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
              <div class="mb-4 flex items-center justify-between gap-4">
                <div>
                  <h3 class="font-serif text-lg font-bold text-emerald-950">Permission Matrix (Guardrails)</h3>
                  <p class="text-xs text-slate-500">Izin kustomisasi yang diberikan kepada tenant / pemilik undangan.</p>
                </div>
                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-800 border border-emerald-200">
                  {{ isAdminMode ? 'Admin Guardrail Control' : 'Tenant Mode (Protected)' }}
                </span>
              </div>

              <div class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2 rounded-xl border border-slate-100 bg-slate-50/50 p-4">
                <label class="flex items-center gap-3 cursor-pointer">
                  <input v-model="form.builder.permissions.tenant_builder_enabled" :disabled="!canEditPermissionMatrix" type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                  <span class="font-medium text-slate-700">Tenant Builder Aktif</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                  <input v-model="form.builder.permissions.block_builder" :disabled="!canEditPermissionMatrix" type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                  <span class="font-medium text-slate-700">Block Builder Seksi</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                  <input v-model="form.builder.permissions.custom_decorations" :disabled="!canEditPermissionMatrix" type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                  <span class="font-medium text-slate-700">Kustomisasi Ornamen/Dekorasi</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                  <input v-model="form.builder.permissions.custom_font" :disabled="!canEditPermissionMatrix" type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                  <span class="font-medium text-slate-700">Pilihan Font Family</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                  <input v-model="form.builder.permissions.custom_text" :disabled="!canEditPermissionMatrix" type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                  <span class="font-medium text-slate-700">Kustomisasi Teks Cover &amp; Penutup</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                  <input v-model="form.builder.permissions.character_image" :disabled="!canEditPermissionMatrix" type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                  <span class="font-medium text-slate-700">Gambar Ilustrasi Karakter</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                  <input v-model="form.builder.permissions.background_image" :disabled="!canEditPermissionMatrix" type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                  <span class="font-medium text-slate-700">Background Image</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                  <input v-model="form.builder.permissions.decoration_animation" :disabled="!canEditPermissionMatrix" type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                  <span class="font-medium text-slate-700">Animasi Dekorasi</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                  <input v-model="form.builder.permissions.music" :disabled="!canEditPermissionMatrix" type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                  <span class="font-medium text-slate-700">Musik Latar &amp; Autoplay</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                  <input v-model="form.builder.permissions.palette" :disabled="!canEditPermissionMatrix" type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                  <span class="font-medium text-slate-700">Palet Warna Undangan</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                  <input v-model="form.builder.permissions.block_visibility" :disabled="!canEditPermissionMatrix" type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                  <span class="font-medium text-slate-700">Visibilitas Blok (On/Off)</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                  <input v-model="form.builder.permissions.block_order" :disabled="!canEditPermissionMatrix" type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                  <span class="font-medium text-slate-700">Urutan Blok (Draggable)</span>
                </label>
              </div>
              <p v-if="!canEditPermissionMatrix" class="mt-3 text-xs text-amber-700">
                🔒 Hanya Administrator yang dapat mengubah konfigurasi Permission Matrix.
              </p>
            </div>

            <!-- Draggable Block Builder Section -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
              <div class="mb-4 flex items-center justify-between">
                <div>
                  <h3 class="font-serif text-lg font-bold text-emerald-950">Susunan Seksi Undangan (Draggable Block Builder)</h3>
                  <p class="text-xs text-slate-500">Tarik ikon <span class="font-bold">⋮⋮</span> atau gunakan tombol panah untuk mengubah alur urutan seksi.</p>
                </div>
                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-800">
                  {{ activeBlocks.length }} Seksi Aktif
                </span>
              </div>

              <div class="space-y-2.5">
                <div
                  v-for="(block, index) in form.builder.content.blocks"
                  :key="block.id"
                  :draggable="!lockDisabled('block_builder') && permissions.block_order"
                  @dragstart="onDragStart(index, $event)"
                  @dragover="onDragOver(index, $event)"
                  @dragleave="onDragLeave(index)"
                  @drop="onDrop(index)"
                  @dragend="onDragEnd"
                  class="flex items-center justify-between rounded-xl border p-3.5 transition-all duration-150"
                  :class="[
                    block.enabled ? 'border-slate-200 bg-white shadow-sm' : 'border-dashed border-slate-200 bg-slate-50/60 opacity-60',
                    draggedIndex === index ? 'opacity-40 border-dashed border-emerald-500 bg-emerald-50' : '',
                    dragOverIndex === index ? 'border-2 border-emerald-600 bg-emerald-50/80 scale-[1.01]' : '',
                  ]"
                >
                  <div class="flex items-center gap-3">
                    <!-- Drag Handle Icon -->
                    <div
                      class="flex cursor-grab items-center justify-center rounded p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-700 active:cursor-grabbing"
                      :class="{ 'opacity-30 pointer-events-none': lockDisabled('block_builder') || !permissions.block_order }"
                      title="Tarik untuk memindahkan urutan"
                    >
                      <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 9h.01M8 15h.01M12 9h.01M12 15h.01M16 9h.01M16 15h.01" />
                      </svg>
                    </div>

                    <div>
                      <div class="flex items-center gap-2">
                        <p class="text-sm font-bold text-slate-800">{{ block.label }}</p>
                        <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-mono text-slate-500">{{ block.id }}</span>
                      </div>
                      <p class="text-[11px] text-slate-400">Posisi urutan: #{{ index + 1 }}</p>
                    </div>
                  </div>

                  <div class="flex items-center gap-3">
                    <label class="flex items-center gap-2 text-xs font-semibold cursor-pointer" :class="block.enabled ? 'text-emerald-700' : 'text-slate-400'">
                      <input
                        v-model="block.enabled"
                        :disabled="lockDisabled('block_builder') || !permissions.block_visibility"
                        type="checkbox"
                        class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                      />
                      <span>{{ block.enabled ? 'Aktif' : 'Nonaktif' }}</span>
                    </label>

                    <div class="flex items-center gap-1 border-l border-slate-200 pl-3">
                      <a
                        v-if="getBlockEditUrl(block.id)"
                        :href="getBlockEditUrl(block.id)"
                        target="_blank"
                        class="mr-1 inline-flex h-7 items-center justify-center rounded-lg border border-slate-200 bg-white px-2 text-[10px] font-semibold text-slate-600 transition hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200"
                        title="Edit Konten"
                      >
                        Edit
                      </a>

                      <Button
                        type="button"
                        size="sm"
                        variant="outline"
                        class="h-7 w-7 p-0 rounded-lg text-xs"
                        :disabled="index === 0 || lockDisabled('block_builder') || !permissions.block_order"
                        @click="moveBlock(index, -1)"
                        title="Geser ke atas"
                      >
                        ↑
                      </Button>
                      <Button
                        type="button"
                        size="sm"
                        variant="outline"
                        class="h-7 w-7 p-0 rounded-lg text-xs"
                        :disabled="index === form.builder.content.blocks.length - 1 || lockDisabled('block_builder') || !permissions.block_order"
                        @click="moveBlock(index, 1)"
                        title="Geser ke bawah"
                      >
                        ↓
                      </Button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Style & Palette Section -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
              <!-- Font & Colors -->
              <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                <h3 class="font-serif text-lg font-bold text-emerald-950">Tipografi &amp; Warna</h3>

                <div class="space-y-1.5">
                  <Label>Font Family</Label>
                  <select v-model="form.builder.content.font_family" :disabled="lockDisabled('custom_font')" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm disabled:bg-slate-100 focus:border-emerald-500 focus:outline-none">
                    <option v-for="font in builderCatalog.fonts" :key="font.value" :value="font.value">{{ font.label }}</option>
                  </select>
                </div>

                <div class="space-y-1.5">
                  <Label>Bingkai Foto Mempelai</Label>
                  <select v-model="form.builder.content.couple_photo_frame" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm disabled:bg-slate-100 focus:border-emerald-500 focus:outline-none">
                    <option value="circle">Lingkaran (1:1 Circle)</option>
                    <option value="portrait">Potret (2:3 Rounded Corner)</option>
                    <option value="rounded_square">Persegi Melengkung (1:1 Rounded)</option>
                    <option value="arch">Kubah / Arch (2:3)</option>
                  </select>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-2">
                  <div class="space-y-1">
                    <Label class="text-xs">Primary Color</Label>
                    <div class="flex items-center gap-2">
                      <input type="color" v-model="form.builder.content.palette.primary" :disabled="lockDisabled('palette')" class="h-8 w-8 cursor-pointer rounded-lg border border-slate-200 p-0.5" />
                      <Input v-model="form.builder.content.palette.primary" :disabled="lockDisabled('palette')" class="text-xs font-mono" />
                    </div>
                  </div>
                  <div class="space-y-1">
                    <Label class="text-xs">Secondary / Gold</Label>
                    <div class="flex items-center gap-2">
                      <input type="color" v-model="form.builder.content.palette.secondary" :disabled="lockDisabled('palette')" class="h-8 w-8 cursor-pointer rounded-lg border border-slate-200 p-0.5" />
                      <Input v-model="form.builder.content.palette.secondary" :disabled="lockDisabled('palette')" class="text-xs font-mono" />
                    </div>
                  </div>
                  <div class="space-y-1">
                    <Label class="text-xs">Background</Label>
                    <div class="flex items-center gap-2">
                      <input type="color" v-model="form.builder.content.palette.background" :disabled="lockDisabled('palette')" class="h-8 w-8 cursor-pointer rounded-lg border border-slate-200 p-0.5" />
                      <Input v-model="form.builder.content.palette.background" :disabled="lockDisabled('palette')" class="text-xs font-mono" />
                    </div>
                  </div>
                  <div class="space-y-1">
                    <Label class="text-xs">Text Color</Label>
                    <div class="flex items-center gap-2">
                      <input type="color" v-model="form.builder.content.palette.text" :disabled="lockDisabled('palette')" class="h-8 w-8 cursor-pointer rounded-lg border border-slate-200 p-0.5" />
                      <Input v-model="form.builder.content.palette.text" :disabled="lockDisabled('palette')" class="text-xs font-mono" />
                    </div>
                  </div>
                  <div class="space-y-1">
                    <Label class="text-xs">Bg "Kepada Yth."</Label>
                    <div class="flex items-center gap-2">
                      <input type="color" v-model="form.builder.content.palette.guest_card_background" :disabled="lockDisabled('palette')" class="h-8 w-8 cursor-pointer rounded-lg border border-slate-200 p-0.5" />
                      <Input v-model="form.builder.content.palette.guest_card_background" :disabled="lockDisabled('palette')" class="text-xs font-mono" />
                    </div>
                  </div>
                </div>
              </div>

              <!-- Music Section -->
              <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                <h3 class="font-serif text-lg font-bold text-emerald-950">Musik Latar Undangan</h3>
                <div class="space-y-1.5">
                  <Label>URL Musik (Streaming / Direct MP3)</Label>
                  <Input v-model="form.builder.content.music_url" :disabled="lockDisabled('music')" placeholder="https://domain.com/music.mp3" />
                  <p v-if="form.errors['builder.content.music_url']" class="text-xs text-red-600">{{ form.errors['builder.content.music_url'] }}</p>
                </div>
                <div class="space-y-1.5">
                  <Label>Upload File Musik (opsional, maks. 20MB)</Label>
                  <input
                    type="file"
                    accept="audio/*"
                    :disabled="lockDisabled('music')"
                    @change="onMusicFileChange($event)"
                    class="block w-full rounded-xl border border-slate-200 px-3 py-1.5 text-xs disabled:bg-slate-100"
                  />
                  <p v-if="musicFileError" class="text-xs text-red-600">{{ musicFileError }}</p>
                  <p v-else-if="musicFileName" class="text-xs text-slate-500">📎 Dipilih: {{ musicFileName }}</p>
                  <p v-if="form.errors['builder.content.music_file']" class="text-xs text-red-600">{{ form.errors['builder.content.music_file'] }}</p>
                </div>
                <p v-if="form.builder.content.music_uploaded_url" class="text-xs text-emerald-700 truncate">
                  ✓ File terupload: {{ form.builder.content.music_uploaded_url.split('/').pop() }}
                </p>
                <label class="flex items-center gap-3 text-xs font-medium text-slate-700 cursor-pointer pt-1">
                  <input v-model="form.builder.content.music_autoplay" :disabled="lockDisabled('music')" type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                  Putar musik otomatis saat tamu menekan "Buka Undangan"
                </label>
              </div>
            </div>

            <!-- Custom Copy & Cover Content -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
              <h3 class="font-serif text-lg font-bold text-emerald-950">Teks &amp; Karakter Personalisasi</h3>
              <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="space-y-1.5">
                  <Label>Teks Pengantar Cover (Intro)</Label>
                  <Input v-model="form.builder.content.custom_text.cover_intro" :disabled="lockDisabled('custom_text')" placeholder="The Wedding of" />
                </div>
                <div class="space-y-1.5">
                  <Label>Label Tombol Cover</Label>
                  <Input v-model="form.builder.content.custom_text.cover_button_label" :disabled="lockDisabled('custom_text')" placeholder="Buka Undangan" />
                </div>
                <div class="space-y-1.5 sm:col-span-2">
                  <Label>Hashtag Cover (Di Bawah Tanggal)</Label>
                  <Input v-model="form.builder.content.custom_text.cover_hashtag" :disabled="lockDisabled('custom_text')" placeholder="#selamANYAuntukHAKIM" />
                </div>
                <div class="space-y-1.5 sm:col-span-2">
                  <Label>Efek Teks Cover (Kontras)</Label>
                  <select v-model="form.builder.content.cover_text_effect" :disabled="lockDisabled('custom_text')" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm disabled:bg-slate-100 focus:border-emerald-500 focus:outline-none">
                    <option value="none">Tanpa Efek (Normal)</option>
                    <option value="shadow">Drop Shadow (Bayangan)</option>
                    <option value="stroke">Stroke (Garis Tepi Hitam)</option>
                  </select>
                </div>
                <div class="space-y-1.5 sm:col-span-2">
                  <div class="flex items-center justify-between">
                    <Label>Jarak Margin Atas Layar ke Salam (Spacing Atas)</Label>
                    <span class="text-xs font-mono font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                      {{ form.builder.content.cover_top_spacing ?? 84 }}px
                    </span>
                  </div>
                  <input
                    type="range"
                    min="20"
                    max="220"
                    step="4"
                    :disabled="lockDisabled('custom_text')"
                    v-model.number="form.builder.content.cover_top_spacing"
                    class="w-full accent-emerald-600 cursor-pointer"
                  />
                  <div class="flex justify-between text-[10px] text-slate-400 font-mono">
                    <span>20px (Mepet)</span>
                    <span>Default: 84px (Lega)</span>
                    <span>220px (Jauh ke Bawah)</span>
                  </div>
                </div>
                <div class="space-y-1.5 sm:col-span-2">
                  <Label>Teks Penutup Undangan (Closing Note)</Label>
                  <textarea v-model="form.builder.content.custom_text.closing_note" :disabled="lockDisabled('custom_text')" rows="2" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm disabled:bg-slate-100 focus:border-emerald-500 focus:outline-none" placeholder="Merupakan suatu kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir..."></textarea>
                </div>
              </div>
            </div>

            <!-- Decorations & Assets — Premium Panel -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
              <div class="flex items-center gap-2">
                <h3 class="font-serif text-lg font-bold text-emerald-950">Ornamen &amp; Aset Premium</h3>
                <span v-if="isPremium" class="rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-bold text-amber-700">👑 Premium</span>
              </div>
              <PremiumDecorationPanel
                :builder="form.builder"
                :isPremium="isPremium"
                :disabled="lockDisabled('custom_decorations')"
                @update:builder="form.builder = $event"
              />
            </div>

            <!-- Submit Button Bar -->
            <div class="flex items-center justify-end gap-3 pt-2">
              <Button type="submit" :disabled="form.processing" class="rounded-xl bg-emerald-700 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800 transition flex items-center gap-2">
                <span v-if="form.processing">Menyimpan...</span>
                <span v-else>Simpan Perubahan Builder</span>
              </Button>
            </div>
          </div>

          <!-- Right Column: Interactive Live Preview Phone Mockup -->
          <aside class="space-y-4">
            <div class="sticky top-2 sm:top-3 lg:top-4 rounded-2xl border border-slate-200 bg-white p-3.5 sm:p-4 shadow-sm">
              <div class="flex items-center justify-between pb-2.5 border-b border-slate-100">
                <div class="flex items-center gap-2">
                  <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                  <h3 class="font-serif text-sm sm:text-base font-bold text-emerald-950">Live Preview</h3>
                </div>
                <div class="flex items-center gap-1.5">
                  <span class="text-[10px] font-mono font-semibold bg-slate-100 text-slate-600 px-2 py-0.5 rounded-md border border-slate-200">
                    {{ Math.round(previewScale * 100) }}%
                  </span>
                  <a v-if="previewUrl" :href="previewUrl" target="_blank" class="p-1 text-slate-400 hover:text-emerald-700 hover:bg-slate-100 rounded-md transition" title="Buka di tab baru">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                  </a>
                </div>
              </div>

              <!-- Phone Screen Mockup Frame Wrapper -->
              <div ref="previewWrapper" class="mt-2.5 flex justify-center items-center overflow-hidden">
                <div 
                  class="relative overflow-hidden rounded-[36px] sm:rounded-[40px] border-[6px] sm:border-[7px] border-slate-900 shadow-2xl bg-slate-950 transition-all duration-150"
                  :style="{
                    width: `${displayWidth + 12}px`,
                    height: `${displayHeight + 12}px`,
                  }"
                >
                  <!-- Phone Dynamic Island -->
                  <div class="absolute top-2 inset-x-0 z-50 flex justify-center items-center pointer-events-none">
                    <div 
                      class="rounded-full bg-black flex items-center justify-end pr-2 shadow-inner"
                      :style="{
                        width: `${Math.round(84 * Math.min(1, previewScale * 1.1))}px`,
                        height: `${Math.round(18 * Math.min(1, previewScale * 1.1))}px`,
                      }"
                    >
                      <div 
                        class="rounded-full bg-slate-900 border border-slate-800"
                        :style="{
                          width: `${Math.max(4, Math.round(6 * previewScale))}px`,
                          height: `${Math.max(4, Math.round(6 * previewScale))}px`,
                        }"
                      ></div>
                    </div>
                  </div>

                  <!-- Preview Viewport Screen (393x852 scaled) -->
                  <div 
                    ref="previewContainer" 
                    class="relative bg-white overflow-hidden"
                    :style="{
                      width: `${displayWidth}px`,
                      height: `${displayHeight}px`,
                    }"
                  >
                    <iframe 
                      v-if="previewUrl" 
                      ref="previewIframe" 
                      :src="previewUrl" 
                      class="absolute top-0 left-0 border-0" 
                      :style="{ 
                        width: '393px', 
                        height: '852px', 
                        transform: `scale(${previewScale})`, 
                        transformOrigin: 'top left' 
                      }"
                    ></iframe>
                    <div v-else class="flex h-full items-center justify-center p-6 text-center text-slate-400 text-xs">
                      Live preview belum tersedia. Silakan simpan builder pertama kali.
                    </div>
                  </div>

                  <!-- Phone Home Indicator Bar -->
                  <div class="absolute bottom-1.5 inset-x-0 z-50 flex justify-center items-center pointer-events-none">
                    <div 
                      class="rounded-full bg-slate-400/40"
                      :style="{
                        width: `${Math.round(90 * previewScale)}px`,
                        height: '3px',
                      }"
                    ></div>
                  </div>
                </div>
              </div>

              <!-- Quick Info Footer -->
              <div class="mt-2.5 flex items-center justify-between text-[11px] text-slate-500">
                <span class="truncate">
                  🎵 Musik: <span class="font-semibold text-slate-700">{{ previewMusicUrl ? 'Aktif' : 'Off' }}</span>
                </span>
                <a v-if="previewUrl" :href="previewUrl" target="_blank" class="font-semibold text-emerald-700 hover:underline flex items-center gap-0.5 shrink-0">
                  <span>Tab Baru</span>
                  <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                  </svg>
                </a>
              </div>
            </div>
          </aside>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
