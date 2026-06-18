<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center px-4"
        @click.self="$emit('close')"
      >
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" />

        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm z-10 max-h-[80vh] flex flex-col">
          <div class="flex items-center justify-between p-5 border-b border-gray-100">
            <div>
              <h3 class="text-base font-bold text-gray-900">Pengikut</h3>
              <p class="text-xs text-gray-500 mt-0.5">{{ totalFollowers }} orang</p>
            </div>
            <button
              @click="$emit('close')"
              class="p-1 text-gray-400 hover:text-gray-600 transition-colors"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <!-- Loading -->
          <div v-if="loading" class="flex-1 flex items-center justify-center py-12">
            <div class="w-6 h-6 border-2 border-[#8B4513] border-t-transparent rounded-full animate-spin" />
          </div>

          <!-- Empty -->
          <div v-else-if="followers.length === 0" class="flex-1 flex flex-col items-center justify-center py-12 text-center">
            <svg class="w-10 h-10 text-gray-300 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
            </svg>
            <p class="text-sm text-gray-400">Belum ada pengikut</p>
          </div>

          <!-- List -->
          <div v-else class="flex-1 overflow-y-auto px-5 py-3 space-y-2">
            <div
              v-for="f in followers"
              :key="f.id_user"
              class="flex items-center gap-3 py-2"
            >
              <div class="w-9 h-9 rounded-full bg-[#FDF5EE] flex items-center justify-center text-sm font-bold text-[#8B4513] shrink-0 overflow-hidden">
                <img v-if="f.foto_profil_url" :src="f.foto_profil_url" alt="" class="w-full h-full object-cover" />
                <span v-else>{{ (f.nama_lengkap || f.username || '?').charAt(0).toUpperCase() }}</span>
              </div>
              <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-gray-900 truncate">{{ f.nama_lengkap || f.username }}</p>
              </div>
            </div>
          </div>

          <div v-if="totalFollowers > 25" class="px-5 py-3 border-t border-gray-100 text-center">
            <p class="text-xs text-gray-400">Menampilkan 25 pengikut terbaru</p>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, watch } from 'vue'
import api from '@/api/axios'

const props = defineProps({
  show: { type: Boolean, default: false },
  communityId: { type: Number, required: true },
})

const emit = defineEmits(['close'])

const followers = ref([])
const totalFollowers = ref(0)
const loading = ref(false)

watch(() => props.show, async (val) => {
  if (val) {
    loading.value = true
    try {
      const res = await api.get(`/communities/${props.communityId}/followers`)
      followers.value = res.data.data.followers || []
      totalFollowers.value = res.data.data.total_followers || 0
    } catch {
      followers.value = []
    } finally {
      loading.value = false
    }
  }
})

function formatDate(s) {
  if (!s) return ''
  return new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }).format(new Date(s))
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.2s ease;
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>
