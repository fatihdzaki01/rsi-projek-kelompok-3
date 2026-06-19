<template>
  <div 
    class="flex items-center justify-between p-5 hover:bg-[#FDF5EE] cursor-pointer transition-colors"
    @click="$emit('click', campaign)"
  >
    <div class="min-w-0 flex-1">
      <h3 class="text-sm font-bold text-[#1a2744] truncate">{{ campaign.judul }}</h3>
      <p class="text-xs text-gray-500 mt-1">
        Saldo Tersedia: <span class="font-medium text-[#8B4513]">Rp {{ Number(campaign.saldo_tersedia).toLocaleString('id-ID') }}</span>
      </p>
    </div>
    <div class="ml-4 shrink-0">
      <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
      </svg>
    </div>
  </div>
</template>

<script setup>
defineProps({
  campaign: {
    type: Object,
    required: true
  }
})

defineEmits(['click'])
</script>
