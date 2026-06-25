<template>
  <div class="min-h-screen w-full bg-[#E8DDD0] flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-2xl bg-white rounded-2xl shadow-md p-10">
      <p class="text-xs font-semibold tracking-widest text-[#1a2744] mb-6">BERBAGIVE</p>

      <h1 class="text-3xl font-bold text-[#1a2744] mb-1">Daftar Komunitas</h1>
      <p class="text-sm text-[#6B7280] mb-6">Isi data lembaga untuk bergabung di Berbagive.</p>

      <div class="flex items-center gap-3 mb-6 p-3 bg-[#F5F0E8] rounded-lg border border-stone-200">
        <div class="w-8 h-8 rounded-full bg-[#1a2744] flex items-center justify-center text-white text-xs font-bold uppercase">
          {{ loggedInEmail.charAt(0) }}
        </div>
        <div>
          <p class="text-xs text-gray-500">Login sebagai</p>
          <p class="text-sm font-medium text-[#2C2C2C]">{{ loggedInEmail }}</p>
        </div>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-5" novalidate>

        <!-- Nama PIC -->
        <div>
          <label for="nama_pic" class="block text-sm font-medium text-[#374151] mb-1">Nama PIC <span class="text-red-500">*</span></label>
          <input
            id="nama_pic" v-model="form.nama_pic" type="text" placeholder="Nama penanggung jawab"
            class="w-full h-11 px-3 bg-[#F5F0E8] border border-gray-200 rounded-lg text-sm text-gray-700 placeholder-gray-400 outline-none focus:ring-2 focus:ring-[#1a2744]/30"
            :class="{ 'ring-2 ring-red-400': errors.nama_pic }"
          />
          <p v-if="errors.nama_pic" class="mt-1 text-xs text-red-500">{{ errors.nama_pic }}</p>
        </div>

        <!-- Nama Lembaga -->
        <div>
          <label for="nama_lembaga" class="block text-sm font-medium text-[#374151] mb-1">Nama Lembaga</label>
          <input
            id="nama_lembaga" v-model="form.nama_lembaga" type="text" placeholder="Nama lembaga atau komunitas"
            class="w-full h-11 px-3 bg-[#F5F0E8] border border-gray-200 rounded-lg text-sm text-gray-700 placeholder-gray-400 outline-none focus:ring-2 focus:ring-[#1a2744]/30"
            :class="{ 'ring-2 ring-red-400': errors.nama_lembaga }"
          />
          <p v-if="errors.nama_lembaga" class="mt-1 text-xs text-red-500">{{ errors.nama_lembaga }}</p>
        </div>

        <!-- Jenis Lembaga dropdown -->
        <div>
          <label for="jenis_lembaga" class="block text-sm font-medium text-[#374151] mb-1">Jenis Lembaga</label>
          <select
            id="jenis_lembaga" v-model="form.id_jenis_lembaga"
            class="w-full h-11 px-3 bg-[#F5F0E8] border border-gray-200 rounded-lg text-sm text-gray-700 outline-none focus:ring-2 focus:ring-[#1a2744]/30"
            :class="{ 'ring-2 ring-red-400': errors.id_jenis_lembaga }"
          >
            <option value="" disabled>Pilih jenis lembaga</option>
            <option v-for="item in jenisLembagaList" :key="item.id_jenis" :value="item.id_jenis">
              {{ item.nama_jenis }}
            </option>
          </select>
          <p v-if="errors.id_jenis_lembaga" class="mt-1 text-xs text-red-500">{{ errors.id_jenis_lembaga }}</p>
        </div>

        <!-- Wilayah cascading dropdowns -->
        <div>
          <label class="block text-sm font-medium text-[#374151] mb-1">Wilayah</label>
          <div class="grid grid-cols-3 gap-3">
            <select
              v-model="selectedProvince" @change="onProvinceChange"
              class="w-full h-11 px-3 bg-[#F5F0E8] border border-gray-200 rounded-lg text-sm text-gray-700 outline-none focus:ring-2 focus:ring-[#1a2744]/30"
            >
              <option value="">Provinsi</option>
              <option v-for="p in provinces" :key="p.kode" :value="p.kode">{{ p.nama }}</option>
            </select>
            <select
              v-model="selectedKabupaten" @change="onKabupatenChange" :disabled="!selectedProvince"
              class="w-full h-11 px-3 bg-[#F5F0E8] border border-gray-200 rounded-lg text-sm text-gray-700 outline-none focus:ring-2 focus:ring-[#1a2744]/30 disabled:opacity-50"
            >
              <option value="">Kabupaten/Kota</option>
              <option v-for="k in kabupatens" :key="k.kode" :value="k.kode">{{ k.nama }}</option>
            </select>
            <select
              v-model="form.kode_wilayah" :disabled="!selectedKabupaten"
              class="w-full h-11 px-3 bg-[#F5F0E8] border border-gray-200 rounded-lg text-sm text-gray-700 outline-none focus:ring-2 focus:ring-[#1a2744]/30 disabled:opacity-50"
            >
              <option value="">Kecamatan</option>
              <option v-for="c in kecamatans" :key="c.kode" :value="c.kode">{{ c.nama }}</option>
            </select>
          </div>
          <p v-if="errors.kode_wilayah" class="mt-1 text-xs text-red-500">{{ errors.kode_wilayah }}</p>
        </div>

        <!-- Alamat Detail -->
        <div>
          <label for="alamat_detail" class="block text-sm font-medium text-[#374151] mb-1">Alamat Detail</label>
          <input
            id="alamat_detail" v-model="form.alamat_detail" type="text" placeholder="Jalan, RT/RW, nomor, dsb."
            class="w-full h-11 px-3 bg-[#F5F0E8] border border-gray-200 rounded-lg text-sm text-gray-700 placeholder-gray-400 outline-none focus:ring-2 focus:ring-[#1a2744]/30"
            :class="{ 'ring-2 ring-red-400': errors.alamat_detail }"
          />
          <p v-if="errors.alamat_detail" class="mt-1 text-xs text-red-500">{{ errors.alamat_detail }}</p>
        </div>

        <!-- Nomor Kontak + Deskripsi -->
        <div>
          <label for="nomor_kontak" class="block text-sm font-medium text-[#374151] mb-1">Nomor Kontak</label>
          <input
            id="nomor_kontak" v-model="form.nomor_kontak" type="text" placeholder="08xxxxxxxxxx"
            class="w-full h-11 px-3 bg-[#F5F0E8] border border-gray-200 rounded-lg text-sm text-gray-700 placeholder-gray-400 outline-none focus:ring-2 focus:ring-[#1a2744]/30"
            :class="{ 'ring-2 ring-red-400': errors.nomor_kontak }"
          />
          <p v-if="errors.nomor_kontak" class="mt-1 text-xs text-red-500">{{ errors.nomor_kontak }}</p>
        </div>

        <div>
          <label for="deskripsi" class="block text-sm font-medium text-[#374151] mb-1">Deskripsi Lembaga</label>
          <textarea
            id="deskripsi" v-model="form.deskripsi" rows="3" placeholder="Ceritakan tentang lembaga atau komunitas Anda..."
            class="w-full px-3 py-2 bg-[#F5F0E8] border border-gray-200 rounded-lg text-sm text-gray-700 placeholder-gray-400 outline-none focus:ring-2 focus:ring-[#1a2744]/30 resize-none"
          ></textarea>
        </div>

        <!-- Dokumen Section -->
        <div v-if="dokumenList.length" class="pt-2">
          <h3 class="text-sm font-bold text-[#2C2C2C] mb-3 flex items-center gap-2">
            <FileText class="size-4 text-[#8B4513]" /> Dokumen Pendukung
          </h3>
          <p class="text-xs text-gray-500 mb-4">Unggah dokumen yang diperlukan. Format JPG, PNG, atau PDF (maks. 5 MB).</p>

          <div class="space-y-3">
            <div v-for="doc in dokumenList" :key="doc.id_jenis_dok" class="flex items-center gap-3 p-3 bg-[#F5F0E8] rounded-lg">
              <div class="flex items-center gap-2 flex-1 min-w-0">
                <span class="text-sm text-green-600 font-bold flex-shrink-0">☑</span>
                <div class="min-w-0">
                  <p class="text-sm font-medium text-[#2C2C2C] truncate">{{ doc.nama_dokumen }}</p>
                  <p class="text-xs text-gray-400 truncate">{{ doc.deskripsi }}</p>
                </div>
                <span v-if="doc.is_opsional" class="text-[10px] px-1.5 py-0.5 rounded-full bg-gray-200 text-gray-500 flex-shrink-0">opsional</span>
                <span v-else class="text-[10px] px-1.5 py-0.5 rounded-full bg-red-100 text-red-600 flex-shrink-0">wajib</span>
              </div>

              <div class="flex items-center gap-2 flex-shrink-0">
                <!-- Preview -->
                <div v-if="previews[doc.id_jenis_dok]" class="relative w-10 h-10 rounded-lg overflow-hidden bg-white border border-gray-200">
                  <img v-if="isImage(doc.id_jenis_dok)" :src="previews[doc.id_jenis_dok]" class="w-full h-full object-cover" />
                  <div v-else class="w-full h-full flex items-center justify-center text-[8px] text-gray-500">PDF</div>
                </div>

                <!-- Upload button -->
                <label class="flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium bg-white border border-[#8B4513]/30 text-[#8B4513] hover:bg-[#f8e8d8] cursor-pointer transition-colors">
                  <Upload class="size-3" />
                  {{ files[doc.id_jenis_dok] ? 'Ganti' : 'Upload' }}
                  <input type="file" class="hidden" :accept="'.jpg,.jpeg,.png,.pdf'" @change="(e) => onFileChange(doc.id_jenis_dok, e)" />
                </label>
              </div>
            </div>
          </div>
        </div>

        <!-- Global error -->
        <p v-if="globalError" class="text-xs text-red-500 text-center">{{ globalError }}</p>
        <p v-if="success" class="text-sm text-green-700 bg-green-50 border border-green-200 rounded-xl px-3 py-2 text-center">{{ success }}</p>

        <!-- Submit -->
        <button
          type="submit" :disabled="loading"
          class="w-full h-11 bg-[#1a2744] hover:bg-[#2a3754] disabled:opacity-60 disabled:cursor-not-allowed text-white rounded-lg font-medium transition-colors flex items-center justify-center gap-1.5"
        >
          <template v-if="loading">Memproses...</template>
          <template v-else>Daftar Komunitas <span aria-hidden="true" class="text-lg leading-none">→</span></template>
        </button>
      </form>

      <p class="mt-6 text-center text-sm text-gray-600">
        <router-link to="/profile" class="text-[#8B4513] font-semibold hover:underline">← Kembali ke Profil</router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { FileText, Upload } from 'lucide-vue-next'
import api from '@/api/axios'

const router = useRouter()

const loggedInUser = ref(null)
const loggedInEmail = computed(() => loggedInUser.value?.email || '')

const form = ref({
  nama_pic: '',
  nama_lembaga: '',
  id_jenis_lembaga: '',
  kode_wilayah: '',
  alamat_detail: '',
  nomor_kontak: '',
  deskripsi: '',
})

const loading = ref(false)
const success = ref('')
const globalError = ref('')
const errors = ref({})

// Reference data
const jenisLembagaList = ref([])
const provinces = ref([])
const kabupatens = ref([])
const kecamatans = ref([])
const dokumenList = ref([])

// Cascading wilayah state
const selectedProvince = ref('')
const selectedKabupaten = ref('')

// Document upload state
const files = ref({})
const previews = ref({})

onMounted(async () => {
  try {
    const user = JSON.parse(localStorage.getItem('user') || 'null')
    if (!user) {
      router.push('/login')
      return
    }
    loggedInUser.value = user
    form.value.nama_pic = user.nama_lengkap || ''

    const [jl, prov] = await Promise.all([
      api.get('/jenis-lembaga'),
      api.get('/wilayah?level=1'),
    ])
    jenisLembagaList.value = jl.data.data || []
    provinces.value = prov.data.data || []
  } catch {
    router.push('/login')
  }
})

async function onProvinceChange() {
  selectedKabupaten.value = ''
  form.value.kode_wilayah = ''
  kabupatens.value = []
  kecamatans.value = []
  if (!selectedProvince.value) return
  try {
    const res = await api.get(`/wilayah?parent=${selectedProvince.value}`)
    kabupatens.value = res.data.data || []
  } catch {}
}

async function onKabupatenChange() {
  form.value.kode_wilayah = ''
  kecamatans.value = []
  if (!selectedKabupaten.value) return
  try {
    const res = await api.get(`/wilayah?parent=${selectedKabupaten.value}`)
    kecamatans.value = res.data.data || []
  } catch {}
}

// Fetch dokumen when jenis lembaga changes
watch(() => form.value.id_jenis_lembaga, async (val) => {
  files.value = {}
  previews.value = {}
  dokumenList.value = []
  if (!val) return
  try {
    const res = await api.get(`/jenis-dokumen?jenis_lembaga_id=${val}`)
    dokumenList.value = res.data.data || []
  } catch {}
})

function isImage(idJenisDok) {
  const file = files.value[idJenisDok]
  if (!file) return false
  return file.type.startsWith('image/')
}

function onFileChange(idJenisDok, event) {
  const file = event.target.files[0]
  if (!file) return
  files.value[idJenisDok] = file
  const reader = new FileReader()
  reader.onload = (e) => { previews.value[idJenisDok] = e.target.result }
  reader.readAsDataURL(file)
}

async function handleSubmit() {
  errors.value = {}
  globalError.value = ''
  success.value = ''

  const missingDocs = dokumenList.value
    .filter(d => !d.is_opsional && !files.value[d.id_jenis_dok])
    .map(d => d.nama_dokumen)

  if (missingDocs.length) {
    globalError.value = `Dokumen wajib belum lengkap: ${missingDocs.join(', ')}`
    return
  }

  loading.value = true
  try {
    const fd = new FormData()
    fd.append('nama_pic', form.value.nama_pic)
    fd.append('nama_lembaga', form.value.nama_lembaga)
    fd.append('id_jenis_lembaga', form.value.id_jenis_lembaga)
    fd.append('kode_wilayah', form.value.kode_wilayah)
    fd.append('alamat_detail', form.value.alamat_detail)
    fd.append('nomor_kontak', form.value.nomor_kontak)
    if (form.value.deskripsi) fd.append('deskripsi', form.value.deskripsi)

    for (const [idJenisDok, file] of Object.entries(files.value)) {
      fd.append(`dokumen[${idJenisDok}]`, file)
    }

    const res = await api.post('/users/me/register-komunitas', fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    const result = res.data.data || res.data
    const updatedUser = {
      ...loggedInUser.value,
      role: result.role || 'KOMUNITAS',
    }
    localStorage.setItem('user', JSON.stringify(updatedUser))

    success.value = 'Registrasi komunitas berhasil! Mengalihkan...'
    setTimeout(() => router.push('/communities/dashboard'), 2000)
  } catch (e) {
    const status = e.response?.status
    const errData = e.response?.data?.errors || {}
    const message = e.response?.data?.message || ''

    if (status === 401) {
      globalError.value = 'Sesi login habis. Silakan login ulang.'
      setTimeout(() => router.push('/login'), 2000)
    } else if (status === 403) {
      globalError.value = message || 'Hanya akun Donatur yang dapat mendaftar'
    } else if (status === 409) {
      globalError.value = message || 'Anda sudah terdaftar sebagai Komunitas'
    } else if (status === 422) {
      errors.value = Object.fromEntries(
        Object.entries(errData).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v])
      )
    } else {
      globalError.value = message || 'Terjadi kesalahan. Silakan coba lagi.'
    }
  } finally {
    loading.value = false
  }
}
</script>
