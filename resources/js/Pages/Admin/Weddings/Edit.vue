<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { DateTimePicker } from '@/components/ui/datetime-picker'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
  wedding: { type: Object, required: true },
  users: { type: Array, default: () => [] }
})

const profiles = computed(() => props.wedding.couple_profiles || props.wedding.coupleProfiles || [])
const groom = computed(() => profiles.value.find((p) => p.role === 'groom') || profiles.value[0] || null)
const bride = computed(() => profiles.value.find((p) => p.role === 'bride') || profiles.value[1] || null)

const form = useForm({
  user_id: props.wedding.user_id || '',
  cover_title: props.wedding.cover_title || '',
  cover_subtitle: props.wedding.cover_subtitle || '',
  wedding_date: props.wedding.wedding_date ? props.wedding.wedding_date.slice(0, 16) : '',
  timezone: props.wedding.timezone || 'Asia/Jakarta',
  welcome_text: props.wedding.welcome_text || '',
  closing_text: props.wedding.closing_text || '',
  rsvp_required: props.wedding.rsvp_required ?? true,
  comments_need_approval: props.wedding.comments_need_approval ?? false,
  pax_buffer_percentage: props.wedding.pax_buffer_percentage ?? 10,
})

const userSearch = ref('')
const showUserDropdown = ref(false)

const filteredUsers = computed(() => {
  if (!userSearch.value) return props.users
  const q = userSearch.value.toLowerCase()
  return props.users.filter(u => 
    u.name.toLowerCase().includes(q) || 
    u.email.toLowerCase().includes(q)
  )
})

const selectedUser = computed(() => {
  return props.users.find(u => u.id === form.user_id)
})

function selectUser(user) {
  form.user_id = user.id
  userSearch.value = ''
  showUserDropdown.value = false
}

function submit() {
  form.put(`/weddings/${props.wedding.id}`)
}

function destroy() {
  if (confirm('Yakin ingin menghapus undangan ini?')) {
    form.delete(`/weddings/${props.wedding.id}`)
  }
}
</script>

<template>
  <Head :title="`Edit: ${form.cover_subtitle || form.cover_title || 'Undangan'}`" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between gap-4">
        <div>
          <h2 class="font-serif text-3xl font-bold text-emerald-900">Edit Undangan</h2>
          <p class="mt-1 text-sm text-slate-500">Admin bisa edit undangan lintas owner. Detail mempelai dan acara diatur dari menu terpisah.</p>
        </div>
        <Link href="/weddings" class="text-sm text-emerald-700 hover:text-emerald-800">
          Kembali ke daftar
        </Link>
      </div>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-3xl space-y-6 sm:px-6 lg:px-8">
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
          <p class="font-semibold">Ringkasan Mempelai</p>
          <p class="mt-1">{{ groom?.full_name || 'Mempelai Pria' }} &amp; {{ bride?.full_name || 'Mempelai Wanita' }}</p>
          <p class="text-slate-600">{{ groom?.child_order_text || '-' }} dari Bapak {{ groom?.father_name || '-' }} &amp; Ibu {{ groom?.mother_name || '-' }}</p>
          <p class="text-slate-600">{{ bride?.child_order_text || '-' }} dari Bapak {{ bride?.father_name || '-' }} &amp; Ibu {{ bride?.mother_name || '-' }}</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-8 shadow-sm">
          <form @submit.prevent="submit" class="space-y-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
              <div v-if="users && users.length > 0" class="space-y-2 sm:col-span-2 relative">
                <Label>Pemilik Undangan (User)</Label>
                <div class="relative">
                  <div 
                    class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm cursor-pointer flex justify-between items-center"
                    @click="showUserDropdown = !showUserDropdown"
                  >
                    <span>{{ selectedUser ? `${selectedUser.name} (${selectedUser.email})` : '-- Pilih Pengguna --' }}</span>
                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                  </div>
                  
                  <div v-if="showUserDropdown" class="absolute z-10 mt-1 w-full rounded-md border border-slate-300 bg-white shadow-lg overflow-hidden">
                    <input 
                      v-model="userSearch" 
                      type="text" 
                      placeholder="Cari nama atau email..." 
                      class="w-full border-b border-slate-200 px-3 py-2.5 text-sm focus:outline-none bg-slate-50"
                      @click.stop
                    >
                    <ul class="max-h-48 overflow-y-auto py-1">
                      <li 
                        v-for="user in filteredUsers" 
                        :key="user.id" 
                        @click="selectUser(user)"
                        class="px-3 py-2 text-sm cursor-pointer hover:bg-emerald-50 hover:text-emerald-700"
                        :class="{'bg-emerald-100 text-emerald-800 font-semibold': form.user_id === user.id}"
                      >
                        {{ user.name }} <span class="text-xs opacity-60">({{ user.email }})</span>
                      </li>
                      <li v-if="filteredUsers.length === 0" class="px-3 py-2 text-sm text-slate-500 text-center">
                        Tidak ada pengguna ditemukan.
                      </li>
                    </ul>
                  </div>
                </div>
                <InputError :message="form.errors.user_id" />
              </div>

              <div class="space-y-2 sm:col-span-2">
                <Label for="cover_title">Judul Cover</Label>
                <Input id="cover_title" v-model="form.cover_title" />
                <InputError :message="form.errors.cover_title" />
              </div>

              <div class="space-y-2 sm:col-span-2">
                <Label for="cover_subtitle">Subjudul Cover</Label>
                <Input id="cover_subtitle" v-model="form.cover_subtitle" />
                <InputError :message="form.errors.cover_subtitle" />
              </div>

              <div class="space-y-2">
                <Label for="wedding_date">Tanggal Pernikahan</Label>
                <DateTimePicker id="wedding_date" v-model="form.wedding_date" />
                <InputError :message="form.errors.wedding_date" />
              </div>

              <div class="space-y-2">
                <Label for="timezone">Timezone</Label>
                <Input id="timezone" v-model="form.timezone" />
                <InputError :message="form.errors.timezone" />
              </div>

              <div class="space-y-2 sm:col-span-2">
                <Label for="welcome_text">Teks Pembuka</Label>
                <textarea id="welcome_text" v-model="form.welcome_text" rows="3" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"></textarea>
                <InputError :message="form.errors.welcome_text" />
              </div>

              <div class="space-y-2 sm:col-span-2">
                <Label for="closing_text">Teks Penutup</Label>
                <textarea id="closing_text" v-model="form.closing_text" rows="3" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500"></textarea>
                <InputError :message="form.errors.closing_text" />
              </div>

              <div class="space-y-2">
                <Label for="pax_buffer_percentage">Buffer Katering (%)</Label>
                <Input id="pax_buffer_percentage" type="number" min="0" max="100" v-model="form.pax_buffer_percentage" />
                <InputError :message="form.errors.pax_buffer_percentage" />
              </div>

              <div class="space-y-3 rounded-xl border border-slate-200 p-4">
                <label class="flex items-center gap-3 text-sm text-slate-700">
                  <input v-model="form.rsvp_required" type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                  RSVP wajib
                </label>
                <label class="flex items-center gap-3 text-sm text-slate-700">
                  <input v-model="form.comments_need_approval" type="checkbox" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" />
                  Ucapan perlu moderasi
                </label>
              </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
              <p class="font-semibold">Menu detail lanjutan</p>
              <div class="mt-3 flex flex-wrap gap-2">
                <Link :href="`/weddings/${wedding.id}/builder`"><Button type="button" variant="outline">Builder</Button></Link>
                <Link :href="`/weddings/${wedding.id}/couple-profiles`"><Button type="button" variant="outline">Profil Mempelai</Button></Link>
                <Link :href="`/weddings/${wedding.id}/events`"><Button type="button" variant="outline">Acara</Button></Link>
                <Link :href="`/weddings/${wedding.id}/guests`"><Button type="button" variant="outline">Tamu</Button></Link>
                <Link :href="`/weddings/${wedding.id}/rsvps`"><Button type="button" variant="outline">RSVP</Button></Link>
                <Link :href="`/weddings/${wedding.id}/wishes`"><Button type="button" variant="outline">Ucapan</Button></Link>
              </div>
            </div>

            <div class="flex items-center justify-between pt-2">
              <Button variant="destructive" type="button" @click="destroy">
                Hapus
              </Button>
              <Button type="submit" :disabled="form.processing" class="bg-emerald-700 text-white hover:bg-emerald-800">
                Simpan Perubahan
              </Button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
