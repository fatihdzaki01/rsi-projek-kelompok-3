<template>
  <div
    @click="$emit('card-click', community.id)"
    class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-visible cursor-pointer hover:shadow-md hover:scale-[1.01] transition-all duration-200 flex flex-col"
  >
    <!-- Cover image -->
    <div class="relative h-36 rounded-t-2xl overflow-hidden flex-shrink-0">
      <img
        v-if="community.foto_komunitas"
        :src="community.foto_komunitas"
        :alt="community.nama_komunitas"
        class="w-full h-full object-cover"
      />
      <!-- Placeholder cover -->
      <div
        v-else
        class="w-full h-full bg-gradient-to-br from-[#c4783c] via-[#8B4513] to-[#5c2d0a] flex items-center justify-center"
      >
        <span class="text-4xl opacity-30">🤝</span>
      </div>

      <!-- Avatar overlay -->
      <div class="absolute -bottom-7 left-4">
        <div
          class="w-14 h-14 rounded-full border-2 border-white shadow-md flex items-center justify-center text-white font-bold text-lg bg-gradient-to-br from-[#8B4513] to-[#c4783c]"
        >
          {{ (community.nama_komunitas || '?').charAt(0) }}
        </div>
      </div>
    </div>

    <!-- Card body -->
    <div class="flex flex-col flex-1 px-4 pb-4 pt-10">

      <!-- Name -->
      <h3 class="text-sm font-bold text-gray-900 leading-tight mb-1">
        {{ community.nama_komunitas }}
      </h3>

      <!-- Location -->
      <div class="flex items-center gap-1 mb-2">
        <svg class="w-3 h-3 text-[#8B4513] flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
        </svg>
        <span class="text-[11px] text-gray-400 truncate">{{ community.kota }}</span>
      </div>

      <!-- Description -->
      <p class="text-xs text-gray-500 leading-relaxed line-clamp-2 mb-3 flex-1">
        {{ community.deskripsi }}
      </p>

      <!-- Divider -->
      <div class="border-t border-stone-100 mb-3" />

      <!-- Stats row -->
      <div class="grid grid-cols-3 gap-1 mb-4">
        <div class="text-center">
          <p class="text-sm font-bold text-gray-900">{{ formatFollower(community.total_follower) }}</p>
          <p class="text-[10px] text-gray-400 mt-0.5">Follower</p>
        </div>
        <div class="text-center border-x border-stone-100">
          <p class="text-sm font-bold text-gray-900">{{ community.total_campaign_aktif || 0 }}</p>
          <p class="text-[10px] text-gray-400 mt-0.5">Campaign Aktif</p>
        </div>
        <div class="text-center">
          <p class="text-sm font-bold text-[#8B4513]">{{ formatDana(community.total_dana_diterima) }}</p>
          <p class="text-[10px] text-gray-400 mt-0.5">Total Dana</p>
        </div>
      </div>

      <!-- Follow button -->
      <button
        @click.stop="handleFollowClick"
        :class="[
          'w-full py-2 rounded-xl text-xs font-semibold transition-all duration-150',
          community.is_following
            ? 'bg-white border border-gray-200 text-gray-600 hover:border-red-200 hover:text-red-500'
            : 'bg-[#8B4513] text-white hover:bg-[#a85c2c] shadow-sm'
        ]"
      >
        <span v-if="!community.is_following">Ikuti</span>
        <span v-else class="flex items-center justify-center gap-1">
          <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
          </svg>
          Mengikuti
        </span>
      </button>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  community: { type: Object, required: true },
  isGuest:   { type: Boolean, default: false }
})

const emit = defineEmits(['card-click', 'follow', 'unfollow'])

function handleFollowClick() {
  if (props.isGuest) {
    emit('follow', props.community) // parent handles redirect
    return
  }
  if (props.community.is_following) {
    emit('unfollow', props.community)
  } else {
    emit('follow', props.community)
  }
}

function formatFollower(n) {
  const num = Number(n) || 0
  if (num >= 1000) return (num / 1000).toFixed(1).replace('.0', '') + 'K'
  return num.toString()
}

function formatDana(n) {
  const num = Number(n) || 0
  if (num >= 1_000_000_000) return 'Rp ' + (num / 1_000_000_000).toFixed(1) + 'M'
  if (num >= 1_000_000)     return 'Rp ' + (num / 1_000_000).toFixed(1) + 'Jt'
  return 'Rp ' + num.toLocaleString('id-ID')
}
</script>
