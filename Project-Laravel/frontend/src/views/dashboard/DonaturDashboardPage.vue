<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/api/axios'
import Navbar from '@/components/shared/Navbar.vue'
import AppFooter from '@/components/shared/AppFooter.vue'

const loading = ref(true)
const error = ref('')
const profile = ref(null)
const donations = ref([])

const totalDonasi = computed(() => donations.value.length)
const totalNominal = computed(() => donations.value.reduce((sum, d) => sum + (d.nominal || 0), 0))
const donasiBerhasil = computed(() => donations.value.filter(d => d.status_pembayaran === 'berhasil'))

function rupiah(val) {
  return 'Rp ' + (Number(val) || 0).toLocaleString('id-ID')
}

function tgl(d) {
  if (!d) return '-'
  return new Date(d).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' })
}

async function fetchDashboard() {
  loading.value = true
  error.value = ''
  try {
    const [profileRes, donasiRes] = await Promise.all([
      api.get('/users/me'),
      api.get('/users/me/donations').catch(() => ({ data: { data: [] } })),
    ])
    profile.value = profileRes.data.data
    donations.value = donasiRes.data.data || []
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
    <Navbar />

    <main class="flex-1 py-8">
      <div class="max-w-5xl mx-auto px-6">

        <nav class="text-sm text-gray-500 mb-4 flex items-center gap-1">
          <router-link to="/" class="hover:text-[#8B4513] transition-colors">Beranda</router-link>
          <span>/</span>
          <span class="text-[#2C2C2C] font-medium">Dashboard Saya</span>
        </nav>

        <div v-if="loading" class="flex flex-col items-center justify-center py-20">
          <div class="w-8 h-8 border-2 border-[#8B4513] border-t-transparent rounded-full animate-spin mb-3" />
          <p class="text-sm text-gray-400">Memuat dashboard...</p>
        </div>

        <div v-else-if="error" class="bg-white rounded-xl shadow-sm p-8 text-center">
          <p class="text-sm text-red-500 mb-4">{{ error }}</p>
          <button @click="fetchDashboard" class="px-5 py-2 bg-[#8B4513] text-white rounded-lg text-sm font-medium hover:bg-[#6b3410] transition-colors">Coba Lagi</button>
        </div>

        <template v-else>
          <!-- Profile Header -->
          <div class="bg-white rounded-xl shadow-sm border border-stone-100 p-6 mb-6">
            <div class="flex items-center gap-4">
              <div class="w-14 h-14 rounded-full bg-[#F5F0E8] flex items-center justify-center flex-shrink-0 overflow-hidden">
                <img v-if="profile?.foto_profil_url" :src="profile.foto_profil_url" alt="" class="w-full h-full object-cover" />
                <span v-else class="text-lg font-bold text-[#8B4513]">{{ (profile?.nama_lengkap || profile?.username || '?').charAt(0).toUpperCase() }}</span>
              </div>
              <div class="flex-1 min-w-0">
                <h1 class="text-lg font-bold text-[#2C2C2C] truncate">{{ profile?.nama_lengkap || profile?.username || 'Donatur' }}</h1>
                <p class="text-sm text-gray-400">{{ profile?.email }}</p>
              </div>
              <RouterLink to="/profile" class="text-xs text-[#8B4513] hover:underline shrink-0">Edit Profil</RouterLink>
            </div>
          </div>

          <!-- Stats -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-stone-100 p-5">
              <p class="text-xs text-gray-400 mb-1">Total Donasi</p>
              <p class="text-xl font-bold text-[#2C2C2C]">{{ totalDonasi }}</p>
              <p class="text-[10px] text-gray-400 mt-1">Semua transaksi</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-stone-100 p-5">
              <p class="text-xs text-gray-400 mb-1">Donasi Berhasil</p>
              <p class="text-xl font-bold text-green-600">{{ donasiBerhasil.length }}</p>
              <p class="text-[10px] text-gray-400 mt-1">Pembayaran sukses</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-stone-100 p-5">
              <p class="text-xs text-gray-400 mb-1">Total Nominal</p>
              <p class="text-xl font-bold text-[#2C2C2C]">{{ rupiah(totalNominal) }}</p>
              <p class="text-[10px] text-gray-400 mt-1">Nominal terkumpul</p>
            </div>
          </div>

          <!-- Quick Links -->
          <div class="flex flex-wrap gap-2 mb-6">
            <RouterLink to="/campaigns" class="px-4 py-2 bg-[#8B4513] text-white rounded-lg text-sm font-medium hover:bg-[#6b3410] transition-colors">Jelajahi Campaign</RouterLink>
            <RouterLink to="/donations/history" class="px-4 py-2 bg-white text-[#8B4513] border border-[#8B4513] rounded-lg text-sm font-medium hover:bg-stone-50 transition-colors">Riwayat Donasi</RouterLink>
            <RouterLink to="/communities" class="px-4 py-2 border border-stone-200 text-[#2C2C2C] rounded-lg text-sm font-medium hover:bg-stone-50 transition-colors">Lihat Komunitas</RouterLink>
          </div>

          <!-- Recent Donations -->
          <div class="bg-white rounded-xl shadow-sm border border-stone-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-stone-100 flex items-center justify-between">
              <h2 class="text-sm font-bold text-[#2C2C2C]">Donasi Terbaru</h2>
              <RouterLink to="/donations/history" class="text-xs text-[#8B4513] hover:underline">Lihat Semua</RouterLink>
            </div>
            <div v-if="donations.length === 0" class="px-6 py-10 text-center text-sm text-gray-400">
              Belum ada donasi. Mulai donasi sekarang!
            </div>
            <div v-else>
              <div v-for="(d, i) in donations.slice(0, 5)" :key="d.id_donasi" :class="['flex items-center gap-4 px-6 py-4', i < Math.min(donations.length, 5) - 1 ? 'border-b border-stone-100' : '']">
                <div class="w-10 h-10 rounded-lg bg-[#F5F0E8] flex items-center justify-center flex-shrink-0">
                  <svg class="w-5 h-5 text-[#8B4513]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                  </svg>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-[#2C2C2C] truncate">{{ d.judul_campaign || 'Campaign' }}</p>
                  <p class="text-xs text-gray-400">{{ rupiah(d.nominal) }} &middot; {{ d.metode_pembayaran }}</p>
                </div>
                <div class="text-right">
                  <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', d.status_pembayaran === 'berhasil' ? 'text-green-700 bg-green-50' : d.status_pembayaran === 'pending' ? 'text-yellow-700 bg-yellow-50' : 'text-red-700 bg-red-50']">
                    {{ d.status_pembayaran }}
                  </span>
                  <p class="text-[10px] text-gray-400 mt-0.5">{{ tgl(d.created_at) }}</p>
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
