<template>
  <div
    @click="$emit('select', notif)"
    :class="[
      'flex items-start gap-3 px-3 py-3 rounded-xl cursor-pointer transition-all duration-150 group border-l-4',
      isActive
        ? 'bg-[#FDF0E6] border-[#8B4513]'
        : notif.is_read
          ? 'bg-white border-transparent hover:bg-orange-50'
          : 'bg-[#FDF6F0] border-[#8B4513] hover:bg-orange-50'
    ]"
  >
    <!-- Icon circle with unread dot -->
    <div class="relative flex-shrink-0 mt-0.5">
      <div :class="['w-9 h-9 rounded-full flex items-center justify-center text-base', typeConfig.bg]">
        {{ typeConfig.icon }}
      </div>
      <span
        v-if="!notif.is_read"
        class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-[#8B4513] border-2 border-white"
      />
    </div>

    <!-- Content -->
    <div class="flex-1 min-w-0">
      <div class="flex items-start justify-between gap-1">
        <p :class="['text-xs font-semibold leading-tight truncate', notif.is_read ? 'text-gray-600' : 'text-gray-900']">
          {{ notif.title }}
        </p>
        <span class="text-[10px] text-gray-400 flex-shrink-0 mt-0.5">{{ formatTime(notif.created_at) }}</span>
      </div>
      <p class="text-[11px] text-gray-400 mt-0.5 truncate leading-relaxed">
        {{ notif.preview }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  notif:    { type: Object,  required: true },
  isActive: { type: Boolean, default: false }
})
defineEmits(['select'])

const typeMap = {
  transaksi_berhasil:      { icon: '✅', bg: 'bg-emerald-100' },
  transaksi_gagal:         { icon: '❌', bg: 'bg-red-100'     },
  campaign_baru:           { icon: '📢', bg: 'bg-blue-100'    },
  update_campaign:         { icon: '📝', bg: 'bg-teal-100'    },
  campaign_hampir_selesai: { icon: '⏰', bg: 'bg-orange-100'  },
  follow_komunitas:        { icon: '🤝', bg: 'bg-purple-100'  },
  sistem:                  { icon: '⚙️', bg: 'bg-gray-100'    }
}

const typeConfig = computed(() => typeMap[props.notif.type] ?? { icon: '🔔', bg: 'bg-gray-100' })

function formatTime(dateStr) {
  const d    = new Date(dateStr)
  const now  = new Date()
  const diff = Math.floor((now - d) / 3600000)
  if (diff < 1)  return 'Baru saja'
  if (diff < 24) return `${diff} jam lalu`
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })
}
</script>
