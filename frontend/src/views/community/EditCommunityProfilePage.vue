<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Loader2 } from 'lucide-vue-next'
import Navbar from '@/components/shared/Navbar.vue'
import AppFooter from '@/components/shared/AppFooter.vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/api/axios'

const router = useRouter()
const authStore = useAuthStore()

const form = reactive({
  nama_lembaga: '',
  email: '',
  nomor_kontak: '',
  deskripsi: '',
  alamat_detail: '',
  link_medsos: '',
})

const errors = reactive({})
const logoFile = ref(null)
const logoPreview = ref(null)
const fileInput = ref(null)
const loading = ref(false)
const toast = ref({ type: '', msg: '' })
const profile = ref(null)

const showToast = (type, msg) => {
  toast.value = { type, msg }
  setTimeout(() => (toast.value = { type: '', msg: '' }), 3500)
}

const triggerFile = () => fileInput.value?.click()

const onFileChange = (e) => {
  const file = e.target.files?.[0]
  if (!file) return
  if (file.size > 2 * 1024 * 1024) {
    showToast('error', 'Ukuran file terlalu besar. Maksimal 2MB.')
    return
  }
  logoFile.value = file
  logoPreview.value = URL.createObjectURL(file)
}

const handleSubmit = async () => {
  Object.keys(errors).forEach((k) => delete errors[k])
  loading.value = true

  try {
    const fd = new FormData()
    fd.append('nomor_kontak', form.nomor_kontak || '')
    fd.append('deskripsi', form.deskripsi || '')
    fd.append('alamat_detail', form.alamat_detail || '')
    fd.append('link_medsos', form.link_medsos || '')
    if (logoFile.value) fd.append('foto_lembaga', logoFile.value)
    fd.append('_method', 'PATCH')

    const res = await api.post('/communities/profile', fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    profile.value = res.data.data
    showToast('success', 'Profil komunitas berhasil diperbarui')
    setTimeout(() => router.push('/community/profile'), 800)
  } catch (e) {
    if (e.response?.status === 401) {
      router.push('/login')
      return
    }
    if (e.response?.status === 413) {
      showToast('error', 'Ukuran file terlalu besar. Maksimal 2MB.')
    } else if (e.response?.status === 422 && e.response.data?.errors) {
      Object.assign(errors, Object.fromEntries(
        Object.entries(e.response.data.errors).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v])
      ))
    } else {
      showToast('error', 'Terjadi kesalahan saat menyimpan.')
    }
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  try {
    const res = await api.get('/communities/profile')
    const p = res.data.data
    profile.value = p
    form.nama_lembaga = p?.nama_lembaga || ''
    form.email = p?.email || p?.user?.email || ''
    form.nomor_kontak = p?.nomor_kontak || ''
    form.deskripsi = p?.deskripsi || ''
    form.alamat_detail = p?.alamat_detail || ''
    form.link_medsos = (p?.link_medsos || '').replace(/^@/, '')
    if (p?.foto_lembaga_url) logoPreview.value = p.foto_lembaga_url
  } catch (e) {
    if (e.response?.status === 401) router.push('/login')
  }
})
</script>

<template>
  <div class="min-h-screen bg-[#E8DDD0] flex flex-col">
    <Navbar />

    <main class="flex-1 px-4 py-6">
      <div class="max-w-xl mx-auto">
        <!-- Breadcrumb -->
        <nav class="text-xs text-gray-500 mb-4">
          <router-link to="/" class="hover:text-[#8B4513]">Beranda</router-link>
          <span class="mx-1">›</span>
          <router-link to="/community/profile" class="hover:text-[#8B4513]">Profil Komunitas</router-link>
          <span class="mx-1">›</span>
          <span class="text-[#1a2744] font-medium">Edit Profil</span>
        </nav>

        <div class="bg-white rounded-2xl shadow-sm p-8">
          <h1 class="text-lg font-semibold text-[#1a2744]">Edit Profil Komunitas</h1>
          <p class="text-xs text-gray-500 mb-6">Perubahan akan langsung disimpan ke sistem.</p>

          <!-- Logo upload -->
          <div class="bg-[#FDF5EE] rounded-xl p-4 flex items-center gap-4 mb-6">
            <img
              v-if="logoPreview"
              :src="logoPreview"
              alt="Logo komunitas"
              class="h-14 w-14 rounded-full object-cover shrink-0"
            />
            <div
              v-else
              class="h-14 w-14 rounded-full bg-[#1a2744] text-white font-bold text-xl flex items-center justify-center shrink-0"
            >
              {{ initials }}
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-[#1a2744]">Logo Komunitas</p>
              <p class="text-xs text-gray-500 mb-2">Maks: 2MB • JPG, PNG</p>
              <button
                type="button"
                @click="triggerFile"
                class="border border-[#8B4513] text-[#8B4513] bg-white hover:bg-[#8B4513]/5 text-xs font-medium rounded-md px-3 py-1 transition-colors"
              >
                Ganti Logo
              </button>
              <input
                ref="fileInput"
                type="file"
                accept="image/jpg,image/jpeg,image/png"
                class="hidden"
                @change="onFileChange"
              />
            </div>
          </div>

          <form @submit.prevent="handleSubmit" class="space-y-4" novalidate>
            <!-- Nama Komunitas -->
            <div>
              <label class="block text-xs font-semibold tracking-widest text-gray-500 mb-1">NAMA KOMUNITAS</label>
              <input
                :value="form.nama_lembaga"
                type="text"
                maxlength="100"
                disabled
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-400 cursor-not-allowed"
              />
              <p class="mt-1 text-xs text-gray-400">Perubahan nama organisasi harus diajukan ke superadmin</p>
            </div>

            <!-- Email (disabled) -->
            <div>
              <label class="block text-xs font-semibold tracking-widest text-gray-500 mb-1">EMAIL</label>
              <input
                v-model="form.email"
                type="email"
                disabled
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-400 cursor-not-allowed"
              />
            </div>

            <!-- Nomor Kontak -->
            <div>
              <label class="block text-xs font-semibold tracking-widest text-gray-500 mb-1">NOMOR KONTAK</label>
              <input
                v-model="form.nomor_kontak"
                type="text"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1a2744]/30"
              />
              <p v-if="errors.nomor_kontak" class="mt-1 text-xs text-red-500">{{ errors.nomor_kontak }}</p>
            </div>

            <!-- Deskripsi -->
            <div>
              <label class="block text-xs font-semibold tracking-widest text-gray-500 mb-1">DESKRIPSI</label>
              <textarea
                v-model="form.deskripsi"
                rows="3"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1a2744]/30 resize-none"
              ></textarea>
              <p v-if="errors.deskripsi" class="mt-1 text-xs text-red-500">{{ errors.deskripsi }}</p>
            </div>

            <!-- Visi Misi -->
            <div>
              <label class="block text-xs font-semibold tracking-widest text-gray-500 mb-1">LINK MEDIA SOSIAL</label>
            </div>

            <!-- Alamat -->
            <div>
              <label class="block text-xs font-semibold tracking-widest text-gray-500 mb-1">ALAMAT</label>
              <input
                v-model="form.alamat_detail"
                type="text"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1a2744]/30"
              />
              <p v-if="errors.alamat_detail" class="mt-1 text-xs text-red-500">{{ errors.alamat_detail }}</p>
            </div>

            <!-- Instagram & Website -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-semibold tracking-widest text-gray-500 mb-1">INSTAGRAM</label>
                <input
                    v-model="form.link_medsos"
                    type="text"
                    placeholder="@username atau URL"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1a2744]/30"
                  />
                </div>
                <p v-if="errors.link_medsos" class="mt-1 text-xs text-red-500">{{ errors.link_medsos }}</p>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-wrap gap-2 pt-2">
              <button
                type="submit"
                :disabled="loading"
                class="bg-[#1a2744] hover:bg-[#2a3a5c] text-white text-sm font-medium rounded-lg px-6 py-2 transition-colors flex items-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed"
              >
                <Loader2 v-if="loading" class="h-4 w-4 animate-spin" />
                {{ loading ? 'Menyimpan...' : 'Simpan Perubahan' }}
              </button>
              <button
                type="button"
                :disabled="loading"
                @click="router.back()"
                class="text-gray-600 hover:text-gray-800 text-sm font-medium px-4 py-2 disabled:opacity-60"
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
