<template>
  <div class="min-h-screen bg-[#F5F0E8] flex flex-col">
    <Navbar />

    <main class="flex-1 py-8">
      <div class="max-w-6xl mx-auto px-6">

        <nav class="text-sm text-gray-500 mb-4 flex items-center gap-1">
          <router-link to="/" class="hover:text-[#8B4513] transition-colors">Beranda</router-link>
          <span>/</span>
          <span class="text-[#2C2C2C] font-medium">Dashboard Komunitas</span>
        </nav>

        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
          <h1 class="text-2xl font-bold text-[#2C2C2C]">Dashboard Komunitas</h1>
          <div class="flex flex-wrap items-center gap-2">
            <router-link to="/communities/campaigns/history" class="px-4 py-2 bg-white text-[#8B4513] border border-[#8B4513] rounded-lg text-sm font-medium hover:bg-stone-50 transition-colors">Campaign Saya</router-link>
            <router-link to="/communities/campaigns/create" class="px-4 py-2 bg-[#8B4513] text-white rounded-lg text-sm font-medium hover:bg-[#6b3410] transition-colors">+ Campaign Baru</router-link>
            <router-link to="/communities/campaigns/updates/create" class="px-4 py-2 bg-white text-[#8B4513] border border-[#8B4513] rounded-lg text-sm font-medium hover:bg-stone-50 transition-colors">+ Update Campaign</router-link>
            <router-link to="/communities/profile/edit" class="px-4 py-2 border border-stone-200 text-[#2C2C2C] rounded-lg text-sm font-medium hover:bg-stone-50 transition-colors">Edit Profil</router-link>
            <router-link to="/communities/withdrawals" class="px-4 py-2 border border-stone-200 text-[#2C2C2C] rounded-lg text-sm font-medium hover:bg-stone-50 transition-colors">Ajukan Pencairan</router-link>
          </div>
        </div>

        <div v-if="loading" class="flex flex-col items-center justify-center py-20">
          <div class="w-8 h-8 border-2 border-[#8B4513] border-t-transparent rounded-full animate-spin mb-3" />
          <p class="text-sm text-gray-400">Memuat dashboard...</p>
        </div>

        <div v-else-if="error" class="bg-white rounded-xl shadow-sm p-8 text-center">
          <p class="text-sm text-red-500 mb-4">{{ error }}</p>
          <button @click="fetchDashboard" class="px-5 py-2 bg-[#8B4513] text-white rounded-lg text-sm font-medium hover:bg-[#6b3410] transition-colors">Coba Lagi</button>
        </div>

        <template v-else>
          <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-stone-100 p-5">
              <p class="text-xs text-gray-400 mb-1">Dana Terkumpul</p>
              <p class="text-xl font-bold text-[#2C2C2C]">{{ rupiah(stats.total_dana_terkumpul) }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-stone-100 p-5">
              <p class="text-xs text-gray-400 mb-1">Campaign Aktif</p>
              <p class="text-xl font-bold text-[#2C2C2C]">{{ stats.total_campaign_aktif }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-stone-100 p-5">
              <p class="text-xs text-gray-400 mb-1">Campaign Selesai</p>
              <p class="text-xl font-bold text-[#2C2C2C]">{{ stats.total_campaign_selesai }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-stone-100 p-5">
              <p class="text-xs text-gray-400 mb-1">Donatur Unik</p>
              <p class="text-xl font-bold text-[#2C2C2C]">{{ stats.total_donatur_unik }}</p>
            </div>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Campaign stats mini -->
            <div class="bg-white rounded-xl shadow-sm border border-stone-100 p-5">
              <h2 class="text-sm font-bold text-[#2C2C2C] mb-3">Ringkasan Campaign</h2>
              <div class="space-y-2.5 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">Aktif</span><span class="font-semibold">{{ stats.total_campaign_aktif }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Selesai</span><span class="font-semibold">{{ stats.total_campaign_selesai }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Menunggu Review</span><span class="font-semibold">{{ stats.total_campaign_review }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Ditolak</span><span class="font-semibold">{{ stats.total_campaign_ditolak }}</span></div>
              </div>
            </div>

            <!-- Financial summary -->
            <div class="bg-white rounded-xl shadow-sm border border-stone-100 p-5">
              <h2 class="text-sm font-bold text-[#2C2C2C] mb-3">Ringkasan Keuangan</h2>
              <div class="space-y-2.5 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">Dana Terkumpul</span><span class="font-semibold">{{ rupiah(stats.total_dana_terkumpul) }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Sudah Dicairkan</span><span class="font-semibold">{{ rupiah(stats.total_dana_dicairkan) }}</span></div>
                <div class="flex justify-between border-t border-stone-100 pt-2.5"><span class="text-gray-500">Saldo Tersisa</span><span class="font-semibold text-green-600">{{ rupiah(stats.total_saldo_tersisa) }}</span></div>
              </div>
            </div>

            <!-- Pending withdrawals -->
            <div class="bg-white rounded-xl shadow-sm border border-stone-100 p-5">
              <h2 class="text-sm font-bold text-[#2C2C2C] mb-3">Pencairan Pending</h2>
              <div v-if="pencairanPending.length === 0" class="text-sm text-gray-400 py-3 text-center">Tidak ada pengajuan pending</div>
              <div v-else class="space-y-2">
                <div v-for="p in pencairanPending" :key="p.id_pencairan" class="text-sm flex justify-between items-center">
                  <span class="text-gray-600">#{{ p.id_pencairan }}</span>
                  <span class="font-semibold">{{ rupiah(p.nominal_diajukan) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Campaigns nearing completion -->
          <div class="bg-white rounded-xl shadow-sm border border-stone-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-stone-100">
              <h2 class="text-sm font-bold text-[#2C2C2C]">Campaign Hampir Tercapai (&ge;80%)</h2>
            </div>
            <div v-if="campaignHampirSelesai.length === 0" class="px-6 py-10 text-center text-sm text-gray-400">
              Belum ada campaign yang mendekati target
            </div>
            <div v-else>
              <div v-for="(c, i) in campaignHampirSelesai" :key="c.id_campaign" :class="['flex items-center gap-4 px-6 py-4', i < campaignHampirSelesai.length - 1 ? 'border-b border-stone-100' : '']">
                <div class="w-10 h-10 rounded-lg bg-[#F5F0E8] flex items-center justify-center flex-shrink-0">
                  <svg class="w-5 h-5 text-[#8B4513]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                  </svg>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-[#2C2C2C] truncate">{{ c.judul }}</p>
                  <p class="text-xs text-gray-400">{{ rupiah(c.dana_terkumpul) }} / {{ rupiah(c.target_dana) }}</p>
                </div>
                <div class="text-right">
                  <p class="text-sm font-bold text-green-600">{{ Math.round((c.dana_terkumpul / c.target_dana) * 100) }}%</p>
                  <p class="text-[10px] text-gray-400">target</p>
                </div>
              </div>
            </div>
          </div>
        </template>
      </div>
    </main>

    <AppFooter />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api/axios'
import Navbar from '@/components/shared/Navbar.vue'
import AppFooter from '@/components/shared/AppFooter.vue'

const loading = ref(true)
const error = ref('')
const stats = ref({
  total_campaign_aktif: 0,
  total_campaign_selesai: 0,
  total_campaign_review: 0,
  total_campaign_ditolak: 0,
  total_donatur_unik: 0,
  total_dana_terkumpul: 0,
  total_dana_dicairkan: 0,
  total_saldo_tersisa: 0,
})
const pencairanPending = ref([])
const campaignHampirSelesai = ref([])

function rupiah(val) {
  return 'Rp ' + (Number(val) || 0).toLocaleString('id-ID')
}

async function fetchDashboard() {
  loading.value = true
  error.value = ''
  try {
    const res = await api.get('/communities/dashboard')
    const data = res.data.data || res.data
    stats.value = { ...stats.value, ...(data.statistik || data) }
    pencairanPending.value = data.daftar_pencairan_pending || data.pencairan_pending || []
    campaignHampirSelesai.value = data.daftar_campaign_hampir_selesai || data.campaign_hampir_selesai || []
  } catch (e) {
    error.value = e.response?.data?.message || 'Gagal memuat dashboard'
  } finally {
    loading.value = false
  }
}

onMounted(fetchDashboard)
</script>
