<script setup>
import { onMounted, ref, computed } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import api from '@/api/axios'
import AdminLayout from '@/components/admin/AdminLayout.vue'

const route = useRoute()
const campaignId = route.params.id

const data = ref({ campaign: null, summary: null, donations: [], withdrawals: [] })
const loading = ref(true)
const errorMessage = ref('')

async function fetchInternalMonitoring() {
  loading.value = true
  errorMessage.value = ''
  try {
    const res = await api.get(`/campaigns/${campaignId}/internal`)
    data.value = res.data.data
  } catch (e) {
    errorMessage.value = e.response?.data?.message || 'Gagal memuat monitoring internal.'
  } finally {
    loading.value = false
  }
}

onMounted(fetchInternalMonitoring)

const campaign = computed(() => data.value.campaign || {})
const summary = computed(() => data.value.summary || {})
const donations = computed(() => data.value.donations || [])
const withdrawals = computed(() => data.value.withdrawals || [])

const progressPersen = computed(() => {
  if (!campaign.value.target_dana || campaign.value.target_dana <= 0) return 0
  return Math.min(100, Math.round((campaign.value.dana_terkumpul / campaign.value.target_dana) * 100))
})

function formatRupiah(n) {
  return 'Rp ' + Number(n || 0).toLocaleString('id-ID')
}

function formatDate(s) {
  if (!s) return '-'
  return new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }).format(new Date(s))
}

const statusBadgeClass = computed(() => {
  const map = {
    aktif: 'bg-green-100 text-green-700',
    selesai: 'bg-blue-100 text-blue-700',
    menunggu_review: 'bg-amber-100 text-amber-700',
    ditolak: 'bg-red-100 text-red-700',
    nonaktif: 'bg-gray-100 text-gray-600',
    ditutup_permanen: 'bg-red-100 text-red-700',
  }
  return map[campaign.value.status] || 'bg-gray-100 text-gray-600'
})

const disbursementStatusBadge = (status) => {
  const map = {
    menunggu_review: 'bg-amber-100 text-amber-700',
    disetujui: 'bg-green-100 text-green-700',
    ditolak: 'bg-red-100 text-red-700',
    dicairkan: 'bg-blue-100 text-blue-700',
  }
  return map[status] || 'bg-gray-100 text-gray-600'
}
</script>

<template>
  <AdminLayout>
    <div class="max-w-5xl">
      <div class="mb-6">
        <h1 class="text-xl font-bold text-[#2C2C2C]">Monitoring Internal Campaign</h1>
        <p class="text-xs text-gray-400 mt-0.5">Detail keuangan, donasi, dan pencairan dana campaign</p>
      </div>

      <div v-if="loading" class="flex items-center justify-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#8B4513]"></div>
        <span class="ml-3 text-gray-500 text-sm">Memuat data...</span>
      </div>

      <div v-else-if="errorMessage" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
        {{ errorMessage }}
      </div>

      <template v-else>
        <div class="flex items-center gap-2 mb-6">
          <RouterLink to="/campaigns/approval" class="text-xs text-[#8B4513] hover:underline">Campaign Approval</RouterLink>
          <span class="text-xs text-gray-400">/</span>
          <RouterLink :to="`/campaigns/${campaignId}/review`" class="text-xs text-[#8B4513] hover:underline">Review</RouterLink>
          <span class="text-xs text-gray-400">/</span>
          <span class="text-xs text-[#1a2744] font-medium">Monitoring Internal</span>
        </div>

        <section class="bg-white rounded-xl shadow-sm p-6 mb-4">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <h2 class="text-lg font-bold text-[#1a2744]">{{ campaign.judul }}</h2>
              <p class="text-sm text-gray-500 mt-1">{{ campaign.nama_lembaga }} &bull; {{ campaign.nama_kategori }}</p>
            </div>
            <span class="shrink-0 px-3 py-1 rounded-full text-xs font-semibold" :class="statusBadgeClass">
              {{ campaign.status }}
            </span>
          </div>

          <div class="mt-4">
            <div class="flex justify-between text-sm mb-1.5">
              <span class="font-semibold text-[#1a2744]">{{ formatRupiah(campaign.dana_terkumpul) }}</span>
              <span class="text-gray-400">Target {{ formatRupiah(campaign.target_dana) }}</span>
            </div>
            <div class="w-full h-2 bg-stone-200 rounded-full overflow-hidden">
              <div class="h-full rounded-full transition-all duration-500" :style="{ width: `${progressPersen}%`, backgroundColor: '#8B4513' }" />
            </div>
            <p class="text-xs text-gray-400 mt-1">{{ progressPersen }}% terkumpul</p>
          </div>
        </section>

        <section class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <div class="bg-white rounded-xl shadow-sm p-4 text-center">
            <p class="text-lg font-bold text-[#1a2744]">{{ formatRupiah(summary.total_donasi_berhasil) }}</p>
            <p class="text-xs text-gray-500 mt-1">Total Donasi Berhasil</p>
          </div>
          <div class="bg-white rounded-xl shadow-sm p-4 text-center">
            <p class="text-lg font-bold text-[#1a2744]">{{ summary.total_donatur || 0 }}</p>
            <p class="text-xs text-gray-500 mt-1">Total Donatur</p>
          </div>
          <div class="bg-white rounded-xl shadow-sm p-4 text-center">
            <p class="text-lg font-bold text-[#8B4513]">{{ formatRupiah(summary.total_dicairkan) }}</p>
            <p class="text-xs text-gray-500 mt-1">Total Dicairkan</p>
          </div>
          <div class="bg-white rounded-xl shadow-sm p-4 text-center">
            <p class="text-lg font-bold text-[#8B4513]">{{ formatRupiah(campaign.saldo_tersedia) }}</p>
            <p class="text-xs text-gray-500 mt-1">Saldo Tersedia</p>
          </div>
        </section>

        <section class="bg-white rounded-xl shadow-sm p-6 mb-6">
          <h3 class="text-sm font-bold text-[#1a2744] mb-3">Detail Requirement Campaign</h3>
          <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
            <div><span class="text-xs text-gray-400 block">Tipe Distribusi</span><span class="text-gray-800 font-medium">{{ campaign.tipe_distribusi || '-' }}</span></div>
            <div><span class="text-xs text-gray-400 block">Target Audiens</span><span class="text-gray-800 font-medium">{{ campaign.target_audiens || '-' }}</span></div>
            <div><span class="text-xs text-gray-400 block">Target Penerima</span><span class="text-gray-800 font-medium">{{ campaign.target_penerima_label || '-' }}</span></div>
            <div><span class="text-xs text-gray-400 block">Pencairan</span><span class="text-gray-800 font-medium">{{ campaign.jumlah_pencairan || 0 }}x</span></div>
            <div><span class="text-xs text-gray-400 block">Lokasi</span><span class="text-gray-800 font-medium">{{ campaign.nama_wilayah || '-' }}</span></div>
            <div><span class="text-xs text-gray-400 block">Status</span><span :class="statusBadgeClass" class="inline px-3 py-0.5 rounded-full text-xs font-semibold">{{ campaign.status }}</span></div>
          </div>
          <div v-if="campaign.deskripsi" class="mt-4 pt-4 border-t border-stone-100">
            <p class="text-xs text-gray-400 font-medium mb-1">Deskripsi</p>
            <p class="text-sm text-gray-700 leading-relaxed">{{ campaign.deskripsi }}</p>
          </div>
          <div v-if="campaign.url_rab" class="mt-3">
            <p class="text-xs text-gray-400 font-medium mb-1">Dokumen RAB</p>
            <a :href="campaign.url_rab" target="_blank" class="text-sm text-[#8B4513] underline break-all">{{ campaign.url_rab }}</a>
          </div>
          <div v-if="campaign.alasan_penolakan" class="mt-3 bg-red-50 border border-red-200 rounded-lg px-4 py-3">
            <p class="text-xs text-red-500 font-medium mb-0.5">Alasan Penolakan</p>
            <p class="text-sm text-red-700">{{ campaign.alasan_penolakan }}</p>
          </div>
        </section>

        <section class="bg-white rounded-xl shadow-sm border border-stone-100 overflow-hidden mb-6">
          <div class="px-5 py-3 border-b border-stone-100">
            <h3 class="text-sm font-semibold text-gray-800">Donasi (50 Terbaru)</h3>
          </div>

          <div v-if="donations.length === 0" class="px-5 py-8 text-center text-sm text-gray-400">
            Belum ada donasi.
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-stone-100 bg-stone-50">
                  <th class="text-left px-5 py-2 text-xs font-semibold text-gray-500">User</th>
                  <th class="text-left px-5 py-2 text-xs font-semibold text-gray-500">Nominal</th>
                  <th class="text-left px-5 py-2 text-xs font-semibold text-gray-500">Metode</th>
                  <th class="text-left px-5 py-2 text-xs font-semibold text-gray-500">Status</th>
                  <th class="text-left px-5 py-2 text-xs font-semibold text-gray-500">Tanggal</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(d, i) in donations" :key="d.id_donasi" :class="['hover:bg-stone-50', i < donations.length - 1 ? 'border-b border-stone-100' : '']">
                  <td class="px-5 py-3 text-[#2C2C2C]">{{ d.is_anonim ? 'Anonim' : (d.nama_tampil || d.nama_lengkap || d.username || '-') }}</td>
                  <td class="px-5 py-3 text-gray-700">{{ formatRupiah(d.nominal) }}</td>
                  <td class="px-5 py-3 text-gray-500 text-xs">{{ d.metode_pembayaran || '-' }}</td>
                  <td class="px-5 py-3">
                    <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', d.status_pembayaran === 'berhasil' ? 'bg-green-100 text-green-700' : d.status_pembayaran === 'gagal' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700']">{{ d.status_pembayaran || '-' }}</span>
                  </td>
                  <td class="px-5 py-3 text-xs text-gray-400">{{ formatDate(d.created_at) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

        <section class="bg-white rounded-xl shadow-sm border border-stone-100 overflow-hidden mb-6">
          <div class="px-5 py-3 border-b border-stone-100">
            <h3 class="text-sm font-semibold text-gray-800">Pencairan Dana</h3>
          </div>

          <div v-if="withdrawals.length === 0" class="px-5 py-8 text-center text-sm text-gray-400">
            Belum ada pencairan dana.
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-stone-100 bg-stone-50">
                  <th class="text-left px-5 py-2 text-xs font-semibold text-gray-500">Urutan</th>
                  <th class="text-left px-5 py-2 text-xs font-semibold text-gray-500">Diajukan</th>
                  <th class="text-left px-5 py-2 text-xs font-semibold text-gray-500">Disetujui</th>
                  <th class="text-left px-5 py-2 text-xs font-semibold text-gray-500">Status</th>
                  <th class="text-left px-5 py-2 text-xs font-semibold text-gray-500">Tanggal</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(w, i) in withdrawals" :key="w.id_pencairan" :class="['hover:bg-stone-50', i < withdrawals.length - 1 ? 'border-b border-stone-100' : '']">
                  <td class="px-5 py-3 text-[#2C2C2C]">#{{ w.urutan_ke }}</td>
                  <td class="px-5 py-3 text-gray-700">{{ formatRupiah(w.nominal_diajukan) }}</td>
                  <td class="px-5 py-3 text-gray-700">{{ w.nominal_disetujui ? formatRupiah(w.nominal_disetujui) : '-' }}</td>
                  <td class="px-5 py-3">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="disbursementStatusBadge(w.status)">{{ w.status }}</span>
                  </td>
                  <td class="px-5 py-3 text-xs text-gray-400">{{ formatDate(w.tanggal_pengajuan) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </template>
    </div>
  </AdminLayout>
</template>

<style scoped>
.animate-spin {
  animation: spin 1s linear infinite;
}
@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
