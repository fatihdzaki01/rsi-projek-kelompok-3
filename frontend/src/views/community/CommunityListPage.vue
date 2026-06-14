<template>
  <div class="min-h-screen bg-[#F5F0E8] flex flex-col">

    <!-- ===== NAVBAR ===== -->
    <nav class="bg-[#F5F0E8] border-b border-stone-200 px-6 py-3 flex items-center justify-between sticky top-0 z-30">
      <div class="flex items-center gap-6">
        <span class="font-bold text-[#1a2744] tracking-wide text-sm">BERBAGIVE</span>
        <div class="hidden md:flex items-center gap-1">
          <a href="#" class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 rounded-full">Beranda</a>
          <a href="#" class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 rounded-full">Campaign</a>
          <a href="#" class="px-3 py-1.5 text-xs font-medium bg-[#1a2744] text-white rounded-full">Komunitas</a>
          <a href="#" class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 rounded-full">Donasi Saya</a>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <div class="relative hidden sm:block">
          <svg class="w-3.5 h-3.5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
          </svg>
          <input type="text" placeholder="Search" class="bg-white/70 text-xs pl-8 pr-4 py-1.5 rounded-full border border-stone-200 focus:outline-none focus:ring-1 focus:ring-stone-300 w-36"/>
        </div>
        <button class="w-7 h-7 rounded-full bg-stone-300 flex items-center justify-center">
          <svg class="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 20 20"><path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/></svg>
        </button>
        <button class="text-gray-500 hover:text-gray-700">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
          </svg>
        </button>
      </div>
    </nav>

    <!-- ===== MAIN ===== -->
    <main class="flex-1 max-w-5xl mx-auto w-full px-4 sm:px-6 py-8">

      <!-- Page header -->
      <div class="flex items-center gap-3 mb-6">
        <button class="w-8 h-8 rounded-full bg-white border border-stone-200 flex items-center justify-center hover:bg-stone-50 transition-colors shadow-sm flex-shrink-0">
          <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
          </svg>
        </button>
        <div>
          <h1 class="text-xl font-bold text-gray-900">Daftar Komunitas</h1>
          <p class="text-xs text-gray-400 mt-0.5">{{ filteredCommunities.length }} komunitas ditemukan</p>
        </div>
      </div>

      <!-- Search & filter row -->
      <div class="flex items-center gap-3 mb-8">
        <!-- Search -->
        <div class="relative flex-1">
          <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
          </svg>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari komunitas..."
            class="w-full bg-white border border-stone-200 rounded-xl pl-10 pr-4 py-2.5 text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#8B4513]/20 focus:border-[#8B4513] transition-all shadow-sm"
          />
          <button
            v-if="searchQuery"
            @click="searchQuery = ''"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-300 hover:text-gray-500"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Location filter -->
        <LocationDropdown
          v-model="selectedLocation"
          :locations="locations"
        />
      </div>

      <!-- ===== GRID ===== -->
      <div v-if="paginatedCommunities.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
        <CommunityCard
          v-for="community in paginatedCommunities"
          :key="community.id"
          :community="community"
          :is-guest="isGuest"
          @card-click="goToCommunityProfile"
          @follow="handleFollow"
          @unfollow="handleUnfollow"
        />
      </div>

      <!-- Empty state -->
      <div v-else class="flex flex-col items-center justify-center py-20 text-center">
        <div class="w-16 h-16 rounded-full bg-stone-200 flex items-center justify-center mb-4 text-3xl">
          🔍
        </div>
        <h3 class="text-base font-bold text-gray-600 mb-1">Komunitas tidak ditemukan</h3>
        <p class="text-sm text-gray-400">Coba kata kunci atau filter lokasi yang lain</p>
        <button
          @click="searchQuery = ''; selectedLocation = 'semua'"
          class="mt-4 text-xs font-medium text-[#8B4513] underline underline-offset-2 hover:opacity-70 transition-opacity"
        >
          Reset pencarian
        </button>
      </div>

      <!-- Pagination -->
      <PaginationBar
        v-if="totalPages > 1"
        :current-page="currentPage"
        :total-pages="totalPages"
        @change="goToPage"
      />

    </main>

    <!-- ===== FOOTER ===== -->
    <footer class="border-t border-stone-200 bg-[#F5F0E8] px-6 py-6 mt-4">
      <div class="max-w-5xl mx-auto flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <p class="font-bold text-[#1a2744] text-sm mb-0.5">Berbagive</p>
          <p class="text-[10px] text-gray-400">© 2024 Berbagive. Part of The Human Archive project.</p>
        </div>
        <div class="flex flex-wrap items-center gap-5 text-xs text-gray-500">
          <a href="#" class="hover:text-gray-700">Kebijakan Privasi</a>
          <a href="#" class="hover:text-gray-700">Syarat &amp; Ketentuan</a>
          <a href="#" class="hover:text-gray-700">Hubungi Kami</a>
          <a href="#" class="hover:text-gray-700">FAQ</a>
          <button class="hover:text-gray-700">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
              <path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92s2.92-1.31 2.92-2.92-1.31-2.92-2.92-2.92z"/>
            </svg>
          </button>
        </div>
      </div>
    </footer>

  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import CommunityCard    from '@/components/community/CommunityCard.vue'
import LocationDropdown from '@/components/ui/LocationDropdown.vue'
import PaginationBar    from '@/components/ui/PaginationBar.vue'

// --- Config ---
const itemsPerPage = 6
const isGuest = ref(false)

// --- Filter state ---
const searchQuery      = ref('')
const selectedLocation = ref('semua')
const currentPage      = ref(1)

const locations = ref([
  { value: 'semua',      label: 'Semua Lokasi' },
  { value: 'jakarta',    label: 'Jakarta' },
  { value: 'bandung',    label: 'Bandung' },
  { value: 'surabaya',   label: 'Surabaya' },
  { value: 'yogyakarta', label: 'Yogyakarta' },
  { value: 'medan',      label: 'Medan' },
  { value: 'makassar',   label: 'Makassar' }
])

// --- Data ---
const allCommunities = ref([
  {
    id: 1,
    nama_komunitas: 'Komunitas Peduli Air',
    deskripsi: 'Komunitas yang berdedikasi membantu akses air bersih di 6 wilayah Indonesia.',
    kota: 'Jakarta Pusat',
    foto_komunitas: '',
    total_follower: 1840,
    total_campaign_aktif: 3,
    total_dana_diterima: 1200000000,
    is_following: false
  },
  {
    id: 2,
    nama_komunitas: 'Yayasan Pendidikan Cerdas',
    deskripsi: 'Membangun sekolah dan beasiswa untuk anak-anak kurang mampu di pelosok negeri.',
    kota: 'Bandung',
    foto_komunitas: '',
    total_follower: 3210,
    total_campaign_aktif: 5,
    total_dana_diterima: 4500000000,
    is_following: true
  },
  {
    id: 3,
    nama_komunitas: 'Relawan Alam Nusantara',
    deskripsi: 'Gerakan penghijauan pesisir dan rehabilitasi hutan mangrove Indonesia.',
    kota: 'Surabaya',
    foto_komunitas: '',
    total_follower: 980,
    total_campaign_aktif: 2,
    total_dana_diterima: 890000000,
    is_following: false
  },
  {
    id: 4,
    nama_komunitas: 'Dokter Tanpa Batas Desa',
    deskripsi: 'Menghadirkan layanan kesehatan gratis ke daerah terpencil yang kekurangan tenaga medis.',
    kota: 'Yogyakarta',
    foto_komunitas: '',
    total_follower: 5670,
    total_campaign_aktif: 4,
    total_dana_diterima: 7200000000,
    is_following: false
  },
  {
    id: 5,
    nama_komunitas: 'Aksi Cepat Jakarta',
    deskripsi: 'Respon cepat bencana banjir dan tanggap darurat wilayah DKI Jakarta.',
    kota: 'Jakarta Timur',
    foto_komunitas: '',
    total_follower: 2340,
    total_campaign_aktif: 1,
    total_dana_diterima: 3100000000,
    is_following: true
  },
  {
    id: 6,
    nama_komunitas: 'Komunitas Makan Siang Gratis',
    deskripsi: 'Menyediakan makan siang gratis untuk lansia, anak yatim, dan kaum dhuafa setiap hari.',
    kota: 'Medan',
    foto_komunitas: '',
    total_follower: 720,
    total_campaign_aktif: 2,
    total_dana_diterima: 450000000,
    is_following: false
  }
])

// --- Computed ---
const filteredCommunities = computed(() =>
  allCommunities.value.filter(c => {
    const q = searchQuery.value.toLowerCase()
    const matchSearch =
      c.nama_komunitas.toLowerCase().includes(q) ||
      c.deskripsi.toLowerCase().includes(q) ||
      c.kota.toLowerCase().includes(q)
    const matchLocation =
      selectedLocation.value === 'semua' ||
      c.kota.toLowerCase().includes(selectedLocation.value)
    return matchSearch && matchLocation
  })
)

const paginatedCommunities = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  return filteredCommunities.value.slice(start, start + itemsPerPage)
})

const totalPages = computed(() =>
  Math.ceil(filteredCommunities.value.length / itemsPerPage)
)

// Reset page on filter change
watch([searchQuery, selectedLocation], () => { currentPage.value = 1 })

// --- Actions ---
function goToPage(page) {
  currentPage.value = page
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function goToCommunityProfile(id) {
  // router.push(`/komunitas/${id}`)
  console.log('Navigate to SCR-12, community id:', id)
}

function handleFollow(community) {
  if (isGuest.value) {
    // router.push('/login')
    return
  }
  community.is_following = true
  community.total_follower++
}

function handleUnfollow(community) {
  community.is_following = false
  community.total_follower--
}
</script>
