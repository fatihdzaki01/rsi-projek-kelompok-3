<template>
  <div class="min-h-screen flex flex-col bg-background">
    <TheNavbar />

    <main class="flex-1 max-w-5xl mx-auto w-full px-4 py-6">
      <!-- Two-column grid: left content, right sidebar -->
      <div class="grid grid-cols-1 lg:grid-cols-[1fr_300px] gap-6 items-start">

        <!-- ── LEFT COLUMN ─────────────────────────────────────── -->
        <div class="flex flex-col gap-5">

          <!-- Hero image with category badge -->
          <div class="relative rounded-xl overflow-hidden">
            <img
              :src="campaign.foto_campaign_url"
              :alt="`Foto kampanye: ${campaign.judul}`"
              class="w-full aspect-[16/9] object-cover"
            />
            <div class="absolute top-3 left-3">
              <div
                class="text-[10px] font-bold tracking-wider uppercase px-2.5 py-1 rounded-full text-white"
                :style="{ backgroundColor: '#2E8B74' }"
              >
                {{ campaign.nama_kategori }}
              </div>
            </div>
          </div>

          <!-- Campaign title -->
          <h1 class="text-2xl font-bold text-foreground text-balance leading-snug">
            {{ campaign.judul }}
          </h1>

          <!-- Fund progress row -->
          <div class="flex flex-col gap-2">
            <div class="flex items-end justify-between gap-2">
              <div>
                <p class="text-[10px] font-semibold tracking-widest text-muted-foreground uppercase">
                  Terkumpul
                </p>
                <p class="text-xl font-bold text-foreground">
                  {{ formatRupiah(campaign.dana_terkumpul) }}
                </p>
              </div>
              <div class="text-right">
                <p class="text-[10px] font-semibold tracking-widest text-muted-foreground uppercase">
                  Target
                </p>
                <p class="text-sm font-semibold text-foreground">
                  {{ formatRupiah(campaign.target_dana) }}
                </p>
              </div>
            </div>

            <!-- Progress bar -->
            <div
              class="w-full h-1.5 bg-secondary rounded-full overflow-hidden"
              role="progressbar"
              :aria-valuenow="pct"
              aria-valuemin="0"
              aria-valuemax="100"
              :aria-label="`${pct}% dari target terkumpul`"
            >
              <div
                class="h-full rounded-full transition-all duration-500"
                :style="{ width: `${pct}%`, backgroundColor: '#8B4513' }"
              />
            </div>
          </div>

          <!-- Stats row -->
          <div class="flex items-center gap-5">
            <div class="flex items-center gap-1.5 text-sm text-foreground">
              <Heart
                class="size-4 text-[#C0392B]"
                aria-hidden="true"
              />
              <span>
                <span class="font-semibold">
                  {{ campaign.jumlah_donatur.toLocaleString('id-ID') }}
                </span>
                Donatur
              </span>
            </div>
            <div class="flex items-center gap-1.5 text-sm text-foreground">
              <Clock
                class="size-4 text-muted-foreground"
                aria-hidden="true"
              />
              <span>
                <span class="font-semibold">{{ campaign.hari_tersisa }}</span>
                Hari Lagi
              </span>
            </div>
          </div>

          <!-- Campaign story -->
          <CampaignStory />
        </div>

        <!-- ── RIGHT COLUMN (sticky sidebar) ───────────────────── -->
        <DonationSidebar :campaign-id="campaign.id_campaign" />
      </div>
    </main>

    <TheFooter />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Heart, Clock } from 'lucide-vue-next'
import TheNavbar from '@/components/shared/Navbar.vue'
import TheFooter from '@/components/shared/Footer.vue'
import DonationSidebar from '@/components/ui/DonationSidebar.vue'
import CampaignStory from '@/components/ui/CampaignStory.vue'

const campaign = {
  id_campaign: 1,
  judul: "Wujudkan Mimpi Anak-Anak di Pedalaman NTT",
  nama_lembaga: "Yayasan Pendidikan Cerdas",
  dana_terkumpul: 125400000,
  target_dana: 250000000,
  foto_campaign_url:
    "https://images.unsplash.com/photo-1541802645635-11f2286a7482?w=800&h=480&fit=crop",
  tanggal_selesai: "2024-06-12",
  nama_kategori: "PENDIDIKAN",
  persentase_pencapaian: 50,
  jumlah_donatur: 1240,
  hari_tersisa: 12,
}

const pct = computed(() => {
  return Math.min(
    100,
    Math.round((campaign.dana_terkumpul / campaign.target_dana) * 100)
  )
})

function formatRupiah(amount) {
  return 'Rp ' + amount.toLocaleString('id-ID')
}
</script>
