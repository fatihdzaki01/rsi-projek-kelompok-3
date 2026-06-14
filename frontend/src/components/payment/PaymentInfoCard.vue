<template>
  <div class="bg-white rounded-2xl shadow-md p-6 w-full">
    <!-- Transaction ID -->
    <div class="text-center mb-5">
      <p class="text-[10px] uppercase tracking-widest text-gray-400 mb-1">ID Transaksi</p>
      <p class="text-base font-bold text-gray-800">#{{ transactionId }}</p>
    </div>

    <!-- Total Payment Box -->
    <div class="bg-[#F5E6D8] rounded-xl px-4 py-4 text-center mb-4">
      <p class="text-[10px] uppercase tracking-widest text-gray-500 mb-1">Total Pembayaran</p>
      <p class="text-2xl font-bold text-[#1a1a1a]">{{ formattedAmount }}</p>
    </div>

    <!-- Countdown -->
    <div class="flex items-center justify-center gap-1.5 mb-4">
      <svg class="w-3.5 h-3.5 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
      </svg>
      <span class="text-xs text-rose-500 font-medium">Berakhir dalam: {{ formattedTime }}</span>
    </div>

    <!-- Info Box -->
    <div class="bg-[#FDF0E8] rounded-xl px-4 py-3 flex gap-2.5 items-start">
      <svg class="w-4 h-4 text-teal-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
      </svg>
      <p class="text-xs text-gray-500 leading-relaxed">
        Pembayaran akan otomatis terverifikasi setelah selesai. Mohon simpan bukti pembayaran Anda.
      </p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  transactionId: {
    type: String,
    required: true
  },
  totalAmount: {
    type: Number,
    required: true
  },
  formattedTime: {
    type: String,
    required: true
  }
})

const formattedAmount = computed(() => {
  return 'Rp ' + props.totalAmount.toLocaleString('id-ID')
})
</script>
