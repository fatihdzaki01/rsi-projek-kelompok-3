<template>
  <div class="min-h-screen bg-[#F5F0E8] flex flex-col">

    <!-- Navbar -->
    <nav class="bg-[#F5F0E8] border-b border-stone-200 px-6 py-3 flex items-center justify-between">
      <div class="flex items-center gap-6">
        <span class="font-bold text-[#1a2744] tracking-wide text-sm">BERBAGIVE</span>
        <div class="hidden md:flex items-center gap-1">
          <a href="#" class="px-3 py-1.5 text-xs font-medium bg-[#1a2744] text-white rounded-full">Beranda</a>
          <a href="#" class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 rounded-full">Campaign</a>
          <a href="#" class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 rounded-full">Komunitas</a>
          <a href="#" class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 rounded-full">Donasi Saya</a>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <div class="relative hidden sm:block">
          <svg class="w-3.5 h-3.5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
          </svg>
          <input
            type="text"
            placeholder="Search"
            class="bg-white/70 text-xs pl-8 pr-4 py-1.5 rounded-full border border-stone-200 focus:outline-none focus:ring-1 focus:ring-stone-300 w-36"
          />
        </div>
        <!-- User icon -->
        <button class="w-7 h-7 rounded-full bg-stone-300 flex items-center justify-center">
          <svg class="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
          </svg>
        </button>
        <!-- Mail icon -->
        <button class="text-gray-500 hover:text-gray-700">
          <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
          </svg>
        </button>
        <!-- Logout icon -->
        <button class="text-gray-500 hover:text-gray-700">
          <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/>
          </svg>
        </button>
      </div>
    </nav>

    <!-- Main content -->
    <main class="flex-1 flex flex-col items-center px-4 py-10">

      <!-- Page Header -->
      <div class="text-center mb-7">
        <h1 class="text-2xl font-bold text-[#1a1a1a] mb-1">Selesaikan Pembayaran</h1>
        <p class="text-sm text-gray-500">Terima kasih atas kontribusi Anda untuk kebaikan.</p>
      </div>

      <!-- Payment Method Tab -->
      <div class="w-full max-w-sm mb-4">
        <div class="inline-flex items-center gap-2 bg-white rounded-xl px-4 py-2.5 shadow-sm border border-stone-100">
          <!-- Bank icon -->
          <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path d="M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11M20 10v11M8 10v11M12 10v11M16 10v11"/>
          </svg>
          <span class="text-sm font-medium text-gray-700">{{ paymentMethod }}</span>
          <span class="ml-1 text-[10px] font-semibold uppercase tracking-wide bg-amber-700 text-white px-2.5 py-0.5 rounded-full">TERPILIH</span>
        </div>
      </div>

      <!-- Payment Info Card -->
      <div class="w-full max-w-sm mb-5">
        <PaymentInfoCard
          :transaction-id="transactionId"
          :total-amount="totalAmount"
          :formatted-time="formattedTime"
        />
      </div>

      <!-- Action Buttons -->
      <div class="w-full max-w-sm flex flex-col items-center gap-2">
        <button
          @click="checkStatus"
          class="w-full bg-[#1a2744] text-white text-sm font-semibold rounded-xl px-8 py-3 hover:bg-[#22325a] transition-colors duration-150 active:scale-[0.98]"
        >
          Cek Status Pembayaran
        </button>
        <button
          @click="changeMethod"
          class="text-xs text-gray-400 hover:text-gray-600 transition-colors duration-150 mt-1"
        >
          ← Ganti metode pembayaran
        </button>
      </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-stone-200 bg-[#F5F0E8] px-6 py-6">
      <div class="max-w-5xl mx-auto flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <p class="font-bold text-[#1a2744] text-sm mb-0.5">Berbagive</p>
          <p class="text-[10px] text-gray-400">© 2024 Berbagive. Part of The Human Archive project.</p>
        </div>
        <div class="flex items-center gap-5 text-xs text-gray-500">
          <a href="#" class="hover:text-gray-700">Kebijakan Privasi</a>
          <a href="#" class="hover:text-gray-700">Syarat &amp; Ketentuan</a>
          <a href="#" class="hover:text-gray-700">Hubungi Kami</a>
          <a href="#" class="hover:text-gray-700">FAQ</a>
          <button class="hover:text-gray-700">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
              <path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92s2.92-1.31 2.92-2.92-1.31-2.92-2.92-2.92z"/>
            </svg>
          </button>
        </div>
      </div>
    </footer>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import PaymentInfoCard from '@/components/payment/PaymentInfoCard.vue'

const transactionId = ref('BRB-99201')
const totalAmount = ref(500000)
const paymentMethod = ref('Bank Mandiri')
const timeLeft = ref(23 * 60 + 45)
let timer = null

const formattedTime = computed(() => {
  const m = Math.floor(timeLeft.value / 60).toString().padStart(2, '0')
  const s = (timeLeft.value % 60).toString().padStart(2, '0')
  return `${m}:${s}`
})

onMounted(() => {
  timer = setInterval(() => {
    if (timeLeft.value > 0) timeLeft.value--
  }, 1000)
})

onUnmounted(() => clearInterval(timer))

function checkStatus() {
  // will call GET /api/v1/donations/{id}
  console.log('Checking status for:', transactionId.value)
}

function changeMethod() {
  // router.push('/payment/method')
  console.log('Changing payment method')
}
</script>
