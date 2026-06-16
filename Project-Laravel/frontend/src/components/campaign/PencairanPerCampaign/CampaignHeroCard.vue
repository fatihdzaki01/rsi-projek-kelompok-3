<template>
  <div class="bg-[#F5F0E8] rounded-lg p-4">
    <p class="text-xs text-gray-500 mb-1">{{ campaign.judul }}</p>
    <div class="flex items-center justify-between text-sm">
      <span class="text-gray-500">Dana Terkumpul</span>
      <span class="font-semibold text-[#2C2C2C]">{{ rupiah(campaign.dana_terkumpul) }}</span>
    </div>
    <div class="flex items-center justify-between text-sm mt-1">
      <span class="text-gray-500">Saldo Tersedia</span>
      <span class="font-semibold text-green-600">{{ rupiah(campaign.saldo_tersedia) }}</span>
    </div>
    <div class="flex items-center justify-between text-sm mt-1">
      <span class="text-gray-500">Sisa Kesempatan</span>
      <span class="font-semibold text-blue-600">{{ campaign.sisa_kesempatan ?? '-' }} / 5</span>
    </div>
  </div>
</template>

<script setup>
defineProps({
  campaign: { type: Object, required: true },
})

function rupiah(val) {
  return 'Rp ' + (Number(val) || 0).toLocaleString('id-ID')
}
</script>
