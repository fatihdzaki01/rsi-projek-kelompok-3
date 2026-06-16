<script setup>
import { onMounted, ref, computed } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import api from '@/api/axios'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const auth = useAuthStore()

const loading = ref(true)
const error = ref('')

const summary = ref({ total_users: 0, total_komunitas: 0, total_campaign: 0, total_donasi: 0, total_donasi_berhasil: 0, total_nominal: 0 })
const statusBreakdown = ref({})
const recentDonations = ref([])
const recentCampaigns = ref([])
const sidebarOpen = ref(false)

const statusLabels = { aktif: 'Aktif', selesai: 'Selesai', ditolak: 'Ditolak', menunggu_review: 'Review', dinonaktifkan: 'Nonaktif' }
const statusColors = { aktif: 'bg-green-100 text-green-700', selesai: 'bg-blue-100 text-blue-700', ditolak: 'bg-red-100 text-red-700', menunggu_review: 'bg-yellow-100 text-yellow-700', dinonaktifkan: 'bg-gray-100 text-gray-500' }

const totalStatusCampaign = computed(() => {
  const entries = Object.entries(statusBreakdown.value)
  return entries.map(([k, v]) => ({ status: k, total: v, label: statusLabels[k] || k, color: statusColors[k] || 'bg-gray-100 text-gray-500' }))
})

function rupiah(n) {
  return 'Rp ' + (Number(n) || 0).toLocaleString('id-ID')
}

function tgl(d) {
  if (!d) return '-'
  return new Date(d).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' })
}

async function fetchDashboard() {
  loading.value = true
  error.value = ''
  try {
    const res = await api.get('/superadmin/dashboard')
    const data = res.data.data
    summary.value = data.summary || summary.value
    statusBreakdown.value = data.campaign_status_breakdown || {}
    recentDonations.value = data.recent_donations || []
    recentCampaigns.value = data.recent_campaigns || []
  } catch (e) {
    error.value = e.response?.data?.message || 'Gagal memuat dashboard'
  } finally {
    loading.value = false
  }
}

onMounted(fetchDashboard)
</script>

<template>
  <div class="min-h-screen bg-[#F5F0E8] flex flex-col">
    <header class="bg-white border-b border-stone-200 sticky top-0 z-40">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 flex items-center justify-between h-14">
        <div class="flex items-center gap-3">
          <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-1.5 rounded-lg hover:bg-stone-100 text-stone-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
          </button>
          <RouterLink to="/" class="text-lg font-bold text-[#8B4513] tracking-tight">BERBAGIVE</RouterLink>
          <span class="hidden sm:inline text-[10px] uppercase tracking-widest text-stone-400 bg-stone-100 px-2 py-0.5 rounded-full">Superadmin</span>
        </div>
        <button @click="auth.logout(); router.push('/login')" class="text-xs text-stone-500 hover:text-red-600 transition-colors font-medium">Logout</button>
      </div>
    </header>

    <div class="flex flex-1 max-w-7xl mx-auto w-full">
      <!-- Sidebar -->
      <aside :class="['w-56 bg-white border-r border-stone-200 shrink-0 lg:block', sidebarOpen ? 'fixed inset-0 z-50 block' : 'hidden']">
        <div v-if="sidebarOpen" class="lg:hidden flex items-center justify-between px-4 h-14 border-b border-stone-200">
          <span class="font-bold text-[#8B4513]">Menu</span>
          <button @click="sidebarOpen = false" class="p-1.5 rounded-lg hover:bg-stone-100 text-stone-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>
        <nav class="p-3 space-y-0.5 text-sm">
          <RouterLink to="/dashboard" class="flex items-center gap-2.5 px-3 py-2 rounded-lg bg-[#8B4513] text-white font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Dashboard
          </RouterLink>
          <RouterLink to="/dashboard/community-registrations" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-stone-600 hover:bg-stone-50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Registrasi
          </RouterLink>
          <RouterLink to="/campaigns/approval" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-stone-600 hover:bg-stone-50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            Approval Campaign
          </RouterLink>
          <RouterLink to="/disbursements" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-stone-600 hover:bg-stone-50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            Pencairan
          </RouterLink>
          <RouterLink to="/dashboard/donors" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-stone-600 hover:bg-stone-50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Donatur
          </RouterLink>
          <RouterLink to="/dashboard/communities" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-stone-600 hover:bg-stone-50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21v-2a4 4 0 00-4-4H9a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            Komunitas
          </RouterLink>
          <RouterLink to="/dashboard/campaign-categories" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-stone-600 hover:bg-stone-50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
            Kategori
          </RouterLink>
          <RouterLink to="/dashboard/bank-account-changes" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-stone-600 hover:bg-stone-50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
            Verif Rekening
          </RouterLink>
          <RouterLink to="/dashboard/campaign-reports" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-stone-600 hover:bg-stone-50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            Laporan
          </RouterLink>
          <RouterLink to="/dashboard/document-templates" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-stone-600 hover:bg-stone-50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Template Dok
          </RouterLink>
          <RouterLink to="/dashboard/analytics" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-stone-600 hover:bg-stone-50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            Analitik
          </RouterLink>
          <RouterLink to="/dashboard/audit-logs" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-stone-600 hover:bg-stone-50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Audit Log
          </RouterLink>
        </nav>
      </aside>

      <!-- Overlay -->
      <div v-if="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-black/20 z-40 lg:hidden" />

      <!-- Main Content -->
      <main class="flex-1 min-w-0 p-4 sm:p-6 lg:p-8">
        <div v-if="loading" class="flex flex-col items-center py-20">
          <div class="w-8 h-8 border-2 border-[#8B4513] border-t-transparent rounded-full animate-spin mb-3" />
          <p class="text-sm text-gray-400">Memuat dashboard...</p>
        </div>

        <div v-else-if="error" class="bg-white rounded-xl shadow-sm p-8 text-center">
          <p class="text-sm text-red-500 mb-4">{{ error }}</p>
          <button @click="fetchDashboard" class="px-5 py-2 bg-[#8B4513] text-white rounded-lg text-sm font-medium hover:bg-[#6b3410] transition-colors">Coba Lagi</button>
        </div>

        <template v-else>
          <div class="mb-6">
            <h1 class="text-xl font-bold text-[#2C2C2C]">Dashboard</h1>
            <p class="text-sm text-gray-500 mt-0.5">Ringkasan platform Berbagive</p>
          </div>

          <!-- Summary Cards -->
          <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-stone-100 p-4 sm:p-5">
              <p class="text-xs text-gray-400 mb-1">Total User</p>
              <p class="text-xl sm:text-2xl font-bold text-[#2C2C2C]">{{ (summary.total_users || 0).toLocaleString('id-ID') }}</p>
              <p class="text-[10px] text-gray-400 mt-1">{{ (summary.total_komunitas || 0).toLocaleString('id-ID') }} komunitas</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-stone-100 p-4 sm:p-5">
              <p class="text-xs text-gray-400 mb-1">Total Campaign</p>
              <p class="text-xl sm:text-2xl font-bold text-[#2C2C2C]">{{ (summary.total_campaign || 0).toLocaleString('id-ID') }}</p>
              <p class="text-[10px] text-gray-400 mt-1">Semua campaign</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-stone-100 p-4 sm:p-5">
              <p class="text-xs text-gray-400 mb-1">Total Donasi</p>
              <p class="text-xl sm:text-2xl font-bold text-[#2C2C2C]">{{ (summary.total_donasi_berhasil || summary.total_donasi || 0).toLocaleString('id-ID') }}</p>
              <p class="text-[10px] text-gray-400 mt-1">Transaksi berhasil</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-stone-100 p-4 sm:p-5">
              <p class="text-xs text-gray-400 mb-1">Total Nominal</p>
              <p class="text-xl sm:text-2xl font-bold text-[#2C2C2C]">{{ rupiah(summary.total_nominal || 0) }}</p>
              <p class="text-[10px] text-gray-400 mt-1">Dana terkumpul</p>
            </div>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Campaign Status Breakdown -->
            <div class="lg:col-span-1">
              <div class="bg-white rounded-xl shadow-sm border border-stone-100 p-5">
                <h2 class="text-sm font-bold text-[#2C2C2C] mb-4">Status Campaign</h2>
                <div class="space-y-2.5">
                  <div v-for="item in totalStatusCampaign" :key="item.status" class="flex items-center justify-between">
                    <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', item.color]">{{ item.label }}</span>
                    <span class="text-sm font-semibold text-[#2C2C2C]">{{ item.total }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Recent Campaigns -->
            <div class="lg:col-span-2">
              <div class="bg-white rounded-xl shadow-sm border border-stone-100">
                <div class="px-5 py-4 border-b border-stone-100 flex items-center justify-between">
                  <h2 class="text-sm font-bold text-[#2C2C2C]">Campaign Terbaru</h2>
                  <RouterLink to="/campaigns/approval" class="text-xs text-[#8B4513] hover:underline">Lihat Semua</RouterLink>
                </div>
                <div v-if="recentCampaigns.length === 0" class="px-5 py-10 text-center text-sm text-gray-400">
                  Belum ada campaign
                </div>
                <div v-else>
                  <div v-for="(c, i) in recentCampaigns" :key="c.id_campaign" :class="['flex items-center gap-3 px-5 py-3.5', i < recentCampaigns.length - 1 ? 'border-b border-stone-100' : '']">
                    <div class="w-8 h-8 rounded-lg bg-[#F5F0E8] flex items-center justify-center flex-shrink-0">
                      <svg class="w-4 h-4 text-[#8B4513]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-medium text-[#2C2C2C] truncate">{{ c.judul }}</p>
                      <p class="text-xs text-gray-400">oleh {{ c.nama_lembaga }} &middot; {{ rupiah(c.dana_terkumpul) }} / {{ rupiah(c.target_dana) }}</p>
                    </div>
                    <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', statusColors[c.status] || 'bg-gray-100 text-gray-500']">{{ statusLabels[c.status] || c.status }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Recent Donations -->
          <div class="bg-white rounded-xl shadow-sm border border-stone-100 mt-6">
            <div class="px-5 py-4 border-b border-stone-100">
              <h2 class="text-sm font-bold text-[#2C2C2C]">Donasi Terbaru</h2>
            </div>
            <div v-if="recentDonations.length === 0" class="px-5 py-10 text-center text-sm text-gray-400">
              Belum ada donasi
            </div>
            <div v-else class="overflow-x-auto">
              <table class="w-full text-sm">
                <thead>
                  <tr class="border-b border-stone-100 text-left text-xs text-gray-400 uppercase tracking-wider">
                    <th class="px-5 py-3 font-medium">Donatur</th>
                    <th class="px-5 py-3 font-medium">Campaign</th>
                    <th class="px-5 py-3 font-medium">Nominal</th>
                    <th class="px-5 py-3 font-medium">Metode</th>
                    <th class="px-5 py-3 font-medium">Tanggal</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(d, i) in recentDonations" :key="i" class="border-b border-stone-50 hover:bg-stone-50 transition-colors">
                    <td class="px-5 py-3.5 text-[#2C2C2C] font-medium">{{ d.nama_donatur || d.nama_lengkap || 'Anonim' }}</td>
                    <td class="px-5 py-3.5 text-gray-600">{{ d.judul_campaign || d.judul || '-' }}</td>
                    <td class="px-5 py-3.5 text-[#2C2C2C] font-medium">{{ rupiah(d.nominal || d.nominal_donasi) }}</td>
                    <td class="px-5 py-3.5">
                      <span class="text-xs bg-stone-100 text-stone-600 px-2 py-0.5 rounded-full">{{ d.metode_pembayaran || '-' }}</span>
                    </td>
                    <td class="px-5 py-3.5 text-gray-500 text-xs">{{ tgl(d.tanggal_transaksi || d.created_at) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </template>
      </main>
    </div>
  </div>
</template>
