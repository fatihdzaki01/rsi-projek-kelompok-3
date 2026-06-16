<template>
  <AdminLayout>
    <div class="max-w-4xl">
      <router-link to="/dashboard/communities" class="inline-flex items-center gap-1 text-xs text-gray-400 hover:text-gray-600 mb-4">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali ke Daftar Komunitas
      </router-link>

      <div v-if="loading" class="bg-white rounded-xl shadow-sm p-8 text-center text-sm text-gray-400">Memuat data...</div>

      <template v-else-if="data">
        <div class="bg-white rounded-xl shadow-sm border border-stone-100 overflow-hidden mb-6">
          <div class="px-6 py-5 flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-[#F5F0E8] flex items-center justify-center text-lg font-bold text-[#8B4513]">{{ (data.community?.nama_lembaga || '?').charAt(0) }}</div>
            <div class="flex-1">
              <h1 class="text-base font-bold text-[#2C2C2C]">{{ data.community?.nama_lembaga }}</h1>
              <p class="text-sm text-gray-400">{{ data.community?.email }}</p>
              <div class="flex items-center gap-3 mt-1">
                <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', data.community?.is_active ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700']">{{ data.community?.is_active ? 'Aktif' : 'Nonaktif' }}</span>
                <span class="text-xs text-gray-400">{{ data.community?.jenis_lembaga || '-' }}</span>
                <span class="text-xs text-gray-400">{{ data.community?.nama_wilayah || '-' }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-stone-100 overflow-hidden">
          <div class="px-6 py-4 border-b border-stone-100">
            <h2 class="text-sm font-bold text-[#2C2C2C]">Campaign {{ data.community?.nama_lembaga }}</h2>
          </div>
          <div v-if="!data.campaigns || data.campaigns.length === 0" class="px-6 py-10 text-center text-sm text-gray-400">Belum ada campaign.</div>
          <table v-else class="w-full text-sm">
            <thead>
              <tr class="border-b border-stone-100 bg-stone-50">
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500">Judul</th>
                <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500">Target</th>
                <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500">Terkumpul</th>
                <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500">Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(c, i) in data.campaigns" :key="c.id_campaign" :class="['hover:bg-stone-50', i < data.campaigns.length - 1 ? 'border-b border-stone-100' : '']">
                <td class="px-5 py-3 text-[#2C2C2C]">{{ c.judul }}</td>
                <td class="px-5 py-3 text-right">{{ formatRupiah(c.target_dana) }}</td>
                <td class="px-5 py-3 text-right font-medium text-green-600">{{ formatRupiah(c.dana_terkumpul) }}</td>
                <td class="px-5 py-3 text-center">
                  <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', statusBadge(c.status)]">{{ statusLabel(c.status) }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </template>

      <div v-else class="bg-white rounded-xl shadow-sm p-8 text-center text-sm text-red-500">Komunitas tidak ditemukan</div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/api/axios'
import AdminLayout from '@/components/admin/AdminLayout.vue'

const route = useRoute()
const loading = ref(true)
const data = ref(null)

function formatRupiah(val) { return 'Rp ' + (Number(val) || 0).toLocaleString('id-ID') }
function statusBadge(s) {
  const map = { aktif: 'text-green-700 bg-green-50', menunggu_review: 'text-yellow-700 bg-yellow-50', selesai: 'text-blue-700 bg-blue-50', ditolak: 'text-red-700 bg-red-50', nonaktif: 'text-gray-500 bg-gray-100' }
  return map[s] || 'text-gray-500 bg-gray-100'
}
function statusLabel(s) {
  const map = { aktif: 'Aktif', menunggu_review: 'Review', selesai: 'Selesai', ditolak: 'Ditolak', nonaktif: 'Nonaktif' }
  return map[s] || s
}

onMounted(async () => {
  loading.value = true
  try {
    const res = await api.get(`/superadmin/communities/${route.params.id}`)
    data.value = res.data.data || res.data
  } catch (e) {
    data.value = null
  } finally {
    loading.value = false
  }
})
</script>
