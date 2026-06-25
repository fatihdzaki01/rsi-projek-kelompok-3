<script setup>
import { ref, onMounted } from 'vue'
import { BellOff, Bell } from 'lucide-vue-next'
import Footer from '@/components/shared/Footer.vue'
import Navbar from '@/components/shared/Navbar.vue'
import PaginationBar from '@/components/ui/PaginationBar.vue'
import { useNotificationStore } from '@/stores/notification'

const store = useNotificationStore()
const selectedNotif = ref(null)
const currentPage = ref(1)
const itemsPerPage = ref(10)

const selectNotif = async (item) => {
  selectedNotif.value = item
  if (!item.is_read) {
    await store.markAsRead(item.id)
  }
}

function goToPage(page) {
  currentPage.value = page
  store.fetchAll({ page, per_page: itemsPerPage.value })
}

function changePerPage(perPage) {
  itemsPerPage.value = perPage
  currentPage.value = 1
  store.fetchAll({ page: 1, per_page: perPage })
}

onMounted(() => {
  store.fetchAll({ page: currentPage.value, per_page: itemsPerPage.value })
})
</script>

<template>
  <div class="min-h-screen bg-[#F5F0E8] flex flex-col">
    <Navbar />
    <div class="flex-1 flex flex-col px-4 py-10 max-w-6xl mx-auto w-full">
      <h1 class="text-2xl font-bold text-[#1a2744] mb-6">Notifikasi</h1>

      <div v-if="store.loading" class="flex flex-col items-center justify-center py-20">
        <div class="w-8 h-8 border-2 border-[#8B4513] border-t-transparent rounded-full animate-spin mb-3" />
        <p class="text-sm text-gray-400">Memuat notifikasi...</p>
      </div>

      <div v-else-if="store.items.length === 0" class="flex flex-col items-center justify-center py-20">
        <BellOff :size="56" class="text-gray-300" />
        <p class="text-base text-[#9CA3AF] mt-3">Tidak ada notifikasi</p>
      </div>

      <template v-else>
        <div class="flex gap-6 min-h-[28rem]">
          <div class="w-1/4 space-y-3 shrink-0">
            <button
              v-for="item in store.items"
              :key="item.id"
              @click="selectNotif(item)"
              class="relative w-full text-left bg-white rounded-xl p-4 cursor-pointer border transition-all"
              :class="selectedNotif?.id === item.id ? 'border-[#8B4513] bg-[#FDF0E8]' : 'border-transparent hover:border-[#8B4513]/20 hover:shadow-sm'"
            >
              <div class="flex gap-3">
                <div class="shrink-0 w-10 h-10 rounded-full bg-[#F5E6D3] flex items-center justify-center">
                  <Bell :size="18" class="text-[#8B4513]" />
                </div>
                <div class="flex flex-col gap-0.5 min-w-0 flex-1">
                  <p class="text-sm font-semibold text-[#1a2744] truncate">{{ item.title }}</p>
                  <p class="text-xs text-[#6B7280] truncate">{{ item.message }}</p>
                  <p class="text-xs text-[#9CA3AF] mt-1">{{ item.created_at ? new Date(item.created_at).toLocaleDateString('id-ID') : '' }}</p>
                </div>
              </div>
              <span v-if="!item.is_read" class="absolute top-3 right-3 w-2 h-2 rounded-full bg-blue-500" />
            </button>
          </div>

          <div class="flex-1">
            <div v-if="!selectedNotif" class="flex flex-col items-center justify-center h-full">
              <Bell :size="64" class="text-gray-300" />
              <p class="text-lg font-medium text-[#6B7280] mt-4 text-center">
                Pilih notifikasi untuk melihat detail
              </p>
              <p class="text-sm text-[#9CA3AF] mt-1 text-center">
                Notifikasi kamu akan tampil di sini
              </p>
            </div>

            <div v-else class="bg-white rounded-xl p-6">
              <div class="w-14 h-14 rounded-full bg-[#F5E6D3] flex items-center justify-center mb-4">
                <Bell :size="24" class="text-[#8B4513]" />
              </div>
              <h2 class="text-xl font-bold text-[#1a2744] mb-1">{{ selectedNotif.title }}</h2>
              <p class="text-xs text-[#9CA3AF] mb-4">{{ selectedNotif.created_at ? new Date(selectedNotif.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '' }}</p>
              <div class="border-t border-gray-100 mb-4" />
              <p class="text-sm text-[#374151] leading-relaxed">{{ selectedNotif.message }}</p>
            </div>
          </div>
        </div>

        <PaginationBar
          v-if="store.pagination.last_page > 1"
          :currentPage="currentPage"
          :totalPages="store.pagination.last_page"
          :perPage="itemsPerPage"
          :total="store.pagination.total"
          @update:currentPage="goToPage"
          @update:perPage="changePerPage"
        />
      </template>
    </div>
    <Footer />
  </div>
</template>
