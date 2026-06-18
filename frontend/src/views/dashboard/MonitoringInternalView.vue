<template>
  <div class="min-h-screen flex flex-col bg-[#F5F0E8]">
    <TheNavbar />

    <main class="flex-1 px-4 py-6">
      <div class="max-w-2xl mx-auto">
        <nav class="text-xs text-gray-500 mb-4">
          <router-link to="/" class="hover:text-[#8B4513]">Beranda</router-link>
          <span class="mx-1">›</span>
          <router-link :to="`/campaigns/${campaignId}`" class="hover:text-[#8B4513]">Campaign</router-link>
          <span class="mx-1">›</span>
          <span class="text-[#1a2744] font-medium">Monitoring</span>
        </nav>

        <!-- Loading -->
        <div v-if="loading" class="flex items-center justify-center py-20">
          <div class="w-8 h-8 border-2 border-[#8B4513] border-t-transparent rounded-full animate-spin" />
        </div>

        <!-- Error -->
        <div v-else-if="errorMessage" class="bg-white rounded-2xl shadow-sm p-8 text-center">
          <p class="text-sm text-red-500">{{ errorMessage }}</p>
        </div>

        <template v-else>
          <!-- Card: Campaign Header -->
          <section class="bg-white rounded-2xl shadow-sm p-6 mb-4">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <h1 class="text-lg font-bold text-[#1a2744]">{{ data.judul }}</h1>
                <p class="text-sm text-gray-500 mt-1">{{ data.nama_lembaga }} • {{ data.nama_kategori }}</p>
              </div>
              <span
                class="shrink-0 px-3 py-1 rounded-full text-xs font-semibold"
                :class="statusBadgeClass"
              >
                {{ data.status }}
              </span>
            </div>

            <!-- Progress -->
            <div class="mt-4">
              <div class="flex justify-between text-sm mb-1.5">
                <span class="font-semibold text-[#1a2744]">{{ formatRupiah(data.dana_terkumpul) }}</span>
                <span class="text-gray-400">Target {{ formatRupiah(data.target_dana) }}</span>
              </div>
              <div class="w-full h-2 bg-stone-200 rounded-full overflow-hidden">
                <div
                  class="h-full rounded-full transition-all duration-500"
                  :style="{ width: `${data.progress_persen}%`, backgroundColor: '#8B4513' }"
                />
              </div>
              <p class="text-xs text-gray-400 mt-1">{{ data.progress_persen }}% terkumpul</p>
            </div>
          </section>

          <!-- Stats Cards -->
          <section class="grid grid-cols-2 gap-4 mb-4">
            <div class="bg-white rounded-2xl shadow-sm p-5 text-center">
              <p class="text-2xl font-bold text-[#1a2744]">{{ data.jumlah_donatur }}</p>
              <p class="text-xs text-gray-500 mt-1">Donatur</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm p-5 text-center">
              <p class="text-2xl font-bold text-[#8B4513]">{{ formatTimeRemaining(data.tanggal_selesai) }}</p>
              <p class="text-xs text-gray-500 mt-1">Waktu Tersisa</p>
            </div>
          </section>

          <!-- Donatur Terbaru -->
          <section class="bg-white rounded-2xl shadow-sm p-6 mb-4">
            <h2 class="text-sm font-bold text-[#1a2744] mb-4">Donatur Terbaru</h2>

            <div v-if="data.donatur_terbaru?.length === 0" class="text-sm text-gray-400 text-center py-6">
              Belum ada donatur.
            </div>

            <div v-else class="space-y-2">
              <div
                v-for="(d, i) in data.donatur_terbaru"
                :key="i"
                class="flex items-center justify-between py-2 border-b border-stone-50 last:border-b-0"
              >
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-[#FDF5EE] flex items-center justify-center text-xs font-bold text-[#8B4513]">
                    {{ d.nama.charAt(0).toUpperCase() }}
                  </div>
                  <span class="text-sm text-gray-700">{{ d.nama }}</span>
                </div>
                <span class="text-xs text-gray-400">{{ formatDate(d.created_at) }}</span>
              </div>
            </div>
          </section>

          <!-- Back link -->
          <div class="text-center">
            <router-link
              :to="`/campaigns/${campaignId}`"
              class="text-sm text-[#8B4513] hover:text-[#6b3410] underline-offset-2 hover:underline transition-colors"
            >
              ← Kembali ke Campaign
            </router-link>
          </div>
        </template>
      </div>
    </main>

    <TheFooter />
  </div>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/api/axios'
import TheNavbar from '@/components/shared/Navbar.vue'
import TheFooter from '@/components/shared/Footer.vue'
import { formatTimeRemaining } from '@/utils/time'

const route = useRoute()
const campaignId = route.params.id

const data = ref(null)
const loading = ref(true)
const errorMessage = ref('')

async function fetchMonitoring() {
  loading.value = true
  errorMessage.value = ''
  try {
    const res = await api.get(`/campaigns/${campaignId}/monitoring`)
    data.value = res.data.data
  } catch (e) {
    errorMessage.value = e.response?.data?.message || 'Gagal memuat monitoring.'
  } finally {
    loading.value = false
  }
}

onMounted(fetchMonitoring)

const statusBadgeClass = computed(() => {
  const map = {
    aktif: 'bg-green-100 text-green-700',
    selesai: 'bg-blue-100 text-blue-700',
    menunggu_review: 'bg-amber-100 text-amber-700',
    ditolak: 'bg-red-100 text-red-700',
    nonaktif: 'bg-gray-100 text-gray-600',
  }
  return map[data.value?.status] || 'bg-gray-100 text-gray-600'
})

function formatRupiah(n) {
  return 'Rp ' + Number(n || 0).toLocaleString('id-ID')
}

function formatDate(s) {
  if (!s) return ''
  return new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }).format(new Date(s))
}
</script>
