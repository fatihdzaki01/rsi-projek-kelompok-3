<script setup>
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { User, Lock, Eye, EyeOff } from 'lucide-vue-next'
import Navbar from '@/components/shared/Navbar.vue'
import Footer from '@/components/shared/Footer.vue'
import UpdatePasswordSuccessCard from '@/components/auth/UpdatePasswordSuccessCard.vue'

const route = useRoute()
const router = useRouter()

const email = ref(route.query.email ?? '')
const token = route.query.token ?? ''

const newPassword = ref('')
const confirmPassword = ref('')
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)

const errors = ref({ newPassword: '', confirmPassword: '' })
const loading = ref(false)
const showSuccess = ref(false)

const validate = () => {
  const e = { newPassword: '', confirmPassword: '' }
  if (newPassword.value.length < 8) e.newPassword = 'Password minimal 8 karakter.'
  if (confirmPassword.value !== newPassword.value) e.confirmPassword = 'Konfirmasi password tidak sama.'
  errors.value = e
  return !e.newPassword && !e.confirmPassword
}

const submit = async () => {
  if (!validate()) return
  loading.value = true
  try {
    // await axios.post('/api/auth/reset-password', {
    //   token, email: email.value,
    //   password: newPassword.value,
    //   password_confirmation: confirmPassword.value,
    // })
    await new Promise((r) => setTimeout(r, 600))
    showSuccess.value = true
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
        <div class="hidden sm:block w-2/5 bg-[#FDF0E8] rounded-l-2xl" />

        <div class="w-full sm:w-3/5 bg-white rounded-r-2xl p-8">
          <p class="text-xs font-semibold tracking-widest text-[#1a2744] mb-2">BERBAGIVE</p>
          <h1 class="text-2xl font-bold text-[#1a2744]">Perbarui password</h1>
          <p class="text-sm text-[#6B7280] mb-6">Silahkan masukkan password baru anda</p>

          <form @submit.prevent="submit" novalidate class="space-y-4">
            <!-- Email (readonly) -->
            <div>
              <label class="block text-sm font-medium text-[#374151] mb-1.5">Email</label>
              <div class="relative">
                <User :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-[#9CA3AF]" />
                <input
                  v-model="email"
                  type="email"
                  readonly
                  class="w-full h-11 pl-9 pr-3 bg-[#F5F0E8] border border-gray-200 rounded-lg text-sm text-[#1a2744] opacity-75 cursor-not-allowed focus:outline-none"
                />
              </div>
            </div>

            <!-- New Password -->
            <div>
              <label class="block text-sm font-medium text-[#374151] mb-1.5">New Password</label>
              <div class="relative">
                <Lock :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-[#9CA3AF]" />
                <input
                  v-model="newPassword"
                  :type="showNewPassword ? 'text' : 'password'"
                  placeholder="••••••••"
                  class="w-full h-11 pl-9 pr-10 bg-[#F5F0E8] border border-gray-200 rounded-lg text-sm text-[#1a2744] placeholder-gray-400 focus:outline-none focus:border-[#8B4513]"
                />
                <button
                  type="button"
                  @click="showNewPassword = !showNewPassword"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-[#9CA3AF]"
                  :aria-label="showNewPassword ? 'Sembunyikan password' : 'Tampilkan password'"
                >
                  <EyeOff v-if="showNewPassword" :size="16" />
                  <Eye v-else :size="16" />
                </button>
              </div>
              <p v-if="errors.newPassword" class="mt-1.5 text-xs text-red-500">{{ errors.newPassword }}</p>
            </div>

            <!-- Verify New Password -->
            <div>
              <label class="block text-sm font-medium text-[#374151] mb-1.5">Verify new password</label>
              <div class="relative">
                <Lock :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-[#9CA3AF]" />
                <input
                  v-model="confirmPassword"
                  :type="showConfirmPassword ? 'text' : 'password'"
                  placeholder="••••••••"
                  class="w-full h-11 pl-9 pr-10 bg-[#F5F0E8] border border-gray-200 rounded-lg text-sm text-[#1a2744] placeholder-gray-400 focus:outline-none focus:border-[#8B4513]"
                />
                <button
                  type="button"
                  @click="showConfirmPassword = !showConfirmPassword"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-[#9CA3AF]"
                  :aria-label="showConfirmPassword ? 'Sembunyikan password' : 'Tampilkan password'"
                >
                  <EyeOff v-if="showConfirmPassword" :size="16" />
                  <Eye v-else :size="16" />
                </button>
              </div>
              <p v-if="errors.confirmPassword" class="mt-1.5 text-xs text-red-500">{{ errors.confirmPassword }}</p>
            </div>

            <button
              type="submit"
              :disabled="loading"
              class="w-full h-11 rounded-lg bg-[#1a2744] text-white font-medium hover:bg-[#2a3754] transition-colors disabled:opacity-50"
            >
              {{ loading ? 'Menyimpan...' : 'Save →' }}
            </button>

            <p class="text-center text-sm text-[#6B7280]">
              Belum punya akun Berbagive?
              <router-link to="/register" class="text-[#8B4513] font-semibold">Daftar sekarang</router-link>
            </p>
          </form>
        </div>
      </div>
    </main>

    <Footer />

    <UpdatePasswordSuccessCard
      v-if="showSuccess"
      @back="router.push('/login')"
      @view-history="router.push('/donations/history')"
    />
  </div>
</template>