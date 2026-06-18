<template>
  <div class="min-h-screen flex flex-col bg-[#F5F0E8]">
    <Navbar />

    <main class="flex-1 py-8">
      <div class="max-w-6xl mx-auto px-6">

        <!-- Breadcrumb -->
        <nav class="text-sm text-gray-500 mb-4 flex items-center gap-1">
          <a href="#" class="hover:text-[#8B4513] transition-colors">Beranda</a>
          <span>/</span>
          <a href="#" class="hover:text-[#8B4513] transition-colors">Profil User</a>
          <span>/</span>
          <span class="text-[#2C2C2C] font-medium">Riwayat Donasi</span>
        </nav>

        <!-- Page Title -->
        <h1 class="text-2xl font-bold text-[#2C2C2C] mb-6">Riwayat Donasi</h1>

        <!-- Filter Card -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <!-- Search -->
            <div>
              <label class="block text-xs font-medium text-gray-500 mb-1">Cari Campaign</label>
              <input
                v-model="searchTerm"
                @input="handleSearch"
                type="text"
                placeholder="Nama campaign atau no. transaksi..."
                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm text-[#2C2C2C] placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-transparent"
              />
            </div>

            <!-- Status Filter -->
            <div>
              <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
              <select
                v-model="statusFilter"
                @change="handleStatusChange"
                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm text-[#2C2C2C] focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-transparent bg-white"
              >
                <option value="semua">Semua Status</option>
                <option value="berhasil">Berhasil</option>
                <option value="pending">Pending</option>
                <option value="gagal">Gagal</option>
              </select>
            </div>

          </div>
        </div>

        <!-- Donation List Card -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">

          <!-- Loading skeleton -->
          <template v-if="store.loading">
            <div
              v-for="i in 5"
              :key="i"
              class="flex items-center gap-4 px-6 py-4 border-b border-gray-100 last:border-b-0 animate-pulse"
            >
              <div class="w-12 h-12 rounded-lg bg-gray-200 flex-shrink-0"></div>
              <div class="flex-1 space-y-2">
                <div class="h-3 bg-gray-200 rounded w-3/4"></div>
                <div class="h-3 bg-gray-200 rounded w-1/2"></div>
              </div>
              <div class="flex flex-col items-end gap-2">
                <div class="h-3 bg-gray-200 rounded w-16"></div>
                <div class="h-5 bg-gray-200 rounded-full w-14"></div>
              </div>
            </div>
          </template>

          <!-- Error state -->
          <div v-else-if="store.error" class="py-16 flex flex-col items-center gap-3">
            <svg class="w-12 h-12 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <p class="text-sm font-medium text-gray-500">{{ store.error }}</p>
            <button
              @click="loadPage(currentPage)"
              class="mt-2 px-5 py-2 bg-[#8B4513] text-white rounded-lg text-sm font-medium hover:bg-[#6b3410] transition-colors"
            >
              Coba Lagi
            </button>
          </div>

          <!-- List has results -->
          <template v-else-if="store.donations.length > 0">
            <div
              v-for="(donation, index) in store.donations"
              :key="donation.id_donasi"
              :class="['flex items-center gap-4 px-6 py-4 hover:bg-gray-50 transition-colors', index < store.donations.length - 1 ? 'border-b border-gray-100' : '']"
            >
              <!-- Thumbnail -->
              <div class="w-12 h-12 rounded-lg bg-[#F5F0E8] flex-shrink-0 flex items-center justify-center">
                <span class="text-base font-bold text-[#8B4513]">
                  {{ donation.judul_campaign?.charAt(0) ?? '?' }}
                </span>
              </div>

              <!-- Campaign Info -->
              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-[#2C2C2C] truncate">
                  {{ donation.judul_campaign }}
                </p>
                <p v-if="donation.nama_lembaga" class="text-xs text-gray-500 truncate mt-0.5">
                  {{ donation.nama_lembaga }}
                </p>
                <p v-if="donation.nomor_transaksi" class="text-xs text-gray-400 mt-0.5">
                  No. Transaksi: <span class="font-mono">{{ donation.nomor_transaksi }}</span>
                </p>
              </div>

              <!-- Date / Time -->
              <div class="hidden md:flex flex-col items-end gap-0.5 flex-shrink-0 text-right">
                <p class="text-sm text-gray-700">{{ formatDate(donation.created_at) }}</p>
                <p class="text-xs text-gray-400">{{ formatTime(donation.created_at) }}</p>
              </div>

              <!-- Amount + Method + Badge -->
              <div class="flex-shrink-0 flex flex-col items-end gap-1">
                <p class="text-sm font-bold text-[#8B4513]">{{ formatCurrency(donation.nominal) }}</p>
                <p v-if="donation.metode_pembayaran" class="text-xs text-gray-400">{{ donation.metode_pembayaran }}</p>
                <span :class="badgeClass(donation.status_pembayaran)">
                  {{ statusLabel(donation.status_pembayaran) }}
                </span>
              </div>

              <!-- Actions -->
              <div class="flex-shrink-0 flex flex-col gap-2 ml-2">
                <button
                  @click="viewDetail(donation.id_donasi)"
                  class="px-3 py-1.5 border border-[#8B4513] text-[#8B4513] rounded-lg text-xs font-medium hover:bg-[#8B4513] hover:text-white transition-colors"
                >
                  Lihat Detail
                </button>
                <button
                  v-if="donation.status_pembayaran === 'berhasil'"
                  @click="viewReceipt(donation.id_donasi)"
                  class="px-3 py-1.5 border border-gray-300 text-gray-600 rounded-lg text-xs font-medium hover:bg-gray-100 transition-colors"
                >
                  Lihat Bukti
                </button>
              </div>
            </div>
          </template>

          <!-- Empty state -->
          <div v-else class="py-16 flex flex-col items-center gap-3">
            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <p class="text-sm font-medium text-gray-500">Belum ada riwayat donasi</p>
          </div>

        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="flex items-center justify-between">
          <p class="text-sm text-gray-500">
            Halaman {{ currentPage }} dari {{ totalPages }}
            ({{ store.pagination.total }} donasi)
          </p>

          <div class="flex items-center gap-1">
            <button
              @click="loadPage(currentPage - 1)"
              :disabled="currentPage === 1 || store.loading"
              class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </button>

            <button
              v-for="page in visiblePages"
              :key="page"
              @click="loadPage(page)"
              :disabled="store.loading"
              :class="[
                'w-8 h-8 flex items-center justify-center rounded-lg text-sm font-medium transition-colors',
                currentPage === page
                  ? 'bg-[#8B4513] text-white'
                  : 'border border-gray-200 text-gray-700 hover:bg-gray-100'
              ]"
            >
              {{ page }}
            </button>

            <button
              @click="loadPage(currentPage + 1)"
              :disabled="currentPage === totalPages || store.loading"
              class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>
        </div>

      </div>
    </main>

    <Footer />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import Navbar from '@/components/shared/Navbar.vue'
import Footer from '@/components/shared/Footer.vue'
import { useDonationStore } from '@/stores/donation'
import api from '@/api/axios'

const router = useRouter()
const store = useDonationStore()

const searchTerm = ref('')
const statusFilter = ref('semua')
const currentPage = ref(1)

function viewDetail(id) {
  router.push(`/donations/${id}`)
}

async function viewReceipt(id) {
  try {
    const res = await api.get(`/donations/${id}/receipt`)
    const url = res.data.data?.bukti_pdf_url
    if (url) {
      window.open(url, '_blank')
    } else {
      window.open(`/donations/${id}/receipt-pdf`, '_blank')
    }
  } catch {
    window.open(`/donations/${id}/receipt-pdf`, '_blank')
  }
}

const totalPages = computed(() =>
  store.pagination.total > 0
    ? Math.ceil(store.pagination.total / store.pagination.per_page)
    : 0
)

const visiblePages = computed(() => {
  const total = totalPages.value
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)
  const cur = currentPage.value
  const pages = new Set([1, total, cur])
  if (cur > 1) pages.add(cur - 1)
  if (cur < total) pages.add(cur + 1)
  return [...pages].sort((a, b) => a - b)
})

function loadPage(page) {
  currentPage.value = page
  store.fetchHistory({ page, search: searchTerm.value, status: statusFilter.value })
}

let searchTimer = null
function handleSearch() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    currentPage.value = 1
    store.fetchHistory({ page: 1, search: searchTerm.value, status: statusFilter.value })
  }, 400)
}

function handleStatusChange() {
  currentPage.value = 1
  store.fetchHistory({ page: 1, search: searchTerm.value, status: statusFilter.value })
}

const fmt = new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'long', year: 'numeric', timeZone: 'Asia/Jakarta' })
const fmtTime = new Intl.DateTimeFormat('id-ID', { hour: '2-digit', minute: '2-digit', timeZone: 'Asia/Jakarta' })

const formatDate = (s) => fmt.format(new Date(s))
const formatTime = (s) => fmtTime.format(new Date(s)) + ' WIB'
const formatCurrency = (n) =>
  new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n)

const badgeClass = (status) => {
  const base = 'px-2.5 py-0.5 rounded-full text-xs font-medium'
  const map = { berhasil: 'bg-green-100 text-green-700', pending: 'bg-amber-100 text-amber-700', gagal: 'bg-red-100 text-red-700' }
  return `${base} ${map[status] ?? 'bg-gray-100 text-gray-600'}`
}
const statusLabel = (s) => ({ berhasil: 'Berhasil', pending: 'Pending', gagal: 'Gagal' }[s] ?? s)

onMounted(() => store.fetchHistory({ page: 1 }))
</script>
