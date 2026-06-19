<template>
  <div class="min-h-screen bg-[#F5F0E8] flex flex-col">
    <Navbar />

    <main class="flex-1 py-8">
      <div class="max-w-6xl mx-auto px-6">

        <nav class="text-sm text-gray-500 mb-6 flex items-center gap-1">
          <router-link to="/" class="hover:text-[#8B4513] transition-colors">Beranda</router-link>
          <span>/</span>
          <router-link to="/communities/withdrawals" class="hover:text-[#8B4513] transition-colors">Pencairan</router-link>
          <span>/</span>
          <span class="text-[#2C2C2C] font-medium">Ajukan Pencairan</span>
        </nav>

        <div v-if="loading" class="flex flex-col items-center justify-center py-20">
          <div class="w-8 h-8 border-2 border-[#8B4513] border-t-transparent rounded-full animate-spin mb-3" />
          <p class="text-sm text-gray-400">Memuat data penarikan...</p>
        </div>

        <template v-else>
          <!-- Info Disetujui -->
          <div class="mb-6 bg-white rounded-2xl shadow-sm border border-stone-100 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
              <h2 class="text-lg font-bold text-[#2C2C2C]">Pencairan Disetujui</h2>
              <p class="text-sm text-gray-500 mt-1">Maksimal pengajuan pencairan yang dapat disetujui untuk campaign ini adalah 5 kali.</p>
            </div>
            <div class="md:text-right">
              <p class="text-3xl font-black text-[#8B4513]">{{ approvedCount }} <span class="text-lg text-gray-400 font-medium">/ 5</span></p>
            </div>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- KIRI: Riwayat -->
            <div class="lg:col-span-2 space-y-6">
              <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-stone-100">
                  <h2 class="text-sm font-bold text-[#2C2C2C]">Riwayat Pencairan Dana</h2>
                </div>
                <div v-if="campaignHistory.length === 0" class="px-6 py-10 text-center text-sm text-gray-400">
                  Belum ada riwayat pencairan dana untuk campaign ini.
                </div>
                <div v-else>
                  <div v-for="(item, i) in campaignHistory" :key="item.id_pencairan" :class="['px-6 py-4', i < campaignHistory.length - 1 ? 'border-b border-stone-100' : '']">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                      <p class="text-sm font-medium text-[#2C2C2C]">{{ item.judul_campaign }}</p>
                      <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', statusBadge(item.status)]">{{ statusLabel(item.status) }}</span>
                    </div>
                    <p class="text-lg font-bold text-[#8B4513]">Rp {{ Number(item.nominal_diajukan).toLocaleString('id-ID') }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ item.tanggal_pengajuan ? new Date(item.tanggal_pengajuan).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' }) : '' }}</p>
                    <p v-if="item.alasan_penolakan" class="text-xs text-red-500 mt-1">Alasan ditolak: {{ item.alasan_penolakan }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- KANAN: Form & Rekening -->
            <div class="lg:col-span-1 space-y-6">
              
              <!-- Form Ajukan -->
              <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-stone-100">
                  <h2 class="text-sm font-bold text-[#2C2C2C]">Ajukan Pencairan Baru</h2>
                </div>
                <div class="px-6 py-5 space-y-4">
                  <div v-if="changeSuccess" class="p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ changeSuccess }}</div>
                  <div v-if="changeError" class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-600">{{ changeError }}</div>

                  <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Campaign</label>
                    <div class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-[#2C2C2C]">
                      <template v-if="activeCampaign">
                        <p class="font-medium truncate">{{ activeCampaign.judul }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">Saldo: Rp {{ activeCampaign.saldo_tersedia.toLocaleString('id-ID') }}</p>
                      </template>
                      <template v-else>
                        <p class="text-gray-400">Campaign tidak ditemukan atau tidak aktif</p>
                      </template>
                    </div>
                  </div>

                  <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Nominal Pencairan <span class="text-red-400">*</span></label>
                    <div class="relative">
                      <span class="absolute left-3 top-2 text-sm text-gray-500">Rp</span>
                      <input v-model="form.nominal_diajukan" type="number" class="w-full pl-9 px-3 py-2 border border-gray-200 rounded-lg text-sm text-[#2C2C2C] focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-transparent" />
                    </div>
                    <p v-if="errors.nominal_diajukan" class="mt-1 text-xs text-red-500">{{ errors.nominal_diajukan }}</p>
                  </div>

                  <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">URL Proposal <span class="text-red-400">*</span></label>
                    <input v-model="form.url_proposal" type="url" placeholder="https://" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm text-[#2C2C2C] focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-transparent" />
                    <p v-if="errors.url_proposal" class="mt-1 text-xs text-red-500">{{ errors.url_proposal }}</p>
                  </div>

                  <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Keterangan / Tujuan <span class="text-red-400">*</span></label>
                    <textarea v-model="form.keterangan" rows="3" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm text-[#2C2C2C] focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-transparent resize-none"></textarea>
                    <p v-if="errors.keterangan" class="mt-1 text-xs text-red-500">{{ errors.keterangan }}</p>
                  </div>

                  <button @click="handleSubmit" :disabled="submitting || !activeAccount || approvedCount >= 5" class="w-full py-2.5 bg-[#8B4513] text-white rounded-lg text-sm font-bold hover:bg-[#6b3410] transition-colors disabled:opacity-50 mt-2">
                    {{ submitting ? 'Mengirim...' : 'Ajukan Pencairan' }}
                  </button>
                  <p v-if="approvedCount >= 5" class="text-xs text-red-500 mt-2 text-center">Batas maksimal pencairan (5 kali) telah tercapai.</p>
                </div>
              </div>

              <!-- Rekening -->
              <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-stone-100 flex items-center gap-2">
                  <div class="w-1 h-4 bg-[#8B4513] rounded-full" />
                  <h1 class="text-sm font-bold text-[#2C2C2C]">Rekening Pencairan</h1>
                </div>
                <div class="px-6 py-5">
                  <div v-if="activeAccount" class="flex flex-col gap-2">
                    <p class="text-sm font-bold text-[#2C2C2C]">{{ activeAccount.nama_bank }}</p>
                    <p class="text-sm text-gray-500 bg-gray-50 px-3 py-2 rounded-lg border border-gray-100 inline-block font-mono">{{ activeAccount.nomor_rekening }}</p>
                    <p class="text-xs text-gray-400 mt-2 leading-relaxed">Dana ditransfer ke rekening ini. Jika ingin mengubahnya, silakan ke <router-link to="/communities/bank-account" class="text-[#8B4513] hover:underline font-medium">Pengaturan Rekening</router-link>.</p>
                  </div>
                  <div v-else class="text-sm text-gray-400">
                    Belum ada rekening terdaftar. Silakan atur rekening terlebih dahulu.
                  </div>
                </div>
              </div>

            </div>
          </div>
        </template>
      </div>
    </main>

    <AppFooter />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/api/axios'
import Navbar from '@/components/shared/Navbar.vue'
import AppFooter from '@/components/shared/AppFooter.vue'

const route = useRoute()
const loading = ref(true)
const activeAccount = ref(null)
const campaigns = ref([])
const history = ref([])

const activeCampaign = computed(() => campaigns.value.find(c => c.id_campaign == route.params.id))
const campaignHistory = computed(() => history.value.filter(h => h.id_campaign == route.params.id))

const approvedCount = computed(() => {
  return campaignHistory.value.filter(h => h.status === 'disetujui' || h.status === 'selesai').length
})

const form = ref({
  id_campaign: route.params.id,
  nominal_diajukan: '',
  keterangan: '',
  url_proposal: '',
})
const errors = ref({})
const changeSuccess = ref('')
const changeError = ref('')
const submitting = ref(false)

function statusBadge(status) {
  const map = { menunggu_review: 'text-yellow-700 bg-yellow-50', disetujui: 'text-green-700 bg-green-50', ditolak: 'text-red-700 bg-red-50', selesai: 'text-blue-700 bg-blue-50' }
  return map[status] || 'text-gray-500 bg-gray-100'
}

function statusLabel(status) {
  const map = { menunggu_review: 'Menunggu', disetujui: 'Disetujui', ditolak: 'Ditolak', selesai: 'Selesai' }
  return map[status] || status
}

async function fetchData() {
  loading.value = true
  try {
    const res = await api.get('/communities/withdrawals')
    const data = res.data.data || res.data
    activeAccount.value = data.rekening_aktif || null
    campaigns.value = data.campaigns || []
    history.value = data.riwayat || []
  } catch (e) {
    // silent
  } finally {
    loading.value = false
  }
}

async function handleSubmit() {
  errors.value = {}
  changeSuccess.value = ''
  changeError.value = ''
  submitting.value = true

  try {
    await api.post('/communities/withdrawals', {
      id_campaign: form.value.id_campaign,
      nominal_diajukan: form.value.nominal_diajukan,
      url_proposal: form.value.url_proposal,
      keterangan: form.value.keterangan,
    })
    changeSuccess.value = 'Permintaan pencairan dana berhasil diajukan'
    form.value = { id_campaign: '', nominal_diajukan: '', url_proposal: '', keterangan: '' }
    fetchData()
  } catch (e) {
    const status = e.response?.status
    const errData = e.response?.data?.errors || {}
    const message = e.response?.data?.message || ''
    if (status === 400 || status === 422) {
      errors.value = Object.fromEntries(
        Object.entries(errData).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v])
      )
      if (Object.keys(errors.value).length === 0) {
        changeError.value = message || 'Data tidak valid'
      }
    } else if (status === 409 || status === 403) {
      changeError.value = message || 'Tidak dapat memproses permintaan'
    } else {
      changeError.value = message || 'Gagal mengajukan pencairan'
    }
  } finally {
    submitting.value = false
  }
}

onMounted(fetchData)
</script>
