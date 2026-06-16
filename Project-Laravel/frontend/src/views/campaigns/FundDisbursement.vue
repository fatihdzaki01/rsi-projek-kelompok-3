<template>
  <div class="min-h-screen bg-[#F5F0E8] flex flex-col">
    <Navbar />

    <main class="flex-1 py-8">
      <div class="max-w-3xl mx-auto px-6">

        <nav class="text-sm text-gray-500 mb-4 flex items-center gap-1">
          <router-link to="/" class="hover:text-[#8B4513] transition-colors">Beranda</router-link>
          <span>/</span>
          <router-link to="/communities/dashboard" class="hover:text-[#8B4513] transition-colors">Dashboard Komunitas</router-link>
          <span>/</span>
          <router-link v-if="campaign" :to="`/communities/campaigns/${campaign.id_campaign}/withdrawals`" class="hover:text-[#8B4513] transition-colors">Riwayat Pencairan</router-link>
          <span v-if="campaign">/</span>
          <span class="text-[#2C2C2C] font-medium">Ajukan Pencairan</span>
        </nav>

        <div v-if="loading" class="flex flex-col items-center justify-center py-20">
          <div class="w-8 h-8 border-2 border-[#8B4513] border-t-transparent rounded-full animate-spin mb-3" />
          <p class="text-sm text-gray-400">Memuat data campaign...</p>
        </div>

        <div v-else-if="error" class="bg-white rounded-xl shadow-sm p-8 text-center">
          <p class="text-sm text-red-500 mb-4">{{ error }}</p>
          <button @click="fetchCampaign" class="px-5 py-2 bg-[#8B4513] text-white rounded-lg text-sm font-medium hover:bg-[#6b3410] transition-colors">Coba Lagi</button>
        </div>

        <template v-else>
          <div v-if="successMsg" class="p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700 mb-4">{{ successMsg }}</div>
          <div v-if="submitError" class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-600 mb-4">{{ submitError }}</div>

          <div class="bg-white rounded-xl shadow-sm border border-stone-100 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-stone-100">
              <h1 class="text-sm font-bold text-[#2C2C2C]">Ajukan Pencairan Dana</h1>
              <p class="text-xs text-gray-400 mt-1">Isi form di bawah untuk mengajukan pencairan dana campaign</p>
            </div>
            <div class="px-6 py-5 space-y-5">

              <CampaignHeroCard v-if="campaign" :campaign="campaign" />

              <NormalInput v-model="form.nominal" label="Nominal Pencairan" type="number" placeholder="Masukkan nominal" :presets="nominalPresets" />

              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Keterangan <span class="text-red-400">*</span></label>
                <textarea
                  v-model="form.keterangan"
                  rows="4"
                  class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm text-[#2C2C2C] focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-transparent resize-none"
                  placeholder="Jelaskan rencana penggunaan dana..."
                />
                <p v-if="errors.keterangan" class="mt-1 text-xs text-red-500">{{ errors.keterangan }}</p>
              </div>

              <UploadDokumen v-model="form.url_proposal" @update:modelValue="handleFile" />

              <InfoBanner :is-first-time="isFirstTime" />

              <button
                @click="handleSubmit"
                :disabled="!isValid || submitting"
                class="w-full px-5 py-3 bg-[#8B4513] text-white rounded-lg text-sm font-medium hover:bg-[#6b3410] transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {{ submitting ? 'Mengirim...' : 'Ajukan Pencairan' }}
              </button>

            </div>
          </div>
        </template>
      </div>
    </main>

    <AppFooter />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/api/axios'
import Navbar from '@/components/shared/Navbar.vue'
import AppFooter from '@/components/shared/AppFooter.vue'
import CampaignHeroCard from '@/components/campaign/PencairanPerCampaign/CampaignHeroCard.vue'
import NormalInput from '@/components/campaign/PencairanPerCampaign/NormalInput.vue'
import UploadDokumen from '@/components/campaign/PencairanPerCampaign/UploadDokumen.vue'
import InfoBanner from '@/components/campaign/PencairanPerCampaign/InfoBanner.vue'

const route = useRoute()
const router = useRouter()

const loading = ref(true)
const error = ref('')
const submitting = ref(false)
const successMsg = ref('')
const submitError = ref('')
const campaign = ref(null)
const riwayatBerhasil = ref([])
const errors = ref({})

const isFirstTime = computed(() => riwayatBerhasil.value.length === 0)

const nominalPresets = [500000, 1000000, 2000000, 5000000]

const form = reactive({
  nominal: '',
  keterangan: '',
  url_proposal: '',
})

const isValid = computed(() =>
  Number(form.nominal) > 0 &&
  form.keterangan.trim() !== '' &&
  form.url_proposal.trim() !== ''
)

function handleFile(val) {
  form.url_proposal = val || ''
}

function rupiah(val) {
  return 'Rp ' + (Number(val) || 0).toLocaleString('id-ID')
}

async function fetchCampaign() {
  loading.value = true
  error.value = ''
  try {
    const id = route.params.id
    const res = await api.get(`/communities/campaigns/${id}/withdrawals`)
    const data = res.data.data || res.data
    campaign.value = data.campaign || null
    riwayatBerhasil.value = (data.pencairan || []).filter(p => p.status === 'selesai' || p.status === 'disetujui')
  } catch (e) {
    error.value = e.response?.data?.message || 'Gagal memuat data campaign'
  } finally {
    loading.value = false
  }
}

async function handleSubmit() {
  if (!isValid.value) return
  submitting.value = true
  successMsg.value = ''
  submitError.value = ''
  errors.value = {}

  try {
    await api.post('/communities/campaigns/withdrawals', {
      id_campaign: route.params.id,
      nominal: Number(form.nominal),
      keterangan: form.keterangan,
      url_proposal: form.url_proposal,
    })
    successMsg.value = 'Pengajuan pencairan berhasil dikirim!'
    form.nominal = ''
    form.keterangan = ''
    form.url_proposal = ''
    setTimeout(() => router.push(`/communities/campaigns/${route.params.id}/withdrawals`), 1500)
  } catch (e) {
    const status = e.response?.status
    const errData = e.response?.data?.errors || {}
    const message = e.response?.data?.message || ''
    if (status === 400 || status === 422) {
      errors.value = Object.fromEntries(
        Object.entries(errData).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v])
      )
      if (Object.keys(errors.value).length === 0) {
        submitError.value = message || 'Data tidak valid'
      }
    } else {
      submitError.value = message || 'Gagal mengajukan pencairan'
    }
  } finally {
    submitting.value = false
  }
}

onMounted(fetchCampaign)
</script>
