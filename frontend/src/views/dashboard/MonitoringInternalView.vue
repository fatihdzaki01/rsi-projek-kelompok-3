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

        <div class="flex items-center justify-between mb-4">
          <div class="flex gap-2">
            <button
              type="button"
              @click="switchMode('rest')"
              class="px-4 py-2 rounded-lg text-sm font-medium transition"
              :class="
                !useGrpc
                  ? 'bg-[#8B4513] text-white'
                  : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
              "
            >
              REST API
            </button>

            <button
              type="button"
              @click="switchMode('grpc')"
              class="px-4 py-2 rounded-lg text-sm font-medium transition"
              :class="
                useGrpc
                  ? 'bg-[#8B4513] text-white'
                  : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
              "
            >
              gRPC Streaming
            </button>
          </div>

          <div v-if="useGrpc" class="text-sm font-medium">
            <span v-if="streamStatus === 'connecting'" class="text-yellow-600">
              🟡 Connecting...
            </span>

            <span v-else-if="streamStatus === 'connected'" class="text-green-600">
              🟢 Live
            </span>

            <span v-else-if="streamStatus === 'error'" class="text-red-600">
              🔴 Error
            </span>

            <span v-else-if="streamStatus === 'ended'" class="text-gray-500">
              ⚪ Ended
            </span>
          </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex items-center justify-center py-20">
          <div class="w-8 h-8 border-2 border-[#8B4513] border-t-transparent rounded-full animate-spin" />
        </div>

        <!-- Error -->
        <div v-else-if="errorMessage" class="bg-white rounded-2xl shadow-sm p-8 text-center">
          <p class="text-sm text-red-500">{{ errorMessage }}</p>
        </div>

        <template v-else-if="data">
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
          <section class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-4">
            <div class="bg-white rounded-2xl shadow-sm p-5 text-center">
              <p class="text-2xl font-bold text-[#1a2744]">{{ data.jumlah_donatur }}</p>
              <p class="text-xs text-gray-500 mt-1">Donatur</p>
            </div>
            <div v-if="data.total_penerima_manfaat" class="bg-white rounded-2xl shadow-sm p-5 text-center">
              <p class="text-2xl font-bold text-[#1a2744]">{{ data.total_penerima_manfaat.toLocaleString('id-ID') }}</p>
              <p class="text-xs text-gray-500 mt-1">Target Penerima</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm p-5 text-center">
              <p class="text-2xl font-bold text-[#8B4513]">{{ formatTimeRemaining(data.tanggal_selesai) }}</p>
              <p class="text-xs text-gray-500 mt-1">Waktu Tersisa</p>
            </div>
          </section>

          <!-- Donatur Terbaru -->
          <section class="bg-white rounded-2xl shadow-sm p-6 mb-4">
            <h2 class="text-sm font-bold text-[#1a2744] mb-4">Donatur Terbaru</h2>

            <div v-if="!data.donatur_terbaru || data.donatur_terbaru.length === 0" class="text-sm text-gray-400 text-center py-6">
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

            <PaginationBar
              v-if="data.donatur_pagination && data.donatur_pagination.last_page > 1"
              :currentPage="currentPage"
              :totalPages="data.donatur_pagination.last_page"
              :perPage="itemsPerPage"
              :total="data.donatur_pagination.total"
              @update:currentPage="goToPage"
              @update:perPage="changePerPage"
            />
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
import { onMounted, onUnmounted, ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/api/axios'
import TheNavbar from '@/components/shared/Navbar.vue'
import TheFooter from '@/components/shared/Footer.vue'
import PaginationBar from '@/components/ui/PaginationBar.vue'
import { formatTimeRemaining } from '@/utils/time'

import * as grpcWebPb from '@berbagive/grpc-client'
import * as monitoringPb from '@berbagive/grpc-client/monitoring_pb.js'

const CampaignMonitoringServiceClient = grpcWebPb.CampaignMonitoringServiceClient
const CampaignMonitoringRequest = monitoringPb.CampaignMonitoringRequest

const route = useRoute()
const campaignId = route.params.id

const data = ref(null)
const loading = ref(true)
const errorMessage = ref('')
const currentPage = ref(1)
const itemsPerPage = ref(15)
const grpcClient = new CampaignMonitoringServiceClient('http://localhost:8090')

const useGrpc = ref(false)
const streamStatus = ref('')
let grpcStream = null

function mapGrpcResponse(response) {
  return {
    id_campaign: response.getIdCampaign(),
    judul: response.getJudul(),
    status: response.getStatus(),
    nama_lembaga: response.getNamaLembaga(),
    nama_kategori: response.getNamaKategori(),
    target_dana: response.getTargetDana(),
    dana_terkumpul: response.getDanaTerkumpul(),
    progress_persen: response.getProgressPersen(),
    jumlah_donatur: response.getJumlahDonatur(),
    hari_tersisa: response.getHariTersisa(),
    tanggal_mulai: response.getTanggalMulai(),
    tanggal_selesai: response.getTanggalSelesai(),

    donatur_terbaru: response.getDonaturTerbaruList().map((donatur) => ({
      nama: donatur.getNama(),
      created_at: donatur.getCreatedAt(),
      nominal: donatur.getNominal(),
    })),

    // Tidak tersedia di monitoring.proto
    total_penerima_manfaat: null,
    donatur_pagination: null,
  }
}

function startGrpcStream() {
  stopGrpcStream()

  const request = new CampaignMonitoringRequest()
  request.setIdCampaign(Number(campaignId))

  streamStatus.value = 'connecting'
  loading.value = true
  errorMessage.value = ''

  grpcStream = grpcClient.streamCampaignMonitoring(request, {})

  grpcStream.on('data', (response) => {
    console.log('gRPC stream data:', response)

    data.value = mapGrpcResponse(response)

    loading.value = false
    streamStatus.value = 'connected'
  })

  grpcStream.on('error', (err) => {
    console.error('gRPC stream error:', err)

    loading.value = false
    streamStatus.value = 'error'
    errorMessage.value = 'Gagal terhubung ke gRPC streaming.'
  })

  grpcStream.on('end', () => {
    console.log('gRPC stream ended')

    streamStatus.value = 'ended'
    grpcStream = null
  })
}

function stopGrpcStream() {
  if (grpcStream) {
    grpcStream.cancel()
    grpcStream = null
  }

  streamStatus.value = ''
}

function switchMode(mode) {
  if (mode === 'grpc') {
    useGrpc.value = true
    startGrpcStream()
    return
  }

  useGrpc.value = false
  stopGrpcStream()
  fetchMonitoring()
}

async function fetchMonitoring() {
  loading.value = true
  errorMessage.value = ''
  try {
    const res = await api.get(`/campaigns/${campaignId}/monitoring`, {
      params: { page: currentPage.value, per_page: itemsPerPage.value },
    })
    data.value = res.data.data
  } catch (e) {
    errorMessage.value = e.response?.data?.message || 'Gagal memuat monitoring.'
  } finally {
    loading.value = false
  }
}

function testGrpcUnary() {
  const request = new CampaignMonitoringRequest()

  request.setIdCampaign(Number(campaignId))

  grpcClient.getCampaignMonitoring(
    request,
    {},
    (err, response) => {
      if (err) {
        console.error('gRPC error:', err)
        return
      }

      console.log('gRPC response:', response)
    }
  )
}

function goToPage(page) {
  currentPage.value = page
  fetchMonitoring()
}

function changePerPage(perPage) {
  itemsPerPage.value = perPage
  currentPage.value = 1
  fetchMonitoring()
}

onMounted(() => {fetchMonitoring()})
onUnmounted(() => {stopGrpcStream()})

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
