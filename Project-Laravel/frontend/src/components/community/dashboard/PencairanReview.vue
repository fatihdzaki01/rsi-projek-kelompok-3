<template>
  <div class="bg-white rounded-xl shadow-sm border border-stone-100 p-5">
    <h2 class="text-sm font-bold text-[#2C2C2C] mb-4">Pengajuan Pencairan Terbaru</h2>
    <div class="space-y-3">
      <div v-for="item in items" :key="item.id" class="flex justify-between items-center p-3 rounded-lg bg-[#F9F8F6] border border-stone-100">
        <div>
          <span class="text-xs font-semibold text-[#8B4513]">{{ item.id }}</span>
          <p class="text-sm font-bold text-gray-800">{{ item.campaign }}</p>
        </div>
        <div class="text-right">
          <p class="text-sm font-extrabold text-[#2C2C2C]">{{ formatRupiah(item.nominal) }}</p>
          <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-yellow-100 text-yellow-800">
            {{ item.status }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  items: Array
})

const formatRupiah = (val) => {
  return 'Rp ' + (Number(val) || 0).toLocaleString('id-ID')
}
</script>
