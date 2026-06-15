<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { User, Mail, Lock, Eye, EyeOff } from 'lucide-vue-next'
import api from '@/api/axios'

const router = useRouter()

const form = reactive({
  username: '',
  email: '',
  password: '',
})

const errors = reactive({
  username: '',
  email: '',
  password: '',
})

const showPassword = ref(false)
const loading = ref(false)
const globalError = ref('')
const successMessage = ref('')

function resetErrors() {
  errors.username = ''
  errors.email = ''
  errors.password = ''
  globalError.value = ''
  successMessage.value = ''
}

function clearFieldError(field) {
  errors[field] = ''
  globalError.value = ''
  successMessage.value = ''
}

function validate() {
  resetErrors()
  let valid = true

  if (!form.username.trim()) {
    errors.username = 'Username wajib diisi'
    valid = false
  }

  if (!form.email.trim()) {
    errors.email = 'Alamat email wajib diisi'
    valid = false
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errors.email = 'Format email tidak valid'
    valid = false
  }

  if (!form.password.trim()) {
    errors.password = 'Password wajib diisi'
    valid = false
  } else if (form.password.length < 8) {
    errors.password = 'Password minimal 8 karakter'
    valid = false
  } else if (!/(?=.*[A-Za-z])(?=.*\d)/.test(form.password)) {
    errors.password = 'Password harus kombinasi huruf dan angka'
    valid = false
  }

  return valid
}

async function handleSubmit() {
  if (!validate()) return
  loading.value = true
  globalError.value = ''
  successMessage.value = ''

  try {
    const res = await api.post('/auth/register-user', {
      username: form.username,
      email: form.email,
      password: form.password,
    })

    const isVerified = res.data?.data?.is_verified
    if (isVerified) {
      successMessage.value = 'Registrasi berhasil! Silakan login.'
      setTimeout(() => router.push('/login'), 1000)
    } else {
      localStorage.setItem('verification_email', form.email)
      router.push({ path: '/email-verification', query: { email: form.email } })
    }
  } catch (error) {
    const status = error.response?.status
    const errData = error.response?.data?.errors || {}
    const message = error.response?.data?.message || error.message || ''

    resetErrors()

    if (status === 409) {
      globalError.value = message || 'Email sudah terdaftar'
    } else if (status === 400 || status === 422) {
      if (errData.username) errors.username = Array.isArray(errData.username) ? errData.username[0] : errData.username
      if (errData.email) errors.email = Array.isArray(errData.email) ? errData.email[0] : errData.email
      if (errData.password) errors.password = Array.isArray(errData.password) ? errData.password[0] : errData.password
      if (!errors.username && !errors.email && !errors.password) {
        globalError.value = message || 'Format email atau password tidak memenuhi ketentuan'
      }
    } else if (status === 500) {
      globalError.value = 'Server sedang bermasalah. Silakan coba lagi nanti'
    } else {
      globalError.value = message || 'Registrasi gagal. Silakan coba lagi'
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

      <h1 class="text-3xl font-bold text-[#1a2744] mb-1">Mari Mulai Kebaikan</h1>
      <p class="text-sm text-[#6B7280] mb-6">Bergabung menjadi donatur dan abadikan jejak kemanusiaan Anda.</p>

      <form @submit.prevent="handleSubmit" class="space-y-4" novalidate>
        <div>
          <label for="username" class="block text-sm font-medium text-[#374151] mb-1">Username</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-[#9CA3AF] pointer-events-none">
              <User class="h-4 w-4" />
            </span>
            <input
              id="username"
              v-model="form.username"
              type="text"
              placeholder="pilih nama unik"
              autocomplete="username"
              @input="clearFieldError('username')"
              class="w-full h-11 pl-10 pr-3 bg-[#F5F0E8] border border-gray-200 rounded-lg text-sm text-gray-700 placeholder-gray-400 outline-none focus:ring-2 focus:ring-[#1a2744]/30 transition-shadow"
              :class="{ 'ring-2 ring-red-400': errors.username }"
            />
          </div>
          <p v-if="errors.username" class="mt-1 text-xs text-red-500">{{ errors.username }}</p>
        </div>

        <div>
          <label for="email" class="block text-sm font-medium text-[#374151] mb-1">Alamat Email</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-[#9CA3AF] pointer-events-none">
              <Mail class="h-4 w-4" />
            </span>
            <input
              id="email"
              v-model="form.email"
              type="email"
              placeholder="nama@email.com"
              autocomplete="email"
              @input="clearFieldError('email')"
              class="w-full h-11 pl-10 pr-3 bg-[#F5F0E8] border border-gray-200 rounded-lg text-sm text-gray-700 placeholder-gray-400 outline-none focus:ring-2 focus:ring-[#1a2744]/30 transition-shadow"
              :class="{ 'ring-2 ring-red-400': errors.email }"
            />
          </div>
          <p v-if="errors.email" class="mt-1 text-xs text-red-500">{{ errors.email }}</p>
        </div>

        <div>
          <label for="password" class="block text-sm font-medium text-[#374151] mb-1">Kata Sandi</label>
          <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-[#9CA3AF] pointer-events-none">
              <Lock class="h-4 w-4" />
            </span>
            <input
              id="password"
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="min. 8 karakter, kombinasi huruf & angka"
              autocomplete="new-password"
              @input="clearFieldError('password')"
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

        <p v-if="globalError" class="text-xs text-red-500 text-center">{{ globalError }}</p>
        <p v-if="successMessage" class="text-xs text-green-600 text-center">{{ successMessage }}</p>

        <button
          type="submit"
          :disabled="loading"
          class="w-full h-11 bg-[#1a2744] hover:bg-[#2a3754] disabled:opacity-60 disabled:cursor-not-allowed text-white rounded-lg font-medium transition-colors flex items-center justify-center gap-1.5"
        >
          <template v-if="loading">Memproses...</template>
          <template v-else>Daftar <span aria-hidden="true" class="text-lg leading-none">→</span></template>
        </button>
      </form>

      <p class="mt-6 text-center text-sm text-gray-600">
        Sudah memiliki akun?
        <button type="button" @click="router.push('/login')" class="text-[#8B4513] font-semibold hover:underline ml-0.5">Masuk ke Dashboard</button>
      </p>
    </div>
  </div>
</template>
