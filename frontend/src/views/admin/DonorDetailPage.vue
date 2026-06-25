<template>
  <AdminLayout>
    <div class="max-w-4xl">
      <router-link to="/dashboard/donors" class="inline-flex items-center gap-1 text-xs text-gray-400 hover:text-gray-600 mb-4">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali ke Daftar Donatur
      </router-link>

      <div v-if="loading" class="bg-white rounded-xl shadow-sm p-8 text-center text-sm text-gray-400">Memuat data...</div>

      <template v-else-if="donor">
        <div class="bg-white rounded-xl shadow-sm border border-stone-100 overflow-hidden mb-6">
          <div class="px-6 py-5 flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-[#F5F0E8] flex items-center justify-center text-lg font-bold text-[#8B4513]">{{ (donor.nama_lengkap || '?').charAt(0) }}</div>
            <div>
              <h1 class="text-base font-bold text-[#2C2C2C]">{{ donor.nama_lengkap }}</h1>
              <p class="text-sm text-gray-400">{{ donor.email }}</p>
              <span :class="['inline-block mt-1 px-2 py-0.5 rounded-full text-xs font-medium', donor.is_active ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700']">{{ donor.is_active ? 'Aktif' : 'Nonaktif' }}</span>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-stone-100 overflow-hidden">
          <div class="px-6 py-4 border-b border-stone-100">
            <h2 class="text-sm font-bold text-[#2C2C2C]">Riwayat Donasi</h2>
          </div>
          <div v-if="history.length === 0" class="px-6 py-10 text-center text-sm text-gray-400">Belum ada riwayat donasi.</div>
          <table v-else class="w-full text-sm">
            <thead>
              <tr class="border-b border-stone-100 bg-stone-50">
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500">Campaign</th>
                <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500">Nominal</th>
                <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500">Status</th>
                <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500">Tanggal</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(h, i) in history" :key="h.id_donasi" :class="['hover:bg-stone-50', i < history.length - 1 ? 'border-b border-stone-100' : '']">
                <td class="px-5 py-3 text-[#2C2C2C]">{{ h.judul_campaign || '-' }}</td>
                <td class="px-5 py-3 text-right font-medium">{{ formatRupiah(h.nominal) }}</td>
                <td class="px-5 py-3 text-center">
                  <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', statusClass(h.status_pembayaran)]">{{ h.status_pembayaran }}</span>
                </td>
                <td class="px-5 py-3 text-right text-gray-500">{{ h.created_at ? new Date(h.created_at).toLocaleDateString('id-ID') : '-' }}</td>
              </tr>
            </tbody>
          </table>
          <div v-if="historyPagination.last_page > 1" class="px-5 py-3 border-t border-stone-100">
            <PaginationBar :currentPage="currentPage" :totalPages="historyPagination.last_page" :perPage="perPage" :total="historyPagination.total" @update:currentPage="loadPage" @update:perPage="changePerPage" />
          </div>
        </div>
      </template>

      <div v-else class="bg-white rounded-xl shadow-sm p-8 text-center text-sm text-red-500">Donatur tidak ditemukan</div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/api/axios'
import PaginationBar from '@/components/ui/PaginationBar.vue'
import AdminLayout from '@/components/admin/AdminLayout.vue'

const route = useRoute()
const loading = ref(true)
const donor = ref(null)
const history = ref([])
const historyPagination = ref({ current_page: 1, last_page: 1, total: 0 })
const currentPage = ref(1)
const perPage = ref(15)

function formatRupiah(val) { return 'Rp ' + (Number(val) || 0).toLocaleString('id-ID') }
function statusClass(s) {
  const map = { berhasil: 'text-green-700 bg-green-50', pending: 'text-yellow-700 bg-yellow-50', gagal: 'text-red-700 bg-red-50' }
  return map[s] || 'text-gray-500 bg-gray-100'
}

function changePerPage(pp) {
  perPage.value = pp
  loadPage(1)
}

async function loadPage(page = 1) {
  currentPage.value = page
  loading.value = true
  try {
    const res = await api.get(`/superadmin/donors/${route.params.id}`, { params: { page, per_page: perPage.value } })
    const data = res.data.data || res.data
    donor.value = data.donor || data
    if (data.donation_history) {
      history.value = data.donation_history.data || []
      historyPagination.value = { current_page: data.donation_history.current_page || 1, last_page: data.donation_history.last_page || 1, total: data.donation_history.total || 0 }
    }
  } catch (e) {
    donor.value = null
  } finally {
    loading.value = false
  }
}

onMounted(() => loadPage())
</script>
