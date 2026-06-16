<template>
  <div class="bg-white rounded-xl shadow-sm border border-stone-100 p-5 text-center">
    <h2 class="text-sm font-bold text-[#2C2C2C] mb-6">Target Bulan Ini</h2>
    <div class="relative inline-flex items-center justify-center mb-4">
      <svg class="w-32 h-32 transform -rotate-90">
        <circle cx="64" cy="64" r="50" stroke="#f5f0eb" stroke-width="10" fill="transparent" />
        <circle cx="64" cy="64" r="50" stroke="#8B4513" stroke-width="10" fill="transparent"
          stroke-dasharray="314" :stroke-dashoffset="314 - (314 * percent) / 100" stroke-linecap="round" />
      </svg>
      <div class="absolute text-center">
        <span class="text-2xl font-extrabold text-[#2C2C2C]">{{ percent }}%</span>
        <p class="text-[9px] text-gray-400 font-bold uppercase tracking-wider">Tercapai</p>
      </div>
    </div>
    <div class="space-y-1">
      <p class="text-xs text-gray-400">Pencapaian saat ini:</p>
      <p class="text-base font-bold text-[#8B4513]">{{ formatRupiah(current) }}</p>
      <p class="text-[10px] text-gray-400">dari target {{ formatRupiah(max) }}</p>
    </div>
  </div>
</template>

<script setup>
defineProps({
  current: Number,
  max: Number,
  percent: Number
})

const formatRupiah = (val) => {
  return 'Rp ' + (Number(val) || 0).toLocaleString('id-ID')
}
</script>
