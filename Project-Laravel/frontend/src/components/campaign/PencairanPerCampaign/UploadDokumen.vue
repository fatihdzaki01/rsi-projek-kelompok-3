<template>
  <div>
    <label class="block text-xs font-medium text-gray-500 mb-1">URL Dokumen Pendukung <span class="text-red-400">*</span></label>
    <input
      type="url"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      placeholder="https://..."
      class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm text-[#2C2C2C] focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-transparent"
    />
    <p class="text-xs text-gray-400 mt-1">Masukkan URL file PDF proposal atau bukti penggunaan dana</p>
  </div>
</template>

<script setup>
defineProps({
  modelValue: { type: String, default: '' },
})

defineEmits(['update:modelValue'])
</script>
