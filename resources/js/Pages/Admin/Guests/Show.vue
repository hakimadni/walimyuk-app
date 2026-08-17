<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Button } from '@/components/ui/button'

const props = defineProps({
  wedding: { type: Object, required: true },
  guest: { type: Object, required: true },
})

const copied = ref(false)

function getPersonalLink() {
  const origin = typeof window !== 'undefined' ? window.location.origin : ''
  return `${origin}/w/${props.wedding.slug}?guest=${props.guest.token}`
}

function copyPersonalLink() {
  navigator.clipboard.writeText(getPersonalLink())
  copied.value = true
  setTimeout(() => {
    copied.value = false
  }, 2000)
}

function openWhatsApp() {
  const url = getPersonalLink()
  const couple = props.wedding.cover_subtitle || props.wedding.cover_title || 'Pernikahan Kami'
  const text = encodeURIComponent(
    `Kepada Yth. *${props.guest.name}*,\n\n` +
    `Tanpa mengurangi rasa hormat, perkenankan kami mengundang Bapak/Ibu/Saudara/i untuk menghadiri acara pernikahan kami:\n\n` +
    `*${couple}*\n\n` +
    `Informasi lengkap & konfirmasi kehadiran (RSVP) dapat diakses melalui tautan undangan personal berikut:\n` +
    `${url}\n\n` +
    `Merupakan suatu kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir dan memberikan doa restu.\n\n` +
    `Terima kasih.`
  )

  const phone = (props.guest.phone_number || '').replace(/[^0-9]/g, '')
  const waUrl = phone ? `https://wa.me/${phone}?text=${text}` : `https://wa.me/?text=${text}`
  window.open(waUrl, '_blank')
}

function markSent() {
  router.post(`/dashboard/weddings/${props.wedding.id}/guests/${props.guest.id}/send`)
}
</script>

<template>
  <Head :title="`Detail Tamu - ${guest.name}`" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <div class="flex items-center gap-2">
            <Link :href="`/dashboard/weddings/${wedding.id}/guests`" class="text-xs text-emerald-700 hover:underline">&larr; Kembali ke Daftar Tamu</Link>
          </div>
          <h2 class="font-serif text-3xl font-bold text-emerald-950">{{ guest.name }}</h2>
          <p class="mt-1 text-sm text-slate-500">{{ wedding.cover_title }} • Detail tamu &amp; status RSVP.</p>
        </div>

        <div class="flex items-center gap-2">
          <Button type="button" class="bg-emerald-600 text-xs font-semibold text-white hover:bg-emerald-700 flex items-center gap-1.5" @click="openWhatsApp">
            <span>Kirim via WhatsApp</span>
          </Button>
          <Link :href="`/dashboard/weddings/${wedding.id}/guests/${guest.id}/edit`">
            <Button variant="outline" class="text-xs font-semibold">Edit Tamu</Button>
          </Link>
        </div>
      </div>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-4xl space-y-6 sm:px-6 lg:px-8">
        <!-- Personal Link Card -->
        <Card class="border-emerald-200 bg-emerald-50/50">
          <CardContent class="p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
              <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-800">Tautan Undangan Personal Tamu</p>
                <p class="mt-1 font-mono text-xs text-slate-700 break-all select-all">{{ getPersonalLink() }}</p>
              </div>
              <div class="flex items-center gap-2 shrink-0">
                <Button
                  type="button"
                  variant="outline"
                  class="rounded-xl border-emerald-300 text-xs font-semibold text-emerald-900 hover:bg-emerald-100"
                  @click="copyPersonalLink"
                >
                  {{ copied ? '✓ Berhasil Disalin' : 'Salin Tautan' }}
                </Button>
                <a :href="getPersonalLink()" target="_blank" rel="noopener noreferrer">
                  <Button class="rounded-xl bg-emerald-700 text-xs font-semibold text-white hover:bg-emerald-800">
                    Buka Preview
                  </Button>
                </a>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Information Grid -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
          <!-- Guest Profile Card -->
          <Card class="border-slate-200">
            <CardHeader>
              <CardTitle class="font-serif text-lg font-bold text-emerald-950">Informasi Tamu</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3 text-sm text-slate-700">
              <div class="flex justify-between border-b border-slate-100 pb-2">
                <span class="text-slate-400">Nama Lengkap</span>
                <span class="font-semibold text-slate-900">{{ guest.name }}</span>
              </div>
              <div class="flex justify-between border-b border-slate-100 pb-2">
                <span class="text-slate-400">Grup / Kategori</span>
                <span class="font-semibold text-slate-900">{{ guest.group_name || '-' }}</span>
              </div>
              <div class="flex justify-between border-b border-slate-100 pb-2">
                <span class="text-slate-400">No. WhatsApp</span>
                <span class="font-semibold font-mono text-slate-900">{{ guest.phone_number || '-' }}</span>
              </div>
              <div class="flex justify-between border-b border-slate-100 pb-2">
                <span class="text-slate-400">Batas Kuota Pax</span>
                <span class="font-semibold text-slate-900">{{ guest.max_pax }} Pax</span>
              </div>
              <div class="flex justify-between border-b border-slate-100 pb-2">
                <span class="text-slate-400">Status Kirim</span>
                <span class="font-semibold" :class="guest.is_invitation_sent ? 'text-emerald-700' : 'text-slate-400'">
                  {{ guest.is_invitation_sent ? '✓ Sudah Dikirim' : 'Belum Dikirim' }}
                </span>
              </div>
              <div v-if="guest.notes" class="pt-1">
                <span class="text-xs text-slate-400 block mb-1">Catatan:</span>
                <p class="rounded-lg bg-slate-50 p-2.5 text-xs text-slate-600">{{ guest.notes }}</p>
              </div>
            </CardContent>
          </Card>

          <!-- RSVP Status Card -->
          <Card class="border-slate-200">
            <CardHeader>
              <CardTitle class="font-serif text-lg font-bold text-emerald-950">Status Konfirmasi Kehadiran</CardTitle>
            </CardHeader>
            <CardContent>
              <div v-if="guest.rsvp" class="space-y-3 text-sm text-slate-700">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                  <span class="text-slate-400">Konfirmasi</span>
                  <span
                    class="rounded-full px-2.5 py-0.5 text-xs font-semibold"
                    :class="guest.rsvp.status === 'attending' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'"
                  >
                    {{ guest.rsvp.status === 'attending' ? 'Hadir' : 'Tidak Hadir' }}
                  </span>
                </div>
                <div class="flex justify-between border-b border-slate-100 pb-2">
                  <span class="text-slate-400">Jumlah Orang (Pax)</span>
                  <span class="font-semibold text-slate-900">{{ guest.rsvp.pax_count || 1 }} Orang</span>
                </div>
                <div v-if="guest.rsvp.notes" class="pt-1">
                  <span class="text-xs text-slate-400 block mb-1">Pesan dari Tamu:</span>
                  <p class="rounded-lg bg-slate-50 p-2.5 text-xs text-slate-600 italic">"{{ guest.rsvp.notes }}"</p>
                </div>
              </div>
              <div v-else class="py-8 text-center text-slate-400">
                <p class="text-sm">Tamu belum mengisi formulir RSVP.</p>
                <p class="text-xs mt-1">Kirim link undangan untuk mengumpulkan konfirmasi.</p>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Wishes Submitted by Guest -->
        <Card v-if="guest.wishes && guest.wishes.length" class="border-slate-200">
          <CardHeader>
            <CardTitle class="font-serif text-lg font-bold text-emerald-950">Doa &amp; Ucapan yang Dikirimkan</CardTitle>
          </CardHeader>
          <CardContent class="space-y-3">
            <div v-for="wish in guest.wishes" :key="wish.id" class="rounded-xl border border-slate-100 bg-slate-50/50 p-4">
              <p class="text-sm text-slate-700 italic">"{{ wish.message }}"</p>
              <div class="mt-2 flex items-center justify-between text-[11px] text-slate-400">
                <span>{{ wish.sender_name }}</span>
                <span>{{ wish.created_at ? new Date(wish.created_at).toLocaleDateString('id-ID') : '' }}</span>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
