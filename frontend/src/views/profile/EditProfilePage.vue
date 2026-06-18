<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api/axios'
import Navbar from '@/components/shared/Navbar.vue'
import Footer from '@/components/shared/Footer.vue'

const router = useRouter()
const fileInput = ref(null)
const photoPreview = ref(null)
const loading = ref(true)
const submitting = ref(false)
const fetchError = ref('')
const success = ref('')
const errorMsg = ref('')
const errors = ref({})
const profile = ref(null)

const form = reactive({
  nama_lengkap: '',
  email: '',
  nomor_telepon: '',
  jenis_kelamin: '',
  tanggal_lahir: '',
  kode_wilayah: '',
})

const initials = computed(() => {
  const name = form.nama_lengkap || profile.value?.nama_lengkap || ''
  return name.split(' ').slice(0, 2).map((n) => n[0]).join('').toUpperCase()
})

const fotoProfilUrl = computed(() => {
  if (photoPreview.value) return photoPreview.value
  return profile.value?.foto_profil_url || null
})

const handlePhotoChange = (e) => {
  const file = e.target.files[0]
  if (file) photoPreview.value = URL.createObjectURL(file)
}

async function fetchProfile() {
  loading.value = true
  fetchError.value = ''
  try {
    const res = await api.get('/users/me')
    profile.value = res.data.data || res.data
    form.nama_lengkap = profile.value.nama_lengkap || ''
    form.email = profile.value.email || ''
    form.nomor_telepon = profile.value.nomor_telepon || ''
    form.jenis_kelamin = profile.value.jenis_kelamin || ''
    form.tanggal_lahir = profile.value.tanggal_lahir || ''
    form.kode_wilayah = profile.value.kode_wilayah || ''
  } catch (e) {
    fetchError.value = e.response?.data?.message || 'Gagal memuat profil'
  } finally {
    loading.value = false
  }
}

async function handleSubmit() {
  errors.value = {}
  success.value = ''
  errorMsg.value = ''
  submitting.value = true
  try {
    const fd = new FormData()
    fd.append('nama_lengkap', form.nama_lengkap)
    fd.append('nomor_telepon', form.nomor_telepon || '')
    fd.append('jenis_kelamin', form.jenis_kelamin || '')
    fd.append('tanggal_lahir', form.tanggal_lahir || '')
    fd.append('kode_wilayah', form.kode_wilayah || '')
    if (fileInput.value?.files?.[0]) {
      fd.append('foto_profil', fileInput.value.files[0])
    }
    fd.append('_method', 'PATCH')

    await api.post('/users/me', fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    success.value = 'Profil berhasil diperbarui'
    await fetchProfile()
  } catch (e) {
    const status = e.response?.status
    const errData = e.response?.data?.errors || {}
    const message = e.response?.data?.message || ''
    if (status === 400 || status === 422) {
      errors.value = Object.fromEntries(
        Object.entries(errData).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v])
      )
      if (Object.keys(errors.value).length === 0) {
        errorMsg.value = message || 'Data tidak valid'
      }
    } else {
      errorMsg.value = message || 'Gagal memperbarui profil'
    }
  } finally {
    submitting.value = false
  }
}

const handleCancel = () => router.back()

const labelCls = 'block text-xs font-semibold text-[#9CA3AF] uppercase tracking-wider mb-1'
const inputCls = 'w-full h-10 px-3 bg-white border border-gray-200 rounded-lg text-sm text-[#374151] focus:outline-none focus:border-[#8B4513]'

onMounted(fetchProfile)
</script>

<template>
  <div class="min-h-screen flex flex-col bg-[#E8DDD0]">
    <Navbar />

    <main class="flex-1 py-10 px-4">
      <p class="max-w-xl mx-auto text-xs text-[#9CA3AF] mb-4">
        Beranda / Profil User / <span class="font-medium text-[#1a2744]">Edit Profil</span>
      </p>

      <div v-if="loading" class="max-w-xl mx-auto bg-white rounded-2xl shadow-sm p-6 text-center">
        <div class="w-8 h-8 border-2 border-[#8B4513] border-t-transparent rounded-full animate-spin mx-auto mb-3" />
        <p class="text-xs text-gray-400">Memuat profil...</p>
      </div>

      <div v-else-if="fetchError" class="max-w-xl mx-auto bg-white rounded-2xl shadow-sm p-6 text-center">
        <p class="text-sm text-red-500 mb-4">{{ fetchError }}</p>
        <button @click="fetchProfile" class="px-5 py-2 bg-[#8B4513] text-white rounded-lg text-sm font-medium">Coba Lagi</button>
      </div>

      <div v-else class="max-w-xl mx-auto bg-white rounded-2xl shadow-sm p-6">
        <h1 class="text-lg font-bold text-[#1a2744]">Edit Profil</h1>
        <p class="text-xs text-[#9CA3AF] mb-4">Perubahan akan langsung disimpan ke sistem</p>

        <div v-if="success" class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ success }}</div>
        <div v-if="errorMsg" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-600">{{ errorMsg }}</div>

        <!-- Foto Profil -->
        <div class="bg-[#FDF0E8] rounded-xl p-4 flex items-center gap-4 mb-5">
          <div class="w-12 h-12 bg-[#1a2744] rounded-full flex items-center justify-center overflow-hidden shrink-0">
            <img v-if="fotoProfilUrl" :src="fotoProfilUrl" class="w-full h-full object-cover" alt="Foto profil" />
            <span v-else class="text-white text-sm font-bold">{{ initials }}</span>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-[#374151]">Foto Profil</p>
            <p class="text-xs text-[#9CA3AF]">Maks. 2MB • JPG, PNG</p>
          </div>
          <button type="button" @click="fileInput.click()" class="border border-[#8B4513] text-[#8B4513] rounded-lg px-3 h-8 text-xs hover:bg-[#FDF0E8] transition-colors">Ganti Foto</button>
          <input ref="fileInput" type="file" accept=".jpg,.jpeg,.png" class="hidden" @change="handlePhotoChange" />
        </div>

        <!-- Form -->
        <form @submit.prevent="handleSubmit" class="space-y-3">
          <div>
            <label :class="labelCls">Nama Lengkap</label>
            <input v-model="form.nama_lengkap" type="text" :class="inputCls" />
            <p v-if="errors.nama_lengkap" class="mt-1 text-xs text-red-500">{{ errors.nama_lengkap }}</p>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label :class="labelCls">Email</label>
              <input :value="form.email" type="email" :class="inputCls" disabled class="w-full h-10 px-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-400 cursor-not-allowed" />
            </div>
            <div>
              <label :class="labelCls">Nomor Telepon</label>
              <input v-model="form.nomor_telepon" type="tel" :class="inputCls" />
              <p v-if="errors.nomor_telepon" class="mt-1 text-xs text-red-500">{{ errors.nomor_telepon }}</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label :class="labelCls">Jenis Kelamin</label>
              <select v-model="form.jenis_kelamin" :class="inputCls">
                <option value="">Pilih</option>
                <option value="L">Laki-laki</option>
                <option value="P">Perempuan</option>
              </select>
              <p v-if="errors.jenis_kelamin" class="mt-1 text-xs text-red-500">{{ errors.jenis_kelamin }}</p>
            </div>
            <div>
              <label :class="labelCls">Tanggal Lahir</label>
              <input v-model="form.tanggal_lahir" type="date" :class="inputCls" />
              <p v-if="errors.tanggal_lahir" class="mt-1 text-xs text-red-500">{{ errors.tanggal_lahir }}</p>
            </div>
          </div>

          <div>
            <label :class="labelCls">Kode Wilayah</label>
            <input v-model="form.kode_wilayah" type="text" :class="inputCls" placeholder="Contoh: 32.01" />
            <p v-if="errors.kode_wilayah" class="mt-1 text-xs text-red-500">{{ errors.kode_wilayah }}</p>
          </div>

          <div class="flex gap-3 mt-6">
            <button type="submit" :disabled="submitting" class="bg-[#1a2744] text-white rounded-lg px-5 h-10 text-sm font-medium hover:bg-[#2a3754] transition-colors disabled:opacity-50">
              {{ submitting ? 'Menyimpan...' : 'Simpan Perubahan' }}
            </button>
            <button type="button" @click="handleCancel" class="bg-white border border-gray-300 text-gray-500 rounded-lg px-5 h-10 text-sm hover:bg-gray-50 transition-colors">Batal</button>
          </div>
        </form>
      </div>
    </main>

    <Footer />
  </div>
</template>
