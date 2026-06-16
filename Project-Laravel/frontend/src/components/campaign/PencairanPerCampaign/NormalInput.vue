<template>
  <div>
    <label class="block text-xs font-medium text-gray-500 mb-1">{{ label }} <span class="text-red-400">*</span></label>
    <input
      :type="type"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      :placeholder="placeholder"
      class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm text-[#2C2C2C] focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-transparent"
    />
    <div v-if="presets && presets.length > 0" class="flex flex-wrap gap-2 mt-2">
      <button
        v-for="p in presets"
        :key="p"
        type="button"
        @click="$emit('update:modelValue', String(p))"
        :class="['px-3 py-1.5 rounded-lg text-xs font-medium border transition-colors', Number(modelValue) === p ? 'bg-[#8B4513] text-white border-[#8B4513]' : 'bg-white text-gray-600 border-gray-200 hover:border-[#8B4513]']"
      >
        {{ rupiah(p) }}
      </button>
    </div>
  </div>
</template>

<script setup>
defineProps({
  modelValue: { type: [String, Number], default: '' },
  label: { type: String, default: '' },
  type: { type: String, default: 'text' },
  placeholder: { type: String, default: '' },
  presets: { type: Array, default: () => [] },
})

defineEmits(['update:modelValue'])

function rupiah(val) {
  return 'Rp ' + (Number(val) || 0).toLocaleString('id-ID')
}
</script>
