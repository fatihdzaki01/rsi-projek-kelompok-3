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
            <button @click="showDetail(r)" class="px-4 py-1.5 text-xs font-medium text-[#8B4513] border border-[#8B4513]/20 rounded-lg hover:bg-orange-50 transition-colors">Detail</button>
            <button @click="showRejectModal(r)" class="px-4 py-1.5 text-xs font-medium text-red-600 border border-red-200 rounded-lg hover:bg-red-50 transition-colors">Tolak</button>
            <button @click="handleApprove(r)" class="px-4 py-1.5 text-xs font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">Setujui</button>
          </div>
        </div>

        <div v-if="pagination.last_page > 1" class="flex justify-center mt-4">
          <PaginationBar :currentPage="regCurrentPage" :totalPages="pagination.last_page" :perPage="regPerPage" :total="pagination.total" @update:currentPage="loadPage" @update:perPage="changeRegPerPage" />
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
          <div v-if="historyPagination.last_page > 1" class="px-5 py-3 border-t border-stone-100">
            <PaginationBar :currentPage="historyCurrentPage" :totalPages="historyPagination.last_page" :perPage="historyPerPage" :total="historyPagination.total" @update:currentPage="loadHistoryPage" @update:perPage="changeHistoryPerPage" />
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

    <div v-if="showDetailModal && detailData" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" @click.self="showDetailModal = false">
      <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-lg mx-4 max-h-[80vh] overflow-y-auto">
        <h3 class="text-sm font-bold text-[#2C2C2C] mb-4">Detail Pendaftaran - {{ detailData.registration?.nama_lembaga }}</h3>
        <div class="space-y-3 text-sm">
          <div><span class="text-xs text-gray-400 block">Nama Lembaga</span><span class="text-gray-800 font-medium">{{ detailData.registration?.nama_lembaga || '-' }}</span></div>
          <div><span class="text-xs text-gray-400 block">Nama Pengurus</span><span class="text-gray-800 font-medium">{{ detailData.registration?.nama_lengkap || detailData.registration?.nama_pengurus || '-' }}</span></div>
          <div><span class="text-xs text-gray-400 block">Email</span><span class="text-gray-800 font-medium">{{ detailData.registration?.email || '-' }}</span></div>
          <div><span class="text-xs text-gray-400 block">Username</span><span class="text-gray-800 font-medium">{{ detailData.registration?.username || '-' }}</span></div>
          <div><span class="text-xs text-gray-400 block">Jenis Lembaga</span><span class="text-gray-800 font-medium">{{ detailData.registration?.jenis_lembaga || '-' }}</span></div>
          <div><span class="text-xs text-gray-400 block">Wilayah</span><span class="text-gray-800 font-medium">{{ detailData.registration?.nama_wilayah || '-' }}</span></div>
          <div><span class="text-xs text-gray-400 block">Kontak</span><span class="text-gray-800 font-medium">{{ detailData.registration?.nomor_kontak || '-' }}</span></div>
          <div v-if="detailData.registration?.deskripsi"><span class="text-xs text-gray-400 block">Deskripsi</span><span class="text-gray-800 leading-relaxed">{{ detailData.registration.deskripsi }}</span></div>
          <div v-if="detailData.registration?.alamat_detail"><span class="text-xs text-gray-400 block">Alamat</span><span class="text-gray-800 leading-relaxed">{{ detailData.registration.alamat_detail }}</span></div>
          <div v-if="detailData.registration?.foto_lembaga_url"><span class="text-xs text-gray-400 block">Foto Lembaga</span><a :href="detailData.registration.foto_lembaga_url" target="_blank" class="text-[#8B4513] underline text-xs break-all">{{ detailData.registration.foto_lembaga_url }}</a></div>
          <div v-if="detailData.registration?.foto_buku_rekening_url"><span class="text-xs text-gray-400 block">Foto Buku Rekening</span><a :href="detailData.registration.foto_buku_rekening_url" target="_blank" class="text-[#8B4513] underline text-xs break-all">{{ detailData.registration.foto_buku_rekening_url }}</a></div>
          <div v-if="detailData.registration?.nama_bank"><span class="text-xs text-gray-400 block">Bank</span><span class="text-gray-800 font-medium">{{ detailData.registration.nama_bank }} - {{ detailData.registration.nomor_rekening }}</span></div>
          <div v-if="detailData.documents && detailData.documents.length > 0" class="pt-3 border-t border-stone-100">
            <p class="text-xs text-gray-400 font-medium mb-2">Dokumen Persyaratan</p>
            <div v-for="doc in detailData.documents" :key="doc.id_dok_kom" class="flex items-center justify-between py-1.5 border-b border-stone-50 last:border-0">
              <span class="text-xs text-gray-700">{{ doc.nama_dokumen || 'Dokumen' }}</span>
              <a v-if="doc.url_dokumen" :href="doc.url_dokumen" target="_blank" class="text-xs text-[#8B4513] underline">Lihat</a>
              <span v-else class="text-xs text-gray-400">-</span>
            </div>
          </div>
        </div>
        <div class="flex items-center justify-end gap-3 mt-5 pt-4 border-t border-stone-100">
          <button @click="showDetailModal = false" class="px-4 py-2 text-xs bg-[#8B4513] text-white rounded-lg hover:bg-[#6b3410]">Tutup</button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import api from '@/api/axios'
import PaginationBar from '@/components/ui/PaginationBar.vue'
import AdminLayout from '@/components/admin/AdminLayout.vue'

const activeTab = ref('pending')
const loading = ref(true)
const registrations = ref([])
const pagination = ref({ current_page: 1, last_page: 1, total: 0 })
const regCurrentPage = ref(1)
const regPerPage = ref(10)
const historyList = ref([])
const historyPagination = ref({ current_page: 1, last_page: 1, total: 0 })
const historyCurrentPage = ref(1)
const historyPerPage = ref(15)

const showModal = ref(false)
const rejectTarget = ref(null)
const rejectReason = ref('')
const rejectError = ref('')

const showDetailModal = ref(false)
const detailData = ref(null)

async function loadPage(page = 1) {
  regCurrentPage.value = page
  loading.value = true
  try {
    const res = await api.get('/superadmin/community-registrations', { params: { page, per_page: regPerPage.value } })
    const data = res.data.data || res.data
    registrations.value = data.data || data
    pagination.value = data.meta || { current_page: data.current_page || 1, last_page: data.last_page || 1, total: data.total || 0 }
  } catch (e) {
    registrations.value = []
  } finally {
    loading.value = false
  }
}

async function loadHistoryPage(page = 1) {
  historyCurrentPage.value = page
  loading.value = true
  try {
    const res = await api.get('/superadmin/community-registrations/history', { params: { page, per_page: historyPerPage.value } })
    const data = res.data.data || res.data
    historyList.value = data.data || data
    historyPagination.value = data.meta || { current_page: data.current_page || 1, last_page: data.last_page || 1, total: data.total || 0 }
  } catch (e) {
    historyList.value = []
  } finally {
    loading.value = false
  }
}

function changeRegPerPage(pp) {
  regPerPage.value = pp
  loadPage(1)
}

function changeHistoryPerPage(pp) {
  historyPerPage.value = pp
  loadHistoryPage(1)
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

async function showDetail(r) {
  showDetailModal.value = true
  detailData.value = null
  try {
    const res = await api.get(`/superadmin/community-registrations/${r.id_komunitas}`)
    detailData.value = res.data.data || res.data
  } catch (e) {
    detailData.value = { registration: r, documents: [] }
  }
}

watch(activeTab, (tab) => {
  if (tab === 'pending') loadPage()
  else loadHistoryPage()
})

onMounted(() => loadPage())
</script>
