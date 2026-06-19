<template>
  <div class="min-h-screen flex flex-col bg-[#E8DDD0]">
    <TheNavbar />

    <main class="flex-1 px-4 py-6">
      <div class="max-w-2xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="text-xs text-gray-500 mb-4">
          <router-link to="/" class="hover:text-[#8B4513]">Beranda</router-link>
          <span class="mx-1">›</span>
          <router-link to="/communities" class="hover:text-[#8B4513]">Komunitas</router-link>
          <span class="mx-1">›</span>
          <span class="text-[#1a2744] font-medium">{{ profile?.nama_lembaga || 'Profil Komunitas' }}</span>
        </nav>

        <!-- Loading -->
        <div v-if="loading" class="bg-white rounded-2xl shadow-sm p-6">
          <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-gray-200 animate-pulse shrink-0" />
            <div class="flex-1 space-y-2">
              <div class="h-5 w-48 bg-gray-200 rounded animate-pulse" />
              <div class="h-3 w-32 bg-gray-200 rounded animate-pulse" />
            </div>
          </div>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="bg-white rounded-2xl shadow-sm p-6 text-center">
          <div class="w-16 h-16 rounded-full bg-stone-200 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-stone-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
            </svg>
          </div>
          <h2 class="text-lg font-bold text-gray-700 mb-1">Komunitas tidak ditemukan</h2>
          <p class="text-sm text-gray-400">{{ error }}</p>
        </div>

        <!-- Content -->
        <template v-else-if="profile">
          <!-- Card 1: Header + Stat -->
          <section class="bg-white rounded-2xl shadow-sm p-6 mb-4">
            <div class="flex items-start gap-4">
              <!-- Avatar -->
              <div class="shrink-0">
                <img
                  v-if="profile.foto_lembaga_url"
                  :src="profile.foto_lembaga_url"
                  alt="Logo komunitas"
                  class="h-14 w-14 rounded-full object-cover"
                />
                <div
                  v-else
                  class="h-14 w-14 rounded-full bg-[#1a2744] text-white font-bold text-xl flex items-center justify-center"
                >
                  {{ initials }}
                </div>
              </div>

              <!-- Info -->
              <div class="flex-1 min-w-0">
                <h2 class="text-lg font-semibold text-[#1a2744] truncate">
                  {{ profile.nama_lembaga }}
                </h2>
                <div class="flex flex-wrap items-center gap-2 mt-2">
                  <span class="inline-block px-2 py-0.5 bg-[#E8F5E9] text-[#2E7D32] text-xs font-semibold rounded">
                    TERVERIFIKASI
                  </span>
                  <span class="inline-block px-2 py-0.5 bg-[#E8F4F8] text-[#1a2744] text-xs font-semibold rounded">
                    KOMUNITAS
                  </span>
                </div>
              </div>

              <router-link
                v-if="isOwner"
                to="/communities/profile/edit"
                class="shrink-0 text-sm font-medium rounded-lg px-4 py-1.5 border border-[#8B4513] text-[#8B4513] bg-white hover:bg-[#F5F0E8] transition-colors"
              >
                Edit Profil
              </router-link>
              
              <!-- Follow/Unfollow button (DONATUR only) -->
              <button
                v-else-if="canFollow"
                @click="isFollowing ? confirmUnfollow() : follow()"
                :disabled="followLoading"
                class="shrink-0 text-sm font-medium rounded-lg px-4 py-1.5 transition-colors"
                :class="isFollowing
                  ? 'border border-gray-200 text-gray-600 bg-white hover:bg-gray-50'
                  : 'border border-[#1a2744] text-white bg-[#1a2744] hover:bg-[#22325a]'"
              >
                {{ followLoading ? '...' : isFollowing ? 'Mengikuti' : 'Ikuti' }}
              </button>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 mt-4 pt-4 border-t border-gray-100">
              <div
                class="bg-[#FDF5EE] rounded-xl p-3 text-center cursor-pointer hover:bg-[#f8efe4] transition-colors"
                @click="showFollowersModal = true"
              >
                <p class="text-xl font-bold text-[#1a2744]">{{ formatNumber(profile.total_follower) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Followers</p>
              </div>
              <div
                class="bg-[#FDF5EE] rounded-xl p-3 text-center cursor-pointer hover:bg-[#f8efe4] transition-colors"
                @click="showCampaignAktifModal = true"
              >
                <p class="text-xl font-bold text-[#1a2744]">{{ profile.total_campaign_aktif }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Campaign Aktif</p>
              </div>
              <div
                class="bg-[#FDF5EE] rounded-xl p-3 text-center cursor-pointer hover:bg-[#f8efe4] transition-colors"
                @click="showCampaignSelesaiModal = true"
              >
                <p class="text-xl font-bold text-[#1a2744]">{{ profile.total_campaign_selesai }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Campaign Selesai</p>
              </div>
              <div class="bg-[#FDF5EE] rounded-xl p-3 text-center">
                <p class="text-xl font-bold text-[#8B4513]">{{ formatRupiah(profile.total_dana_diterima) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Total Dana</p>
              </div>
            </div>
          </section>

          <!-- Card 2: About -->
          <section class="bg-white rounded-2xl shadow-sm p-6 mb-4">
            <h3 class="text-base font-semibold text-[#1a2744] mb-5">Tentang Komunitas</h3>

            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4">
              <div class="sm:col-span-2">
                <dt class="text-xs tracking-widest uppercase text-gray-500 mb-1">Deskripsi</dt>
                <dd class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">
                  {{ profile.deskripsi || '-' }}
                </dd>
              </div>

              <hr class="sm:col-span-2 border-gray-100" />

              <div>
                <dt class="text-xs tracking-widest uppercase text-gray-500 mb-1">Alamat</dt>
                <dd class="text-sm font-medium text-[#1a2744]">{{ profile.alamat_detail || '-' }}</dd>
              </div>
              <div>
                <dt class="text-xs tracking-widest uppercase text-gray-500 mb-1">Kontak</dt>
                <dd class="text-sm font-medium text-[#1a2744]">{{ profile.nomor_kontak || '-' }}</dd>
              </div>
              <div>
                <dt class="text-xs tracking-widest uppercase text-gray-500 mb-1">Media Sosial</dt>
                <dd class="text-sm font-medium text-[#1a2744]">{{ profile.link_medsos || '-' }}</dd>
              </div>
            </dl>
          </section>

          <!-- Card 3: Active Campaigns -->
          <section class="bg-white rounded-2xl shadow-sm p-6 mb-4">
            <h3 class="text-base font-semibold text-[#1a2744] mb-4">Campaign Aktif</h3>

            <div v-if="profile.daftar_campaign_aktif?.length === 0" class="text-sm text-gray-400 text-center py-6">
              Belum ada campaign aktif.
            </div>

            <div v-else class="space-y-3">
              <div
                v-for="c in profile.daftar_campaign_aktif"
                :key="c.id_campaign"
                @click="router.push(`/campaigns/${c.id_campaign}`)"
                class="flex items-center justify-between p-3 rounded-xl bg-[#FDF5EE] hover:bg-[#f8efe4] cursor-pointer transition-colors"
              >
                <div class="min-w-0 flex-1">
                  <p class="text-sm font-semibold text-[#1a2744] truncate">{{ c.judul }}</p>
                  <p class="text-xs text-gray-500 mt-0.5">
                    Target: {{ formatRupiah(c.target_dana) }}
                  </p>
                </div>
                <div class="text-right shrink-0 ml-3">
                  <p class="text-sm font-bold text-[#8B4513]">{{ formatRupiah(c.dana_terkumpul) }}</p>
                  <p class="text-[10px] text-gray-400">terkumpul</p>
                </div>
              </div>
            </div>
          </section>
        </template>
      </div>
    </main>

    <!-- Unfollow Confirmation Modal -->
    <UnfollowModal
      :show="showUnfollowModal"
      @confirm="unfollow"
      @cancel="showUnfollowModal = false"
    />

    <!-- Followers Modal -->
    <FollowersModal
      :show="showFollowersModal"
      :community-id="profile?.id_komunitas"
      @close="showFollowersModal = false"
    />

    <!-- Campaign Aktif Modal -->
    <CampaignListModal
      :show="showCampaignAktifModal"
      title="Campaign Aktif"
      empty-message="Belum ada campaign aktif."
      :campaigns="profile?.daftar_campaign_aktif || []"
      @close="showCampaignAktifModal = false"
    />

    <!-- Campaign Selesai Modal -->
    <CampaignListModal
      :show="showCampaignSelesaiModal"
      title="Campaign Selesai"
      empty-message="Belum ada campaign selesai."
      :campaigns="profile?.daftar_campaign_selesai || []"
      @close="showCampaignSelesaiModal = false"
    />

    <AppFooter />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/api/axios'
import TheNavbar from '@/components/shared/Navbar.vue'
import AppFooter from '@/components/shared/AppFooter.vue'
import UnfollowModal from '@/components/community/UnfollowModal.vue'
import FollowersModal from '@/components/community/FollowersModal.vue'
import CampaignListModal from '@/components/community/CampaignListModal.vue'
import { useAuthStore } from '@/stores/auth'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const profile = ref(null)
const loading = ref(true)
const error = ref('')
const isFollowing = ref(false)
const followLoading = ref(false)
const showUnfollowModal = ref(false)
const showFollowersModal = ref(false)
const showCampaignAktifModal = ref(false)
const showCampaignSelesaiModal = ref(false)

const initials = computed(() => {
  const name = profile.value?.nama_lembaga || ''
  return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2) || '--'
})

const canFollow = computed(() => {
  return authStore.isLoggedIn && authStore.isDonor
})

const isOwner = computed(() => {
  return authStore.isLoggedIn && authStore.isCommunity && 
         (authStore.user?.komunitas?.id_komunitas || authStore.user?.id_komunitas) === profile.value?.id_komunitas
})

async function fetchProfile() {
  try {
    const res = await api.get(`/communities/${route.params.id}/profile`)
    profile.value = res.data.data
    isFollowing.value = res.data.data.is_following || false
  } catch (e) {
    error.value = e.response?.data?.message || 'Komunitas tidak ditemukan.'
  } finally {
    loading.value = false
  }
}

async function follow() {
  followLoading.value = true
  try {
    await api.post(`/communities/${route.params.id}/follow`)
    isFollowing.value = true
    if (profile.value) profile.value.total_follower++
  } catch (e) {
    alert(e.response?.data?.message || 'Gagal mengikuti komunitas.')
  } finally {
    followLoading.value = false
  }
}

function confirmUnfollow() {
  showUnfollowModal.value = true
}

async function unfollow() {
  showUnfollowModal.value = false
  followLoading.value = true
  try {
    await api.delete(`/communities/${route.params.id}/follow`)
    isFollowing.value = false
    if (profile.value) profile.value.total_follower = Math.max(0, profile.value.total_follower - 1)
  } catch (e) {
    alert(e.response?.data?.message || 'Gagal berhenti mengikuti komunitas.')
  } finally {
    followLoading.value = false
  }
}

function formatNumber(n) {
  n = n || 0
  if (n >= 1000) return (n / 1000).toFixed(1).replace('.0', '') + 'K'
  return n.toLocaleString('id-ID')
}

function formatRupiah(n) {
  n = n || 0
  if (n >= 1000000000) return 'Rp ' + (n / 1000000000).toFixed(1) + 'M'
  if (n >= 1000000) return 'Rp ' + (n / 1000000).toFixed(1) + 'Jt'
  return 'Rp ' + n.toLocaleString('id-ID')
}

onMounted(fetchProfile)
</script>
