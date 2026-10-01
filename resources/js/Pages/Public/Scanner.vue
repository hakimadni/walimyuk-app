<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import { Html5QrcodeScanner } from 'html5-qrcode'
import axios from 'axios'

const props = defineProps({
  wedding: Object,
})

const checkInResult = ref(null)
const errorMsg = ref(null)

onMounted(() => {
  const scanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: {width: 250, height: 250} }, false)
  scanner.render(onScanSuccess, onScanFailure)

  function onScanSuccess(decodedText, decodedResult) {
    if (decodedText) {
      scanner.pause()
      axios.post(`/w/${props.wedding.slug}/scanner/check-in`, { qr_code_hash: decodedText })
      .then(res => {
        const data = res.data;
        if (data.error) {
          errorMsg.value = data.error
          checkInResult.value = null
        } else {
          errorMsg.value = null
          checkInResult.value = data
        }
        setTimeout(() => {
          checkInResult.value = null
          errorMsg.value = null
          scanner.resume()
        }, 3000)
      })
      .catch(err => {
        console.error(err)
        errorMsg.value = err.response?.data?.error || "Terjadi kesalahan jaringan."
        setTimeout(() => {
          errorMsg.value = null
          scanner.resume()
        }, 3000)
      })
    }
  }

  function onScanFailure(error) {
    // ignore
  }

  onUnmounted(() => {
    scanner.clear()
  })
})
</script>

<template>
  <Head title="QR Scanner" />
  <div class="min-h-screen bg-slate-50 p-4">
    <div class="max-w-md mx-auto bg-white rounded-xl shadow p-6">
      <h1 class="text-xl font-bold text-center mb-4">Check-in Tamu</h1>
      <div id="reader" width="600px"></div>

      <div v-if="checkInResult" class="mt-4 p-4 rounded-lg text-center" :class="checkInResult.success ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'">
        <p class="font-bold text-lg">{{ checkInResult.message }}</p>
        <div v-if="checkInResult.guest">
          <p class="text-xl mt-2">{{ checkInResult.guest.name }}</p>
          <p v-if="checkInResult.guest.group">{{ checkInResult.guest.group }}</p>
          <p class="font-semibold">{{ checkInResult.guest.pax_count }} Pax</p>
        </div>
      </div>
      <div v-if="errorMsg" class="mt-4 p-4 rounded-lg text-center bg-rose-100 text-rose-800">
        <p class="font-bold">{{ errorMsg }}</p>
      </div>
    </div>
  </div>
</template>
