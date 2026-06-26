<template>
  <AdminLayout>
    <div class="max-w-6xl">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-xl font-bold text-[#2C2C2C]">Manajemen Komunitas</h1>
          <p class="text-xs text-gray-400 mt-0.5">Kelola akun komunitas platform</p>
        </div>
        <div class="relative">
          <svg class="w-3.5 h-3.5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          <input v-model="search" @input="handleSearch" type="text" placeholder="Cari komunitas..." class="pl-9 pr-4 py-2 bg-white rounded-lg border border-stone-200 text-xs focus:outline-none focus:ring-1 focus:ring-[#8B4513] w-56" />
        </div>
      </div>

      <div v-if="loading" class="bg-white rounded-xl shadow-sm p-8 text-center text-sm text-gray-400">Memuat data...</div>
      <div v-else-if="error" class="bg-white rounded-xl shadow-sm p-8 text-center text-sm text-red-500">{{ error }}</div>

      <div v-else class="bg-white rounded-xl shadow-sm border border-stone-100 overflow-hidden">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-stone-100 bg-stone-50">
              <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500">Nama Lembaga</th>
              <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500">Email</th>
              <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500">Campaign</th>
              <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500">Donasi Diterima</th>
              <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500">Status</th>
              <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(c, i) in communities" :key="c.id_komunitas" :class="['hover:bg-stone-50 transition-colors', i < communities.length - 1 ? 'border-b border-stone-100' : '']">
              <td class="px-5 py-3.5">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-[#F5F0E8] flex items-center justify-center text-xs font-bold text-[#8B4513]">{{ (c.nama_lembaga || '?').charAt(0) }}</div>
                  <span class="font-medium text-[#2C2C2C]">{{ c.nama_lembaga }}</span>
                </div>
              </td>
              <td class="px-5 py-3.5 text-gray-500">{{ c.email }}</td>
              <td class="px-5 py-3.5 text-right font-medium">{{ c.total_campaign || 0 }}</td>
              <td class="px-5 py-3.5 text-right font-medium">{{ formatRupiah(c.total_donasi_diterima) }}</td>
              <td class="px-5 py-3.5 text-center">
                <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', statusBadge(c.status)]">{{ statusLabel(c.status) }}</span>
              </td>
              <td class="px-5 py-3.5 text-center">
                <div class="flex items-center justify-center gap-2">
                  <router-link :to="`/dashboard/communities/${c.id_komunitas}`" class="text-xs text-[#8B4513] hover:underline">Detail</router-link>
                  <button @click="toggleStatus(c)" :class="['text-xs px-2 py-0.5 rounded transition-colors', c.is_active ? 'text-red-600 hover:bg-red-50' : 'text-green-600 hover:bg-green-50']">
                    {{ c.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="communities.length === 0">
              <td colspan="6" class="px-5 py-12 text-center text-sm text-gray-400">Tidak ada komunitas ditemukan</td>
            </tr>
          </tbody>
        </table>
        <div v-if="pagination.last_page > 1" class="px-5 py-3 border-t border-stone-100">
          <PaginationBar :currentPage="currentPage" :totalPages="pagination.last_page" :perPage="perPage" :total="pagination.total" @update:currentPage="loadPage" @update:perPage="changePerPage" />
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api/axios'
import PaginationBar from '@/components/ui/PaginationBar.vue'
import AdminLayout from '@/components/admin/AdminLayout.vue'

const communities = ref([])
const pagination = ref({ current_page: 1, last_page: 1, total: 0 })
const search = ref('')
const loading = ref(true)
const error = ref('')
const currentPage = ref(1)
const perPage = ref(15)

function formatRupiah(val) { return 'Rp ' + (Number(val) || 0).toLocaleString('id-ID') }
function statusBadge(s) {
  const map = { aktif: 'text-green-700 bg-green-50', menunggu: 'text-yellow-700 bg-yellow-50', ditolak: 'text-red-700 bg-red-50', dinonaktifkan: 'text-gray-500 bg-gray-100' }
  return map[s] || 'text-gray-500 bg-gray-100'
}
function statusLabel(s) {
  const map = { aktif: 'Aktif', menunggu: 'Menunggu', ditolak: 'Ditolak', dinonaktifkan: 'Nonaktif' }
  return map[s] || s
}

let debounceTimer = null
function handleSearch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => loadPage(1), 400)
}

async function loadPage(page = 1) {
  currentPage.value = page
  loading.value = true
  error.value = ''
  try {
    const params = { page, per_page: perPage.value }
    if (search.value) params.search = search.value
    const res = await api.get('/superadmin/communities', { params })
    const data = res.data.data || res.data
    communities.value = data.data || data
    pagination.value = data.meta || { current_page: data.current_page || 1, last_page: data.last_page || 1, total: data.total || 0 }
  } catch (e) {
    error.value = e.response?.data?.message || 'Gagal memuat data komunitas'
  } finally {
    loading.value = false
  }
}

function changePerPage(pp) {
  perPage.value = pp
  loadPage(1)
}

async function toggleStatus(c) {
  try {
    await api.patch(`/superadmin/communities/${c.id_komunitas}/status`, { is_active: !c.is_active })
    c.is_active = !c.is_active
    c.status = c.is_active ? 'aktif' : 'dinonaktifkan'
  } catch (e) {
    alert(e.response?.data?.message || 'Gagal mengubah status')
  }
}

onMounted(() => loadPage())
</script>
