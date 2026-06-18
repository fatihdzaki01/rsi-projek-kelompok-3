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
              <h3 class="text-base font-bold text-gray-900">Komunitas Yang Diikuti</h3>
              <p class="text-xs text-gray-500 mt-0.5">{{ items.length }} komunitas</p>
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
          <div v-else-if="items.length === 0" class="flex-1 flex flex-col items-center justify-center py-12 text-center px-6">
            <svg class="w-10 h-10 text-gray-300 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72"/>
            </svg>
            <p class="text-sm text-gray-400">Belum mengikuti komunitas manapun.</p>
          </div>

          <!-- List -->
          <div v-else class="flex-1 overflow-y-auto px-5 py-3 space-y-1">
            <div
              v-for="item in items"
              :key="item.id_follow"
              class="flex items-center gap-3 py-2.5 px-2 rounded-xl hover:bg-[#FDF5EE] transition-colors cursor-pointer"
              @click="goToCommunity(item.id_komunitas)"
            >
              <div class="w-10 h-10 rounded-full bg-[#8B4513] flex items-center justify-center text-white text-sm font-bold shrink-0 overflow-hidden">
                <img v-if="item.foto_lembaga_url" :src="item.foto_lembaga_url" alt="" class="w-full h-full object-cover" />
                <span v-else>{{ (item.nama_lembaga || 'K').charAt(0).toUpperCase() }}</span>
              </div>
              <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-gray-900 truncate">{{ item.nama_lembaga }}</p>
                <p class="text-xs text-gray-400">Sejak {{ formatDate(item.followed_at) }}</p>
              </div>
              <svg class="w-4 h-4 text-gray-300 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
              </svg>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api/axios'

const router = useRouter()

const props = defineProps({
  show: { type: Boolean, default: false },
})

const emit = defineEmits(['close'])

const items = ref([])
const loading = ref(false)

watch(() => props.show, async (val) => {
  if (val) {
    loading.value = true
    try {
      const res = await api.get('/users/me/following')
      items.value = res.data.data || []
    } catch {
      items.value = []
    } finally {
      loading.value = false
    }
  }
})

function goToCommunity(id) {
  router.push(`/communities/${id}`)
  emit('close')
}

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
