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
          <span class="text-[#2C2C2C] font-medium">Buat Campaign</span>
        </nav>

        <div v-if="loading" class="flex flex-col items-center justify-center py-20">
          <div class="w-8 h-8 border-2 border-[#8B4513] border-t-transparent rounded-full animate-spin mb-3" />
          <p class="text-sm text-gray-400">Memuat data...</p>
        </div>

        <template v-else>
          <div class="bg-white rounded-xl shadow-sm border border-stone-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-stone-100">
              <h1 class="text-sm font-bold text-[#2C2C2C]">Buat Campaign Baru</h1>
            </div>

            <div class="px-6 py-5 space-y-5">
              <div v-if="success" class="p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
                {{ success }}
                <router-link :to="`/campaigns/${newCampaignId}`" class="ml-2 underline">Lihat Campaign</router-link>
              </div>
              <div v-if="errorMsg" class="p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-600">{{ errorMsg }}</div>

              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Judul Campaign <span class="text-red-400">*</span></label>
                <input v-model="form.judul" type="text" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm text-[#2C2C2C] focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-transparent" />
                <p v-if="errors.judul" class="mt-1 text-xs text-red-500">{{ errors.judul }}</p>
              </div>

              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Kategori <span class="text-red-400">*</span></label>
                <select v-model="form.id_kategori" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm text-[#2C2C2C] focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-transparent bg-white">
                  <option value="">Pilih Kategori</option>
                  <option v-for="k in categories" :key="k.id_kategori" :value="k.id_kategori">{{ k.nama_kategori }}</option>
                </select>
                <p v-if="errors.id_kategori" class="mt-1 text-xs text-red-500">{{ errors.id_kategori }}</p>
              </div>

              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Deskripsi</label>
                <textarea v-model="form.deskripsi" rows="4" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm text-[#2C2C2C] focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-transparent resize-none"></textarea>
              </div>

              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Target Dana <span class="text-red-400">*</span></label>
                <div class="relative">
                  <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-400">Rp</span>
                  <input v-model.number="form.target_dana" type="number" min="10000000" class="w-full pl-10 pr-3 py-2 border border-gray-200 rounded-lg text-sm text-[#2C2C2C] focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-transparent" />
                </div>
                <p class="mt-1 text-xs text-gray-400">Minimal Rp10.000.000</p>
                <p v-if="errors.target_dana" class="mt-1 text-xs text-red-500">{{ errors.target_dana }}</p>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Mulai</label>
                  <input v-model="form.tanggal_mulai" type="date" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm text-[#2C2C2C] focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-transparent" />
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Selesai</label>
                  <input v-model="form.tanggal_selesai" type="date" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm text-[#2C2C2C] focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-transparent" />
                </div>
              </div>

              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Lokasi (Wilayah) <span class="text-red-400">*</span></label>
                <select v-model="form.kode_wilayah" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm text-[#2C2C2C] focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-transparent bg-white">
                  <option value="">Pilih Provinsi</option>
                  <option v-for="w in wilayah" :key="w.kode" :value="w.kode">{{ w.nama }}</option>
                </select>
                <p v-if="errors.kode_wilayah" class="mt-1 text-xs text-red-500">{{ errors.kode_wilayah }}</p>
              </div>

              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Tipe Distribusi <span class="text-red-400">*</span></label>
                <div class="flex items-center gap-4 mt-1">
                  <label class="flex items-center gap-2 text-sm text-[#2C2C2C] cursor-pointer">
                    <input type="radio" v-model="form.tipe_distribusi" value="individual" class="accent-[#8B4513]" />
                    Individual
                  </label>
                  <label class="flex items-center gap-2 text-sm text-[#2C2C2C] cursor-pointer">
                    <input type="radio" v-model="form.tipe_distribusi" value="kolektif" class="accent-[#8B4513]" />
                    Kolektif
                  </label>
                </div>
                <p v-if="errors.tipe_distribusi" class="mt-1 text-xs text-red-500">{{ errors.tipe_distribusi }}</p>
              </div>

              <div v-if="form.tipe_distribusi === 'individual'">
                <label class="block text-xs font-medium text-gray-500 mb-1">Target Audiens <span class="text-red-400">*</span></label>
                <input v-model="form.target_audiens" type="text" maxlength="150" placeholder="Misal: Anak-anak usia sekolah di daerah terpencil" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm text-[#2C2C2C] focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-transparent" />
                <p v-if="errors.target_audiens" class="mt-1 text-xs text-red-500">{{ errors.target_audiens }}</p>
              </div>

              <div v-if="form.tipe_distribusi === 'individual'">
                <label class="block text-xs font-medium text-gray-500 mb-1">Total Penerima Manfaat <span class="text-red-400">*</span></label>
                <input v-model.number="form.total_penerima_manfaat" type="number" min="0" placeholder="Jumlah penerima manfaat" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm text-[#2C2C2C] focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-transparent" />
                <p class="mt-1 text-xs text-gray-400">Jumlah orang yang akan menerima manfaat</p>
                <p v-if="errors.total_penerima_manfaat" class="mt-1 text-xs text-red-500">{{ errors.total_penerima_manfaat }}</p>
              </div>

              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Foto Campaign <span class="text-red-400">*</span></label>
                <input v-model="form.foto_campaign_url" type="url" placeholder="https://" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm text-[#2C2C2C] focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-transparent" />
                <p class="mt-1 text-xs text-gray-400">URL gambar campaign</p>
                <p v-if="errors.foto_campaign_url" class="mt-1 text-xs text-red-500">{{ errors.foto_campaign_url }}</p>
              </div>

              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">URL RAB (opsional)</label>
                <input v-model="form.url_rab" type="url" placeholder="https://example.com/rab.pdf" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm text-[#2C2C2C] focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-transparent" />
                <p class="mt-1 text-xs text-gray-400">File PDF (jika ada)</p>
                <p v-if="errors.url_rab" class="mt-1 text-xs text-red-500">{{ errors.url_rab }}</p>
              </div>

              <div class="flex items-center gap-3 pt-2">
                <button @click="handleSubmit" :disabled="submitting" class="px-5 py-2 bg-[#8B4513] text-white rounded-lg text-sm font-medium hover:bg-[#6b3410] transition-colors disabled:opacity-50">
                  {{ submitting ? 'Menyimpan...' : 'Ajukan Campaign' }}
                </button>
                <router-link to="/communities/dashboard" class="px-5 py-2 text-sm text-gray-500 hover:text-gray-700 transition-colors">Batal</router-link>
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
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api/axios'
import Navbar from '@/components/shared/Navbar.vue'
import AppFooter from '@/components/shared/AppFooter.vue'

const router = useRouter()
const loading = ref(true)
const submitting = ref(false)
const success = ref('')
const errorMsg = ref('')
const newCampaignId = ref(null)

const categories = ref([])
const wilayah = ref([])

const form = ref({
  judul: '',
  id_kategori: '',
  deskripsi: '',
  target_dana: null,
  tanggal_mulai: '',
  tanggal_selesai: '',
  kode_wilayah: '',
  tipe_distribusi: 'kolektif',
  target_audiens: '',
  total_penerima_manfaat: null,
  foto_campaign_url: '',
  url_rab: '',
})

const errors = ref({})

async function fetchMasterData() {
  try {
    const [catRes, wilRes] = await Promise.all([
      api.get('/campaign-categories'),
      api.get('/wilayah', { params: { level: 1 } }),
    ])
    categories.value = catRes.data.data || catRes.data || []
    wilayah.value = wilRes.data.data || wilRes.data || []
  } catch (e) {
    errorMsg.value = 'Gagal memuat data master'
  } finally {
    loading.value = false
  }
}

async function handleSubmit() {
  errors.value = {}
  success.value = ''
  errorMsg.value = ''
  submitting.value = true

  try {
    const res = await api.post('/communities/campaigns', {
      judul: form.value.judul,
      id_kategori: form.value.id_kategori,
      deskripsi: form.value.deskripsi || undefined,
      target_dana: form.value.target_dana,
      tanggal_mulai: form.value.tanggal_mulai || undefined,
      tanggal_selesai: form.value.tanggal_selesai || undefined,
      kode_wilayah: form.value.kode_wilayah,
      tipe_distribusi: form.value.tipe_distribusi,
      target_audiens: form.value.target_audiens || undefined,
      total_penerima_manfaat: form.value.total_penerima_manfaat || undefined,
      foto_campaign_url: form.value.foto_campaign_url,
      url_rab: form.value.url_rab || undefined,
    })
    newCampaignId.value = res.data.data?.id_campaign || res.data.id_campaign
    success.value = 'Campaign berhasil diajukan!'
    setTimeout(() => router.push(`/campaigns/${newCampaignId.value}`), 1500)
  } catch (e) {
    const status = e.response?.status
    const errData = e.response?.data?.errors || {}
    const message = e.response?.data?.message || ''
    if (status === 400 || status === 422) {
      errors.value = Object.fromEntries(
        Object.entries(errData).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v])
      )
      if (Object.keys(errors.value).length === 0) {
        errorMsg.value = message || 'Data tidak valid'
      }
    } else {
      errorMsg.value = message || 'Gagal mengajukan campaign'
    }
  } finally {
    submitting.value = false
  }
}

onMounted(fetchMasterData)
</script>
