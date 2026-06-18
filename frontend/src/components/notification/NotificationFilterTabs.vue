<template>
  <div class="flex items-center gap-1 overflow-x-auto scrollbar-hide pb-0.5">
    <button
      v-for="f in filters"
      :key="f.value"
      @click="$emit('update:modelValue', f.value)"
      :class="[
        'flex-shrink-0 px-3 py-1.5 rounded-full text-xs font-medium transition-all duration-150',
        modelValue === f.value
          ? 'bg-[#8B4513] text-white shadow-sm'
          : 'text-gray-500 hover:bg-stone-100 hover:text-gray-700'
      ]"
    >
      {{ f.label }}
      <span
        v-if="f.value === 'belum_dibaca' && unreadCount > 0"
        class="ml-1 text-[10px] font-bold"
        :class="modelValue === 'belum_dibaca' ? 'text-orange-200' : 'text-[#8B4513]'"
      >{{ unreadCount }}</span>
    </button>
  </div>
</template>

<script setup>
defineProps({
  modelValue:   { type: String,  default: 'semua' },
  unreadCount:  { type: Number,  default: 0 }
})
defineEmits(['update:modelValue'])

const filters = [
  { value: 'semua',        label: 'Semua' },
  { value: 'belum_dibaca', label: 'Belum Dibaca' },
  { value: 'transaksi',    label: 'Transaksi' },
  { value: 'campaign',     label: 'Campaign' },
  { value: 'komunitas',    label: 'Komunitas' }
]
</script>
