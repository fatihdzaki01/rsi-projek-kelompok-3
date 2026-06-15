<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { User } from 'lucide-vue-next'
import Navbar from '@/components/shared/Navbar.vue'
import Footer from '@/components/shared/Footer.vue'

const router = useRouter()
const email = ref('')
const error = ref('')
const loading = ref(false)

const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

const validate = () => {
  if (!email.value.trim()) return 'Email wajib diisi.'
  if (!emailRegex.test(email.value)) return 'Format email tidak valid.'
  return ''
}

const submit = async () => {
  error.value = validate()
  if (error.value) return
  loading.value = true
  try {
    // await axios.post('/api/auth/forgot-password', { email: email.value })
    await new Promise((r) => setTimeout(r, 600))
    router.push({ path: '/email-verification', query: { email: email.value } })
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex flex-col bg-[#E8DDD0]">
    <Navbar />

    <main class="flex-1 flex items-center justify-center py-12 px-4">
      <div class="max-w-lg w-full mx-auto flex rounded-2xl shadow-md overflow-hidden bg-white">
        <!-- Left decorative panel -->
        <div class="hidden sm:block w-2/5 bg-[#FDF0E8] rounded-l-2xl" />

        <!-- Right form panel -->
        <div class="w-full sm:w-3/5 bg-white rounded-r-2xl p-8">
          <p class="text-xs font-semibold tracking-widest text-[#1a2744] mb-2">BERBAGIVE</p>
          <h1 class="text-2xl font-bold text-[#1a2744]">Lupa password</h1>
          <p class="text-sm text-[#6B7280] mb-6">Silahkan masukkan email anda</p>

          <form @submit.prevent="submit" novalidate>
            <label class="block text-sm font-medium text-[#374151] mb-1.5">Email</label>
            <div class="relative">
              <User :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-[#9CA3AF]" />
              <input
                v-model="email"
                type="email"
                placeholder="nama@email.com"
                class="w-full h-11 pl-9 pr-3 bg-[#F5F0E8] border border-gray-200 rounded-lg text-sm text-[#1a2744] placeholder-gray-400 focus:outline-none focus:border-[#8B4513]"
              />
            </div>
            <p v-if="error" class="mt-1.5 text-xs text-red-500">{{ error }}</p>

            <button
              type="submit"
              :disabled="loading"
              class="mt-5 w-full h-11 rounded-lg bg-[#1a2744] text-white font-medium hover:bg-[#2a3754] transition-colors disabled:opacity-50"
            >
              {{ loading ? 'Mengirim...' : 'Send →' }}
            </button>

            <p class="mt-5 text-center text-sm text-[#6B7280]">
              Belum punya akun Berbagive?
              <router-link to="/register" class="text-[#8B4513] font-semibold">Daftar sekarang</router-link>
            </p>
          </form>
        </div>
      </div>
    </main>

    <Footer />
  </div>
</template>