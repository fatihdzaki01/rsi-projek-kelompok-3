<script setup>
const props = defineProps({
  modelValue: { type: String, default: 'semua' },
  categories: { type: Array, required: true }
})

const emit = defineEmits(['update:modelValue'])
</script>

<template>
  <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-hide">
    <button
      v-for="cat in categories"
      :key="cat.value"
      @click="emit('update:modelValue', cat.value)"
      class="shrink-0 px-4 py-2 rounded-full text-xs font-semibold border transition-colors whitespace-nowrap focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#8B4513]/40"
      :class="modelValue === cat.value
        ? 'bg-[#8B4513] text-white border-[#8B4513] shadow-sm'
        : 'bg-white text-[#2C2C2C] border-gray-200 hover:border-[#8B4513] hover:text-[#8B4513]'"
    >
      {{ cat.label }}
      <span
        v-if="cat.count !== undefined"
        class="ml-1.5 text-[10px] font-medium"
        :class="modelValue === cat.value ? 'text-white/70' : 'text-gray-400'"
      >
        {{ cat.count }}
      </span>
    </button>
  </div>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
