<template>
  <div class="min-h-screen bg-[#F5F0E8] flex flex-col">
    <Navbar />
    <main class="flex-1 py-8">
      <div class="max-w-2xl mx-auto px-6">
        <nav class="text-sm text-gray-500 mb-4 flex items-center gap-1">
          <router-link to="/communities/dashboard" class="hover:text-[#8B4513] transition-colors">Dashboard</router-link>
          <span>/</span>
          <span class="text-[#2C2C2C] font-medium">Buat Update Campaign</span>
        </nav>

        <div class="bg-white rounded-xl shadow-sm border border-stone-100 p-8">
          <h1 class="text-xl font-bold text-[#2C2C2C] mb-6">Buat Update Campaign</h1>

          <div v-if="loading" class="flex justify-center py-10">
            <div class="w-8 h-8 border-2 border-[#8B4513] border-t-transparent rounded-full animate-spin" />
          </div>

          <form v-else @submit.prevent="handleSubmit" class="space-y-6">
            <div class="form-group">
              <label class="block text-sm font-medium text-gray-700 mb-1">Campaign</label>
              <select v-model="form.id_campaign" class="w-full px-4 py-2.5 rounded-lg border border-stone-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-[#8B4513]/20 focus:border-[#8B4513]" required>
                <option value="" disabled>Pilih campaign</option>
                <option v-for="c in campaignList" :key="c.id_campaign" :value="c.id_campaign">
                  {{ c.judul }}
                </option>
              </select>
            </div>

            <div class="form-group">
              <label class="block text-sm font-medium text-gray-700 mb-1">Judul Update</label>
              <input v-model="form.judul_update" type="text" class="w-full px-4 py-2.5 rounded-lg border border-stone-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-[#8B4513]/20 focus:border-[#8B4513]" placeholder="Judul update" required />
            </div>

            <div class="form-group">
              <label class="block text-sm font-medium text-gray-700 mb-1">Konten</label>
              <textarea v-model="form.konten" rows="6" class="w-full px-4 py-2.5 rounded-lg border border-stone-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-[#8B4513]/20 focus:border-[#8B4513]" placeholder="Tulis perkembangan campaign..." required></textarea>
            </div>

            <div class="form-group">
              <label class="block text-sm font-medium text-gray-700 mb-1">Foto URL (opsional, maks 10)</label>
              <div v-for="(_, i) in form.foto_urls" :key="i" class="flex gap-2 mb-2">
                <input v-model="form.foto_urls[i]" type="url" class="flex-1 px-4 py-2.5 rounded-lg border border-stone-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-[#8B4513]/20 focus:border-[#8B4513]" :placeholder="'URL foto ' + (i + 1)" />
                <button type="button" @click="form.foto_urls.splice(i, 1)" class="px-3 py-2 text-red-500 hover:bg-red-50 rounded-lg text-sm">Hapus</button>
              </div>
              <button v-if="form.foto_urls.length < 10" type="button" @click="form.foto_urls.push('')" class="text-sm text-[#8B4513] hover:underline">
                + Tambah URL Foto
              </button>
            </div>

            <div v-if="error" class="p-3 rounded-lg bg-red-50 text-sm text-red-600">{{ error }}</div>
            <div v-if="success" class="p-3 rounded-lg bg-green-50 text-sm text-green-600">{{ success }}</div>

            <div class="flex gap-3">
              <router-link to="/communities/dashboard" class="px-6 py-2.5 border border-stone-200 text-gray-600 rounded-lg text-sm font-medium hover:bg-stone-50 transition-colors">Batal</router-link>
              <button type="submit" :disabled="submitting" class="px-6 py-2.5 bg-[#8B4513] text-white rounded-lg text-sm font-medium hover:bg-[#6b3410] transition-colors disabled:opacity-60">
                {{ submitting ? 'Mempublikasikan...' : 'Publikasikan Update' }}
              </button>
            </div>
          </form>
        </div>
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
const error = ref('')
const success = ref('')
const campaignList = ref([])

const form = ref({
  id_campaign: '',
  judul_update: '',
  konten: '',
  foto_urls: [],
})

async function fetchCampaigns() {
  try {
    const res = await api.get('/communities/campaigns')
    campaignList.value = res.data.data || res.data || []
  } catch {
    error.value = 'Gagal memuat daftar campaign'
  } finally {
    loading.value = false
  }
}

async function handleSubmit() {
  if (!form.value.id_campaign || !form.value.judul_update || !form.value.konten) {
    error.value = 'Harap lengkapi semua field wajib'
    return
  }
  submitting.value = true
  error.value = ''
  success.value = ''
  try {
    const payload = {
      judul_update: form.value.judul_update,
      konten: form.value.konten,
    }
    const urls = form.value.foto_urls.filter(Boolean)
    if (urls.length > 0) payload.foto_urls = urls

    await api.post(`/communities/campaigns/${form.value.id_campaign}/updates`, payload)
    success.value = 'Update berhasil dipublikasikan!'
    form.value = { id_campaign: '', judul_update: '', konten: '', foto_urls: [] }
    setTimeout(() => router.push('/communities/dashboard'), 1500)
  } catch (e) {
    error.value = e.response?.data?.message || 'Gagal mempublikasikan update'
  } finally {
    submitting.value = false
  }
}

onMounted(fetchCampaigns)
</script>
