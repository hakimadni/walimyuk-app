<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'

const props = defineProps({
  wedding: { type: Object, required: true },
  coupleProfiles: { type: Array, default: () => [] },
})

const groomData = props.coupleProfiles.find(p => p.role === 'groom') || {}
const brideData = props.coupleProfiles.find(p => p.role === 'bride') || {}

const groomPreview = ref(groomData.photo_url || groomData.photo_path || '')
const bridePreview = ref(brideData.photo_url || brideData.photo_path || '')

const groomFileInput = ref(null)
const brideFileInput = ref(null)
const photoErrors = ref({ groom: '', bride: '' })

const currentFrame = props.wedding.theme_config?.builder?.content?.couple_photo_frame || 'circle'

const form = useForm({
  _method: 'put',
  couple_photo_frame: currentFrame,
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
      photo_file: null,
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
      photo_file: null,
      instagram_url: brideData.instagram_url || '',
      sort_order: 2,
    },
  ],
})

const previewFrameClasses = computed(() => {
  switch (form.couple_photo_frame) {
    case 'portrait':
    case 'portrait_2_3':
      return {
        container: 'w-20 aspect-[2/3] rounded-2xl',
        img: 'rounded-xl',
      }
    case 'rounded_square':
    case 'square':
      return {
        container: 'w-20 h-20 aspect-square rounded-2xl',
        img: 'rounded-xl',
      }
    case 'arch':
      return {
        container: 'w-20 aspect-[2/3] rounded-t-full rounded-b-2xl',
        img: 'rounded-t-full rounded-b-xl',
      }
    case 'circle':
    default:
      return {
        container: 'w-20 h-20 aspect-square rounded-full',
        img: 'rounded-full',
      }
  }
})

function onPhotoChange(role, event) {
  const file = event.target.files?.[0]
  if (!file) return

  photoErrors.value[role] = ''

  const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp']
  if (!validTypes.includes(file.type)) {
    photoErrors.value[role] = 'Format file harus JPG, PNG, atau WEBP.'
    event.target.value = ''
    return
  }

  if (file.size > 5 * 1024 * 1024) {
    photoErrors.value[role] = `Ukuran file terlalu besar (${(file.size / 1024 / 1024).toFixed(1)} MB). Maksimal 5 MB.`
    event.target.value = ''
    return
  }

  const index = role === 'groom' ? 0 : 1
  form.profiles[index].photo_file = file
  const previewUrl = URL.createObjectURL(file)
  if (role === 'groom') {
    groomPreview.value = previewUrl
  } else {
    bridePreview.value = previewUrl
  }
}

function removePhoto(role) {
  const index = role === 'groom' ? 0 : 1
  form.profiles[index].photo_file = null
  form.profiles[index].photo_path = ''
  photoErrors.value[role] = ''
  if (role === 'groom') {
    groomPreview.value = ''
    if (groomFileInput.value) groomFileInput.value.value = ''
  } else {
    bridePreview.value = ''
    if (brideFileInput.value) brideFileInput.value.value = ''
  }
}

function triggerFileInput(role) {
  if (role === 'groom' && groomFileInput.value) {
    groomFileInput.value.click()
  } else if (role === 'bride' && brideFileInput.value) {
    brideFileInput.value.click()
  }
}

function submit() {
  form.post(`/weddings/${props.wedding.id}/couple-profiles`, {
    forceFormData: true,
  })
}
</script>

<template>
  <Head :title="`Profil Mempelai - ${wedding.cover_subtitle || wedding.cover_title}`" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-2">
        <Link :href="`/weddings/${wedding.id}`" class="text-xs text-emerald-700 hover:underline">&larr; Kembali ke Detail</Link>
      </div>
      <h2 class="font-serif text-3xl font-bold text-emerald-950 mt-1">Profil Mempelai &amp; Keluarga</h2>
      <p class="text-sm text-slate-500">{{ wedding.cover_title }} • Informasi data mempelai pria, wanita, dan kedua orang tua.</p>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-6xl space-y-6 sm:px-6 lg:px-8">
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Frame Style Customizer -->
          <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 space-y-4">
            <div>
              <h3 class="font-serif text-xl font-bold text-slate-900">Bentuk Bingkai Foto Mempelai</h3>
              <p class="text-xs text-slate-400">Pilih model dan rasio bingkai foto profil mempelai yang tampil di undangan publik.</p>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
              <!-- Option 1: Circle -->
              <button
                type="button"
                @click="form.couple_photo_frame = 'circle'"
                class="flex flex-col items-center justify-center gap-2.5 rounded-2xl border p-4 text-center transition-all"
                :class="form.couple_photo_frame === 'circle'
                  ? 'border-emerald-600 bg-emerald-50/60 ring-2 ring-emerald-600/30 font-bold text-emerald-950 shadow-sm'
                  : 'border-slate-200 bg-slate-50/50 text-slate-600 hover:border-slate-300 hover:bg-white'"
              >
                <div class="flex h-12 w-12 items-center justify-center rounded-full border-2 border-dashed border-emerald-600/60 bg-white text-base shadow-sm">
                  💍
                </div>
                <div>
                  <p class="text-xs font-bold">Lingkaran (1:1)</p>
                  <p class="text-[10px] text-slate-400">Klasik Full Rounded</p>
                </div>
              </button>

              <!-- Option 2: Portrait 2:3 -->
              <button
                type="button"
                @click="form.couple_photo_frame = 'portrait'"
                class="flex flex-col items-center justify-center gap-2.5 rounded-2xl border p-4 text-center transition-all"
                :class="form.couple_photo_frame === 'portrait'
                  ? 'border-emerald-600 bg-emerald-50/60 ring-2 ring-emerald-600/30 font-bold text-emerald-950 shadow-sm'
                  : 'border-slate-200 bg-slate-50/50 text-slate-600 hover:border-slate-300 hover:bg-white'"
              >
                <div class="flex h-14 w-10 items-center justify-center rounded-xl border-2 border-dashed border-emerald-600/60 bg-white text-base shadow-sm">
                  ✨
                </div>
                <div>
                  <p class="text-xs font-bold">Potret (2:3)</p>
                  <p class="text-[10px] text-slate-400">Rounded Corner 2:3</p>
                </div>
              </button>

              <!-- Option 3: Rounded Square 1:1 -->
              <button
                type="button"
                @click="form.couple_photo_frame = 'rounded_square'"
                class="flex flex-col items-center justify-center gap-2.5 rounded-2xl border p-4 text-center transition-all"
                :class="form.couple_photo_frame === 'rounded_square'
                  ? 'border-emerald-600 bg-emerald-50/60 ring-2 ring-emerald-600/30 font-bold text-emerald-950 shadow-sm'
                  : 'border-slate-200 bg-slate-50/50 text-slate-600 hover:border-slate-300 hover:bg-white'"
              >
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl border-2 border-dashed border-emerald-600/60 bg-white text-base shadow-sm">
                  ⏹
                </div>
                <div>
                  <p class="text-xs font-bold">Persegi (1:1)</p>
                  <p class="text-[10px] text-slate-400">Sudut Melengkung</p>
                </div>
              </button>

              <!-- Option 4: Arch (2:3) -->
              <button
                type="button"
                @click="form.couple_photo_frame = 'arch'"
                class="flex flex-col items-center justify-center gap-2.5 rounded-2xl border p-4 text-center transition-all"
                :class="form.couple_photo_frame === 'arch'
                  ? 'border-emerald-600 bg-emerald-50/60 ring-2 ring-emerald-600/30 font-bold text-emerald-950 shadow-sm'
                  : 'border-slate-200 bg-slate-50/50 text-slate-600 hover:border-slate-300 hover:bg-white'"
              >
                <div class="flex h-14 w-10 items-center justify-center rounded-t-full rounded-b-lg border-2 border-dashed border-emerald-600/60 bg-white text-base shadow-sm">
                  🕌
                </div>
                <div>
                  <p class="text-xs font-bold">Kubah (Arch)</p>
                  <p class="text-[10px] text-slate-400">Model Kubah Islami</p>
                </div>
              </button>
            </div>
          </div>

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

              <!-- Foto Profil Mempelai Pria -->
              <div class="space-y-2">
                <label class="block text-xs font-bold text-slate-700">Foto Profil Mempelai Pria</label>
                <div class="flex items-center gap-4 rounded-2xl border border-slate-100 bg-slate-50/70 p-3.5">
                  <div
                    class="relative flex-shrink-0 overflow-hidden border-2 border-emerald-600/30 bg-white shadow-inner flex items-center justify-center transition-all duration-300"
                    :class="previewFrameClasses.container"
                  >
                    <img
                      v-if="groomPreview"
                      :src="groomPreview"
                      alt="Foto Mempelai Pria"
                      class="h-full w-full object-cover"
                      :class="previewFrameClasses.img"
                    />
                    <span v-else class="text-3xl">🤵</span>
                  </div>

                  <div class="flex-1 space-y-1.5">
                    <input
                      ref="groomFileInput"
                      type="file"
                      accept="image/jpeg,image/png,image/jpg,image/webp"
                      class="hidden"
                      @change="e => onPhotoChange('groom', e)"
                    />
                    <div class="flex flex-wrap gap-2">
                      <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        class="h-8 rounded-xl text-xs font-medium border-slate-300 hover:border-emerald-600 hover:text-emerald-700"
                        @click="triggerFileInput('groom')"
                      >
                        <svg class="mr-1.5 h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2 2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        {{ groomPreview ? 'Ganti Foto' : 'Unggah Foto' }}
                      </Button>
                      <Button
                        v-if="groomPreview"
                        type="button"
                        variant="ghost"
                        size="sm"
                        class="h-8 rounded-xl text-xs text-rose-600 hover:bg-rose-50 hover:text-rose-700"
                        @click="removePhoto('groom')"
                      >
                        Hapus Foto
                      </Button>
                    </div>
                    <p class="text-[11px] text-slate-400">Format JPG, PNG, atau WEBP (Maksimal 5 MB).</p>
                    <p v-if="photoErrors.groom" class="text-[11px] text-rose-600 font-medium">{{ photoErrors.groom }}</p>
                    <p v-if="form.errors['profiles.0.photo_file']" class="text-[11px] text-rose-600 font-medium">{{ form.errors['profiles.0.photo_file'] }}</p>
                  </div>
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

              <!-- Foto Profil Mempelai Wanita -->
              <div class="space-y-2">
                <label class="block text-xs font-bold text-slate-700">Foto Profil Mempelai Wanita</label>
                <div class="flex items-center gap-4 rounded-2xl border border-rose-100 bg-rose-50/40 p-3.5">
                  <div
                    class="relative flex-shrink-0 overflow-hidden border-2 border-rose-300 bg-white shadow-inner flex items-center justify-center transition-all duration-300"
                    :class="previewFrameClasses.container"
                  >
                    <img
                      v-if="bridePreview"
                      :src="bridePreview"
                      alt="Foto Mempelai Wanita"
                      class="h-full w-full object-cover"
                      :class="previewFrameClasses.img"
                    />
                    <span v-else class="text-3xl">👰</span>
                  </div>

                  <div class="flex-1 space-y-1.5">
                    <input
                      ref="brideFileInput"
                      type="file"
                      accept="image/jpeg,image/png,image/jpg,image/webp"
                      class="hidden"
                      @change="e => onPhotoChange('bride', e)"
                    />
                    <div class="flex flex-wrap gap-2">
                      <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        class="h-8 rounded-xl text-xs font-medium border-slate-300 hover:border-rose-500 hover:text-rose-700"
                        @click="triggerFileInput('bride')"
                      >
                        <svg class="mr-1.5 h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2 2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        {{ bridePreview ? 'Ganti Foto' : 'Unggah Foto' }}
                      </Button>
                      <Button
                        v-if="bridePreview"
                        type="button"
                        variant="ghost"
                        size="sm"
                        class="h-8 rounded-xl text-xs text-rose-600 hover:bg-rose-50 hover:text-rose-700"
                        @click="removePhoto('bride')"
                      >
                        Hapus Foto
                      </Button>
                    </div>
                    <p class="text-[11px] text-slate-400">Format JPG, PNG, atau WEBP (Maksimal 5 MB).</p>
                    <p v-if="photoErrors.bride" class="text-[11px] text-rose-600 font-medium">{{ photoErrors.bride }}</p>
                    <p v-if="form.errors['profiles.1.photo_file']" class="text-[11px] text-rose-600 font-medium">{{ form.errors['profiles.1.photo_file'] }}</p>
                  </div>
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
            <Link :href="`/weddings/${wedding.id}`">
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
