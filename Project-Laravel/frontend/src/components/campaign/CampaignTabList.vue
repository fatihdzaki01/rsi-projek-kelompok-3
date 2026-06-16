<template>
  <div>
    <!-- Tab switcher -->
    <div class="flex items-center gap-1 bg-stone-100 p-1 rounded-xl w-fit mb-5">
      <button
        @click="$emit('update:activeTab', 'aktif')"
        :class="[
          'px-4 py-1.5 text-xs font-semibold rounded-lg transition-all duration-150',
          activeTab === 'aktif'
            ? 'bg-white text-[#1a2744] shadow-sm'
            : 'text-gray-500 hover:text-gray-700'
        ]"
      >
        Campaign Aktif
        <span
          :class="[
            'ml-1.5 text-[10px] font-bold px-1.5 py-0.5 rounded-full',
            activeTab === 'aktif' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-500'
          ]"
        >{{ campaigns.aktif.length }}</span>
      </button>
      <button
        @click="$emit('update:activeTab', 'selesai')"
        :class="[
          'px-4 py-1.5 text-xs font-semibold rounded-lg transition-all duration-150',
          activeTab === 'selesai'
            ? 'bg-white text-[#1a2744] shadow-sm'
            : 'text-gray-500 hover:text-gray-700'
        ]"
      >
        Campaign Selesai
        <span
          :class="[
            'ml-1.5 text-[10px] font-bold px-1.5 py-0.5 rounded-full',
            activeTab === 'selesai' ? 'bg-gray-100 text-gray-600' : 'bg-gray-200 text-gray-500'
          ]"
        >{{ campaigns.selesai.length }}</span>
      </button>
    </div>

    <!-- Campaign grid -->
    <div v-if="currentCampaigns.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div
        v-for="campaign in currentCampaigns"
        :key="campaign.id"
        @click="goToDetail(campaign.id)"
        class="bg-white rounded-2xl shadow-sm border border-stone-100 p-4 hover:shadow-md transition-shadow duration-200 cursor-pointer group"
      >
        <!-- Header row -->
        <div class="flex items-start justify-between gap-2 mb-3">
          <h3 class="text-sm font-semibold text-gray-900 leading-snug group-hover:text-[#8B4513] transition-colors line-clamp-2">
            {{ campaign.judul }}
          </h3>
          <span
            :class="[
              'flex-shrink-0 text-[10px] font-bold uppercase tracking-wide px-2 py-0.5 rounded-full',
              activeTab === 'aktif'
                ? 'bg-emerald-100 text-emerald-700'
                : 'bg-gray-100 text-gray-500'
            ]"
          >
            {{ activeTab === 'aktif' ? 'AKTIF' : 'SELESAI' }}
          </span>
        </div>

        <!-- Amount -->
        <p class="text-base font-bold text-[#8B4513] mb-2">
          {{ formatRupiah(campaign.dana_terkumpul) }}
          <span class="text-xs font-normal text-gray-400"> terkumpul</span>
        </p>

        <!-- Progress bar -->
        <div class="w-full h-1.5 bg-stone-100 rounded-full overflow-hidden mb-2">
          <div
            class="h-full bg-[#8B4513] rounded-full transition-all duration-500"
            :style="{ width: Math.min(campaign.persentase, 100) + '%' }"
          />
        </div>

        <!-- Footer row -->
        <div class="flex items-center justify-between">
          <p class="text-[10px] text-gray-400">
            dari {{ formatRupiah(campaign.target_dana) }}
          </p>
          <p class="text-[10px] font-semibold" :class="campaign.persentase >= 100 ? 'text-emerald-600' : 'text-gray-500'">
            {{ campaign.persentase }}%
          </p>
        </div>
      </div>
    </div>

    <!-- Empty state -->
    <div v-else class="flex flex-col items-center justify-center py-14 text-center">
      <div class="w-12 h-12 rounded-full bg-stone-100 flex items-center justify-center mb-3">
        <svg class="w-6 h-6 text-stone-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
        </svg>
      </div>
      <p class="text-sm font-medium text-gray-400">Belum ada campaign</p>
      <p class="text-xs text-gray-300 mt-1">Campaign akan muncul di sini</p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const props = defineProps({
  campaigns: {
    type: Object,
    required: true
  },
  activeTab: {
    type: String,
    default: 'aktif'
  }
})

defineEmits(['update:activeTab'])

const router = useRouter()
const auth = useAuthStore()
const currentCampaigns = computed(() => props.campaigns[props.activeTab] || [])

function goToDetail(id) {
  if (auth.user?.role === 'KOMUNITAS' || auth.user?.role === 'SUPERADMIN') {
    router.push(`/dashboard/campaigns/${id}/internal`)
  } else {
    router.push(`/campaigns/${id}`)
  }
}

function formatRupiah(n) {
  if (n >= 1_000_000_000) return 'Rp ' + (n / 1_000_000_000).toFixed(1) + 'M'
  if (n >= 1_000_000) return 'Rp ' + (n / 1_000_000).toFixed(1) + 'M'
  return 'Rp ' + n.toLocaleString('id-ID')
}
</script>
