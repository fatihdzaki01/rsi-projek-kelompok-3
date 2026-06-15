<script setup>
import { ref } from 'vue'
import { Receipt } from 'lucide-vue-next'
import Navbar from '@/components/shared/Navbar.vue'
import Footer from '@/components/shared/Footer.vue'

const donations = ref([
  { id: 1, campaign: 'Air Bersih untuk Desa Suka', komunitas: 'Komunitas Peduli', nominal: 250000, tanggal: '2023-10-24', status: 'BERHASIL' },
  { id: 2, campaign: 'Beasiswa Pelajar Cerdas', komunitas: 'Yayasan Pendidikan', nominal: 100000, tanggal: '2023-10-18', status: 'BERHASIL' },
  { id: 3, campaign: 'Bantuan Sumatera', komunitas: 'Komunitas Peduli Bencana', nominal: 500000, tanggal: '2023-10-12', status: 'BERHASIL' },
  { id: 4, campaign: 'Hijaukan Pesisir', komunitas: 'Relawan Alam Nusantara', nominal: 75000, tanggal: '2023-10-05', status: 'BERHASIL' },
  { id: 5, campaign: 'Makanan Darurat Banjir', komunitas: 'Komunitas Peduli Bencana', nominal: 200000, tanggal: '2023-09-28', status: 'GAGAL' },
])

const formatRp = (n) => 'Rp ' + n.toLocaleString('id-ID')

const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']
const formatDate = (iso) => {
  const d = new Date(iso)
  return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`
}

const statusClass = (s) => ({
  BERHASIL: 'bg-green-100 text-green-700',
  GAGAL: 'bg-red-100 text-red-600',
  PENDING: 'bg-yellow-100 text-yellow-700',
}[s] || 'bg-gray-100 text-gray-600')
</script>

<template>
  <div class="min-h-screen flex flex-col bg-[#E8DDD0]">
    <Navbar />

    <main class="flex-1 py-10 px-4">
      <p class="max-w-2xl mx-auto text-xs text-[#9CA3AF] mb-4">
        Beranda / Profil User / <span class="font-medium text-[#1a2744]">Riwayat Donasi</span>
      </p>

      <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-sm p-6">
        <h1 class="text-lg font-bold text-[#1a2744]">Riwayat Donasi</h1>
        <p class="text-xs text-[#9CA3AF] mb-4">Semua donasi yang pernah Anda lakukan</p>

        <!-- Empty -->
        <div v-if="donations.length === 0" class="flex flex-col items-center justify-center py-12">
          <Receipt :size="40" class="text-gray-300" />
          <p class="text-sm text-[#9CA3AF] mt-2">Belum ada riwayat donasi</p>
        </div>

        <!-- Table -->
        <div v-else>
          <div class="grid grid-cols-[1fr_auto_auto_auto] gap-4 border-b border-gray-100 pb-2 mb-1 text-xs font-semibold text-[#9CA3AF] uppercase tracking-wider">
            <span>Campaign</span>
            <span class="text-right">Nominal</span>
            <span class="text-center">Tanggal</span>
            <span class="text-right">Status</span>
          </div>

          <div
            v-for="d in donations"
            :key="d.id"
            class="grid grid-cols-[1fr_auto_auto_auto] gap-4 items-center py-3 border-b border-gray-50"
          >
            <div class="min-w-0">
              <p class="text-sm font-semibold text-[#1a2744] hover:text-[#8B4513] cursor-pointer truncate">
                {{ d.campaign }}
              </p>
              <p class="text-xs text-[#9CA3AF] truncate">{{ d.komunitas }}</p>
            </div>
            <p class="text-sm font-semibold text-[#8B4513] text-right whitespace-nowrap">
              {{ formatRp(d.nominal) }}
            </p>
            <p class="text-xs text-[#6B7280] text-center whitespace-nowrap">
              {{ formatDate(d.tanggal) }}
            </p>
            <span class="rounded-full px-2 py-0.5 text-xs font-medium" :class="statusClass(d.status)">
              {{ d.status }}
            </span>
          </div>
        </div>
      </div>
    </main>

    <Footer />
  </div>
</template>