<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Navbar from '@/components/shared/Navbar.vue'
import QRCodeDisplay from '@/components/payment/QRCodeDisplay.vue'
import api from '@/api/axios'

const route = useRoute()
const router = useRouter()

const donationId = ref(route.params.id)
const transactionId = ref('')
const totalAmount = ref(0)
const paymentMethod = ref('qris')
const loading = ref(true)
const paying = ref(false)
const error = ref('')
const toast = ref('')
const toastVisible = ref(false)
const pollCount = ref(0)

const timeLeft = ref(29 * 60 + 45)
let timer = null
let pollTimer = null

const isEWallet = computed(() => ['qris', 'gopay', 'ovo', 'shopeepay'].includes(paymentMethod.value))
const isVA = computed(() => ['bca', 'mandiri', 'bni', 'bri', 'btn'].includes(paymentMethod.value))

const vaNumber = computed(() => {
  if (!isVA.value) return ''
  const rand = Math.floor(1000000000 + Math.random() * 9000000000)
  return '8801' + rand
})

const formattedAmount = computed(() => {
  return 'Rp ' + totalAmount.value.toLocaleString('id-ID')
})

const methodLabel = computed(() => {
  const labels = {
    qris: 'QRIS', gopay: 'GoPay', ovo: 'OVO', shopeepay: 'ShopeePay',
    bca: 'BCA', mandiri: 'Bank Mandiri', bni: 'BNI',
  }
  return labels[paymentMethod.value] || paymentMethod.value
})

onMounted(async () => {
  await fetchDonation()
  timer = setInterval(() => {
    if (timeLeft.value > 0) timeLeft.value--
  }, 1000)
  startPollToast()
})

onUnmounted(() => {
  clearInterval(timer)
  clearInterval(pollTimer)
})

function startPollToast() {
  pollTimer = setInterval(() => {
    if (pollCount.value < 3) {
      pollCount.value++
      showToast('Menunggu pembayaran... (' + pollCount.value + '/3)')
    } else {
      clearInterval(pollTimer)
    }
  }, 3000)
}

function showToast(msg) {
  toast.value = msg
  toastVisible.value = true
  setTimeout(() => { toastVisible.value = false }, 2500)
}

async function fetchDonation() {
  try {
    const res = await api.get('/donations/' + donationId.value)
    const data = res.data.data
    transactionId.value = data.nomor_transaksi ?? data.id_donasi
    totalAmount.value = data.nominal
    paymentMethod.value = data.metode_pembayaran || 'qris'
  } catch {
    error.value = 'Gagal memuat data donasi'
  } finally {
    loading.value = false
  }
}

async function handlePaySuccess() {
  paying.value = true
  error.value = ''
  try {
    await api.patch('/donations/' + donationId.value + '/payment-status', {
      status_pembayaran: 'berhasil',
    })
    router.push('/donations/success/' + donationId.value)
  } catch (e) {
    error.value = e.response?.data?.message || 'Gagal memproses pembayaran'
  } finally {
    paying.value = false
  }
}

async function handlePayCancel() {
  paying.value = true
  try {
    await api.patch('/donations/' + donationId.value + '/payment-status', {
      status_pembayaran: 'gagal',
    })
    router.push('/donations/failed/' + donationId.value)
  } catch {
    router.push('/donations/failed/' + donationId.value)
  } finally {
    paying.value = false
  }
}

async function copyVA() {
  if (!vaNumber.value) return
  try {
    await navigator.clipboard.writeText(vaNumber.value)
    showToast('No. VA disalin!')
  } catch {
    showToast('Gagal menyalin')
  }
}
</script>

<template>
  <div class="min-h-screen bg-[#F5F0E8] flex flex-col">
    <Navbar />

    <main class="flex-1 px-4 py-8">
      <div v-if="loading" class="text-center pt-20 text-sm text-gray-500">Memuat data donasi...</div>

      <template v-else-if="error && !totalAmount">
        <div class="text-center pt-20 text-sm text-red-500">{{ error }}</div>
      </template>

      <template v-else>
        <div class="text-center mb-6">
          <h1 class="text-2xl font-bold text-[#2C2C2C]">Selesaikan Pembayaran</h1>
          <p class="text-sm text-gray-500 mt-1">Terima kasih atas kontribusi Anda</p>
        </div>

        <!-- Payment Method Tab -->
        <div class="flex justify-center mb-6">
          <div class="inline-flex items-center gap-2 bg-white rounded-xl px-4 py-2.5 shadow-sm border border-stone-100">
            <svg v-if="isEWallet" class="w-4 h-4 text-[#8B4513]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/>
            </svg>
            <svg v-else class="w-4 h-4 text-[#8B4513]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11M20 10v11M8 10v11M12 10v11M16 10v11"/>
            </svg>
            <span class="text-sm font-medium text-gray-700">{{ methodLabel }}</span>
            <span class="text-[10px] font-semibold uppercase bg-[#8B4513] text-white px-2.5 py-0.5 rounded-full">TERPILIH</span>
          </div>
        </div>

        <div class="max-w-sm mx-auto">
          <div class="bg-white rounded-2xl shadow-md p-6">
            <!-- Transaction ID -->
            <div class="text-center mb-4">
              <p class="text-xs uppercase tracking-widest text-gray-400 mb-1">ID Transaksi</p>
              <p class="text-base font-bold text-[#2C2C2C]">#{{ transactionId }}</p>
            </div>

            <!-- Total -->
            <div class="bg-[#8B4513] rounded-xl px-5 py-4 text-center mb-4">
              <p class="text-xs uppercase tracking-widest text-white/80 mb-1">Total Pembayaran</p>
              <p class="text-2xl font-bold text-white">{{ formattedAmount }}</p>
            </div>

            <!-- QR Code (for e-wallet) -->
            <div v-if="isEWallet">
              <QRCodeDisplay :timeLeft="timeLeft" />
            </div>

            <!-- VA Number (for bank transfer) -->
            <div v-if="isVA">
              <div class="flex items-center justify-center gap-1.5 mb-4">
                <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
                <span class="text-xs font-medium text-rose-500">Berakhir dalam: {{ String(Math.floor(timeLeft/60)).padStart(2,'0') }}:{{ String(timeLeft%60).padStart(2,'0') }}</span>
              </div>

              <div class="bg-[#6B3A2A] rounded-xl p-5 text-center">
                <p class="text-xs text-white/70 mb-2">Nomor Virtual Account</p>
                <p class="text-xl font-bold text-white tracking-wider select-all">{{ vaNumber }}</p>
                <button @click="copyVA" class="mt-3 px-4 py-1.5 bg-white/20 text-white text-xs rounded-lg hover:bg-white/30 transition-colors">
                  Salin Nomor VA
                </button>
              </div>

              <p class="text-xs text-center text-gray-500 mt-3 leading-relaxed">
                Transfer ke nomor Virtual Account di atas melalui {{ methodLabel }}. Dana akan otomatis terverifikasi.
              </p>
            </div>

            <!-- Info -->
            <div class="flex items-start gap-3 rounded-xl p-3 mt-4 bg-[#FDF0E8]">
              <svg class="w-4 h-4 mt-0.5 shrink-0 text-teal-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"/>
              </svg>
              <p class="text-xs text-gray-500 leading-relaxed">
                Ini adalah simulasi pembayaran. Klik "Saya Sudah Bayar" untuk menandai donasi sebagai berhasil.
              </p>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-col items-center gap-3 mt-6 max-w-sm mx-auto">
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

    <!-- Toast -->
    <div
      v-if="toastVisible"
      class="fixed bottom-8 left-1/2 -translate-x-1/2 z-50 px-6 py-3 bg-[#2C2C2C] text-white text-sm rounded-xl shadow-lg transition-all duration-300"
    >
      {{ toast }}
    </div>
  </div>
</template>
