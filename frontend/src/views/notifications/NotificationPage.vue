<!-- src/views/notifications/NotificationsPage.vue -->
<script setup>
import { ref, computed, onMounted } from 'vue'
import { BellOff, Bell, Mail, CreditCard, Megaphone } from 'lucide-vue-next'
import Footer from '@/components/shared/Footer.vue'
import { useNotificationStore } from '@/stores/notification'

const store = useNotificationStore()

const tabs = [
  { key: 'all', label: 'Semua' },
  { key: 'unread', label: 'Belum Dibaca' },
]

const activeTab = ref('all')
const selectedNotif = ref(null)

const filteredNotifications = computed(() => {
  if (activeTab.value === 'unread') return store.items.filter((n) => !n.is_read)
  return store.items
})

const iconFor = (type) => {
  if (type === 'transaksi' || type === 'TRANSAKSI') return CreditCard
  if (type === 'campaign' || type === 'CAMPAIGN') return Megaphone
  return Bell
}

const selectNotif = async (item) => {
  selectedNotif.value = item
  if (!item.is_read) {
    await store.markAsRead(item.id)
  }
}

const markAll = async () => {
  await store.markAllAsRead()
}

onMounted(() => {
  store.fetchAll()
})
</script>

<template>
  <div class="min-h-screen bg-[#F5F0E8] flex flex-col">
    <div class="flex-1 flex flex-col px-8 py-10 max-w-4xl mx-auto w-full">
      <!-- Header -->
      <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold text-[#1a2744]">Notifikasi</h1>
        <button
          v-if="store.items.some(n => !n.is_read)"
          @click="markAll"
          class="text-sm text-[#8B4513] hover:underline"
        >
          Tandai semua sudah dibaca
        </button>
      </div>

      <!-- Tabs -->
      <div class="flex gap-2 overflow-x-auto scrollbar-hide mb-6">
        <button
          v-for="tab in tabs"
          :key="tab.key"
          @click="activeTab = tab.key"
          class="whitespace-nowrap px-4 py-1.5 rounded-full text-sm transition-colors"
          :class="
            activeTab === tab.key
              ? 'bg-[#8B4513] text-white font-medium'
              : 'text-[#6B7280] hover:bg-[#E8DDD0]'
          "
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- Loading -->
      <div v-if="store.loading" class="flex flex-col items-center justify-center py-20">
        <div class="w-8 h-8 border-2 border-[#8B4513] border-t-transparent rounded-full animate-spin mb-3" />
        <p class="text-sm text-gray-400">Memuat notifikasi...</p>
      </div>

      <!-- Main split -->
      <div v-else class="flex-1 flex gap-6 min-h-[28rem]">
        <!-- LEFT: list -->
        <div class="w-2/5 flex flex-col">
          <!-- Empty state -->
          <div
            v-if="filteredNotifications.length === 0"
            class="flex-1 flex flex-col items-center justify-center"
          >
            <BellOff :size="56" class="text-gray-300" />
            <p class="text-base text-[#9CA3AF] mt-3 text-center">Tidak ada notifikasi</p>
          </div>

          <!-- List -->
          <div v-else class="space-y-3">
            <button
              v-for="item in filteredNotifications"
              :key="item.id"
              @click="selectNotif(item)"
              class="relative w-full text-left bg-white rounded-xl p-4 cursor-pointer border transition-all"
              :class="
                selectedNotif?.id === item.id
                  ? 'border-[#8B4513] bg-[#FDF0E8]'
                  : 'border-transparent hover:border-[#8B4513]/20 hover:shadow-sm'
              "
            >
              <div class="flex gap-3">
                <div class="shrink-0 w-10 h-10 rounded-full bg-[#F5E6D3] flex items-center justify-center">
                  <component :is="iconFor(item.type)" :size="18" class="text-[#8B4513]" />
                </div>
                <div class="flex flex-col gap-0.5 min-w-0 flex-1">
                  <p class="text-sm font-semibold text-[#1a2744] truncate">{{ item.title }}</p>
                  <p class="text-xs text-[#6B7280] truncate">{{ item.message }}</p>
                  <p class="text-xs text-[#9CA3AF] mt-1">{{ item.created_at ? new Date(item.created_at).toLocaleDateString('id-ID') : '' }}</p>
                </div>
              </div>
              <span
                v-if="!item.is_read"
                class="absolute top-3 right-3 w-2 h-2 rounded-full bg-blue-500"
                aria-label="Belum dibaca"
              />
            </button>
          </div>
        </div>

        <!-- RIGHT: detail -->
        <div class="w-3/5 flex flex-col">
          <!-- Placeholder -->
          <div
            v-if="!selectedNotif"
            class="flex-1 flex flex-col items-center justify-center"
          >
            <Mail :size="64" class="text-gray-300" />
            <p class="text-lg font-medium text-[#6B7280] mt-4 text-center">
              Pilih notifikasi untuk melihat detail
            </p>
            <p class="text-sm text-[#9CA3AF] mt-1 text-center">
              Notifikasi kamu akan tampil di sini
            </p>
          </div>

          <!-- Detail -->
          <div v-else class="bg-white rounded-xl p-6">
            <div class="w-14 h-14 rounded-full bg-[#F5E6D3] flex items-center justify-center mb-4">
              <component :is="iconFor(selectedNotif.type)" :size="24" class="text-[#8B4513]" />
            </div>
            <h2 class="text-xl font-bold text-[#1a2744] mb-1">{{ selectedNotif.title }}</h2>
            <p class="text-xs text-[#9CA3AF] mb-4">{{ selectedNotif.created_at ? new Date(selectedNotif.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '' }}</p>
            <div class="border-t border-gray-100 mb-4" />
            <p class="text-sm text-[#374151] leading-relaxed">{{ selectedNotif.message }}</p>
          </div>
        </div>
      </div>
    </div>

    <Footer />
  </div>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}
.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>