<template>
  <div class="min-h-screen bg-[#F5F0E8] flex flex-col">
    <Navbar />

    <main class="flex-1 py-8">
      <div class="max-w-6xl mx-auto px-6">

        <nav class="text-sm text-gray-500 mb-4 flex items-center gap-1">
          <router-link to="/" class="hover:text-[#8B4513] transition-colors">Beranda</router-link>
          <span>/</span>
          <span class="text-[#2C2C2C] font-medium">Grafik Donasi</span>
        </nav>

        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
          <h1 class="text-2xl font-bold text-[#2C2C2C]">Grafik Donasi</h1>
        </div>

        <div v-if="loading" class="flex flex-col items-center justify-center py-20">
          <div class="w-8 h-8 border-2 border-[#8B4513] border-t-transparent rounded-full animate-spin mb-3" />
          <p class="text-sm text-gray-400">Memuat data...</p>
        </div>

        <div v-else-if="error" class="bg-white rounded-xl shadow-sm p-8 text-center">
          <p class="text-sm text-red-500 mb-4">{{ error }}</p>
          <button @click="fetchData" class="px-5 py-2 bg-[#8B4513] text-white rounded-lg text-sm font-medium hover:bg-[#6b3410] transition-colors">Coba Lagi</button>
        </div>

        <template v-else>
          <FilterBar
            v-model:startDate="filters.startDate"
            v-model:endDate="filters.endDate"
            v-model:selectedCampaign="filters.selectedCampaign"
            :campaigns="campaigns"
            @apply="fetchData"
            @reset="resetFilters"
          />

          <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6 mt-6">
            <StatMiniCard label="Total Donasi" :value="rupiah(stats.total_donasi)" color="text-[#8B4513]" />
            <StatMiniCard label="Jumlah Donatur" :value="stats.jumlah_donatur" color="text-blue-600" />
            <StatMiniCard label="Donasi Hari Ini" :value="rupiah(stats.donasi_hari_ini)" color="text-green-600" />
            <StatMiniCard label="Rata-rata Donasi" :value="rupiah(stats.rata_rata_donasi)" color="text-purple-600" />
          </div>

          <div class="bg-white rounded-xl shadow-sm border border-stone-100 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-stone-100">
              <h2 class="text-sm font-bold text-[#2C2C2C]">Performa Donasi</h2>
            </div>
            <div class="p-6">
              <PerformaChart :data="chartData" />
            </div>
          </div>
        </template>
      </div>
    </main>

    <AppFooter />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import api from '@/api/axios'
import Navbar from '@/components/shared/Navbar.vue'
import AppFooter from '@/components/shared/AppFooter.vue'
import FilterBar from '@/components/community/GrafikDonasi/FilterBar.vue'
import PerformaChart from '@/components/community/GrafikDonasi/PerformaChart.vue'
import StatMiniCard from '@/components/community/GrafikDonasi/StatMiniCard.vue'

const loading = ref(true)
const error = ref('')
const campaigns = ref([])
const chartData = ref([])
const stats = ref({
  total_donasi: 0,
  jumlah_donatur: 0,
  donasi_hari_ini: 0,
  rata_rata_donasi: 0,
})

const filters = reactive({
  startDate: '',
  endDate: '',
  selectedCampaign: '',
})

function rupiah(val) {
  return 'Rp ' + (Number(val) || 0).toLocaleString('id-ID')
}

function resetFilters() {
  filters.startDate = ''
  filters.endDate = ''
  filters.selectedCampaign = ''
  fetchData()
}

function buildParams() {
  const params = {}
  if (filters.startDate) params.start_date = filters.startDate
  if (filters.endDate) params.end_date = filters.endDate
  if (filters.selectedCampaign) params.campaign_id = filters.selectedCampaign
  return params
}

async function fetchData() {
  loading.value = true
  error.value = ''
  try {
    const params = buildParams()
    const [statsRes, chartRes, campaignsRes] = await Promise.all([
      api.get('/communities/donations/statistics', { params }),
      api.get('/communities/donations/chart', { params }),
      api.get('/communities/campaigns'),
    ])
    const statsData = statsRes.data.data || statsRes.data
    stats.value = { ...stats.value, ...(statsData.statistik || statsData) }
    chartData.value = (chartRes.data.data || chartRes.data).chart || []
    campaigns.value = (campaignsRes.data.data || campaignsRes.data) || []
  } catch (e) {
    error.value = e.response?.data?.message || 'Gagal memuat data grafik'
  } finally {
    loading.value = false
  }
}

onMounted(fetchData)
</script>
