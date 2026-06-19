<template>
  <div class="min-h-screen bg-[#F5F0E8] flex flex-col">
    <Navbar />

    <main class="flex-1 py-8">
      <div class="max-w-3xl mx-auto px-6">

        <nav class="text-sm text-gray-500 mb-4 flex items-center gap-1">
          <router-link to="/" class="hover:text-[#8B4513] transition-colors">Beranda</router-link>
          <span>/</span>
          <span class="text-[#2C2C2C] font-medium">Pencairan</span>
        </nav>

        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
          <h1 class="text-2xl font-bold text-[#2C2C2C]">Pilih Campaign</h1>
        </div>

        <div v-if="loading" class="flex flex-col items-center justify-center py-20">
          <div class="w-8 h-8 border-2 border-[#8B4513] border-t-transparent rounded-full animate-spin mb-3" />
          <p class="text-sm text-gray-400">Memuat daftar campaign...</p>
        </div>

        <div v-else-if="error" class="bg-white rounded-xl shadow-sm p-8 text-center">
          <p class="text-sm text-red-500 mb-4">{{ error }}</p>
          <button @click="fetchCampaigns" class="px-5 py-2 bg-[#8B4513] text-white rounded-lg text-sm font-medium hover:bg-[#6b3410] transition-colors">Coba Lagi</button>
        </div>

        <div v-else-if="campaigns.length === 0" class="bg-white rounded-xl shadow-sm p-8 text-center">
          <p class="text-sm text-gray-400">Belum ada campaign</p>
          <router-link to="/communities/campaigns/create" class="inline-block mt-3 px-5 py-2 bg-[#8B4513] text-white rounded-lg text-sm font-medium hover:bg-[#6b3410] transition-colors">+ Campaign Baru</router-link>
        </div>

        <div v-else class="bg-white rounded-xl shadow-sm border border-stone-100 overflow-hidden divide-y divide-stone-100">
          <CampaignCard
            v-for="c in campaigns"
            :key="c.id_campaign"
            :campaign="c"
            @click="goToPencairan"
          />
        </div>
      </div>
    </main>

    <AppFooter />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api/axios'
import Navbar from '@/components/shared/Navbar.vue'
import AppFooter from '@/components/shared/AppFooter.vue'
import CampaignCard from '@/components/campaigns/PencairanPerCampaign/CampaignCard.vue'

const router = useRouter()
const loading = ref(true)
const error = ref('')
const campaigns = ref([])

function goToPencairan(campaign) {
  router.push(`/communities/campaigns/${campaign.id_campaign}/withdrawals`)
}

async function fetchCampaigns() {
  loading.value = true
  error.value = ''
  try {
    const res = await api.get('/communities/campaigns', { params: { per_page: 50 } })
    const list = res.data.data || res.data
    campaigns.value = Array.isArray(list) ? list : []
  } catch (e) {
    error.value = e.response?.data?.message || 'Gagal memuat daftar campaign'
  } finally {
    loading.value = false
  }
}

onMounted(fetchCampaigns)
</script>
