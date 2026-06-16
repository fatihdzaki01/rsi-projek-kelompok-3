<template>
  <div class="min-h-screen bg-[#ede8e1] flex flex-col">
    <Navbar />

    <main class="flex-grow monitoring-page max-w-7xl mx-auto w-full py-8 px-4 lg:px-10">
      <!-- Akses Ditolak (fallback UI) -->
      <div v-if="!canAccess" class="forbidden-block">
        <p>🚫 Anda tidak memiliki akses ke halaman ini.</p>
      </div>

      <template v-else>
        <!-- Header: Pilih Campaign + Aksi -->
        <div class="page-header">
          <div>
            <p class="section-label">PILIH CAMPAIGN</p>
            <CampaignSelector
              :campaigns="campaigns"
              v-model="selectedCampaign"
            />
          </div>
          <div class="header-actions">
            <span class="badge-aktif">● AKTIF</span>
            <button class="btn-ajukan" @click="handleAjukan">
              + Ajukan Pencairan
            </button>
          </div>
        </div>

        <!-- Stat Cards Baris 1 -->
        <div class="stats-top">
          <StatCardTop
            icon="💰"
            label="DANA TERKUMPUL"
            :value="formatShort(stats.terkumpul)"
            badge="+12% vs bln lalu"
            :show-progress="true"
            :progress="stats.progressPercent"
            :target-label="`Target: ${formatShort(stats.target)}`"
            :progress-label="`${stats.progressPercent}%`"
          />
          <StatCardTop
            icon="📤"
            label="DANA DICAIRKAN"
            :value="formatShort(stats.dicairkan)"
            :sub-text="`Terakhir cair: ${stats.terakhirCair}`"
            value-color="#c0392b"
          />
          <StatCardTop
            icon="💎"
            label="SALDO TERSISA"
            :value="formatShort(stats.saldo)"
            sub-text="Dapat dicairkan segera"
            variant="dark"
          />
        </div>

        <!-- Stat Cards Baris 2 -->
        <div class="stats-bottom">
          <StatCardBottom
            icon="👥"
            label="JUMLAH DONATUR"
            :value="stats.donatur.toLocaleString('id-ID')"
            sub-text="Donatur unik terverifikasi"
          />
          <StatCardBottom
            icon="📋"
            label="SISA PENCAIRAN"
            :value="`${stats.sisaPencairan} / ${stats.maxPencairan}`"
            sub-text="Sisa limit periode ini"
          />
        </div>

        <!-- Tabel Riwayat -->
        <RiwayatTable :transactions="transactions" />
      </template>
    </main>

    <AppFooter />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import Navbar from '@/components/shared/Navbar.vue'
import AppFooter from '@/components/shared/AppFooter.vue'
import CampaignSelector from '@/components/campaign/Monitoring-Internal/CampaignSelector.vue'
import StatCardTop      from '@/components/campaign/Monitoring-Internal/StatCardTop.vue'
import StatCardBottom   from '@/components/campaign/Monitoring-Internal/StatCardBottom.vue'
import RiwayatTable     from '@/components/campaign/Monitoring-Internal/RIwayatTable.vue'

const auth = useAuthStore()
const canAccess = computed(() => auth.canAccessMonitoringInternal)

const campaigns = [
  { id: 1, name: 'Bantuan Medis Anak Pelosok Papua' },
  { id: 2, name: 'Air Bersih untuk Desa Sukamaju' },
  { id: 3, name: 'Sekolah Layak NTT' },
]
const selectedCampaign = ref(campaigns[0])

const stats = {
  terkumpul      : 128400000,
  target         : 200000000,
  progressPercent: 64,
  dicairkan      : 45200000,
  terakhirCair   : '12 Okt 2023',
  saldo          : 83200000,
  donatur        : 342,
  sisaPencairan  : 3,
  maxPencairan   : 5,
}

const transactions = [
  { id: '#WD-78210', date: '12 Okt 2023, 14:20', category: 'Biaya Medis',  amount: 15000000, status: 'BERHASIL'  },
  { id: '#WD-78195', date: '05 Okt 2023, 09:15', category: 'Operasional',  amount: 10200000, status: 'BERHASIL'  },
  { id: '#WD-78182', date: '28 Sep 2023, 16:45', category: 'Logistik',     amount: 20000000, status: 'DIPROSES'  },
]

const formatShort = (n) =>
  'Rp ' + (n >= 1000000
    ? (n / 1000000).toFixed(1).replace('.0', '') + 'jt'
    : n.toLocaleString('id-ID'))

const handleAjukan = () => {
  console.log('Ajukan pencairan:', selectedCampaign.value.name)
}
</script>

<style scoped>
.monitoring-page {
  font-family: 'Inter', sans-serif;
}

/* Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 24px;
}

.section-label {
  font-size: 11px;
  font-weight: 700;
  color: #888;
  letter-spacing: 0.5px;
  margin-bottom: 8px;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.badge-aktif {
  background: #e0f5ec;
  color: #2e7d5e;
  font-size: 12px;
  font-weight: 600;
  padding: 6px 14px;
  border-radius: 999px;
}

.btn-ajukan {
  background: #4a3728;
  color: white;
  border: none;
  border-radius: 8px;
  padding: 10px 18px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}
.btn-ajukan:hover { background: #3a2a1e; }

/* Stats */
.stats-top {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
  margin-bottom: 16px;
}

.stats-bottom {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
  margin-bottom: 24px;
}

/* Forbidden */
.forbidden-block {
  text-align: center;
  margin-top: 100px;
  font-size: 18px;
  color: #c0392b;
  font-weight: 600;
}

@media (max-width: 768px) {
  .stats-top        { grid-template-columns: 1fr; }
  .stats-bottom     { grid-template-columns: 1fr; }
  .page-header      { flex-direction: column; align-items: flex-start; gap: 14px; }
}
</style>