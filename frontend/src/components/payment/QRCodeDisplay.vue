<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  timeLeft: {
    type: Number,
    required: true
  }
})

const formattedTime = computed(() => {
  const m = Math.floor(props.timeLeft / 60).toString().padStart(2, '0')
  const s = (props.timeLeft % 60).toString().padStart(2, '0')
  return `${m}:${s}`
})
</script>

<template>
  <div>
    <!-- Countdown -->
    <div class="flex items-center justify-center gap-1.5 mb-4">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-rose-500" viewBox="0 0 24 24"
        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"/>
        <polyline points="12 6 12 12 16 14"/>
      </svg>
      <span class="text-xs font-medium text-rose-500">
        Berakhir dalam: {{ formattedTime }}
      </span>
    </div>

    <!-- QR Code Container -->
    <div class="rounded-xl p-5 flex flex-col items-center" style="background-color: #6B3A2A;">
      <!-- QR Pattern SVG Placeholder -->
      <div class="bg-white rounded-lg p-3">
        <svg width="140" height="140" viewBox="0 0 140 140" xmlns="http://www.w3.org/2000/svg">
          <!-- Top-left finder -->
          <rect x="8" y="8" width="38" height="38" rx="4" fill="#4a2010"/>
          <rect x="14" y="14" width="26" height="26" rx="2" fill="white"/>
          <rect x="19" y="19" width="16" height="16" rx="1" fill="#4a2010"/>
          <!-- Top-right finder -->
          <rect x="94" y="8" width="38" height="38" rx="4" fill="#4a2010"/>
          <rect x="100" y="14" width="26" height="26" rx="2" fill="white"/>
          <rect x="105" y="19" width="16" height="16" rx="1" fill="#4a2010"/>
          <!-- Bottom-left finder -->
          <rect x="8" y="94" width="38" height="38" rx="4" fill="#4a2010"/>
          <rect x="14" y="100" width="26" height="26" rx="2" fill="white"/>
          <rect x="19" y="105" width="16" height="16" rx="1" fill="#4a2010"/>
          <!-- Data modules (random-ish pattern) -->
          <rect x="56" y="8" width="6" height="6" fill="#4a2010"/>
          <rect x="68" y="8" width="6" height="6" fill="#4a2010"/>
          <rect x="80" y="8" width="6" height="6" fill="#4a2010"/>
          <rect x="56" y="20" width="6" height="6" fill="#4a2010"/>
          <rect x="74" y="20" width="6" height="6" fill="#4a2010"/>
          <rect x="62" y="32" width="6" height="6" fill="#4a2010"/>
          <rect x="80" y="32" width="6" height="6" fill="#4a2010"/>
          <rect x="8" y="56" width="6" height="6" fill="#4a2010"/>
          <rect x="20" y="56" width="6" height="6" fill="#4a2010"/>
          <rect x="32" y="56" width="6" height="6" fill="#4a2010"/>
          <rect x="44" y="56" width="6" height="6" fill="#4a2010"/>
          <rect x="56" y="56" width="6" height="6" fill="#4a2010"/>
          <rect x="68" y="56" width="6" height="6" fill="#4a2010"/>
          <rect x="80" y="56" width="6" height="6" fill="#4a2010"/>
          <rect x="92" y="56" width="6" height="6" fill="#4a2010"/>
          <rect x="104" y="56" width="6" height="6" fill="#4a2010"/>
          <rect x="116" y="56" width="6" height="6" fill="#4a2010"/>
          <rect x="126" y="56" width="6" height="6" fill="#4a2010"/>
          <rect x="14" y="68" width="6" height="6" fill="#4a2010"/>
          <rect x="38" y="68" width="6" height="6" fill="#4a2010"/>
          <rect x="56" y="68" width="6" height="6" fill="#4a2010"/>
          <rect x="74" y="68" width="6" height="6" fill="#4a2010"/>
          <rect x="86" y="68" width="6" height="6" fill="#4a2010"/>
          <rect x="98" y="68" width="6" height="6" fill="#4a2010"/>
          <rect x="116" y="68" width="6" height="6" fill="#4a2010"/>
          <rect x="8" y="80" width="6" height="6" fill="#4a2010"/>
          <rect x="26" y="80" width="6" height="6" fill="#4a2010"/>
          <rect x="44" y="80" width="6" height="6" fill="#4a2010"/>
          <rect x="62" y="80" width="6" height="6" fill="#4a2010"/>
          <rect x="80" y="80" width="6" height="6" fill="#4a2010"/>
          <rect x="104" y="80" width="6" height="6" fill="#4a2010"/>
          <rect x="126" y="80" width="6" height="6" fill="#4a2010"/>
          <rect x="56" y="94" width="6" height="6" fill="#4a2010"/>
          <rect x="68" y="94" width="6" height="6" fill="#4a2010"/>
          <rect x="86" y="94" width="6" height="6" fill="#4a2010"/>
          <rect x="104" y="94" width="6" height="6" fill="#4a2010"/>
          <rect x="116" y="94" width="6" height="6" fill="#4a2010"/>
          <rect x="62" y="106" width="6" height="6" fill="#4a2010"/>
          <rect x="80" y="106" width="6" height="6" fill="#4a2010"/>
          <rect x="98" y="106" width="6" height="6" fill="#4a2010"/>
          <rect x="126" y="106" width="6" height="6" fill="#4a2010"/>
          <rect x="56" y="118" width="6" height="6" fill="#4a2010"/>
          <rect x="74" y="118" width="6" height="6" fill="#4a2010"/>
          <rect x="92" y="118" width="6" height="6" fill="#4a2010"/>
          <rect x="110" y="118" width="6" height="6" fill="#4a2010"/>
          <rect x="56" y="126" width="6" height="6" fill="#4a2010"/>
          <rect x="80" y="126" width="6" height="6" fill="#4a2010"/>
          <rect x="104" y="126" width="6" height="6" fill="#4a2010"/>
        </svg>
      </div>
    </div>

    <!-- Caption -->
    <p class="text-xs text-center mt-3 leading-relaxed" style="color: #9e8e80;">
      Scan QR code untuk menyelesaikan pembayaran melalui aplikasi e-wallet pilihan Anda.
    </p>
  </div>
</template>