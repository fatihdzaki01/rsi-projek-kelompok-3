<template>
  <AdminLayout>
    <div class="max-w-6xl">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-xl font-bold text-[#2C2C2C]">Registrasi Komunitas</h1>
          <p class="text-xs text-gray-400 mt-0.5">Tinjau dan setujui/tolak pendaftaran komunitas baru</p>
        </div>
        <div class="flex items-center gap-2">
          <button @click="activeTab = 'pending'" :class="['px-3 py-1.5 rounded-lg text-xs font-medium transition-colors', activeTab === 'pending' ? 'bg-[#8B4513] text-white' : 'bg-white border border-stone-200 text-gray-500']">Menunggu</button>
          <button @click="activeTab = 'history'" :class="['px-3 py-1.5 rounded-lg text-xs font-medium transition-colors', activeTab === 'history' ? 'bg-[#8B4513] text-white' : 'bg-white border border-stone-200 text-gray-500']">Riwayat</button>
        </div>
      </div>

      <div v-if="loading" class="bg-white rounded-xl shadow-sm p-8 text-center text-sm text-gray-400">Memuat data...</div>

      <template v-else-if="activeTab === 'pending'">
        <div v-if="registrations.length === 0" class="bg-white rounded-xl shadow-sm p-8 text-center text-sm text-gray-400">Tidak ada pendaftaran menunggu review.</div>

        <div v-for="r in registrations" :key="r.id_komunitas" class="bg-white rounded-xl shadow-sm border border-stone-100 overflow-hidden mb-4">
          <div class="px-6 py-4 flex items-start justify-between">
            <div class="flex items-start gap-4">
              <div class="w-10 h-10 rounded-full bg-[#F5F0E8] flex items-center justify-center text-sm font-bold text-[#8B4513]">{{ (r.nama_lembaga || '?').charAt(0) }}</div>
              <div>
                <h3 class="text-sm font-semibold text-[#2C2C2C]">{{ r.nama_lembaga }}</h3>
                <p class="text-xs text-gray-400 mt-0.5">{{ r.email }} | {{ r.nama_lengkap || '-' }}</p>
                <p class="text-xs text-gray-400">{{ r.jenis_lembaga || '-' }} | {{ r.nama_wilayah || '-' }}</p>
              </div>
            </div>
            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-50 text-yellow-700">Menunggu</span>
          </div>
          <div class="px-6 py-3 border-t border-stone-100 bg-stone-50 flex items-center gap-3 justify-end">
            <button @click="showRejectModal(r)" class="px-4 py-1.5 text-xs font-medium text-red-600 border border-red-200 rounded-lg hover:bg-red-50 transition-colors">Tolak</button>
            <button @click="handleApprove(r)" class="px-4 py-1.5 text-xs font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">Setujui</button>
          </div>
        </div>

        <div v-if="pagination.last_page > 1" class="flex items-center justify-center gap-2 mt-4">
          <button @click="loadPage(pagination.current_page - 1)" :disabled="pagination.current_page <= 1" class="px-3 py-1 text-xs border border-stone-200 rounded hover:bg-stone-50 disabled:opacity-30">Prev</button>
          <span class="text-xs text-gray-400">{{ pagination.current_page }}/{{ pagination.last_page }}</span>
          <button @click="loadPage(pagination.current_page + 1)" :disabled="pagination.current_page >= pagination.last_page" class="px-3 py-1 text-xs border border-stone-200 rounded hover:bg-stone-50 disabled:opacity-30">Next</button>
        </div>
      </template>

      <template v-else>
        <div class="bg-white rounded-xl shadow-sm border border-stone-100 overflow-hidden">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-stone-100 bg-stone-50">
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500">Nama Lembaga</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500">Email</th>
                <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500">Status</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500">Reviewer</th>
                <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500">Tanggal</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(r, i) in historyList" :key="r.id_komunitas" :class="['hover:bg-stone-50', i < historyList.length - 1 ? 'border-b border-stone-100' : '']">
                <td class="px-5 py-3.5 font-medium text-[#2C2C2C]">{{ r.nama_lembaga }}</td>
                <td class="px-5 py-3.5 text-gray-500">{{ r.email }}</td>
                <td class="px-5 py-3.5 text-center">
                  <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', r.status === 'aktif' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700']">{{ r.status === 'aktif' ? 'Disetujui' : 'Ditolak' }}</span>
                </td>
                <td class="px-5 py-3.5 text-gray-500">{{ r.reviewer || '-' }}</td>
                <td class="px-5 py-3.5 text-right text-gray-500">{{ r.updated_at ? new Date(r.updated_at).toLocaleDateString('id-ID') : '-' }}</td>
              </tr>
            </tbody>
          </table>
          <div v-if="historyPagination.last_page > 1" class="px-5 py-3 border-t border-stone-100 flex justify-between items-center">
            <span class="text-xs text-gray-400">Halaman {{ historyPagination.current_page }} dari {{ historyPagination.last_page }}</span>
            <div class="flex gap-2">
              <button @click="loadHistoryPage(historyPagination.current_page - 1)" :disabled="historyPagination.current_page <= 1" class="px-3 py-1 text-xs border border-stone-200 rounded hover:bg-stone-50 disabled:opacity-30">Prev</button>
              <button @click="loadHistoryPage(historyPagination.current_page + 1)" :disabled="historyPagination.current_page >= historyPagination.last_page" class="px-3 py-1 text-xs border border-stone-200 rounded hover:bg-stone-50 disabled:opacity-30">Next</button>
            </div>
          </div>
        </div>
      </template>
    </div>

    <div v-if="showModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" @click.self="showModal = false">
      <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md mx-4">
        <h3 class="text-sm font-bold text-[#2C2C2C] mb-4">Tolak Pendaftaran</h3>
        <p class="text-xs text-gray-500 mb-3">Alasan penolakan untuk <strong>{{ rejectTarget?.nama_lembaga }}</strong>:</p>
        <textarea v-model="rejectReason" rows="3" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-400 resize-none" placeholder="Masukkan alasan penolakan..."></textarea>
        <p v-if="rejectError" class="text-xs text-red-500 mt-1">{{ rejectError }}</p>
        <div class="flex items-center justify-end gap-3 mt-4">
          <button @click="showModal = false" class="px-4 py-2 text-xs text-gray-500 hover:text-gray-700">Batal</button>
          <button @click="handleReject" :disabled="!rejectReason.trim()" class="px-4 py-2 text-xs font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50">Konfirmasi Tolak</button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import api from '@/api/axios'
import AdminLayout from '@/components/admin/AdminLayout.vue'

const activeTab = ref('pending')
const loading = ref(true)
const registrations = ref([])
const pagination = ref({ current_page: 1, last_page: 1 })
const historyList = ref([])
const historyPagination = ref({ current_page: 1, last_page: 1 })

const showModal = ref(false)
const rejectTarget = ref(null)
const rejectReason = ref('')
const rejectError = ref('')

async function loadPage(page = 1) {
  loading.value = true
  try {
    const res = await api.get('/superadmin/community-registrations', { params: { page, per_page: 10 } })
    const data = res.data.data || res.data
    registrations.value = data.data || data
    pagination.value = data.meta || data.pagination || { current_page: 1, last_page: 1 }
  } catch (e) {
    registrations.value = []
  } finally {
    loading.value = false
  }
}

async function loadHistoryPage(page = 1) {
  loading.value = true
  try {
    const res = await api.get('/superadmin/community-registrations/history', { params: { page, per_page: 15 } })
    const data = res.data.data || res.data
    historyList.value = data.data || data
    historyPagination.value = data.meta || data.pagination || { current_page: 1, last_page: 1 }
  } catch (e) {
    historyList.value = []
  } finally {
    loading.value = false
  }
}

async function handleApprove(r) {
  if (!confirm(`Setujui pendaftaran ${r.nama_lembaga}?`)) return
  try {
    await api.post(`/superadmin/community-registrations/${r.id_komunitas}/approve`)
    registrations.value = registrations.value.filter(x => x.id_komunitas !== r.id_komunitas)
  } catch (e) {
    alert(e.response?.data?.message || 'Gagal menyetujui')
  }
}

function showRejectModal(r) {
  rejectTarget.value = r
  rejectReason.value = ''
  rejectError.value = ''
  showModal.value = true
}

async function handleReject() {
  if (!rejectReason.value.trim()) return
  rejectError.value = ''
  try {
    await api.post(`/superadmin/community-registrations/${rejectTarget.value.id_komunitas}/reject`, {
      alasan_penolakan: rejectReason.value,
    })
    registrations.value = registrations.value.filter(x => x.id_komunitas !== rejectTarget.value.id_komunitas)
    showModal.value = false
  } catch (e) {
    rejectError.value = e.response?.data?.message || 'Gagal menolak'
  }
}

watch(activeTab, (tab) => {
  if (tab === 'pending') loadPage()
  else loadHistoryPage()
})

onMounted(() => loadPage())
</script>
