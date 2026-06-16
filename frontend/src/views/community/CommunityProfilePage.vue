<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api/axios'
import Navbar from '@/components/shared/Navbar.vue'
import AppFooter from '@/components/shared/AppFooter.vue'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const profile = ref(null)
const isLoading = ref(true)

const initials = computed(() => {
  const name = profile.value?.nama_lembaga || ''
  return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2) || '--'
})
const totalDanaFormatted = computed(() => formatNumber(profile.value?.total_dana_diterima || 0))

const displayValue = (v) => v || '-'
const formatNumber = (n) => Number(n || 0).toLocaleString('id-ID')

const handleLogout = () => {
  authStore.logout()
  router.push('/login')
}

onMounted(async () => {
  try {
    const res = await api.get('/communities/profile')
    profile.value = res.data.data
  } catch (e) {
    if (e.response?.status === 401) router.push('/login')
  } finally {
    isLoading.value = false
  }
})
</script>

<template>
  <div class="min-h-screen bg-[#E8DDD0] flex flex-col">
    <Navbar />

    <main class="flex-1 px-4 py-6">
      <div class="max-w-2xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="text-xs text-gray-500 mb-4">
          <router-link to="/" class="hover:text-[#8B4513]">Beranda</router-link>
          <span class="mx-1">›</span>
          <span class="text-[#1a2744] font-medium">Profil Komunitas</span>
        </nav>

        <!-- ============ CARD 1: Header + Stat ============ -->
        <section class="bg-white rounded-2xl shadow-sm p-6 mb-4">
          <div class="flex items-start gap-4">
            <!-- Avatar -->
            <div class="shrink-0">
              <img
                v-if="profile?.foto_lembaga_url"
                :src="profile.foto_lembaga_url"
                alt="Logo komunitas"
                class="h-14 w-14 rounded-full object-cover"
              />
              <div
                v-else
                class="h-14 w-14 rounded-full bg-[#1a2744] text-white font-bold text-xl flex items-center justify-center"
              >
                <span v-if="!isLoading">{{ initials }}</span>
                <span v-else class="animate-pulse text-gray-300">··</span>
              </div>
            </div>

            <!-- Info -->
            <div class="flex-1 min-w-0">
              <template v-if="isLoading">
                <div class="h-5 w-48 bg-gray-200 rounded animate-pulse mb-2"></div>
                <div class="h-3 w-40 bg-gray-200 rounded animate-pulse mb-3"></div>
                <div class="h-4 w-56 bg-gray-200 rounded animate-pulse"></div>
              </template>
              <template v-else>
                <h2 class="text-lg font-semibold text-[#1a2744] truncate">
                  {{ profile?.nama_lembaga || '-' }}
                </h2>
                <div class="flex flex-wrap items-center gap-2 mt-2">
                  <span
                    v-if="profile?.status === 'aktif'"
                    class="inline-block px-2 py-0.5 bg-[#E8F5E9] text-[#2E7D32] text-xs font-semibold rounded"
                  >
                    TERVERIFIKASI
                  </span>
                  <span class="inline-block px-2 py-0.5 bg-[#E8F4F8] text-[#1a2744] text-xs font-semibold rounded">
                    KOMUNITAS
                  </span>
                </div>
              </template>
            </div>

            <button
              @click="router.push('/communities/profile/edit')"
              class="shrink-0 border border-[#8B4513] text-[#8B4513] bg-white hover:bg-[#8B4513]/5 text-sm font-medium rounded-lg px-4 py-1.5 transition-colors"
            >
              Edit Profil
            </button>
          </div>

          <!-- Stat 3 kolom -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-4 pt-4 border-t border-gray-100">
            <div class="bg-[#FDF5EE] rounded-xl p-3 text-center">
              <p class="text-xl font-bold text-[#1a2744]">
                {{ isLoading ? '—' : formatNumber(profile?.total_campaign_aktif) }}
              </p>
              <p class="text-xs text-gray-500 mt-0.5">Campaign Aktif</p>
            </div>
            <div class="bg-[#FDF5EE] rounded-xl p-3 text-center">
              <p class="text-xl font-bold text-[#1a2744]">
                {{ isLoading ? '—' : formatNumber(profile?.total_follower) }}
              </p>
              <p class="text-xs text-gray-500 mt-0.5">Anggota</p>
            </div>
            <div class="bg-[#FDF5EE] rounded-xl p-3 text-center">
              <p class="text-xl font-bold text-[#1a2744]">
                {{ isLoading ? '—' : totalDanaFormatted }}
              </p>
              <p class="text-xs text-gray-500 mt-0.5">Dana Terkumpul</p>
            </div>
          </div>
        </section>

        <!-- ============ CARD 2: Tentang Komunitas ============ -->
        <section class="bg-white rounded-2xl shadow-sm p-6 mb-4">
          <h3 class="text-base font-semibold text-[#1a2744] mb-5">Tentang Komunitas</h3>

          <div v-if="isLoading" class="space-y-4">
            <div class="h-4 w-full bg-gray-200 rounded animate-pulse"></div>
            <div class="h-4 w-3/4 bg-gray-200 rounded animate-pulse"></div>
          </div>

          <dl v-else class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4">
            <div class="sm:col-span-2">
              <dt class="text-xs tracking-widest uppercase text-gray-500 mb-1">Deskripsi</dt>
              <dd class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">
                {{ displayValue(profile?.deskripsi) }}
              </dd>
            </div>

            <hr class="sm:col-span-2 border-gray-100" />

            <div>
              <dt class="text-xs tracking-widest uppercase text-gray-500 mb-1">Alamat</dt>
              <dd class="text-sm font-medium text-[#1a2744]">{{ displayValue(profile?.alamat_detail) }}</dd>
            </div>
            <div>
              <dt class="text-xs tracking-widest uppercase text-gray-500 mb-1">Kontak</dt>
              <dd class="text-sm font-medium text-[#1a2744]">{{ displayValue(profile?.nomor_kontak) }}</dd>
            </div>
            <div>
              <dt class="text-xs tracking-widest uppercase text-gray-500 mb-1">Instagram</dt>
              <dd class="text-sm font-medium text-[#1a2744]">{{ displayValue(profile?.link_medsos) }}</dd>
            </div>
          </dl>

          <!-- Alert rekening -->
          <div class="mt-6 bg-amber-50 border border-amber-200 text-amber-800 text-xs rounded-lg p-3 flex items-center gap-2">
            <span aria-hidden="true">⚠️</span>
            Informasi rekening tidak ditampilkan ke publik.
          </div>

          <!-- Actions -->
          <div class="flex flex-wrap gap-2 mt-4">
            <button
              @click="router.push('/community/bank-settings')"
              class="border border-[#8B4513] text-[#8B4513] bg-white hover:bg-[#8B4513]/5 text-sm font-medium rounded-lg px-4 py-2 transition-colors"
            >
              Kelola Rekening
            </button>
            <button
              @click="router.push('/community/campaign-history')"
              class="border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 text-sm font-medium rounded-lg px-4 py-2 transition-colors"
            >
              Riwayat Campaign
            </button>
          </div>
        </section>

        <!-- Logout -->
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
