<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'

const props = defineProps({
  wedding: { type: Object, required: true },
  coupleProfiles: { type: Array, default: () => [] },
})

const groomData = props.coupleProfiles.find(p => p.role === 'groom') || {}
const brideData = props.coupleProfiles.find(p => p.role === 'bride') || {}

const form = useForm({
  profiles: [
    {
      id: groomData.id || null,
      role: 'groom',
      full_name: groomData.full_name || '',
      nickname: groomData.nickname || '',
      father_name: groomData.father_name || '',
      mother_name: groomData.mother_name || '',
      child_order_text: groomData.child_order_text || 'Putra pertama dari',
      photo_path: groomData.photo_path || '',
      instagram_url: groomData.instagram_url || '',
      sort_order: 1,
    },
    {
      id: brideData.id || null,
      role: 'bride',
      full_name: brideData.full_name || '',
      nickname: brideData.nickname || '',
      father_name: brideData.father_name || '',
      mother_name: brideData.mother_name || '',
      child_order_text: brideData.child_order_text || 'Putri kedua dari',
      photo_path: brideData.photo_path || '',
      instagram_url: brideData.instagram_url || '',
      sort_order: 2,
    },
  ],
})

function submit() {
  form.put(`/dashboard/weddings/${props.wedding.id}/couple-profiles`)
}
</script>

<template>
  <Head :title="`Profil Mempelai - ${wedding.cover_subtitle || wedding.cover_title}`" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-2">
        <Link :href="`/dashboard/weddings/${wedding.id}`" class="text-xs text-emerald-700 hover:underline">&larr; Kembali ke Detail</Link>
      </div>
      <h2 class="font-serif text-3xl font-bold text-emerald-950 mt-1">Profil Mempelai &amp; Keluarga</h2>
      <p class="text-sm text-slate-500">{{ wedding.cover_title }} • Informasi data mempelai pria, wanita, dan kedua orang tua.</p>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-6xl space-y-6 sm:px-6 lg:px-8">
        <form @submit.prevent="submit" class="space-y-6">
          <div class="grid gap-6 md:grid-cols-2">
            <!-- Groom Card -->
            <div class="rounded-3xl border border-emerald-100 bg-white p-6 shadow-sm sm:p-8 space-y-5">
              <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-800 text-white font-serif font-bold text-lg">
                  🤵
                </div>
                <div>
                  <h3 class="font-serif text-xl font-bold text-slate-900">Mempelai Pria (Groom)</h3>
                  <p class="text-xs text-slate-400">Data calon pengantin laki-laki</p>
                </div>
              </div>

              <!-- Full Name -->
              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700">Nama Lengkap &amp; Gelar</label>
                <Input v-model="form.profiles[0].full_name" placeholder="Contoh: Farhan Ramadhan, S.Kom." required class="text-sm" />
              </div>

              <!-- Nickname -->
              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700">Nama Panggilan</label>
                <Input v-model="form.profiles[0].nickname" placeholder="Contoh: Farhan" class="text-sm" />
              </div>

              <!-- Child Order -->
              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700">Keterangan Anak Ke-</label>
                <Input v-model="form.profiles[0].child_order_text" placeholder="Contoh: Putra pertama dari" class="text-sm" />
              </div>

              <!-- Parents -->
              <div class="grid gap-3 sm:grid-cols-2">
                <div class="space-y-1.5">
                  <label class="block text-xs font-bold text-slate-700">Nama Ayah</label>
                  <Input v-model="form.profiles[0].father_name" placeholder="Bpk. H. Rahmat" class="text-sm" />
                </div>
                <div class="space-y-1.5">
                  <label class="block text-xs font-bold text-slate-700">Nama Ibu</label>
                  <Input v-model="form.profiles[0].mother_name" placeholder="Ibu Hj. Aminah" class="text-sm" />
                </div>
              </div>

              <!-- Instagram URL -->
              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700">Tautan Instagram</label>
                <Input v-model="form.profiles[0].instagram_url" placeholder="https://instagram.com/username" class="text-sm" />
              </div>
            </div>

            <!-- Bride Card -->
            <div class="rounded-3xl border border-rose-100 bg-white p-6 shadow-sm sm:p-8 space-y-5">
              <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-amber-500 text-white font-serif font-bold text-lg">
                  👰
                </div>
                <div>
                  <h3 class="font-serif text-xl font-bold text-slate-900">Mempelai Wanita (Bride)</h3>
                  <p class="text-xs text-slate-400">Data calon pengantin perempuan</p>
                </div>
              </div>

              <!-- Full Name -->
              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700">Nama Lengkap &amp; Gelar</label>
                <Input v-model="form.profiles[1].full_name" placeholder="Contoh: Aisyah Salsabila, S.Ked." required class="text-sm" />
              </div>

              <!-- Nickname -->
              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700">Nama Panggilan</label>
                <Input v-model="form.profiles[1].nickname" placeholder="Contoh: Aisyah" class="text-sm" />
              </div>

              <!-- Child Order -->
              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700">Keterangan Anak Ke-</label>
                <Input v-model="form.profiles[1].child_order_text" placeholder="Contoh: Putri kedua dari" class="text-sm" />
              </div>

              <!-- Parents -->
              <div class="grid gap-3 sm:grid-cols-2">
                <div class="space-y-1.5">
                  <label class="block text-xs font-bold text-slate-700">Nama Ayah</label>
                  <Input v-model="form.profiles[1].father_name" placeholder="Bpk. H. Abdullah" class="text-sm" />
                </div>
                <div class="space-y-1.5">
                  <label class="block text-xs font-bold text-slate-700">Nama Ibu</label>
                  <Input v-model="form.profiles[1].mother_name" placeholder="Ibu Hj. Khadijah" class="text-sm" />
                </div>
              </div>

              <!-- Instagram URL -->
              <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700">Tautan Instagram</label>
                <Input v-model="form.profiles[1].instagram_url" placeholder="https://instagram.com/username" class="text-sm" />
              </div>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="flex items-center justify-end gap-3 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
            <Link :href="`/dashboard/weddings/${wedding.id}`">
              <Button type="button" variant="outline" class="rounded-xl text-xs">Batal</Button>
            </Link>
            <Button
              type="submit"
              :disabled="form.processing"
              class="rounded-xl bg-emerald-700 px-6 text-xs font-semibold text-white hover:bg-emerald-800"
            >
              {{ form.processing ? 'Menyimpan...' : 'Simpan Profil Mempelai' }}
            </Button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
