<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card'

defineProps({
  stats: { type: Object, default: () => ({
    total_invited: 0,
    total_quota: 0,
    rsvp_submitted: 0,
    rsvp_pending: 0,
    rsvp_attending: 0,
    rsvp_declined: 0,
    confirmed_pax: 0,
    recommended_catering: 0,
  })}
})
</script>

<template>
  <Head title="Dashboard" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-serif text-3xl font-bold text-emerald-900">Dashboard</h2>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-8">
          <Card class="border-emerald-200">
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium text-slate-500">Total Tamu Diundang</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold text-emerald-900">{{ stats.total_invited }}</div>
            </CardContent>
          </Card>
          <Card class="border-sage-200">
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium text-slate-500">Total Kuota</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold text-sage-900">{{ stats.total_quota }}</div>
            </CardContent>
          </Card>
          <Card class="border-emerald-200">
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium text-slate-500">RSVP Masuk</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold text-emerald-900">{{ stats.rsvp_submitted }}</div>
            </CardContent>
          </Card>
          <Card class="border-gold-200">
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium text-slate-500">RSVP Pending</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold text-gold-900">{{ stats.rsvp_pending }}</div>
            </CardContent>
          </Card>
          <Card class="border-emerald-200">
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium text-slate-500">Hadir</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold text-emerald-900">{{ stats.rsvp_attending }}</div>
            </CardContent>
          </Card>
          <Card class="border-red-200">
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium text-slate-500">Tidak Hadir</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold text-red-900">{{ stats.rsvp_declined }}</div>
            </CardContent>
          </Card>
          <Card class="border-emerald-200">
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium text-slate-500">Pax Terkonfirmasi</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold text-emerald-900">{{ stats.confirmed_pax }}</div>
            </CardContent>
          </Card>
          <Card class="border-gold-200">
            <CardHeader class="pb-2">
              <CardTitle class="text-sm font-medium text-slate-500">Rekomendasi Katering</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-2xl font-bold text-gold-900">{{ stats.recommended_catering }}</div>
            </CardContent>
          </Card>
        </div>

        <!-- Warning: high pending RSVP ratio -->
        <div
          v-if="stats.rsvp_pending > 0 && stats.total_invited > 0 && (stats.rsvp_pending / stats.total_invited) > 0.3"
          class="mb-6 rounded-xl border border-gold-300 bg-gold-50 p-4 text-sm text-gold-800"
        >
          ⚠️ {{ stats.rsvp_pending }} tamu belum RSVP (&gt;30% dari total undangan). Segera lakukan follow-up!
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>