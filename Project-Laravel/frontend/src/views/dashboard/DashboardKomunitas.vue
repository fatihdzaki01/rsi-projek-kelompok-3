<template>
  <div class="min-h-screen bg-[#ede8e1] flex flex-col">
    <Navbar />

    <main class="flex-grow py-8 px-4 lg:px-10 max-w-7xl mx-auto w-full">
      <!-- Header with Action Buttons -->
      <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
          <h1 class="dashboard-title">Dashboard Komunitas</h1>
          <p class="dashboard-sub text-gray-600 text-sm">
            Selamat datang, <strong>Komunitas</strong>
          </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <router-link to="/communities/campaigns/create" class="px-4 py-2 bg-[#8B4513] text-white rounded-lg text-sm font-medium hover:bg-[#6b3410] transition-colors">+ Campaign Baru</router-link>
          <router-link to="/communities/campaigns/updates/create" class="px-4 py-2 bg-white text-[#8B4513] border border-[#8B4513] rounded-lg text-sm font-medium hover:bg-stone-50 transition-colors">+ Update Campaign</router-link>
          <router-link to="/communities/profile/edit" class="px-4 py-2 border border-stone-200 text-[#2C2C2C] bg-white rounded-lg text-sm font-medium hover:bg-stone-50 transition-colors">Edit Profil</router-link>
          <router-link to="/communities/withdrawals" class="px-4 py-2 border border-stone-200 text-[#2C2C2C] bg-white rounded-lg text-sm font-medium hover:bg-stone-50 transition-colors">Ajukan Pencairan</router-link>
        </div>
      </div>

      <!-- Stat Cards Besar -->
      <div class="stats-large">
        <StatCardLarge
          label="TOTAL DANA TERKUMPUL"
          :value="formatShort(stats.totalTerkumpul)"
          icon="💰"
        />
        <StatCardLarge
          label="TOTAL DICAIRKAN"
          :value="formatShort(stats.totalDicairkan)"
          icon="📤"
          value-color="#c0392b"
        />
        <StatCardLarge
          label="SALDO TERSISA"
          :value="formatShort(stats.saldoTersisa)"
          icon="💎"
        />
      </div>

      <!-- Stat Cards Kecil -->
      <div class="stats-small">
        <StatCardSmall label="CAMPAIGN AKTIF"    :value="stats.campaignAktif" />
        <StatCardSmall label="CAMPAIGN SELESAI"  :value="stats.campaignSelesai" />
        <StatCardSmall label="MENUNGGU REVIEW"   :value="stats.menungguReview" />
        <StatCardSmall label="DITOLAK"           :value="stats.ditolak" />
        <StatCardSmall label="TOTAL DONATUR"     :value="stats.totalDonatur.toLocaleString('id-ID')" />
      </div>

      <!-- Main Content -->
      <div class="main-content">
        <!-- Kiri -->
        <div class="content-left">
          <PencairanReview :items="pencairanList" />
          <HampirBatasWaktu :campaigns="hampirHabis" />
        </div>

        <!-- Kanan -->
        <div class="content-right">
          <TargetBulanIni
            :current="target.current"
            :max="target.max"
            :percent="target.percent"
          />
        </div>
      </div>
    </main>

    <AppFooter />
  </div>
</template>

<script setup>
import Navbar from '@/components/shared/Navbar.vue'
import AppFooter from '@/components/shared/AppFooter.vue'
import StatCardLarge   from '@/components/community/dashboard/StatCardLarge.vue'
import StatCardSmall   from '@/components/community/dashboard/StatCardSmall.vue'
import PencairanReview from '@/components/community/dashboard/PencairanReview.vue'
import HampirBatasWaktu from '@/components/community/dashboard/HampirBatasWaktu.vue'
import TargetBulanIni  from '@/components/community/dashboard/TargetBulanIni.vue'

const stats = {
  totalTerkumpul : 342000000,
  totalDicairkan : 215000000,
  saldoTersisa   : 127000000,
  campaignAktif  : 3,
  campaignSelesai: 12,
  menungguReview : 1,
  ditolak        : 1,
  totalDonatur   : 1840,
}

const pencairanList = [
  {
    id      : '#WD-82910',
    campaign: 'Air Bersih untuk Desa Sukamaju',
    nominal : 25000000,
    status  : 'REVIEWING',
  },
]

const hampirHabis = [
  {
    name    : 'Sumur Bor Dusun Merapi',
    deadline: '3 hari',
    collected: 45200000,
    target  : 50000000,
    percent : 90,
  },
  {
    name    : 'Filter Air Sekolah Dasar 01',
    deadline: '3 hari',
    collected: 12800000,
    target  : 15000000,
    percent : 85,
  },
]

const target = {
  current: 75000000,
  max    : 100000000,
  percent: 75,
}

const formatShort = (n) =>
  'Rp ' + (n >= 1000000 ? (n / 1000000).toFixed(0) + 'jt' : n.toLocaleString('id-ID'))
</script>

<style scoped>
.dashboard-title {
  font-size: 26px;
  font-weight: 800;
  color: #1a1a2e;
  margin-bottom: 4px;
}

.dashboard-sub {
  font-size: 13px;
  color: #666;
  margin-bottom: 0px;
}

/* Stat Besar */
.stats-large {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
  margin-bottom: 16px;
}

/* Stat Kecil */
.stats-small {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 12px;
  margin-bottom: 24px;
}

/* Main Layout */
.main-content {
  display: grid;
  grid-template-columns: 1fr 260px;
  gap: 20px;
  align-items: start;
}

.content-left {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

@media (max-width: 900px) {
  .stats-large  { grid-template-columns: 1fr; }
  .stats-small  { grid-template-columns: repeat(3, 1fr); }
  .main-content { grid-template-columns: 1fr; }
}
</style>