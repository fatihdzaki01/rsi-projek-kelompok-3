<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Check, ArrowRight } from 'lucide-vue-next'
import Navbar from '@/components/shared/Navbar.vue'
import AppFooter from '@/components/shared/AppFooter.vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/api/axios'

const router = useRouter()
const authStore = useAuthStore()

const user = computed(() => authStore.user)
const isLoading = ref(true)

// ---------- Helpers ----------
const userInitials = computed(() => {
  if (!user.value?.nama_lengkap) return '?'
  return user.value.nama_lengkap
    .split(' ')
    .slice(0, 2)
    .map((n) => n[0])
    .join('')
    .toUpperCase()
})

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
}

const formatJoinDate = (dateStr) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('id-ID', {
    month: 'long',
    year: 'numeric',
  })
}

const formatPhone = (phone) => {
  if (!phone) return '-'
  // Format 0812-3456-7890
  return phone.replace(/(\d{4})(\d{4})(\d+)/, '$1-$2-$3')
}

const formatRupiah = (num) => {
  if (!num) return 'Rp 0'
  return 'Rp ' + Number(num).toLocaleString('id-ID')
}

const formatGender = (val) => ({ L: 'Laki-laki', P: 'Perempuan' }[val] || '-')

const displayValue = (val) => val || '-'

// ---------- Actions ----------
const goEditProfile = () => router.push('/profile/edit')
const goChangePassword = () => router.push('/profile/change-password')
const goDonationHistory = () => router.push('/donations/history')

const handleLogout = () => {
  authStore.logout()
  router.push('/login')
}

// ---------- Fetch ----------
onMounted(async () => {
  try {
    await authStore.fetchMe()
  } catch (e) {
    if (e.response?.status === 401) {
      router.push('/login')
      return
    }
  } finally {
    isLoading.value = false
  }
})
</script>

<template>
  <div class="min-h-screen bg-[#E8DDD0] flex flex-col">
    <Navbar @logout="handleLogout" />

    <main class="flex-1 px-4 py-6">
      <div class="max-w-2xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="text-xs text-gray-500 mb-4">
          <router-link to="/" class="hover:text-[#8B4513]">Beranda</router-link>
          <span class="mx-1">›</span>
          <span class="text-[#1a2744] font-medium">Profil User</span>
        </nav>

        <!-- ============ CARD 1: Header Profil ============ -->
        <section class="bg-white rounded-2xl shadow-sm p-6 mb-4">
          <div class="flex items-start gap-4">
            <!-- Avatar -->
            <div class="relative shrink-0">
              <div
                v-if="user?.foto_profil_url"
                class="h-14 w-14 rounded-full overflow-hidden"
              >
                <img
                  :src="user.foto_profil_url"
                  alt="Foto profil"
                  class="h-full w-full object-cover"
                />
              </div>
              <div
                v-else
                class="h-14 w-14 rounded-full bg-[#1a2744] text-white font-bold text-xl flex items-center justify-center"
              >
                <span v-if="!isLoading">{{ userInitials }}</span>
                <span v-else class="animate-pulse text-gray-300">··</span>
              </div>
              <div
                v-if="user?.is_verified"
                class="absolute -bottom-0.5 -right-0.5 h-5 w-5 rounded-full bg-[#5BC8C0] border-2 border-white flex items-center justify-center"
                title="Terverifikasi"
              >
                <Check class="h-3 w-3 text-white" stroke-width="3" />
              </div>
            </div>

            <!-- Info ringkas -->
            <div class="flex-1 min-w-0">
              <template v-if="isLoading">
                <div class="h-5 w-40 bg-gray-200 rounded animate-pulse mb-2"></div>
                <div class="h-3 w-48 bg-gray-200 rounded animate-pulse mb-3"></div>
                <div class="h-4 w-64 bg-gray-200 rounded animate-pulse"></div>
              </template>
              <template v-else>
                <h2 class="text-lg font-semibold text-[#1a2744] truncate">
                  {{ user?.nama_lengkap || '-' }}
                </h2>
                <p class="text-sm text-gray-500 truncate">{{ user?.email || '-' }}</p>
                <div class="flex flex-wrap items-center gap-2 mt-2">
                  <span class="inline-block px-2 py-0.5 bg-[#E8F4F8] text-[#1a2744] text-xs font-semibold rounded">
                    DONATUR
                  </span>
                  <span
                    v-if="user?.is_verified"
                    class="inline-block px-2 py-0.5 bg-[#E8F5E9] text-[#2E7D32] text-xs font-semibold rounded"
                  >
                    TERVERIFIKASI
                  </span>
                  <span class="text-xs text-gray-500">
                    Bergabung {{ formatJoinDate(user?.created_at) }}
                  </span>
                </div>
              </template>
            </div>

            <!-- Edit Profil button -->
            <button
              @click="goEditProfile"
              class="shrink-0 border border-[#8B4513] text-[#8B4513] bg-white hover:bg-[#8B4513]/5 text-sm font-medium rounded-lg px-4 py-1.5 transition-colors"
            >
              Edit Profil
            </button>
          </div>
        </section>

        <!-- ============ CARD 2: Informasi Pribadi ============ -->
        <section class="bg-white rounded-2xl shadow-sm p-6 mb-4">
          <h3 class="text-base font-semibold text-[#1a2744]">Informasi Pribadi</h3>
          <p class="text-xs text-gray-500 mb-5">Data profil Anda yang tersimpan di sistem.</p>

          <!-- Skeleton -->
          <div v-if="isLoading" class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4">
            <div v-for="i in 6" :key="i">
              <div class="h-3 w-24 bg-gray-200 rounded animate-pulse mb-2"></div>
              <div class="h-4 w-40 bg-gray-200 rounded animate-pulse"></div>
            </div>
          </div>

          <!-- Data grid -->
          <dl v-else class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4">
            <div>
              <dt class="text-xs tracking-widest uppercase text-gray-500 mb-1">Nama Lengkap</dt>
              <dd class="text-sm font-medium text-[#1a2744]">{{ displayValue(user?.nama_lengkap) }}</dd>
            </div>
            <div>
              <dt class="text-xs tracking-widest uppercase text-gray-500 mb-1">Email</dt>
              <dd class="text-sm font-medium text-[#1a2744] break-all">{{ displayValue(user?.email) }}</dd>
            </div>
            <div>
              <dt class="text-xs tracking-widest uppercase text-gray-500 mb-1">Nomor Telepon</dt>
              <dd class="text-sm font-medium text-[#1a2744]">{{ formatPhone(user?.nomor_telepon) }}</dd>
            </div>
            <div>
              <dt class="text-xs tracking-widest uppercase text-gray-500 mb-1">Jenis Kelamin</dt>
              <dd class="text-sm font-medium text-[#1a2744]">{{ formatGender(user?.jenis_kelamin) }}</dd>
            </div>
            <div>
              <dt class="text-xs tracking-widest uppercase text-gray-500 mb-1">Tanggal Lahir</dt>
              <dd class="text-sm font-medium text-[#1a2744]">{{ formatDate(user?.tanggal_lahir) }}</dd>
            </div>
            <div>
              <dt class="text-xs tracking-widest uppercase text-gray-500 mb-1">Kota Domisili</dt>
              <dd class="text-sm font-medium text-[#1a2744]">
                {{ displayValue(user?.kota_domisili || user?.kode_wilayah) }}
              </dd>
            </div>
          </dl>

          <!-- Divider -->
          <hr class="my-5 border-gray-100" />

          <!-- Action buttons -->
          <div class="flex flex-wrap gap-2">
            <button
              @click="goEditProfile"
              class="bg-[#1a2744] hover:bg-[#2a3a5c] text-white text-sm font-medium rounded-lg px-4 py-2 transition-colors"
            >
              Edit Profil
            </button>
            <button
              @click="goChangePassword"
              class="border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 text-sm font-medium rounded-lg px-4 py-2 transition-colors"
            >
              Ganti Password
            </button>
          </div>
        </section>

        <!-- ============ CARD 3: Ringkasan Donasi ============ -->
        <section class="bg-white rounded-2xl shadow-sm p-6">
          <h3 class="text-base font-semibold text-[#1a2744] mb-4">Ringkasan Donasi</h3>

          <button
            @click="goDonationHistory"
            class="border border-[#8B4513] text-[#8B4513] bg-white hover:bg-[#8B4513]/5 text-sm font-medium rounded-lg px-4 py-2 transition-colors inline-flex items-center gap-1"
          >
            Lihat Riwayat Donasi
            <ArrowRight class="h-4 w-4" />
          </button>
        </section>

        <!-- Logout button -->
        <div class="flex justify-end mt-6">
          <button
            @click="handleLogout"
            class="border border-[#8B4513] text-[#8B4513] bg-white hover:bg-[#8B4513]/5 text-sm font-medium rounded-lg px-6 py-2 transition-colors"
          >
            Logout
          </button>
        </div>
      </div>
    </main>

    <AppFooter />
  </div>
</template>
