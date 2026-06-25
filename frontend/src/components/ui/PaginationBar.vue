<script setup>
const props = defineProps({
  currentPage: { type: Number, required: true },
  totalPages:  { type: Number, required: true },
  perPage:     { type: Number, default: 15 },
  total:       { type: Number, default: 0 },
})

const emit = defineEmits(['update:currentPage', 'update:perPage'])

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

function changePerPage(e) {
  const val = parseInt(e.target.value, 10)
  emit('update:perPage', val)
}
</script>

<template>
  <div class="flex items-center justify-between gap-4 mt-8">
    <div class="flex items-center gap-2">
      <label class="text-xs text-gray-500">Tampilkan</label>
      <select
        :value="perPage"
        @change="changePerPage"
        class="px-2 py-1 border border-gray-200 rounded-lg text-xs text-[#2C2C2C] focus:outline-none focus:ring-2 focus:ring-[#8B4513] bg-white"
      >
        <option :value="10">10</option>
        <option :value="20">20</option>
        <option :value="50">50</option>
      </select>
    </div>

    <div class="flex items-center gap-2">
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

    <span class="text-xs text-gray-400">{{ total }} data</span>
  </div>
</template>
