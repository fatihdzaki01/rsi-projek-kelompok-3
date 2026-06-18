<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/api/axios'
import AdminLayout from '@/components/admin/AdminLayout.vue'

const campaigns = ref([])
const pagination = ref(null)
const loading = ref(true)
const errorMessage = ref('')
const successMessage = ref('')

const selectedStatus = ref('menunggu_review')
const currentPage = ref(1)

const actionLoading = ref({})
const rejectModal = ref({ show: false, id: null, reason: '' })

const statuses = [
  { label: 'Pending', value: 'menunggu_review' },
  { label: 'Disetujui', value: 'aktif' },
  { label: 'Ditolak', value: 'ditolak' },
  { label: 'Semua', value: 'semua' },
]

const fetchCampaigns = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const params = { per_page: 10, page: currentPage.value }
    if (selectedStatus.value !== 'semua') params.status = selectedStatus.value
    else params.status = ''

    const response = await api.get('/superadmin/campaigns/review', { params })
    campaigns.value = response.data.data.data
    pagination.value = response.data.data
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Gagal memuat daftar campaign.'
  } finally {
    loading.value = false
  }
}

const changeStatus = (status) => {
  selectedStatus.value = status
  currentPage.value = 1
  fetchCampaigns()
}

const nextPage = () => {
  if (pagination.value?.next_page_url) { currentPage.value += 1; fetchCampaigns() }
}
const prevPage = () => {
  if (pagination.value?.prev_page_url) { currentPage.value -= 1; fetchCampaigns() }
}

const handleApprove = async (id) => {
  actionLoading.value = { ...actionLoading.value, [id]: true }
  successMessage.value = ''
  try {
    await api.post(`/superadmin/campaigns/${id}/approve`)
    successMessage.value = 'Campaign #' + id + ' disetujui.'
    fetchCampaigns()
  } catch (e) {
    errorMessage.value = e.response?.data?.message || 'Gagal menyetujui.'
  } finally {
    actionLoading.value = { ...actionLoading.value, [id]: false }
  }
}

const openReject = (id) => {
  rejectModal.value = { show: true, id, reason: '' }
}

const handleReject = async () => {
  if (!rejectModal.value.reason.trim()) return
  const id = rejectModal.value.id
  actionLoading.value = { ...actionLoading.value, [id]: true }
  try {
    await api.post(`/superadmin/campaigns/${id}/reject`, { alasan_penolakan: rejectModal.value.reason })
    successMessage.value = 'Campaign #' + id + ' ditolak.'
    rejectModal.value.show = false
    fetchCampaigns()
  } catch (e) {
    errorMessage.value = e.response?.data?.message || 'Gagal menolak.'
  } finally {
    actionLoading.value = { ...actionLoading.value, [id]: false }
  }
}

const formatRupiah = (n) => 'Rp ' + Number(n || 0).toLocaleString('id-ID')
const formatDate = (s) => s ? new Date(s).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-'

onMounted(fetchCampaigns)
</script>

<template>
  <AdminLayout>
    <div class="space-y-4">
      <div>
        <h1 class="text-xl font-bold text-gray-800">Approval Campaign</h1>
        <p class="text-sm text-gray-500">Review dan kelola permintaan campaign dari komunitas.</p>
      </div>

      <div v-if="successMessage" class="bg-green-50 border border-green-200 text-green-700 px-4 py-2 rounded-lg text-sm">{{ successMessage }}</div>

      <div class="flex gap-2 flex-wrap">
        <button
          v-for="item in statuses" :key="item.value"
          @click="changeStatus(item.value)"
          class="px-4 py-1.5 rounded-full text-xs font-medium border transition-colors"
          :class="selectedStatus === item.value
            ? 'bg-[#8B4513] text-white border-[#8B4513]'
            : 'bg-white text-gray-600 border-gray-200 hover:border-[#8B4513]'"
        >{{ item.label }}</button>
      </div>

      <div v-if="loading" class="bg-white rounded-xl p-8 text-center text-sm text-gray-400">Memuat data...</div>
      <div v-else-if="errorMessage" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">{{ errorMessage }}</div>

      <template v-else>
        <div class="bg-white rounded-xl shadow-sm border border-stone-100 overflow-hidden">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-stone-100 bg-stone-50 text-left">
                <th class="px-4 py-2.5 text-xs font-semibold text-gray-500">ID</th>
                <th class="px-4 py-2.5 text-xs font-semibold text-gray-500">Judul</th>
                <th class="px-4 py-2.5 text-xs font-semibold text-gray-500">Komunitas</th>
                <th class="px-4 py-2.5 text-xs font-semibold text-gray-500">Kategori</th>
                <th class="px-4 py-2.5 text-xs font-semibold text-gray-500">Target</th>
                <th class="px-4 py-2.5 text-xs font-semibold text-gray-500">Status</th>
                <th class="px-4 py-2.5 text-xs font-semibold text-gray-500">Tanggal</th>
                <th class="px-4 py-2.5 text-xs font-semibold text-gray-500">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="campaigns.length === 0">
                <td colspan="8" class="px-4 py-8 text-center text-gray-400">Tidak ada campaign pada status ini.</td>
              </tr>
              <tr v-for="c in campaigns" :key="c.id_campaign" class="border-b border-stone-50 hover:bg-stone-50/50">
                <td class="px-4 py-3 text-gray-500">#{{ c.id_campaign }}</td>
                <td class="px-4 py-3">
                  <p class="font-medium text-gray-800 line-clamp-1 max-w-[200px]">{{ c.judul }}</p>
                </td>
                <td class="px-4 py-3 text-gray-600 text-xs max-w-[150px] truncate">{{ c.nama_lembaga }}</td>
                <td class="px-4 py-3 text-gray-500 text-xs">{{ c.nama_kategori }}</td>
                <td class="px-4 py-3 text-gray-700">{{ formatRupiah(c.target_dana) }}</td>
                <td class="px-4 py-3">
                  <span :class="[
                    'px-2 py-0.5 rounded-full text-xs font-medium',
                    c.status === 'menunggu_review' ? 'bg-amber-100 text-amber-700' :
                    c.status === 'aktif' ? 'bg-green-100 text-green-700' :
                    c.status === 'ditolak' ? 'bg-red-100 text-red-700' :
                    'bg-gray-100 text-gray-600'
                  ]">{{ c.status === 'menunggu_review' ? 'pending' : c.status }}</span>
                </td>
                <td class="px-4 py-3 text-xs text-gray-400">{{ formatDate(c.created_at) }}</td>
                <td class="px-4 py-3">
                  <div class="flex gap-1.5">
                    <RouterLink
                      :to="`/campaigns/${c.id_campaign}/review`"
                      class="px-2.5 py-1 text-xs rounded-md bg-gray-100 text-gray-600 hover:bg-gray-200"
                    >Detail</RouterLink>
                    <template v-if="c.status === 'menunggu_review'">
                      <button
                        class="px-2.5 py-1 text-xs rounded-md bg-green-600 text-white hover:bg-green-700 disabled:opacity-50"
                        :disabled="actionLoading[c.id_campaign]"
                        @click="handleApprove(c.id_campaign)"
                      >{{ actionLoading[c.id_campaign] ? '...' : 'Setujui' }}</button>
                      <button
                        class="px-2.5 py-1 text-xs rounded-md bg-red-600 text-white hover:bg-red-700"
                        @click="openReject(c.id_campaign)"
                      >Tolak</button>
                    </template>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="pagination" class="flex items-center justify-between text-sm">
          <button :disabled="!pagination.prev_page_url" @click="prevPage" class="px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 disabled:opacity-40 hover:bg-gray-50">← Sebelumnya</button>
          <span class="text-gray-400 text-xs">Halaman {{ pagination.current_page }} dari {{ pagination.last_page }}</span>
          <button :disabled="!pagination.next_page_url" @click="nextPage" class="px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 disabled:opacity-40 hover:bg-gray-50">Selanjutnya →</button>
        </div>
      </template>
    </div>

    <!-- Reject Modal -->
    <div v-if="rejectModal.show" class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center" @click.self="rejectModal.show = false">
      <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-lg">
        <h3 class="text-sm font-bold text-gray-800 mb-3">Tolak Campaign</h3>
        <textarea v-model="rejectModal.reason" rows="3" placeholder="Alasan penolakan..." class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm resize-none focus:outline-none focus:ring-2 focus:ring-red-400"></textarea>
        <div class="flex justify-end gap-2 mt-3">
          <button @click="rejectModal.show = false" class="px-4 py-1.5 text-sm text-gray-600 hover:text-gray-800">Batal</button>
          <button @click="handleReject" :disabled="!rejectModal.reason.trim() || actionLoading[rejectModal.id]" class="px-4 py-1.5 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50">
            {{ actionLoading[rejectModal.id] ? '...' : 'Tolak' }}
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
