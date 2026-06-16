<template>
  <div class="register-page">
    <header class="navbar">
      <div class="brand">BERBAGIVE</div>
      <nav class="nav-menu">
        <a href="/" class="nav-link active">Beranda</a>
        <a href="/campaigns" class="nav-link">Campaign</a>
        <a href="/communities" class="nav-link">Komunitas</a>
      </nav>
      <div class="nav-right">
        <a href="/login" class="small-button">Masuk</a>
        <a href="/register" class="small-button">Daftar</a>
      </div>
    </header>

    <main class="register-main">
      <section class="register-card register-card-wide">
        <div class="register-content">
          <h1>Daftar Sebagai Komunitas</h1>
          <p class="subtitle">
            Lengkapi data lembaga Anda untuk mengajukan kampanye penggalangan dana.
          </p>

          <!-- Step indicator -->
          <div class="step-indicator">
            <div :class="['step', step >= 1 ? 'active' : '']">1. Data Akun</div>
            <div :class="['step', step >= 2 ? 'active' : '']">2. Data Lembaga</div>
            <div :class="['step', step >= 3 ? 'active' : '']">3. Dokumen</div>
          </div>

          <form class="register-form" @submit.prevent="handleSubmit">
            <!-- STEP 1: Akun -->
            <div v-if="step === 1">
              <div class="form-group">
                <label>NAMA PIC</label>
                <input v-model="form.nama_pic" type="text" placeholder="Nama penanggung jawab" />
                <small v-if="errors.nama_pic" class="error-text">{{ errors.nama_pic }}</small>
              </div>
              <div class="form-group">
                <label>EMAIL</label>
                <input v-model="form.email" type="email" placeholder="email@komunitas.id" />
                <small v-if="errors.email" class="error-text">{{ errors.email }}</small>
              </div>
              <div class="form-group">
                <label>NOMOR HP</label>
                <input v-model="form.no_hp" type="tel" placeholder="08xxxxxxxxxx" />
                <small v-if="errors.no_hp" class="error-text">{{ errors.no_hp }}</small>
              </div>
              <div class="form-group">
                <label>KATA SANDI</label>
                <input v-model="form.password" type="password" placeholder="Min. 8 karakter" />
                <small v-if="errors.password" class="error-text">{{ errors.password }}</small>
              </div>
              <div class="form-group">
                <label>KONFIRMASI KATA SANDI</label>
                <input v-model="form.password_confirmation" type="password" placeholder="Ulangi kata sandi" />
              </div>
            </div>

            <!-- STEP 2: Lembaga -->
            <div v-if="step === 2">
              <div class="form-group">
                <label>NAMA LEMBAGA</label>
                <input v-model="form.nama_lembaga" type="text" placeholder="Nama resmi lembaga" />
                <small v-if="errors.nama_lembaga" class="error-text">{{ errors.nama_lembaga }}</small>
              </div>
              <div class="form-group">
                <label>JENIS LEMBAGA</label>
                <select v-model="form.jenis_lembaga_id">
                  <option :value="null">Pilih jenis lembaga</option>
                  <option v-for="j in jenisLembagaList" :key="j.id" :value="j.id">{{ j.nama }}</option>
                </select>
              </div>
              <div class="form-group">
                <label>WILAYAH</label>
                <select v-model="form.wilayah_id">
                  <option :value="null">Pilih wilayah</option>
                  <option v-for="w in wilayahList" :key="w.id" :value="w.id">
                    {{ w.nama_kabupaten || w.nama }}
                  </option>
                </select>
              </div>
              <div class="form-group">
                <label>ALAMAT LENGKAP</label>
                <textarea v-model="form.alamat" rows="3" placeholder="Alamat lengkap kantor/sekretariat"></textarea>
              </div>
              <div class="form-group">
                <label>DESKRIPSI SINGKAT</label>
                <textarea v-model="form.deskripsi" rows="3" placeholder="Visi misi, fokus kegiatan, dll"></textarea>
              </div>
            </div>

            <!-- STEP 3: Dokumen -->
            <div v-if="step === 3">
              <p class="info-text">Unggah dokumen sesuai jenis lembaga yang dipilih. Format: PDF/JPG/PNG, maks 5MB per file.</p>
              <div v-for="(jd, idx) in jenisDokumenList" :key="jd.id" class="form-group">
                <label>{{ jd.nama }} <span v-if="jd.wajib" class="required">*</span></label>
                <input type="file" :accept="'.pdf,.jpg,.jpeg,.png'" @change="(e) => handleFile(e, jd.id)" />
                <small v-if="form.dokumen[jd.id]" class="info-text">
                  Terpilih: {{ form.dokumen[jd.id].name }}
                </small>
              </div>
              <div class="form-group">
                <label class="checkbox-label">
                  <input v-model="form.agree" type="checkbox" />
                  Saya menyatakan data yang diisi adalah benar dan menyetujui Syarat &amp; Ketentuan.
                </label>
                <small v-if="errors.agree" class="error-text">{{ errors.agree }}</small>
              </div>
            </div>

            <!-- Navigation -->
            <div class="form-actions">
              <button v-if="step > 1" type="button" class="btn-secondary" @click="step--">Kembali</button>
              <button v-if="step < 3" type="button" class="btn-primary" @click="nextStep">Lanjut</button>
              <button v-else type="submit" class="btn-primary" :disabled="loading">
                {{ loading ? 'Mendaftarkan...' : 'Daftar Sekarang' }}
              </button>
            </div>

            <div v-if="serverError" class="error-banner">{{ serverError }}</div>
            <div v-if="successMessage" class="success-banner">{{ successMessage }}</div>
          </form>

          <p class="auth-footer">
            Sudah memiliki akun? <a href="/login">Login sekarang</a>
          </p>
        </div>
      </section>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api/axios'

const router = useRouter()
const step = ref(1)
const loading = ref(false)
const serverError = ref('')
const successMessage = ref('')

const form = ref({
  nama_pic: '',
  email: '',
  no_hp: '',
  password: '',
  password_confirmation: '',
  nama_lembaga: '',
  jenis_lembaga_id: null,
  wilayah_id: null,
  alamat: '',
  deskripsi: '',
  dokumen: {},
  agree: false,
})

const errors = ref({})
const jenisLembagaList = ref([])
const wilayahList = ref([])
const jenisDokumenList = ref([])

onMounted(async () => {
  try {
    const [jl, wl] = await Promise.all([
      api.get('/jenis-lembaga').catch(() => ({ data: { data: [] } })),
      api.get('/wilayah').catch(() => ({ data: { data: [] } })),
    ])
    jenisLembagaList.value = jl.data.data || jl.data || []
    wilayahList.value = wl.data.data || wl.data || []
  } catch (e) {
    console.warn('Gagal memuat data master:', e.message)
  }
})

async function loadDokumen() {
  if (!form.value.jenis_lembaga_id) return
  try {
    const r = await api.get(`/jenis-dokumen?jenis_lembaga_id=${form.value.jenis_lembaga_id}`)
    jenisDokumenList.value = r.data.data || r.data || []
  } catch (e) {
    jenisDokumenList.value = []
  }
}

function handleFile(e, jenisDokumenId) {
  const file = e.target.files?.[0]
  if (file) form.value.dokumen[jenisDokumenId] = file
}

function validateStep() {
  errors.value = {}
  if (step.value === 1) {
    if (!form.value.nama_pic) errors.value.nama_pic = 'Nama PIC wajib diisi'
    if (!form.value.email) errors.value.email = 'Email wajib diisi'
    if (!form.value.no_hp) errors.value.no_hp = 'Nomor HP wajib diisi'
    if (!form.value.password || form.value.password.length < 8)
      errors.value.password = 'Password minimal 8 karakter'
    if (form.value.password !== form.value.password_confirmation)
      errors.value.password = 'Konfirmasi tidak cocok'
  } else if (step.value === 2) {
    if (!form.value.nama_lembaga) errors.value.nama_lembaga = 'Nama lembaga wajib diisi'
  } else if (step.value === 3) {
    if (!form.value.agree) errors.value.agree = 'Anda harus menyetujui pernyataan'
  }
  return Object.keys(errors.value).length === 0
}

async function nextStep() {
  if (!validateStep()) return
  if (step.value === 2) await loadDokumen()
  step.value++
}

async function handleSubmit() {
  if (!validateStep()) return
  loading.value = true
  serverError.value = ''

  try {
    const fd = new FormData()
    Object.entries(form.value).forEach(([k, v]) => {
      if (k === 'dokumen') {
        Object.entries(v).forEach(([dokId, file]) => fd.append(`dokumen[${dokId}]`, file))
      } else if (v !== null && v !== undefined && k !== 'agree') {
        fd.append(k, v)
      }
    })

    await api.post('/auth/register-komunitas', fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    successMessage.value = 'Pendaftaran berhasil! Mohon tunggu verifikasi dari Superadmin.'
    setTimeout(() => router.push('/login'), 2500)
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors || {}
      serverError.value = e.response.data.message || 'Data tidak valid'
    } else {
      serverError.value = e.response?.data?.message || 'Gagal mendaftar. Coba lagi.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.register-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #f0fdf4, #ecfdf5);
}
.navbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 48px;
  background: white;
  border-bottom: 1px solid #e5e7eb;
}
.brand {
  font-weight: 800;
  font-size: 22px;
  color: #047857;
}
.nav-menu {
  display: flex;
  gap: 24px;
}
.nav-link {
  color: #374151;
  text-decoration: none;
  font-weight: 500;
}
.nav-link.active {
  color: #047857;
}
.nav-right {
  display: flex;
  gap: 8px;
}
.small-button {
  padding: 6px 16px;
  border: 1px solid #047857;
  border-radius: 6px;
  color: #047857;
  text-decoration: none;
  font-size: 14px;
}
.register-main {
  display: flex;
  justify-content: center;
  padding: 32px 16px;
}
.register-card-wide {
  max-width: 640px;
  width: 100%;
  background: white;
  border-radius: 16px;
  padding: 40px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
}
h1 {
  font-size: 28px;
  font-weight: 700;
  color: #064e3b;
  margin: 0 0 8px;
}
.subtitle {
  color: #4b5563;
  margin-bottom: 24px;
}
.step-indicator {
  display: flex;
  gap: 8px;
  margin-bottom: 24px;
  border-bottom: 1px solid #e5e7eb;
  padding-bottom: 12px;
}
.step {
  flex: 1;
  text-align: center;
  font-size: 13px;
  color: #9ca3af;
  font-weight: 600;
}
.step.active {
  color: #047857;
}
.form-group {
  margin-bottom: 16px;
}
.form-group label {
  display: block;
  font-size: 12px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 6px;
  letter-spacing: 0.05em;
}
.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 14px;
}
.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #047857;
  box-shadow: 0 0 0 3px rgba(4, 120, 87, 0.1);
}
.checkbox-label {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  font-size: 13px;
  font-weight: 400;
  color: #4b5563;
}
.required {
  color: #dc2626;
}
.error-text {
  color: #dc2626;
  font-size: 12px;
  margin-top: 4px;
  display: block;
}
.info-text {
  color: #6b7280;
  font-size: 13px;
  margin-bottom: 12px;
}
.form-actions {
  display: flex;
  gap: 8px;
  justify-content: space-between;
  margin-top: 24px;
}
.btn-primary {
  background: #047857;
  color: white;
  border: none;
  padding: 10px 24px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
}
.btn-primary:disabled {
  opacity: 0.6;
}
.btn-secondary {
  background: white;
  color: #374151;
  border: 1px solid #d1d5db;
  padding: 10px 24px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
}
.error-banner {
  margin-top: 16px;
  padding: 10px;
  background: #fef2f2;
  color: #991b1b;
  border-radius: 8px;
  font-size: 14px;
}
.success-banner {
  margin-top: 16px;
  padding: 10px;
  background: #ecfdf5;
  color: #065f46;
  border-radius: 8px;
  font-size: 14px;
}
.auth-footer {
  margin-top: 24px;
  text-align: center;
  color: #6b7280;
  font-size: 14px;
}
.auth-footer a {
  color: #047857;
  font-weight: 600;
}
</style>
