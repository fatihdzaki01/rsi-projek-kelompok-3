<script setup>
import { ref, reactive, computed } from 'vue'
import { useRouter } from 'vue-router'
import { Eye, EyeOff, AlertTriangle } from 'lucide-vue-next'
import Navbar from '@/components/shared/Navbar.vue'
import Footer from '@/components/shared/Footer.vue'

const router = useRouter()

const oldPassword = ref('')
const newPassword = ref('')
const confirmPassword = ref('')
const showOld = ref(false)
const showNew = ref(false)
const showConfirm = ref(false)
const errors = reactive({ old: '', new: '', confirm: '' })

const passwordStrength = computed(() => {
  const p = newPassword.value
  if (!p) return 0
  let s = 0
  if (p.length >= 8) s++
  if (/[A-Z]/.test(p)) s++
  if (/[0-9]/.test(p)) s++
  if (/[^A-Za-z0-9]/.test(p)) s++
  return s
})

const strengthLabel = computed(() => ['', 'Lemah', 'Sedang', 'Kuat', 'Sangat Kuat'][passwordStrength.value])
const strengthColors = ['bg-gray-200', 'bg-red-500', 'bg-yellow-400', 'bg-green-300', 'bg-green-500']
const strengthTextColors = ['', 'text-red-500', 'text-yellow-600', 'text-green-500', 'text-green-600']

const segmentClass = (i) =>
  i < passwordStrength.value ? strengthColors[passwordStrength.value] : 'bg-gray-200'

const handleSubmit = () => {
  errors.old = errors.new = errors.confirm = ''
  if (!oldPassword.value) errors.old = 'Password lama wajib diisi.'
  if (newPassword.value.length < 8) errors.new = 'Password baru minimal 8 karakter.'
  if (confirmPassword.value !== newPassword.value) errors.confirm = 'Password tidak cocok.'
  if (errors.old || errors.new || errors.confirm) return
  alert('Password berhasil diubah')
}

const labelCls = 'block text-xs font-semibold text-[#9CA3AF] uppercase tracking-wider mb-1'
const inputCls = 'w-full h-10 pl-3 pr-10 bg-white border border-gray-200 rounded-lg text-sm text-[#374151] focus:outline-none focus:border-[#8B4513]'
</script>

<template>
  <div class="min-h-screen flex flex-col bg-[#E8DDD0]">
    <Navbar />

    <main class="flex-1 py-10 px-4">
      <p class="max-w-md mx-auto text-xs text-[#9CA3AF] mb-4">
        Beranda / Profil User / <span class="font-medium text-[#1a2744]">Ganti Password</span>
      </p>

      <div class="max-w-md mx-auto bg-white rounded-2xl shadow-sm p-6">
        <h1 class="text-lg font-bold text-[#1a2744]">Ganti Password</h1>
        <p class="text-xs text-[#9CA3AF] mb-5">Masukkan password lama untuk konfirmasi identitas Anda</p>

        <form @submit.prevent="handleSubmit" class="space-y-4">
          <!-- Password lama -->
          <div>
            <label :class="labelCls">Password Lama</label>
            <div class="relative">
              <input v-model="oldPassword" :type="showOld ? 'text' : 'password'" :class="inputCls" />
              <button type="button" @click="showOld = !showOld" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#9CA3AF]">
                <EyeOff v-if="showOld" :size="16" /><Eye v-else :size="16" />
              </button>
            </div>
            <p v-if="errors.old" class="mt-1 text-xs text-red-500">{{ errors.old }}</p>
          </div>

          <!-- Password baru -->
          <div>
            <label :class="labelCls">Password Baru</label>
            <div class="relative">
              <input v-model="newPassword" :type="showNew ? 'text' : 'password'" :class="inputCls" />
              <button type="button" @click="showNew = !showNew" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#9CA3AF]">
                <EyeOff v-if="showNew" :size="16" /><Eye v-else :size="16" />
              </button>
            </div>

            <div v-if="newPassword" class="flex items-center gap-2 mt-2">
              <div class="flex gap-1 flex-1">
                <div v-for="i in 4" :key="i" class="h-1 flex-1 rounded-full transition-colors" :class="segmentClass(i - 1)" />
              </div>
              <span class="text-xs" :class="strengthTextColors[passwordStrength]">{{ strengthLabel }}</span>
            </div>

            <p v-if="errors.new" class="mt-1 text-xs text-red-500">{{ errors.new }}</p>
          </div>

          <!-- Konfirmasi -->
          <div>
            <label :class="labelCls">Konfirmasi Password Baru</label>
            <div class="relative">
              <input v-model="confirmPassword" :type="showConfirm ? 'text' : 'password'" :class="inputCls" />
              <button type="button" @click="showConfirm = !showConfirm" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#9CA3AF]">
                <EyeOff v-if="showConfirm" :size="16" /><Eye v-else :size="16" />
              </button>
            </div>
            <p v-if="errors.confirm" class="mt-1 text-xs text-red-500">{{ errors.confirm }}</p>
          </div>

          <!-- Warning -->
          <div class="bg-[#FEF9C3] border border-yellow-200 rounded-lg p-3 flex items-start gap-2 mt-2">
            <AlertTriangle :size="14" class="text-[#CA8A04] shrink-0 mt-0.5" />
            <p class="text-xs text-[#92400E]">
              Semua sesi aktif lain akan diakhiri setelah password berhasil diubah.
            </p>
          </div>

          <div class="flex gap-3 pt-2">
            <button type="submit" class="bg-[#1a2744] text-white rounded-lg px-5 h-10 text-sm font-medium hover:bg-[#2a3754] transition-colors">
              Ubah Password
            </button>
            <button type="button" @click="router.back()" class="bg-white border border-gray-300 text-gray-500 rounded-lg px-5 h-10 text-sm hover:bg-gray-50 transition-colors">
              Batal
            </button>
          </div>
        </form>
      </div>
    </main>

    <Footer />
  </div>
</template>