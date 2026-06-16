<template>
  <aside
    class="lg:sticky lg:top-16 bg-card border border-border rounded-xl p-5 flex flex-col gap-4 shadow-sm"
    aria-label="Pilih nominal donasi"
  >
    <!-- Title -->
    <h2 class="font-semibold text-sm text-foreground">Pilih Nominal Donasi</h2>

    <!-- Preset amounts grid -->
    <div class="grid grid-cols-2 gap-2" role="group" aria-label="Nominal preset">
      <button
        v-for="amount in presetAmounts"
        :key="amount"
        @click="handlePresetClick(amount)"
        :class="[
          'py-2 px-3 rounded-lg text-sm font-medium border transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring',
          selected === amount
            ? 'bg-[#8B4513] text-white border-[#8B4513]'
            : 'bg-card text-foreground border-border hover:border-[#8B4513] hover:text-[#8B4513]'
        ]"
        :aria-pressed="selected === amount"
      >
        {{ formatRupiah(amount) }}
      </button>
    </div>

    <!-- Custom amount -->
    <div class="flex flex-col gap-1.5">
      <label class="text-[10px] font-semibold tracking-widest text-muted-foreground uppercase">
        Nominal Lainnya
      </label>
      <div class="flex items-center border border-border rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-ring bg-card">
        <span class="px-3 text-sm text-muted-foreground select-none border-r border-border py-2">
          Rp
        </span>
        <input
          type="number"
          min="1000"
          placeholder="Masukkan jumlah"
          :value="custom"
          @input="handleCustomChange"
          class="flex-1 px-3 py-2 text-sm bg-transparent text-foreground placeholder:text-muted-foreground outline-none"
          aria-label="Masukkan nominal donasi lainnya"
        />
      </div>
    </div>

    <!-- Anonim toggle -->
    <div class="flex flex-col gap-1.5">
      <label class="text-[10px] font-semibold tracking-widest text-muted-foreground uppercase">
        Identitas Donasi
      </label>
      <div class="flex items-center gap-2">
        <label class="relative inline-flex items-center cursor-pointer">
          <input
            type="checkbox"
            v-model="isAnonim"
            class="sr-only peer"
          />
          <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#8B4513]" />
        </label>
        <span class="text-xs text-foreground">Sembunyikan nama saya (Anonim)</span>
      </div>

      <!-- Nama tampil (only when not anonim) -->
      <input
        v-if="!isAnonim"
        v-model="namaTampil"
        type="text"
        placeholder="Nama yang ditampilkan"
        maxlength="100"
        class="w-full px-3 py-2 text-sm bg-card border border-border rounded-lg text-foreground placeholder:text-muted-foreground outline-none focus:ring-2 focus:ring-ring"
      />
    </div>

    <!-- Message / Doa -->
    <div class="flex flex-col gap-1.5">
      <label class="text-[10px] font-semibold tracking-widest text-muted-foreground uppercase">
        Pesan / Doa (opsional)
      </label>
      <textarea
        v-model="pesan"
        rows="2"
        placeholder="Tulis pesan atau doa untuk campaign ini..."
        maxlength="500"
        class="w-full px-3 py-2 text-sm bg-card border border-border rounded-lg text-foreground placeholder:text-muted-foreground outline-none resize-none focus:ring-2 focus:ring-ring"
      />
    </div>

    <!-- Payment method -->
    <div class="flex flex-col gap-1.5">
      <label class="text-[10px] font-semibold tracking-widest text-muted-foreground uppercase">
        Metode Pembayaran
      </label>
      <select
        v-model="metodePembayaran"
        class="w-full px-3 py-2 text-sm bg-card border border-border rounded-lg text-foreground outline-none focus:ring-2 focus:ring-ring"
      >
        <option value="qris">QRIS</option>
        <option value="gopay">GoPay</option>
        <option value="ovo">OVO</option>
        <option value="shopeepay">ShopeePay</option>
        <option value="bca">BCA</option>
        <option value="mandiri">Mandiri</option>
        <option value="bni">BNI</option>
      </select>
    </div>

    <!-- Action buttons -->
    <div class="flex flex-col gap-2">
      <button
        :disabled="donating"
        class="w-full py-2.5 rounded-lg bg-[#2C2C2C] text-white text-sm font-semibold hover:bg-[#444] transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:opacity-50"
        @click="handleDonate"
      >
        {{ donating ? 'Memproses...' : 'Donasi Sekarang' }}
      </button>
    </div>

    <!-- Notification -->
    <Transition name="fade">
      <div
        v-if="notif"
        class="text-xs font-medium px-3 py-2 rounded-lg"
        :class="notif.type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
      >
        {{ notif.message }}
      </div>
    </Transition>

    <div class="h-px bg-border" />

    <!-- Trusted fundraiser -->
    <div class="flex items-center gap-2 text-muted-foreground">
      <ShieldCheck class="size-4 shrink-0 text-[#2E8B74]" aria-hidden="true" />
      <span class="text-[10px] font-semibold tracking-widest uppercase">
        Penggalang Dana Terpercaya
      </span>
    </div>

    <!-- Recent donors -->
    <div class="flex flex-col gap-2">
      <span class="text-[10px] font-semibold tracking-widest text-muted-foreground uppercase">
        Donatur Terbaru
      </span>
      <div class="flex items-center gap-1" aria-label="Donatur terbaru">
        <div
          v-for="(donor, idx) in recentDonors"
          :key="donor.name"
          :class="[
            'size-7 flex items-center justify-center rounded-full border-2 border-card text-[10px] bg-secondary text-foreground font-medium',
            idx > 0 ? '-ml-1' : ''
          ]"
        >
          {{ donor.initials }}
        </div>
        <span class="ml-1 text-xs text-muted-foreground font-medium">+36</span>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { ShieldCheck } from 'lucide-vue-next'
import api from '@/api/axios'

const router = useRouter()

const props = defineProps({
  campaignId: { type: Number, required: true },
})

const presetAmounts = [50000, 100000, 250000, 500000]
const recentDonors = [
  { name: 'Andi', initials: 'AN', src: '' },
  { name: 'Budi', initials: 'BU', src: '' },
  { name: 'Citra', initials: 'CI', src: '' },
  { name: 'Desi', initials: 'DE', src: '' },
]

const selected = ref(250000)
const custom = ref('')
const isAnonim = ref(false)
const namaTampil = ref('')
const pesan = ref('')
const metodePembayaran = ref('qris')
const donating = ref(false)
const notif = ref(null)

function formatRupiah(amount) {
  return 'Rp ' + amount.toLocaleString('id-ID')
}

function handlePresetClick(amount) {
  selected.value = amount
  custom.value = ''
}

function handleCustomChange(e) {
  custom.value = e.target.value
  selected.value = null
}

async function handleDonate() {
  if (!localStorage.getItem('token')) {
    router.push(`/login?redirect=/campaigns/${props.campaignId}`)
    return
  }

  const nominal = custom.value ? Number(custom.value) : selected.value
  if (!nominal || nominal < 5000) {
    notif.value = { type: 'error', message: 'Minimal donasi Rp 5.000' }
    return
  }

  donating.value = true
  notif.value = null
  try {
    const res = await api.post('/donations', {
      id_campaign: props.campaignId,
      nominal,
      metode_pembayaran: metodePembayaran.value,
      is_anonim: isAnonim.value,
      nama_tampil: isAnonim.value ? null : (namaTampil.value || null),
      pesan: pesan.value || null,
    })
    const data = res.data.data
    const vaMethods = ['bca', 'mandiri', 'bni']
    if (vaMethods.includes(metodePembayaran.value)) {
      router.push(`/payments/va/${data.id_donasi}`)
    } else {
      router.push(`/payments/checkout/${data.id_donasi}`)
    }
  } catch (e) {
    const msg = e.response?.data?.message ?? 'Gagal membuat donasi'
    notif.value = { type: 'error', message: msg }
  } finally {
    donating.value = false
  }
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
