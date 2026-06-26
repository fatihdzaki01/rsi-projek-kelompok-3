<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/api/axios'
import PaginationBar from '@/components/ui/PaginationBar.vue'
import AdminLayout from '@/components/admin/AdminLayout.vue'

const activeTab = ref('review')
const items = ref([])
const pagination = ref(null)
const loading = ref(true)
const errorMessage = ref('')
const currentPage = ref(1)
const perPage = ref(8)
const successMessage = ref('')

const showRejectModal = ref(false)
const rejectId = ref(null)
const rejectReason = ref('')
const rejectLoading = ref(false)
const approveLoading = ref({})

const showDetailModal = ref(false)
const detailItem = ref(null)

const fetchDisbursements = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const endpoint =
      activeTab.value === 'review'
        ? '/superadmin/disbursements'
        : '/superadmin/disbursements/history'

    const response = await api.get(endpoint, {
      params: {
        per_page: perPage.value,
        page: currentPage.value,
      },
    })

    items.value = response.data.data.data
    pagination.value = response.data.data.meta || response.data.data
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || 'Gagal memuat data pencairan.'
  } finally {
    loading.value = false
  }
}

const changeTab = (tab) => {
  activeTab.value = tab
  currentPage.value = 1
  fetchDisbursements()
}

const loadPage = (page) => {
  currentPage.value = page
  fetchDisbursements()
}

const changePerPage = (pp) => {
  perPage.value = pp
  currentPage.value = 1
  fetchDisbursements()
}

const openReject = (id) => {
  rejectId.value = id
  rejectReason.value = ''
  showRejectModal.value = true
}

const handleApprove = async (id) => {
  approveLoading.value = { ...approveLoading.value, [id]: true }
  successMessage.value = ''
  try {
    await api.post(`/superadmin/disbursements/${id}/approve`)
    successMessage.value = 'Pencairan disetujui.'
    fetchDisbursements()
  } catch (e) {
    errorMessage.value = e.response?.data?.message || 'Gagal menyetujui.'
  } finally {
    approveLoading.value = { ...approveLoading.value, [id]: false }
  }
}

const handleReject = async () => {
  if (!rejectReason.value.trim()) return
  rejectLoading.value = true
  try {
    await api.post(`/superadmin/disbursements/${rejectId.value}/reject`, {
      alasan_penolakan: rejectReason.value,
    })
    successMessage.value = 'Pencairan ditolak.'
    showRejectModal.value = false
    fetchDisbursements()
  } catch (e) {
    errorMessage.value = e.response?.data?.message || 'Gagal menolak.'
  } finally {
    rejectLoading.value = false
  }
}

async function showDetail(id) {
  showDetailModal.value = true
  detailItem.value = null
  try {
    const res = await api.get(`/superadmin/disbursements/${id}`)
    detailItem.value = res.data.data || res.data
  } catch (e) {
    detailItem.value = {}
  }
}

onMounted(fetchDisbursements)
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-xl font-bold text-gray-800">Pencairan Dana Superadmin</h1>
          <p class="text-sm text-gray-500">Review pengajuan pencairan dan riwayat pencairan dana.</p>
        </div>
      </div>

      <section v-if="successMessage" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
        {{ successMessage }}
      </section>

      <div class="flex space-x-1 bg-stone-100 p-1 rounded-lg w-fit">
        <button
          @click="changeTab('review')"
          :class="['px-4 py-2 text-sm font-medium rounded-md transition-colors', activeTab === 'review' ? 'bg-white shadow-sm text-gray-800' : 'text-gray-500 hover:text-gray-700']"
        >
          Menunggu Review
        </button>
        <button
          @click="changeTab('history')"
          :class="['px-4 py-2 text-sm font-medium rounded-md transition-colors', activeTab === 'history' ? 'bg-white shadow-sm text-gray-800' : 'text-gray-500 hover:text-gray-700']"
        >
          Riwayat Pencairan
        </button>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-stone-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-stone-100 flex items-center justify-between">
          <div>
            <h2 class="text-sm font-semibold text-gray-800">
              {{ activeTab === 'review' ? 'Pengajuan Pencairan' : 'Riwayat Pencairan' }}
            </h2>
            <p class="text-xs text-gray-400">
              {{ activeTab === 'review'
                ? 'Daftar pengajuan dana yang perlu direview superadmin.'
                : 'Daftar riwayat keputusan pencairan dana.'
              }}
            </p>
          </div>
        </div>

        <div v-if="loading" class="flex items-center justify-center py-12">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#8B4513]"></div>
          <span class="ml-3 text-gray-500">Memuat data...</span>
        </div>

        <div v-else-if="errorMessage" class="p-8 text-center text-red-500 text-sm">
          {{ errorMessage }}
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-stone-50 text-gray-500 text-xs uppercase">
              <tr>
                <th class="px-5 py-3 text-left font-medium">ID</th>
                <th class="px-5 py-3 text-left font-medium">Campaign</th>
                <th class="px-5 py-3 text-left font-medium">Komunitas</th>
                <th class="px-5 py-3 text-left font-medium">Nominal</th>
                <th class="px-5 py-3 text-left font-medium">Status</th>
                <th class="px-5 py-3 text-left font-medium">Tanggal</th>
                <th class="px-5 py-3 text-center font-medium">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
              <tr v-if="items.length === 0">
                <td colspan="7" class="p-8 text-center text-gray-400 text-sm">Belum ada data pencairan.</td>
              </tr>
              <tr v-for="item in items" :key="item.id_pencairan" class="hover:bg-stone-50 transition-colors">
                <td class="px-5 py-3 text-gray-500">#{{ item.id_pencairan }}</td>
                <td class="px-5 py-3 font-medium text-gray-800 max-w-[200px] truncate">
                  <button @click="showDetail(item.id_pencairan)" class="text-[#8B4513] hover:underline text-left">{{ item.judul_campaign }}</button>
                </td>
                <td class="px-5 py-3 text-gray-600">{{ item.nama_lembaga }}</td>
                <td class="px-5 py-3 text-gray-700">Rp{{ Number(item.nominal_diajukan || item.nominal_disetujui || 0).toLocaleString('id-ID') }}</td>
                <td class="px-5 py-3">
                  <span
                    :class="[
                      'inline-block px-2 py-0.5 rounded-full text-xs font-medium',
                      item.status === 'menunggu_review' ? 'bg-amber-100 text-amber-700' : '',
                      item.status === 'disetujui' ? 'bg-green-100 text-green-700' : '',
                      item.status === 'selesai' ? 'bg-blue-100 text-blue-700' : '',
                      item.status === 'ditolak' ? 'bg-red-100 text-red-700' : ''
                    ]"
                  >
                    {{ item.status.replace('_', ' ').toUpperCase() }}
                  </span>
                </td>
                <td class="px-5 py-3 text-gray-500 text-xs">
                  {{ new Date(item.tanggal_pengajuan || item.tanggal_keputusan).toLocaleDateString('id-ID') }}
                </td>
                <td class="px-5 py-3 text-center">
                  <template v-if="activeTab === 'review' && item.status === 'menunggu_review'">
                    <div class="flex justify-center gap-2">
                      <button
                        class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 text-xs disabled:opacity-50 transition-colors"
                        :disabled="approveLoading[item.id_pencairan]"
                        @click="handleApprove(item.id_pencairan)"
                      >
                        {{ approveLoading[item.id_pencairan] ? '...' : 'Setujui' }}
                      </button>
                      <button
                        class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-xs transition-colors"
                        @click="openReject(item.id_pencairan)"
                      >
                        Tolak
                      </button>
                    </div>
                  </template>
                  <span v-else class="text-xs text-gray-400">-</span>
                </td>
              </tr>
            </tbody>
          </table>

          <div v-if="pagination && pagination.last_page > 1" class="px-5 py-4 border-t border-stone-100">
            <PaginationBar :currentPage="currentPage" :totalPages="pagination.last_page" :perPage="perPage" :total="pagination.total" @update:currentPage="loadPage" @update:perPage="changePerPage" />
          </div>
        </div>
      </div>
    </div>

    <div v-if="showRejectModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" @click.self="showRejectModal = false">
      <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-xl">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Tolak Pencairan</h3>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Alasan Penolakan</label>
          <textarea
            v-model="rejectReason"
            rows="3"
            placeholder="Masukkan alasan penolakan..."
            class="w-full px-3 py-2 border border-stone-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#8B4513]/50 focus:border-[#8B4513] resize-none"
          ></textarea>
        </div>
        <div class="flex justify-end gap-3">
          <button class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800" @click="showRejectModal = false">Batal</button>
          <button
            class="px-4 py-2 text-sm font-medium bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 transition-colors"
            :disabled="!rejectReason.trim() || rejectLoading"
            @click="handleReject"
          >
            {{ rejectLoading ? 'Memproses...' : 'Tolak Pencairan' }}
          </button>
        </div>
      </div>
    </div>

    <div v-if="showDetailModal && detailItem" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" @click.self="showDetailModal = false">
      <div class="bg-white rounded-xl p-6 w-full max-w-lg shadow-xl max-h-[80vh] overflow-y-auto">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Detail Pengajuan Pencairan #{{ detailItem.id_pencairan }}</h3>
        <div class="space-y-3 text-sm">
          <div><span class="text-xs text-gray-400 block">Campaign</span><span class="text-gray-800 font-medium">{{ detailItem.judul_campaign || '-' }}</span></div>
          <div><span class="text-xs text-gray-400 block">Komunitas</span><span class="text-gray-800 font-medium">{{ detailItem.nama_lembaga || '-' }}</span></div>
          <div><span class="text-xs text-gray-400 block">Nominal Diajukan</span><span class="text-gray-800 font-medium">Rp{{ Number(detailItem.nominal_diajukan || 0).toLocaleString('id-ID') }}</span></div>
          <div v-if="detailItem.nominal_disetujui"><span class="text-xs text-gray-400 block">Nominal Disetujui</span><span class="text-gray-800 font-medium">Rp{{ Number(detailItem.nominal_disetujui || 0).toLocaleString('id-ID') }}</span></div>
          <div><span class="text-xs text-gray-400 block">Status</span>
            <span :class="['inline px-2 py-0.5 rounded-full text-xs font-medium',
              detailItem.status === 'menunggu_review' ? 'bg-amber-100 text-amber-700' :
              detailItem.status === 'disetujui' ? 'bg-green-100 text-green-700' :
              detailItem.status === 'ditolak' ? 'bg-red-100 text-red-700' :
              'bg-blue-100 text-blue-700'
            ]">{{ detailItem.status }}</span>
          </div>
          <div v-if="detailItem.keterangan"><span class="text-xs text-gray-400 block">Keterangan</span><span class="text-gray-800 leading-relaxed">{{ detailItem.keterangan }}</span></div>
          <div v-if="detailItem.url_proposal"><span class="text-xs text-gray-400 block">Proposal</span><a :href="detailItem.url_proposal" target="_blank" class="text-[#8B4513] underline break-all">{{ detailItem.url_proposal }}</a></div>
          <div><span class="text-xs text-gray-400 block">Bank Tujuan</span><span class="text-gray-800 font-medium">{{ detailItem.nama_bank_tujuan }} - {{ detailItem.nomor_rekening_tujuan }}</span></div>
          <div v-if="detailItem.bukti_transfer_url"><span class="text-xs text-gray-400 block">Bukti Transfer</span><a :href="detailItem.bukti_transfer_url" target="_blank" class="text-[#8B4513] underline break-all">{{ detailItem.bukti_transfer_url }}</a></div>
          <div v-if="detailItem.alasan_penolakan"><span class="text-xs text-gray-400 block">Alasan Penolakan</span><span class="text-red-700">{{ detailItem.alasan_penolakan }}</span></div>
          <div v-if="detailItem.deskripsi_penggunaan" class="pt-3 border-t border-stone-100">
            <p class="text-xs text-gray-400 font-medium mb-2">Laporan Penggunaan Dana</p>
            <div><span class="text-xs text-gray-400 block">Deskripsi</span><span class="text-gray-800 leading-relaxed">{{ detailItem.deskripsi_penggunaan }}</span></div>
            <div><span class="text-xs text-gray-400 block">Total Realisasi</span><span class="text-gray-800 font-medium">Rp{{ Number(detailItem.total_realisasi || 0).toLocaleString('id-ID') }}</span></div>
            <div v-if="detailItem.file_dokumentasi_url"><span class="text-xs text-gray-400 block">Dokumentasi</span><a :href="detailItem.file_dokumentasi_url" target="_blank" class="text-[#8B4513] underline break-all">{{ detailItem.file_dokumentasi_url }}</a></div>
          </div>
        </div>
        <div class="flex justify-end gap-3 mt-5 pt-4 border-t border-stone-100">
          <button @click="showDetailModal = false" class="px-4 py-2 text-sm bg-[#8B4513] text-white rounded-lg hover:bg-[#6b3410]">Tutup</button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<style scoped>
.animate-spin {
  animation: spin 1s linear infinite;
}
@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
