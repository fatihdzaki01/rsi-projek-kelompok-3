<template>
  <div class="public-monitoring">

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Memuat data monitoring...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-state">
      <div class="error-icon">⚠️</div>
      <h2>Monitoring Tidak Tersedia</h2>
      <p>{{ error }}</p>
      <router-link to="/campaigns" class="btn-back">Kembali ke Daftar Campaign</router-link>
    </div>

    <!-- Main Content -->
    <template v-else>
      <!-- Page Header -->
      <div class="page-header">
        <span class="page-tag">CAMPAIGN MONITORING</span>
        <h1 class="page-title">{{ campaign.title }}</h1>
        <p class="page-desc">{{ campaign.description }}</p>
      </div>

      <!-- Stat Cards -->
      <div class="stats-row">
        <div class="stat-card">
          <div class="stat-icon">💰</div>
          <div>
            <p class="stat-label">Total Donasi</p>
            <h3 class="stat-value">{{ formatRp(campaign.totalDonasi) }}</h3>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon">📤</div>
          <div>
            <p class="stat-label">Total Pengeluaran</p>
            <h3 class="stat-value">{{ formatRp(campaign.totalPengeluaran) }}</h3>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon">💎</div>
          <div>
            <p class="stat-label">Sisa Dana</p>
            <h3 class="stat-value highlight">{{ formatRp(campaign.sisaDana) }}</h3>
          </div>
        </div>
      </div>

      <!-- Main Grid -->
      <div class="main-content">
        <!-- Tabel Donatur -->
        <DonorTable :donors="donors" />

        <!-- Sidebar Kanan -->
        <div class="sidebar">
          <CampaignDetailCard :campaign="campaign" />
          <DonorInsightCard :insight="campaign.insight" />
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/api/axios'
import DonorTable from '@/components/campaign/Monitoring/DonorTable.vue'
import CampaignDetailCard from '@/components/campaign/Monitoring/CampaignDetailCard.vue'
import DonorInsightCard from '@/components/campaign/Monitoring/DonorInsightCard.vue'

const route = useRoute()
const loading = ref(true)
const error = ref(null)

const campaign = ref({
  title: '',
  description: '',
  totalDonasi: 0,
  totalPengeluaran: 0,
  sisaDana: 0,
  image: '',
  about: '',
  progressPercent: 0,
  collected: 0,
  target: 0,
  insight: 'Memproses pola donasi dari data terbaru...',
})

const donors = ref([])

const formatRp = (n) => {
  const num = Number(n) || 0
  return 'Rp ' + num.toLocaleString('id-ID')
}

const fetchMonitoringData = async () => {
  try {
    loading.value = true
    const campaignId = route.params.id
    const response = await api.get(`/campaigns/${campaignId}/public-monitoring`)
    const raw = response.data.data
    
    // Map campaign detail
    campaign.value = {
      title: raw.campaign.judul,
      description: raw.campaign.deskripsi,
      totalDonasi: Number(raw.summary.total_dana_masuk) || 0,
      totalPengeluaran: Number(raw.summary.total_dana_dicairkan) || 0,
      sisaDana: Number(raw.summary.saldo_tersisa) || 0,
      image: raw.campaign.foto_campaign_url,
      about: raw.campaign.deskripsi,
      progressPercent: raw.campaign.target_dana > 0 
        ? Math.min(100, Math.round((raw.campaign.dana_terkumpul / raw.campaign.target_dana) * 100))
        : 0,
      collected: Number(raw.campaign.dana_terkumpul) || 0,
      target: Number(raw.campaign.target_dana) || 0,
      insight: generateInsight(raw.donations),
    }

    // Map donors list
    donors.value = (raw.donations || []).map(d => {
      let donorName = 'Anonim'
      if (!d.is_anonim) {
        donorName = d.nama_tampil || d.nama_lengkap || d.username || 'Donatur'
      }
      return {
        name: donorName,
        amount: Number(d.nominal) || 0,
        date: d.created_at
      }
    })
  } catch (err) {
    console.error(err)
    error.value = err.response?.data?.message || 'Gagal memuat data monitoring.'
  } finally {
    loading.value = false
  }
}

// Simple heuristic to analyze donation hours for insight
function generateInsight(donationsList) {
  if (!donationsList || donationsList.length === 0) {
    return 'Belum ada donasi terkumpul. Mari sebarkan campaign ini ke media sosial Anda untuk menarik donatur.'
  }
  
  // Count counts of hour ranges: Morning (5-11), Afternoon (12-16), Evening (17-21), Night (22-4)
  let timeSlots = {
    'pagi hari': 0,
    'siang hari': 0,
    'sore/malam hari': 0,
    'larut malam': 0
  }
  
  donationsList.forEach(d => {
    if (!d.created_at) return
    const hour = new Date(d.created_at).getHours()
    if (hour >= 5 && hour < 12) timeSlots['pagi hari']++
    else if (hour >= 12 && hour < 17) timeSlots['siang hari']++
    else if (hour >= 17 && hour < 22) timeSlots['sore/malam hari']++
    else timeSlots['larut malam']++
  })

  // Find slot with maximum count
  let maxSlot = 'sore/malam hari'
  let maxCount = -1
  Object.keys(timeSlots).forEach(key => {
    if (timeSlots[key] > maxCount) {
      maxCount = timeSlots[key]
      maxSlot = key
    }
  })

  return `Sebagian besar donasi masuk pada ${maxSlot}. Disarankan membagikan update atau newsletter pada waktu tersebut untuk menjangkau donatur lebih maksimal.`
}

onMounted(fetchMonitoringData)
</script>

<style scoped>
.public-monitoring {
  background: #ede8e1;
  min-height: 100vh;
  padding: 32px 40px;
  font-family: 'Inter', sans-serif;
}

/* Loading and Error States */
.loading-state, .error-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 60vh;
  text-align: center;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #8b4513;
  border-top-color: transparent;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 16px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.error-icon {
  font-size: 48px;
  margin-bottom: 16px;
}

.error-state h2 {
  font-size: 20px;
  color: #1a1a2e;
  margin-bottom: 8px;
}

.error-state p {
  color: #666;
  margin-bottom: 20px;
}

.btn-back {
  background: #8b4513;
  color: white;
  padding: 10px 20px;
  border-radius: 8px;
  text-decoration: none;
  font-size: 14px;
  font-weight: 600;
  transition: background 0.2s;
}

.btn-back:hover {
  background: #72380f;
}

/* Header */
.page-tag {
  font-size: 11px;
  font-weight: 700;
  color: #8a7060;
  letter-spacing: 1px;
}

.page-title {
  font-size: 26px;
  font-weight: 800;
  color: #1a1a2e;
  margin: 6px 0 8px;
}

.page-desc {
  font-size: 13px;
  color: #666;
  max-width: 360px;
  line-height: 1.6;
  margin-bottom: 28px;
}

/* Stat Cards */
.stats-row {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
  margin-bottom: 24px;
}

.stat-card {
  background: white;
  border-radius: 14px;
  padding: 18px 20px;
  display: flex;
  align-items: center;
  gap: 14px;
  box-shadow: 0 1px 8px rgba(0,0,0,0.06);
  position: relative;
}

.stat-icon {
  font-size: 22px;
  width: 44px;
  height: 44px;
  background: #f5f0eb;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.stat-label {
  font-size: 11px;
  color: #888;
  font-weight: 600;
  margin-bottom: 4px;
}

.stat-value {
  font-size: 18px;
  font-weight: 700;
  color: #1a1a2e;
}

.stat-value.highlight { color: #c0392b; }

.stat-badge {
  position: absolute;
  top: 14px;
  right: 14px;
  font-size: 10px;
  font-weight: 600;
  padding: 3px 8px;
  border-radius: 999px;
}

.stat-badge.positive {
  background: #e0f5ec;
  color: #2e7d5e;
}

/* Main Layout */
.main-content {
  display: grid;
  grid-template-columns: 1fr 300px;
  gap: 20px;
  align-items: start;
}

.sidebar {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

@media (max-width: 768px) {
  .public-monitoring { padding: 16px; }
  .stats-row { grid-template-columns: 1fr; }
  .main-content { grid-template-columns: 1fr; }
}
</style>
