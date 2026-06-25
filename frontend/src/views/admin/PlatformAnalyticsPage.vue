<template>
  <AdminLayout>
    <div class="max-w-6xl">
      <div class="mb-6">
        <h1 class="text-xl font-bold text-[#2C2C2C]">Analitik Platform</h1>
        <p class="text-xs text-gray-400 mt-0.5">Ringkasan performa dan statistik platform</p>
      </div>

      <div class="bg-white rounded-xl shadow-sm p-4 mb-6 flex items-center gap-4">
        <div>
          <label class="block text-xs font-medium text-gray-500 mb-1">Dari Tanggal</label>
          <input v-model="startDate" type="date" class="px-3 py-2 border border-gray-200 rounded-lg text-xs" />
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-500 mb-1">Sampai Tanggal</label>
          <input v-model="endDate" type="date" class="px-3 py-2 border border-gray-200 rounded-lg text-xs" />
        </div>
        <div class="flex items-end">
          <button @click="fetchAnalytics" class="px-4 py-2 bg-[#8B4513] text-white rounded-lg text-xs font-medium hover:bg-[#6b3410]">Terapkan</button>
        </div>
      </div>

      <div v-if="loading" class="bg-white rounded-xl shadow-sm p-8 text-center text-sm text-gray-400">Memuat data...</div>

      <template v-else-if="data">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
          <div class="bg-white rounded-xl shadow-sm border border-stone-100 p-5">
            <p class="text-xs text-gray-400 mb-1">Total Donasi</p>
            <p class="text-xl font-bold text-[#2C2C2C]">{{ formatRupiah(data.financial_summary?.total_donasi) }}</p>
          </div>
          <div class="bg-white rounded-xl shadow-sm border border-stone-100 p-5">
            <p class="text-xs text-gray-400 mb-1">Total Pencairan</p>
            <p class="text-xl font-bold text-[#2C2C2C]">{{ formatRupiah(data.financial_summary?.total_pencairan) }}</p>
          </div>
          <div class="bg-white rounded-xl shadow-sm border border-stone-100 p-5">
            <p class="text-xs text-gray-400 mb-1">Saldo Akhir</p>
            <p class="text-xl font-bold text-green-600">{{ formatRupiah(data.saldo_akhir) }}</p>
          </div>
          <div class="bg-white rounded-xl shadow-sm border border-stone-100 p-5">
            <p class="text-xs text-gray-400 mb-1">Success Rate</p>
            <p class="text-xl font-bold text-[#2C2C2C]">{{ data.campaign_success_rate || 0 }}%</p>
          </div>
          <div class="bg-white rounded-xl shadow-sm border border-stone-100 p-5">
            <p class="text-xs text-gray-400 mb-1">Target Penerima</p>
            <p class="text-xl font-bold text-[#2C2C2C]">{{ (data.beneficiary_summary?.total_target || 0).toLocaleString('id-ID') }}</p>
            <p class="text-xs text-gray-400 mt-1">Manfaat</p>
          </div>
          <div class="bg-white rounded-xl shadow-sm border border-stone-100 p-5">
            <p class="text-xs text-gray-400 mb-1">Realisasi Penerima</p>
            <p class="text-xl font-bold text-green-600">{{ (data.beneficiary_summary?.total_realisasi || 0).toLocaleString('id-ID') }}</p>
            <p class="text-xs text-gray-400 mt-1">Tersalurkan</p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
          <div class="bg-white rounded-xl shadow-sm border border-stone-100 p-5">
            <h2 class="text-sm font-bold text-[#2C2C2C] mb-4">Distribusi Kategori</h2>
            <div v-if="!data.category_distribution || data.category_distribution.length === 0" class="text-sm text-gray-400 text-center py-8">Belum ada data</div>
            <div v-else class="space-y-2">
              <div v-for="cat in data.category_distribution" :key="cat.nama_kategori" class="flex items-center gap-3">
                <span class="text-xs text-gray-600 w-24 truncate">{{ cat.nama_kategori }}</span>
                <div class="flex-1 h-2 bg-stone-100 rounded-full overflow-hidden">
                  <div class="h-full bg-[#8B4513] rounded-full" :style="{ width: barWidth(cat.total) + '%' }"></div>
                </div>
                <span class="text-xs font-medium text-gray-500 w-12 text-right">{{ cat.total }}</span>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-xl shadow-sm border border-stone-100 p-5">
            <h2 class="text-sm font-bold text-[#2C2C2C] mb-4">Komposisi Campaign</h2>
            <div v-if="!data.type_distribution || data.type_distribution.length === 0" class="text-sm text-gray-400 text-center py-8">Belum ada data</div>
            <div v-else class="space-y-2">
              <div v-for="t in typeDistList" :key="t.key" class="flex items-center gap-3">
                <span class="text-xs text-gray-600 w-24 truncate">{{ t.label }}</span>
                <div class="flex-1 h-2 bg-stone-100 rounded-full overflow-hidden">
                  <div class="h-full rounded-full" :class="t.key === 'individual' ? 'bg-blue-500' : 'bg-emerald-500'" :style="{ width: typeBarWidth(t.total) + '%' }"></div>
                </div>
                <span class="text-xs font-medium text-gray-500 w-12 text-right">{{ t.total }}</span>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-xl shadow-sm border border-stone-100 p-5">
            <h2 class="text-sm font-bold text-[#2C2C2C] mb-4">Ringkasan Keuangan</h2>
            <div class="space-y-3 text-sm">
              <div class="flex justify-between">
                <span class="text-gray-500">Jumlah Donasi</span>
                <span class="font-medium">{{ data.financial_summary?.jumlah_donasi || 0 }} transaksi</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500">Jumlah Pencairan</span>
                <span class="font-medium">{{ data.financial_summary?.jumlah_pencairan || 0 }} transaksi</span>
              </div>
              <div class="flex justify-between pt-2 border-t border-stone-100">
                <span class="text-gray-500">Total Biaya</span>
                <span class="font-medium">{{ formatRupiah(data.financial_summary?.total_potongan) }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-stone-100 overflow-hidden">
          <div class="px-6 py-4 border-b border-stone-100">
            <h2 class="text-sm font-bold text-[#2C2C2C]">Data Harian Platform</h2>
          </div>
          <div v-if="!data.platform_daily || data.platform_daily.length === 0" class="px-6 py-10 text-center text-sm text-gray-400">Belum ada data</div>
          <table v-else class="w-full text-sm">
            <thead>
              <tr class="border-b border-stone-100 bg-stone-50">
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500">Tanggal</th>
                <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500">Donasi Baru</th>
                <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500">Total Donasi</th>
                <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500">Pencairan</th>
                <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500">Saldo</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(d, i) in data.platform_daily" :key="i" :class="['hover:bg-stone-50', i < data.platform_daily.length - 1 ? 'border-b border-stone-100' : '']">
                <td class="px-5 py-3 text-[#2C2C2C]">{{ d.tanggal || '-' }}</td>
                <td class="px-5 py-3 text-right">{{ d.donasi_baru || 0 }}</td>
                <td class="px-5 py-3 text-right font-medium">{{ formatRupiah(d.total_donasi) }}</td>
                <td class="px-5 py-3 text-right">{{ formatRupiah(d.total_pencairan) }}</td>
                <td class="px-5 py-3 text-right font-medium text-green-600">{{ formatRupiah(d.saldo_akhir) }}</td>
              </tr>
            </tbody>
          </table>

          <div v-if="data.platform_daily_pagination && data.platform_daily_pagination.last_page > 1" class="px-6 py-4 flex items-center justify-between border-t border-stone-100">
            <p class="text-xs text-gray-500">
              Halaman {{ data.platform_daily_pagination.current_page }} dari {{ data.platform_daily_pagination.last_page }}
              ({{ data.platform_daily_pagination.total }} data)
            </p>
            <div class="flex items-center gap-1">
              <button
                @click="changePage(data.platform_daily_pagination.current_page - 1)"
                :disabled="data.platform_daily_pagination.current_page === 1 || loading"
                class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
              </button>
              <button
                v-for="p in visibleAnalyticsPages"
                :key="p"
                @click="changePage(p)"
                :disabled="loading"
                :class="[
                  'w-8 h-8 flex items-center justify-center rounded-lg text-xs font-medium transition-colors',
                  currentAnalyticsPage === p ? 'bg-[#8B4513] text-white' : 'border border-gray-200 text-gray-700 hover:bg-gray-100'
                ]"
              >
                {{ p }}
              </button>
              <button
                @click="changePage(data.platform_daily_pagination.current_page + 1)"
                :disabled="data.platform_daily_pagination.current_page === data.platform_daily_pagination.last_page || loading"
                class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
              </button>
            </div>
          </div>
        </div>
      </template>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/api/axios'
import AdminLayout from '@/components/admin/AdminLayout.vue'

const loading = ref(true)
const data = ref(null)
const currentAnalyticsPage = ref(1)

const now = new Date()
const startDate = ref(new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0])
const endDate = ref(now.toISOString().split('T')[0])

function formatRupiah(val) { return 'Rp ' + (Number(val) || 0).toLocaleString('id-ID') }

function barWidth(total) {
  const totals = data.value?.category_distribution?.map(c => c.total) || []
  const max = Math.max(...totals, 1)
  return (total / max) * 100
}

const typeDistList = computed(() => {
  const raw = data.value?.type_distribution || []
  const defaults = [
    { key: 'individual', label: 'Individual', total: 0 },
    { key: 'kolektif', label: 'Kolektif', total: 0 },
  ]
  defaults.forEach(d => {
    const found = raw.find(r => r.tipe_distribusi === d.key)
    if (found) d.total = Number(found.total) || 0
  })
  return defaults
})

function typeBarWidth(total) {
  const totals = typeDistList.value.map(t => t.total)
  const max = Math.max(...totals, 1)
  return (total / max) * 100
}

const visibleAnalyticsPages = computed(() => {
  const pag = data.value?.platform_daily_pagination
  if (!pag) return []
  const total = pag.last_page
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)
  const cur = pag.current_page
  const pages = new Set([1, total, cur])
  if (cur > 1) pages.add(cur - 1)
  if (cur < total) pages.add(cur + 1)
  return [...pages].sort((a, b) => a - b)
})

function changePage(page) {
  currentAnalyticsPage.value = page
  fetchAnalytics()
}

async function fetchAnalytics() {
  loading.value = true
  try {
    const res = await api.get('/superadmin/analytics/platform', {
      params: {
        start_date: startDate.value,
        end_date: endDate.value,
        page: currentAnalyticsPage.value,
        per_page: 15,
      },
    })
    data.value = res.data.data || res.data
  } catch (e) {
    data.value = null
  } finally {
    loading.value = false
  }
}

onMounted(fetchAnalytics)
</script>
