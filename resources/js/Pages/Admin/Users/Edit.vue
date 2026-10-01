<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
  userModel: Object
})

const form = useForm({
  name: props.userModel.name || '',
  email: props.userModel.email || '',
  password: '',
  role: props.userModel.role || 'tenant',
  is_premium: props.userModel.is_premium == 1,
  builder_permissions: props.userModel.builder_permissions || {
    tenant_builder_enabled: false,
    block_builder: false,
    custom_decorations: false,
    custom_font: false,
    custom_text: true,
    character_image: false,
    background_image: false,
    decoration_animation: false,
    music: false,
    palette: false,
    block_visibility: true,
    block_order: true,
  }
})

function submit() {
  form.put(`/users/${props.userModel.id}`)
}
</script>

<template>
  <Head :title="`Edit Pengguna: ${userModel.name}`" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between gap-4">
        <div>
          <h2 class="font-serif text-3xl font-bold text-emerald-900">Edit Pengguna</h2>
          <p class="mt-1 text-sm text-slate-500">Ubah data akun pengguna.</p>
        </div>
        <Link href="/users" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">
          Kembali ke daftar
        </Link>
      </div>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-2xl">
        <div class="rounded-xl border border-slate-200 bg-white p-8 shadow-sm">
          <form @submit.prevent="submit" class="space-y-6">
            <div class="space-y-2">
              <Label for="name">Nama Lengkap</Label>
              <Input id="name" v-model="form.name" required />
              <InputError :message="form.errors.name" />
            </div>

            <div class="space-y-2">
              <Label for="email">Alamat Email</Label>
              <Input id="email" type="email" v-model="form.email" required />
              <InputError :message="form.errors.email" />
            </div>

            <div class="space-y-2">
              <Label for="password">Password (Biarkan kosong jika tidak diubah)</Label>
              <Input id="password" type="password" v-model="form.password" placeholder="********" />
              <InputError :message="form.errors.password" />
            </div>

            <div class="space-y-2">
              <Label for="role">Role (Peran)</Label>
              <select id="role" v-model="form.role" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500" :disabled="userModel.id === $page.props.auth.user.id">
                <option value="tenant">Tenant (Klien)</option>
                <option value="admin">Admin</option>
                <option value="super_admin">Super Admin</option>
              </select>
              <p v-if="userModel.id === $page.props.auth.user.id" class="text-xs text-amber-600 mt-1">Anda tidak bisa mengubah role Anda sendiri.</p>
              <InputError :message="form.errors.role" />
            </div>

            <div class="flex items-center gap-3">
              <input id="is_premium" type="checkbox" v-model="form.is_premium" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 h-5 w-5" />
              <div>
                <Label for="is_premium" class="cursor-pointer">Akun Premium</Label>
                <p class="text-xs text-slate-500">Membuka semua fitur terkunci (contoh: hapus watermark, dsb).</p>
              </div>
              <InputError :message="form.errors.is_premium" />
            </div>

            <!-- Permission Matrix for Tenant -->
            <div v-if="form.role === 'tenant'" class="rounded-xl border border-slate-200 bg-slate-50 p-6 space-y-4">
              <div>
                <h3 class="font-serif text-lg font-bold text-emerald-950">Permission Matrix (Builder Guardrails)</h3>
                <p class="text-xs text-slate-500">Atur izin fitur yang bisa diakses user ini saat membangun undangannya.</p>
              </div>
              <div class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                <label class="flex items-center gap-3 cursor-pointer">
                  <input v-model="form.builder_permissions.tenant_builder_enabled" type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                  <span class="font-medium text-slate-700">Tenant Builder Aktif</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                  <input v-model="form.builder_permissions.block_builder" type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                  <span class="font-medium text-slate-700">Block Builder Seksi</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                  <input v-model="form.builder_permissions.custom_decorations" type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                  <span class="font-medium text-slate-700">Kustomisasi Ornamen/Dekorasi</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                  <input v-model="form.builder_permissions.custom_font" type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                  <span class="font-medium text-slate-700">Pilihan Font Family</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                  <input v-model="form.builder_permissions.custom_text" type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                  <span class="font-medium text-slate-700">Kustomisasi Teks Cover &amp; Penutup</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                  <input v-model="form.builder_permissions.character_image" type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                  <span class="font-medium text-slate-700">Gambar Ilustrasi Karakter</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                  <input v-model="form.builder_permissions.background_image" type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                  <span class="font-medium text-slate-700">Background Image</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                  <input v-model="form.builder_permissions.decoration_animation" type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                  <span class="font-medium text-slate-700">Animasi Dekorasi</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                  <input v-model="form.builder_permissions.music" type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                  <span class="font-medium text-slate-700">Musik Latar &amp; Autoplay</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                  <input v-model="form.builder_permissions.palette" type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                  <span class="font-medium text-slate-700">Palet Warna Undangan</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                  <input v-model="form.builder_permissions.block_visibility" type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                  <span class="font-medium text-slate-700">Visibilitas Blok (On/Off)</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                  <input v-model="form.builder_permissions.block_order" type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                  <span class="font-medium text-slate-700">Urutan Blok (Draggable)</span>
                </label>
              </div>
            </div>

            <div class="flex items-center justify-end pt-4">
              <Button type="submit" :disabled="form.processing" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 rounded-xl shadow-sm">
                Simpan Perubahan
              </Button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
