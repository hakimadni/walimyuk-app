<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import InputError from '@/Components/InputError.vue'

const form = useForm({
  name: '',
  email: '',
  password: '',
  role: 'tenant',
  is_premium: false
})

function submit() {
  form.post('/users')
}
</script>

<template>
  <Head title="Tambah Pengguna" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between gap-4">
        <div>
          <h2 class="font-serif text-3xl font-bold text-emerald-900">Tambah Pengguna</h2>
          <p class="mt-1 text-sm text-slate-500">Buat akun baru untuk client (tenant) atau pengurus (admin).</p>
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
              <Label for="password">Password</Label>
              <Input id="password" type="password" v-model="form.password" required />
              <InputError :message="form.errors.password" />
            </div>

            <div class="space-y-2">
              <Label for="role">Role (Peran)</Label>
              <select id="role" v-model="form.role" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                <option value="tenant">Tenant (Klien)</option>
                <option value="admin">Admin</option>
                <option value="super_admin">Super Admin</option>
              </select>
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

            <div class="flex items-center justify-end pt-4">
              <Button type="submit" :disabled="form.processing" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 rounded-xl shadow-sm">
                Simpan
              </Button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
