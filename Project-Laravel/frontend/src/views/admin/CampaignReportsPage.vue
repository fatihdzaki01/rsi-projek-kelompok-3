<template>
  <AdminLayout>
    <div class="max-w-6xl">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-xl font-bold text-[#2C2C2C]">Laporan Campaign</h1>
          <p class="text-xs text-gray-400 mt-0.5">Tinjau campaign yang dilaporkan oleh pengguna</p>
        </div>
        <div class="flex items-center gap-2">
          <button @click="activeTab = 'reports'" :class="['px-3 py-1.5 rounded-lg text-xs font-medium', activeTab === 'reports' ? 'bg-[#8B4513] text-white' : 'bg-white border border-stone-200 text-gray-500']">Laporan Masuk</button>
          <button @click="activeTab = 'clarifications'" :class="['px-3 py-1.5 rounded-lg text-xs font-medium', activeTab === 'clarifications' ? 'bg-[#8B4513] text-white' : 'bg-white border border-stone-200 text-gray-500']">Klarifikasi</button>
        </div>
      </div>

      <div v-if="loading" class="bg-white rounded-xl shadow-sm p-8 text-center text-sm text-gray-400">Memuat data...</div>

      <template v-else-if="activeTab === 'reports'">
        <div v-if="reports.length === 0" class="bg-white rounded-xl shadow-sm p-8 text-center text-sm text-gray-400">Tidak ada laporan menunggu review.</div>
        <div v-for="(r, i) in reports" :key="r.id_campaign" class="bg-white rounded-xl shadow-sm border border-stone-100 overflow-hidden mb-4">
          <div class="px-6 py-4 flex items-start justify-between">
            <div>
              <h3 class="text-sm font-semibold text-[#2C2C2C]">{{ r.judul }}</h3>
              <p class="text-xs text-gray-400 mt-0.5">{{ r.nama_lembaga }} | {{ r.nama_kategori }}</p>
            </div>
            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-50 text-yellow-700">Dilaporkan</span>
          </div>
          <div class="px-6 py-3 border-t border-stone-100 bg-stone-50 flex items-center gap-3 justify-end">
            <button @click="handleIgnore(r)" class="px-4 py-1.5 text-xs font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-100">Abaikan</button>
            <router-link :to="`/dashboard/campaigns/${r.id_campaign}`" class="px-4 py-1.5 text-xs font-medium text-white bg-[#8B4513] rounded-lg hover:bg-[#6b3410]">Tinjau</router-link>
          </div>
        </div>
      </template>

      <template v-else>
        <div v-if="clarifications.length === 0" class="bg-white rounded-xl shadow-sm p-8 text-center text-sm text-gray-400">Tidak ada campaign perlu klarifikasi.</div>
        <div v-for="(c, i) in clarifications" :key="c.id_campaign" class="bg-white rounded-xl shadow-sm border border-stone-100 overflow-hidden mb-4">
          <div class="px-6 py-4 flex items-start justify-between">
            <div>
              <h3 class="text-sm font-semibold text-[#2C2C2C]">{{ c.judul }}</h3>
              <p class="text-xs text-gray-400 mt-0.5">{{ c.nama_lembaga }} | {{ c.nama_kategori }}</p>
            </div>
            <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', c.status === 'nonaktif' ? 'bg-yellow-50 text-yellow-700' : 'bg-red-50 text-red-700']">{{ c.status === 'nonaktif' ? 'Nonaktif' : 'Ditutup' }}</span>
          </div>
          <div class="px-6 py-3 border-t border-stone-100 bg-stone-50 flex items-center gap-3 justify-end">
            <button v-if="c.status === 'nonaktif'" @click="handleReactivate(c)" class="px-4 py-1.5 text-xs font-medium text-green-600 border border-green-200 rounded-lg hover:bg-green-50">Aktifkan Kembali</button>
            <button @click="handleClosePermanent(c)" class="px-4 py-1.5 text-xs font-medium text-red-600 border border-red-200 rounded-lg hover:bg-red-50">Tutup Permanen</button>
          </div>
        </div>
      </template>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import api from '@/api/axios'
import AdminLayout from '@/components/admin/AdminLayout.vue'

const activeTab = ref('reports')
const loading = ref(true)
const reports = ref([])
const clarifications = ref([])

async function loadReports() {
  loading.value = true
  try {
    const res = await api.get('/superadmin/campaign-reports', { params: { per_page: 20 } })
    const data = res.data.data || res.data
    reports.value = data.data || data
  } catch (e) {
    reports.value = []
  } finally {
    loading.value = false
  }
}

async function loadClarifications() {
  loading.value = true
  try {
    const res = await api.get('/superadmin/campaign-clarifications', { params: { per_page: 20 } })
    const data = res.data.data || res.data
    clarifications.value = data.data || data
  } catch (e) {
    clarifications.value = []
  } finally {
    loading.value = false
  }
}

async function handleIgnore(r) {
  if (!confirm(`Abaikan laporan untuk campaign "${r.judul}"?`)) return
  try {
    await api.post(`/superadmin/campaign-reports/${r.id_campaign}/ignore`)
    reports.value = reports.value.filter(x => x.id_campaign !== r.id_campaign)
  } catch (e) {
    alert(e.response?.data?.message || 'Gagal mengabaikan laporan')
  }
}

async function handleReactivate(c) {
  if (!confirm(`Aktifkan kembali campaign "${c.judul}"?`)) return
  try {
    await api.post(`/superadmin/campaign-clarifications/${c.id_campaign}/reactivate`)
    clarifications.value = clarifications.value.filter(x => x.id_campaign !== c.id_campaign)
  } catch (e) {
    alert(e.response?.data?.message || 'Gagal mengaktifkan')
  }
}

async function handleClosePermanent(c) {
  if (!confirm(`Tutup permanen campaign "${c.judul}"? Tindakan ini tidak bisa dibatalkan.`)) return
  try {
    await api.post(`/superadmin/campaign-clarifications/${c.id_campaign}/close-permanently`)
    clarifications.value = clarifications.value.filter(x => x.id_campaign !== c.id_campaign)
  } catch (e) {
    alert(e.response?.data?.message || 'Gagal menutup campaign')
  }
}

watch(activeTab, (t) => { if (t === 'reports') loadReports(); else loadClarifications() })
onMounted(() => loadReports())
</script>
