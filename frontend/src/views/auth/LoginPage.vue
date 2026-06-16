<script setup>
import { ref, reactive } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { User, Lock, Eye, EyeOff } from 'lucide-vue-next'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const email = ref('')
const password = ref('')
const rememberMe = ref(false)
const showPassword = ref(false)
const loading = ref(false)
const globalError = ref('')

const errors = reactive({
  email: '',
  password: '',
})

function validate() {
  errors.email = ''
  errors.password = ''
  globalError.value = ''
  let valid = true

  if (!email.value) {
    errors.email = 'Email wajib diisi'
    valid = false
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
    errors.email = 'Format email tidak valid'
    valid = false
  }

  if (!password.value) {
    errors.password = 'Password wajib diisi'
    valid = false
  } else if (password.value.length < 6) {
    errors.password = 'Password minimal 6 karakter'
    valid = false
  }

  return valid
}

async function handleSubmit() {
  if (!validate()) return
  loading.value = true
  globalError.value = ''

  try {
    await authStore.login(email.value, password.value)

    if (route.query.redirect) {
      router.push(route.query.redirect)
    } else {
      const roleDashboard = {
        SUPERADMIN: '/dashboard',
        KOMUNITAS: '/communities/dashboard',
        DONATUR: '/campaigns',
      }
      router.push(roleDashboard[authStore.user?.role] || '/campaigns')
    }
  } catch (error) {
    const status = error.response?.status
    const message = error.response?.data?.message || ''

    if (status === 401) {
      errors.email = 'Email atau password salah'
      errors.password = 'Email atau password salah'
    } else if (status === 423) {
      globalError.value = 'Akun terkunci. Coba lagi nanti.'
    } else if (status === 403) {
      if (message.includes('verifikasi') || message.includes('review') || message.includes('proses')) {
        errors.email = 'Akun belum terverifikasi'
      } else if (message.includes('tidak aktif') || message.includes('nonaktif')) {
        errors.email = 'Akun tidak aktif'
      } else {
        errors.email = 'Akun belum terverifikasi'
      }
    } else if (status === 422) {
      const errData = error.response?.data?.errors || {}
      if (errData.email) errors.email = Array.isArray(errData.email) ? errData.email[0] : errData.email
      if (errData.password) errors.password = Array.isArray(errData.password) ? errData.password[0] : errData.password
    } else {
      globalError.value = 'Terjadi kesalahan. Silakan coba lagi.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen w-full bg-[#E8DDD0] flex items-center justify-center px-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-md p-10">
      <p class="text-xs font-semibold tracking-widest text-[#1a2744] mb-6">BERBAGIVE</p>

      <h1 class="text-3xl font-bold text-[#1a2744] mb-1">Masuk</h1>
      <p class="text-sm text-[#6B7280] mb-6">Selamat datang kembali. Silakan akses akun Anda.</p>

      <form @submit.prevent="handleSubmit" class="space-y-4" novalidate>
        <div>
          <label for="email" class="block text-sm font-medium text-[#374151] mb-1">Email</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-[#9CA3AF] pointer-events-none">
              <User class="h-4 w-4" />
            </span>
            <input
              id="email"
              v-model="email"
              type="email"
              placeholder="nama@email.com"
              autocomplete="email"
              class="w-full h-11 pl-10 pr-3 bg-[#F5F0E8] border border-gray-200 rounded-lg text-sm text-gray-700 placeholder-gray-400 outline-none focus:ring-2 focus:ring-[#1a2744]/30 transition-shadow"
              :class="{ 'ring-2 ring-red-400': errors.email }"
            />
          </div>
          <p v-if="errors.email" class="mt-1 text-xs text-red-500">{{ errors.email }}</p>
        </div>

        <div>
          <div class="flex items-center justify-between mb-1">
            <label for="password" class="text-sm font-medium text-[#374151]">Password</label>
            <button type="button" @click="router.push('/forgot-password')" class="text-sm text-[#8B4513] hover:underline">Lupa Password?</button>
          </div>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-[#9CA3AF] pointer-events-none">
              <Lock class="h-4 w-4" />
            </span>
            <input
              id="password"
              v-model="password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="••••••••"
              autocomplete="current-password"
              class="w-full h-11 pl-10 pr-10 bg-[#F5F0E8] border border-gray-200 rounded-lg text-sm text-gray-700 placeholder-gray-400 outline-none focus:ring-2 focus:ring-[#1a2744]/30 transition-shadow"
              :class="{ 'ring-2 ring-red-400': errors.password }"
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute inset-y-0 right-0 flex items-center pr-3 text-[#9CA3AF] hover:text-gray-600"
              :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'"
            >
              <EyeOff v-if="showPassword" class="h-4 w-4" />
              <Eye v-else class="h-4 w-4" />
            </button>
          </div>
          <p v-if="errors.password" class="mt-1 text-xs text-red-500">{{ errors.password }}</p>
        </div>

        <label class="flex items-center gap-2 text-sm text-[#6B7280] cursor-pointer">
          <input
            v-model="rememberMe"
            type="checkbox"
            class="h-4 w-4 rounded border-gray-300 text-[#1a2744] focus:ring-[#1a2744]"
          />
          Ingat saya untuk 30 hari
        </label>

        <p v-if="globalError" class="text-xs text-red-500 text-center">{{ globalError }}</p>

        <button
          type="submit"
          :disabled="loading"
          class="w-full h-11 bg-[#1a2744] hover:bg-[#2a3754] disabled:opacity-60 disabled:cursor-not-allowed text-white rounded-lg font-medium transition-colors flex items-center justify-center gap-1.5"
        >
          <template v-if="loading">Memproses...</template>
          <template v-else>Masuk <span aria-hidden="true" class="text-lg leading-none">→</span></template>
        </button>
      </form>

      <p class="mt-6 text-center text-sm text-gray-600">
        Belum punya akun Berbagive?
        <button type="button" @click="router.push('/register')" class="text-[#8B4513] font-semibold hover:underline ml-0.5">Daftar sekarang</button>
      </p>
    </div>
  </div>
</template>
