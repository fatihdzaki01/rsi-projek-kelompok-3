<template>
  <div
    @click="$emit('click', campaign)"
    class="flex items-center gap-3 px-4 py-3 hover:bg-[#F5F0E8] cursor-pointer transition-colors"
  >
    <div class="w-9 h-9 rounded-lg bg-[#F5F0E8] flex items-center justify-center flex-shrink-0">
      <svg class="w-4 h-4 text-[#8B4513]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
      </svg>
    </div>
    <div class="flex-1 min-w-0">
      <p class="text-sm font-medium text-[#2C2C2C] truncate">{{ campaign.judul }}</p>
      <p class="text-xs text-gray-400">
        {{ rupiah(campaign.dana_terkumpul) }} / {{ rupiah(campaign.target_dana) }}
      </p>
    </div>
    <div class="text-right flex-shrink-0">
      <p class="text-xs font-semibold" :class="statusColor">{{ statusLabel }}</p>
      <p class="text-[10px] text-gray-400">{{ campaign.pencairan_count || 0 }} pencairan</p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  campaign: { type: Object, required: true },
})

defineEmits(['click'])

const statusColor = computed(() => {
  const map = {
    aktif: 'text-green-600',
    selesai: 'text-blue-600',
    menunggu_review: 'text-yellow-600',
    ditolak: 'text-red-600',
    nonaktif: 'text-gray-400',
    ditutup_permanen: 'text-red-700',
  }
  return map[props.campaign.status] || 'text-gray-500'
})

const statusLabel = computed(() => {
  const map = {
    aktif: 'Aktif',
    selesai: 'Selesai',
    menunggu_review: 'Menunggu',
    ditolak: 'Ditolak',
    nonaktif: 'Nonaktif',
    ditutup_permanen: 'Ditutup',
  }
  return map[props.campaign.status] || props.campaign.status
})

function rupiah(val) {
  return 'Rp ' + (Number(val) || 0).toLocaleString('id-ID')
}
</script>
