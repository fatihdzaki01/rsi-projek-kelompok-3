<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/api/axios'

const route = useRoute()
const router = useRouter()

const donation = ref(null)
const loading = ref(true)

const formattedAmount = computed(() => {
  if (!donation.value) return ''
  return 'Rp ' + Number(donation.value.nominal).toLocaleString('id-ID')
})

onMounted(async () => {
  try {
    const res = await api.get(`/donations/${route.params.id}`)
    donation.value = res.data.data
  } catch {
    alert('Gagal memuat data donasi')
    router.push('/donations/history')
  } finally {
    loading.value = false
  }
})

function goBack() {
  router.push('/')
}
</script>

<template>
  <div class="min-h-screen flex flex-col items-center justify-center px-4 py-8" style="background-color: #E8DDD0;">

    <div v-if="loading" class="text-sm text-gray-500">Memuat...</div>

    <!-- Card -->
    <div v-if="donation" class="relative bg-white rounded-3xl shadow-xl w-full max-w-md p-8 overflow-hidden">

      <!-- Decorative blob top-right -->
      <div
        class="absolute top-0 right-0 w-24 h-24 pointer-events-none"
        style="background-color: #EDE4D8; border-radius: 0 1.5rem 0 100%;"
      ></div>

      <!-- Success Icon -->
      <div
        class="relative z-10 w-12 h-12 rounded-full flex items-center justify-center mb-4"
        style="background-color: #5BC8C0;"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="20 6 9 17 4 12" />
        </svg>
      </div>

      <!-- Label -->
      <p class="text-xs font-semibold tracking-widest uppercase mb-1" style="color: #5BC8C0;">
        Donasi Berhasil!
      </p>

      <!-- Title -->
      <h1 class="text-3xl font-bold leading-tight mb-6" style="color: #1a2744;">
        Terimakasih<br>Orang Baik
      </h1>

      <!-- Impact Box -->
      <div
        class="rounded-lg p-4 mb-5"
        style="background-color: #FDF0E8; border-left: 4px solid #E8B89A;"
      >
        <p class="text-xs font-semibold uppercase tracking-wider mb-2" style="color: #a08070;">
          Dampak Donasi Anda
        </p>
        <p class="text-sm leading-relaxed" style="color: #3a3a3a;">
          Donasi sebesar
          <strong style="color: #1a2744;">{{ formattedAmount }}</strong>
          telah dialokasikan untuk {{ donation.judul_campaign }}.
        </p>
      </div>

      <!-- Badges -->
      <div class="flex flex-wrap gap-2 mb-6">
        <!-- ID Badge -->
        <span
          class="flex items-center gap-1.5 border rounded-full px-3 py-1 text-sm"
          style="border-color: #d1d5db; color: #4b5563;"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2" y="5" width="20" height="14" rx="2" />
            <line x1="2" y1="10" x2="22" y2="10" />
          </svg>
          ID: #{{ donation.nomor_transaksi }}
        </span>

        <!-- Donatur Tetap Badge -->
        <span
          v-if="false"
          class="flex items-center gap-1.5 border rounded-full px-3 py-1 text-sm"
          style="border-color: #fca5a5; color: #f87171;"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
          </svg>
          Donatur Tetap
        </span>
      </div>

      <!-- Action Buttons -->
      <div class="flex items-center gap-4">
        <button
          @click="goBack"
          class="px-6 py-3 rounded-xl font-semibold text-white text-sm transition-opacity hover:opacity-90 active:opacity-80"
          style="background-color: #1a2744;"
        >
          Kembali ke Beranda
        </button>
      </div>
    </div>

    <!-- Footer -->
    <div class="flex items-center gap-2 mt-6">
      <span class="text-xs" style="color: #9e8e80;">BERBAGIVE © 2024</span>
      <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" style="color: #9e8e80;" viewBox="0 0 24 24"
        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
      </svg>
      <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" style="color: #9e8e80;" viewBox="0 0 24 24"
        fill="currentColor">
        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
      </svg>
    </div>

  </div>
</template>
