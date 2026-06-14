<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/api/axios'

const route = useRoute()
const router = useRouter()

const failedDonation = ref(null)
const loading = ref(true)

const formattedAmount = computed(() => {
  if (!failedDonation.value) return ''
  return 'Rp ' + Number(failedDonation.value.nominal).toLocaleString('id-ID')
})

onMounted(async () => {
  try {
    const res = await api.get(`/donations/${route.params.id}`)
    failedDonation.value = res.data.data
  } catch {
    alert('Gagal memuat data donasi')
    router.push('/donations/history')
  } finally {
    loading.value = false
  }
})

function retryPayment() {
  const method = failedDonation.value?.metode_pembayaran
  const va = ['bca', 'mandiri', 'bni', 'bri', 'btn', 'syariah']
  if (va.includes(method)) {
    router.push(`/payments/va/${route.params.id}`)
  } else {
    router.push(`/payments/checkout/${route.params.id}`)
  }
}

function changeMethod() {
  router.back()
}

function contactSupport() {
  alert('Hubungi bantuan melalui email support@berbagive.com')
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center px-4" style="background-color: #E8DDD0;">

    <div v-if="loading" class="text-sm text-gray-500">Memuat...</div>

    <div v-if="failedDonation" class="bg-white rounded-3xl shadow-lg w-full max-w-sm p-8">

      <!-- Error Icon -->
      <div
        class="w-14 h-14 rounded-full flex items-center justify-center mx-auto border border-gray-200"
        style="background-color: #F5F0E8;"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24"
          fill="none" stroke="#6B7280" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18"/>
          <line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </div>

      <!-- Title -->
      <h1 class="text-2xl font-bold text-center mt-4" style="color: #1a1a1a;">
        Donasi Gagal
      </h1>

      <!-- Error Info Box -->
      <div class="rounded-xl p-4 mt-4" style="background-color: #FDF0E8;">
        <p class="text-xs uppercase tracking-wider font-medium mb-1.5" style="color: #9CA3AF;">
          Terjadi Kesalahan
        </p>
        <p class="text-sm leading-relaxed" style="color: #4B5563;">
          Donasi sebesar {{ formattedAmount }} tidak dapat diproses saat ini. Silakan coba kembali atau gunakan metode pembayaran lain.
        </p>
      </div>

      <!-- Badges Row -->
      <div class="flex items-center gap-3 mt-4">
        <span
          class="flex items-center gap-1.5 border border-gray-200 rounded-full px-3 py-1 text-xs"
          style="color: #6B7280;"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2" y="5" width="20" height="14" rx="2"/>
            <line x1="2" y1="10" x2="22" y2="10"/>
          </svg>
          #{{ failedDonation.nomor_transaksi }}
        </span>

        <button
          @click="changeMethod"
          class="text-xs underline underline-offset-2 transition-opacity hover:opacity-70"
          style="color: #0D9488;"
        >
          Coba Metode Lain
        </button>
      </div>

      <!-- Action Buttons -->
      <div class="flex flex-col gap-3 mt-6">
        <button
          @click="retryPayment"
          class="w-full py-3 rounded-xl font-semibold text-white text-sm transition-opacity hover:opacity-90 active:opacity-80"
          style="background-color: #1a2744;"
        >
          Coba Lagi
        </button>

        <button
          @click="contactSupport"
          class="text-sm text-center transition-opacity hover:opacity-60"
          style="color: #9CA3AF;"
        >
          Hubungi Bantuan
        </button>
      </div>

    </div>
  </div>
</template>