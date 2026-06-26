<template>
  <div class="min-h-screen bg-[#F5F0E8] flex flex-col">
    <Navbar />
    <main class="flex-1 py-8">
      <div class="max-w-5xl mx-auto px-6">
        <nav class="text-sm text-gray-500 mb-4 flex items-center gap-1">
          <router-link to="/" class="hover:text-[#8B4513] transition-colors">Beranda</router-link>
          <span>/</span>
          <router-link to="/communities/dashboard" class="hover:text-[#8B4513] transition-colors">Dashboard</router-link>
          <span>/</span>
          <span class="text-[#2C2C2C] font-medium">Analisis Campaign</span>
        </nav>

        <div v-if="loading" class="flex flex-col items-center justify-center py-20">
          <div class="w-8 h-8 border-2 border-[#8B4513] border-t-transparent rounded-full animate-spin mb-3" />
          <p class="text-sm text-gray-400">Memuat data...</p>
        </div>

        <div v-else-if="error" class="bg-white rounded-xl shadow-sm p-8 text-center">
          <p class="text-sm text-red-500 mb-4">{{ error }}</p>
          <button @click="fetchData" class="px-5 py-2 bg-[#8B4513] text-white rounded-lg text-sm font-medium hover:bg-[#6b3410]">Coba Lagi</button>
        </div>

        <template v-else-if="data">
          <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <div class="flex items-start justify-between gap-3 mb-4">
              <div>
                <h1 class="text-xl font-bold text-[#2C2C2C]">{{ data.campaign?.judul }}</h1>
                <span :class="['inline px-2 py-0.5 rounded-full text-xs font-medium mt-1.5',
                  data.campaign?.status === 'aktif' ? 'bg-green-100 text-green-700' :
                  data.campaign?.status === 'selesai' ? 'bg-blue-100 text-blue-700' :
                  'bg-gray-100 text-gray-600']">{{ data.campaign?.status }}</span>
              </div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
              <div><span class="text-xs text-gray-400 block">Target Dana</span><span class="text-gray-800 font-medium">{{ rupiah(data.campaign?.target_dana) }}</span></div>
              <div><span class="text-xs text-gray-400 block">Dana Terkumpul</span><span class="text-green-600 font-medium">{{ rupiah(data.campaign?.dana_terkumpul) }}</span></div>
              <div><span class="text-xs text-gray-400 block">Saldo Tersedia</span><span class="text-gray-800 font-medium">{{ rupiah(data.campaign?.saldo_tersedia) }}</span></div>
              <div><span class="text-xs text-gray-400 block">Target Penerima</span><span class="text-gray-800 font-medium">{{ data.jumlah_penerima || '-' }}</span></div>
            </div>
          </div>

          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-stone-100 p-4 text-center">
              <p class="text-xl font-bold text-[#2C2C2C]">{{ data.jumlah_pelapor || 0 }}</p>
              <p class="text-xs text-gray-400 mt-1">Jumlah Pelapor</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-stone-100 p-4 text-center">
              <p class="text-xl font-bold text-[#2C2C2C]">{{ data.jumlah_pencairan || 0 }}x</p>
              <p class="text-xs text-gray-400 mt-1">Jumlah Pencairan</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-stone-100 p-4 text-center">
              <p class="text-xl font-bold text-[#8B4513]">{{ rupiah(data.total_dicairkan) }}</p>
              <p class="text-xs text-gray-400 mt-1">Total Dicairkan</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-stone-100 p-4 text-center">
              <p class="text-xl font-bold text-green-600">{{ rupiah(data.campaign?.saldo_tersedia) }}</p>
              <p class="text-xs text-gray-400 mt-1">Saldo Tersisa</p>
            </div>
          </div>

          <div v-if="data.tren_penerimaan && data.tren_penerimaan.length > 0" class="bg-white rounded-xl shadow-sm border border-stone-100 p-5 mb-6">
            <h2 class="text-sm font-bold text-[#2C2C2C] mb-4">Tren Penerimaan Donasi (6 Bulan)</h2>
            <div class="flex items-end gap-1 h-40">
              <div v-for="(t, i) in data.tren_penerimaan" :key="i" class="flex-1 flex flex-col items-center gap-1 h-full justify-end">
                <span class="text-[10px] font-medium text-gray-600">{{ formatTrenRupiah(t.total) }}</span>
                <div class="w-full max-w-[48px] rounded-t" :style="{ height: barHeight(t.total, maxTren) + '%', backgroundColor: '#8B4513', minHeight: t.total > 0 ? '4px' : '0' }" :title="`${t.bulan}: ${rupiah(t.total)}`" />
                <span class="text-[10px] text-gray-400">{{ formatBulan(t.bulan) }}</span>
              </div>
            </div>
          </div>

          <div v-if="data.komposisi_metode && data.komposisi_metode.length > 0" class="bg-white rounded-xl shadow-sm border border-stone-100 p-5 mb-6">
            <h2 class="text-sm font-bold text-[#2C2C2C] mb-4">Komposisi Metode Pembayaran</h2>
            <div class="space-y-2.5">
              <div v-for="(m, i) in data.komposisi_metode" :key="i" class="flex items-center gap-3">
                <span class="text-xs text-gray-600 w-20 capitalize">{{ m.metode }}</span>
                <div class="flex-1 h-5 bg-stone-100 rounded-full overflow-hidden">
                  <div class="h-full rounded-full bg-[#8B4513] transition-all duration-500" :style="{ width: barPct(m.total, maxKomposisi) + '%' }" />
                </div>
                <span class="text-xs font-medium text-gray-700 w-24 text-right">{{ rupiah(m.total) }}</span>
                <span class="text-xs text-gray-400 w-10 text-right">{{ m.jumlah }}x</span>
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
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/api/axios'
import Navbar from '@/components/shared/Navbar.vue'
import AppFooter from '@/components/shared/AppFooter.vue'

const route = useRoute()
const data = ref(null)
const loading = ref(true)
const error = ref('')

function rupiah(val) { return 'Rp ' + (Number(val) || 0).toLocaleString('id-ID') }

function formatTrenRupiah(val) {
  if (val >= 1000000) return (val / 1000000).toFixed(1) + 'M'
  if (val >= 1000) return (val / 1000).toFixed(0) + 'K'
  return val.toString()
}

function formatBulan(ym) {
  const [y, m] = (ym || '').split('-')
  const bulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des']
  return bulan[parseInt(m) - 1] || ym
}

const maxTren = computed(() => {
  const arr = data.value?.tren_penerimaan || []
  return Math.max(1, ...arr.map(t => t.total))
})

const maxKomposisi = computed(() => {
  const arr = data.value?.komposisi_metode || []
  return Math.max(1, ...arr.map(m => m.total))
})

function barHeight(val, max) { return Math.round((val / max) * 100) || 0 }
function barPct(val, max) { return (val / max) * 100 }

async function fetchData() {
  loading.value = true
  error.value = ''
  try {
    const res = await api.get(`/communities/campaigns/${route.params.id}/donation-stats`)
    data.value = res.data.data || res.data
  } catch (e) {
    error.value = e.response?.data?.message || 'Gagal memuat data analisis'
  } finally {
    loading.value = false
  }
}

onMounted(fetchData)
</script>
