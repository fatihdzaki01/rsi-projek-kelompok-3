<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { ChevronRight, FolderOpen } from 'lucide-vue-next'
import Navbar from '@/components/shared/Navbar.vue'
import AppFooter from '@/components/shared/AppFooter.vue'
import PaginationBar from '@/components/ui/PaginationBar.vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/api/axios'

const router = useRouter()
const authStore = useAuthStore()

const FILTERS = [
  { key: 'semua', label: 'Semua' },
  { key: 'aktif', label: 'Aktif' },
  { key: 'selesai', label: 'Selesai' },
  { key: 'ditolak', label: 'Ditolak' },
]

const activeFilter = ref('semua')
const campaigns = ref([])
const isLoading = ref(true)
const currentPage = ref(1)
const perPage = ref(15)
const pagination = ref({ current_page: 1, last_page: 1, total: 0 })

const fetchCampaigns = async () => {
  isLoading.value = true
  try {
    const params = { page: currentPage.value, per_page: perPage.value }
    if (activeFilter.value !== 'semua') params.status = activeFilter.value
    const res = await api.get('/communities/campaigns', { params })
    const data = res.data.data || res.data
    campaigns.value = data.data || data
    pagination.value = data.meta || { current_page: data.current_page || 1, last_page: data.last_page || 1, total: data.total || 0 }
  } catch (e) {
    if (e.response?.status === 401) router.push('/login')
  } finally {
    isLoading.value = false
  }
}

const loadPage = (page) => {
  currentPage.value = page
  fetchCampaigns()
}

const changePerPage = (pp) => {
  perPage.value = pp
  currentPage.value = 1
  fetchCampaigns()
}

watch(activeFilter, () => { currentPage.value = 1; fetchCampaigns() })
onMounted(fetchCampaigns)

// ---------- Helpers ----------
const formatRupiah = (n) => 'Rp ' + Number(n || 0).toLocaleString('id-ID')

const formatRange = (start, end) => {
  if (!start || !end) return '-'
  const s = new Date(start)
  const e = new Date(end)
  const monthsShort = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']
  const sameYear = s.getFullYear() === e.getFullYear()
  const sStr = `${s.getDate()} ${monthsShort[s.getMonth()]}${sameYear ? '' : ' ' + s.getFullYear()}`
  const eStr = `${e.getDate()} ${monthsShort[e.getMonth()]} ${e.getFullYear()}`
  return `${sStr} – ${eStr}`
}

const progressPercent = (c) => {
  const target = Number(c.target_dana || 0)
  if (!target) return 0
  return Math.min(100, Math.round((Number(c.dana_terkumpul || 0) / target) * 100))
}

const statusClass = (status) => {
  switch ((status || '').toLowerCase()) {
    case 'aktif': return 'text-[#2E7D32]'
    case 'selesai': return 'text-gray-500'
    case 'ditolak': return 'text-[#DC2626]'
    case 'pending': return 'text-amber-600'
    case 'nonaktif': return 'text-gray-400'
    default: return 'text-gray-500'
  }
}

const goCampaign = (id) => router.push(`/campaigns/${id}`)
</script>

<template>
  <div class="min-h-screen bg-[#E8DDD0] flex flex-col">
    <Navbar />

    <main class="flex-1 px-4 py-6">
      <div class="max-w-xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="text-xs text-gray-500 mb-4">
          <router-link to="/" class="hover:text-[#8B4513]">Beranda</router-link>
          <span class="mx-1">›</span>
          <router-link to="/communities/dashboard" class="hover:text-[#8B4513]">Dashboard Komunitas</router-link>
          <span class="mx-1">›</span>
          <span class="text-[#1a2744] font-medium">Riwayat Campaign</span>
        </nav>

        <div class="bg-white rounded-2xl shadow-sm p-6">
          <h1 class="text-base font-semibold text-[#1a2744]">Riwayat Campaign</h1>
          <p class="text-xs text-gray-500 mb-4">
            Semua campaign yang pernah dibuat oleh komunitas Anda.
          </p>

          <!-- Filter tabs -->
          <div class="flex flex-wrap gap-2 mb-2">
            <button
              v-for="f in FILTERS"
              :key="f.key"
              @click="activeFilter = f.key"
              :class="[
                'text-xs font-medium rounded-full px-3 py-1 transition-colors',
                activeFilter === f.key
                  ? 'bg-[#8B4513] text-white'
                  : 'text-gray-600 hover:bg-gray-100',
              ]"
            >
              {{ f.label }}
            </button>
          </div>

          <!-- Loading skeleton -->
          <div v-if="isLoading" class="divide-y divide-gray-100">
            <div v-for="i in 3" :key="i" class="py-4">
              <div class="flex justify-between mb-2">
                <div class="h-4 w-48 bg-gray-200 rounded animate-pulse"></div>
                <div class="h-4 w-24 bg-gray-200 rounded animate-pulse"></div>
              </div>
              <div class="h-3 w-32 bg-gray-200 rounded animate-pulse mb-3"></div>
              <div class="h-1.5 w-full bg-gray-100 rounded-full"></div>
            </div>
          </div>

          <!-- Empty -->
          <div
            v-else-if="!campaigns.length"
            class="py-12 text-center text-gray-500"
          >
            <FolderOpen class="h-10 w-10 mx-auto text-gray-300 mb-2" />
            <p class="text-sm">Belum ada campaign yang dibuat.</p>
          </div>

          <!-- List -->
          <ul v-else class="divide-y divide-gray-100">
            <li
              v-for="c in campaigns"
              :key="c.id_campaign"
              class="py-4 flex items-start gap-3 cursor-pointer hover:bg-gray-50 -mx-2 px-2 rounded transition-colors"
              @click="goCampaign(c.id_campaign)"
            >
              <div class="flex-1 min-w-0">
                <!-- Baris atas -->
                <div class="flex justify-between items-start gap-3 mb-1">
                  <p class="text-sm font-medium text-[#1a2744] truncate">{{ c.judul }}</p>
                  <div class="text-right shrink-0">
                    <p class="text-sm font-semibold text-[#8B4513]">{{ formatRupiah(c.dana_terkumpul) }}</p>
                    <p :class="['text-xs font-medium mt-0.5 uppercase tracking-wide', statusClass(c.status)]">
                      {{ c.status }}
                    </p>
                  </div>
                </div>

                <!-- Tanggal -->
                <p class="text-xs text-gray-500 mb-2">
                  {{ formatRange(c.tanggal_mulai, c.tanggal_selesai) }}
                </p>

                <!-- Progress bar -->
                <div class="flex items-center gap-2">
                  <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                    <div
                      class="h-full bg-[#5BC8C0] transition-all"
                      :style="{ width: progressPercent(c) + '%' }"
                    ></div>
                  </div>
                  <span class="text-xs text-gray-500 w-8 text-right">{{ progressPercent(c) }}</span>
                </div>
              </div>

              <ChevronRight class="h-5 w-5 text-gray-400 shrink-0 mt-1" />
              <router-link
                :to="`/communities/campaigns/${c.id_campaign}/analysis`"
                class="text-[10px] font-medium text-[#8B4513] border border-[#8B4513]/20 rounded px-2 py-0.5 hover:bg-orange-50 transition-colors shrink-0"
                @click.stop
              >Analisis</router-link>
            </li>
          </ul>

          <PaginationBar
            v-if="pagination.last_page > 1"
            :currentPage="currentPage"
            :totalPages="pagination.last_page"
            :perPage="perPage"
            :total="pagination.total"
            @update:currentPage="loadPage"
            @update:perPage="changePerPage"
          />
        </div>
      </div>
    </main>

    <AppFooter />
  </div>
</template>
