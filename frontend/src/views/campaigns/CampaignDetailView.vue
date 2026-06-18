<script setup>
import { onMounted, ref } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import api from '@/api/axios'
import AdminLayout from '@/components/admin/AdminLayout.vue'

const route = useRoute()

const campaign = ref(null)
const loading = ref(true)
const actionLoading = ref(false)
const errorMessage = ref('')
const showRejectInput = ref(false)
const rejectReason = ref('')

const fetchCampaignDetail = async () => {
  loading.value = true
  errorMessage.value = ''
  try {
    const response = await api.get(`/superadmin/campaigns/${route.params.id}`)
    campaign.value = response.data.data
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Gagal memuat detail campaign.'
  } finally {
    loading.value = false
  }
}

const approveCampaign = async () => {
  actionLoading.value = true
  try {
    await api.post(`/superadmin/campaigns/${route.params.id}/approve`)
    campaign.value.status = 'aktif'
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Gagal menyetujui campaign.'
  } finally {
    actionLoading.value = false
  }
}

const rejectCampaign = async () => {
  if (!rejectReason.value.trim()) return
  actionLoading.value = true
  try {
    await api.post(`/superadmin/campaigns/${route.params.id}/reject`, { alasan_penolakan: rejectReason.value })
    campaign.value.status = 'ditolak'
    campaign.value.alasan_penolakan = rejectReason.value
    showRejectInput.value = false
    rejectReason.value = ''
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Gagal menolak campaign.'
  } finally {
    actionLoading.value = false
  }
}

const formatRupiah = (n) => 'Rp ' + Number(n || 0).toLocaleString('id-ID')

onMounted(fetchCampaignDetail)
</script>

<template>
  <AdminLayout>
    <div class="space-y-4">
      <div class="flex items-center gap-2 text-xs text-gray-400">
        <RouterLink to="/campaigns/approval" class="text-[#8B4513] hover:underline">Approval Campaign</RouterLink>
        <span>/</span>
        <span class="text-gray-600">Detail #{{ route.params.id }}</span>
      </div>

      <div v-if="loading" class="bg-white rounded-xl p-8 text-center text-sm text-gray-400">Memuat detail campaign...</div>
      <div v-else-if="errorMessage" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">{{ errorMessage }}</div>

      <template v-else>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
          <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-stone-100 p-5 space-y-4">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <h2 class="text-lg font-bold text-gray-800">{{ campaign.judul }}</h2>
                <p class="text-sm text-gray-500">{{ campaign.nama_lembaga }}</p>
              </div>
              <span :class="[
                'px-3 py-1 rounded-full text-xs font-semibold shrink-0',
                campaign.status === 'menunggu_review' ? 'bg-amber-100 text-amber-700' :
                campaign.status === 'aktif' ? 'bg-green-100 text-green-700' :
                campaign.status === 'ditolak' ? 'bg-red-100 text-red-700' :
                'bg-gray-100 text-gray-600'
              ]">{{ campaign.status === 'menunggu_review' ? 'Pending' : campaign.status }}</span>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-sm">
              <div><span class="text-xs text-gray-400 block">Kategori</span><span class="text-gray-800 font-medium">{{ campaign.nama_kategori }}</span></div>
              <div><span class="text-xs text-gray-400 block">Wilayah</span><span class="text-gray-800 font-medium">{{ campaign.nama_wilayah || '-' }}</span></div>
              <div><span class="text-xs text-gray-400 block">Target Dana</span><span class="text-gray-800 font-medium">{{ formatRupiah(campaign.target_dana) }}</span></div>
              <div><span class="text-xs text-gray-400 block">Dana Terkumpul</span><span class="text-gray-800 font-medium">{{ formatRupiah(campaign.dana_terkumpul) }}</span></div>
              <div><span class="text-xs text-gray-400 block">Tipe Distribusi</span><span class="text-gray-800 font-medium">{{ campaign.tipe_distribusi || '-' }}</span></div>
              <div><span class="text-xs text-gray-400 block">Target Audiens</span><span class="text-gray-800 font-medium">{{ campaign.target_audiens || '-' }}</span></div>
            </div>

            <div class="pt-2">
              <p class="text-xs text-gray-400 font-medium mb-1">Deskripsi</p>
              <p class="text-sm text-gray-700 leading-relaxed">{{ campaign.deskripsi || '-' }}</p>
            </div>

            <div v-if="campaign.url_rab" class="pt-2">
              <p class="text-xs text-gray-400 font-medium mb-1">Dokumen RAB</p>
              <a :href="campaign.url_rab" target="_blank" class="text-sm text-[#8B4513] underline break-all">{{ campaign.url_rab }}</a>
            </div>

            <div v-if="campaign.alasan_penolakan" class="bg-red-50 border border-red-200 rounded-lg px-4 py-3">
              <p class="text-xs text-red-500 font-medium mb-0.5">Alasan Penolakan</p>
              <p class="text-sm text-red-700">{{ campaign.alasan_penolakan }}</p>
            </div>
          </div>

          <div class="bg-white rounded-xl shadow-sm border border-stone-100 p-5 space-y-4 self-start">
            <h3 class="text-sm font-bold text-gray-800">Aksi Review</h3>
            <p v-if="campaign.status === 'menunggu_review'" class="text-xs text-gray-500">Setujui atau tolak campaign ini.</p>
            <p v-else class="text-xs text-gray-500">Keputusan telah diambil untuk campaign ini.</p>

            <div v-if="showRejectInput" class="space-y-2">
              <textarea v-model="rejectReason" rows="3" placeholder="Alasan penolakan..." class="w-full px-3 py-2 border border-red-200 rounded-lg text-sm resize-none focus:outline-none focus:ring-2 focus:ring-red-400"></textarea>
              <div class="flex gap-2">
                <button @click="showRejectInput = false" class="flex-1 px-3 py-1.5 text-sm border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50">Batal</button>
                <button @click="rejectCampaign" :disabled="!rejectReason.trim() || actionLoading" class="flex-1 px-3 py-1.5 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50">
                  {{ actionLoading ? '...' : 'Konfirmasi Tolak' }}
                </button>
              </div>
            </div>

            <button
              v-if="!showRejectInput"
              class="w-full px-4 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50"
              :disabled="actionLoading || campaign.status !== 'menunggu_review'"
              @click="approveCampaign"
            >{{ actionLoading ? 'Memproses...' : 'Setujui Campaign' }}</button>

            <button
              v-if="!showRejectInput"
              class="w-full px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50"
              :disabled="actionLoading || campaign.status !== 'menunggu_review'"
              @click="showRejectInput = true"
            >Tolak Campaign</button>

            <RouterLink
              :to="`/campaigns/${campaign.id_campaign}/internal`"
              class="block w-full text-center px-4 py-2 text-sm border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50"
            >Lihat Monitoring Internal</RouterLink>
          </div>
        </div>
      </template>
    </div>
  </AdminLayout>
</template>
