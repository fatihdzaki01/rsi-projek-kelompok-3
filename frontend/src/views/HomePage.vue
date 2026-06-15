<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api/axios'
import CampaignCard from '@/components/campaign/CampaignCard.vue'
import Navbar from '@/components/shared/Navbar.vue'
import Footer from '@/components/shared/Footer.vue'

const router = useRouter()

const campaigns = ref([])
const communities = ref([])
const loading = ref({ campaigns: true, communities: true })
const stats = ref({ total_donasi: 0, total_campaign: 0, total_komunitas: 0 })

async function fetchCampaigns() {
  try {
    const res = await api.get('/campaigns/search', { params: { per_page: 6 } })
    const items = res.data.data.items || []
    campaigns.value = items.map(c => ({
      ...c,
      persentase_pencapaian: c.target_dana
        ? Math.min(100, Math.round((c.dana_terkumpul / c.target_dana) * 100))
        : 0,
    }))
    const total = res.data.data?.pagination?.total || 0
    stats.value.total_donasi = items.reduce((sum, c) => sum + (c.dana_terkumpul || 0), 0)
    stats.value.total_campaign = total
  } catch {
    campaigns.value = []
  } finally {
    loading.value.campaigns = false
  }
}

async function fetchCommunities() {
  try {
    const res = await api.get('/communities/search', { params: { per_page: 3 } })
    const items = res.data.data.items || []
    communities.value = items.map(c => ({
      id_komunitas: c.id_komunitas,
      nama_lembaga: c.nama_lembaga,
      deskripsi: c.deskripsi || '',
      foto_lembaga_url: c.foto_lembaga_url,
      kode_wilayah: c.kode_wilayah,
      total_follower: c.total_follower || 0,
      total_campaign_aktif: c.total_campaign_aktif || 0,
      total_dana_diterima: c.total_dana_diterima || 0,
    }))
    stats.value.total_komunitas = res.data.data?.pagination?.total || items.length
  } catch {
    communities.value = []
  } finally {
    loading.value.communities = false
  }
}

function goToCampaignDetail(id) {
  router.push(`/campaigns/${id}`)
}

function goToCommunityProfile(id) {
  router.push(`/communities/${id}`)
}

function formatStat(val) {
  if (val >= 1000000000) return (val / 1000000000).toFixed(1) + ' M'
  if (val >= 1000000) return (val / 1000000).toFixed(1) + ' Jt'
  if (val >= 1000) return (val / 1000).toFixed(0) + ' Rb'
  return String(val)
}

function formatRupiah(amount) {
  return 'Rp ' + (amount || 0).toLocaleString('id-ID')
}

onMounted(() => {
  fetchCampaigns()
  fetchCommunities()
})
</script>

<template>
  <div class="min-h-screen flex flex-col bg-[#F5F0E8]">
    <Navbar />

    <main class="flex-1">
      <!-- Hero Section -->
      <section class="bg-gradient-to-br from-[#8B4513] to-[#6b3410] text-white">
        <div class="max-w-5xl mx-auto px-6 py-16 text-center">
          <h1 class="text-4xl font-bold mb-4">Bersama Berbagi Kebaikan</h1>
          <p class="text-lg text-white/80 mb-8 max-w-2xl mx-auto">
            Temukan dan dukung campaign sosial yang dekat dengan hati Anda. Setiap donasi berarti bagi mereka yang membutuhkan.
          </p>
          <div class="flex items-center justify-center gap-8 text-sm">
            <div>
              <p class="text-2xl font-bold">{{ formatStat(stats.total_donasi) }}</p>
              <p class="text-white/60">Total Donasi</p>
            </div>
            <div class="w-px h-10 bg-white/20" />
            <div>
              <p class="text-2xl font-bold">{{ stats.total_campaign }}</p>
              <p class="text-white/60">Campaign Aktif</p>
            </div>
            <div class="w-px h-10 bg-white/20" />
            <div>
              <p class="text-2xl font-bold">{{ stats.total_komunitas }}</p>
              <p class="text-white/60">Komunitas</p>
            </div>
          </div>
        </div>
      </section>

      <div class="max-w-5xl mx-auto px-6 py-10">

        <!-- Campaign Section -->
        <section class="mb-14">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h2 class="text-xl font-bold text-[#1a2744]">Campaign Terbaru</h2>
              <p class="text-xs text-gray-500 mt-1">Temukan campaign yang sesuai dengan hatimu</p>
            </div>
            <router-link to="/campaigns" class="text-sm font-medium text-[#8B4513] hover:underline">
              Lihat Semua &rarr;
            </router-link>
          </div>

          <div v-if="loading.campaigns" class="flex justify-center py-12">
            <div class="w-8 h-8 border-2 border-[#8B4513] border-t-transparent rounded-full animate-spin" />
          </div>

          <div v-else-if="campaigns.length === 0" class="text-center py-12 text-sm text-gray-400">
            Belum ada campaign tersedia
          </div>

          <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <div
              v-for="campaign in campaigns"
              :key="campaign.id_campaign"
              @click="goToCampaignDetail(campaign.id_campaign)"
              class="cursor-pointer"
            >
              <CampaignCard :campaign="campaign" />
            </div>
          </div>
        </section>

        <!-- Community Section -->
        <section>
          <div class="flex items-center justify-between mb-6">
            <div>
              <h2 class="text-xl font-bold text-[#1a2744]">Komunitas Terdaftar</h2>
              <p class="text-xs text-gray-500 mt-1">Kenali komunitas yang berdedikasi di sekitarmu</p>
            </div>
            <router-link to="/communities" class="text-sm font-medium text-[#8B4513] hover:underline">
              Lihat Semua &rarr;
            </router-link>
          </div>

          <div v-if="loading.communities" class="flex justify-center py-12">
            <div class="w-8 h-8 border-2 border-[#8B4513] border-t-transparent rounded-full animate-spin" />
          </div>

          <div v-else-if="communities.length === 0" class="text-center py-12 text-sm text-gray-400">
            Belum ada komunitas terdaftar
          </div>

          <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <div
              v-for="community in communities"
              :key="community.id_komunitas"
              @click="goToCommunityProfile(community.id_komunitas)"
              class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden cursor-pointer"
            >
              <div class="h-36 bg-gradient-to-br from-[#c4783c] via-[#8B4513] to-[#5c2d0a] flex items-center justify-center text-white text-2xl font-bold">
                {{ community.nama_lembaga?.charAt(0) || 'K' }}
              </div>
              <div class="p-4">
                <h3 class="text-sm font-bold text-[#2C2C2C] mb-1">{{ community.nama_lembaga }}</h3>
                <p class="text-xs text-gray-500 line-clamp-2 mb-3">{{ community.deskripsi || 'Komunitas sosial yang bergerak di bidang kemanusiaan.' }}</p>
                <div class="grid grid-cols-3 gap-1 border-t border-stone-100 pt-3">
                  <div class="text-center">
                    <p class="text-sm font-bold text-gray-900">{{ community.total_follower }}</p>
                    <p class="text-[10px] text-gray-400">Follower</p>
                  </div>
                  <div class="text-center border-x border-stone-100">
                    <p class="text-sm font-bold text-gray-900">{{ community.total_campaign_aktif }}</p>
                    <p class="text-[10px] text-gray-400">Campaign</p>
                  </div>
                  <div class="text-center">
                    <p class="text-sm font-bold text-[#8B4513]">{{ formatRupiah(community.total_dana_diterima) }}</p>
                    <p class="text-[10px] text-gray-400">Terkumpul</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

      </div>
    </main>

    <Footer />
  </div>
</template>
