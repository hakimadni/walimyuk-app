<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, ref, watch, onMounted, onUnmounted } from 'vue'
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
const previewContainer = ref(null)
const previewScale = ref(1)

function updateScale() {
  if (previewContainer.value) {
    const width = previewContainer.value.clientWidth
    previewScale.value = width / 1080
  }
}

onMounted(() => {
  updateScale()
  window.addEventListener('resize', updateScale)
})

onUnmounted(() => {
  window.removeEventListener('resize', updateScale)
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
  ? `/dashboard/weddings/${props.wedding.id}/builder`
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

function submit() {
  form.post(submitUrl.value, { forceFormData: true })
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
          <Link :href="`/dashboard/weddings/${wedding.id}`">
            <Button variant="outline" size="sm" class="text-xs font-semibold">Detail</Button>
          </Link>
          <Link :href="`/dashboard/weddings/${wedding.id}/guests`">
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

    <div class="py-6">
      <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
        <form @submit.prevent="submit" class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(380px,1fr)]">
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
                </div>
              </div>

              <!-- Music Section -->
              <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                <h3 class="font-serif text-lg font-bold text-emerald-950">Musik Latar Undangan</h3>
                <div class="space-y-1.5">
                  <Label>URL Musik (Streaming / Direct MP3)</Label>
                  <Input v-model="form.builder.content.music_url" :disabled="lockDisabled('music')" placeholder="https://domain.com/music.mp3" />
                </div>
                <div class="space-y-1.5">
                  <Label>Upload File Musik (opsional)</Label>
                  <input type="file" accept="audio/*" :disabled="lockDisabled('music')" @input="form.builder.content.music_file = $event.target.files[0]" class="block w-full rounded-xl border border-slate-200 px-3 py-1.5 text-xs disabled:bg-slate-100" />
                </div>
                <p v-if="form.builder.content.music_uploaded_url" class="text-xs text-emerald-700 truncate">
                  ✓ File terupload: {{ form.builder.content.music_uploaded_url }}
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
          <aside class="space-y-6">
            <div class="sticky top-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
              <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                  <h3 class="font-serif text-base font-bold text-emerald-950">Live Preview Interaktif</h3>
                  <p class="text-[11px] text-slate-400">Tampilan asli di mobile</p>
                </div>
              </div>

              <!-- Phone Screen Mockup Frame -->
              <div class="mt-4 overflow-hidden rounded-[32px] border-[6px] border-slate-900 shadow-2xl bg-slate-950 relative">
                <!-- Phone Speaker / Camera Notch -->
                <div class="absolute top-0 inset-x-0 h-4 z-50 bg-slate-900 flex justify-center items-center rounded-b-xl w-32 mx-auto">
                  <div class="h-1.5 w-12 rounded-full bg-slate-700"></div>
                </div>

                <!-- Preview Viewport Screen -->
                <div class="relative w-full aspect-[9/20] bg-white overflow-hidden" ref="previewContainer">
                  <iframe 
                    v-if="previewUrl" 
                    ref="previewIframe" 
                    :src="previewUrl" 
                    class="absolute top-0 left-0 border-0" 
                    :style="{ 
                      width: '1080px', 
                      height: '2400px', 
                      transform: `scale(${previewScale})`, 
                      transformOrigin: 'top left' 
                    }"
                  ></iframe>
                  <div v-else class="flex h-full items-center justify-center p-6 text-center text-slate-400 text-sm">
                    Live preview belum tersedia. Silakan simpan builder pertama kali.
                  </div>
                </div>
              </div>

              <!-- Quick Info Footer -->
              <div class="mt-4 space-y-1 text-xs text-slate-500">
                <p><span class="font-semibold text-slate-700">Musik latar:</span> {{ previewMusicUrl ? 'Aktif' : 'Tidak ada musik' }}</p>
                <p v-if="previewUrl">
                  <a :href="previewUrl" target="_blank" class="font-semibold text-emerald-700 hover:underline flex items-center gap-1 mt-1">
                    <span>Lihat Full Invitation Online</span>
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                  </a>
                </p>
              </div>
            </div>
          </aside>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
