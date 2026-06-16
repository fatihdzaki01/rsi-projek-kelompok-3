<script setup>
import { useRouter } from 'vue-router'
import { HeartHandshake, Users, ArrowLeft, ArrowRight } from 'lucide-vue-next'
import AppNavbar from '@/components/shared/AppNavbar.vue'
import AppFooter from '@/components/shared/AppFooter.vue'

const router = useRouter()

const pilihDonatur = () => router.push('/register/donatur')
const pilihKomunitas = () => router.push('/register/komunitas')
</script>

<template>
  <div class="min-h-screen bg-[#E8DDD0] flex flex-col">
    <AppNavbar />

    <main class="flex-1 px-4 py-8">
      <div class="max-w-4xl mx-auto">
        <!-- Back link -->
        <div class="flex justify-end mb-4">
          <button
            @click="router.push('/')"
            class="flex items-center gap-1 text-sm text-gray-600 hover:text-[#8B4513]"
          >
            <ArrowLeft class="h-4 w-4" />
            Kembali ke Beranda
          </button>
        </div>

        <!-- Main card -->
        <div class="bg-[#FDF5EE] rounded-2xl shadow-md p-10">
          <h1 class="text-3xl font-bold text-[#1a2744] mb-2">Daftar Sebagai</h1>
          <p class="text-sm text-gray-500 mb-8 max-w-md">
            Pilih bagaimana Anda ingin berkontribusi dalam arsip kemanusiaan ini.
          </p>

          <!-- Two choice cards -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Donatur -->
            <div class="bg-white border border-gray-100 rounded-xl p-5 flex flex-col">
              <div class="h-11 w-11 rounded-lg bg-[#5BC8C0] flex items-center justify-center mb-4">
                <HeartHandshake class="h-6 w-6 text-white" />
              </div>
              <h3 class="font-semibold text-[#1a2744] mb-2">Donatur</h3>
              <p class="text-xs text-gray-600 mb-6 flex-1">
                Dukung berbagai kampanye sosial melalui donasi satu kali atau rutin untuk membantu mereka yang membutuhkan.
              </p>
              <button
                @click="pilihDonatur"
                class="text-sm text-[#5BC8C0] font-medium hover:underline text-left flex items-center gap-1"
              >
                Pilih Sebagai Donatur
                <ArrowRight class="h-4 w-4" />
              </button>
            </div>

            <!-- Komunitas -->
            <div class="bg-white border border-gray-100 rounded-xl p-5 flex flex-col">
              <div class="h-11 w-11 rounded-lg bg-[#1a2744] flex items-center justify-center mb-4">
                <Users class="h-6 w-6 text-white" />
              </div>
              <h3 class="font-semibold text-[#1a2744] mb-2">Komunitas</h3>
              <p class="text-xs text-gray-600 mb-6 flex-1">
                Kelola transparansi penggalangan dana komunitas Anda dan mulai buat dampak nyata secara kolektif.
              </p>
              <button
                @click="pilihKomunitas"
                class="text-sm text-[#1a2744] font-medium hover:underline text-left flex items-center gap-1"
              >
                Pilih Sebagai Komunitas
                <ArrowRight class="h-4 w-4" />
              </button>
            </div>
          </div>

          <!-- Login link -->
          <p class="mt-6 text-xs text-gray-500">
            Sudah memiliki akun?
            <router-link to="/login" class="text-[#8B4513] font-medium hover:underline">
              Masuk di sini
            </router-link>
          </p>
        </div>
      </div>
    </main>

    <AppFooter />
  </div>
</template>
