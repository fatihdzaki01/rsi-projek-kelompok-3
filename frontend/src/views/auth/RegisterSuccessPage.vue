<script setup>
import { useRouter } from 'vue-router'
import { Check } from 'lucide-vue-next'

const router = useRouter()
</script>

<template>
  <div class="min-h-screen bg-[#E8DDD0] flex items-center justify-center px-4 py-8">
    <div class="w-full max-w-sm bg-white rounded-2xl shadow-md p-10 text-center">
      <div class="mx-auto h-12 w-12 rounded-full bg-[#FDF5EE] flex items-center justify-center mb-4">
        <Check class="h-6 w-6 text-[#8B4513]" stroke-width="2.5" />
      </div>

      <h1 class="text-xl font-bold text-[#1a2744] mb-2">Registrasi berhasil</h1>
      <p class="text-sm text-gray-500 mb-6">
        Akunmu berhasil dibuat. Silakan login untuk melanjutkan.
      </p>

      <button
        @click="router.push('/login')"
        class="w-full h-12 bg-[#1a2744] hover:bg-[#2a3a5c] text-white rounded-full font-medium transition-colors"
      >
        Login Sekarang
      </button>

      <button
        @click="router.push('/')"
        class="mt-3 text-xs text-gray-500 hover:text-[#8B4513]"
      >
        Kembali ke Beranda
      </button>
    </div>
  </div>
</template>
