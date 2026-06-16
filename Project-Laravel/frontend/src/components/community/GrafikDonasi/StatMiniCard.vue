<template>
  <div class="bg-white rounded-xl shadow-sm border border-stone-100 p-5">
    <p class="text-xs text-gray-400 mb-1">{{ label }}</p>
    <p class="text-xl font-bold" :class="color">{{ value }}</p>
  </div>
</template>

<script setup>
defineProps({
  label: { type: String, required: true },
  value: { type: [String, Number], default: '0' },
  color: { type: String, default: 'text-[#2C2C2C]' },
})
</script>
