<template>
  <div class="min-h-screen flex flex-col bg-background">
    <TheNavbar />

    <main class="flex-1 max-w-5xl mx-auto w-full px-4 py-6">

      <!-- Loading -->
      <div v-if="loading" class="flex flex-col items-center justify-center py-20">
        <div class="w-8 h-8 border-2 border-[#8B4513] border-t-transparent rounded-full animate-spin mb-3" />
        <p class="text-sm text-gray-400">Memuat campaign...</p>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="flex flex-col items-center justify-center py-20 text-center">
        <div class="w-16 h-16 rounded-full bg-stone-200 flex items-center justify-center mb-4">
          <svg class="w-8 h-8 text-stone-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
          </svg>
        </div>
        <h2 class="text-lg font-bold text-gray-700 mb-1">Campaign tidak tersedia</h2>
        <p class="text-sm text-gray-400">{{ error }}</p>
      </div>

      <!-- Content -->
      <template v-else>
        <div class="grid grid-cols-1 lg:grid-cols-[1fr_300px] gap-6 items-start">

        <!-- ── LEFT COLUMN ─────────────────────────────────────── -->
        <div class="flex flex-col gap-5">

          <!-- Hero image with category badge -->
          <div class="relative rounded-xl overflow-hidden">
            <img
              :src="campaign.foto_campaign_url"
              :alt="`Foto kampanye: ${campaign.judul}`"
              class="w-full aspect-[16/9] object-cover"
            />
            <div class="absolute top-3 left-3">
              <div
                class="text-[10px] font-bold tracking-wider uppercase px-2.5 py-1 rounded-full text-white"
                :style="{ backgroundColor: '#2E8B74' }"
              >
                {{ campaign.nama_kategori }}
              </div>
            </div>
          </div>

          <!-- Campaign title + report button -->
          <div class="flex items-start justify-between gap-3">
            <div class="flex flex-col gap-2">
              <h1 class="text-2xl font-bold text-foreground text-balance leading-snug">
                {{ campaign.judul }}
              </h1>
              <router-link
                v-if="komunitas.id_komunitas"
                :to="`/communities/${komunitas.id_komunitas}`"
                class="inline-flex items-center gap-2 self-start px-3 py-1.5 rounded-full border border-[#8B4513]/20 bg-[#FDF5EE] text-xs font-medium text-[#8B4513] hover:bg-[#f8e8d8] transition-colors"
              >
                <span class="w-5 h-5 rounded-full bg-[#8B4513] text-white flex items-center justify-center text-[9px] font-bold flex-shrink-0">
                  {{ komunitas.nama_lembaga.charAt(0).toUpperCase() }}
                </span>
                <span class="truncate max-w-48">{{ komunitas.nama_lembaga }}</span>
              </router-link>
            </div>
            <button
              v-if="authStore.isLoggedIn && authStore.isDonor"
              @click="showReportModal = true"
              class="shrink-0 flex items-center gap-1.5 text-xs text-gray-400 hover:text-red-500 transition-colors mt-1"
              title="Laporkan campaign ini"
            >
              <Flag class="size-4" />
              Laporkan
            </button>
          </div>

          <!-- Fund progress row -->
          <div class="flex flex-col gap-2">
            <div class="flex items-end justify-between gap-2">
              <div>
                <p class="text-[10px] font-semibold tracking-widest text-muted-foreground uppercase">
                  Terkumpul
                </p>
                <p class="text-xl font-bold text-foreground">
                  {{ formatRupiah(campaign.dana_terkumpul) }}
                </p>
              </div>
              <div class="text-right">
                <p class="text-[10px] font-semibold tracking-widest text-muted-foreground uppercase">
                  Target
                </p>
                <p class="text-sm font-semibold text-foreground">
                  {{ formatRupiah(campaign.target_dana) }}
                </p>
              </div>
            </div>

            <!-- Progress bar -->
            <div
              class="w-full h-1.5 bg-secondary rounded-full overflow-hidden"
              role="progressbar"
              :aria-valuenow="pct"
              aria-valuemin="0"
              aria-valuemax="100"
              :aria-label="`${pct}% dari target terkumpul`"
            >
              <div
                class="h-full rounded-full transition-all duration-500"
                :style="{ width: `${pct}%`, backgroundColor: '#8B4513' }"
              />
            </div>
          </div>

          <!-- Stats row -->
          <div class="flex items-center gap-5">
            <div class="flex items-center gap-1.5 text-sm text-foreground">
              <Heart
                class="size-4 text-[#C0392B]"
                aria-hidden="true"
              />
              <span>
                <span class="font-semibold">
                  {{ campaign.jumlah_donatur.toLocaleString('id-ID') }}
                </span>
                Donatur
              </span>
            </div>
            <div v-if="campaign.total_penerima_manfaat" class="flex items-center gap-1.5 text-sm text-foreground">
              <Users class="size-4 text-[#8B4513]" aria-hidden="true" />
              <span>
                <span class="font-semibold">{{ campaign.total_penerima_manfaat.toLocaleString('id-ID') }}</span>
                Penerima
              </span>
            </div>
            <div class="flex items-center gap-1.5 text-sm text-foreground">
              <Clock
                class="size-4 text-muted-foreground"
                aria-hidden="true"
              />
              <span class="font-semibold">{{ timeRemaining }}</span>
            </div>
          </div>

          <!-- Report success alert -->
          <div
            v-if="reportAlert"
            class="text-sm text-green-700 bg-green-50 border border-green-200 rounded-xl px-3 py-2"
          >
            {{ reportAlert }}
          </div>

          <!-- Monitoring link -->
          <div class="flex items-center gap-2">
            <button
              @click="router.push(`/campaigns/${campaign.id_campaign}/monitoring`)"
              class="text-xs font-medium text-[#8B4513] hover:text-[#6b3410] underline-offset-2 hover:underline transition-colors"
            >
              Lihat Monitoring Campaign
            </button>
          </div>

          <!-- Campaign story -->
          <CampaignStory :deskripsi="campaign.deskripsi" :update-post="updates" />
        </div>

        <!-- ── RIGHT COLUMN (sticky sidebar) ───────────────────── -->
        <DonationSidebar v-if="campaign.tombol_donasi_aktif" :campaign-id="campaign.id_campaign" :campaign-status="campaign.status" />
      </div>
      </template>
      <!-- Report Modal -->
      <ReportCampaignModal
        :show="showReportModal"
        :campaign-id="campaign.id_campaign"
        @close="showReportModal = false"
        @success="handleReportSuccess"
      />
    </main>

    <TheFooter />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Heart, Clock, Flag, Building2, Users } from 'lucide-vue-next'
import api from '@/api/axios'
import TheNavbar from '@/components/shared/Navbar.vue'
import TheFooter from '@/components/shared/Footer.vue'
import DonationSidebar from '@/components/donation/DonationSidebar.vue'
import CampaignStory from '@/components/campaign/CampaignStory.vue'
import ReportCampaignModal from '@/components/campaign/ReportCampaignModal.vue'
import { useAuthStore } from '@/stores/auth'
import { formatTimeRemaining } from '@/utils/time'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const campaign = ref({
  id_campaign: 0,
  judul: '',
  nama_lembaga: '',
  dana_terkumpul: 0,
  target_dana: 0,
  foto_campaign_url: '',
  tanggal_selesai: '',
  nama_kategori: '',
  status: '',
  progress_persen: 0,
  jumlah_donatur: 0,
  total_penerima_manfaat: 0,
})
const komunitas = ref({ id_komunitas: null, nama_lembaga: '' })
const updates = ref([])
const loading = ref(true)
const error = ref('')
const showReportModal = ref(false)
const reportSuccess = ref(false)
const reportAlert = ref('')

async function fetchCampaign() {
  try {
    const res = await api.get(`/campaigns/${route.params.id}/public`)
    campaign.value = res.data.data.campaign
    komunitas.value = res.data.data.komunitas || {}
    updates.value = res.data.data.update_post || []
  } catch (e) {
    error.value = e.response?.data?.message || 'Halaman tidak dapat dimuat.'
  } finally {
    loading.value = false
  }
}

onMounted(fetchCampaign)

const pct = computed(() => {
  const c = campaign.value
  if (!c.target_dana) return 0
  return Math.min(100, Math.round((c.dana_terkumpul / c.target_dana) * 100))
})

const timeRemaining = computed(() => formatTimeRemaining(campaign.value.tanggal_selesai))

function formatRupiah(amount) {
  return 'Rp ' + Number(amount).toLocaleString('id-ID')
}

function handleReportSuccess() {
  reportSuccess.value = true
  reportAlert.value = 'Laporan berhasil dikirim. Terima kasih atas partisipasi Anda.'
  setTimeout(() => {
    reportSuccess.value = false
    reportAlert.value = ''
  }, 5000)
}
</script>
