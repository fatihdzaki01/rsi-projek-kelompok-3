<template>
  <div>
    <div v-if="updates.length > 0" class="divide-y divide-stone-100">
      <div
        v-for="(update, i) in updates"
        :key="update.id"
        class="py-4 first:pt-0 last:pb-0"
      >
        <div class="flex items-start gap-3">
          <!-- Timeline dot -->
          <div class="flex flex-col items-center flex-shrink-0 mt-1">
            <div class="w-2 h-2 rounded-full bg-[#8B4513]" />
            <div v-if="i < updates.length - 1" class="w-px flex-1 bg-stone-200 mt-1.5 min-h-[2rem]" />
          </div>

          <!-- Content -->
          <div class="flex-1 min-w-0 pb-1">
            <!-- Campaign label -->
            <p class="text-[10px] uppercase tracking-wide text-[#8B4513] font-semibold mb-1">
              {{ update.campaign_name }}
            </p>

            <!-- Update title -->
            <h4 class="text-sm font-semibold text-gray-900 mb-1.5 leading-snug">
              {{ update.title }}
            </h4>

            <!-- Update body -->
            <p class="text-xs text-gray-500 leading-relaxed line-clamp-3">
              {{ update.body }}
            </p>

            <!-- Timestamp -->
            <p class="text-[10px] text-gray-300 mt-2">
              {{ formatDate(update.created_at) }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty state -->
    <div v-else class="flex flex-col items-center justify-center py-12 text-center">
      <div class="w-12 h-12 rounded-full bg-stone-100 flex items-center justify-center mb-3">
        <svg class="w-6 h-6 text-stone-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/>
        </svg>
      </div>
      <p class="text-sm font-medium text-gray-400">Belum ada update</p>
      <p class="text-xs text-gray-300 mt-1">Update komunitas akan tampil di sini</p>
    </div>
  </div>
</template>

<script setup>
defineProps({
  updates: {
    type: Array,
    default: () => []
  }
})

function formatDate(dateStr) {
  const d = new Date(dateStr)
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
}
</script>
