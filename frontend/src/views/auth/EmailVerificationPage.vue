<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import axios from 'axios'
import { MailCheck, Clock, Loader2 } from 'lucide-vue-next'
import { useAuthStore } from '@/stores/authStore'

const authStore = useAuthStore()
const email = computed(() => authStore.pendingEmail || 'user@email.com')

const api = axios.create({
  baseURL: `${import.meta.env.VITE_API_URL}/api/v1`,
})

const countdown = ref(30)
const COUNTDOWN_START = 30
const canResend = computed(() => countdown.value === 0)
const progressPercent = computed(() => (countdown.value / COUNTDOWN_START) * 100)
const resending = ref(false)
const toast = ref('')

let intervalId = null

const startCountdown = () => {
  countdown.value = COUNTDOWN_START
  if (intervalId) clearInterval(intervalId)
  intervalId = setInterval(() => {
    if (countdown.value > 0) countdown.value--
    else clearInterval(intervalId)
  }, 1000)
}

const formatTime = (sec) => {
  const m = String(Math.floor(sec / 60)).padStart(2, '0')
  const s = String(sec % 60).padStart(2, '0')
  return `${m}:${s}`
}

const resendEmail = async () => {
  if (!canResend.value || resending.value) return
  resending.value = true
  try {
    await api.post('/auth/resend-verification', { email: email.value })
    toast.value = 'Email verifikasi berhasil dikirim ulang.'
    setTimeout(() => (toast.value = ''), 3000)
    startCountdown()
  } catch (err) {
    toast.value = 'Gagal mengirim ulang email. Coba lagi.'
    setTimeout(() => (toast.value = ''), 3000)
  } finally {
    resending.value = false
  }
}

onMounted(startCountdown)
onUnmounted(() => intervalId && clearInterval(intervalId))
</script>

<template>
  <div class="min-h-screen bg-[#E8DDD0] flex flex-col">
    <main class="flex-1 flex items-center justify-center px-4 py-8">
      <div class="w-full max-w-md bg-white rounded-2xl shadow-md p-10 text-center">
        <!-- Icon -->
        <div class="mx-auto h-14 w-14 rounded-full bg-[#5BC8C0]/15 flex items-center justify-center mb-4">
          <MailCheck class="h-7 w-7 text-[#1a2744]" />
        </div>

        <h1 class="text-xl font-bold text-[#1a2744] mb-3">Verifikasi Email</h1>

        <p class="text-sm text-gray-500">
          Kami telah mengirimkan link verifikasi ke email kamu
          <br />
          <span class="font-semibold text-[#5BC8C0]">{{ email }}</span>
        </p>

        <p class="text-sm text-gray-500 mt-3 mb-6">
          Silakan cek inbox atau folder spam untuk melanjutkan pendaftaran kamu di Berbagive.
        </p>

        <!-- Countdown box -->
        <div class="bg-[#FDF5EE] rounded-xl p-4 mb-6">
          <div class="flex items-center justify-center gap-1.5 text-[10px] tracking-widest font-semibold text-gray-500">
            <Clock class="h-3 w-3" />
            LINK VERIFIKASI AKAN DIKIRIM DALAM
          </div>
          <p class="font-mono text-3xl font-bold text-[#1a2744] mt-2">
            {{ formatTime(countdown) }}
          </p>
          <div class="mt-3 h-1 w-full bg-gray-200 rounded-full overflow-hidden">
            <div
              class="h-full bg-[#5BC8C0] transition-all duration-1000 ease-linear"
              :style="{ width: `${progressPercent}%` }"
            ></div>
          </div>
        </div>

        <p class="text-sm text-gray-500 mb-3">Belum menerima email?</p>

        <button
          @click="resendEmail"
          :disabled="!canResend || resending"
          class="w-full h-12 bg-[#1a2744] hover:bg-[#2a3a5c] text-white rounded-full font-medium transition-colors flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <Loader2 v-if="resending" class="h-4 w-4 animate-spin" />
          {{ resending ? 'Mengirim...' : 'Kirim Ulang Email' }}
        </button>

        <p v-if="toast" class="mt-3 text-xs text-gray-600">{{ toast }}</p>
      </div>
    </main>
  </div>
</template>
