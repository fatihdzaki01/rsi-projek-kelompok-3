<script setup>
const props = defineProps({
  modelValue: { type: String, default: 'semua' },
  categories: { type: Array, required: true }
})

const emit = defineEmits(['update:modelValue'])
</script>

<template>
  <div class="relative">
    <div class="flex items-center gap-2 bg-white border border-gray-200 rounded-full
                px-5 py-2.5 cursor-pointer shadow-sm min-w-[180px]">
      <svg class="w-4 h-4 shrink-0" style="color: #6B7280;" xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <line x1="4" y1="6" x2="20" y2="6"/>
        <line x1="8" y1="12" x2="20" y2="12"/>
        <line x1="12" y1="18" x2="20" y2="18"/>
      </svg>
      <select
        :value="modelValue"
        @change="emit('update:modelValue', $event.target.value)"
        class="bg-transparent text-sm outline-none cursor-pointer flex-1 appearance-none font-medium"
        style="color: #1a2744;"
      >
        <option v-for="cat in categories" :key="cat.value" :value="cat.value">
          {{ cat.label }}<template v-if="cat.count !== undefined"> ({{ cat.count }})</template>
        </option>
      </select>
      <svg class="w-3.5 h-3.5 shrink-0" style="color: #6B7280;" xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <polyline points="6 9 12 15 18 9"/>
      </svg>
    </div>
  </div>
</template>
