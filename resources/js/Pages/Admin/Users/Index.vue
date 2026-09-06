<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

defineProps({
  users: Array
})

const deleteForm = useForm({})

function destroy(id) {
  if (confirm('Yakin ingin menghapus pengguna ini? Semua undangan miliknya akan ikut terhapus!')) {
    deleteForm.delete(`/users/${id}`)
  }
}
</script>

<template>
  <Head title="Manajemen Pengguna" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h2 class="font-serif text-2xl font-bold leading-tight text-emerald-900">
            Manajemen Pengguna
          </h2>
          <p class="mt-1 text-sm text-slate-500">Kelola akun tenant dan admin sistem.</p>
        </div>
        <Link href="/users/create" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
          Tambah User
        </Link>
      </div>
    </template>

    <div class="py-6">
      <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500 border-b border-slate-200">
              <tr>
                <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Nama & Email</th>
                <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Role</th>
                <th scope="col" class="px-6 py-4 font-semibold tracking-wider text-center">Jml Undangan</th>
                <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Tgl Daftar</th>
                <th scope="col" class="px-6 py-4 font-semibold tracking-wider text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
              <tr v-for="user in users" :key="user.id" class="hover:bg-slate-50 transition">
                <td class="px-6 py-4">
                  <div class="font-bold text-slate-900 flex items-center gap-2">
                    {{ user.name }}
                    <span v-if="user.is_premium" class="px-1.5 py-0.5 rounded text-[10px] uppercase font-bold bg-amber-100 text-amber-700">Premium</span>
                  </div>
                  <div class="text-xs text-slate-500 mt-0.5">{{ user.email }}</div>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold"
                    :class="{
                      'bg-indigo-100 text-indigo-700': user.role === 'super_admin' || user.role === 'admin',
                      'bg-slate-100 text-slate-700': user.role === 'tenant'
                    }">
                    {{ user.role === 'super_admin' ? 'Super Admin' : (user.role === 'admin' ? 'Admin' : 'Tenant') }}
                  </span>
                </td>
                <td class="px-6 py-4 text-center">
                  <span class="font-bold text-slate-700">{{ user.weddings_count || 0 }}</span>
                </td>
                <td class="px-6 py-4 text-xs">
                  {{ new Date(user.created_at).toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric'}) }}
                </td>
                <td class="px-6 py-4 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <Link :href="`/users/${user.id}/edit`" class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-emerald-600 transition">
                      Edit
                    </Link>
                    <button 
                      v-if="user.id !== $page.props.auth.user.id"
                      @click="destroy(user.id)" 
                      class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 transition"
                    >
                      Hapus
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="users.length === 0">
                <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                  Belum ada data pengguna.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
