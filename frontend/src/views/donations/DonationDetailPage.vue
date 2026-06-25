<template>
  <div class="min-h-screen flex flex-col bg-[#F5F0E8]">
    <Navbar />

    <main class="flex-1 py-8">
      <div class="max-w-2xl mx-auto px-6">
        <nav class="text-sm text-gray-500 mb-4 flex items-center gap-1">
          <router-link to="/" class="hover:text-[#8B4513] transition-colors">Beranda</router-link>
          <span>/</span>
          <router-link to="/donations/history" class="hover:text-[#8B4513] transition-colors">Riwayat Donasi</router-link>
          <span>/</span>
          <span class="text-[#2C2C2C] font-medium">Detail Donasi</span>
        </nav>

        <!-- Loading -->
        <div v-if="loading" class="bg-white rounded-xl shadow-sm p-8 text-center">
          <div class="w-8 h-8 border-2 border-[#8B4513] border-t-transparent rounded-full animate-spin mx-auto mb-3" />
          <p class="text-sm text-gray-400">Memuat detail donasi...</p>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="bg-white rounded-xl shadow-sm p-8 text-center">
          <div class="w-16 h-16 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <h2 class="text-lg font-bold text-gray-700 mb-1">Gagal memuat detail</h2>
          <p class="text-sm text-gray-400">{{ error }}</p>
        </div>

        <!-- Content -->
        <template v-else-if="donation">
          <!-- Status Banner -->
          <div
            class="rounded-xl px-4 py-3 mb-4 text-sm font-medium flex items-center gap-2"
            :class="statusBannerClass"
          >
            <span class="w-2 h-2 rounded-full shrink-0" :class="statusDotClass" />
            {{ statusLabel }}
          </div>

          <!-- Card: Donasi Info -->
          <section class="bg-white rounded-xl shadow-sm overflow-hidden mb-4">
            <div class="px-6 py-5 border-b border-gray-100">
              <h1 class="text-lg font-bold text-[#2C2C2C]">{{ donation.judul_campaign }}</h1>
            </div>

            <div class="px-6 py-5 space-y-4">
              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-500">Nomor Transaksi</span>
                <span class="text-sm font-mono font-semibold text-[#2C2C2C]">{{ donation.nomor_transaksi }}</span>
              </div>

              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-500">Nominal Donasi</span>
                <span class="text-lg font-bold text-[#8B4513]">{{ formatCurrency(donation.nominal) }}</span>
              </div>

              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-500">Metode Pembayaran</span>
                <span class="text-sm font-medium text-[#2C2C2C] capitalize">{{ donation.metode_pembayaran }}</span>
              </div>

              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-500">Tanggal</span>
                <span class="text-sm text-[#2C2C2C]">{{ formatDate(donation.created_at) }}</span>
              </div>

              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-500">Status</span>
                <span :class="badgeClass">{{ statusLabel }}</span>
              </div>
            </div>
          </section>

          <!-- Card: Identitas -->
          <section class="bg-white rounded-xl shadow-sm overflow-hidden mb-4">
            <div class="px-6 py-4 border-b border-gray-100">
              <h2 class="text-sm font-bold text-[#2C2C2C]">Identitas Donasi</h2>
            </div>
            <div class="px-6 py-4 space-y-3">
              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-500">Ditampilkan sebagai</span>
                <span class="text-sm font-medium text-[#2C2C2C]">
                  {{ donation.is_anonim ? 'Anonim' : (donation.nama_tampil || 'Anonim') }}
                </span>
              </div>
              <div v-if="donation.pesan" class="border-t border-gray-100 pt-3">
                <span class="text-xs text-gray-500 block mb-1">Pesan / Doa</span>
                <p class="text-sm text-[#2C2C2C] italic">"{{ donation.pesan }}"</p>
              </div>
            </div>
          </section>

          <!-- Actions -->
          <div class="flex gap-3">
            <router-link
              to="/"
              class="flex-1 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 text-center hover:bg-gray-50 transition-colors"
            >
              Kembali ke Beranda
            </router-link>
          </div>
        </template>
      </div>
    </main>

    <Footer />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/api/axios'
import Navbar from '@/components/shared/Navbar.vue'
import Footer from '@/components/shared/Footer.vue'

const route = useRoute()
const router = useRouter()

const donation = ref(null)
const loading = ref(true)
const error = ref('')

async function fetchDetail() {
  try {
    const res = await api.get(`/donations/${route.params.id}`)
    donation.value = res.data.data
  } catch (e) {
    error.value = e.response?.data?.message || 'Gagal memuat detail donasi.'
  } finally {
    loading.value = false
  }
}

const statusBannerClass = computed(() => {
  const map = {
    berhasil: 'bg-green-50 text-green-700 border border-green-200',
    pending: 'bg-amber-50 text-amber-700 border border-amber-200',
    gagal: 'bg-red-50 text-red-700 border border-red-200',
  }
  return map[donation.value?.status_pembayaran] || 'bg-gray-50 text-gray-600 border border-gray-200'
})

const statusDotClass = computed(() => {
  const map = { berhasil: 'bg-green-500', pending: 'bg-amber-500', gagal: 'bg-red-500' }
  return map[donation.value?.status_pembayaran] || 'bg-gray-500'
})

const statusLabel = computed(() => {
  const map = { berhasil: 'Berhasil', pending: 'Pending', gagal: 'Gagal' }
  return map[donation.value?.status_pembayaran] || donation.value?.status_pembayaran
})

const badgeClass = computed(() => {
  const base = 'px-2.5 py-0.5 rounded-full text-xs font-medium'
  const map = { berhasil: 'bg-green-100 text-green-700', pending: 'bg-amber-100 text-amber-700', gagal: 'bg-red-100 text-red-700' }
  return `${base} ${map[donation.value?.status_pembayaran] || 'bg-gray-100 text-gray-600'}`
})

function formatCurrency(n) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n)
}

function formatDate(s) {
  return new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'long', year: 'numeric', timeZone: 'Asia/Jakarta' }).format(new Date(s))
}

onMounted(fetchDetail)
</script>
