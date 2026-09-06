<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  CategoryScale,
  LinearScale,
  ArcElement,
  PointElement,
  LineElement,
  LineController,
  PieController
} from 'chart.js'
import { Line, Pie } from 'vue-chartjs'

ChartJS.register(
  CategoryScale,
  LinearScale,
  ArcElement,
  PointElement,
  LineElement,
  LineController,
  PieController,
  Title,
  Tooltip,
  Legend
)

const props = defineProps({
  metrics: Object,
  chartData: Object,
  recentWeddings: Array,
  isAdmin: Boolean
})

const trendChartData = computed(() => {
  const datasets = [
    {
      label: 'Undangan Baru',
      backgroundColor: '#059669',
      borderColor: '#059669',
      data: props.chartData.weddings,
      tension: 0.3
    }
  ]
  
  if (props.isAdmin) {
    datasets.push({
      label: 'User Baru',
      backgroundColor: '#4f46e5',
      borderColor: '#4f46e5',
      data: props.chartData.users,
      tension: 0.3
    })
  }

  return {
    labels: props.chartData.labels,
    datasets
  }
})

const rsvpChartData = computed(() => ({
  labels: ['Hadir', 'Tidak Hadir'],
  datasets: [
    {
      backgroundColor: ['#059669', '#ef4444'],
      data: [props.chartData.rsvp.attending, props.chartData.rsvp.notAttending]
    }
  ]
}))

const lineOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { position: 'bottom' } }
}

const pieOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { position: 'bottom' } }
}
</script>

<template>
  <Head title="Dashboard" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-serif text-2xl font-bold leading-tight text-emerald-900">
        Dashboard Overview
      </h2>
    </template>

    <div class="py-6 space-y-8">
      <!-- Welcome card -->
      <div class="rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-600 p-8 text-white shadow-lg relative overflow-hidden">
        <div class="absolute right-0 top-0 opacity-10">
          <svg class="h-48 w-48 -mr-10 -mt-10" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
        </div>
        <div class="relative z-10">
          <h3 class="text-2xl font-bold font-serif mb-2">Selamat datang kembali, {{ $page.props.auth.user.name }}!</h3>
          <p class="text-emerald-50 max-w-xl text-sm leading-relaxed">
            Pantau semua aktivitas undangan digital Anda melalui dashboard ini. 
            Anda dapat melihat ringkasan tamu yang RSVP, ucapan doa terbaru, hingga mengelola detail acara.
          </p>
        </div>
      </div>

      <!-- Metrics Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm hover:shadow-md transition">
          <p class="text-xs font-semibold text-slate-500 mb-1 uppercase tracking-wider">Total Undangan</p>
          <p class="text-3xl font-bold text-slate-900">{{ metrics.totalWeddings }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm hover:shadow-md transition">
          <p class="text-xs font-semibold text-slate-500 mb-1 uppercase tracking-wider">Total Tamu Diundang</p>
          <p class="text-3xl font-bold text-slate-900">{{ metrics.totalGuests }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm hover:shadow-md transition">
          <p class="text-xs font-semibold text-slate-500 mb-1 uppercase tracking-wider">RSVP Masuk</p>
          <p class="text-3xl font-bold text-slate-900">{{ metrics.totalRsvps }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm hover:shadow-md transition">
          <p class="text-xs font-semibold text-slate-500 mb-1 uppercase tracking-wider">Ucapan & Doa</p>
          <p class="text-3xl font-bold text-slate-900">{{ metrics.totalWishes }}</p>
        </div>
      </div>
      
      <div v-if="isAdmin && metrics.totalUsers !== null" class="rounded-xl border border-indigo-200 bg-indigo-50 p-6 shadow-sm flex items-center justify-between hover:shadow-md transition">
        <div>
          <p class="text-xs font-semibold text-indigo-500 mb-1 uppercase tracking-wider">Pengguna Terdaftar</p>
          <p class="text-3xl font-bold text-indigo-900">{{ metrics.totalUsers }}</p>
        </div>
        <Link href="/users" class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700 shadow-sm transition">
          Kelola User
        </Link>
      </div>

      <!-- Charts Area -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Trend Chart -->
        <div class="lg:col-span-2 rounded-xl border border-slate-200 bg-white p-6 shadow-sm flex flex-col">
          <h3 class="font-bold text-slate-800 mb-4">Tren Pertumbuhan (6 Bulan Terakhir)</h3>
          <div class="relative flex-1 min-h-[300px]">
            <Line :data="trendChartData" :options="lineOptions" />
          </div>
        </div>

        <!-- RSVP Distribution Chart -->
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm flex flex-col">
          <h3 class="font-bold text-slate-800 mb-4">Distribusi Kehadiran RSVP</h3>
          <div class="relative flex-1 min-h-[250px] flex items-center justify-center">
            <Pie v-if="metrics.totalRsvps > 0" :data="rsvpChartData" :options="pieOptions" />
            <div v-else class="text-sm text-slate-400 text-center">Belum ada data RSVP.</div>
          </div>
        </div>
      </div>

      <!-- Recent Weddings -->
      <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-200 px-6 py-4 flex items-center justify-between bg-slate-50/50">
          <h3 class="font-bold text-slate-800">Undangan Terbaru</h3>
          <Link href="/weddings" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 uppercase tracking-wider">
            Lihat Semua
          </Link>
        </div>
        
        <div v-if="recentWeddings.length === 0" class="p-12 text-center">
          <div class="mx-auto w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-3">
            <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
          </div>
          <p class="text-slate-500 mb-4">Belum ada undangan yang dibuat.</p>
          <Link href="/weddings/create" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700">
            Buat Undangan Baru
          </Link>
        </div>
        
        <ul v-else class="divide-y divide-slate-100">
          <li v-for="wedding in recentWeddings" :key="wedding.id" class="px-6 py-4 flex items-center justify-between hover:bg-slate-50 transition group">
            <div>
              <p class="font-bold text-slate-900">{{ wedding.cover_title }}</p>
              <div class="text-xs text-slate-500 flex items-center gap-3 mt-1">
                <span class="flex items-center gap-1">
                  <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                  {{ new Date(wedding.created_at).toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric'}) }}
                </span>
                <span v-if="isAdmin" class="text-indigo-600 font-semibold flex items-center gap-1 bg-indigo-50 px-2 py-0.5 rounded">
                  <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                  {{ wedding.user?.name }}
                </span>
              </div>
            </div>
            <Link :href="`/weddings/${wedding.id}`" class="px-4 py-2 border border-slate-200 rounded-lg text-xs font-semibold hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 transition opacity-0 group-hover:opacity-100">
              Kelola
            </Link>
          </li>
        </ul>
      </div>
      
    </div>
  </AuthenticatedLayout>
</template>
