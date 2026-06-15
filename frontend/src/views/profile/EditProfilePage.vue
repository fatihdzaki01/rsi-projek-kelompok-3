<script setup>
import { ref, reactive, computed } from 'vue'
import { useRouter } from 'vue-router'
import Navbar from '@/components/shared/Navbar.vue'
import Footer from '@/components/shared/Footer.vue'

const router = useRouter()
const fileInput = ref(null)
const photoPreview = ref(null)

const form = reactive({
  nama: 'Aditya Pratama',
  email: 'aditya@gmail.com',
  telepon: '0812-3456-7890',
  jenisKelamin: 'Laki-laki',
  tanggalLahir: '1995-03-15',
  kotaDomisili: 'Jakarta Selatan',
})

const initials = computed(() =>
  form.nama.split(' ').slice(0, 2).map((n) => n[0]).join('').toUpperCase()
)

const handlePhotoChange = (e) => {
  const file = e.target.files[0]
  if (file) photoPreview.value = URL.createObjectURL(file)
}

const handleSubmit = () => {
  // simulate save
  alert('Perubahan berhasil disimpan')
}

const handleCancel = () => router.back()

const labelCls = 'block text-xs font-semibold text-[#9CA3AF] uppercase tracking-wider mb-1'
const inputCls = 'w-full h-10 px-3 bg-white border border-gray-200 rounded-lg text-sm text-[#374151] focus:outline-none focus:border-[#8B4513]'
</script>

<template>
  <div class="min-h-screen flex flex-col bg-[#E8DDD0]">
    <Navbar />

    <main class="flex-1 py-10 px-4">
      <p class="max-w-xl mx-auto text-xs text-[#9CA3AF] mb-4">
        Beranda / Profil User / <span class="font-medium text-[#1a2744]">Edit Profil</span>
      </p>

      <div class="max-w-xl mx-auto bg-white rounded-2xl shadow-sm p-6">
        <h1 class="text-lg font-bold text-[#1a2744]">Edit Profil</h1>
        <p class="text-xs text-[#9CA3AF] mb-4">Perubahan akan langsung disimpan ke sistem</p>

        <!-- Foto Profil -->
        <div class="bg-[#FDF0E8] rounded-xl p-4 flex items-center gap-4 mb-5">
          <div class="w-12 h-12 bg-[#1a2744] rounded-full flex items-center justify-center overflow-hidden shrink-0">
            <img v-if="photoPreview" :src="photoPreview" class="w-full h-full object-cover" alt="Foto profil" />
            <span v-else class="text-white text-sm font-bold">{{ initials }}</span>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-[#374151]">Foto Profil</p>
            <p class="text-xs text-[#9CA3AF]">Maks. 2MB • JPG, PNG</p>
          </div>
          <button
            type="button"
            @click="fileInput.click()"
            class="border border-[#8B4513] text-[#8B4513] rounded-lg px-3 h-8 text-xs hover:bg-[#FDF0E8] transition-colors"
          >
            Ganti Foto
          </button>
          <input ref="fileInput" type="file" accept=".jpg,.jpeg,.png" class="hidden" @change="handlePhotoChange" />
        </div>

        <!-- Form -->
        <form @submit.prevent="handleSubmit" class="space-y-3">
          <div>
            <label :class="labelCls">Nama Lengkap</label>
            <input v-model="form.nama" type="text" :class="inputCls" />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label :class="labelCls">Email</label>
              <input v-model="form.email" type="email" :class="inputCls" />
            </div>
            <div>
              <label :class="labelCls">Nomor Telepon</label>
              <input v-model="form.telepon" type="tel" :class="inputCls" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label :class="labelCls">Jenis Kelamin</label>
              <select v-model="form.jenisKelamin" :class="inputCls">
                <option>Laki-laki</option>
                <option>Perempuan</option>
              </select>
            </div>
            <div>
              <label :class="labelCls">Tanggal Lahir</label>
              <input v-model="form.tanggalLahir" type="date" :class="inputCls" />
            </div>
          </div>

          <div>
            <label :class="labelCls">Kota Domisili</label>
            <input v-model="form.kotaDomisili" type="text" :class="inputCls" />
          </div>

          <div class="flex gap-3 mt-6">
            <button type="submit" class="bg-[#1a2744] text-white rounded-lg px-5 h-10 text-sm font-medium hover:bg-[#2a3754] transition-colors">
              Simpan Perubahan
            </button>
            <button type="button" @click="handleCancel" class="bg-white border border-gray-300 text-gray-500 rounded-lg px-5 h-10 text-sm hover:bg-gray-50 transition-colors">
              Batal
            </button>
          </div>
        </form>
      </div>
    </main>

    <Footer />
  </div>
</template>