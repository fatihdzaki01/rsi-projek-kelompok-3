<template>
  <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">

    <!-- Top section: cover strip -->
    <div class="h-2 bg-gradient-to-r from-[#8B4513] via-[#a85c2c] to-[#c4783c]" />

    <div class="px-6 pt-5 pb-6">
      <!-- Profile row -->
      <div class="flex items-start justify-between gap-4">
        <!-- Left: avatar + info -->
        <div class="flex items-start gap-4">
          <!-- Avatar -->
          <div class="relative flex-shrink-0">
            <div
              class="w-16 h-16 rounded-full bg-gradient-to-br from-[#8B4513] to-[#c4783c] flex items-center justify-center text-white text-xl font-bold shadow-md"
            >
              {{ community.nama_komunitas.charAt(0) }}
            </div>
            <span class="absolute -bottom-0.5 -right-0.5 w-4 h-4 bg-emerald-400 rounded-full border-2 border-white" />
          </div>

          <!-- Info -->
          <div class="min-w-0">
            <h1 class="text-lg font-bold text-gray-900 leading-tight">{{ community.nama_komunitas }}</h1>

            <div class="flex flex-col gap-1 mt-1.5">
              <div class="flex items-center gap-1.5 text-xs text-gray-500">
                <svg class="w-3 h-3 flex-shrink-0 text-[#8B4513]" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                </svg>
                <span class="truncate">{{ community.alamat_lengkap }}</span>
              </div>
              <div class="flex items-center gap-1.5 text-xs text-gray-500">
                <svg class="w-3 h-3 flex-shrink-0 text-[#8B4513]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                </svg>
                <span>Bergabung {{ formatYear(community.tanggal_bergabung) }}</span>
              </div>
            </div>

            <p class="text-xs text-gray-500 mt-2.5 leading-relaxed max-w-sm">
              {{ community.deskripsi_komunitas }}
            </p>
          </div>
        </div>

        <!-- Right: Follow button -->
        <div class="flex-shrink-0">
          <button
            v-if="!isFollowing"
            @click="$emit('follow')"
            class="flex items-center gap-1.5 px-4 py-2 bg-[#1a2744] text-white text-xs font-semibold rounded-xl hover:bg-[#22325a] transition-colors shadow-sm"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Ikuti
          </button>
          <button
            v-else
            @click="$emit('unfollow-click')"
            class="flex items-center gap-1.5 px-4 py-2 border border-gray-200 text-gray-600 text-xs font-semibold rounded-xl hover:bg-gray-50 transition-colors"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            Mengikuti
          </button>
        </div>
      </div>

      <!-- Divider -->
      <div class="my-5 border-t border-stone-100" />

      <!-- Stats row -->
      <div class="grid grid-cols-4 gap-2">
        <div class="text-center">
          <p class="text-lg font-bold text-gray-900">{{ formatNumber(community.total_follower) }}</p>
          <p class="text-[10px] uppercase tracking-wide text-gray-400 mt-0.5">Follower</p>
        </div>
        <div class="text-center border-l border-stone-100">
          <p class="text-lg font-bold text-[#8B4513]">{{ formatMillion(community.total_dana_diterima) }}</p>
          <p class="text-[10px] uppercase tracking-wide text-gray-400 mt-0.5">Total Dana</p>
        </div>
        <div class="text-center border-l border-stone-100">
          <p class="text-lg font-bold text-gray-900">{{ community.total_campaign_aktif }}</p>
          <p class="text-[10px] uppercase tracking-wide text-gray-400 mt-0.5">Campaign Aktif</p>
        </div>
        <div class="text-center border-l border-stone-100">
          <p class="text-lg font-bold text-gray-900">{{ community.total_campaign_selesai }}</p>
          <p class="text-[10px] uppercase tracking-wide text-gray-400 mt-0.5">Selesai</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  community: {
    type: Object,
    required: true
  },
  isFollowing: {
    type: Boolean,
    default: false
  }
})

defineEmits(['follow', 'unfollow-click'])

function formatNumber(n) {
  if (n >= 1000) return (n / 1000).toFixed(1).replace('.0', '') + 'K'
  return n.toLocaleString('id-ID')
}

function formatMillion(n) {
  if (n >= 1_000_000_000) return 'Rp ' + (n / 1_000_000_000).toFixed(1) + 'M'
  if (n >= 1_000_000) return 'Rp ' + (n / 1_000_000).toFixed(1) + 'M'
  return 'Rp ' + n.toLocaleString('id-ID')
}

function formatYear(dateStr) {
  return new Date(dateStr).getFullYear()
}
</script>
