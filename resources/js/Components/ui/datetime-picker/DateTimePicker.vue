<script setup>
import { computed, ref, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  placeholder: {
    type: String,
    default: 'Pilih tanggal & waktu pernikahan',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  id: {
    type: String,
    default: 'datetime-picker',
  },
})

const emit = defineEmits(['update:modelValue'])

const isOpen = ref(false)

// Parse initial model value
function parseInitialValue(val) {
  if (!val) {
    const now = new Date()
    return {
      year: now.getFullYear(),
      month: now.getMonth(), // 0-indexed
      day: now.getDate(),
      hour: 8,
      minute: 0,
    }
  }

  // Handle 'YYYY-MM-DDTHH:mm', 'YYYY-MM-DD HH:mm:ss', or 'YYYY-MM-DD'
  const clean = val.replace(' ', 'T')
  const dateObj = new Date(clean)
  if (isNaN(dateObj.getTime())) {
    const now = new Date()
    return {
      year: now.getFullYear(),
      month: now.getMonth(),
      day: now.getDate(),
      hour: 8,
      minute: 0,
    }
  }

  return {
    year: dateObj.getFullYear(),
    month: dateObj.getMonth(),
    day: dateObj.getDate(),
    hour: dateObj.getHours(),
    minute: dateObj.getMinutes(),
  }
}

const initial = parseInitialValue(props.modelValue)
const selectedYear = ref(initial.year)
const selectedMonth = ref(initial.month)
const selectedDay = ref(props.modelValue ? initial.day : null)
const selectedHour = ref(initial.hour)
const selectedMinute = ref(initial.minute)

// View navigation in calendar
const viewYear = ref(initial.year)
const viewMonth = ref(initial.month)

watch(
  () => props.modelValue,
  (newVal) => {
    if (newVal) {
      const parsed = parseInitialValue(newVal)
      selectedYear.value = parsed.year
      selectedMonth.value = parsed.month
      selectedDay.value = parsed.day
      selectedHour.value = parsed.hour
      selectedMinute.value = parsed.minute
      viewYear.value = parsed.year
      viewMonth.value = parsed.month
    } else {
      selectedDay.value = null
    }
  }
)

const monthNames = [
  'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
]

const dayHeaders = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']

const currentMonthName = computed(() => monthNames[viewMonth.value])

// Calendar days calculation
const calendarDays = computed(() => {
  const year = viewYear.value
  const month = viewMonth.value

  const firstDayIndex = new Date(year, month, 1).getDay() // 0 = Sunday
  const totalDaysInMonth = new Date(year, month + 1, 0).getDate()
  const prevMonthTotalDays = new Date(year, month, 0).getDate()

  const days = []

  // Previous month padding days
  for (let i = firstDayIndex - 1; i >= 0; i--) {
    days.push({
      day: prevMonthTotalDays - i,
      month: month - 1,
      year: month === 0 ? year - 1 : year,
      isCurrentMonth: false,
    })
  }

  // Current month days
  for (let d = 1; d <= totalDaysInMonth; d++) {
    days.push({
      day: d,
      month: month,
      year: year,
      isCurrentMonth: true,
    })
  }

  // Next month padding days to fill 35 or 42 grid cells
  const remaining = (7 - (days.length % 7)) % 7
  for (let n = 1; n <= remaining; n++) {
    days.push({
      day: n,
      month: month + 1,
      year: month === 11 ? year + 1 : year,
      isCurrentMonth: false,
    })
  }

  return days
})

function prevMonth() {
  if (viewMonth.value === 0) {
    viewMonth.value = 11
    viewYear.value--
  } else {
    viewMonth.value--
  }
}

function nextMonth() {
  if (viewMonth.value === 11) {
    viewMonth.value = 0
    viewYear.value++
  } else {
    viewMonth.value++
  }
}

function selectDate(item) {
  selectedYear.value = item.year
  selectedMonth.value = item.month
  selectedDay.value = item.day
  viewYear.value = item.year
  viewMonth.value = item.month
  emitFormattedValue()
}

function setQuickPreset(monthsToAdd) {
  const target = new Date()
  target.setMonth(target.getMonth() + monthsToAdd)
  selectedYear.value = target.getFullYear()
  selectedMonth.value = target.getMonth()
  selectedDay.value = target.getDate()
  viewYear.value = target.getFullYear()
  viewMonth.value = target.getMonth()
  emitFormattedValue()
}

function setTimePreset(hour, minute) {
  selectedHour.value = hour
  selectedMinute.value = minute
  if (selectedDay.value === null) {
    const today = new Date()
    selectedYear.value = today.getFullYear()
    selectedMonth.value = today.getMonth()
    selectedDay.value = today.getDate()
  }
  emitFormattedValue()
}

function emitFormattedValue() {
  if (selectedDay.value === null) {
    emit('update:modelValue', '')
    return
  }

  const yyyy = selectedYear.value
  const mm = String(selectedMonth.value + 1).padStart(2, '0')
  const dd = String(selectedDay.value).padStart(2, '0')
  const hh = String(selectedHour.value).padStart(2, '0')
  const ii = String(selectedMinute.value).padStart(2, '0')

  const val = `${yyyy}-${mm}-${dd}T${hh}:${ii}`
  emit('update:modelValue', val)
}

function clearValue() {
  selectedDay.value = null
  emit('update:modelValue', '')
  isOpen.value = false
}

function isSelected(item) {
  return (
    selectedDay.value === item.day &&
    selectedMonth.value === item.month &&
    selectedYear.value === item.year
  )
}

function isToday(item) {
  const now = new Date()
  return (
    now.getDate() === item.day &&
    now.getMonth() === item.month &&
    now.getFullYear() === item.year
  )
}

const formattedDisplay = computed(() => {
  if (!props.modelValue || selectedDay.value === null) return ''

  try {
    const yyyy = selectedYear.value
    const mm = selectedMonth.value
    const dd = selectedDay.value
    const hh = String(selectedHour.value).padStart(2, '0')
    const ii = String(selectedMinute.value).padStart(2, '0')

    const date = new Date(yyyy, mm, dd)
    const dayName = new Intl.DateTimeFormat('id-ID', { weekday: 'long' }).format(date)
    const monthName = monthNames[mm]

    return `${dayName}, ${dd} ${monthName} ${yyyy} • ${hh}:${ii} WIB`
  } catch (e) {
    return props.modelValue
  }
})
</script>

<template>
  <div class="relative w-full">
    <!-- Trigger Input Box -->
    <div
      :id="id"
      class="group flex w-full cursor-pointer items-center justify-between rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm shadow-sm transition-all hover:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
      :class="{
        'border-emerald-600 ring-2 ring-emerald-500/20': isOpen,
        'pointer-events-none bg-slate-100 opacity-60': disabled,
      }"
      @click="isOpen = !isOpen"
    >
      <div class="flex items-center gap-2.5 truncate">
        <span class="text-emerald-700">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
        </span>
        <span v-if="formattedDisplay" class="font-medium text-slate-800">{{ formattedDisplay }}</span>
        <span v-else class="text-slate-400">{{ placeholder }}</span>
      </div>

      <div class="flex items-center gap-1">
        <button
          v-if="modelValue"
          type="button"
          class="rounded p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
          title="Hapus Tanggal"
          @click.stop="clearValue"
        >
          <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
        <span class="text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': isOpen }">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
          </svg>
        </span>
      </div>
    </div>

    <!-- Backdrop overlay for outside click -->
    <div
      v-if="isOpen"
      class="fixed inset-0 z-40"
      @click="isOpen = false"
    />

    <!-- Popover Card -->
    <div
      v-if="isOpen"
      class="absolute left-0 top-full z-50 mt-2 w-full min-w-[320px] max-w-sm rounded-2xl border border-emerald-100 bg-white p-4 shadow-xl ring-1 ring-black/5"
    >
      <!-- Quick Presets Header -->
      <div class="mb-3 flex flex-wrap items-center gap-1.5 border-b border-slate-100 pb-3">
        <span class="text-xs font-semibold text-slate-400">Quick:</span>
        <button
          type="button"
          class="rounded-lg bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-800 transition hover:bg-emerald-100"
          @click="setQuickPreset(0)"
        >
          Hari Ini
        </button>
        <button
          type="button"
          class="rounded-lg bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-800 transition hover:bg-emerald-100"
          @click="setQuickPreset(1)"
        >
          +1 Bulan
        </button>
        <button
          type="button"
          class="rounded-lg bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-800 transition hover:bg-emerald-100"
          @click="setQuickPreset(3)"
        >
          +3 Bulan
        </button>
        <button
          type="button"
          class="rounded-lg bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-800 transition hover:bg-emerald-100"
          @click="setQuickPreset(6)"
        >
          +6 Bulan
        </button>
      </div>

      <!-- Month & Year Navigation -->
      <div class="mb-3 flex items-center justify-between">
        <button
          type="button"
          class="rounded-lg p-1.5 text-slate-600 hover:bg-slate-100"
          @click="prevMonth"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
          </svg>
        </button>

        <div class="flex items-center gap-1 text-sm font-bold text-emerald-950">
          <span>{{ currentMonthName }}</span>
          <span>{{ viewYear }}</span>
        </div>

        <button
          type="button"
          class="rounded-lg p-1.5 text-slate-600 hover:bg-slate-100"
          @click="nextMonth"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>

      <!-- Day Headers -->
      <div class="mb-1 grid grid-cols-7 text-center text-[11px] font-semibold text-slate-400">
        <span v-for="d in dayHeaders" :key="d" :class="{ 'text-red-500': d === 'Min' }">{{ d }}</span>
      </div>

      <!-- Calendar Days Grid -->
      <div class="grid grid-cols-7 gap-1 text-center text-xs">
        <button
          v-for="(item, idx) in calendarDays"
          :key="idx"
          type="button"
          class="relative flex h-8 w-8 items-center justify-center rounded-lg font-medium transition-all"
          :class="[
            !item.isCurrentMonth ? 'text-slate-300' : 'text-slate-700 hover:bg-emerald-50 hover:text-emerald-700',
            isSelected(item) ? '!bg-emerald-700 !text-white font-bold shadow-sm' : '',
            isToday(item) && !isSelected(item) ? 'border border-emerald-500 font-bold text-emerald-700' : '',
          ]"
          @click="selectDate(item)"
        >
          {{ item.day }}
        </button>
      </div>

      <!-- Time Selection Section -->
      <div class="mt-4 border-t border-slate-100 pt-3">
        <div class="flex items-center justify-between text-xs font-semibold text-slate-600 mb-2">
          <span>Waktu / Jam Akad &amp; Resepsi:</span>
          <div class="flex items-center gap-1">
            <button
              type="button"
              class="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] text-slate-600 hover:bg-emerald-100 hover:text-emerald-800"
              @click="setTimePreset(8, 0)"
            >
              08:00
            </button>
            <button
              type="button"
              class="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] text-slate-600 hover:bg-emerald-100 hover:text-emerald-800"
              @click="setTimePreset(10, 0)"
            >
              10:00
            </button>
            <button
              type="button"
              class="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] text-slate-600 hover:bg-emerald-100 hover:text-emerald-800"
              @click="setTimePreset(13, 0)"
            >
              13:00
            </button>
            <button
              type="button"
              class="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] text-slate-600 hover:bg-emerald-100 hover:text-emerald-800"
              @click="setTimePreset(19, 0)"
            >
              19:00
            </button>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-2">
          <div>
            <label class="block text-[10px] text-slate-400 mb-1">Jam (00 - 23)</label>
            <select
              v-model="selectedHour"
              class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-xs text-slate-700 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
              @change="emitFormattedValue"
            >
              <option v-for="h in 24" :key="h - 1" :value="h - 1">
                {{ String(h - 1).padStart(2, '0') }}:00
              </option>
            </select>
          </div>
          <div>
            <label class="block text-[10px] text-slate-400 mb-1">Menit (00 - 55)</label>
            <select
              v-model="selectedMinute"
              class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-xs text-slate-700 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
              @change="emitFormattedValue"
            >
              <option v-for="m in [0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55]" :key="m" :value="m">
                :{{ String(m).padStart(2, '0') }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <!-- Action Footer -->
      <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3">
        <button
          type="button"
          class="text-xs text-slate-400 hover:text-slate-600"
          @click="clearValue"
        >
          Reset
        </button>
        <button
          type="button"
          class="rounded-xl bg-emerald-700 px-4 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-800 transition"
          @click="isOpen = false"
        >
          Selesai
        </button>
      </div>
    </div>
  </div>
</template>
