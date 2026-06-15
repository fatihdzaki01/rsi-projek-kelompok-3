<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Navbar from '@/components/shared/Navbar.vue'
import api from '@/api/axios'

const route = useRoute()
const router = useRouter()

const donationId = ref(route.params.id)
const transactionId = ref('')
const totalAmount = ref(0)
const paymentMethod = ref('bca')
const loading = ref(true)
const paying = ref(false)
const error = ref('')
const copied = ref(false)

const timeLeft = ref(14 * 60 + 60) // ~15 minutes
let timer = null

const vaNumber = computed(() => {
  const rand = Math.floor(1000000000 + Math.random() * 9000000000)
  return '8801' + rand
})

const formattedTime = computed(() => {
  const m = Math.floor(timeLeft.value / 60).toString().padStart(2, '0')
  const s = (timeLeft.value % 60).toString().padStart(2, '0')
  return m + ':' + s
})

const formattedAmount = computed(() => 'Rp ' + totalAmount.value.toLocaleString('id-ID'))

const methodLabel = computed(() => {
  const labels = { bca: 'BCA', mandiri: 'Bank Mandiri', bni: 'BNI', bri: 'BRI', btn: 'BTN' }
  return labels[paymentMethod.value] || 'Bank Transfer'
})

onMounted(async () => {
  await fetchDonation()
  timer = setInterval(() => { if (timeLeft.value > 0) timeLeft.value-- }, 1000)
})

onUnmounted(() => clearInterval(timer))

async function fetchDonation() {
  try {
    const res = await api.get('/donations/' + donationId.value)
    const data = res.data.data
    transactionId.value = data.nomor_transaksi ?? data.id_donasi
    totalAmount.value = data.nominal
    paymentMethod.value = data.metode_pembayaran || 'bca'
  } catch {
    error.value = 'Gagal memuat data donasi'
  } finally {
    loading.value = false
  }
}

async function handlePaySuccess() {
  paying.value = true
  try {
    await api.patch('/donations/' + donationId.value + '/payment-status', { status_pembayaran: 'berhasil' })
    router.push('/donations/success/' + donationId.value)
  } catch (e) {
    error.value = e.response?.data?.message || 'Gagal memproses'
  } finally {
    paying.value = false
  }
}

async function handlePayCancel() {
  paying.value = true
  try {
    await api.patch('/donations/' + donationId.value + '/payment-status', { status_pembayaran: 'gagal' })
  } catch {}
  router.push('/donations/failed/' + donationId.value)
}

async function copyVA() {
  try {
    await navigator.clipboard.writeText(vaNumber.value)
    copied.value = true
    setTimeout(() => { copied.value = false }, 2000)
  } catch {}
}
</script>

<template>
  <div class="min-h-screen bg-[#F5F0E8] flex flex-col">
    <Navbar />

    <main class="flex-1 flex flex-col items-center px-4 py-8">
      <div v-if="loading" class="text-sm text-gray-500 pt-10">Memuat data donasi...</div>

      <template v-else-if="!error">
        <div class="text-center mb-6">
          <h1 class="text-2xl font-bold text-[#2C2C2C]">Selesaikan Pembayaran</h1>
          <p class="text-sm text-gray-500 mt-1">Transfer via Virtual Account {{ methodLabel }}</p>
        </div>

        <!-- Method badge -->
        <div class="inline-flex items-center gap-2 bg-white rounded-xl px-4 py-2.5 shadow-sm border border-stone-100 mb-6">
          <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path d="M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11M20 10v11M8 10v11M12 10v11M16 10v11"/>
          </svg>
          <span class="text-sm font-medium text-gray-700">{{ methodLabel }}</span>
          <span class="text-[10px] font-semibold uppercase bg-[#8B4513] text-white px-2.5 py-0.5 rounded-full">TERPILIH</span>
        </div>

        <div class="w-full max-w-sm">
          <div class="bg-white rounded-2xl shadow-md p-6">
            <!-- Transaction ID -->
            <div class="text-center mb-4">
              <p class="text-[10px] uppercase tracking-widest text-gray-400 mb-1">ID Transaksi</p>
              <p class="text-base font-bold text-[#2C2C2C]">#{{ transactionId }}</p>
            </div>

            <!-- Total -->
            <div class="bg-[#F5E6D8] rounded-xl px-4 py-4 text-center mb-4">
              <p class="text-[10px] uppercase tracking-widest text-gray-500 mb-1">Total Pembayaran</p>
              <p class="text-2xl font-bold text-[#2C2C2C]">{{ formattedAmount }}</p>
            </div>

            <!-- Countdown -->
            <div class="flex items-center justify-center gap-1.5 mb-4">
              <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
              </svg>
              <span class="text-xs text-rose-500 font-medium">Sisa waktu: {{ formattedTime }}</span>
            </div>

            <!-- VA Number -->
            <div class="bg-[#6B3A2A] rounded-xl p-5 text-center mb-4">
              <p class="text-xs text-white/70 mb-2">Nomor Virtual Account</p>
              <p class="text-xl font-bold text-white tracking-wider select-all">{{ vaNumber }}</p>
              <button @click="copyVA" class="mt-3 px-5 py-1.5 bg-white/20 text-white text-xs rounded-lg hover:bg-white/30 transition-colors">
                {{ copied ? 'Tersalin!' : 'Salin Nomor VA' }}
              </button>
            </div>

            <p class="text-xs text-center text-gray-500 mb-4 leading-relaxed">
              Transfer sejumlah <strong>{{ formattedAmount }}</strong> ke nomor VA di atas melalui {{ methodLabel }}.
            </p>

            <!-- Steps -->
            <div class="space-y-2 text-xs text-gray-500 bg-[#FDF0E8] rounded-xl p-3">
              <p class="font-medium text-gray-700 mb-1">Langkah pembayaran:</p>
              <p>1. Buka aplikasi {{ methodLabel }}</p>
              <p>2. Pilih menu Transfer &gt; Virtual Account</p>
              <p>3. Masukkan nomor VA: <strong class="text-[#2C2C2C]">{{ vaNumber }}</strong></p>
              <p>4. Konfirmasi transfer</p>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-col items-center gap-3 mt-6 w-full max-w-sm">
          <p v-if="error" class="text-sm text-red-500">{{ error }}</p>
          <button
            @click="handlePaySuccess"
            :disabled="paying"
            class="w-full py-3 rounded-xl font-semibold text-white text-sm bg-green-700 hover:bg-green-800 disabled:opacity-60 transition-colors"
          >
            {{ paying ? 'Memproses...' : 'Saya Sudah Bayar' }}
          </button>
          <button
            @click="handlePayCancel"
            :disabled="paying"
            class="w-full py-3 rounded-xl font-semibold text-gray-600 text-sm border border-gray-300 hover:bg-gray-50 disabled:opacity-60 transition-colors"
          >
            Batal Bayar
          </button>
        </div>
      </template>
    </main>
  </div>
</template>
