<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center px-4"
        @click.self="$emit('close')"
      >
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" />

        <div class="relative bg-white rounded-2xl shadow-xl p-6 w-full max-w-md z-10">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900">Laporkan Campaign</h3>
            <button
              @click="$emit('close')"
              class="p-1 text-gray-400 hover:text-gray-600 transition-colors"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <p class="text-sm text-gray-500 mb-4">
            Laporkan campaign ini jika melanggar aturan atau mencurigakan.
          </p>

          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Alasan Laporan <span class="text-red-500">*</span>
              </label>
              <select
                v-model="alasanLaporan"
                class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm outline-none focus:ring-2 focus:ring-[#8B4513]/20 focus:border-[#8B4513] transition-colors"
              >
                <option value="" disabled>Pilih alasan</option>
                <option value="informasi-palsu">Informasi palsu atau menyesatkan</option>
                <option value="penipuan">Indikasi penipuan</option>
                <option value="pelanggaran-hak-cipta">Pelanggaran hak cipta</option>
                <option value="konten-tidak-pantas">Konten tidak pantas</option>
                <option value="lainnya">Lainnya</option>
              </select>
              <p v-if="errors.alasan" class="text-xs text-red-500 mt-1">{{ errors.alasan }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Deskripsi (opsional)
              </label>
              <textarea
                v-model="deskripsiLaporan"
                rows="3"
                placeholder="Jelaskan secara detail..."
                class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm outline-none resize-none focus:ring-2 focus:ring-[#8B4513]/20 focus:border-[#8B4513] transition-colors"
              />
            </div>
          </div>

          <div v-if="submitError" class="mt-4 bg-red-50 text-red-700 text-sm rounded-xl px-3 py-2">
            {{ submitError }}
          </div>

          <div class="flex gap-3 mt-6">
            <button
              @click="$emit('close')"
              class="flex-1 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors"
            >
              Batal
            </button>
            <button
              @click="submitReport"
              :disabled="submitting"
              class="flex-1 py-2.5 rounded-xl bg-red-600 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              {{ submitting ? 'Mengirim...' : 'Kirim Laporan' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref } from 'vue'
import api from '@/api/axios'

const props = defineProps({
  show: { type: Boolean, default: false },
  campaignId: { type: Number, required: true },
})

const emit = defineEmits(['close', 'success'])

const alasanLaporan = ref('')
const deskripsiLaporan = ref('')
const submitting = ref(false)
const submitError = ref('')
const errors = ref({})

async function submitReport() {
  errors.value = {}
  submitError.value = ''

  if (!alasanLaporan.value) {
    errors.value.alasan = 'Alasan laporan wajib diisi'
    return
  }

  submitting.value = true
  try {
    await api.post(`/campaigns/${props.campaignId}/reports`, {
      alasan_laporan: alasanLaporan.value,
      deskripsi_laporan: deskripsiLaporan.value || null,
    })
    emit('success')
    emit('close')
  } catch (e) {
    const msg = e.response?.data?.message || 'Gagal mengirim laporan. Silakan coba lagi.'
    submitError.value = msg
  } finally {
    submitting.value = false
  }
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.2s ease;
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>
