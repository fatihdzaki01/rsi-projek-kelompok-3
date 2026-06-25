<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { Loader2 } from 'lucide-vue-next'
import Navbar from '@/components/shared/Navbar.vue'
import AppFooter from '@/components/shared/AppFooter.vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/api/axios'

const router = useRouter()
const authStore = useAuthStore()

const activeBank = ref(null)
const perubahan = ref([])
const isLoading = ref(true)

const form = reactive({
  nama_bank_baru: '',
  nomor_rekening_baru: '',
  foto_buku_rekening_url: '',
  alasan_perubahan: '',
})

const bankPrefixMap = {
  '002': 'BRI',
  '008': 'Mandiri',
  '009': 'BNI',
  '014': 'BCA',
  '013': 'Permata',
  '011': 'Danamon',
  '022': 'CIMB Niaga',
  '200': 'BTN',
  '213': 'BTPN',
  '422': 'BSI',
  '147': 'Muamalat',
  '432': 'BJB',
  '426': 'Mega',
  '441': 'BSI',
  '451': 'BSI',
  '016': 'Maybank',
  '484': 'Bank Kalsel',
  '490': 'Bank Jateng',
  '494': 'Bank Jatim',
}

let userSelectedBank = false

watch(() => form.nama_bank_baru, () => {
  userSelectedBank = true
})

watch(() => form.nomor_rekening_baru, (val) => {
  if (!val || userSelectedBank) return
  const cleaned = val.replace(/\D/g, '')
  for (const [prefix, bank] of Object.entries(bankPrefixMap)) {
    if (cleaned.startsWith(prefix)) {
      form.nama_bank_baru = bank
      return
    }
  }
  form.nama_bank_baru = ''
})
const errors = reactive({})
const submitting = ref(false)
const toast = ref({ type: '', msg: '' })

const pendingRequest = computed(() =>
  perubahan.value.find((p) => (p.status || '').toLowerCase() === 'menunggu') || null
)
const isPending = computed(() => !!pendingRequest.value)

const showToast = (type, msg) => {
  toast.value = { type, msg }
  setTimeout(() => (toast.value = { type: '', msg: '' }), 3500)
}

const fetchBank = async () => {
  isLoading.value = true
  try {
    const res = await api.get('/communities/bank-account/history')
    const data = res.data.data || res.data
    activeBank.value = data.rekening_aktif || null
    perubahan.value = data.riwayat_perubahan || []
  } catch (e) {
    if (e.response?.status === 401) router.push('/login')
  } finally {
    isLoading.value = false
  }
}

const handleSubmit = async () => {
  Object.keys(errors).forEach((k) => delete errors[k])

  if (!form.nama_bank_baru) errors.nama_bank_baru = 'Nama bank wajib diisi'
  if (!form.nomor_rekening_baru) errors.nomor_rekening_baru = 'Nomor rekening wajib diisi'
  if (!form.foto_buku_rekening_url) errors.foto_buku_rekening_url = 'URL foto buku rekening wajib diisi'
  if (!form.alasan_perubahan) errors.alasan_perubahan = 'Alasan perubahan wajib diisi'
  if (Object.keys(errors).length) return

  submitting.value = true
  try {
    await api.post('/communities/bank-account/change', {
      nama_bank_baru: form.nama_bank_baru,
      nomor_rekening_baru: form.nomor_rekening_baru,
      foto_buku_rekening_url: form.foto_buku_rekening_url,
      alasan_perubahan: form.alasan_perubahan,
    })

    showToast('success', 'Pengajuan rekening berhasil diajukan.')
    await fetchBank()
    form.nama_bank_baru = ''
    form.nomor_rekening_baru = ''
    form.foto_buku_rekening_url = ''
    form.alasan_perubahan = ''
    userSelectedBank = false
  } catch (e) {
    if (e.response?.status === 401) {
      router.push('/login')
      return
    }
    if (e.response?.status === 422 && e.response.data?.errors) {
      Object.assign(errors, Object.fromEntries(
        Object.entries(e.response.data.errors).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v])
      ))
    } else if (e.response?.status === 409) {
      showToast('error', 'Masih ada pengajuan perubahan yang menunggu verifikasi.')
    } else {
      showToast('error', 'Terjadi kesalahan. Coba lagi.')
    }
  } finally {
    submitting.value = false
  }
}

onMounted(fetchBank)
</script>

<template>
  <div class="min-h-screen bg-[#E8DDD0] flex flex-col">
    <Navbar />

    <main class="flex-1 px-4 py-6">
      <div class="max-w-md mx-auto">
        <!-- Breadcrumb -->
        <nav class="text-xs text-gray-500 mb-4">
          <router-link to="/" class="hover:text-[#8B4513]">Beranda</router-link>
          <span class="mx-1">›</span>
          <router-link to="/communities/dashboard" class="hover:text-[#8B4513]">Dashboard Komunitas</router-link>
          <span class="mx-1">›</span>
          <span class="text-[#1a2744] font-medium">Pengaturan Rekening</span>
        </nav>

        <div class="bg-white rounded-2xl shadow-sm p-8">
          <h1 class="text-base font-semibold text-[#1a2744]">Pengaturan Rekening</h1>
          <p class="text-xs text-gray-500 mb-5">
            Perubahan rekening memerlukan verifikasi Superadmin.
          </p>

          <!-- Rekening Aktif -->
          <div class="bg-[#FDF5EE] border border-[#FDF5EE] rounded-xl p-4 mb-6">
            <p class="text-xs tracking-widest uppercase text-gray-500 mb-2">
              Rekening Aktif Saat Ini
            </p>
            <template v-if="isLoading">
              <div class="h-5 w-40 bg-gray-200 rounded animate-pulse"></div>
            </template>
            <template v-else-if="activeBank">
              <p class="font-semibold text-[#1a2744]">
                {{ activeBank.nama_bank }} · {{ activeBank.nomor_rekening }}
              </p>
              <span class="inline-block mt-2 px-2 py-0.5 bg-[#E8F5E9] text-[#2E7D32] text-xs font-semibold rounded">
                TERVERIFIKASI
              </span>
            </template>
            <p v-else class="text-sm text-gray-500">Belum ada rekening terdaftar.</p>
          </div>

          <!-- State: PENDING -->
          <div
            v-if="isPending"
            class="bg-amber-50 border border-amber-200 rounded-xl p-4"
          >
            <p class="text-sm text-amber-800">
              Pengajuan perubahan rekening sedang dalam proses verifikasi Superadmin.
            </p>
            <span class="inline-block mt-3 px-2 py-0.5 bg-amber-100 text-amber-700 text-xs font-semibold rounded">
              MENUNGGU VERIFIKASI
            </span>
            <div class="mt-4 text-xs text-gray-600 border-t border-amber-200 pt-3">
              <p><span class="text-gray-500">Bank:</span> {{ pendingRequest?.nama_bank_baru }}</p>
              <p><span class="text-gray-500">No. Rekening:</span> {{ pendingRequest?.nomor_rekening_baru }}</p>
            </div>
          </div>

          <!-- Form Pengajuan -->
          <form v-else @submit.prevent="handleSubmit" class="space-y-4" novalidate>
            <div>
              <label class="block text-xs font-semibold tracking-widest text-gray-500 mb-1">NAMA BANK</label>
              <input
                v-model="form.nama_bank_baru"
                type="text"
                placeholder="Bank BCA"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1a2744]/30"
              />
              <p v-if="errors.nama_bank_baru" class="mt-1 text-xs text-red-500">{{ errors.nama_bank_baru }}</p>
            </div>

            <div>
              <label class="block text-xs font-semibold tracking-widest text-gray-500 mb-1">NOMOR REKENING</label>
              <input
                v-model="form.nomor_rekening_baru"
                type="text"
                placeholder="1234567890"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1a2744]/30"
              />
              <p v-if="errors.nomor_rekening_baru" class="mt-1 text-xs text-red-500">{{ errors.nomor_rekening_baru }}</p>
            </div>

            <div>
              <label class="block text-xs font-semibold tracking-widest text-gray-500 mb-1">FOTO BUKU REKENING (URL)</label>
              <input
                v-model="form.foto_buku_rekening_url"
                type="url"
                placeholder="https://example.com/buku-rekening.jpg"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1a2744]/30"
              />
              <p v-if="errors.foto_buku_rekening_url" class="mt-1 text-xs text-red-500">{{ errors.foto_buku_rekening_url }}</p>
            </div>

            <div>
              <label class="block text-xs font-semibold tracking-widest text-gray-500 mb-1">ALASAN PERUBAHAN</label>
              <textarea
                v-model="form.alasan_perubahan"
                rows="3"
                placeholder="Jelaskan alasan perubahan rekening..."
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1a2744]/30 resize-none"
              />
              <p v-if="errors.alasan_perubahan" class="mt-1 text-xs text-red-500">{{ errors.alasan_perubahan }}</p>
            </div>

            <!-- Alert -->
            <div class="bg-amber-50 border border-amber-200 text-amber-800 text-xs rounded-lg p-3 flex items-start gap-2">
              <span aria-hidden="true">⚠️</span>
              <span>
                Perubahan rekening harus diverifikasi Superadmin. Rekening lama tetap digunakan selama proses verifikasi.
              </span>
            </div>

            <div class="flex flex-wrap gap-2 pt-2">
              <button
                type="submit"
                :disabled="submitting"
                class="bg-[#1a2744] hover:bg-[#2a3a5c] text-white text-sm font-medium rounded-lg px-6 py-2 transition-colors flex items-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed"
              >
                <Loader2 v-if="submitting" class="h-4 w-4 animate-spin" />
                {{ submitting ? 'Mengajukan...' : 'Ajukan Perubahan' }}
              </button>
              <button
                type="button"
                @click="router.back()"
                class="text-gray-600 hover:text-gray-800 text-sm font-medium px-4 py-2"
              >
                Batal
              </button>
            </div>
          </form>
        </div>

        <!-- Toast -->
        <div
          v-if="toast.msg"
          :class="[
            'fixed bottom-6 right-6 px-4 py-2 rounded-lg shadow-lg text-sm text-white',
            toast.type === 'success' ? 'bg-[#2E7D32]' : 'bg-[#DC2626]',
          ]"
        >
          {{ toast.msg }}
        </div>
      </div>
    </main>

    <AppFooter />
  </div>
</template>
