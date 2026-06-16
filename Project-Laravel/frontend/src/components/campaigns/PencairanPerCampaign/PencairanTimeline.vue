<template>
  <div v-if="items.length === 0" class="text-center py-10 text-sm text-gray-400">
    Belum ada riwayat pencairan untuk campaign ini
  </div>
  <div v-else class="space-y-3">
    <div v-for="item in items" :key="item.id_pencairan" class="flex items-start gap-3">
      <div class="flex flex-col items-center">
        <div :class="['w-2.5 h-2.5 rounded-full mt-1.5', dotColor(item.status)]" />
        <div v-if="item !== items[items.length - 1]" class="w-px h-full min-h-[2rem] bg-gray-200" />
      </div>
      <div class="flex-1 pb-3">
        <div class="flex items-center justify-between">
          <p class="text-sm font-medium text-[#2C2C2C]">Pencairan #{{ item.urutan_ke }}</p>
          <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', badgeClass(item.status)]">{{ badgeLabel(item.status) }}</span>
        </div>
        <p class="text-xs text-gray-500 mt-0.5">Diajukan: {{ item.nominal_diajukan ? rupiah(item.nominal_diajukan) : '-' }}</p>
        <p v-if="item.nominal_disetujui" class="text-xs text-gray-500">Disetujui: {{ rupiah(item.nominal_disetujui) }}</p>
        <p class="text-xs text-gray-400 mt-0.5">{{ formatDate(item.tanggal_pengajuan) }}</p>
        <p v-if="item.alasan_penolakan" class="text-xs text-red-500 mt-1">Alasan ditolak: {{ item.alasan_penolakan }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  items: { type: Array, default: () => [] },
})

function dotColor(status) {
  const map = { menunggu_review: 'bg-yellow-400', disetujui: 'bg-blue-400', ditolak: 'bg-red-400', selesai: 'bg-green-400' }
  return map[status] || 'bg-gray-300'
}

function badgeClass(status) {
  const map = { menunggu_review: 'text-yellow-700 bg-yellow-50', disetujui: 'text-blue-700 bg-blue-50', ditolak: 'text-red-700 bg-red-50', selesai: 'text-green-700 bg-green-50' }
  return map[status] || 'text-gray-500 bg-gray-100'
}

function badgeLabel(status) {
  const map = { menunggu_review: 'Menunggu', disetujui: 'Disetujui', ditolak: 'Ditolak', selesai: 'Selesai' }
  return map[status] || status
}

function rupiah(val) {
  return 'Rp ' + (Number(val) || 0).toLocaleString('id-ID')
}

function formatDate(date) {
  if (!date) return ''
  return new Date(date).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' })
}
</script>
