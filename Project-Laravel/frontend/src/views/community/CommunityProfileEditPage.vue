<template>
  <div class="min-h-screen bg-[#F5F0E8] flex flex-col">
    <Navbar />

    <main class="flex-1 py-8">
      <div class="max-w-3xl mx-auto px-6">

        <nav class="text-sm text-gray-500 mb-4 flex items-center gap-1">
          <router-link to="/" class="hover:text-[#8B4513] transition-colors">Beranda</router-link>
          <span>/</span>
          <router-link to="/community/dashboard" class="hover:text-[#8B4513] transition-colors">Dashboard</router-link>
          <span>/</span>
          <span class="text-[#2C2C2C] font-medium">Edit Profil Komunitas</span>
        </nav>

        <div v-if="loading" class="flex flex-col items-center justify-center py-20">
          <div class="w-8 h-8 border-2 border-[#8B4513] border-t-transparent rounded-full animate-spin mb-3" />
          <p class="text-sm text-gray-400">Memuat profil komunitas...</p>
        </div>

        <div v-else-if="fetchError" class="bg-white rounded-xl shadow-sm p-8 text-center">
          <p class="text-sm text-red-500 mb-4">{{ fetchError }}</p>
          <button @click="fetchProfile" class="px-5 py-2 bg-[#8B4513] text-white rounded-lg text-sm font-medium hover:bg-[#6b3410] transition-colors">Coba Lagi</button>
        </div>

        <template v-else>
          <div class="bg-white rounded-xl shadow-sm border border-stone-100 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-stone-100 flex items-center gap-2">
              <div class="w-1 h-4 bg-[#8B4513] rounded-full" />
              <h1 class="text-sm font-bold text-[#2C2C2C]">Edit Profil Komunitas</h1>
            </div>
            <div class="px-6 py-5 space-y-4">
              <div v-if="success" class="p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ success }}</div>
              <div v-if="errorMsg" class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-600">{{ errorMsg }}</div>

              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Nama Lembaga</label>
                <input v-model="form.nama_lembaga" type="text" disabled class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm text-[#2C2C2C] bg-stone-50 cursor-not-allowed focus:outline-none" />
                <p class="mt-1 text-[10px] text-gray-400">Perubahan nama lembaga harus diajukan ke superadmin</p>
              </div>

              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Deskripsi</label>
                <textarea v-model="form.deskripsi" rows="4" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm text-[#2C2C2C] focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-transparent resize-none"></textarea>
                <p v-if="errors.deskripsi" class="mt-1 text-xs text-red-500">{{ errors.deskripsi }}</p>
              </div>

              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Alamat Detail (Read-Only)</label>
                <input v-model="form.alamat" type="text" disabled class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm text-[#2C2C2C] bg-stone-50 cursor-not-allowed focus:outline-none" />
              </div>

              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Nomor Kontak</label>
                <input v-model="form.nomor_kontak" type="text" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm text-[#2C2C2C] focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-transparent" />
                <p v-if="errors.nomor_kontak" class="mt-1 text-xs text-red-500">{{ errors.nomor_kontak }}</p>
              </div>

              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Media Sosial (opsional)</label>
                <input v-model="form.website" type="text" placeholder="https://" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm text-[#2C2C2C] focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-transparent" />
              </div>

              <div class="flex items-center gap-3 pt-2">
                <button @click="handleSubmit" :disabled="submitting" class="px-5 py-2 bg-[#8B4513] text-white rounded-lg text-sm font-medium hover:bg-[#6b3410] transition-colors disabled:opacity-50">
                  {{ submitting ? 'Menyimpan...' : 'Simpan Perubahan' }}
                </button>
                <router-link to="/community/dashboard" class="px-5 py-2 text-sm text-gray-500 hover:text-gray-700 transition-colors">Batal</router-link>
              </div>
            </div>
          </div>
        </template>
      </div>
    </main>

    <AppFooter />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api/axios'
import Navbar from '@/components/shared/Navbar.vue'
import AppFooter from '@/components/shared/AppFooter.vue'

const loading = ref(true)
const fetchError = ref('')
const success = ref('')
const errorMsg = ref('')
const submitting = ref(false)

const form = ref({
  nama_lembaga: '',
  deskripsi: '',
  alamat: '',
  nomor_kontak: '',
  website: '',
})

const errors = ref({})

async function fetchProfile() {
  loading.value = true
  fetchError.value = ''
  try {
    const res = await api.get('/communities/profile')
    const data = res.data.data || res.data
    form.value = {
      nama_lembaga: data.nama_lembaga || '',
      deskripsi: data.deskripsi || '',
      alamat: data.alamat_detail || '',
      nomor_kontak: data.nomor_kontak || '',
      website: data.link_medsos || '',
    }
  } catch (e) {
    fetchError.value = e.response?.data?.message || 'Gagal memuat profil komunitas'
  } finally {
    loading.value = false
  }
}

async function handleSubmit() {
  errors.value = {}
  success.value = ''
  errorMsg.value = ''
  submitting.value = true
  try {
    await api.patch('/communities/profile', {
      deskripsi: form.value.deskripsi,
      nomor_kontak: form.value.nomor_kontak,
      link_medsos: form.value.website,
    })
    success.value = 'Profil komunitas berhasil diperbarui'
  } catch (e) {
    const status = e.response?.status
    const errData = e.response?.data?.errors || {}
    const message = e.response?.data?.message || ''
    if (status === 400 || status === 422) {
      errors.value = Object.fromEntries(
        Object.entries(errData).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v])
      )
      if (Object.keys(errors.value).length === 0) {
        errorMsg.value = message || 'Data tidak valid'
      }
    } else {
      errorMsg.value = message || 'Gagal memperbarui profil'
    }
  } finally {
    submitting.value = false
  }
}

onMounted(fetchProfile)
</script>
