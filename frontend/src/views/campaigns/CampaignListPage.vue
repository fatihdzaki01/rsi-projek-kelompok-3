<template>
  <!-- Page wrapper -->
  <div class="min-h-screen flex flex-col bg-[#F5F0E8] font-sans">

    <TheNavbar />

    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6">

      <!-- Page header: back arrow + title -->
      <div class="flex items-center gap-3 mb-6">
        <button
          class="p-1.5 rounded-full bg-white shadow-sm hover:bg-[#F5F0E8] transition-colors"
          aria-label="Kembali"
          @click="goBack"
        >
          <svg
            class="w-4 h-4 text-[#2C2C2C]"
            fill="none"
            stroke="currentColor"
            stroke-width="2.5"
            viewBox="0 0 24 24"
            aria-hidden="true"
          >
            <path d="M19 12H5M12 5l-7 7 7 7" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </button>
        <h1 class="text-2xl sm:text-[1.75rem] font-extrabold text-[#2C2C2C] tracking-tight leading-tight">
          Daftar Campaign
        </h1>
      </div>

      <!-- Filter row: search + category dropdown -->
      <div class="flex flex-col sm:flex-row gap-3 mb-6">

        <!-- Search input -->
        <div class="relative flex-1">
          <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
            <svg
              class="w-4 h-4 text-gray-400"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              viewBox="0 0 24 24"
              aria-hidden="true"
            >
              <circle cx="11" cy="11" r="8" />
              <path d="M21 21l-4.35-4.35" stroke-linecap="round" />
            </svg>
          </span>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari aksi kebaikan..."
            class="w-full pl-9 pr-4 py-2.5 text-sm bg-white border border-[#D9D0C4] rounded-xl
                   focus:outline-none focus:border-[#8B4513] transition-colors placeholder-gray-400"
            @input="currentPage = 1"
          />
        </div>

        <!-- Category dropdown -->
        <div class="relative flex-shrink-0">
          <button
            class="flex items-center gap-2 px-4 py-2.5 bg-white border border-[#D9D0C4]
                   rounded-xl hover:border-[#8B4513] transition-colors cursor-pointer"
            aria-haspopup="listbox"
            :aria-expanded="dropdownOpen"
            @click="dropdownOpen = !dropdownOpen"
          >
            <!-- Filter lines icon -->
            <svg
              class="w-4 h-4 text-gray-500"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              viewBox="0 0 24 24"
              aria-hidden="true"
            >
              <path d="M4 6h16M7 12h10M10 18h4" stroke-linecap="round" />
            </svg>
            <span class="text-sm text-[#2C2C2C] whitespace-nowrap">{{ selectedCategory }}</span>
            <svg
              class="w-3.5 h-3.5 text-gray-500 transition-transform duration-200"
              :class="{ 'rotate-180': dropdownOpen }"
              fill="none"
              stroke="currentColor"
              stroke-width="2.5"
              viewBox="0 0 24 24"
              aria-hidden="true"
            >
              <path d="M6 9l6 6 6-6" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </button>

          <!-- Dropdown menu -->
          <Transition name="dropdown">
            <ul
              v-if="dropdownOpen"
              role="listbox"
              class="absolute right-0 mt-1 w-48 bg-white border border-[#D9D0C4] rounded-xl
                     shadow-lg overflow-hidden z-20"
            >
              <li
                v-for="cat in categories"
                :key="cat"
                role="option"
                :aria-selected="selectedCategory === cat"
                class="px-4 py-2.5 text-sm cursor-pointer hover:bg-[#F5F0E8] transition-colors"
                :class="selectedCategory === cat
                  ? 'text-[#8B4513] font-semibold bg-[#FBF7F2]'
                  : 'text-[#2C2C2C]'"
                @click="selectCategory(cat)"
              >
                {{ cat }}
              </li>
            </ul>
          </Transition>
        </div>

      </div>

      <!-- Campaign grid -->
      <section aria-label="Daftar kampanye donasi">
        <div
          v-if="paginatedCampaigns.length > 0"
          class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5"
        >
          <CampaignCard
            v-for="campaign in paginatedCampaigns"
            :key="campaign.id_campaign"
            :campaign="campaign"
          />
        </div>

        <!-- Empty state -->
        <div
          v-else
          class="flex flex-col items-center justify-center py-24 text-gray-400 gap-3"
        >
          <svg
            class="w-12 h-12"
            fill="none"
            stroke="currentColor"
            stroke-width="1.5"
            viewBox="0 0 24 24"
            aria-hidden="true"
          >
            <circle cx="11" cy="11" r="8" />
            <path d="M21 21l-4.35-4.35" stroke-linecap="round" />
          </svg>
          <p class="text-sm">Tidak ada campaign yang ditemukan.</p>
        </div>
      </section>

      <!-- Pagination -->
      <nav
        v-if="totalPages > 1"
        class="flex justify-center items-center gap-1 mt-10"
        aria-label="Navigasi halaman"
      >
        <!-- Prev -->
        <button
          :disabled="currentPage === 1"
          class="w-8 h-8 flex items-center justify-center rounded-full text-sm
                 disabled:opacity-30 hover:bg-[#E8D5C4] transition-colors cursor-pointer"
          aria-label="Halaman sebelumnya"
          @click="currentPage--"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M15 18l-6-6 6-6" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </button>

        <!-- Page items -->
        <template v-for="item in paginationItems" :key="`page-${item}`">
          <span
            v-if="item === '...'"
            class="w-8 h-8 flex items-center justify-center text-sm text-gray-400 select-none"
          >
            ...
          </span>
          <button
            v-else
            class="w-8 h-8 rounded-full text-sm font-medium transition-colors cursor-pointer"
            :class="currentPage === item
              ? 'bg-[#8B4513] text-white'
              : 'text-[#2C2C2C] hover:bg-[#E8D5C4]'"
            :aria-label="`Halaman ${item}`"
            :aria-current="currentPage === item ? 'page' : undefined"
            @click="currentPage = Number(item)"
          >
            {{ item }}
          </button>
        </template>

        <!-- Next -->
        <button
          :disabled="currentPage === totalPages"
          class="w-8 h-8 flex items-center justify-center rounded-full text-sm
                 disabled:opacity-30 hover:bg-[#E8D5C4] transition-colors cursor-pointer"
          aria-label="Halaman berikutnya"
          @click="currentPage++"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M9 18l6-6-6-6" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </button>
      </nav>

    </main>

    <TheFooter />

  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import TheNavbar from '@/components/shared/Navbar.vue'
import TheFooter from '@/components/shared/Footer.vue'
import CampaignCard from '@/components/ui/CampaignCard.vue'
import type { Campaign } from '@/components/ui/CampaignCard.vue'

// ── Mock data ─────────────────────────────────────────────────────────────────
const allCampaigns: Campaign[] = [
  {
    id_campaign: 1,
    judul: 'Bantuan Sumatera',
    nama_lembaga: 'Komunitas Peduli Bencana',
    dana_terkumpul: 45_000_000,
    target_dana: 60_000_000,
    foto_campaign_url: 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=600&q=80',
    tanggal_selesai: '25/04/24',
    nama_kategori: 'UMUM',
    persentase_pencapaian: 75,
  },
  {
    id_campaign: 2,
    judul: 'Sekolah Untuk Semua',
    nama_lembaga: 'Yayasan Pendidikan Cerdas',
    dana_terkumpul: 12_000_000,
    target_dana: 40_000_000,
    foto_campaign_url: 'https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?w=600&q=80',
    tanggal_selesai: '12/05/24',
    nama_kategori: 'PENDIDIKAN',
    persentase_pencapaian: 30,
  },
  {
    id_campaign: 3,
    judul: 'Hijaukan Pesisir',
    nama_lembaga: 'Relawan Alam Nusantara',
    dana_terkumpul: 89_200_000,
    target_dana: 100_000_000,
    foto_campaign_url: 'https://images.unsplash.com/photo-1505118380757-91f5f5632de0?w=600&q=80',
    tanggal_selesai: '30/06/24',
    nama_kategori: 'LINGKUNGAN',
    persentase_pencapaian: 90,
  },
  {
    id_campaign: 4,
    judul: 'Kesehatan Pelosok',
    nama_lembaga: 'Dokter Tanpa Batas Desa',
    dana_terkumpul: 270_000_000,
    target_dana: 300_000_000,
    foto_campaign_url: 'https://images.unsplash.com/photo-1584515933487-779824d29309?w=600&q=80',
    tanggal_selesai: '05/07/24',
    nama_kategori: 'KESEHATAN',
    persentase_pencapaian: 95,
  },
  {
    id_campaign: 5,
    judul: 'Siaga Banjir Kota',
    nama_lembaga: 'Aksi Cepat Jakarta',
    dana_terkumpul: 32_000_000,
    target_dana: 200_000_000,
    foto_campaign_url: 'https://images.unsplash.com/photo-1547683905-f686c993aae5?w=600&q=80',
    tanggal_selesai: '15/07/24',
    nama_kategori: 'BENCANA',
    persentase_pencapaian: 15,
  },
  {
    id_campaign: 6,
    judul: 'Dapur Berbagi',
    nama_lembaga: 'Komunitas Makan Siang Gratis',
    dana_terkumpul: 6_800_000,
    target_dana: 17_000_000,
    foto_campaign_url: 'https://images.unsplash.com/photo-1547592180-85f173990554?w=600&q=80',
    tanggal_selesai: '21/08/24',
    nama_kategori: 'SOSIAL',
    persentase_pencapaian: 40,
  },
  // Extra campaigns for pagination demo (pages 2-12 content)
  {
    id_campaign: 7,
    judul: 'Beasiswa Anak Pesisir',
    nama_lembaga: 'Yayasan Harapan Bahari',
    dana_terkumpul: 18_500_000,
    target_dana: 50_000_000,
    foto_campaign_url: 'https://images.unsplash.com/photo-1497486751825-1233686d5d80?w=600&q=80',
    tanggal_selesai: '10/09/24',
    nama_kategori: 'PENDIDIKAN',
    persentase_pencapaian: 37,
  },
  {
    id_campaign: 8,
    judul: 'Tanam Mangrove Kalimantan',
    nama_lembaga: 'Relawan Hijau Indonesia',
    dana_terkumpul: 54_000_000,
    target_dana: 80_000_000,
    foto_campaign_url: 'https://images.unsplash.com/photo-1518531933037-91b2f5f229cc?w=600&q=80',
    tanggal_selesai: '20/09/24',
    nama_kategori: 'LINGKUNGAN',
    persentase_pencapaian: 68,
  },
  {
    id_campaign: 9,
    judul: 'Rumah Singgah Pasien',
    nama_lembaga: 'Yayasan Kasih Sehat',
    dana_terkumpul: 120_000_000,
    target_dana: 200_000_000,
    foto_campaign_url: 'https://images.unsplash.com/photo-1579154341184-f2e99b8f6fc5?w=600&q=80',
    tanggal_selesai: '01/10/24',
    nama_kategori: 'KESEHATAN',
    persentase_pencapaian: 60,
  },
  {
    id_campaign: 10,
    judul: 'Pemulihan Pasca Gempa NTB',
    nama_lembaga: 'Aksi Kemanusiaan Nusantara',
    dana_terkumpul: 88_000_000,
    target_dana: 250_000_000,
    foto_campaign_url: 'https://images.unsplash.com/photo-1565071783368-d462d0a34db2?w=600&q=80',
    tanggal_selesai: '15/10/24',
    nama_kategori: 'BENCANA',
    persentase_pencapaian: 35,
  },
  {
    id_campaign: 11,
    judul: 'Warung Lansia Gratis',
    nama_lembaga: 'Komunitas Peduli Lansia',
    dana_terkumpul: 9_200_000,
    target_dana: 20_000_000,
    foto_campaign_url: 'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?w=600&q=80',
    tanggal_selesai: '30/10/24',
    nama_kategori: 'SOSIAL',
    persentase_pencapaian: 46,
  },
  {
    id_campaign: 12,
    judul: 'Buku Untuk Desa Terpencil',
    nama_lembaga: 'Gerakan Literasi Nusantara',
    dana_terkumpul: 7_500_000,
    target_dana: 15_000_000,
    foto_campaign_url: 'https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=600&q=80',
    tanggal_selesai: '05/11/24',
    nama_kategori: 'PENDIDIKAN',
    persentase_pencapaian: 50,
  },
]

// ── State ─────────────────────────────────────────────────────────────────────
const searchQuery      = ref('')
const selectedCategory = ref('Semua Kategori')
const dropdownOpen     = ref(false)
const currentPage      = ref(1)
const ITEMS_PER_PAGE   = 6

const categories = [
  'Semua Kategori',
  'UMUM',
  'PENDIDIKAN',
  'LINGKUNGAN',
  'KESEHATAN',
  'BENCANA',
  'SOSIAL',
]

// ── Computed ──────────────────────────────────────────────────────────────────

const filteredCampaigns = computed<Campaign[]>(() => {
  const q   = searchQuery.value.toLowerCase().trim()
  const cat = selectedCategory.value

  return allCampaigns.filter((c) => {
    const matchSearch =
      !q ||
      c.judul.toLowerCase().includes(q) ||
      c.nama_lembaga.toLowerCase().includes(q)
    const matchCategory =
      cat === 'Semua Kategori' || c.nama_kategori === cat

    return matchSearch && matchCategory
  })
})

const totalPages = computed(() =>
  Math.max(1, Math.ceil(filteredCampaigns.value.length / ITEMS_PER_PAGE))
)

const paginatedCampaigns = computed<Campaign[]>(() => {
  const start = (currentPage.value - 1) * ITEMS_PER_PAGE
  return filteredCampaigns.value.slice(start, start + ITEMS_PER_PAGE)
})

/** Pagination: [1, 2, 3, '...', 12] pattern matching the screenshot */
const paginationItems = computed<(number | '...')[]>(() => {
  const total = totalPages.value
  if (total <= 5) {
    return Array.from({ length: total }, (_, i) => i + 1)
  }
  const items: (number | '...')[] = [1, 2, 3, '...', total]
  return items
})

// ── Methods ───────────────────────────────────────────────────────────────────

function selectCategory(cat: string) {
  selectedCategory.value = cat
  dropdownOpen.value = false
  currentPage.value = 1
}

function goBack() {
  history.back()
}
</script>

<style scoped>
/* Dropdown slide-fade transition */
.dropdown-enter-active,
.dropdown-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}
.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}
</style>
