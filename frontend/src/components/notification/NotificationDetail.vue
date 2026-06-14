<template>
  <!-- Empty state -->
  <div v-if="!notif" class="flex flex-col items-center justify-center h-full min-h-[400px] text-center px-8">
    <div class="w-16 h-16 rounded-full bg-stone-100 flex items-center justify-center mb-4">
      <svg class="w-8 h-8 text-stone-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
      </svg>
    </div>
    <p class="text-sm font-medium text-gray-400">Pilih notifikasi untuk melihat detail</p>
    <p class="text-xs text-gray-300 mt-1">Notifikasi kamu akan tampil di sini</p>
  </div>

  <!-- Detail card -->
  <div v-else class="bg-white rounded-2xl shadow-md overflow-hidden">

    <!-- Top bar -->
    <div class="flex items-center justify-between px-5 py-4 border-b border-stone-100">
      <!-- Type badge -->
      <span :class="['text-[10px] font-bold uppercase tracking-wide px-2.5 py-1 rounded-full', typeConfig.badgeClass]">
        {{ typeConfig.label }}
      </span>
      <!-- Right: timestamp + mark-read button -->
      <div class="flex items-center gap-3">
        <span class="text-[11px] text-gray-400 hidden sm:block">{{ formatFullDate(notif.created_at) }}</span>
      </div>
    </div>

    <div class="px-5 py-5">
      <!-- Icon + title -->
      <div class="flex items-start gap-4 mb-4">
        <div :class="['w-12 h-12 rounded-2xl flex items-center justify-center text-2xl flex-shrink-0', typeConfig.bg]">
          {{ typeConfig.icon }}
        </div>
        <div class="min-w-0">
          <h2 class="text-lg font-bold text-gray-900 leading-snug">{{ notif.title }}</h2>
          <p class="text-xs text-gray-400 mt-0.5">Dari: Sistem Berbagive</p>
          <!-- Timestamp mobile -->
          <p class="text-[11px] text-gray-400 mt-0.5 sm:hidden">{{ formatFullDate(notif.created_at) }}</p>
        </div>
      </div>

      <!-- Divider -->
      <div class="border-t border-stone-100 mb-4" />

      <!-- Body text -->
      <p class="text-sm text-gray-700 leading-relaxed mb-5">{{ notif.body }}</p>

      <!-- Contextual meta blocks -->

      <!-- Transaction receipt box -->
      <div
        v-if="['transaksi_berhasil', 'transaksi_gagal'].includes(notif.type) && notif.meta?.id_transaksi"
        class="bg-[#FDF6F0] rounded-xl p-4 mb-5 border border-orange-100"
      >
        <p class="text-[10px] uppercase tracking-widest text-gray-400 font-semibold mb-3">Detail Transaksi</p>
        <div class="space-y-2">
          <div class="flex justify-between text-xs">
            <span class="text-gray-500">ID Transaksi</span>
            <span class="font-semibold text-gray-900 font-mono">#{{ notif.meta.id_transaksi }}</span>
          </div>
          <div class="flex justify-between text-xs">
            <span class="text-gray-500">Nominal</span>
            <span class="font-bold text-[#8B4513]">{{ formatRupiah(notif.meta.nominal) }}</span>
          </div>
          <div class="flex justify-between text-xs">
            <span class="text-gray-500">Campaign</span>
            <span class="font-semibold text-gray-900 text-right max-w-[55%] truncate">{{ notif.meta.campaign }}</span>
          </div>
          <div class="flex justify-between text-xs">
            <span class="text-gray-500">Metode</span>
            <span class="font-semibold text-gray-900">{{ notif.meta.metode }}</span>
          </div>
        </div>
      </div>

      <!-- Campaign meta box -->
      <div
        v-if="['campaign_baru', 'update_campaign'].includes(notif.type) && notif.meta?.nama_campaign"
        class="bg-blue-50 rounded-xl px-4 py-3 mb-5 flex items-center gap-3"
      >
        <span class="text-lg">📌</span>
        <div>
          <p class="text-[10px] uppercase tracking-wide text-gray-400 font-semibold">Campaign</p>
          <p class="text-sm font-bold text-gray-900">{{ notif.meta.nama_campaign }}</p>
          <p v-if="notif.meta.komunitas" class="text-xs text-gray-500">oleh {{ notif.meta.komunitas }}</p>
        </div>
      </div>

      <!-- Hampir selesai countdown -->
      <div
        v-if="notif.type === 'campaign_hampir_selesai' && notif.meta?.sisa_hari !== undefined"
        class="flex items-center gap-3 bg-orange-50 rounded-xl px-4 py-3 mb-5"
      >
        <span class="text-lg">⏰</span>
        <div>
          <p class="text-[10px] uppercase tracking-wide text-gray-400 font-semibold">{{ notif.meta.nama_campaign }}</p>
          <div class="flex items-center gap-2 mt-0.5">
            <span class="text-sm font-bold text-orange-600">Berakhir dalam</span>
            <span class="bg-orange-600 text-white text-xs font-bold px-2.5 py-0.5 rounded-full">
              {{ notif.meta.sisa_hari }} hari
            </span>
          </div>
        </div>
      </div>

      <!-- Action buttons -->
      <div class="flex gap-2 flex-wrap mt-2">
        <!-- transaksi_berhasil -->
        <template v-if="notif.type === 'transaksi_berhasil'">
          <button class="flex-1 min-w-[120px] bg-[#8B4513] text-white text-xs font-semibold py-2.5 rounded-xl hover:bg-[#a85c2c] transition-colors">
            Lihat Detail Donasi
          </button>
        </template>

        <!-- transaksi_gagal -->
        <template v-else-if="notif.type === 'transaksi_gagal'">
          <button class="flex-1 min-w-[100px] bg-[#1a2744] text-white text-xs font-semibold py-2.5 rounded-xl hover:bg-[#22325a] transition-colors">
            Coba Lagi
          </button>
          <button class="flex-1 min-w-[100px] border border-gray-200 text-gray-700 text-xs font-semibold py-2.5 rounded-xl hover:bg-gray-50 transition-colors">
            Ganti Metode
          </button>
        </template>

        <!-- campaign types -->
        <template v-else-if="['campaign_baru', 'update_campaign', 'campaign_hampir_selesai'].includes(notif.type)">
          <button class="flex-1 min-w-[120px] bg-[#8B4513] text-white text-xs font-semibold py-2.5 rounded-xl hover:bg-[#a85c2c] transition-colors">
            Lihat Campaign
          </button>
        </template>

        <!-- follow -->
        <template v-else-if="notif.type === 'follow_komunitas'">
          <button class="flex-1 min-w-[120px] bg-[#8B4513] text-white text-xs font-semibold py-2.5 rounded-xl hover:bg-[#a85c2c] transition-colors">
            Lihat Profil Komunitas
          </button>
        </template>
      </div>

      <!-- Auto-delete note for read notifs -->
      <p v-if="notif.is_read" class="text-[10px] text-gray-300 text-center mt-5">
        Notifikasi yang sudah dibaca akan otomatis dihapus setelah 5 hari.
      </p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  notif: { type: Object, default: null }
})

const typeMap = {
  transaksi_berhasil:      { icon: '✅', bg: 'bg-emerald-100', badgeClass: 'bg-emerald-100 text-emerald-700', label: 'Transaksi Berhasil' },
  transaksi_gagal:         { icon: '❌', bg: 'bg-red-100',     badgeClass: 'bg-red-100 text-red-700',         label: 'Transaksi Gagal'   },
  campaign_baru:           { icon: '📢', bg: 'bg-blue-100',    badgeClass: 'bg-blue-100 text-blue-700',       label: 'Campaign Baru'     },
  update_campaign:         { icon: '📝', bg: 'bg-teal-100',    badgeClass: 'bg-teal-100 text-teal-700',       label: 'Update Campaign'   },
  campaign_hampir_selesai: { icon: '⏰', bg: 'bg-orange-100',  badgeClass: 'bg-orange-100 text-orange-700',  label: 'Hampir Selesai'    },
  follow_komunitas:        { icon: '🤝', bg: 'bg-purple-100',  badgeClass: 'bg-purple-100 text-purple-700',  label: 'Komunitas'         },
  sistem:                  { icon: '⚙️', bg: 'bg-gray-100',    badgeClass: 'bg-gray-100 text-gray-600',      label: 'Sistem'            }
}

const typeConfig = computed(() =>
  typeMap[props.notif?.type] ?? { icon: '🔔', bg: 'bg-gray-100', badgeClass: 'bg-gray-100 text-gray-600', label: 'Notifikasi' }
)

function formatFullDate(dateStr) {
  const d = new Date(dateStr)
  const date = d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
  const time = d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
  return `${date} — ${time} WIB`
}

function formatRupiah(n) {
  return 'Rp ' + n.toLocaleString('id-ID')
}
</script>
