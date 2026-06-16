<template>
  <div class="bg-white rounded-xl shadow-sm border border-stone-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-stone-100">
      <h2 class="text-sm font-bold text-[#2C2C2C]">Filter</h2>
    </div>
    <div class="px-6 py-4">
      <div class="flex flex-wrap items-end gap-4">
        <div>
          <label class="block text-xs font-medium text-gray-500 mb-1">Dari Tanggal</label>
          <input
            type="date"
            :value="startDate"
            @input="$emit('update:startDate', $event.target.value)"
            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm text-[#2C2C2C] focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-transparent"
          />
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-500 mb-1">Sampai Tanggal</label>
          <input
            type="date"
            :value="endDate"
            @input="$emit('update:endDate', $event.target.value)"
            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm text-[#2C2C2C] focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-transparent"
          />
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-500 mb-1">Campaign</label>
          <select
            :value="selectedCampaign"
            @input="$emit('update:selectedCampaign', $event.target.value)"
            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm text-[#2C2C2C] focus:outline-none focus:ring-2 focus:ring-[#8B4513] focus:border-transparent bg-white"
          >
            <option value="">Semua Campaign</option>
            <option v-for="c in campaigns" :key="c.id_campaign" :value="c.id_campaign">{{ c.judul }}</option>
          </select>
        </div>
        <button @click="$emit('apply')" class="px-4 py-2 bg-[#8B4513] text-white rounded-lg text-sm font-medium hover:bg-[#6b3410] transition-colors">Terapkan</button>
        <button @click="$emit('reset')" class="px-4 py-2 border border-stone-200 text-[#2C2C2C] rounded-lg text-sm font-medium hover:bg-stone-50 transition-colors">Reset</button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  startDate: { type: String, default: '' },
  endDate: { type: String, default: '' },
  selectedCampaign: { type: [String, Number], default: '' },
  campaigns: { type: Array, default: () => [] },
})

defineEmits(['update:startDate', 'update:endDate', 'update:selectedCampaign', 'apply', 'reset'])
</script>
