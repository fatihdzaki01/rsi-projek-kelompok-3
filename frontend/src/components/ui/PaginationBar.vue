<script setup>
const props = defineProps({
  currentPage: { type: Number, required: true },
  totalPages:  { type: Number, required: true }
})

const emit = defineEmits(['update:currentPage'])

function pages() {
  const total = props.totalPages
  const cur   = props.currentPage
  if (total <= 6) return Array.from({ length: total }, (_, i) => i + 1)
  if (cur <= 3)   return [1, 2, 3, '...', total]
  if (cur >= total - 2) return [1, '...', total - 2, total - 1, total]
  return [1, '...', cur - 1, cur, cur + 1, '...', total]
}

function go(p) {
  if (typeof p === 'number' && p >= 1 && p <= props.totalPages) {
    emit('update:currentPage', p)
  }
}
</script>

<template>
  <div class="flex items-center justify-center gap-2 mt-8">
    <!-- Prev -->
    <button
      class="w-8 h-8 rounded-full flex items-center justify-center text-sm
             transition-colors hover:bg-gray-200 disabled:opacity-30"
      :disabled="currentPage === 1"
      @click="go(currentPage - 1)"
    >
      <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
        fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
        <polyline points="15 18 9 12 15 6"/>
      </svg>
    </button>

    <!-- Pages -->
    <template v-for="p in pages()" :key="p">
      <span v-if="p === '...'" class="px-1 text-sm" style="color: #9e8e80;">...</span>
      <button
        v-else
        class="w-8 h-8 rounded-full text-sm font-medium transition-colors"
        :class="p === currentPage
          ? 'text-white'
          : 'text-gray-600 hover:bg-gray-200'"
        :style="p === currentPage ? 'background-color: #8B4513;' : ''"
        @click="go(p)"
      >
        {{ p }}
      </button>
    </template>

    <!-- Next -->
    <button
      class="w-8 h-8 rounded-full flex items-center justify-center text-sm
             transition-colors hover:bg-gray-200 disabled:opacity-30"
      :disabled="currentPage === totalPages"
      @click="go(currentPage + 1)"
    >
      <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
        fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
        <polyline points="9 18 15 12 9 6"/>
      </svg>
    </button>
  </div>
</template>