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
              <h3 class="text-base font-bold text-gray-900">{{ title }}</h3>
              <p class="text-xs text-gray-500 mt-0.5">{{ campaigns.length }} campaign</p>
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

          <!-- Empty -->
          <div v-if="campaigns.length === 0" class="flex-1 flex flex-col items-center justify-center py-12 text-center px-6">
            <svg class="w-10 h-10 text-gray-300 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <p class="text-sm text-gray-400">{{ emptyMessage }}</p>
          </div>

          <!-- List -->
          <div v-else class="flex-1 overflow-y-auto px-5 py-3 space-y-1">
            <div
              v-for="c in campaigns"
              :key="c.id_campaign"
              class="flex items-center gap-3 py-2.5 px-2 rounded-xl hover:bg-[#FDF5EE] transition-colors cursor-pointer"
              @click="goToCampaign(c.id_campaign)"
            >
              <div class="w-10 h-10 rounded-lg bg-[#8B4513] flex items-center justify-center text-white text-sm font-bold shrink-0">
                {{ (c.judul || 'C').charAt(0).toUpperCase() }}
              </div>
              <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-gray-900 truncate leading-snug">{{ c.judul }}</p>
                <p class="text-xs text-gray-400 mt-0.5">
                  {{ formatRupiah(c.dana_terkumpul) }} dari {{ formatRupiah(c.target_dana) }}
                </p>
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
import { useRouter } from 'vue-router'

const router = useRouter()

const props = defineProps({
  show: { type: Boolean, default: false },
  title: { type: String, default: 'Campaign' },
  emptyMessage: { type: String, default: 'Tidak ada campaign.' },
  campaigns: { type: Array, default: () => [] },
})

const emit = defineEmits(['close'])

function goToCampaign(id) {
  router.push(`/campaigns/${id}`)
  emit('close')
}

function formatRupiah(n) {
  n = n || 0
  if (n >= 1000000000) return 'Rp ' + (n / 1000000000).toFixed(1) + 'M'
  if (n >= 1000000) return 'Rp ' + (n / 1000000).toFixed(1) + 'Jt'
  return 'Rp ' + n.toLocaleString('id-ID')
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
