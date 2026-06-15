<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Upload, X, Loader2 } from 'lucide-vue-next'
import AppNavbarKomunitas from '@/components/shared/AppNavbarKomunitas.vue'
import AppFooter from '@/components/shared/AppFooter.vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/api/axios'

const router = useRouter()
const authStore = useAuthStore()

const activeBank = ref(null)
const pendingRequest = ref(null)
const isLoading = ref(true)

const form = reactive({
  nama_bank: '',
  nomor_rekening: '',
  nama_pemilik: '',
})
const fotoBuku = ref(null)
const errors = reactive({})
const fileInput = ref(null)
const submitting = ref(false)
const toast = ref({ type: '', msg: '' })

const isPending = computed(() => !!pendingRequest.value)

const showToast = (type, msg) => {
  toast.value = { type, msg }
  setTimeout(() => (toast.value = { type: '', msg: '' }), 3500)
}

const triggerFile = () => fileInput.value?.click()

const onFileChange = (e) => {
  const file = e.target.files?.[0]
  if (!file) return
  if (file.size > 5 * 1024 * 1024) {
    showToast('error', 'Ukuran file maksimal 5MB.')
    return
  }
  fotoBuku.value = file
}

const removeFile = () => {
  fotoBuku.value = null
  if (fileInput.value) fileInput.value.value = ''
}

const fetchBank = async () => {
  isLoading.value = true
  try {
    const res = await api.get('/communities/bank-account/history')
    activeBank.value = res.data.data?.active || null
    pendingRequest.value = res.data.data?.pending || null
  } catch (e) {
    if (e.response?.status === 401) router.push('/login')
  } finally {
    isLoading.value = false
  }
}

const handleSubmit = async () => {
  Object.keys(errors).forEach((k) => delete errors[k])

  if (!form.nama_bank) errors.nama_bank = 'Nama bank wajib diisi'
  if (!form.nomor_rekening) errors.nomor_rekening = 'Nomor rekening wajib diisi'
  if (!form.nama_pemilik) errors.nama_pemilik = 'Nama pemilik wajib diisi'
  if (!fotoBuku.value) errors.foto_buku_rekening = 'Foto buku rekening wajib diunggah'
  if (Object.keys(errors).length) return

  submitting.value = true
  try {
    const fd = new FormData()
    fd.append('nama_bank', form.nama_bank)
    fd.append('nomor_rekening', form.nomor_rekening)
    fd.append('nama_pemilik', form.nama_pemilik)
    fd.append('foto_buku_rekening', fotoBuku.value)

    await api.post('/communities/bank-account/change', fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    showToast('success', 'Pengajuan rekening berhasil diajukan.')
    await fetchBank()
    // reset form
    form.nama_bank = ''
    form.nomor_rekening = ''
    form.nama_pemilik = ''
    fotoBuku.value = null
  } catch (e) {
    if (e.response?.status === 401) {
      router.push('/login')
      return
    }
    if (e.response?.status === 422 && e.response.data?.errors) {
      Object.assign(errors, Object.fromEntries(
        Object.entries(e.response.data.errors).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v])
      ))
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
    <AppNavbarKomunitas />

    <main class="flex-1 px-4 py-6">
      <div class="max-w-md mx-auto">
        <!-- Breadcrumb -->
        <nav class="text-xs text-gray-500 mb-4">
          <router-link to="/" class="hover:text-[#8B4513]">Beranda</router-link>
          <span class="mx-1">›</span>
          <router-link to="/community/profile" class="hover:text-[#8B4513]">Profil Komunitas</router-link>
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
              <p class="text-sm text-gray-600">{{ activeBank.nama_pemilik }}</p>
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
              <p><span class="text-gray-500">Bank:</span> {{ pendingRequest?.nama_bank }}</p>
              <p><span class="text-gray-500">No. Rekening:</span> {{ pendingRequest?.nomor_rekening }}</p>
              <p><span class="text-gray-500">Atas Nama:</span> {{ pendingRequest?.nama_pemilik }}</p>
            </div>
          </div>

          <!-- Form Pengajuan -->
          <form v-else @submit.prevent="handleSubmit" class="space-y-4" novalidate>
            <div>
              <label class="block text-xs font-semibold tracking-widest text-gray-500 mb-1">NAMA BANK</label>
              <input
                v-model="form.nama_bank"
                type="text"
                placeholder="Bank BCA"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1a2744]/30"
              />
              <p v-if="errors.nama_bank" class="mt-1 text-xs text-red-500">{{ errors.nama_bank }}</p>
            </div>

            <div>
              <label class="block text-xs font-semibold tracking-widest text-gray-500 mb-1">NOMOR REKENING</label>
              <input
                v-model="form.nomor_rekening"
                type="text"
                placeholder="1234567890"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1a2744]/30"
              />
              <p v-if="errors.nomor_rekening" class="mt-1 text-xs text-red-500">{{ errors.nomor_rekening }}</p>
            </div>

            <div>
              <label class="block text-xs font-semibold tracking-widest text-gray-500 mb-1">NAMA PEMILIK REKENING</label>
              <input
                v-model="form.nama_pemilik"
                type="text"
                placeholder="Komunitas Peduli Bencana"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1a2744]/30"
              />
              <p v-if="errors.nama_pemilik" class="mt-1 text-xs text-red-500">{{ errors.nama_pemilik }}</p>
            </div>

            <!-- Upload -->
            <div>
              <label class="block text-xs font-semibold tracking-widest text-gray-500 mb-1">FOTO BUKU REKENING</label>
              <div
                v-if="!fotoBuku"
                @click="triggerFile"
                class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center cursor-pointer hover:border-[#8B4513] hover:bg-[#8B4513]/5 transition-colors"
              >
                <Upload class="h-5 w-5 mx-auto text-gray-400 mb-1" />
                <p class="text-sm text-gray-700">Upload foto buku rekening</p>
                <p class="text-xs text-gray-500">JPG, PNG — maks 5MB</p>
              </div>
              <div v-else class="flex items-center justify-between border border-gray-200 rounded-lg px-3 py-2">
                <span class="text-sm text-gray-700 truncate">{{ fotoBuku.name }}</span>
                <button
                  type="button"
                  @click="removeFile"
                  class="text-gray-400 hover:text-red-500"
                  aria-label="Hapus file"
                >
                  <X class="h-4 w-4" />
                </button>
              </div>
              <input
                ref="fileInput"
                type="file"
                accept="image/*"
                class="hidden"
                @change="onFileChange"
              />
              <p v-if="errors.foto_buku_rekening" class="mt-1 text-xs text-red-500">{{ errors.foto_buku_rekening }}</p>
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
