<template>
  <div class="relative">
    <button
      @click="open = !open"
      class="flex items-center gap-2 bg-white border border-stone-200 rounded-xl px-4 py-2.5 text-sm text-gray-700 font-medium hover:border-stone-300 transition-colors shadow-sm min-w-[160px] justify-between"
    >
      <div class="flex items-center gap-2">
        <svg class="w-4 h-4 text-[#8B4513]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
        </svg>
        <span>{{ selectedLabel }}</span>
      </div>
      <svg
        class="w-3.5 h-3.5 text-gray-400 transition-transform duration-150"
        :class="open ? 'rotate-180' : ''"
        fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
      >
        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
      </svg>
    </button>

    <!-- Dropdown -->
    <Transition name="dropdown">
      <div
        v-if="open"
        class="absolute right-0 top-full mt-1.5 bg-white border border-stone-100 rounded-xl shadow-lg z-20 overflow-hidden min-w-[160px]"
      >
        <button
          v-for="loc in locations"
          :key="loc.value"
          @click="select(loc.value)"
          :class="[
            'w-full text-left px-4 py-2.5 text-sm transition-colors',
            modelValue === loc.value
              ? 'bg-[#F5E6D8] text-[#8B4513] font-semibold'
              : 'text-gray-700 hover:bg-stone-50'
          ]"
        >
          {{ loc.label }}
        </button>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  modelValue: { type: String, default: 'semua' },
  locations:  { type: Array, required: true }
})

const emit = defineEmits(['update:modelValue'])

const open = ref(false)

const selectedLabel = computed(
  () => props.locations.find(l => l.value === props.modelValue)?.label ?? 'Semua Lokasi'
)

function select(value) {
  emit('update:modelValue', value)
  open.value = false
}
</script>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active { transition: opacity 0.15s ease, transform 0.15s ease; }
.dropdown-enter-from,
.dropdown-leave-to   { opacity: 0; transform: translateY(-4px); }
</style>
