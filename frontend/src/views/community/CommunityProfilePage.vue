<template>
  <div class="min-h-screen bg-[#F5F0E8] flex flex-col">

    <!-- ===== NAVBAR ===== -->
    <nav class="bg-[#F5F0E8] border-b border-stone-200 px-6 py-3 flex items-center justify-between sticky top-0 z-30 backdrop-blur-sm">
      <div class="flex items-center gap-6">
        <span class="font-bold text-[#1a2744] tracking-wide text-sm">BERBAGIVE</span>
        <div class="hidden md:flex items-center gap-1">
          <a href="#" class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 rounded-full transition-colors">Beranda</a>
          <a href="#" class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 rounded-full transition-colors">Campaign</a>
          <a href="#" class="px-3 py-1.5 text-xs font-medium bg-[#1a2744] text-white rounded-full">Komunitas</a>
          <a href="#" class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 rounded-full transition-colors">Donasi Saya</a>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <div class="relative hidden sm:block">
          <svg class="w-3.5 h-3.5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
          </svg>
          <input type="text" placeholder="Search" class="bg-white/70 text-xs pl-8 pr-4 py-1.5 rounded-full border border-stone-200 focus:outline-none focus:ring-1 focus:ring-stone-300 w-36"/>
        </div>
        <button class="w-7 h-7 rounded-full bg-stone-300 flex items-center justify-center">
          <svg class="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 20 20"><path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/></svg>
        </button>
        <button class="text-gray-500 hover:text-gray-700">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
          </svg>
        </button>
        <button class="text-gray-500 hover:text-gray-700">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/>
          </svg>
        </button>
      </div>
    </nav>

    <!-- ===== LOADING ===== -->
    <div v-if="loading" class="flex-1 flex flex-col items-center justify-center py-20">
      <div class="w-8 h-8 border-2 border-[#8B4513] border-t-transparent rounded-full animate-spin mb-3" />
      <p class="text-sm text-gray-400">Memuat profil komunitas...</p>
    </div>

    <!-- ===== COMMUNITY INACTIVE STATE ===== -->
    <div v-else-if="!community.is_active" class="flex-1 flex flex-col items-center justify-center px-4 py-20 text-center">
      <div class="w-16 h-16 rounded-full bg-stone-200 flex items-center justify-center mb-4">
        <svg class="w-8 h-8 text-stone-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
        </svg>
      </div>
      <h2 class="text-lg font-bold text-gray-700 mb-1">Komunitas tidak tersedia</h2>
      <p class="text-sm text-gray-400">Komunitas ini telah dinonaktifkan atau tidak dapat ditemukan.</p>
      <a href="#" class="mt-6 text-xs font-medium text-[#8B4513] underline underline-offset-2">← Kembali ke Beranda</a>
    </div>

    <!-- ===== MAIN CONTENT ===== -->
    <main v-else class="flex-1 px-4 sm:px-6 py-6 max-w-3xl mx-auto w-full">

      <!-- Back link -->
      <a href="#" class="inline-flex items-center gap-1.5 text-xs text-gray-400 hover:text-gray-600 transition-colors mb-5">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Kembali ke Detail Campaign
      </a>

      <!-- ===== SECTION 1: Profile Card ===== -->
      <CommunityProfileCard
        :community="community"
        :is-following="isFollowing"
        @follow="handleFollow"
        @unfollow-click="handleUnfollowClick"
        class="mb-6"
      />

      <!-- ===== SECTION 2: Campaign List ===== -->
      <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-5 mb-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-sm font-bold text-gray-900">Campaign</h2>
        </div>
        <CampaignTabList
          v-if="campaigns.aktif.length > 0 || campaigns.selesai.length > 0"
          :campaigns="campaigns"
          v-model:activeTab="activeTab"
        />
        <div v-else class="flex flex-col items-center justify-center py-10 text-center">
          <div class="w-10 h-10 rounded-full bg-stone-100 flex items-center justify-center mb-2 text-lg">📢</div>
          <p class="text-sm font-medium text-gray-400">Belum ada campaign</p>
        </div>
      </div>

      <!-- ===== SECTION 3: Update Feed ===== -->
      <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-5">
        <div class="flex items-center gap-2 mb-5">
          <div class="w-1 h-4 bg-[#8B4513] rounded-full" />
          <h2 class="text-sm font-bold text-gray-900">Update Terbaru dari Komunitas</h2>
        </div>
        <div v-if="updates.length > 0">
          <CommunityUpdateFeed :updates="updates" />
        </div>
        <div v-else class="flex flex-col items-center justify-center py-10 text-center">
          <div class="w-10 h-10 rounded-full bg-stone-100 flex items-center justify-center mb-2 text-lg">📝</div>
          <p class="text-sm font-medium text-gray-400">Belum ada update</p>
        </div>
      </div>

    </main>

    <!-- ===== FOOTER ===== -->
    <footer class="border-t border-stone-200 bg-[#F5F0E8] px-6 py-6 mt-4">
      <div class="max-w-5xl mx-auto flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <p class="font-bold text-[#1a2744] text-sm mb-0.5">Berbagive</p>
          <p class="text-[10px] text-gray-400">© 2024 Berbagive. Part of The Human Archive project.</p>
        </div>
        <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500">
          <a href="#" class="hover:text-gray-700">Kebijakan Privasi</a>
          <a href="#" class="hover:text-gray-700">Syarat &amp; Ketentuan</a>
          <a href="#" class="hover:text-gray-700">Hubungi Kami</a>
          <a href="#" class="hover:text-gray-700">FAQ</a>
          <button class="hover:text-gray-700">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
              <path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92s2.92-1.31 2.92-2.92-1.31-2.92-2.92-2.92z"/>
            </svg>
          </button>
        </div>
      </div>
    </footer>

    <!-- ===== UNFOLLOW MODAL ===== -->
    <UnfollowModal
      :show="showUnfollowModal"
      @confirm="confirmUnfollow"
      @cancel="cancelUnfollow"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/api/axios'
import CommunityProfileCard from '@/components/community/CommunityProfileCard.vue'
import CampaignTabList from '@/components/campaign/CampaignTabList.vue'
import CommunityUpdateFeed from '@/components/community/CommunityUpdateFeed.vue'
import UnfollowModal from '@/components/community/UnfollowModal.vue'

const route = useRoute()

// --- State ---
const loading = ref(true)
const isFollowing = ref(false)
const showUnfollowModal = ref(false)
const activeTab = ref('aktif')
const isGuest = ref(false)

const community = ref({
  foto_komunitas: '',
  nama_komunitas: '',
  deskripsi_komunitas: '',
  alamat_lengkap: '',
  nomor_kontak: '',
  tanggal_bergabung: '',
  total_follower: 0,
  total_dana_diterima: 0,
  total_campaign_aktif: 0,
  total_campaign_selesai: 0,
  is_active: true
})

const campaigns = ref({ aktif: [], selesai: [] })
const updates = ref([])

// --- Fetch ---
async function fetchProfile() {
  try {
    const res = await api.get(`/communities/${route.params.id}/profile`)
    const data = res.data.data

    community.value = {
      foto_komunitas: data.foto_lembaga_url || '',
      nama_komunitas: data.nama_lembaga || '',
      deskripsi_komunitas: data.deskripsi || '',
      alamat_lengkap: data.alamat_detail || '',
      nomor_kontak: data.nomor_kontak || '',
      tanggal_bergabung: data.created_at || '',
      total_follower: data.total_follower || 0,
      total_dana_diterima: data.total_dana_diterima || 0,
      total_campaign_aktif: data.total_campaign_aktif || 0,
      total_campaign_selesai: data.total_campaign_selesai || 0,
      is_active: data.status === 'aktif'
    }

    campaigns.value.aktif = (data.daftar_campaign_aktif || []).map(c => ({
      id: c.id_campaign,
      judul: c.judul,
      dana_terkumpul: c.dana_terkumpul,
      target_dana: c.target_dana,
      persentase: c.target_dana > 0 ? Math.round((c.dana_terkumpul / c.target_dana) * 100) : 0
    }))
  } catch (err) {
    if (err.response?.status === 404) {
      community.value.is_active = false
    }
  } finally {
    loading.value = false
  }
}

onMounted(fetchProfile)

// --- Actions ---
async function handleFollow() {
  if (isGuest.value) return
  try {
    await api.post(`/communities/${route.params.id}/follow`)
    isFollowing.value = true
    community.value.total_follower++
  } catch (e) {
    // silent
  }
}

function handleUnfollowClick() {
  showUnfollowModal.value = true
}

async function confirmUnfollow() {
  try {
    await api.delete(`/communities/${route.params.id}/follow`)
    isFollowing.value = false
    community.value.total_follower--
  } catch (e) {
    // silent
  }
  showUnfollowModal.value = false
}

function cancelUnfollow() {
  showUnfollowModal.value = false
}
</script>
