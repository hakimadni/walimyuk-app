<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { computed, onMounted } from 'vue'
import { formatDate } from '@/lib/date'
import { driver } from 'driver.js'
import 'driver.js/dist/driver.css'

const props = defineProps({
  wedding: { type: Object, required: true },
  builderConfig: { type: Object, required: true },
  previewUrl: { type: String, default: null },
})

const profiles = computed(() => props.wedding.couple_profiles || props.wedding.coupleProfiles || [])
const groom = computed(() => profiles.value.find((p) => p.role === 'groom') || profiles.value[0] || null)
const bride = computed(() => profiles.value.find((p) => p.role === 'bride') || profiles.value[1] || null)
const enabledBlocks = computed(() => (props.builderConfig.content?.blocks || []).filter((block) => block.enabled))

const startTour = () => {
  const driverObj = driver({
    showProgress: true,
    nextBtnText: 'Selanjutnya &rarr;',
    prevBtnText: '&larr; Sebelumnya',
    doneBtnText: 'Selesai',
    steps: [
      { element: '#tour-persiapan-modul', popover: { title: 'Tahapan Persiapan', description: 'Selamat datang! Di sinilah tempat Anda akan mengelola seluruh persiapan undangan, mulai dari dokumen KUA hingga daftar tamu.', side: 'top', align: 'start' } },
      { element: '#tour-step-1', popover: { title: '1. Dokumen KUA', description: 'Langkah pertama: Siapkan dan kelola berkas syarat KUA, RT/RW, Puskesmas hingga Kelurahan di sini.', side: 'bottom', align: 'start' } },
      { element: '#tour-step-2', popover: { title: '2. Profil & Keluarga', description: 'Isi identitas lengkap kedua mempelai dan nama orang tua agar muncul di undangan.', side: 'bottom', align: 'start' } },
      { element: '#tour-step-6', popover: { title: 'Desain Undangan', description: 'Setelah data inti terisi, Anda bisa mengatur warna, font, dan urutan seksi desain di Theme Builder.', side: 'bottom', align: 'start' } },
      { element: '#tour-step-7', popover: { title: 'Daftar & Sebar Undangan', description: 'Tambahkan nama-nama tamu Anda, dan otomatis buatkan link unik serta template pesan WhatsApp untuk disebar.', side: 'bottom', align: 'start' } },
      { element: '#tour-builder-btn', popover: { title: 'Live Preview & Builder', description: 'Gunakan tombol ini kapan pun untuk melihat atau mengedit wujud undangan Anda secara live.', side: 'bottom', align: 'end' } },
    ],
    onDestroyStarted: () => {
      localStorage.setItem('walimyuk_tour_completed', 'true')
      driverObj.destroy()
    }
  })
  driverObj.drive()
}

onMounted(() => {
  if (!localStorage.getItem('walimyuk_tour_completed')) {
    setTimeout(() => {
      startTour()
    }, 500)
  }
})
</script>

<template>
  <Head :title="wedding.cover_subtitle || wedding.cover_title || 'Detail Undangan'" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
        <div class="min-w-0 flex-1">
          <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3">
            <h2 class="font-serif text-2xl sm:text-3xl font-bold text-emerald-950 break-words leading-tight">
              <span class="block sm:inline">{{ groom?.full_name || 'Mempelai Pria' }}</span>
              <span class="block sm:inline text-emerald-700/80 sm:mx-1">&amp;</span>
              <span class="block sm:inline">{{ bride?.full_name || 'Mempelai Wanita' }}</span>
            </h2>
            <span
              class="inline-flex shrink-0 items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize self-start sm:self-auto"
              :class="wedding.status === 'published' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'"
            >
              <span class="h-1.5 w-1.5 rounded-full" :class="wedding.status === 'published' ? 'bg-emerald-600' : 'bg-amber-600'" />
              {{ wedding.status || 'Draft' }}
            </span>
          </div>
          <p class="mt-2 sm:mt-1 text-sm text-slate-500 truncate">{{ wedding.cover_title }} • {{ wedding.wedding_date ? formatDate(wedding.wedding_date) : 'Tanggal belum diatur' }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-2 shrink-0 xl:ml-4">
          <Button @click="startTour" variant="outline" class="border-blue-200 text-xs font-semibold text-blue-700 hover:bg-blue-50 flex items-center gap-1.5 shadow-sm">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Panduan
          </Button>
          <a v-if="previewUrl" :href="previewUrl" target="_blank" rel="noopener noreferrer">
            <Button variant="outline" class="border-emerald-200 text-xs font-semibold text-emerald-800 hover:bg-emerald-50 flex items-center gap-1.5 shadow-sm">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
              </svg>
              Buka Undangan
            </Button>
          </a>
          <Link :href="`/weddings/${wedding.id}/builder`" id="tour-builder-btn">
            <Button class="bg-emerald-700 text-xs font-semibold text-white hover:bg-emerald-800 flex items-center gap-1.5 shadow-sm">
              <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              Theme Builder
            </Button>
          </Link>
          <Link :href="`/weddings/${wedding.id}/edit`">
            <Button variant="ghost" class="text-xs font-medium text-slate-600 hover:bg-slate-100">
              Pengaturan
            </Button>
          </Link>
        </div>
      </div>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
        <!-- Command Center Metric Stats -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <!-- Tamu Undangan -->
          <Link :href="`/weddings/${wedding.id}/guests`" class="group transition">
            <Card class="border-emerald-100 transition-all hover:border-emerald-500 hover:shadow-md">
              <CardContent class="p-5">
                <div class="flex items-center justify-between">
                  <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Tamu</span>
                  <div class="rounded-xl bg-emerald-50 p-2 text-emerald-700 group-hover:bg-emerald-100 transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                  </div>
                </div>
                <div class="mt-2">
                  <p class="font-serif text-2xl font-bold text-emerald-950">{{ wedding.guests_count || 0 }}</p>
                  <p class="text-xs text-emerald-700 font-medium mt-0.5">Kelola &amp; buat link personal &rarr;</p>
                </div>
              </CardContent>
            </Card>
          </Link>

          <!-- RSVP Masuk -->
          <Link :href="`/weddings/${wedding.id}/rsvps`" class="group transition">
            <Card class="border-emerald-100 transition-all hover:border-emerald-500 hover:shadow-md">
              <CardContent class="p-5">
                <div class="flex items-center justify-between">
                  <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">RSVP Masuk</span>
                  <div class="rounded-xl bg-amber-50 p-2 text-amber-700 group-hover:bg-amber-100 transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                </div>
                <div class="mt-2">
                  <p class="font-serif text-2xl font-bold text-emerald-950">{{ wedding.rsvps_count || 0 }}</p>
                  <p class="text-xs text-amber-700 font-medium mt-0.5">Lihat status konfirmasi hadir &rarr;</p>
                </div>
              </CardContent>
            </Card>
          </Link>

          <!-- Ucapan & Doa -->
          <Link :href="`/weddings/${wedding.id}/wishes`" class="group transition">
            <Card class="border-emerald-100 transition-all hover:border-emerald-500 hover:shadow-md">
              <CardContent class="p-5">
                <div class="flex items-center justify-between">
                  <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Ucapan &amp; Doa</span>
                  <div class="rounded-xl bg-purple-50 p-2 text-purple-700 group-hover:bg-purple-100 transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                    </svg>
                  </div>
                </div>
                <div class="mt-2">
                  <p class="font-serif text-2xl font-bold text-emerald-950">{{ wedding.wishes_count || 0 }}</p>
                  <p class="text-xs text-purple-700 font-medium mt-0.5">Moderasi &amp; lihat doa restu &rarr;</p>
                </div>
              </CardContent>
            </Card>
          </Link>

          <!-- Buffer Katering -->
          <Card class="border-emerald-100">
            <CardContent class="p-5">
              <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Buffer Katering</span>
                <div class="rounded-xl bg-blue-50 p-2 text-blue-700">
                  <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                  </svg>
                </div>
              </div>
              <div class="mt-2">
                <p class="font-serif text-2xl font-bold text-emerald-950">+{{ wedding.pax_buffer_percentage || 10 }}%</p>
                <p class="text-xs text-slate-400 mt-0.5">Pengaman estimasi porsi katering</p>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Main Content 2-Column Grid -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
          <!-- Left: Information Cards -->
          <div class="space-y-6 lg:col-span-2">
            <!-- Mempelai Card -->
            <Card class="border-slate-200">
              <CardHeader>
                <div class="flex items-center justify-between">
                  <div>
                    <CardTitle class="font-serif text-lg font-bold text-emerald-950">Profil Mempelai</CardTitle>
                    <CardDescription>Informasi calon pengantin dan orang tua</CardDescription>
                  </div>
                </div>
              </CardHeader>
              <CardContent class="grid grid-cols-1 gap-4 sm:grid-cols-2 text-sm">
                <!-- Groom -->
                <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-4 flex gap-3.5 items-start">
                  <div class="relative h-12 w-12 flex-shrink-0 overflow-hidden rounded-full border border-emerald-300 bg-emerald-100 flex items-center justify-center shadow-sm">
                    <img
                      v-if="groom?.photo_url || groom?.photo_path"
                      :src="groom.photo_url || groom.photo_path"
                      alt="Foto Groom"
                      class="h-full w-full object-cover"
                    />
                    <span v-else class="text-xl">🤵</span>
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                      <span class="rounded-full bg-emerald-700 px-2 py-0.5 text-[10px] font-bold text-white uppercase">Pria</span>
                      <h4 class="font-bold text-emerald-950 truncate">{{ groom?.full_name || 'Mempelai Pria' }}</h4>
                    </div>
                    <p class="text-xs text-slate-600 truncate">{{ groom?.child_order_text || '-' }}</p>
                    <p class="text-xs text-slate-600 mt-1">
                      Putra dari: Bapak <span class="font-medium text-slate-800">{{ groom?.father_name || '-' }}</span> &amp; Ibu <span class="font-medium text-slate-800">{{ groom?.mother_name || '-' }}</span>
                    </p>
                  </div>
                </div>

                <!-- Bride -->
                <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-4 flex gap-3.5 items-start">
                  <div class="relative h-12 w-12 flex-shrink-0 overflow-hidden rounded-full border border-rose-300 bg-rose-100 flex items-center justify-center shadow-sm">
                    <img
                      v-if="bride?.photo_url || bride?.photo_path"
                      :src="bride.photo_url || bride.photo_path"
                      alt="Foto Bride"
                      class="h-full w-full object-cover"
                    />
                    <span v-else class="text-xl">👰</span>
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                      <span class="rounded-full bg-rose-700 px-2 py-0.5 text-[10px] font-bold text-white uppercase">Wanita</span>
                      <h4 class="font-bold text-emerald-950 truncate">{{ bride?.full_name || 'Mempelai Wanita' }}</h4>
                    </div>
                    <p class="text-xs text-slate-600 truncate">{{ bride?.child_order_text || '-' }}</p>
                    <p class="text-xs text-slate-600 mt-1">
                      Putri dari: Bapak <span class="font-medium text-slate-800">{{ bride?.father_name || '-' }}</span> &amp; Ibu <span class="font-medium text-slate-800">{{ bride?.mother_name || '-' }}</span>
                    </p>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Quick Management Modules -->
            <Card class="border-slate-200" id="tour-persiapan-modul">
              <CardHeader>
                <CardTitle class="font-serif text-lg font-bold text-emerald-950">Tahapan Persiapan Undangan</CardTitle>
                <CardDescription>Lengkapi data dan rancang undangan Anda secara berurutan hingga siap disebar</CardDescription>
              </CardHeader>
              <CardContent>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                  <Link :href="`/weddings/${wedding.id}/document-checklist`" id="tour-step-1" class="group flex items-center gap-3 rounded-xl border border-slate-200 p-3.5 transition hover:border-emerald-500 hover:bg-emerald-50/30">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-800 text-xl transition group-hover:scale-105">
                      📋
                    </div>
                    <div>
                      <p class="text-sm font-bold text-emerald-950 group-hover:text-emerald-700">1. Persiapan Dokumen KUA</p>
                      <p class="text-xs text-slate-500 mb-1">Ceklis syarat pengantar RT/RW, Puskesmas &amp; kelurahan</p>
                      <p class="text-[10px] text-amber-600/90 leading-tight italic">*SOP tiap daerah dapat berbeda. Referensi ini berdasarkan pengalaman kepengurusan KUA di Jakarta Selatan.</p>
                    </div>
                  </Link>

                  <Link :href="`/weddings/${wedding.id}/couple-profiles`" id="tour-step-2" class="group flex items-center gap-3 rounded-xl border border-slate-200 p-3.5 transition hover:border-emerald-500 hover:bg-emerald-50/30">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-800 text-xl transition group-hover:scale-105">
                      🤵👰
                    </div>
                    <div>
                      <p class="text-sm font-bold text-emerald-950 group-hover:text-emerald-700">2. Profil &amp; Keluarga</p>
                      <p class="text-xs text-slate-500">Isi data lengkap kedua mempelai dan nama orang tua</p>
                    </div>
                  </Link>

                  <Link :href="`/weddings/${wedding.id}/events`" id="tour-step-3" class="group flex items-center gap-3 rounded-xl border border-slate-200 p-3.5 transition hover:border-emerald-500 hover:bg-emerald-50/30">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-800 text-xl transition group-hover:scale-105">
                      📅
                    </div>
                    <div>
                      <p class="text-sm font-bold text-emerald-950 group-hover:text-emerald-700">3. Rangkaian Acara</p>
                      <p class="text-xs text-slate-500">Waktu, lokasi akad/resepsi, dan titik peta Google Maps</p>
                    </div>
                  </Link>

                  <Link :href="`/weddings/${wedding.id}/wedding-verses`" id="tour-step-4" class="group flex items-center gap-3 rounded-xl border border-slate-200 p-3.5 transition hover:border-emerald-500 hover:bg-emerald-50/30">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-800 text-xl transition group-hover:scale-105">
                      📖
                    </div>
                    <div>
                      <p class="text-sm font-bold text-emerald-950 group-hover:text-emerald-700">4. Kutipan &amp; Doa</p>
                      <p class="text-xs text-slate-500">Pilih ayat pengantar (seperti QS Ar-Rum) atau hadits</p>
                    </div>
                  </Link>

                  <Link :href="`/weddings/${wedding.id}/gift-bank-accounts`" id="tour-step-5" class="group flex items-center gap-3 rounded-xl border border-slate-200 p-3.5 transition hover:border-emerald-500 hover:bg-emerald-50/30">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-indigo-800 text-xl transition group-hover:scale-105">
                      💳
                    </div>
                    <div>
                      <p class="text-sm font-bold text-emerald-950 group-hover:text-emerald-700">5. Amplop &amp; Kado Fisik</p>
                      <p class="text-xs text-slate-500">Nomor rekening transfer dan alamat penerimaan kado</p>
                    </div>
                  </Link>

                  <Link :href="`/weddings/${wedding.id}/builder`" id="tour-step-6" class="group flex items-center gap-3 rounded-xl border border-slate-200 p-3.5 transition hover:border-emerald-500 hover:bg-emerald-50/30">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-800 text-xl transition group-hover:scale-105">
                      🎨
                    </div>
                    <div>
                      <p class="text-sm font-bold text-emerald-950 group-hover:text-emerald-700">6. Desain Tampilan Undangan</p>
                      <p class="text-xs text-slate-500">Atur warna, font, dan susunan bagian undangan</p>
                    </div>
                  </Link>

                  <Link :href="`/weddings/${wedding.id}/guests`" id="tour-step-7" class="group flex items-center gap-3 rounded-xl border border-slate-200 p-3.5 transition hover:border-emerald-500 hover:bg-emerald-50/30">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-800 text-xl transition group-hover:scale-105">
                      👥
                    </div>
                    <div>
                      <p class="text-sm font-bold text-emerald-950 group-hover:text-emerald-700">7. Daftar &amp; Sebar Undangan</p>
                      <p class="text-xs text-slate-500">Kelola tamu, buat link unik, &amp; broadcast WhatsApp</p>
                    </div>
                  </Link>

                  <Link :href="`/weddings/${wedding.id}/rsvps`" id="tour-step-8" class="group flex items-center gap-3 rounded-xl border border-slate-200 p-3.5 transition hover:border-emerald-500 hover:bg-emerald-50/30">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-teal-100 text-teal-800 text-xl transition group-hover:scale-105">
                      🍛
                    </div>
                    <div>
                      <p class="text-sm font-bold text-emerald-950 group-hover:text-emerald-700">8. RSVP &amp; Katering</p>
                      <p class="text-xs text-slate-500">Pantau konfirmasi kehadiran &amp; hitung estimasi porsi</p>
                    </div>
                  </Link>

                  <Link :href="`/weddings/${wedding.id}/wishes`" id="tour-step-9" class="group flex items-center gap-3 rounded-xl border border-slate-200 p-3.5 transition hover:border-emerald-500 hover:bg-emerald-50/30">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-purple-100 text-purple-800 text-xl transition group-hover:scale-105">
                      💌
                    </div>
                    <div>
                      <p class="text-sm font-bold text-emerald-950 group-hover:text-emerald-700">9. Kotak Ucapan</p>
                      <p class="text-xs text-slate-500">Lihat dan moderasi doa yang masuk dari para tamu</p>
                    </div>
                  </Link>
                </div>
              </CardContent>
            </Card>
          </div>

          <!-- Right: Builder & Flow Summary -->
          <div class="space-y-6">
            <Card class="border-slate-200">
              <CardHeader>
                <CardTitle class="font-serif text-base font-bold text-emerald-950">Status Susunan Seksi</CardTitle>
                <CardDescription>{{ enabledBlocks.length }} seksi aktif ditampilkan</CardDescription>
              </CardHeader>
              <CardContent class="space-y-3">
                <div class="flex flex-wrap gap-1.5">
                  <span
                    v-for="block in enabledBlocks"
                    :key="block.id"
                    class="inline-flex items-center gap-1 rounded-lg border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-800"
                  >
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-600" />
                    {{ block.label }}
                  </span>
                </div>

                <div class="border-t border-slate-100 pt-3 text-xs text-slate-600 space-y-1.5">
                  <p><span class="text-slate-400">Font:</span> {{ builderConfig.content?.font_family || 'font-sans' }}</p>
                  <p><span class="text-slate-400">Background:</span> <span class="inline-block h-3 w-3 rounded-full border align-middle mr-1" :style="{ backgroundColor: builderConfig.content?.palette?.background || '#fdf8f0' }"></span>{{ builderConfig.content?.palette?.background || '#fdf8f0' }}</p>
                  <p><span class="text-slate-400">Primary:</span> <span class="inline-block h-3 w-3 rounded-full border align-middle mr-1" :style="{ backgroundColor: builderConfig.content?.palette?.primary || '#065f46' }"></span>{{ builderConfig.content?.palette?.primary || '#065f46' }}</p>
                </div>

                <div class="pt-2">
                  <Link :href="`/weddings/${wedding.id}/builder`" class="block">
                    <Button variant="outline" class="w-full border-emerald-200 text-xs font-semibold text-emerald-800 hover:bg-emerald-50">
                      Buka Builder &rarr;
                    </Button>
                  </Link>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
