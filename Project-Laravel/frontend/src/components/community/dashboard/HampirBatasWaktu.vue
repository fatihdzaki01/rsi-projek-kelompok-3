<template>
  <div class="bg-white rounded-xl shadow-sm border border-stone-100 p-5">
    <h2 class="text-sm font-bold text-[#2C2C2C] mb-4">Campaign Hampir Batas Waktu</h2>
    <div class="space-y-3.5">
      <div v-for="c in campaigns" :key="c.name" class="space-y-2">
        <div class="flex justify-between text-sm">
          <span class="font-medium text-gray-700 truncate max-w-[200px]">{{ c.name }}</span>
          <span class="text-xs font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded">{{ c.deadline }}</span>
        </div>
        <div class="w-full bg-stone-100 h-2 rounded-full overflow-hidden">
          <div class="bg-[#8B4513] h-full rounded-full transition-all duration-500" :style="{ width: c.percent + '%' }"></div>
        </div>
        <div class="flex justify-between text-[11px] text-gray-400">
          <span>{{ formatRupiah(c.collected) }} terkumpul</span>
          <span>Target: {{ formatRupiah(c.target) }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  campaigns: Array
})

const formatRupiah = (val) => {
  return 'Rp ' + (Number(val) || 0).toLocaleString('id-ID')
}
</script>
