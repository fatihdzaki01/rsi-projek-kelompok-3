<template>
  <div>
    <div v-if="data.length === 0" class="text-center py-10 text-sm text-gray-400">
      Belum ada data donasi untuk ditampilkan
    </div>
    <div v-else class="relative" style="height: 300px">
      <svg
        :viewBox="`0 0 ${svgWidth} ${svgHeight}`"
        class="w-full h-full"
        preserveAspectRatio="none"
      >
        <polyline
          :points="linePoints"
          fill="none"
          stroke="#8B4513"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
        <polygon
          :points="areaPoints"
          fill="url(#gradient)"
          opacity="0.15"
        />
        <defs>
          <linearGradient id="gradient" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#8B4513" />
            <stop offset="100%" stop-color="#8B4513" stop-opacity="0" />
          </linearGradient>
        </defs>
      </svg>
      <div class="flex justify-between mt-2">
        <span v-for="(item, i) in visibleLabels" :key="i" class="text-[10px] text-gray-400">{{ item }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  data: { type: Array, default: () => [] },
})

const svgWidth = 800
const svgHeight = 250
const padding = { top: 10, right: 10, bottom: 0, left: 10 }
const chartWidth = svgWidth - padding.left - padding.right
const chartHeight = svgHeight - padding.top - padding.bottom

const maxValue = computed(() => Math.max(...props.data.map(d => d.nominal || 0), 1))

const linePoints = computed(() => {
  return props.data.map((d, i) => {
    const x = padding.left + (i / Math.max(props.data.length - 1, 1)) * chartWidth
    const y = padding.top + chartHeight - ((d.nominal || 0) / maxValue.value) * chartHeight
    return `${x},${y}`
  }).join(' ')
})

const areaPoints = computed(() => {
  if (props.data.length === 0) return ''
  const first = padding.left + ','
  const last = padding.left + chartWidth + ','
  const pts = props.data.map((d, i) => {
    const x = padding.left + (i / Math.max(props.data.length - 1, 1)) * chartWidth
    const y = padding.top + chartHeight - ((d.nominal || 0) / maxValue.value) * chartHeight
    return `${x},${y}`
  }).join(' ')
  return first + (padding.top + chartHeight) + ' ' + pts + ' ' + last + (padding.top + chartHeight)
})

const visibleLabels = computed(() => {
  const max = 6
  const step = Math.max(1, Math.floor(props.data.length / max))
  return props.data.filter((_, i) => i % step === 0 || i === props.data.length - 1).map(d => d.label || '')
})
</script>
