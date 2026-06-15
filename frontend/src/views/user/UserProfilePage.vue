<template>
  <div class="min-h-screen bg-[#F5F0E8] flex flex-col">
    <Navbar />

    <main class="flex-1 py-8">
      <div class="max-w-3xl mx-auto px-6">

        <nav class="text-sm text-gray-500 mb-4 flex items-center gap-1">
          <router-link to="/" class="hover:text-[#8B4513] transition-colors">Beranda</router-link>
          <span>/</span>
          <span class="text-[#2C2C2C] font-medium">Profil Saya</span>
        </nav>

        <div v-if="loading" class="flex flex-col items-center justify-center py-20">
          <div class="w-8 h-8 border-2 border-[#8B4513] border-t-transparent rounded-full animate-spin mb-3" />
          <p class="text-sm text-gray-400">Memuat profil...</p>
        </div>

        <div v-else-if="error" class="bg-white rounded-xl shadow-sm p-8 text-center">
          <p class="text-sm text-red-500 mb-4">{{ error }}</p>
          <button @click="fetchProfile" class="px-5 py-2 bg-[#8B4513] text-white rounded-lg text-sm font-medium hover:bg-[#6b3410] transition-colors">Coba Lagi</button>
        </div>

        <template v-else>
          <div class="bg-white rounded-xl shadow-sm border border-stone-100 overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-[#1a2744] to-[#2a3f64] px-6 py-8 text-white">
              <div class="flex items-center gap-5">
                <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center text-2xl font-bold uppercase">
                  {{ (profile.nama_lengkap || profile.email || '?').charAt(0) }}
                </div>
                <div>
                  <h1 class="text-lg font-bold">{{ profile.nama_lengkap || 'Pengguna' }}</h1>
                  <p class="text-sm text-white/70">{{ profile.email }}</p>
                  <span class="inline-block mt-1 px-3 py-0.5 rounded-full text-xs font-medium bg-white/20 text-white/90">
                    {{ roleLabel }}
                  </span>
                </div>
              </div>
            </div>

            <div class="px-6 py-5 space-y-4">
              <div class="flex items-center gap-3 text-sm">
                <span class="text-gray-400 w-28 flex-shrink-0">Nomor Telepon</span>
                <span class="text-[#2C2C2C]">{{ profile.nomor_telepon || '-' }}</span>
              </div>
              <div class="flex items-center gap-3 text-sm">
                <span class="text-gray-400 w-28 flex-shrink-0">Tanggal Bergabung</span>
                <span class="text-[#2C2C2C]">{{ profile.created_at ? new Date(profile.created_at).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' }) : '-' }}</span>
              </div>
              <div v-if="profile.role === 'DONATUR'" class="flex items-center gap-3 text-sm">
                <span class="text-gray-400 w-28 flex-shrink-0">Total Donasi</span>
                <span class="text-[#2C2C2C] font-medium">{{ profile.total_donasi ? 'Rp ' + Number(profile.total_donasi).toLocaleString('id-ID') : '-' }}</span>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-xl shadow-sm border border-stone-100 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-stone-100">
              <h2 class="text-sm font-bold text-[#2C2C2C]">Edit Profil</h2>
            </div>
            <div class="px-6 py-5 space-y-4">
              <div v-if="editSuccess" class="p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
                {{ editSuccess }}
              </div>
              <div v-if="editError" class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-600">
                {{ editError }}
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Nama Lengkap</label>
                <input v-model="editForm.nama_lengkap" type="text" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm text-[#2C2C2C] focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-transparent" />
                <p v-if="editErrors.nama_lengkap" class="mt-1 text-xs text-red-500">{{ editErrors.nama_lengkap }}</p>
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Nomor Telepon</label>
                <input v-model="editForm.nomor_telepon" type="text" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm text-[#2C2C2C] focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-transparent" />
                <p v-if="editErrors.nomor_telepon" class="mt-1 text-xs text-red-500">{{ editErrors.nomor_telepon }}</p>
              </div>
              <button @click="handleUpdateProfile" :disabled="editLoading" class="px-5 py-2 bg-[#8B4513] text-white rounded-lg text-sm font-medium hover:bg-[#6b3410] transition-colors disabled:opacity-50">
                {{ editLoading ? 'Menyimpan...' : 'Simpan Perubahan' }}
              </button>
            </div>
          </div>

          <div class="bg-white rounded-xl shadow-sm border border-stone-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-stone-100">
              <h2 class="text-sm font-bold text-[#2C2C2C]">Ubah Password</h2>
            </div>
            <div class="px-6 py-5 space-y-4">
              <div v-if="pwSuccess" class="p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
                {{ pwSuccess }}
              </div>
              <div v-if="pwError" class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-600">
                {{ pwError }}
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Password Lama</label>
                <input v-model="passwordForm.password_lama" type="password" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm text-[#2C2C2C] focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-transparent" />
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Password Baru</label>
                <input v-model="passwordForm.password_baru" type="password" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm text-[#2C2C2C] focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-transparent" />
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Konfirmasi Password Baru</label>
                <input v-model="passwordForm.konfirmasi_password" type="password" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm text-[#2C2C2C] focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-transparent" />
              </div>
              <button @click="handleChangePassword" :disabled="pwLoading" class="px-5 py-2 bg-[#8B4513] text-white rounded-lg text-sm font-medium hover:bg-[#6b3410] transition-colors disabled:opacity-50">
                {{ pwLoading ? 'Menyimpan...' : 'Ubah Password' }}
              </button>
            </div>
          </div>
        </template>
      </div>
    </main>

    <AppFooter />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/api/axios'
import Navbar from '@/components/shared/Navbar.vue'
import AppFooter from '@/components/shared/AppFooter.vue'

const loading = ref(true)
const error = ref('')
const profile = ref({})

const editForm = ref({ nama_lengkap: '', nomor_telepon: '' })
const editErrors = ref({})
const editSuccess = ref('')
const editError = ref('')
const editLoading = ref(false)

const passwordForm = ref({ password_lama: '', password_baru: '', konfirmasi_password: '' })
const pwSuccess = ref('')
const pwError = ref('')
const pwLoading = ref(false)

const roleLabel = computed(() => {
  const labels = { DONATUR: 'Donatur', KOMUNITAS: 'Komunitas', SUPERADMIN: 'Superadmin' }
  return labels[profile.value.role] || profile.value.role
})

async function fetchProfile() {
  loading.value = true
  error.value = ''
  try {
    const res = await api.get('/users/me')
    profile.value = res.data.data || res.data
    editForm.value = {
      nama_lengkap: profile.value.nama_lengkap || '',
      nomor_telepon: profile.value.nomor_telepon || '',
    }
  } catch (e) {
    error.value = e.response?.data?.message || 'Gagal memuat profil'
  } finally {
    loading.value = false
  }
}

async function handleUpdateProfile() {
  editErrors.value = {}
  editSuccess.value = ''
  editError.value = ''
  editLoading.value = true
  try {
    await api.patch('/users/me', {
      nama_lengkap: editForm.value.nama_lengkap,
      nomor_telepon: editForm.value.nomor_telepon,
    })
    editSuccess.value = 'Profil berhasil diperbarui'
    profile.value.nama_lengkap = editForm.value.nama_lengkap
    profile.value.nomor_telepon = editForm.value.nomor_telepon
  } catch (e) {
    const status = e.response?.status
    const errData = e.response?.data?.errors || {}
    const message = e.response?.data?.message || ''
    if (status === 400 || status === 422) {
      editErrors.value = Object.fromEntries(
        Object.entries(errData).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v])
      )
      if (Object.keys(editErrors.value).length === 0) {
        editError.value = message || 'Data tidak valid'
      }
    } else {
      editError.value = message || 'Gagal memperbarui profil'
    }
  } finally {
    editLoading.value = false
  }
}

async function handleChangePassword() {
  pwSuccess.value = ''
  pwError.value = ''
  pwLoading.value = true
  try {
    await api.patch('/users/me/password', {
      password_lama: passwordForm.value.password_lama,
      password_baru: passwordForm.value.password_baru,
      konfirmasi_password: passwordForm.value.konfirmasi_password,
    })
    pwSuccess.value = 'Password berhasil diubah'
    passwordForm.value = { password_lama: '', password_baru: '', konfirmasi_password: '' }
  } catch (e) {
    pwError.value = e.response?.data?.message || 'Gagal mengubah password'
  } finally {
    pwLoading.value = false
  }
}

onMounted(fetchProfile)
</script>
