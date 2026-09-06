<script setup>
import { ref, computed } from 'vue'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import AssetGrid from './AssetGrid.vue'
import DecorationCanvas from './DecorationCanvas.vue'
import { PREMIUM_ASSETS } from '@/lib/invitationTheme'

const props = defineProps({
  builder: { type: Object, required: true },
  isPremium: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
})
const emit = defineEmits(['update:builder'])

function update(path, value) {
  // Use structuredClone or shallow copy of the necessary parts, but since Inertia form is deeply reactive, we can just emit the modified object or mutate and emit.
  // We'll mutate a shallow copy of the builder for simplicity, but preserve File objects by using a custom deep clone or just mutating the prop.
  const newBuilder = { ...props.builder }
  
  const keys = path.split('.')
  let obj = newBuilder
  for (let i = 0; i < keys.length - 1; i++) {
    if (!obj[keys[i]]) obj[keys[i]] = {}
    if (Array.isArray(obj[keys[i]])) {
      obj[keys[i]] = [...obj[keys[i]]]
    } else {
      obj[keys[i]] = { ...obj[keys[i]] }
    }
    obj = obj[keys[i]]
  }
  obj[keys[keys.length - 1]] = value
  
  // Re-assign files since we did shallow copies 
  if (props.builder.content?.music_file) {
    newBuilder.content.music_file = props.builder.content.music_file
  }
  if (props.builder.content?.cover_background_image?.file) {
    if (newBuilder.content.cover_background_image) newBuilder.content.cover_background_image.file = props.builder.content.cover_background_image.file
  }
  if (props.builder.content?.background_image?.file) {
    if (newBuilder.content.background_image) newBuilder.content.background_image.file = props.builder.content.background_image.file
  }
  if (props.builder.content?.character_image?.file) {
    if (newBuilder.content.character_image) newBuilder.content.character_image.file = props.builder.content.character_image.file
  }

  emit('update:builder', newBuilder)
}

function updateSlot(layer, slot, field, value) {
  update(`content.${layer}.${slot}.${field}`, value)
}

function getSlot(layer, slot) {
  return props.builder.content?.[layer]?.[slot] || {}
}

const activeTab = ref('cover') // 'cover' | 'content'
const activeSlot = ref('top')  // 'top' | 'bottom' | 'left' | 'right'

const SLOT_LABELS = { top: 'Atas', bottom: 'Bawah', left: 'Kiri', right: 'Kanan' }
const ANIM_OPTIONS = [
  { value: 'none', label: 'Tidak Ada' },
  { value: 'float', label: 'Mengambang' },
  { value: 'pulse', label: 'Berdenyut' },
  { value: 'spin-slow', label: 'Berputar' },
]

const currentLayer = computed(() => activeTab.value === 'cover' ? 'cover_decorations' : 'content_decorations')
const currentSlotData = computed(() => getSlot(currentLayer.value, activeSlot.value))

const coverBg = computed(() => props.builder.content?.cover_background_image || {})
const contentBg = computed(() => props.builder.content?.background_image || {})
const character = computed(() => props.builder.content?.character_image || {})
const palette = computed(() => props.builder.content?.palette || {})
const coverDecos = computed(() => props.builder.content?.cover_decorations || {})
const contentDecos = computed(() => props.builder.content?.content_decorations || {})
</script>

<template>
  <div class="space-y-5">

    <!-- Layer Tabs -->
    <div class="flex gap-1 rounded-xl bg-slate-100 p-1">
      <button
        v-for="tab in [{ key: 'cover', label: '🖼 Cover' }, { key: 'content', label: '📄 Konten' }]"
        :key="tab.key"
        type="button"
        @click="activeTab = tab.key"
        :class="[
          'flex-1 rounded-lg px-3 py-2 text-xs font-semibold transition',
          activeTab === tab.key ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-500 hover:text-slate-700'
        ]"
      >{{ tab.label }}</button>
    </div>

    <!-- Background Section -->
    <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-4 space-y-3">
      <Label class="text-xs font-bold text-slate-700">
        {{ activeTab === 'cover' ? '🎨 Background Cover' : '🎨 Background Konten' }}
      </Label>
      <div v-if="isPremium">
        <AssetGrid
          :modelValue="activeTab === 'cover' ? coverBg.type : contentBg.type"
          :assets="PREMIUM_ASSETS.backgrounds"
          :disabled="disabled"
          @update:modelValue="update(activeTab === 'cover' ? 'content.cover_background_image.type' : 'content.background_image.type', $event)"
        />
      </div>
      <p v-else class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700 mb-2">👑 Background preset tersedia untuk akun Premium</p>
      
      <div class="space-y-1.5 pt-2 border-t border-slate-200/60">
        <Label class="text-[10px] text-slate-500">Atau upload background sendiri (Opsional)</Label>
        <input 
          type="file" 
          accept="image/*" 
          :disabled="disabled"
          @input="update(activeTab === 'cover' ? 'content.cover_background_image.file' : 'content.background_image.file', $event.target.files[0])"
          class="block w-full rounded-xl border border-slate-200 px-3 py-1.5 text-xs disabled:bg-slate-100" 
        />
        <p v-if="activeTab === 'cover' && builder.content.cover_background_image?.uploaded_url" class="text-[10px] text-emerald-700 truncate">
          ✓ File terupload: {{ builder.content.cover_background_image.uploaded_url.split('/').pop() }}
        </p>
        <p v-else-if="activeTab === 'content' && builder.content.background_image?.uploaded_url" class="text-[10px] text-emerald-700 truncate">
          ✓ File terupload: {{ builder.content.background_image.uploaded_url.split('/').pop() }}
        </p>
      </div>
    </div>

    <!-- Decoration Slots -->
    <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-4 space-y-3">
      <Label class="text-xs font-bold text-slate-700">🌸 Ornamen Dekorasi</Label>

      <!-- Slot Selector -->
      <div class="flex gap-1">
        <button
          v-for="(label, slot) in SLOT_LABELS"
          :key="slot"
          type="button"
          @click="activeSlot = slot"
          :class="[
            'flex-1 rounded-lg border px-2 py-1.5 text-[11px] font-semibold transition',
            activeSlot === slot
              ? 'border-emerald-400 bg-emerald-50 text-emerald-700'
              : 'border-slate-200 bg-white text-slate-500 hover:border-slate-300'
          ]"
        >{{ label }}</button>
      </div>

      <!-- Asset picker for active slot -->
      <div v-if="isPremium">
        <AssetGrid
          :modelValue="currentSlotData.type || 'none'"
          :assets="PREMIUM_ASSETS.decorations"
          :disabled="disabled"
          @update:modelValue="updateSlot(currentLayer, activeSlot, 'type', $event)"
        />
      </div>
      <p v-else class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700">👑 Ornamen premium tersedia untuk akun Premium</p>

      <!-- Slot Controls (only when an asset is selected) -->
      <div v-if="currentSlotData.type && currentSlotData.type !== 'none'" class="grid grid-cols-2 gap-3 pt-1">
        <div class="space-y-1">
          <Label class="text-[10px] text-slate-500">Ukuran (px)</Label>
          <input
            type="range" min="40" max="500" step="10"
            :value="currentSlotData.size || 180"
            :disabled="disabled"
            @input="updateSlot(currentLayer, activeSlot, 'size', +$event.target.value)"
            class="w-full accent-emerald-600"
          />
          <span class="text-[10px] text-slate-400">{{ currentSlotData.size || 180 }}px</span>
        </div>
        <div class="space-y-1">
          <Label class="text-[10px] text-slate-500">Opacity (%)</Label>
          <input
            type="range" min="10" max="100" step="5"
            :value="currentSlotData.opacity || 90"
            :disabled="disabled"
            @input="updateSlot(currentLayer, activeSlot, 'opacity', +$event.target.value)"
            class="w-full accent-emerald-600"
          />
          <span class="text-[10px] text-slate-400">{{ currentSlotData.opacity || 90 }}%</span>
        </div>
        <div class="col-span-2 space-y-1">
          <Label class="text-[10px] text-slate-500">Animasi</Label>
          <select
            :value="currentSlotData.animation || 'none'"
            :disabled="disabled"
            @change="updateSlot(currentLayer, activeSlot, 'animation', $event.target.value)"
            class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-xs disabled:bg-slate-100"
          >
            <option v-for="a in ANIM_OPTIONS" :key="a.value" :value="a.value">{{ a.label }}</option>
          </select>
        </div>
        <div class="col-span-2 grid grid-cols-2 gap-2">
          <div class="space-y-1">
            <Label class="text-[10px] text-slate-500">Posisi X ({{ currentSlotData.x ?? 50 }}%)</Label>
            <input
              type="range" min="0" max="100" step="1"
              :value="currentSlotData.x ?? 50"
              :disabled="disabled"
              @input="updateSlot(currentLayer, activeSlot, 'x', +$event.target.value)"
              class="w-full accent-emerald-600"
            />
          </div>
          <div class="space-y-1">
            <Label class="text-[10px] text-slate-500">Posisi Y ({{ currentSlotData.y ?? 50 }}%)</Label>
            <input
              type="range" min="0" max="100" step="1"
              :value="currentSlotData.y ?? 50"
              :disabled="disabled"
              @input="updateSlot(currentLayer, activeSlot, 'y', +$event.target.value)"
              class="w-full accent-emerald-600"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Character Section (Cover only) -->
    <div v-if="activeTab === 'cover'" class="rounded-xl border border-slate-100 bg-slate-50/60 p-4 space-y-3">
      <Label class="text-xs font-bold text-slate-700">👫 Ilustrasi / Karakter Tengah</Label>
      <div>
        <AssetGrid
          :modelValue="character.type || 'none'"
          :assets="[
            { key: 'preset-couple-1', path: 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\'%3E%3Ctext y=\'.9em\' font-size=\'90\'%3E🕌%3C/text%3E%3C/svg%3E', label: 'Masjid' },
            { key: 'preset-couple-2', path: 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\'%3E%3Ctext y=\'.9em\' font-size=\'90\'%3E💍%3C/text%3E%3C/svg%3E', label: 'Cincin' },
            { key: 'preset-couple-3', path: 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\'%3E%3Ctext y=\'.9em\' font-size=\'90\'%3E❤%3C/text%3E%3C/svg%3E', label: 'Hati' },
            ...PREMIUM_ASSETS.characters,
          ]"
          :disabled="disabled"
          @update:modelValue="update('content.character_image.type', $event)"
        />
      </div>
      <!-- Character controls -->
      <div v-if="character.type && character.type !== 'none'" class="grid grid-cols-2 gap-3">
        <div class="space-y-1">
          <Label class="text-[10px] text-slate-500">Ukuran ({{ character.size || 120 }}px)</Label>
          <input type="range" min="40" max="400" step="10"
            :value="character.size || 120" :disabled="disabled"
            @input="update('content.character_image.size', +$event.target.value)"
            class="w-full accent-purple-600" />
        </div>
        <div class="space-y-1">
          <Label class="text-[10px] text-slate-500">Posisi Y ({{ character.y ?? 35 }}%)</Label>
          <input type="range" min="0" max="100" step="1"
            :value="character.y ?? 35" :disabled="disabled"
            @input="update('content.character_image.y', +$event.target.value)"
            class="w-full accent-purple-600" />
        </div>
        <div class="space-y-1">
          <Label class="text-[10px] text-slate-500">Posisi X ({{ character.x ?? 50 }}%)</Label>
          <input type="range" min="0" max="100" step="1"
            :value="character.x ?? 50" :disabled="disabled"
            @input="update('content.character_image.x', +$event.target.value)"
            class="w-full accent-purple-600" />
        </div>
      </div>
    </div>

    <!-- Drag Canvas Preview -->
    <DecorationCanvas
      :decorations="activeTab === 'cover' ? coverDecos : contentDecos"
      :character="activeTab === 'cover' ? character : {}"
      :coverBackground="activeTab === 'cover' ? coverBg : contentBg"
      :palette="palette"
      @update:decorations="update(activeTab === 'cover' ? 'content.cover_decorations' : 'content.content_decorations', $event)"
      @update:character="update('content.character_image', $event)"
    />

  </div>
</template>
