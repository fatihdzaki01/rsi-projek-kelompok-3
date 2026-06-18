<template>
  <AdminLayout>
    <div class="max-w-6xl">
      <div class="mb-6">
        <h1 class="text-xl font-bold text-[#2C2C2C]">Audit Log</h1>
        <p class="text-xs text-gray-400 mt-0.5">Catatan aktivitas superadmin di platform</p>
      </div>

      <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
          <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Aksi</label>
            <select v-model="filter.action_type" @change="loadPage(1)" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs bg-white">
              <option value="">Semua Aksi</option>
              <option value="APPROVE">Approve</option>
              <option value="REJECT">Reject</option>
              <option value="DISABLE">Disable</option>
              <option value="IGNORE_REPORT">Ignore Report</option>
              <option value="REACTIVATE">Reactivate</option>
              <option value="CLOSE_PERMANENT">Close Permanent</option>
              <option value="APPROVE_DISBURSEMENT">Approve Disbursement</option>
              <option value="REJECT_DISBURSEMENT">Reject Disbursement</option>
              <option value="LOGIN">Login</option>
              <option value="CREATE">Create</option>
              <option value="UPDATE">Update</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Dari Tanggal</label>
            <input v-model="filter.start_date" type="date" @change="loadPage(1)" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs" />
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Sampai Tanggal</label>
            <input v-model="filter.end_date" type="date" @change="loadPage(1)" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs" />
          </div>
          <div class="flex items-end">
            <button @click="resetFilter" class="px-4 py-2 text-xs text-gray-500 border border-gray-200 rounded-lg hover:bg-stone-50">Reset</button>
          </div>
        </div>
      </div>

      <div v-if="loading" class="bg-white rounded-xl shadow-sm p-8 text-center text-sm text-gray-400">Memuat data...</div>

      <div v-else class="bg-white rounded-xl shadow-sm border border-stone-100 overflow-hidden">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-stone-100 bg-stone-50">
              <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500">Waktu</th>
              <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500">User</th>
              <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500">Aksi</th>
              <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500">Deskripsi</th>
              <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500">IP Address</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(log, i) in logs" :key="log.id_audit_log" :class="['hover:bg-stone-50', i < logs.length - 1 ? 'border-b border-stone-100' : '']">
              <td class="px-5 py-3.5 text-xs text-gray-500 whitespace-nowrap">{{ log.created_at ? new Date(log.created_at).toLocaleString('id-ID') : '-' }}</td>
              <td class="px-5 py-3.5 text-[#2C2C2C]">{{ log.user_name || '-' }}</td>
              <td class="px-5 py-3.5">
                <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', actionBadge(log.action_type)]">{{ log.action_type }}</span>
              </td>
              <td class="px-5 py-3.5 text-gray-500 max-w-md truncate">{{ log.description || '-' }}</td>
              <td class="px-5 py-3.5 text-xs text-gray-400">{{ log.ip_address || '-' }}</td>
            </tr>
            <tr v-if="logs.length === 0">
              <td colspan="5" class="px-5 py-12 text-center text-sm text-gray-400">Tidak ada log ditemukan</td>
            </tr>
          </tbody>
        </table>
        <div v-if="pagination.last_page > 1" class="px-5 py-3 border-t border-stone-100 flex items-center justify-between">
          <span class="text-xs text-gray-400">Halaman {{ pagination.current_page }} dari {{ pagination.last_page }}</span>
          <div class="flex gap-2">
            <button @click="loadPage(pagination.current_page - 1)" :disabled="pagination.current_page <= 1" class="px-3 py-1 text-xs border border-stone-200 rounded disabled:opacity-30">Prev</button>
            <button @click="loadPage(pagination.current_page + 1)" :disabled="pagination.current_page >= pagination.last_page" class="px-3 py-1 text-xs border border-stone-200 rounded disabled:opacity-30">Next</button>
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

const loading = ref(true)
const logs = ref([])
const pagination = ref({ current_page: 1, last_page: 1, total: 0 })
const filter = ref({ action_type: '', start_date: '', end_date: '' })

function actionBadge(a) {
  const map = {
    LOGIN: 'text-blue-700 bg-blue-50',
    APPROVE: 'text-green-700 bg-green-50',
    REJECT: 'text-red-700 bg-red-50',
    DISABLE: 'text-gray-700 bg-gray-100',
    IGNORE_REPORT: 'text-yellow-700 bg-yellow-50',
    REACTIVATE: 'text-green-700 bg-green-50',
    CLOSE_PERMANENT: 'text-red-700 bg-red-50',
    APPROVE_DISBURSEMENT: 'text-green-700 bg-green-50',
    REJECT_DISBURSEMENT: 'text-red-700 bg-red-50',
    CREATE: 'text-purple-700 bg-purple-50',
    UPDATE: 'text-yellow-700 bg-yellow-50',
  }
  return map[a] || 'text-gray-700 bg-gray-100'
}

function resetFilter() {
  filter.value = { action_type: '', start_date: '', end_date: '' }
  loadPage(1)
}

async function loadPage(page = 1) {
  loading.value = true
  try {
    const params = { page, per_page: 20 }
    if (filter.value.action_type) params.action_type = filter.value.action_type
    if (filter.value.start_date) params.start_date = filter.value.start_date
    if (filter.value.end_date) params.end_date = filter.value.end_date
    const res = await api.get('/superadmin/audit-logs', { params })
    const data = (res.data && res.data.data) || {}
    logs.value = data.data || []
    pagination.value = { current_page: data.current_page || 1, last_page: data.last_page || 1, total: data.total || 0 }
  } catch (e) {
    logs.value = []
  } finally {
    loading.value = false
  }
}

onMounted(() => loadPage())
</script>
