<script setup>
import { ref } from 'vue'
import { resolveAssetUrl, resolveBackgroundVisual, animationClass } from '@/lib/invitationTheme'

const props = defineProps({
  decorations: { type: Object, required: true },
  character: { type: Object, default: () => ({}) },
  coverBackground: { type: Object, default: () => ({}) },
  palette: { type: Object, required: true },
})
const emit = defineEmits(['update:decorations', 'update:character'])

const SLOTS = ['top', 'bottom', 'left', 'right']
const canvas = ref(null)
const dragging = ref(null)

function getDecoUrl(slot) {
  return resolveAssetUrl(props.decorations[slot])
}
function getCharUrl() {
  return resolveAssetUrl(props.character)
}
function getBg() {
  return resolveBackgroundVisual(props.coverBackground)
}

function startDrag(slot, e) {
  e.preventDefault()
  dragging.value = slot
  window.addEventListener('mousemove', onMove)
  window.addEventListener('mouseup', stopDrag)
}
function startDragTouch(slot, e) {
  e.preventDefault()
  dragging.value = slot
  window.addEventListener('touchmove', onMoveTouch, { passive: false })
  window.addEventListener('touchend', stopDrag)
}
function onMove(e) {
  moveAt(e.clientX, e.clientY)
}
function onMoveTouch(e) {
  e.preventDefault()
  if (e.touches[0]) moveAt(e.touches[0].clientX, e.touches[0].clientY)
}
function moveAt(cx, cy) {
  if (!dragging.value || !canvas.value) return
  const rect = canvas.value.getBoundingClientRect()
  const x = Math.round(Math.max(0, Math.min(100, ((cx - rect.left) / rect.width) * 100)))
  const y = Math.round(Math.max(0, Math.min(100, ((cy - rect.top) / rect.height) * 100)))
  if (dragging.value === 'character') {
    emit('update:character', { ...props.character, x, y })
  } else {
    const newDeco = { ...props.decorations }
    newDeco[dragging.value] = { ...newDeco[dragging.value], x, y }
    emit('update:decorations', newDeco)
  }
}
function stopDrag() {
  dragging.value = null
  window.removeEventListener('mousemove', onMove)
  window.removeEventListener('mouseup', stopDrag)
  window.removeEventListener('touchmove', onMoveTouch)
  window.removeEventListener('touchend', stopDrag)
}
</script>

<template>
  <div class="space-y-1.5">
    <p class="text-[11px] text-slate-400">Drag ornamen untuk mengubah posisi 🖐</p>
    <div
      ref="canvas"
      class="relative w-full select-none overflow-hidden rounded-2xl border-2 border-slate-200 bg-slate-800"
      style="aspect-ratio: 9/18; max-height: 380px;"
    >
      <!-- Background -->
      <div
        class="absolute inset-0"
        :style="{
          background: getBg().type === 'image'
            ? `url(${getBg().value}) center/cover no-repeat`
            : `linear-gradient(160deg, ${palette.primary} 0%, ${palette.text} 100%)`
        }"
      />

      <!-- 4 Slot labels -->
      <div class="pointer-events-none absolute top-1 left-1/2 -translate-x-1/2 rounded bg-black/30 px-1.5 py-0.5 text-[9px] font-bold text-white/60">ATAS</div>
      <div class="pointer-events-none absolute bottom-1 left-1/2 -translate-x-1/2 rounded bg-black/30 px-1.5 py-0.5 text-[9px] font-bold text-white/60">BAWAH</div>
      <div class="pointer-events-none absolute left-0.5 top-1/2 -translate-y-1/2 rounded bg-black/30 px-0.5 py-1 text-[9px] font-bold text-white/60" style="writing-mode:vertical-rl">KIRI</div>
      <div class="pointer-events-none absolute right-0.5 top-1/2 -translate-y-1/2 rounded bg-black/30 px-0.5 py-1 text-[9px] font-bold text-white/60" style="writing-mode:vertical-rl">KANAN</div>

      <!-- 4 decoration slots -->
      <template v-for="slot in ['top', 'bottom', 'left', 'right']" :key="slot">
        <div
          v-if="getDecoUrl(slot)"
          class="absolute z-10 cursor-grab active:cursor-grabbing transition-transform"
          :class="[dragging === slot ? 'scale-110 z-20' : '', animationClass(decorations[slot]?.animation)]"
          :style="{
            left: (decorations[slot]?.x ?? 50) + '%',
            top: (decorations[slot]?.y ?? 50) + '%',
            transform: 'translate(-50%, -50%)',
            width: Math.min(decorations[slot]?.size ?? 120, 160) + 'px',
            opacity: (decorations[slot]?.opacity ?? 90) / 100,
          }"
          @mousedown="startDrag(slot, $event)"
          @touchstart="startDragTouch(slot, $event)"
        >
          <img :src="getDecoUrl(slot)" class="pointer-events-none h-auto w-full object-contain" alt="" />
          <div class="absolute -top-3 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-full bg-emerald-600 px-1.5 py-0.5 text-[8px] font-bold text-white opacity-80">{{ slot }}</div>
        </div>
        <div
          v-else
          class="pointer-events-none absolute flex items-center justify-center"
          :style="{ left: (decorations[slot]?.x ?? 50) + '%', top: (decorations[slot]?.y ?? 50) + '%', transform: 'translate(-50%,-50%)' }"
        >
          <div class="flex h-7 w-7 items-center justify-center rounded-full border-2 border-dashed border-white/20 text-white/20 text-xs">+</div>
        </div>
      </template>

      <!-- Character -->
      <div
        v-if="getCharUrl()"
        class="absolute z-10 cursor-grab active:cursor-grabbing"
        :style="{
          left: (character?.x ?? 50) + '%',
          top: (character?.y ?? 35) + '%',
          transform: 'translate(-50%, -50%)',
          width: Math.min(character?.size ?? 100, 140) + 'px',
        }"
        @mousedown="startDrag('character', $event)"
        @touchstart="startDragTouch('character', $event)"
      >
        <img :src="getCharUrl()" class="pointer-events-none h-auto w-full object-contain" alt="" />
        <div class="absolute -top-3 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-full bg-purple-600 px-1.5 py-0.5 text-[8px] font-bold text-white opacity-80">karakter</div>
      </div>

      <!-- Empty hint -->
      <div
        v-if="!getDecoUrl('top') && !getDecoUrl('bottom') && !getDecoUrl('left') && !getDecoUrl('right') && !getCharUrl()"
        class="absolute inset-0 flex flex-col items-center justify-center gap-2 text-white/30"
      >
        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
        <span class="text-xs">Pilih aset di panel kiri</span>
      </div>
    </div>
  </div>
</template>
