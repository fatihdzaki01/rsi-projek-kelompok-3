<template>
  <div class="min-h-screen bg-[#F5F0E8] flex flex-col">

    <!-- ===== NAVBAR ===== -->
    <nav class="bg-[#F5F0E8] border-b border-stone-200 px-6 py-3 flex items-center justify-between sticky top-0 z-30">
      <div class="flex items-center gap-6">
        <span class="font-bold text-[#1a2744] tracking-wide text-sm">BERBAGIVE</span>
        <div class="hidden md:flex items-center gap-1">
          <a href="#" class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 rounded-full">Beranda</a>
          <a href="#" class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 rounded-full">Campaign</a>
          <a href="#" class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 rounded-full">Komunitas</a>
          <a href="#" class="px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 rounded-full">Donasi Saya</a>
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
        <!-- Mail icon with unread badge -->
        <div class="relative">
          <button class="text-gray-500 hover:text-gray-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
          </button>
          <span v-if="unreadCount > 0" class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-[#8B4513] rounded-full text-[8px] text-white flex items-center justify-center font-bold">
            {{ unreadCount > 9 ? '9+' : unreadCount }}
          </span>
        </div>
      </div>
    </nav>

    <!-- ===== MAIN CONTENT ===== -->
    <main class="flex-1 max-w-5xl mx-auto w-full px-4 sm:px-6 py-6">

      <!-- Loading -->
      <div v-if="loading" class="flex flex-col items-center justify-center py-20">
        <div class="w-8 h-8 border-2 border-[#8B4513] border-t-transparent rounded-full animate-spin mb-3" />
        <p class="text-sm text-gray-400">Memuat notifikasi...</p>
      </div>

    <template v-else>
    <!-- ===== MOBILE: List view ===== -->
      <div v-if="!mobileShowDetail" class="lg:hidden">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-2">
            <h1 class="text-xl font-bold text-gray-900">Notifikasi</h1>
            <span v-if="unreadCount > 0" class="bg-[#8B4513] text-white text-[10px] font-bold px-2 py-0.5 rounded-full">
              {{ unreadCount }} belum dibaca
            </span>
          </div>
          <button
            v-if="unreadCount > 0"
            @click="markAllAsRead"
            class="text-[11px] font-medium text-[#8B4513] border border-[#8B4513] px-2.5 py-1 rounded-lg hover:bg-[#FDF6F0] transition-colors"
          >
            Tandai Semua
          </button>
        </div>

        <NotificationFilterTabs v-model="activeFilter" :unread-count="unreadCount" class="mb-4"/>

        <div v-if="filteredNotifications.length > 0" class="space-y-1.5">
          <NotificationItem
            v-for="n in filteredNotifications"
            :key="n.id"
            :notif="n"
            :is-active="selectedNotif?.id === n.id"
            @select="selectNotifMobile"
          />
        </div>
        <div v-else class="flex flex-col items-center justify-center py-16 text-center">
          <div class="w-12 h-12 rounded-full bg-stone-100 flex items-center justify-center mb-3 text-2xl">🔔</div>
          <p class="text-sm font-medium text-gray-400">Tidak ada notifikasi</p>
        </div>
      </div>

      <!-- ===== MOBILE: Detail view ===== -->
      <div v-if="mobileShowDetail" class="lg:hidden">
        <button
          @click="mobileShowDetail = false"
          class="flex items-center gap-1.5 text-xs text-gray-400 hover:text-gray-600 mb-4 transition-colors"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
          </svg>
          Kembali ke Notifikasi
        </button>
        <NotificationDetail :notif="selectedNotif" />
      </div>

      <!-- ===== DESKTOP: 2-column layout ===== -->
      <div class="hidden lg:flex gap-5 h-full">

        <!-- Left column: list -->
        <div class="w-[38%] flex-shrink-0 flex flex-col gap-3">
          <!-- Header -->
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <h1 class="text-xl font-bold text-gray-900">Notifikasi</h1>
              <span v-if="unreadCount > 0" class="bg-[#8B4513] text-white text-[10px] font-bold px-2 py-0.5 rounded-full">
                {{ unreadCount }} belum dibaca
              </span>
            </div>
            <button
              v-if="unreadCount > 0"
              @click="markAllAsRead"
              class="text-[11px] font-medium text-[#8B4513] border border-[#8B4513] px-2.5 py-1 rounded-lg hover:bg-[#FDF6F0] transition-colors"
            >
              Tandai Semua
            </button>
          </div>

          <NotificationFilterTabs v-model="activeFilter" :unread-count="unreadCount" />

          <!-- List -->
          <div class="flex flex-col gap-1 overflow-y-auto max-h-[calc(100vh-220px)] pr-1">
            <template v-if="filteredNotifications.length > 0">
              <NotificationItem
                v-for="n in filteredNotifications"
                :key="n.id"
                :notif="n"
                :is-active="selectedNotif?.id === n.id"
                @select="selectNotif"
              />
            </template>
            <div v-else class="flex flex-col items-center justify-center py-16 text-center">
              <div class="w-12 h-12 rounded-full bg-stone-100 flex items-center justify-center mb-3 text-2xl">🔔</div>
              <p class="text-sm font-medium text-gray-400">Tidak ada notifikasi</p>
            </div>
          </div>
        </div>

        <!-- Right column: detail -->
        <div class="flex-1 min-w-0">
          <NotificationDetail :notif="selectedNotif" />
        </div>
      </div>

    </template>
    </main>

    <!-- ===== FOOTER ===== -->
    <footer class="border-t border-stone-200 bg-[#F5F0E8] px-6 py-6 mt-4">
      <div class="max-w-5xl mx-auto flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
          <p class="font-bold text-[#1a2744] text-sm mb-0.5">Berbagive</p>
          <p class="text-[10px] text-gray-400">© 2024 Berbagive. Part of The Human Archive project.</p>
        </div>
        <div class="flex flex-wrap items-center gap-5 text-xs text-gray-500">
          <a href="#" class="hover:text-gray-700">Kebijakan Privasi</a>
          <a href="#" class="hover:text-gray-700">Syarat &amp; Ketentuan</a>
          <a href="#" class="hover:text-gray-700">Hubungi Kami</a>
          <a href="#" class="hover:text-gray-700">FAQ</a>
        </div>
      </div>
    </footer>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/api/axios'
import NotificationItem       from '@/components/notification/NotificationItem.vue'
import NotificationDetail     from '@/components/notification/NotificationDetail.vue'
import NotificationFilterTabs from '@/components/notification/NotificationFilterTabs.vue'

// --- State ---
const activeFilter      = ref('semua')
const selectedNotif     = ref(null)
const mobileShowDetail  = ref(false)

const notifications = ref([])
const loading = ref(true)

async function fetchNotifications() {
  try {
    const res = await api.get('/notifications')
    notifications.value = res.data.data.data || []
  } catch (e) {
    notifications.value = []
  } finally {
    loading.value = false
  }
}

onMounted(fetchNotifications)

// --- Computed ---
const unreadCount = computed(() => notifications.value.filter(n => !n.is_read).length)

const categoryMap = {
  transaksi: ['transaksi_berhasil', 'transaksi_gagal'],
  campaign:  ['campaign_baru', 'update_campaign', 'campaign_hampir_selesai'],
  komunitas: ['follow_komunitas']
}

const filteredNotifications = computed(() =>
  notifications.value.filter(n => {
    if (activeFilter.value === 'semua')        return true
    if (activeFilter.value === 'belum_dibaca') return !n.is_read
    return categoryMap[activeFilter.value]?.includes(n.type)
  })
)

// --- Actions ---
async function markAsRead(id) {
  try {
    await api.patch(`/notifications/${id}/read`)
    const n = notifications.value.find(n => n.id === id)
    if (n) n.is_read = true
  } catch (e) {
    // silent
  }
}

async function markAllAsRead() {
  try {
    await api.patch('/notifications/read-all')
    notifications.value.forEach(n => n.is_read = true)
  } catch (e) {
    // silent
  }
}

function selectNotif(notif) {
  selectedNotif.value = notif
  if (!notif.is_read) markAsRead(notif.id)
}

function selectNotifMobile(notif) {
  selectNotif(notif)
  mobileShowDetail.value = true
}
</script>
