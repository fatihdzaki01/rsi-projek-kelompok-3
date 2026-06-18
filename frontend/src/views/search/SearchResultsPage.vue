<template>
  <div class="min-h-screen bg-[#F5F0E8] flex flex-col">
    <Navbar />

    <main class="flex-1 py-8">
      <div class="max-w-6xl mx-auto px-6">

        <nav class="text-sm text-gray-500 mb-4 flex items-center gap-1">
          <router-link to="/" class="hover:text-[#8B4513] transition-colors">Beranda</router-link>
          <span>/</span>
          <span class="text-[#2C2C2C] font-medium">Pencarian</span>
        </nav>

        <div class="mb-6">
          <div class="relative max-w-xl">
            <svg class="w-4 h-4 text-gray-400 absolute left-4 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
            </svg>
            <input
              v-model="query"
              @input="handleSearch"
              type="text"
              placeholder="Cari campaign atau komunitas..."
              class="w-full pl-10 pr-4 py-3 bg-white rounded-xl border border-stone-200 text-sm text-[#2C2C2C] focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-transparent shadow-sm"
            />
          </div>
        </div>

        <div class="flex items-center gap-2 mb-6">
          <button @click="activeTab = 'campaigns'" :class="['px-4 py-1.5 rounded-full text-sm font-medium transition-colors', activeTab === 'campaigns' ? 'bg-[#8B4513] text-white' : 'bg-white text-gray-500 hover:text-gray-700 border border-stone-200']">
            Campaign
          </button>
          <button @click="activeTab = 'communities'" :class="['px-4 py-1.5 rounded-full text-sm font-medium transition-colors', activeTab === 'communities' ? 'bg-[#8B4513] text-white' : 'bg-white text-gray-500 hover:text-gray-700 border border-stone-200']">
            Komunitas
          </button>
        </div>

        <div v-if="loading" class="flex flex-col items-center justify-center py-20">
          <div class="w-8 h-8 border-2 border-[#8B4513] border-t-transparent rounded-full animate-spin mb-3" />
          <p class="text-sm text-gray-400">Mencari...</p>
        </div>

        <div v-else-if="!query" class="flex flex-col items-center justify-center py-20 text-center">
          <div class="w-16 h-16 rounded-full bg-white flex items-center justify-center mb-4 shadow-sm">
            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
            </svg>
          </div>
          <p class="text-sm text-gray-400">Cari campaign atau komunitas</p>
        </div>

        <template v-else-if="activeTab === 'campaigns'">
          <div v-if="campaigns.length === 0" class="flex flex-col items-center justify-center py-20 text-center">
            <p class="text-sm text-gray-400">Tidak ditemukan campaign untuk "{{ query }}"</p>
          </div>
          <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="c in campaigns" :key="c.id_campaign" @click="router.push(`/campaigns/${c.id_campaign}`)" class="bg-white rounded-xl shadow-sm border border-stone-100 overflow-hidden cursor-pointer hover:shadow-md transition-shadow">
              <div class="h-36 bg-stone-100 flex items-center justify-center overflow-hidden">
                <img v-if="c.foto_campaign_url" :src="c.foto_campaign_url" :alt="c.judul" class="w-full h-full object-cover" />
                <svg v-else class="w-8 h-8 text-stone-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                  <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/>
                </svg>
              </div>
              <div class="p-4">
                <p class="text-sm font-medium text-[#2C2C2C] truncate">{{ c.judul }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ c.nama_komunitas || '' }}</p>
                <div class="mt-2 flex items-center justify-between">
                  <p class="text-xs font-semibold text-[#8B4513]">{{ formatRupiah(c.dana_terkumpul) }}</p>
                  <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', statusClass(c.status)]">{{ c.status_label || c.status }}</span>
                </div>
              </div>
            </div>
          </div>
          <div v-if="campaignPagination.last_page > 1" class="flex justify-center mt-6">
            <PaginationBar :pagination="campaignPagination" @page-change="loadCampaigns" />
          </div>
        </template>

        <template v-else-if="activeTab === 'communities'">
          <div v-if="communities.length === 0" class="flex flex-col items-center justify-center py-20 text-center">
            <p class="text-sm text-gray-400">Tidak ditemukan komunitas untuk "{{ query }}"</p>
          </div>
          <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="c in communities" :key="c.id_komunitas" @click="router.push(`/communities/${c.id_komunitas}`)" class="bg-white rounded-xl shadow-sm border border-stone-100 p-4 cursor-pointer hover:shadow-md transition-shadow">
              <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-[#F5F0E8] flex items-center justify-center text-sm font-bold text-[#8B4513] uppercase">
                  {{ (c.nama_lembaga || '?').charAt(0) }}
                </div>
                <div class="min-w-0">
                  <p class="text-sm font-medium text-[#2C2C2C] truncate">{{ c.nama_lembaga }}</p>
                  <p class="text-xs text-gray-400">{{ c.total_campaign || 0 }} campaign</p>
                </div>
              </div>
              <p class="text-xs text-gray-500 line-clamp-2">{{ c.deskripsi || 'Tidak ada deskripsi' }}</p>
            </div>
          </div>
          <div v-if="communityPagination.last_page > 1" class="flex justify-center mt-6">
            <PaginationBar :pagination="communityPagination" @page-change="loadCommunities" />
          </div>
        </template>
      </div>
    </main>

    <AppFooter />
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '@/api/axios'
import Navbar from '@/components/shared/Navbar.vue'
import AppFooter from '@/components/shared/AppFooter.vue'
import PaginationBar from '@/components/ui/PaginationBar.vue'

const router = useRouter()
const route = useRoute()

const query = ref(route.query.q || '')
const activeTab = ref('campaigns')
const loading = ref(false)

const campaigns = ref([])
const campaignPagination = ref({ current_page: 1, last_page: 1, total: 0, per_page: 12 })

const communities = ref([])
const communityPagination = ref({ current_page: 1, last_page: 1, total: 0, per_page: 12 })

function formatRupiah(val) {
  const num = Number(val) || 0
  return 'Rp ' + num.toLocaleString('id-ID')
}

function statusClass(status) {
  const map = { aktif: 'text-green-700 bg-green-50', selesai: 'text-blue-700 bg-blue-50', menunggu: 'text-yellow-700 bg-yellow-50' }
  return map[status] || 'text-gray-500 bg-gray-100'
}

let debounceTimer = null
function handleSearch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    if (activeTab.value === 'campaigns') loadCampaigns()
    else loadCommunities()
  }, 400)
}

async function loadCampaigns(page = 1) {
  if (!query.value.trim()) return
  loading.value = true
  try {
    const res = await api.get('/campaigns/search', {
      params: { keyword: query.value, page, per_page: 12 },
    })
    const data = res.data.data || res.data
    campaigns.value = data.items || data.data || []
    campaignPagination.value = data.pagination || data.meta || { current_page: 1, last_page: 1, total: 0, per_page: 12 }
  } catch (e) {
    campaigns.value = []
  } finally {
    loading.value = false
  }
}

async function loadCommunities(page = 1) {
  if (!query.value.trim()) return
  loading.value = true
  try {
    const res = await api.get('/communities/search', {
      params: { keyword: query.value, page, per_page: 12 },
    })
    const data = res.data.data || res.data
    communities.value = data.items || data.data || []
    communityPagination.value = data.pagination || data.meta || { current_page: 1, last_page: 1, total: 0, per_page: 12 }
  } catch (e) {
    communities.value = []
  } finally {
    loading.value = false
  }
}

watch(activeTab, () => {
  if (query.value.trim()) {
    if (activeTab.value === 'campaigns') loadCampaigns()
    else loadCommunities()
  }
})

watch(() => route.query.q, (newQ) => {
  if (newQ && newQ !== query.value) {
    query.value = newQ
    if (activeTab.value === 'campaigns') loadCampaigns()
    else loadCommunities()
  }
})

onMounted(() => {
  if (query.value) {
    if (activeTab.value === 'campaigns') loadCampaigns()
    else loadCommunities()
  }
})
</script>
