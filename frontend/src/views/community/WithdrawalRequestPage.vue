<template>
  <div class="min-h-screen bg-[#F5F0E8] flex flex-col">
    <Navbar />

    <main class="flex-1 py-8">
      <div class="max-w-3xl mx-auto px-6">

        <nav class="text-sm text-gray-500 mb-4 flex items-center gap-1">
          <router-link to="/" class="hover:text-[#8B4513] transition-colors">Beranda</router-link>
          <span>/</span>
          <router-link to="/communities/dashboard" class="hover:text-[#8B4513] transition-colors">Dashboard Komunitas</router-link>
          <span>/</span>
          <span class="text-[#2C2C2C] font-medium">Rekening & Penarikan</span>
        </nav>

        <div v-if="loading" class="flex flex-col items-center justify-center py-20">
          <div class="w-8 h-8 border-2 border-[#8B4513] border-t-transparent rounded-full animate-spin mb-3" />
          <p class="text-sm text-gray-400">Memuat data rekening...</p>
        </div>

        <template v-else>
          <div class="bg-white rounded-xl shadow-sm border border-stone-100 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-stone-100 flex items-center gap-2">
              <div class="w-1 h-4 bg-[#8B4513] rounded-full" />
              <h1 class="text-sm font-bold text-[#2C2C2C]">Rekening Aktif</h1>
            </div>
            <div class="px-6 py-5">
              <div v-if="activeAccount" class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-[#2C2C2C]">{{ activeAccount.nama_bank }}</p>
                  <p class="text-sm text-gray-500">{{ activeAccount.nomor_rekening }}</p>
                  <a v-if="activeAccount.foto_buku_rekening_url" :href="activeAccount.foto_buku_rekening_url" target="_blank" class="text-xs text-[#8B4513] hover:underline mt-1 inline-block">Lihat Foto Buku Rekening</a>
                </div>
                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700">Aktif</span>
              </div>
              <div v-else class="text-sm text-gray-400">
                Belum ada rekening terdaftar.
              </div>
            </div>
          </div>

          <div class="bg-white rounded-xl shadow-sm border border-stone-100 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-stone-100">
              <h2 class="text-sm font-bold text-[#2C2C2C]">Ajukan Perubahan Rekening</h2>
            </div>
            <div class="px-6 py-5 space-y-4">
              <div v-if="changeSuccess" class="p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ changeSuccess }}</div>
              <div v-if="changeError" class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-600">{{ changeError }}</div>

              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Nama Bank Baru <span class="text-red-400">*</span></label>
                <input v-model="changeForm.nama_bank_baru" type="text" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm text-[#2C2C2C] focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-transparent" />
                <p v-if="changeErrors.nama_bank_baru" class="mt-1 text-xs text-red-500">{{ changeErrors.nama_bank_baru }}</p>
              </div>

              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Nomor Rekening Baru <span class="text-red-400">*</span></label>
                <input v-model="changeForm.nomor_rekening_baru" type="text" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm text-[#2C2C2C] focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-transparent" />
                <p v-if="changeErrors.nomor_rekening_baru" class="mt-1 text-xs text-red-500">{{ changeErrors.nomor_rekening_baru }}</p>
              </div>

              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Foto Buku Rekening (URL) <span class="text-red-400">*</span></label>
                <input v-model="changeForm.foto_buku_rekening_url" type="url" placeholder="https://" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm text-[#2C2C2C] focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-transparent" />
                <p v-if="changeErrors.foto_buku_rekening_url" class="mt-1 text-xs text-red-500">{{ changeErrors.foto_buku_rekening_url }}</p>
              </div>

              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Alasan Perubahan <span class="text-red-400">*</span></label>
                <textarea v-model="changeForm.alasan_perubahan" rows="3" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm text-[#2C2C2C] focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-transparent resize-none"></textarea>
                <p v-if="changeErrors.alasan_perubahan" class="mt-1 text-xs text-red-500">{{ changeErrors.alasan_perubahan }}</p>
              </div>

              <button @click="handleChangeRequest" :disabled="changeSubmitting" class="px-5 py-2 bg-[#8B4513] text-white rounded-lg text-sm font-medium hover:bg-[#6b3410] transition-colors disabled:opacity-50">
                {{ changeSubmitting ? 'Mengirim...' : 'Ajukan Perubahan' }}
              </button>
            </div>
          </div>

          <div class="bg-white rounded-xl shadow-sm border border-stone-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-stone-100">
              <h2 class="text-sm font-bold text-[#2C2C2C]">Riwayat Perubahan Rekening</h2>
            </div>
            <div v-if="history.length === 0" class="px-6 py-10 text-center text-sm text-gray-400">
              Belum ada riwayat perubahan rekening.
            </div>
            <div v-else>
              <div v-for="(item, i) in history" :key="item.id_verif" :class="['px-6 py-4', i < history.length - 1 ? 'border-b border-stone-100' : '']">
                <div class="flex items-center justify-between mb-1">
                  <p class="text-sm font-medium text-[#2C2C2C]">{{ item.nama_bank_baru }}</p>
                  <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', statusBadge(item.status)]">{{ statusLabel(item.status) }}</span>
                </div>
                <p class="text-xs text-gray-400">{{ item.nomor_rekening_baru }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ item.created_at ? new Date(item.created_at).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' }) : '' }}</p>
                <p v-if="item.alasan_penolakan" class="text-xs text-red-500 mt-1">Alasan ditolak: {{ item.alasan_penolakan }}</p>
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
const activeAccount = ref(null)
const history = ref([])

const changeForm = ref({
  nama_bank_baru: '',
  nomor_rekening_baru: '',
  foto_buku_rekening_url: '',
  alasan_perubahan: '',
})
const changeErrors = ref({})
const changeSuccess = ref('')
const changeError = ref('')
const changeSubmitting = ref(false)

function statusBadge(status) {
  const map = { menunggu: 'text-yellow-700 bg-yellow-50', disetujui: 'text-green-700 bg-green-50', ditolak: 'text-red-700 bg-red-50' }
  return map[status] || 'text-gray-500 bg-gray-100'
}

function statusLabel(status) {
  const map = { menunggu: 'Menunggu', disetujui: 'Disetujui', ditolak: 'Ditolak' }
  return map[status] || status
}

async function fetchData() {
  loading.value = true
  try {
    const res = await api.get('/communities/bank-account/history')
    const data = res.data.data || res.data
    activeAccount.value = data.rekening_aktif || null
    history.value = data.riwayat_perubahan || []
  } catch (e) {
    // silent
  } finally {
    loading.value = false
  }
}

async function handleChangeRequest() {
  changeErrors.value = {}
  changeSuccess.value = ''
  changeError.value = ''
  changeSubmitting.value = true

  try {
    await api.post('/communities/bank-account/change', {
      nama_bank_baru: changeForm.value.nama_bank_baru,
      nomor_rekening_baru: changeForm.value.nomor_rekening_baru,
      foto_buku_rekening_url: changeForm.value.foto_buku_rekening_url,
      alasan_perubahan: changeForm.value.alasan_perubahan,
    })
    changeSuccess.value = 'Permintaan perubahan rekening berhasil diajukan'
    changeForm.value = { nama_bank_baru: '', nomor_rekening_baru: '', foto_buku_rekening_url: '', alasan_perubahan: '' }
    fetchData()
  } catch (e) {
    const status = e.response?.status
    const errData = e.response?.data?.errors || {}
    const message = e.response?.data?.message || ''
    if (status === 400 || status === 422) {
      changeErrors.value = Object.fromEntries(
        Object.entries(errData).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v])
      )
      if (Object.keys(changeErrors.value).length === 0) {
        changeError.value = message || 'Data tidak valid'
      }
    } else if (status === 409) {
      changeError.value = message || 'Sudah ada permintaan perubahan yang menunggu review'
    } else {
      changeError.value = message || 'Gagal mengajukan perubahan rekening'
    }
  } finally {
    changeSubmitting.value = false
  }
}

onMounted(fetchData)
</script>
