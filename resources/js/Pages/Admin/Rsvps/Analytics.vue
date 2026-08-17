<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Button } from '@/components/ui/button'

const props = defineProps({
  wedding: { type: Object, required: true },
  analytics: { type: Object, required: true },
})

const pricePerPax = ref(65000)
const bufferPct = ref(props.analytics.buffer_percentage || 10)

const confirmedPax = computed(() => props.analytics.confirmed_pax || 0)
const totalQuota = computed(() => props.analytics.total_invitation_quota || (props.analytics.invited_guest_count * 2) || 200)

const recommendedPax = computed(() => {
  return Math.ceil(confirmedPax.value * (1 + bufferPct.value / 100))
})

const smartCateringCost = computed(() => {
  return recommendedPax.value * pricePerPax.value
})

const traditionalOrderPax = computed(() => {
  // Traditional estimation in Indonesia: total invitation * 2
  return totalQuota.value
})

const traditionalCost = computed(() => {
  return traditionalOrderPax.value * pricePerPax.value
})

const estimatedSavings = computed(() => {
  return Math.max(0, traditionalCost.value - smartCateringCost.value)
})

function formatRupiah(val) {
  return 'Rp ' + Number(val).toLocaleString('id-ID')
}
</script>

<template>
  <Head :title="`Analisis Katering & Anggaran - ${wedding.cover_subtitle || wedding.cover_title}`" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <div class="flex items-center gap-2">
            <Link :href="`/dashboard/weddings/${wedding.id}/rsvps`" class="text-xs text-emerald-700 hover:underline">&larr; Kembali ke Rekap RSVP</Link>
          </div>
          <h2 class="font-serif text-3xl font-bold text-emerald-950">Analisis Katering &amp; Anggaran Porsi</h2>
          <p class="mt-1 text-sm text-slate-500">{{ wedding.cover_title }} • Simulasi perhitungan porsi anti mubazir &amp; anti tekor.</p>
        </div>
        <div>
          <a :href="`/dashboard/weddings/${wedding.id}/rsvps/export`" download>
            <Button class="rounded-xl bg-emerald-700 text-xs font-semibold text-white shadow-sm hover:bg-emerald-800">
              📤 Ekspor Laporan Vendor Katering
            </Button>
          </a>
        </div>
      </div>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
        <!-- Interactive Simulator Card -->
        <div class="grid gap-6 lg:grid-cols-12">
          <!-- Left: Sliders and Inputs -->
          <div class="lg:col-span-6 space-y-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div>
              <div class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">
                <span>🎛️</span> Parameter Simulasi Biaya
              </div>
              <h3 class="mt-3 font-serif text-xl font-bold text-slate-900">Sesuaikan Biaya Vendor Anda</h3>
              <p class="text-xs text-slate-500 mt-1">Ubah harga paket prasmanan per porsi dan toleransi cadangan buffer.</p>
            </div>

            <!-- Price Per Pax Slider -->
            <div class="space-y-2">
              <div class="flex items-center justify-between text-xs">
                <label class="font-bold text-slate-700">Harga Paket Katering / Porsi</label>
                <span class="font-serif text-sm font-bold text-emerald-800">{{ formatRupiah(pricePerPax) }}</span>
              </div>
              <input
                type="range"
                min="25000"
                max="250000"
                step="5000"
                v-model.number="pricePerPax"
                class="h-2 w-full cursor-pointer appearance-none rounded-lg bg-slate-200 accent-emerald-700"
              />
              <div class="flex justify-between text-[10px] text-slate-400">
                <span>Rp 25.000</span>
                <span>Rp 125.000</span>
                <span>Rp 250.000</span>
              </div>
            </div>

            <!-- Safety Buffer Slider -->
            <div class="space-y-2">
              <div class="flex items-center justify-between text-xs">
                <label class="font-bold text-slate-700">Persentase Safety Buffer (Cadangan)</label>
                <span class="font-serif text-sm font-bold text-amber-700">+{{ bufferPct }}%</span>
              </div>
              <input
                type="range"
                min="0"
                max="30"
                step="5"
                v-model.number="bufferPct"
                class="h-2 w-full cursor-pointer appearance-none rounded-lg bg-slate-200 accent-amber-600"
              />
              <p class="text-[11px] text-slate-500">
                Direkomendasikan 10% untuk mengantisipasi tamu yang membawa keluarga tambahan tanpa konfirmasi.
              </p>
            </div>

            <!-- Summary Status -->
            <div class="rounded-2xl bg-slate-50 p-4 space-y-2 border border-slate-200/80">
              <div class="flex justify-between text-xs">
                <span class="text-slate-500">Pax Terkonfirmasi Hadir:</span>
                <span class="font-bold text-slate-800">{{ confirmedPax }} Pax</span>
              </div>
              <div class="flex justify-between text-xs">
                <span class="text-slate-500">Porsi Cadangan (+{{ bufferPct }}%):</span>
                <span class="font-bold text-amber-700">+{{ recommendedPax - confirmedPax }} Porsi</span>
              </div>
              <div class="border-t border-slate-200 pt-2 flex justify-between text-sm font-bold">
                <span class="text-emerald-950">Total Pesan ke Katering:</span>
                <span class="text-emerald-800 font-serif text-base">{{ recommendedPax }} Porsi</span>
              </div>
            </div>
          </div>

          <!-- Right: Smart Comparison Card -->
          <div class="lg:col-span-6 flex flex-col justify-between rounded-3xl border border-emerald-900 bg-gradient-to-br from-emerald-950 via-emerald-900 to-emerald-950 p-6 text-white shadow-xl sm:p-8">
            <div class="space-y-6">
              <div>
                <span class="rounded-full bg-amber-400 px-3 py-1 text-xs font-bold text-stone-950 shadow">
                  ★ Rekomendasi Efisiensi Anggaran
                </span>
                <h3 class="mt-4 font-serif text-2xl font-bold text-white">
                  Hemat hingga {{ formatRupiah(estimatedSavings) }}
                </h3>
                <p class="mt-1 text-xs text-emerald-200">
                  Perbandingan estimasi pesanan berbasis RSVP Real-time vs Cara Tradisional (tebak-tebak 2x kuota).
                </p>
              </div>

              <!-- Comparison Table -->
              <div class="space-y-3">
                <!-- Smart Way -->
                <div class="rounded-2xl bg-white/10 p-4 border border-emerald-400/30">
                  <div class="flex items-center justify-between text-xs font-semibold text-emerald-200">
                    <span>💡 Menggunakan WalimYuk (RSVP + Buffer)</span>
                    <span class="rounded bg-emerald-500/20 px-2 py-0.5 text-amber-300 font-bold">{{ recommendedPax }} Porsi</span>
                  </div>
                  <div class="mt-2 font-serif text-2xl font-bold text-white">
                    {{ formatRupiah(smartCateringCost) }}
                  </div>
                </div>

                <!-- Traditional Way -->
                <div class="rounded-2xl bg-black/20 p-4 border border-white/10">
                  <div class="flex items-center justify-between text-xs text-stone-300">
                    <span>⚠️ Cara Tradisional (Asumsi Kuota Penuh)</span>
                    <span class="text-stone-400">{{ traditionalOrderPax }} Porsi</span>
                  </div>
                  <div class="mt-2 font-serif text-xl font-bold text-stone-300">
                    {{ formatRupiah(traditionalCost) }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Note -->
            <div class="mt-6 rounded-xl bg-emerald-900/60 p-3 text-[11px] text-emerald-200/90 border border-emerald-700/50">
              📌 <strong>Tips:</strong> Kirimkan rekap porsi ini ke vendor katering 3 hari sebelum acara (H-3) setelah batas akhir RSVP ditutup.
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
