<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/api/axios'
import PaginationBar from '@/components/ui/PaginationBar.vue'
import AdminLayout from '@/components/admin/AdminLayout.vue'

const campaigns = ref([])
const pagination = ref(null)
const loading = ref(true)
const errorMessage = ref('')
const selectedStatus = ref('menunggu_review')
const currentPage = ref(1)
const perPage = ref(10)

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
    const params = { per_page: perPage.value, page: currentPage.value }
    if (selectedStatus.value !== 'semua') params.status = selectedStatus.value
    else params.status = ''
    const response = await api.get('/superadmin/campaigns/review', { params })
    campaigns.value = response.data.data.data
    pagination.value = response.data.data.meta || response.data.data
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Gagal memuat daftar campaign.'
  } finally {
    loading.value = false
  }
}

const changeStatus = (status) => { selectedStatus.value = status; currentPage.value = 1; fetchCampaigns() }
const loadPage = (page) => { currentPage.value = page; fetchCampaigns() }
const changePerPage = (pp) => { perPage.value = pp; currentPage.value = 1; fetchCampaigns() }
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

      <div class="flex gap-2 flex-wrap">
        <button v-for="item in statuses" :key="item.value" @click="changeStatus(item.value)"
          class="px-4 py-1.5 rounded-full text-xs font-medium border transition-colors"
          :class="selectedStatus === item.value ? 'bg-[#8B4513] text-white border-[#8B4513]' : 'bg-white text-gray-600 border-gray-200 hover:border-[#8B4513]'"
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
                <th class="px-4 py-2.5 text-xs font-semibold text-gray-500">Target Dana</th>
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
                <td class="px-4 py-3 text-gray-500 text-xs">#{{ c.id_campaign }}</td>
                <td class="px-4 py-3 text-gray-800 max-w-[200px] truncate font-medium">{{ c.judul }}</td>
                <td class="px-4 py-3 text-gray-600 text-xs max-w-[140px] truncate">{{ c.nama_lembaga }}</td>
                <td class="px-4 py-3 text-gray-500 text-xs">{{ c.nama_kategori }}</td>
                <td class="px-4 py-3 text-gray-700 text-xs">{{ formatRupiah(c.target_dana) }}</td>
                <td class="px-4 py-3">
                  <span :class="['px-2 py-0.5 rounded-full text-xs font-medium',
                    c.status === 'menunggu_review' ? 'bg-amber-100 text-amber-700' :
                    c.status === 'aktif' ? 'bg-green-100 text-green-700' :
                    c.status === 'ditolak' ? 'bg-red-100 text-red-700' :
                    'bg-gray-100 text-gray-600']"
                  >{{ c.status === 'menunggu_review' ? 'pending' : c.status }}</span>
                </td>
                <td class="px-4 py-3 text-xs text-gray-400">{{ formatDate(c.created_at) }}</td>
                <td class="px-4 py-3">
                  <RouterLink :to="`/campaigns/${c.id_campaign}/review`"
                    class="px-3 py-1 text-xs rounded-md bg-[#8B4513] text-white hover:bg-[#6b3410] inline-block"
                  >Review</RouterLink>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="pagination" class="flex mt-4">
          <PaginationBar :currentPage="currentPage" :totalPages="pagination.last_page" :perPage="perPage" :total="pagination.total" @update:currentPage="loadPage" @update:perPage="changePerPage" />
        </div>
      </template>
    </div>
  </AdminLayout>
</template>
