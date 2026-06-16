<template>
  <AdminLayout>
    <div class="max-w-6xl">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-xl font-bold text-[#2C2C2C]">Manajemen Donatur</h1>
          <p class="text-xs text-gray-400 mt-0.5">Kelola akun donatur platform</p>
        </div>
        <div class="relative">
          <svg class="w-3.5 h-3.5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          <input v-model="search" @input="handleSearch" type="text" placeholder="Cari donatur..." class="pl-9 pr-4 py-2 bg-white rounded-lg border border-stone-200 text-xs focus:outline-none focus:ring-1 focus:ring-[#8B4513] w-56" />
        </div>
      </div>

      <div v-if="loading" class="bg-white rounded-xl shadow-sm p-8 text-center text-sm text-gray-400">Memuat data...</div>
      <div v-else-if="error" class="bg-white rounded-xl shadow-sm p-8 text-center text-sm text-red-500">{{ error }}</div>

      <div v-else class="bg-white rounded-xl shadow-sm border border-stone-100 overflow-hidden">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-stone-100 bg-stone-50">
              <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500">Nama</th>
              <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500">Email</th>
              <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500">Username</th>
              <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500">Total Donasi</th>
              <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500">Status</th>
              <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(d, i) in donors" :key="d.id_user" :class="['hover:bg-stone-50 transition-colors', i < donors.length - 1 ? 'border-b border-stone-100' : '']">
              <td class="px-5 py-3.5">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-[#F5F0E8] flex items-center justify-center text-xs font-bold text-[#8B4513]">{{ (d.nama_lengkap || '?').charAt(0) }}</div>
                  <span class="font-medium text-[#2C2C2C]">{{ d.nama_lengkap || '-' }}</span>
                </div>
              </td>
              <td class="px-5 py-3.5 text-gray-500">{{ d.email }}</td>
              <td class="px-5 py-3.5 text-gray-500">{{ d.username }}</td>
              <td class="px-5 py-3.5 text-right font-medium">{{ formatRupiah(d.total_nominal_donasi) }}</td>
              <td class="px-5 py-3.5 text-center">
                <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', d.is_active ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700']">{{ d.is_active ? 'Aktif' : 'Nonaktif' }}</span>
              </td>
              <td class="px-5 py-3.5 text-center">
                <div class="flex items-center justify-center gap-2">
                  <router-link :to="`/dashboard/donors/${d.id_user}`" class="text-xs text-[#8B4513] hover:underline">Detail</router-link>
                  <button @click="toggleStatus(d)" :class="['text-xs px-2 py-0.5 rounded transition-colors', d.is_active ? 'text-red-600 hover:bg-red-50' : 'text-green-600 hover:bg-green-50']">
                    {{ d.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="donors.length === 0">
              <td colspan="6" class="px-5 py-12 text-center text-sm text-gray-400">Tidak ada donatur ditemukan</td>
            </tr>
          </tbody>
        </table>
        <div v-if="pagination.last_page > 1" class="px-5 py-3 border-t border-stone-100 flex items-center justify-between">
          <span class="text-xs text-gray-400">Halaman {{ pagination.current_page }} dari {{ pagination.last_page }}</span>
          <div class="flex items-center gap-2">
            <button @click="loadPage(pagination.current_page - 1)" :disabled="pagination.current_page <= 1" class="px-3 py-1 text-xs border border-stone-200 rounded hover:bg-stone-50 disabled:opacity-30">Prev</button>
            <button @click="loadPage(pagination.current_page + 1)" :disabled="pagination.current_page >= pagination.last_page" class="px-3 py-1 text-xs border border-stone-200 rounded hover:bg-stone-50 disabled:opacity-30">Next</button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api/axios'
import AdminLayout from '@/components/admin/AdminLayout.vue'

const donors = ref([])
const pagination = ref({ current_page: 1, last_page: 1, total: 0 })
const search = ref('')
const loading = ref(true)
const error = ref('')

function formatRupiah(val) {
  return 'Rp ' + (Number(val) || 0).toLocaleString('id-ID')
}

let debounceTimer = null
function handleSearch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => loadPage(1), 400)
}

async function loadPage(page = 1) {
  loading.value = true
  error.value = ''
  try {
    const params = { page, per_page: 15 }
    if (search.value) params.search = search.value
    const res = await api.get('/superadmin/donors', { params })
    const data = res.data.data || res.data
    donors.value = data.data || data
    pagination.value = data.meta || data.pagination || { current_page: 1, last_page: 1, total: 0 }
  } catch (e) {
    error.value = e.response?.data?.message || 'Gagal memuat data donatur'
  } finally {
    loading.value = false
  }
}

async function toggleStatus(d) {
  try {
    await api.patch(`/superadmin/donors/${d.id_user}/status`, { is_active: !d.is_active })
    d.is_active = !d.is_active
  } catch (e) {
    alert(e.response?.data?.message || 'Gagal mengubah status')
  }
}

onMounted(() => loadPage())
</script>
