<template>
  <AdminLayout>
    <div class="max-w-2xl mx-auto">
      <h1 class="text-lg font-bold text-[#1a2744] mb-6">Profil Superadmin</h1>

      <div v-if="loading" class="flex flex-col items-center justify-center py-20">
        <div class="w-8 h-8 border-2 border-[#8B4513] border-t-transparent rounded-full animate-spin mb-3" />
        <p class="text-sm text-gray-400">Memuat profil...</p>
      </div>

      <div v-else-if="fetchError" class="bg-white rounded-xl shadow-sm p-8 text-center">
        <p class="text-sm text-red-500 mb-4">{{ fetchError }}</p>
        <button @click="fetchProfile" class="px-5 py-2 bg-[#8B4513] text-white rounded-lg text-sm font-medium hover:bg-[#6b3410] transition-colors">Coba Lagi</button>
      </div>

      <template v-else>
        <div class="bg-white rounded-xl shadow-sm border border-stone-100 overflow-hidden mb-6">
          <div class="px-6 py-4 border-b border-stone-100">
            <h2 class="text-sm font-bold text-[#2C2C2C]">Informasi Profil</h2>
          </div>
          <div class="px-6 py-5 space-y-3">
            <div class="flex items-center gap-3 text-sm">
              <span class="text-gray-400 w-32 flex-shrink-0">Nama Lengkap</span>
              <span class="text-[#2C2C2C] font-medium">{{ profile.nama_lengkap || '-' }}</span>
            </div>
            <div class="flex items-center gap-3 text-sm">
              <span class="text-gray-400 w-32 flex-shrink-0">Username</span>
              <span class="text-[#2C2C2C]">{{ profile.username || '-' }}</span>
            </div>
            <div class="flex items-center gap-3 text-sm">
              <span class="text-gray-400 w-32 flex-shrink-0">Email</span>
              <span class="text-[#2C2C2C]">{{ profile.email || '-' }}</span>
            </div>
            <div class="flex items-center gap-3 text-sm">
              <span class="text-gray-400 w-32 flex-shrink-0">Role</span>
              <span class="px-3 py-0.5 rounded-full text-xs font-medium bg-[#1a2744] text-white">Superadmin</span>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-stone-100 overflow-hidden mb-6">
          <div class="px-6 py-4 border-b border-stone-100">
            <h2 class="text-sm font-bold text-[#2C2C2C]">Edit Profil</h2>
          </div>
          <div class="px-6 py-5 space-y-4">
            <div v-if="editSuccess" class="p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ editSuccess }}</div>
            <div v-if="editError" class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-600">{{ editError }}</div>

            <div>
              <label class="block text-xs font-medium text-gray-500 mb-1">Nama Lengkap</label>
              <input v-model="editForm.nama_lengkap" type="text" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm text-[#2C2C2C] focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-transparent" />
              <p v-if="editErrors.nama_lengkap" class="mt-1 text-xs text-red-500">{{ editErrors.nama_lengkap }}</p>
            </div>

            <div>
              <label class="block text-xs font-medium text-gray-500 mb-1">Foto Profil</label>
              <input type="file" accept=".jpg,.jpeg,.png" @change="onPhotoChange" class="w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border file:border-[#8B4513] file:text-xs file:font-medium file:text-[#8B4513] file:bg-white hover:file:bg-[#FDF0E8] file:cursor-pointer" />
              <p v-if="editErrors.foto_profil" class="mt-1 text-xs text-red-500">{{ editErrors.foto_profil }}</p>
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
            <div v-if="pwSuccess" class="p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ pwSuccess }}</div>
            <div v-if="pwError" class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-600">{{ pwError }}</div>

            <div>
              <label class="block text-xs font-medium text-gray-500 mb-1">Password Lama</label>
              <input v-model="passwordForm.old_password" type="password" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm text-[#2C2C2C] focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-transparent" />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-500 mb-1">Password Baru</label>
              <input v-model="passwordForm.new_password" type="password" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm text-[#2C2C2C] focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-transparent" />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-500 mb-1">Konfirmasi Password Baru</label>
              <input v-model="passwordForm.new_password_confirmation" type="password" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm text-[#2C2C2C] focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-transparent" />
            </div>
            <button @click="handleChangePassword" :disabled="pwLoading" class="px-5 py-2 bg-[#8B4513] text-white rounded-lg text-sm font-medium hover:bg-[#6b3410] transition-colors disabled:opacity-50">
              {{ pwLoading ? 'Menyimpan...' : 'Ubah Password' }}
            </button>
          </div>
        </div>
      </template>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api/axios'
import AdminLayout from '@/components/admin/AdminLayout.vue'

const loading = ref(true)
const fetchError = ref('')
const profile = ref({})

const editForm = ref({ nama_lengkap: '' })
const editErrors = ref({})
const editSuccess = ref('')
const editError = ref('')
const editLoading = ref(false)
const photoFile = ref(null)

const passwordForm = ref({ old_password: '', new_password: '', new_password_confirmation: '' })
const pwSuccess = ref('')
const pwError = ref('')
const pwLoading = ref(false)

function onPhotoChange(e) {
  photoFile.value = e.target.files[0] || null
}

async function fetchProfile() {
  loading.value = true
  fetchError.value = ''
  try {
    const res = await api.get('/superadmin/profile')
    profile.value = res.data.data || res.data
    editForm.value.nama_lengkap = profile.value.nama_lengkap || ''
  } catch (e) {
    fetchError.value = e.response?.data?.message || 'Gagal memuat profil'
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
    const fd = new FormData()
    fd.append('nama_lengkap', editForm.value.nama_lengkap)
    if (photoFile.value) fd.append('foto_profil', photoFile.value)
    fd.append('_method', 'PATCH')

    await api.post('/superadmin/profile', fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    editSuccess.value = 'Profil berhasil diperbarui'
    profile.value.nama_lengkap = editForm.value.nama_lengkap
    photoFile.value = null
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
    await api.patch('/superadmin/profile/password', {
      old_password: passwordForm.value.old_password,
      new_password: passwordForm.value.new_password,
      new_password_confirmation: passwordForm.value.new_password_confirmation,
    })
    pwSuccess.value = 'Password berhasil diubah'
    passwordForm.value = { old_password: '', new_password: '', new_password_confirmation: '' }
  } catch (e) {
    pwError.value = e.response?.data?.message || 'Gagal mengubah password'
  } finally {
    pwLoading.value = false
  }
}

onMounted(fetchProfile)
</script>
