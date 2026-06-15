<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { BellOff, Bell, Mail, CreditCard, Megaphone } from 'lucide-vue-next'

const router = useRouter()

const activeTab = ref('semua')
const selectedNotif = ref(null)

const notifications = ref([
  {
    id: 1,
    type: 'transaksi',
    title: 'Donasi Berhasil',
    preview: 'Donasi kamu sebesar Rp 50.000 telah berhasil.',
    message: 'Donasi kamu sebesar Rp 50.000 untuk campaign "Bantu Korban Banjir" telah berhasil diproses. Terima kasih atas kontribusimu!',
    time: '2 menit lalu',
    read: false
  },
  {
    id: 2,
    type: 'campaign',
    title: 'Campaign Mencapai Target',
    preview: 'Campaign yang kamu ikuti telah mencapai 100% target.',
    message: 'Campaign "Beasiswa Anak Yatim" yang kamu dukung telah mencapai 100% dari target dana. Dana akan segera dicairkan.',
    time: '1 jam lalu',
    read: false
  },
  {
    id: 3,
    type: 'umum',
    title: 'Selamat Datang di Berbagive',
    preview: 'Akun kamu telah berhasil dibuat.',
    message: 'Selamat datang di Berbagive! Akun kamu telah berhasil dibuat. Mulai jelajahi campaign dan bergabung dengan komunitas kebaikan.',
    time: '1 hari lalu',
    read: true
  }
])

const filteredNotifications = computed(() => {
  if (activeTab.value === 'semua') return notifications.value
  if (activeTab.value === 'belum-dibaca') return notifications.value.filter(n => !n.read)
  return notifications.value.filter(n => n.type === activeTab.value)
})

function selectNotification(item) {
  selectedNotif.value = item
  item.read = true
}

function getIcon(type) {
  switch (type) {
    case 'transaksi': return CreditCard
    case 'campaign': return Megaphone
    case 'umum': return Bell
    default: return Bell
  }
}

function getIconColor(type) {
  return '#8B4513'
}
</script>

<template>
  <div class="min-h-screen bg-[#F5F0E8] flex flex-col">
    <main class="flex-1 flex flex-col px-8 py-10 max-w-4xl mx-auto w-full">
      <!-- Header & Tab Filter -->
      <div class="mb-6">
        <div class="flex items-center gap-3 mb-4">
          <button
            @click="() => router.push('/')"
            class="w-8 h-8 rounded-full bg-[#F5F0E8] flex items-center justify-center hover:bg-[#E8DDD0] transition-colors"
            aria-label="Kembali"
          >
            <svg class="w-4 h-4 text-[#8B4513]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
          </button>
          <h1 class="text-2xl font-bold text-[#1a2744]">Notifikasi</h1>
        </div>
        
        <div class="flex gap-2 overflow-x-auto scrollbar-hide">
          <button
            v-for="tab in ['semua', 'belum-dibaca', 'transaksi', 'campaign', 'komunitas']"
            :key="tab"
            @click="activeTab = tab"
            class="px-4 py-1.5 text-sm rounded-full transition-colors whitespace-nowrap"
            :class="activeTab === tab
              ? 'bg-[#8B4513] text-white font-medium'
              : 'text-[#6B7280] hover:bg-[#E8DDD0]'
            "
          >
            {{ tab === 'semua' ? 'Semua' : tab === 'belum-dibaca' ? 'Belum Dibaca' : tab.charAt(0).toUpperCase() + tab.slice(1) }}
          </button>
        </div>
      </div>

      <!-- Main Content Area -->
      <div class="flex gap-6 flex-1">
        <!-- LEFT — Notification List -->
        <div class="w-2/5 flex flex-col">
          <div v-if="filteredNotifications.length === 0" class="flex flex-col items-center justify-center py-20">
            <BellOff class="w-14 h-14 text-[#D1D5DB]" stroke-width="1.5" />
            <p class="text-base text-[#9CA3AF] mt-3 text-center">Tidak ada notifikasi</p>
          </div>
          <div
            v-for="item in filteredNotifications"
            :key="item.id"
            @click="selectNotification(item)"
            class="bg-white rounded-xl p-4 mb-3 cursor-pointer border border-transparent transition-all"
            :class="selectedNotif?.id === item.id
              ? 'border-[#8B4513] bg-[#FDF0E8]'
              : 'hover:border-[#8B4513]/20 hover:shadow-sm'
            "
          >
            <div class="flex items-start gap-3 relative">
              <div class="w-10 h-10 bg-[#F5E6D3] rounded-full flex items-center justify-center flex-shrink-0">
                <component :is="getIcon(item.type)" class="w-4 h-4" :style="{ color: getIconColor(item.type) }" stroke-width="2" />
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-[#1a2744] truncate">{{ item.title }}</p>
                <p class="text-xs text-[#6B7280] truncate mt-0.5">{{ item.preview }}</p>
                <p class="text-xs text-[#9CA3AF] mt-1">{{ item.time }}</p>
              </div>
              <div v-if="!item.read" class="w-2 h-2 bg-blue-500 rounded-full absolute top-2 right-2" />
            </div>
          </div>
        </div>

        <!-- RIGHT — Notification Detail -->
        <div class="w-3/5 flex flex-col">
          <div v-if="!selectedNotif" class="flex flex-col items-center justify-center py-20 text-center">
            <Mail class="w-16 h-16 text-[#D1D5DB]" stroke-width="1.5" />
            <p class="text-lg font-medium text-[#6B7280] mt-4">Pilih notifikasi untuk melihat detail</p>
            <p class="text-sm text-[#9CA3AF] mt-1">Notifikasi kamu akan tampil di sini</p>
          </div>
          
          <div v-else class="bg-white rounded-xl p-6">
            <div class="flex items-center gap-4 mb-4">
              <div class="w-14 h-14 bg-[#F5E6D3] rounded-full flex items-center justify-center">
                <component :is="getIcon(selectedNotif.type)" class="w-6 h-6" :style="{ color: getIconColor(selectedNotif.type) }" stroke-width="2" />
              </div>
              <div class="flex-1">
                <h2 class="text-xl font-bold text-[#1a2744] mb-1">{{ selectedNotif.title }}</h2>
                <p class="text-xs text-[#9CA3AF]">{{ selectedNotif.time }}</p>
              </div>
            </div>
            
            <div class="border-t border-gray-100 mb-4" />
            
            <p class="text-sm text-[#374151] leading-relaxed">{{ selectedNotif.message }}</p>
            
            <button
              v-if="selectedNotif.type === 'transaksi'"
              class="mt-6 w-full py-2.5 rounded-lg border border-[#1a2744] text-[#1a2744] text-sm font-medium hover:bg-[#1a2744] hover:text-white transition-colors"
            >
              Lihat Detail Transaksi
            </button>
          </div>
        </div>
      </div>
    </main>
    
    <Footer />
  </div>
</template>
