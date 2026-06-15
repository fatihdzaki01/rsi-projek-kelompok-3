<script setup>
import { onMounted, ref, nextTick } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/services/api'
import NavBar from '@/components/NavBar.vue'
import AppFooter from '@/components/AppFooter.vue'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

const loading = ref(true)
const errorMessage = ref('')

const startDate = ref(new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0])
const endDate = ref(new Date().toISOString().split('T')[0])

const financialSummary = ref(null)
const saldoAkhir = ref(0)
const successRate = ref(0)
const categoryDist = ref([])
const platformData = ref([])
const topCampaigns = ref([])
const topCommunities = ref([])

const trendCanvasRef = ref(null)
const categoryCanvasRef = ref(null)
const topCampaignCanvasRef = ref(null)
const topKomunitasCanvasRef = ref(null)

let trendChart = null
let categoryChart = null
let topCampaignChart = null
let topKomunitasChart = null

const hasTrendData = () => platformData.value?.length && platformData.value.some(d => Number(d.total_donasi) > 0)
const hasCategoryData = () => categoryDist.value?.length

const fetchData = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const [analyticsRes, statsRes] = await Promise.all([
      api.get('/superadmin/analytics/platform', {
        params: { start_date: startDate.value, end_date: endDate.value },
      }),
      api.get('/superadmin/dashboard/statistics', { params: { days: 30 } }),
    ])

    const aData = analyticsRes.data.data
    const sData = statsRes.data.data

    platformData.value = aData.platform_daily || []
    financialSummary.value = aData.financial_summary
    saldoAkhir.value = aData.saldo_akhir
    successRate.value = aData.campaign_success_rate
    categoryDist.value = aData.category_distribution || []
    topCampaigns.value = sData.top_campaigns || []
    topCommunities.value = sData.top_communities || []
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Gagal memuat statistik.'
  } finally {
    loading.value = false
    await nextTick()
    renderCharts()
  }
}

const applyFilters = () => {
  fetchData()
}

const renderCharts = () => {
  if (trendChart) trendChart.destroy()
  if (trendCanvasRef.value && hasTrendData()) {
    trendChart = new Chart(trendCanvasRef.value, {
      type: 'line',
      data: {
        labels: platformData.value.map(d => d.tanggal?.slice(0, 10)),
        datasets: [{
          label: 'Total Donasi',
          data: platformData.value.map(d => Number(d.total_donasi) || 0),
          borderColor: '#a85f20',
          backgroundColor: 'rgba(168, 95, 32, 0.1)',
          fill: true,
          tension: 0.3,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } },
      },
    })
  }

  if (categoryChart) categoryChart.destroy()
  if (categoryCanvasRef.value && hasCategoryData()) {
    categoryChart = new Chart(categoryCanvasRef.value, {
      type: 'doughnut',
      data: {
        labels: categoryDist.value.map(d => d.nama_kategori),
        datasets: [{
          data: categoryDist.value.map(d => d.total),
          backgroundColor: ['#a85f20', '#07313a', '#276749', '#b91c1c', '#6b21a8', '#92400e', '#1d4ed8'],
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } },
      },
    })
  }

  if (topCampaignChart) topCampaignChart.destroy()
  if (topCampaignCanvasRef.value && topCampaigns.value.length) {
    topCampaignChart = new Chart(topCampaignCanvasRef.value, {
      type: 'bar',
      data: {
        labels: topCampaigns.value.map(c => c.judul?.length > 20 ? c.judul.slice(0, 20) + '...' : c.judul),
        datasets: [{
          label: 'Dana Terkumpul',
          data: topCampaigns.value.map(c => Number(c.dana_terkumpul)),
          backgroundColor: '#a85f20',
          borderRadius: 4,
        }],
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true } },
      },
    })
  }

  if (topKomunitasChart) topKomunitasChart.destroy()
  if (topKomunitasCanvasRef.value && topCommunities.value.length) {
    topKomunitasChart = new Chart(topKomunitasCanvasRef.value, {
      type: 'bar',
      data: {
        labels: topCommunities.value.map(k => k.nama_lembaga?.length > 20 ? k.nama_lembaga.slice(0, 20) + '...' : k.nama_lembaga),
        datasets: [{
          label: 'Total Dana',
          data: topCommunities.value.map(k => Number(k.total_dana)),
          backgroundColor: '#07313a',
          borderRadius: 4,
        }],
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true } },
      },
    })
  }
}

onMounted(fetchData)
</script>

<template>
  <main class="dashboard-page">
    <NavBar />

    <section class="container">
      <div class="page-title">
        <div>
          <h1>Statistik Platform</h1>
          <p>Analitik dan statistik platform secara real-time.</p>
        </div>

        <RouterLink to="/dashboard" class="back-link">Kembali Dashboard</RouterLink>
      </div>

      <div class="filter-bar secondary-filters">
        <div class="filter-group">
          <label>Periode</label>
          <div class="filter-row">
            <input v-model="startDate" type="date" class="date-input" />
            <span class="date-sep">—</span>
            <input v-model="endDate" type="date" class="date-input" />
            <button class="apply-btn" @click="applyFilters">Terapkan</button>
          </div>
        </div>
      </div>

      <section v-if="loading" class="card">Memuat statistik...</section>

      <section v-else-if="errorMessage" class="card error">{{ errorMessage }}</section>

      <template v-else>
        <section class="stats-grid">
          <div class="stat-card highlight">
            <p>Total Donasi</p>
            <h2>Rp{{ Number(financialSummary?.total_donasi || 0).toLocaleString('id-ID') }}</h2>
            <span>Periode terpilih</span>
          </div>
          <div class="stat-card">
            <p>Jumlah Donasi</p>
            <h2>{{ Number(financialSummary?.jumlah_donasi || 0).toLocaleString('id-ID') }}</h2>
            <span>Transaksi</span>
          </div>
          <div class="stat-card">
            <p>Potongan Platform</p>
            <h2>Rp{{ Number(financialSummary?.total_potongan || 0).toLocaleString('id-ID') }}</h2>
            <span>Total potongan</span>
          </div>
          <div class="stat-card">
            <p>Pencairan Dana</p>
            <h2>Rp{{ Number(financialSummary?.total_pencairan || 0).toLocaleString('id-ID') }}</h2>
            <span>Total pencairan</span>
          </div>
          <div class="stat-card">
            <p>Saldo Akhir</p>
            <h2>Rp{{ Number(saldoAkhir || 0).toLocaleString('id-ID') }}</h2>
            <span>Saldo platform</span>
          </div>
          <div class="stat-card">
            <p>Success Rate</p>
            <h2>{{ successRate }}%</h2>
            <span>Campaign sukses</span>
          </div>
        </section>

        <section class="content-grid">
          <div class="card">
            <div class="card-header">
              <h2>Tren Donasi</h2>
              <p>Grafik donasi per hari pada periode terpilih.</p>
            </div>
            <div class="chart-box">
              <canvas ref="trendCanvasRef"></canvas>
              <p v-if="!hasTrendData()" class="empty-text">Tidak ada data tren untuk periode ini.</p>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <h2>Distribusi Kategori</h2>
              <p>Persebaran campaign per kategori.</p>
            </div>
            <div class="chart-box">
              <canvas ref="categoryCanvasRef"></canvas>
              <p v-if="!hasCategoryData()" class="empty-text">Tidak ada data kategori.</p>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <h2>Top Campaign</h2>
              <p>5 campaign dengan dana terkumpul tertinggi.</p>
            </div>
            <div class="chart-box">
              <canvas ref="topCampaignCanvasRef"></canvas>
              <p v-if="!topCampaigns.length" class="empty-text">Tidak ada data.</p>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <h2>Top Komunitas</h2>
              <p>5 komunitas dengan total dana tertinggi.</p>
            </div>
            <div class="chart-box">
              <canvas ref="topKomunitasCanvasRef"></canvas>
              <p v-if="!topCommunities.length" class="empty-text">Tidak ada data.</p>
            </div>
          </div>
        </section>
      </template>
    </section>

    <AppFooter />
  </main>
</template>

<style scoped>
.secondary-filters { margin-bottom: 18px; }
.filter-group { display: flex; flex-direction: column; gap: 6px; }
.filter-group label { font-size: 12px; font-weight: 700; color: #6f655b; text-transform: uppercase; letter-spacing: 0.3px; }
.filter-row { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
.date-input { height: 36px; padding: 0 10px; border: 1px solid #dccdbb; border-radius: 8px; background: #fffaf2; color: #07313a; font-size: 13px; font-family: inherit; }
.date-sep { color: #6f655b; font-weight: 700; }
.apply-btn { height: 36px; padding: 0 18px; border: 0; border-radius: 8px; background: #07313a; color: white; font-weight: 700; font-size: 13px; cursor: pointer; }
.stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 20px; }

.content-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; align-items: stretch; }
.content-grid .card { display: flex; flex-direction: column; }

.chart-box { flex: 1; display: flex; align-items: center; justify-content: center; min-height: 220px; position: relative; padding: 8px 0; }
.chart-box canvas { max-height: 220px; max-width: 100%; }
.chart-box .empty-text { color: #6f655b; text-align: center; }

.empty-text { color: #6f655b; text-align: center; padding: 16px; }

@media (max-width: 900px) {
  .content-grid { grid-template-columns: 1fr; }
}
</style>
