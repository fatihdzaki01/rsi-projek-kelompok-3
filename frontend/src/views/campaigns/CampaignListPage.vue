<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api/axios'
import CampaignCard     from '@/components/campaign/CampaignCard.vue'
import CategoryDropdown from '@/components/ui/CategoryDropdown.vue'
import PaginationBar    from '@/components/ui/PaginationBar.vue'
import Navbar from '@/components/shared/Navbar.vue'
import Footer from '@/components/shared/Footer.vue'

const router = useRouter()

const searchQuery     = ref('')
const searchInput     = ref('')
const selectedCategory = ref('semua')
const currentPage     = ref(1)
const itemsPerPage    = ref(6)
const loading         = ref(false)
const allCampaigns    = ref([])
const pagination      = ref({ total: 0, last_page: 1 })
const stats           = ref({ total_donasi: 0, total_campaign: 0, total_donatur: 0 })
const categories      = ref([])

let searchTimer = null

function handleSearchInput() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    searchQuery.value = searchInput.value
  }, 400)
}

async function fetchCategories() {
  try {
    const res = await api.get('/campaign-categories')
    const raw = res.data.data || []
    categories.value = [
      { value: 'semua', label: 'Semua Kategori' },
      ...raw.map(c => ({ value: c.nama_kategori?.toLowerCase(), label: c.nama_kategori })),
    ]
  } catch {
    categories.value = [
      { value: 'semua', label: 'Semua Kategori' },
      { value: 'umum', label: 'Umum' },
      { value: 'pendidikan', label: 'Pendidikan' },
      { value: 'lingkungan', label: 'Lingkungan' },
      { value: 'kesehatan', label: 'Kesehatan' },
      { value: 'bencana', label: 'Bencana' },
      { value: 'sosial', label: 'Sosial' },
    ]
  }
}

async function fetchCampaigns() {
  loading.value = true
  try {
    const params = {
      page: currentPage.value,
      per_page: itemsPerPage.value,
      kategori: selectedCategory.value !== 'semua' ? selectedCategory.value : undefined,
    }
    if (searchQuery.value) params.keyword = searchQuery.value
    const res = await api.get('/campaigns/search', { params })
    const items = res.data.data.items || []
    allCampaigns.value = items.map(c => ({
      ...c,
      persentase_pencapaian: c.target_dana
        ? Math.min(100, Math.round((c.dana_terkumpul / c.target_dana) * 100))
        : 0,
    }))
    pagination.value = res.data.data.pagination || { total: 0, last_page: 1 }
  } catch (e) {
    allCampaigns.value = []
    console.error('Gagal memuat campaign:', e)
  } finally {
    loading.value = false
  }
}

async function fetchStats() {
  try {
    const res = await api.get('/campaigns/search', { params: { per_page: 8 } })
    const items = res.data.data?.items || []
    const total = res.data.data?.pagination?.total || 0
    const totalDonasi = items.reduce((sum, c) => sum + (c.dana_terkumpul || 0), 0)
    stats.value.total_campaign = total
    stats.value.total_donasi = totalDonasi
  } catch {}
}

onMounted(() => {
  fetchCategories()
  fetchCampaigns()
  fetchStats()
})

watch([searchQuery, selectedCategory], () => {
  currentPage.value = 1
  fetchCampaigns()
})

watch(currentPage, fetchCampaigns)

function goToPage(page) {
  currentPage.value = page
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function goToCampaignDetail(id) {
  router.push(`/campaigns/${id}`)
}

function formatStat(val) {
  if (val >= 1000000000) return (val / 1000000000).toFixed(1) + ' M'
  if (val >= 1000000) return (val / 1000000).toFixed(1) + ' Jt'
  if (val >= 1000) return (val / 1000).toFixed(0) + ' Rb'
  return String(val)
}
</script>

<template>
  <div class="min-h-screen" style="background-color: #F5F0E8;">
    <Navbar />

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
            <p class="text-2xl font-bold">{{ stats.total_donatur }}</p>
            <p class="text-white/60">Donatur</p>
          </div>
        </div>
      </div>
    </section>

    <div class="max-w-5xl mx-auto px-6 py-8">

      <!-- Search & Filter -->
      <div class="flex items-center gap-3 mb-8">
        <div class="flex-1 flex items-center gap-2 bg-white border border-gray-200 rounded-full px-4 py-2.5 shadow-sm">
          <svg class="w-4 h-4 shrink-0 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input
            v-model="searchInput"
            @input="handleSearchInput"
            type="text"
            placeholder="Cari aksi kebaikan..."
            class="flex-1 bg-transparent text-sm outline-none placeholder-gray-400"
            style="color: #1a2744;"
          />
        </div>
        <CategoryDropdown v-model="selectedCategory" :categories="categories" />
      </div>

      <!-- Campaign Grid -->
      <div
        v-if="!loading && allCampaigns.length > 0"
        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5"
      >
        <div
          v-for="campaign in allCampaigns"
          :key="campaign.id_campaign"
          @click="goToCampaignDetail(campaign.id_campaign)"
          class="cursor-pointer"
        >
          <CampaignCard :campaign="campaign" />
        </div>
      </div>

      <!-- Loading -->
      <div v-else-if="loading" class="flex flex-col items-center justify-center py-20 gap-3">
        <p class="text-sm font-medium" style="color: #9e8e80;">Memuat data...</p>
      </div>

      <!-- Empty State -->
      <div v-else class="flex flex-col items-center justify-center py-20 gap-3">
        <svg class="w-12 h-12 text-gray-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
          fill="none" stroke="currentColor" stroke-width="1.5">
          <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <p class="text-sm font-medium" style="color: #9e8e80;">Tidak ada campaign ditemukan</p>
        <button
          class="text-xs underline"
          style="color: #8B4513;"
          @click="searchInput = ''; searchQuery = ''; selectedCategory = 'semua'"
        >
          Reset filter
        </button>
      </div>

      <!-- Pagination -->
      <PaginationBar
        v-if="pagination.last_page > 1"
        :currentPage="currentPage"
        :totalPages="pagination.last_page"
        @update:currentPage="goToPage"
      />

    </div>

    <Footer />

  </div>
</template>
