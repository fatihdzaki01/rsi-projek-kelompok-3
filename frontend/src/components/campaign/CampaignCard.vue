<template>
  <!-- Campaign card -->
  <article
    class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200
           overflow-hidden flex flex-col"
  >
    <!-- Image -->
    <div class="h-44 overflow-hidden flex-shrink-0">
      <img
        :src="campaign.foto_campaign_url"
        :alt="campaign.judul"
        class="w-full h-full object-cover"
        loading="lazy"
      />
    </div>

    <!-- Body -->
    <div class="flex flex-col flex-1 p-4 gap-1.5">

      <!-- Title -->
      <h3 class="text-sm font-bold text-[#2C2C2C] leading-snug line-clamp-2">
        {{ campaign.judul }}
      </h3>

      <!-- Organization -->
      <p class="text-xs text-gray-500 leading-snug line-clamp-1">
        {{ campaign.nama_lembaga }}
      </p>

      <!-- Collected amount -->
      <p class="text-xs text-[#2C2C2C] mt-1">
        Terkumpul: <span class="font-semibold">{{ formatRupiah(campaign.dana_terkumpul) }}</span>
      </p>

      <!-- Progress bar -->
      <div class="flex items-center gap-2">
        <div class="flex-1 bg-gray-200 rounded-full h-1.5 overflow-hidden">
          <div
            class="h-full bg-[#8B4513] rounded-full transition-all duration-500"
            :style="{ width: `${Math.min(campaign.persentase_pencapaian, 100)}%` }"
          />
        </div>
        <span class="text-xs font-semibold text-[#8B4513] flex-shrink-0 w-8 text-right">
          {{ campaign.persentase_pencapaian }}%
        </span>
      </div>

      <!-- Donate button -->
      <button
        class="mt-2 w-full py-2.5 rounded-lg bg-[#8B4513] text-white text-sm font-semibold
               hover:bg-[#7a3b10] active:bg-[#6b3210] transition-colors duration-150
               cursor-pointer"
        :aria-label="`Donasi untuk ${campaign.judul}`"
      >
        Donasi Sekarang
      </button>

      <!-- Footer row: date + category badge -->
      <div class="flex items-center justify-between mt-1.5">
        <span class="text-xs text-gray-400">{{ campaign.tanggal_selesai }}</span>
        <span
          class="text-xs font-semibold px-2.5 py-0.5 rounded-full uppercase tracking-wide"
          :class="badgeClass(campaign.nama_kategori)"
        >
          {{ campaign.nama_kategori }}
        </span>
      </div>

    </div>
  </article>
</template>

<script setup lang="ts">
// ── Type ──────────────────────────────────────────────────────────────────────
export interface Campaign {
  id_campaign: number
  judul: string
  nama_lembaga: string
  dana_terkumpul: number
  target_dana: number
  foto_campaign_url: string
  tanggal_selesai: string
  nama_kategori: string
  persentase_pencapaian: number
}

// ── Props ─────────────────────────────────────────────────────────────────────
defineProps<{ campaign: Campaign }>()

// ── Helpers ───────────────────────────────────────────────────────────────────

/** Format number as Indonesian Rupiah: Rp 45.000.000 */
function formatRupiah(amount: number): string {
  return 'Rp ' + amount.toLocaleString('id-ID')
}

/** Badge colour per category */
function badgeClass(kategori: string): string {
  const map: Record<string, string> = {
    UMUM:       'bg-gray-100 text-gray-600',
    PENDIDIKAN: 'bg-blue-100 text-blue-700',
    LINGKUNGAN: 'bg-green-100 text-green-700',
    KESEHATAN:  'bg-teal-100 text-teal-700',
    BENCANA:    'bg-orange-100 text-orange-700',
    SOSIAL:     'bg-purple-100 text-purple-700',
  }
  return map[kategori] ?? 'bg-gray-100 text-gray-600'
}
</script>
