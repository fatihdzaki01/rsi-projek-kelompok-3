<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import QRCodeDisplay from '@/components/payment/QRCodeDisplay.vue'
import api from '@/api/axios'

const route = useRoute()
const router = useRouter()

const donationId = ref(route.params.id)
const transactionId = ref('')
const totalAmount = ref(0)
const timeLeft = ref(29 * 60 + 45)
const loading = ref(true)
let timer = null

const formattedAmount = computed(() => {
  return 'Rp ' + totalAmount.value.toLocaleString('id-ID')
})

onMounted(async () => {
  await fetchDonation()
  timer = setInterval(() => {
    if (timeLeft.value > 0) timeLeft.value--
  }, 1000)
})

onUnmounted(() => clearInterval(timer))

async function fetchDonation() {
  try {
    const res = await api.get(`/donations/${donationId.value}`)
    const data = res.data.data
    transactionId.value = data.nomor_transaksi ?? data.id_donasi
    totalAmount.value = data.nominal
  } catch {
    alert('Gagal memuat data donasi')
    router.back()
  } finally {
    loading.value = false
  }
}

async function checkStatus() {
  try {
    const res = await api.get(`/donations/${donationId.value}`)
    const data = res.data.data
    if (data.status_pembayaran === 'berhasil') {
      router.push(`/donations/success/${donationId.value}`)
    } else if (data.status_pembayaran === 'gagal') {
      router.push(`/donations/failed/${donationId.value}`)
    } else {
      alert('Pembayaran masih pending. Silakan selesaikan pembayaran Anda.')
    }
  } catch (e) {
    alert(e.response?.data?.message ?? 'Gagal mengecek status pembayaran')
  }
}

function changeMethod() {
  router.back()
}
</script>

<template>
  <div class="min-h-screen" style="background-color: #F5F0E8;">

    <!-- Navbar -->
    <nav class="bg-white shadow-sm px-6 py-3 flex items-center justify-between">
      <span class="font-bold text-base" style="color: #1a2744;">Berbagive</span>
      <div class="flex items-center gap-1 text-sm">
        <button class="px-4 py-1.5 rounded-full font-medium text-white text-xs" style="background-color: #8B4513;">Beranda</button>
        <button class="px-4 py-1.5 text-gray-600 text-xs hover:text-gray-900">Campaign</button>
        <button class="px-4 py-1.5 text-gray-600 text-xs hover:text-gray-900">Komunitas</button>
        <button class="px-4 py-1.5 text-gray-600 text-xs hover:text-gray-900">Donasi Saya</button>
      </div>
      <div class="flex items-center gap-3">
        <div class="flex items-center gap-2 border border-gray-200 rounded-full px-3 py-1.5">
          <svg class="w-3.5 h-3.5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <span class="text-xs text-gray-400">Search</span>
        </div>
        <svg class="w-4 h-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <svg class="w-4 h-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
      </div>
    </nav>

    <div v-if="loading" class="text-center pt-20 text-sm text-gray-500">Memuat data donasi...</div>

    <template v-if="!loading">
    <!-- Page Header -->
    <div class="text-center pt-10 pb-6 px-4">
      <h1 class="text-2xl font-bold" style="color: #1a2744;">Selesaikan Pembayaran</h1>
      <p class="text-sm mt-1" style="color: #9e8e80;">Terima kasih atas kontribusi Anda untuk kebaikan.</p>
    </div>

    <!-- Payment Method Tab -->
    <div class="flex justify-center mb-4 px-4">
      <div class="flex items-center gap-2 bg-white border border-gray-200 rounded-full px-4 py-2 shadow-sm">
        <svg class="w-4 h-4" style="color: #8B4513;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>
        </svg>
        <span class="text-sm font-medium" style="color: #1a2744;">QRIS / E-Wallet</span>
        <span class="text-xs font-semibold px-2 py-0.5 rounded-full text-white" style="background-color: #8B4513;">TERPILIH</span>
      </div>
    </div>

    <!-- Main Card -->
    <div class="max-w-sm mx-auto px-4">
      <div class="bg-white rounded-2xl shadow-md p-6">

        <!-- Transaction ID -->
        <div class="text-center mb-4">
          <p class="text-xs uppercase tracking-widest font-medium mb-1" style="color: #9e8e80;">ID Transaksi</p>
          <p class="text-base font-bold" style="color: #1a2744;">#{{ transactionId }}</p>
        </div>

        <!-- Total Payment Box -->
        <div class="rounded-xl px-5 py-4 text-center mb-4" style="background-color: #8B4513;">
          <p class="text-xs uppercase tracking-widest font-medium text-white opacity-80 mb-1">Total Pembayaran</p>
          <p class="text-2xl font-bold text-white">{{ formattedAmount }}</p>
        </div>

        <!-- QR Code + Countdown -->
        <QRCodeDisplay :timeLeft="timeLeft" />

        <!-- Info Box -->
        <div class="flex items-start gap-3 rounded-xl p-3 mt-4" style="background-color: #FDF0E8;">
          <svg class="w-4 h-4 mt-0.5 shrink-0" style="color: #5BC8C0;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
          </svg>
          <p class="text-xs leading-relaxed" style="color: #9e8e80;">
            Pembayaran akan otomatis terverifikasi setelah selesai. Mohon simpan bukti pembayaran Anda.
          </p>
        </div>

      </div>
    </div>
    </template>

    <!-- Action Buttons -->
    <div class="flex flex-col items-center gap-2 mt-6 px-4">
      <button
        @click="checkStatus"
        class="max-w-sm w-full py-3 rounded-xl font-semibold text-white text-sm transition-opacity hover:opacity-90"
        style="background-color: #1a2744;"
      >
        Cek Status Pembayaran
      </button>
      <button
        @click="changeMethod"
        class="text-xs underline transition-opacity hover:opacity-60"
        style="color: #9e8e80;"
      >
        ← Ganti metode pembayaran
      </button>
    </div>

    <!-- Footer -->
    <footer class="mt-12 bg-white border-t border-gray-100 px-6 py-5">
      <div class="max-w-4xl mx-auto flex items-center justify-between">
        <div>
          <p class="font-bold text-sm" style="color: #8B4513;">Berbagive</p>
          <p class="text-xs mt-0.5" style="color: #9e8e80;">© 2024 Berbagive. Part of The Human Archive project.</p>
        </div>
        <div class="flex items-center gap-4 text-xs" style="color: #9e8e80;">
          <span class="cursor-pointer hover:underline">Kebijakan Privasi</span>
          <span class="cursor-pointer hover:underline">Syarat & Ketentuan</span>
          <span class="cursor-pointer hover:underline">Hubungi Kami</span>
          <span class="cursor-pointer hover:underline">FAQ</span>
          <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
            <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
          </svg>
        </div>
      </div>
    </footer>

  </div>
</template>