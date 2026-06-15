<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { Mail, ArrowLeft } from 'lucide-vue-next'
import api from '@/api/axios'

const router = useRouter()

const email = ref('')
const loading = ref(false)
const globalError = ref('')
const successMessage = ref('')

const errors = reactive({
  email: '',
})

function validate() {
  errors.email = ''
  globalError.value = ''
  successMessage.value = ''
  let valid = true

  if (!email.value.trim()) {
    errors.email = 'Email wajib diisi'
    valid = false
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
    errors.email = 'Format email tidak valid'
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
    const response = await api.post('/auth/forgot-password', {
      email: email.value,
    })

    successMessage.value = response.data?.message || 'Link reset password dikirim jika email terdaftar'

    setTimeout(() => {
      router.push({
        path: '/reset-password',
        query: { email: email.value },
      })
    }, 1500)
  } catch (error) {
    const status = error.response?.status
    const errData = error.response?.data?.errors || {}
    const message = error.response?.data?.message || ''

    if (status === 400 || status === 422) {
      if (errData.email) {
        errors.email = Array.isArray(errData.email) ? errData.email[0] : errData.email
      } else {
        globalError.value = message || 'Format email tidak valid'
      }
    } else if (status === 429) {
      globalError.value = 'Terlalu banyak permintaan. Coba lagi nanti.'
    } else {
      globalError.value = message || 'Gagal mengirim link reset password. Silakan coba lagi.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen w-full bg-[#E8DDD0] flex items-center justify-center px-4">
    <div class="w-full max-w-md">
      <!-- Back to login -->
      <button
        type="button"
        @click="router.push('/login')"
        class="flex items-center gap-1.5 text-sm text-[#8B4513] hover:text-[#6b3410] transition-colors mb-4"
      >
        <ArrowLeft class="h-4 w-4" />
        Kembali ke Login
      </button>

      <div class="bg-white rounded-2xl shadow-md p-10">
        <p class="text-xs font-semibold tracking-widest text-[#1a2744] mb-6">BERBAGIVE</p>

        <h1 class="text-3xl font-bold text-[#1a2744] mb-1">Lupa Password</h1>
        <p class="text-sm text-[#6B7280] mb-6">Masukkan email Anda dan kami akan kirimkan tautan reset password.</p>

        <form @submit.prevent="handleSubmit" class="space-y-4" novalidate>
          <div>
            <label for="email" class="block text-sm font-medium text-[#374151] mb-1">Email</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-[#9CA3AF] pointer-events-none">
                <Mail class="h-4 w-4" />
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

          <!-- Success message -->
          <div
            v-if="successMessage"
            class="p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700"
          >
            {{ successMessage }}
          </div>

          <p v-if="globalError" class="text-xs text-red-500 text-center">{{ globalError }}</p>

          <button
            type="submit"
            :disabled="loading || !!successMessage"
            class="w-full h-11 bg-[#1a2744] hover:bg-[#2a3754] disabled:opacity-60 disabled:cursor-not-allowed text-white rounded-lg font-medium transition-colors flex items-center justify-center gap-1.5"
          >
            <template v-if="loading">Mengirim...</template>
            <template v-else>Kirim Tautan Reset <span aria-hidden="true" class="text-lg leading-none">→</span></template>
          </button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-600">
          Belum punya akun Berbagive?
          <button type="button" @click="router.push('/register')" class="text-[#8B4513] font-semibold hover:underline ml-0.5">Daftar sekarang</button>
        </p>
      </div>
    </div>
  </div>
</template>
