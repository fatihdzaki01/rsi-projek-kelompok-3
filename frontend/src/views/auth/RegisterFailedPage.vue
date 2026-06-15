<script setup>
import { useRouter } from 'vue-router'
import { X } from 'lucide-vue-next'

const router = useRouter()
</script>

<template>
  <div class="min-h-screen bg-[#E8DDD0] flex items-center justify-center px-4 py-8">
    <div class="w-full max-w-sm bg-white rounded-2xl shadow-md p-10 text-center">
      <div class="mx-auto h-12 w-12 rounded-full bg-red-100 flex items-center justify-center mb-4">
        <X class="h-6 w-6 text-red-600" stroke-width="2.5" />
      </div>

      <h1 class="text-xl font-bold text-[#1a2744] mb-2">Registrasi gagal</h1>
      <p class="text-sm text-gray-500 mb-6">
        Terjadi kesalahan saat membuat akun. Silakan coba lagi.
      </p>

      <button
        @click="router.push('/register')"
        class="w-full h-12 bg-[#1a2744] hover:bg-[#2a3a5c] text-white rounded-full font-medium transition-colors"
      >
        Coba Lagi
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
