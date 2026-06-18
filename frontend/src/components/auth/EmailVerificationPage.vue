<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import { MailCheck, Clock } from 'lucide-vue-next'
import Navbar from '@/components/shared/Navbar.vue'
import Footer from '@/components/shared/Footer.vue'

const route = useRoute()
const email = computed(() => route.query.email ?? '')

const INITIAL = 30
const countdown = ref(INITIAL)
let timer = null

const canResend = computed(() => countdown.value <= 0)

const formatted = computed(() => {
  const m = String(Math.floor(countdown.value / 60)).padStart(2, '0')
  const s = String(countdown.value % 60).padStart(2, '0')
  return `${m}:${s}`
})

const progress = computed(() => (countdown.value / INITIAL) * 100)

const startTimer = () => {
  clearInterval(timer)
  timer = setInterval(() => {
    if (countdown.value > 0) countdown.value--
    else clearInterval(timer)
  }, 1000)
}

const resend = async () => {
  if (!canResend.value) return
  countdown.value = INITIAL
  startTimer()
  // await axios.post('/api/auth/resend-verification', { email: email.value })
}

onMounted(startTimer)
onUnmounted(() => clearInterval(timer))
</script>

<template>
  <div class="min-h-screen flex flex-col bg-[#E8DDD0]">
    <Navbar :guest="true" />

    <main class="flex-1 flex items-center justify-center py-12 px-4">
      <div class="max-w-sm w-full mx-auto bg-white rounded-2xl shadow-md p-8 text-center">
        <!-- Icon -->
        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-[#F5E6D3]">
          <MailCheck :size="28" class="text-[#8B4513]" />
        </div>

        <h1 class="text-xl font-bold text-[#1a2744] mb-2">Verifikasi Email</h1>
        <p class="text-sm text-gray-500">Kami telah mengirimkan link verifikasi ke email kamu</p>
        <p class="text-sm font-semibold text-[#1a2744] mt-0.5">{{ email }}</p>
        <p class="text-sm text-gray-500 mt-2 mb-6">
          Silakan cek inbox atau folder spam untuk melanjutkan pendaftaran kamu di Berbagive.
        </p>

        <!-- Countdown box -->
        <div class="bg-[#FDF0E8] border border-[#E8DDD0] rounded-xl p-4 mb-4">
          <div class="flex items-center justify-center gap-1.5">
            <Clock :size="14" class="text-[#9CA3AF]" />
            <span class="text-xs uppercase tracking-wide text-gray-400">
              Link verifikasi akan dikirim dalam
            </span>
          </div>
          <p class="text-3xl font-bold font-mono text-[#1a2744] my-2">{{ formatted }}</p>
          <div class="h-1 w-full bg-[#E8DDD0] rounded-full overflow-hidden">
            <div
              class="h-full bg-[#1a2744] rounded-full transition-all duration-1000 ease-linear"
              :style="{ width: progress + '%' }"
            />
          </div>
        </div>

        <p class="text-sm text-gray-400 mb-3">Belum menerima email?</p>
        <button
          @click="resend"
          :disabled="!canResend"
          class="w-full h-11 rounded-lg bg-[#1a2744] text-white font-medium hover:bg-[#2a3754] transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Kirim Ulang Email
        </button>
      </div>
    </main>

    <Footer />
  </div>
</template>