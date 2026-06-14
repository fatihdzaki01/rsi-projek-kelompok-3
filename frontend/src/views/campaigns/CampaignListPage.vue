<script setup>
import { ref, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import CampaignCard     from '@/components/ui/CampaignCard.vue'
import CategoryDropdown from '@/components/ui/CategoryDropdown.vue'
import PaginationBar    from '@/components/ui/PaginationBar.vue'

const router = useRouter()

const searchQuery     = ref('')
const selectedCategory = ref('semua')
const currentPage     = ref(1)
const itemsPerPage    = 6

const categories = ref([
  { value: 'semua',      label: 'Semua Kategori' },
  { value: 'umum',       label: 'Umum' },
  { value: 'pendidikan', label: 'Pendidikan' },
  { value: 'lingkungan', label: 'Lingkungan' },
  { value: 'kesehatan',  label: 'Kesehatan' },
  { value: 'bencana',    label: 'Bencana' },
  { value: 'sosial',     label: 'Sosial' },
])

const allCampaigns = ref([
  { id: 1, judul: 'Bantuan Sumatera',   nama_lembaga: 'Komunitas Peduli Bencana',      dana_terkumpul: 45000000,  target_dana: 60000000,  persentase_pencapaian: 75, foto_campaign_url: '', tanggal_selesai: '25/04/24', nama_kategori: 'UMUM' },
  { id: 2, judul: 'Sekolah Untuk Semua', nama_lembaga: 'Yayasan Pendidikan Cerdas',    dana_terkumpul: 12500000,  target_dana: 40000000,  persentase_pencapaian: 30, foto_campaign_url: '', tanggal_selesai: '12/05/24', nama_kategori: 'PENDIDIKAN' },
  { id: 3, judul: 'Hijaukan Pesisir',   nama_lembaga: 'Relawan Alam Nusantara',        dana_terkumpul: 89200000,  target_dana: 100000000, persentase_pencapaian: 90, foto_campaign_url: '', tanggal_selesai: '30/06/24', nama_kategori: 'LINGKUNGAN' },
  { id: 4, judul: 'Kesehatan Pelosok',  nama_lembaga: 'Dokter Tanpa Batas Desa',       dana_terkumpul: 210200000, target_dana: 250000000, persentase_pencapaian: 55, foto_campaign_url: '', tanggal_selesai: '05/07/24', nama_kategori: 'KESEHATAN' },
  { id: 5, judul: 'Siaga Banjir Kota', nama_lembaga: 'Aksi Cepat Jakarta',             dana_terkumpul: 32000000,  target_dana: 200000000, persentase_pencapaian: 15, foto_campaign_url: '', tanggal_selesai: '15/07/24', nama_kategori: 'BENCANA' },
  { id: 6, judul: 'Dapur Berbagi',      nama_lembaga: 'Komunitas Makan Siang Gratis',  dana_terkumpul: 6800000,   target_dana: 17000000,  persentase_pencapaian: 40, foto_campaign_url: '', tanggal_selesai: '21/08/24', nama_kategori: 'SOSIAL' },
])

const filteredCampaigns = computed(() => {
  return allCampaigns.value.filter(c => {
    const matchSearch = c.judul.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                        c.nama_lembaga.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchCategory = selectedCategory.value === 'semua' ||
                          c.nama_kategori.toLowerCase() === selectedCategory.value
    return matchSearch && matchCategory
  })
})

const paginatedCampaigns = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  return filteredCampaigns.value.slice(start, start + itemsPerPage)
})

const totalPages = computed(() => Math.max(1, Math.ceil(filteredCampaigns.value.length / itemsPerPage)))

function goToPage(page) {
  currentPage.value = page
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function goToCampaignDetail(id) {
  router.push(`/campaigns/${id}`)
}

watch([searchQuery, selectedCategory], () => {
  currentPage.value = 1
})
</script>

<template>
  <div class="min-h-screen" style="background-color: #F5F0E8;">

    <!-- Navbar -->
    <nav class="bg-white shadow-sm px-6 py-3 flex items-center justify-between sticky top-0 z-10">
      <span class="font-bold text-base" style="color: #1a2744;">Berbagive</span>
      <div class="flex items-center gap-1 text-sm">
        <button class="px-4 py-1.5 rounded-full font-medium text-white text-xs" style="background-color: #8B4513;">Beranda</button>
        <button class="px-4 py-1.5 text-gray-600 text-xs hover:text-gray-900">Campaign</button>
        <button class="px-4 py-1.5 text-gray-600 text-xs hover:text-gray-900">Komunitas</button>
        <button class="px-4 py-1.5 text-gray-600 text-xs hover:text-gray-900">Donasi Saya</button>
      </div>
      <div class="flex items-center gap-3">
        <div class="flex items-center gap-2 border border-gray-200 rounded-full px-3 py-1.5">
          <svg class="w-3.5 h-3.5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <span class="text-xs text-gray-400">Search</span>
        </div>
        <svg class="w-4 h-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
          fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <svg class="w-4 h-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
          fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
      </div>
    </nav>

    <div class="max-w-5xl mx-auto px-6 py-8">

      <!-- Page Header -->
      <div class="flex items-center gap-3 mb-6">
        <button
          class="w-8 h-8 rounded-full border border-gray-200 bg-white flex items-center justify-center
                 hover:bg-gray-50 transition-colors"
          @click="router.back()"
        >
          <svg class="w-4 h-4 text-gray-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
            <polyline points="15 18 9 12 15 6"/>
          </svg>
        </button>
        <h1 class="text-2xl font-bold" style="color: #1a2744;">Daftar Campaign</h1>
      </div>

      <!-- Search & Filter -->
      <div class="flex items-center gap-3 mb-8">
        <div class="flex-1 flex items-center gap-2 bg-white border border-gray-200 rounded-full px-4 py-2.5 shadow-sm">
          <svg class="w-4 h-4 shrink-0 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input
            v-model="searchQuery"
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
        v-if="paginatedCampaigns.length > 0"
        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5"
      >
        <CampaignCard
          v-for="campaign in paginatedCampaigns"
          :key="campaign.id"
          :campaign="campaign"
          @click="goToCampaignDetail"
        />
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
          @click="searchQuery = ''; selectedCategory = 'semua'"
        >
          Reset filter
        </button>
      </div>

      <!-- Pagination -->
      <PaginationBar
        v-if="totalPages > 1"
        :currentPage="currentPage"
        :totalPages="totalPages"
        @update:currentPage="goToPage"
      />

    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-100 px-6 py-5 mt-10">
      <div class="max-w-5xl mx-auto flex items-center justify-between">
        <div>
          <p class="font-bold text-sm" style="color: #8B4513;">Berbagive</p>
          <p class="text-xs mt-0.5" style="color: #9e8e80;">© 2024 Berbagive. Part of The Human Archive project.</p>
        </div>
        <div class="flex items-center gap-4 text-xs" style="color: #9e8e80;">
          <span class="cursor-pointer hover:underline">Kebijakan Privasi</span>
          <span class="cursor-pointer hover:underline">Syarat & Ketentuan</span>
          <span class="cursor-pointer hover:underline">Hubungi Kami</span>
          <span class="cursor-pointer hover:underline">FAQ</span>
          <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
            <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
          </svg>
        </div>
      </div>
    </footer>

  </div>
</template>