<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/api/axios'
import AdminLayout from '@/components/admin/AdminLayout.vue'

const profile = ref(null)
const campaigns = ref([])
const stats = ref({
  total_users: 0,
  total_campaign: 0,
  total_donasi: 0,
  campaign_aktif: 0,
  campaign_selesai: 0,
  campaign_menunggu_review: 0,
  total_donatur_aktif: 0,
})

const loading = ref(true)
const errorMessage = ref('')

const fetchDashboard = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const [profileResponse, dashboardResponse] = await Promise.all([
      api.get('/superadmin/profile'),
      api.get('/superadmin/dashboard'),
    ])

    profile.value = profileResponse.data.data
    const data = dashboardResponse.data.data

    const summary = data?.summary ?? {}
    stats.value = {
      total_users: summary.total_users ?? 0,
      total_campaign: summary.total_campaign ?? 0,
      total_donasi: summary.total_nominal_donasi ?? 0,
      campaign_aktif: summary.campaign_aktif ?? 0,
      campaign_selesai: summary.campaign_selesai ?? 0,
      campaign_menunggu_review: summary.campaign_menunggu_review ?? 0,
      total_donatur_aktif: summary.total_donatur_aktif ?? 0,
    }

    const recentCampaigns = data?.recent_campaigns ?? []
    campaigns.value = recentCampaigns.filter(c => c.status === 'menunggu_review')
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || 'Gagal memuat dashboard.'
  } finally {
    loading.value = false
  }
}

onMounted(fetchDashboard)
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-xl font-bold text-gray-800">Ringkasan Platform</h1>
        <p class="text-sm text-gray-500">Pantau aktivitas terbaru dan performa platform secara real-time.</p>
      </div>

      <div v-if="loading" class="flex items-center justify-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#8B4513]"></div>
        <span class="ml-3 text-gray-500">Memuat data...</span>
      </div>

      <section v-else-if="errorMessage" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
        {{ errorMessage }}
      </section>

      <template v-else>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="bg-white rounded-xl p-5 shadow-sm border border-stone-200">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Total User</p>
            <h2 class="text-2xl font-bold text-gray-800 mt-1">{{ Number(stats.total_users).toLocaleString('id-ID') }}</h2>
            <span class="text-xs text-gray-400">Database aktif</span>
          </div>

          <div class="bg-white rounded-xl p-5 shadow-sm border border-stone-200">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Total Campaign</p>
            <h2 class="text-2xl font-bold text-gray-800 mt-1">{{ Number(stats.total_campaign).toLocaleString('id-ID') }}</h2>
            <span class="text-xs text-gray-400">
              {{ stats.campaign_aktif }} aktif &bull; {{ stats.campaign_selesai }} selesai &bull; {{ stats.campaign_menunggu_review }} menunggu
            </span>
          </div>

          <div class="bg-white rounded-xl p-5 shadow-sm border border-stone-200">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Total Transaksi Donasi</p>
            <h2 class="text-2xl font-bold text-[#8B4513] mt-1">Rp{{ Number(stats.total_donasi).toLocaleString('id-ID') }}</h2>
            <span class="text-xs text-gray-400">{{ Number(stats.total_donatur_aktif).toLocaleString('id-ID') }} donatur aktif</span>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-stone-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-stone-100 flex items-center justify-between">
              <div>
                <h2 class="text-sm font-semibold text-gray-800">Perlu Tindakan Segera</h2>
                <p class="text-xs text-gray-400">Campaign yang menunggu review superadmin.</p>
              </div>
              <span class="text-xs bg-amber-100 text-amber-700 px-2.5 py-0.5 rounded-full font-medium">{{ campaigns.length }} data</span>
            </div>

            <div v-if="campaigns.length === 0" class="p-8 text-center text-gray-400 text-sm">
              <p>Tidak ada campaign yang menunggu review.</p>
            </div>

            <div v-else class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead class="bg-stone-50 text-gray-500 text-xs uppercase">
                  <tr>
                    <th class="px-5 py-3 text-left font-medium">ID</th>
                    <th class="px-5 py-3 text-left font-medium">Judul Campaign</th>
                    <th class="px-5 py-3 text-left font-medium">Komunitas</th>
                    <th class="px-5 py-3 text-left font-medium">Target Dana</th>
                    <th class="px-5 py-3 text-left font-medium">Status</th>
                    <th class="px-5 py-3 text-center font-medium">Aksi</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                  <tr v-for="campaign in campaigns" :key="campaign.id_campaign" class="hover:bg-stone-50 transition-colors">
                    <td class="px-5 py-3 text-gray-500">#{{ campaign.id_campaign }}</td>
                    <td class="px-5 py-3 font-medium text-gray-800 max-w-[200px] truncate">{{ campaign.judul }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ campaign.nama_lembaga }}</td>
                    <td class="px-5 py-3 text-gray-700">Rp{{ Number(campaign.target_dana).toLocaleString('id-ID') }}</td>
                    <td class="px-5 py-3">
                      <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                        {{ campaign.status }}
                      </span>
                    </td>
                    <td class="px-5 py-3 text-center">
                      <RouterLink
                        :to="`/campaigns/${campaign.id_campaign}/review`"
                        class="text-xs text-[#8B4513] hover:text-[#6B3410] font-medium hover:underline"
                      >
                        Review
                      </RouterLink>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="bg-white rounded-xl shadow-sm border border-stone-200 p-5">
            <h2 class="text-sm font-semibold text-gray-800 mb-4">Profil Superadmin</h2>

            <div class="flex items-center gap-3 mb-4">
              <div class="w-12 h-12 rounded-full bg-[#8B4513]/10 flex items-center justify-center text-[#8B4513] font-bold text-lg">
                {{ (profile?.nama_lengkap || profile?.username || 'A')[0].toUpperCase() }}
              </div>
              <div>
                <p class="text-sm font-medium text-gray-800">{{ profile?.nama_lengkap || profile?.username || '-' }}</p>
                <p class="text-xs text-gray-400">SUPERADMIN</p>
              </div>
            </div>

            <div class="space-y-3 text-sm">
              <div class="flex justify-between">
                <span class="text-gray-400">Email</span>
                <span class="text-gray-700">{{ profile?.email || '-' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-400">Username</span>
                <span class="text-gray-700">{{ profile?.username || '-' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-400">Status</span>
                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                  {{ profile?.is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
              </div>
            </div>

            <RouterLink
              to="/dashboard/profile"
              class="mt-4 block w-full text-center text-xs py-2 rounded-lg border border-stone-200 text-gray-600 hover:bg-stone-50 transition-colors"
            >
              Edit Profil
            </RouterLink>
          </div>
        </div>
      </template>
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
