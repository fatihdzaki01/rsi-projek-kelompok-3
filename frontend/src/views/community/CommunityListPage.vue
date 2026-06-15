<template>
  <div class="min-h-screen flex flex-col" style="background-color: #F5F0E8;">
    <Navbar />

    <!-- Hero Section (same style as Campaign) -->
    <section class="bg-gradient-to-br from-[#8B4513] to-[#6b3410] text-white">
      <div class="max-w-5xl mx-auto px-6 py-12 text-center">
        <h1 class="text-3xl font-bold mb-2">Komunitas Sosial</h1>
        <p class="text-sm text-white/80 mb-6 max-w-xl mx-auto">
          Temukan komunitas yang bergerak di bidang sosial dan kemanusiaan. Bergabunglah dan bersama-sama kita berbagi kebaikan.
        </p>
        <div class="flex items-center justify-center gap-6 text-sm">
          <div>
            <p class="text-xl font-bold">{{ stats.total_komunitas }}</p>
            <p class="text-white/60">Komunitas</p>
          </div>
          <div class="w-px h-8 bg-white/20" />
          <div>
            <p class="text-xl font-bold">{{ stats.total_campaign }}</p>
            <p class="text-white/60">Campaign Aktif</p>
          </div>
          <div class="w-px h-8 bg-white/20" />
          <div>
            <p class="text-xl font-bold">{{ stats.total_donasi }}</p>
            <p class="text-white/60">Total Donasi</p>
          </div>
        </div>
      </div>
    </section>

    <div class="max-w-5xl mx-auto w-full px-6 py-8">

      <!-- Search & Filter (same style as Campaign) -->
      <div class="flex items-center gap-3 mb-8">
        <div class="flex-1 flex items-center gap-2 bg-white border border-gray-200 rounded-full px-4 py-2.5 shadow-sm">
          <svg class="w-4 h-4 shrink-0 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input
            v-model="searchInput"
            @input="handleSearchInput"
            type="text"
            placeholder="Cari komunitas..."
            class="flex-1 bg-transparent text-sm outline-none placeholder-gray-400"
            style="color: #1a2744;"
          />
        </div>
        <LocationDropdown
          v-model="selectedLocation"
          :locations="locations"
        />
      </div>

      <!-- Community Grid (same card layout as Campaign) -->
      <div
        v-if="!loading && allCommunities.length > 0"
        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5"
      >
        <div
          v-for="community in allCommunities"
          :key="community.id_komunitas"
          @click="goToCommunityProfile(community.id_komunitas)"
          class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200 overflow-hidden cursor-pointer"
        >
          <div class="h-36 bg-gradient-to-br from-[#c4783c] via-[#8B4513] to-[#5c2d0a] flex items-center justify-center text-white text-3xl font-bold">
            {{ (community.nama_lembaga || 'K').charAt(0) }}
          </div>
          <div class="p-4 flex flex-col gap-1.5">
            <h3 class="text-sm font-bold text-[#2C2C2C] leading-snug">{{ community.nama_lembaga }}</h3>
            <p class="text-xs text-gray-500 line-clamp-2">{{ community.deskripsi || 'Komunitas sosial kemanusiaan' }}</p>
            <div class="grid grid-cols-3 gap-1 border-t border-stone-100 pt-3 mt-1">
              <div class="text-center">
                <p class="text-sm font-bold text-gray-900">{{ formatNumber(community.total_follower) }}</p>
                <p class="text-[10px] text-gray-400">Follower</p>
              </div>
              <div class="text-center border-x border-stone-100">
                <p class="text-sm font-bold text-gray-900">{{ community.total_campaign_aktif || 0 }}</p>
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
        <p class="text-sm font-medium" style="color: #9e8e80;">Tidak ada komunitas ditemukan</p>
        <button
          class="text-xs underline"
          style="color: #8B4513;"
          @click="searchInput = ''; searchQuery = ''; selectedLocation = 'semua'"
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

<script setup>
import { ref, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api/axios'
import Navbar          from '@/components/shared/Navbar.vue'
import LocationDropdown from '@/components/ui/LocationDropdown.vue'
import PaginationBar    from '@/components/ui/PaginationBar.vue'
import Footer           from '@/components/shared/Footer.vue'

const router = useRouter()

const itemsPerPage = 6
const loading = ref(false)
const allCommunities = ref([])
const pagination = ref({ total: 0, last_page: 1 })
const stats = ref({ total_komunitas: 0, total_campaign: 0, total_donasi: 0 })

const searchQuery = ref('')
const searchInput = ref('')
const selectedLocation = ref('semua')
const currentPage = ref(1)

let searchTimer = null

const locations = ref([
  { value: 'semua',      label: 'Semua Lokasi' },
  { value: 'jakarta',    label: 'Jakarta' },
  { value: 'bandung',    label: 'Bandung' },
  { value: 'surabaya',   label: 'Surabaya' },
  { value: 'yogyakarta', label: 'Yogyakarta' },
  { value: 'medan',      label: 'Medan' },
  { value: 'makassar',   label: 'Makassar' },
])

function handleSearchInput() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    searchQuery.value = searchInput.value
  }, 400)
}

async function fetchStats() {
  try {
    const res = await api.get('/communities/search', { params: { per_page: 1 } })
    const total = res.data.data?.pagination?.total || 0
    stats.value.total_komunitas = total
  } catch {}
}

async function fetchCommunities() {
  loading.value = true
  try {
    const params = { page: currentPage.value, per_page: itemsPerPage }
    if (searchQuery.value) params.keyword = searchQuery.value
    if (selectedLocation.value !== 'semua') params.kabupaten_kota = selectedLocation.value
    const res = await api.get('/communities/search', { params })
    allCommunities.value = res.data.data.items || []
    pagination.value = res.data.data.pagination || { total: 0, last_page: 1 }
  } catch {
    allCommunities.value = []
  } finally {
    loading.value = false
  }
}

function goToPage(page) {
  currentPage.value = page
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function goToCommunityProfile(id) {
  router.push(`/communities/${id}`)
}

function formatNumber(n) {
  if (n >= 1000) return (n / 1000).toFixed(1).replace('.0', '') + 'K'
  return (n || 0).toString()
}

function formatRupiah(n) {
  n = n || 0
  if (n >= 1000000000) return 'Rp ' + (n / 1000000000).toFixed(1) + 'M'
  if (n >= 1000000) return 'Rp ' + (n / 1000000).toFixed(1) + 'Jt'
  return 'Rp ' + n.toLocaleString('id-ID')
}

onMounted(() => {
  fetchStats()
  fetchCommunities()
})

watch([searchQuery, selectedLocation], () => {
  currentPage.value = 1
  fetchCommunities()
})

watch(currentPage, fetchCommunities)
</script>
