<template>
  <div class="min-h-screen bg-[#F5F0E8] flex flex-col">
    <Navbar />

    <main class="flex-1 py-8">
      <div class="max-w-3xl mx-auto px-6">

        <nav class="text-sm text-gray-500 mb-4 flex items-center gap-1">
          <router-link to="/" class="hover:text-[#8B4513] transition-colors">Beranda</router-link>
          <span>/</span>
          <router-link to="/communities/dashboard" class="hover:text-[#8B4513] transition-colors">Dashboard Komunitas</router-link>
          <span>/</span>
          <span class="text-[#2C2C2C] font-medium">Riwayat Pencairan</span>
        </nav>

        <div v-if="loading" class="flex flex-col items-center justify-center py-20">
          <div class="w-8 h-8 border-2 border-[#8B4513] border-t-transparent rounded-full animate-spin mb-3" />
          <p class="text-sm text-gray-400">Memuat data pencairan...</p>
        </div>

        <div v-else-if="error" class="bg-white rounded-xl shadow-sm p-8 text-center">
          <p class="text-sm text-red-500 mb-4">{{ error }}</p>
          <button @click="fetchData" class="px-5 py-2 bg-[#8B4513] text-white rounded-lg text-sm font-medium hover:bg-[#6b3410] transition-colors">Coba Lagi</button>
        </div>

        <template v-else-if="campaign">
          <div class="bg-white rounded-xl shadow-sm border border-stone-100 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-stone-100 flex items-center justify-between gap-3">
              <h1 class="text-sm font-bold text-[#2C2C2C]">{{ campaign.judul }}</h1>
              <router-link :to="`/communities/campaigns/${campaign.id_campaign}/withdrawals/create`" class="px-4 py-2 bg-[#8B4513] text-white rounded-lg text-sm font-medium hover:bg-[#6b3410] transition-colors whitespace-nowrap">+ Ajukan Pencairan</router-link>
            </div>
            <div class="px-6 py-5">
              <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                  <p class="text-xs text-gray-400">Dana Terkumpul</p>
                  <p class="text-lg font-bold text-[#2C2C2C]">{{ rupiah(campaign.dana_terkumpul) }}</p>
                </div>
                <div>
                  <p class="text-xs text-gray-400">Saldo Tersedia</p>
                  <p class="text-lg font-bold text-green-600">{{ rupiah(campaign.saldo_tersedia) }}</p>
                </div>
                <div>
                  <p class="text-xs text-gray-400">Saldo Terkunci</p>
                  <p class="text-lg font-bold text-yellow-600">{{ rupiah(campaign.saldo_terkunci) }}</p>
                </div>
                <div>
                  <p class="text-xs text-gray-400">Pengajuan Disetujui</p>
                  <p class="text-lg font-bold text-blue-600">{{ jumlahDisetujui }} / 5</p>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-xl shadow-sm border border-stone-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-stone-100">
              <h2 class="text-sm font-bold text-[#2C2C2C]">Riwayat Pencairan</h2>
            </div>
            <div class="px-6 py-5">
              <PencairanTimeline :items="pencairan" />
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
import PencairanTimeline from '@/components/campaigns/PencairanPerCampaign/PencairanTimeline.vue'

const route = useRoute()
const loading = ref(true)
const error = ref('')
const campaign = ref(null)
const pencairan = ref([])
const sisaKesempatan = ref(0)

const jumlahDisetujui = computed(() => pencairan.value.filter(p => p.status === 'disetujui' || p.status === 'selesai').length)

function rupiah(val) {
  return 'Rp ' + (Number(val) || 0).toLocaleString('id-ID')
}

async function fetchData() {
  loading.value = true
  error.value = ''
  try {
    const res = await api.get(`/communities/campaigns/${route.params.id}/withdrawals`)
    const data = res.data.data || res.data
    campaign.value = data.campaign || null
    pencairan.value = data.pencairan || []
    sisaKesempatan.value = data.sisa_kesempatan || 0
  } catch (e) {
    error.value = e.response?.data?.message || 'Gagal memuat data pencairan'
  } finally {
    loading.value = false
  }
}

onMounted(fetchData)
</script>
