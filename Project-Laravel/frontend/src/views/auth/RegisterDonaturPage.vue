<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { User, AtSign, Lock, Loader2 } from 'lucide-vue-next'
import { useAuthStore } from '@/stores/authStore'

const router = useRouter()
const authStore = useAuthStore()

const api = axios.create({
  baseURL: `${import.meta.env.VITE_API_URL}/api/v1`,
})

const form = reactive({
  username: '',
  email: '',
  password: '',
})

const errors = reactive({
  username: '',
  email: '',
  password: '',
  general: '',
})

const loading = ref(false)

const validate = () => {
  errors.username = ''
  errors.email = ''
  errors.password = ''
  errors.general = ''
  let valid = true

  if (!form.username) {
    errors.username = 'Username wajib diisi'
    valid = false
  } else if (!/^[a-zA-Z0-9_]{3,30}$/.test(form.username)) {
    errors.username = 'Username 3-30 karakter, hanya huruf/angka/underscore'
    valid = false
  }

  if (!form.email) {
    errors.email = 'Email wajib diisi'
    valid = false
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errors.email = 'Format email tidak valid'
    valid = false
  }

  if (!form.password) {
    errors.password = 'Password wajib diisi'
    valid = false
  } else if (form.password.length < 8) {
    errors.password = 'Password minimal 8 karakter'
    valid = false
  }

  return valid
}

const handleSubmit = async () => {
  if (!validate()) return

  loading.value = true
  try {
    await api.post('/auth/register-user', {
      username: form.username,
      email: form.email,
      password: form.password,
    })

    authStore.setPendingEmail(form.email)
    router.push('/register/verify-email')
  } catch (err) {
    if (err.response?.status === 422 && err.response.data?.errors) {
      const apiErrors = err.response.data.errors
      errors.username = apiErrors.username?.[0] || ''
      errors.email = apiErrors.email?.[0] || ''
      errors.password = apiErrors.password?.[0] || ''
    } else {
      router.push('/register/failed')
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-[#E8DDD0] flex flex-col">
    <main class="flex-1 flex items-center justify-center px-4 py-8">
      <div class="w-full max-w-md bg-[#FDF5EE] rounded-2xl shadow-md p-10">
        <h1 class="text-2xl font-bold text-[#1a2744]">Mari Mulai Kebaikan.</h1>
        <p class="text-sm text-gray-500 mt-2 mb-6">
          Bergabung menjadi donatur dan abadikan jejak kemanusiaan Anda.
        </p>

        <form @submit.prevent="handleSubmit" class="space-y-4" novalidate>
          <!-- Username -->
          <div>
            <label for="username" class="block text-xs font-semibold tracking-widest text-gray-500 mb-1">
              USERNAME
            </label>
            <div class="relative">
              <input
                id="username"
                v-model="form.username"
                type="text"
                placeholder="pilih nama unik"
                class="w-full h-11 pl-3 pr-10 bg-white border border-gray-200 rounded-lg text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#1a2744]/30"
              />
              <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                <User class="h-4 w-4" />
              </span>
            </div>
            <p v-if="errors.username" class="mt-1 text-xs text-red-500">{{ errors.username }}</p>
          </div>

          <!-- Email -->
          <div>
            <label for="email" class="block text-xs font-semibold tracking-widest text-gray-500 mb-1">
              ALAMAT EMAIL
            </label>
            <div class="relative">
              <input
                id="email"
                v-model="form.email"
                type="email"
                placeholder="nama@email.com"
                class="w-full h-11 pl-3 pr-10 bg-white border border-gray-200 rounded-lg text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#1a2744]/30"
              />
              <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                <AtSign class="h-4 w-4" />
              </span>
            </div>
            <p v-if="errors.email" class="mt-1 text-xs text-red-500">{{ errors.email }}</p>
          </div>

          <!-- Password -->
          <div>
            <label for="password" class="block text-xs font-semibold tracking-widest text-gray-500 mb-1">
              KATA SANDI
            </label>
            <div class="relative">
              <input
                id="password"
                v-model="form.password"
                type="password"
                placeholder="••••••••"
                class="w-full h-11 pl-3 pr-10 bg-white border border-gray-200 rounded-lg text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#1a2744]/30"
              />
              <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                <Lock class="h-4 w-4" />
              </span>
            </div>
            <p v-if="errors.password" class="mt-1 text-xs text-red-500">{{ errors.password }}</p>
          </div>

          <p v-if="errors.general" class="text-xs text-red-500 text-center">{{ errors.general }}</p>

          <button
            type="submit"
            :disabled="loading"
            class="w-full h-12 bg-[#1a2744] hover:bg-[#2a3a5c] text-white rounded-full font-medium transition-colors flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed mt-2"
          >
            <Loader2 v-if="loading" class="h-4 w-4 animate-spin" />
            <template v-else>
              Daftar
              <span aria-hidden="true">→</span>
            </template>
          </button>
        </form>

        <p class="mt-6 text-center text-xs text-gray-500">
          Sudah memiliki akun?
          <router-link to="/login" class="text-[#8B4513] font-semibold hover:underline">
            Masuk ke Dashboard
          </router-link>
        </p>
      </div>
    </main>
  </div>
</template>
