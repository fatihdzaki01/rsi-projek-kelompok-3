<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/services/api'
import NavBar from '@/components/NavBar.vue'
import AppFooter from '@/components/AppFooter.vue'

const profile = ref(null)
const loading = ref(true)
const errorMessage = ref('')

const mode = ref('display')
const form = ref({
  nama_lengkap: '',
  foto_profil_url: '',
})
const saving = ref(false)
const validationErrors = ref({})
const successMessage = ref('')

const passwordForm = ref({
  old_password: '',
  new_password: '',
  new_password_confirmation: '',
})
const passwordErrors = ref({})

const showModal = ref(false)

const closeModal = () => {
  showModal.value = false
  successMessage.value = ''
}

const fetchProfile = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await api.get('/superadmin/profile')
    profile.value = response.data.data
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || 'Gagal memuat profil.'
  } finally {
    loading.value = false
  }
}

const cancelEdit = () => {
  mode.value = 'display'
  validationErrors.value = {}
  passwordErrors.value = {}
  passwordForm.value = {
    old_password: '',
    new_password: '',
    new_password_confirmation: '',
  }
}

const handleSave = async () => {
  saving.value = true
  validationErrors.value = {}
  passwordErrors.value = {}

  const hasPassword =
    passwordForm.value.old_password ||
    passwordForm.value.new_password ||
    passwordForm.value.new_password_confirmation

  try {
    const response = await api.patch('/superadmin/profile', {
      nama_lengkap: form.value.nama_lengkap,
      foto_profil_url: form.value.foto_profil_url || null,
    })

    profile.value = response.data.data

    if (hasPassword) {
      await api.patch('/superadmin/profile/password', {
        old_password: passwordForm.value.old_password,
        new_password: passwordForm.value.new_password,
        new_password_confirmation: passwordForm.value.new_password_confirmation,
      })
    }

    successMessage.value = 'Profil berhasil diperbarui.'
    showModal.value = true
    mode.value = 'display'
    passwordForm.value = {
      old_password: '',
      new_password: '',
      new_password_confirmation: '',
    }
  } catch (error) {
    if (error.response?.status === 422 && error.response?.data?.errors) {
      const errors = error.response.data.errors
      if (
        errors.old_password ||
        errors.new_password ||
        errors.new_password_confirmation
      ) {
        passwordErrors.value = errors
      } else {
        validationErrors.value = errors
      }
    } else {
      validationErrors.value = {
        _general: [
          error.response?.data?.message || 'Gagal menyimpan data.',
        ],
      }
    }
  } finally {
    saving.value = false
  }
}

const openEdit = () => {
  form.value = {
    nama_lengkap: profile.value?.nama_lengkap || '',
    foto_profil_url: profile.value?.foto_profil_url || '',
  }
  validationErrors.value = {}
  passwordErrors.value = {}
  passwordForm.value = {
    old_password: '',
    new_password: '',
    new_password_confirmation: '',
  }
  mode.value = 'edit'
}

onMounted(fetchProfile)
</script>

<template>
  <main class="dashboard-page">
    <NavBar />

    <section class="container">
      <div class="page-title">
        <div>
          <h1>Profil Superadmin</h1>
          <p>Kelola data profil superadmin.</p>
        </div>

        <RouterLink to="/dashboard" class="back-link">Kembali Dashboard</RouterLink>
      </div>

      <section v-if="loading" class="card">Memuat profil...</section>

      <section v-else-if="errorMessage" class="card error">
        {{ errorMessage }}
      </section>

      <template v-else>
        <section class="profile-layout">
          <div class="profile-avatar-col">
            <img
              v-if="profile.foto_profil_url"
              :src="profile.foto_profil_url"
              alt="Foto profil"
              class="profile-avatar"
            />
          </div>

          <div class="profile-detail-col">
            <div v-if="mode === 'display'" class="profile-detail">
              <div class="profile-field">
                <strong>Nama Lengkap</strong>
                <span>{{ profile.nama_lengkap }}</span>
              </div>

              <div class="profile-field">
                <strong>Email</strong>
                <span>{{ profile.email }}</span>
              </div>

              <div class="profile-field">
                <strong>Username</strong>
                <span>{{ profile.username }}</span>
              </div>

              <div class="profile-field">
                <strong>Role</strong>
                <span>{{ profile.role }}</span>
              </div>

              <div class="profile-field">
                <strong>Status</strong>
                <span>{{ profile.is_active ? 'Aktif' : 'Nonaktif' }}</span>
              </div>

              <div class="profile-actions">
                <button class="edit-btn" @click="openEdit">Edit Profil</button>
              </div>
            </div>

            <div v-else class="profile-edit">
              <div class="profile-field">
                <label for="nama_lengkap">Nama Lengkap</label>
                <input
                  id="nama_lengkap"
                  v-model="form.nama_lengkap"
                  type="text"
                  placeholder="Masukkan nama lengkap"
                  :class="{ 'input-error': validationErrors.nama_lengkap }"
                />
                <small
                  v-if="validationErrors.nama_lengkap"
                  class="field-error"
                >
                  {{ validationErrors.nama_lengkap[0] }}
                </small>
              </div>

              <div class="profile-field">
                <label for="foto_profil_url">URL Foto Profil</label>
                <input
                  id="foto_profil_url"
                  v-model="form.foto_profil_url"
                  type="url"
                  placeholder="https://example.com/foto.jpg"
                  :class="{ 'input-error': validationErrors.foto_profil_url }"
                />
                <small
                  v-if="validationErrors.foto_profil_url"
                  class="field-error"
                >
                  {{ validationErrors.foto_profil_url[0] }}
                </small>
              </div>

              <small
                v-if="validationErrors._general || passwordErrors._general"
                class="field-error general-error"
              >
                {{ validationErrors._general?.[0] || passwordErrors._general?.[0] }}
              </small>

              <hr class="profile-divider" />

              <h3>Ubah Password</h3>

              <div class="profile-field">
                <label for="old_password">Password Lama</label>
                <input
                  id="old_password"
                  v-model="passwordForm.old_password"
                  type="password"
                  placeholder="Masukkan password lama"
                  :class="{ 'input-error': passwordErrors.old_password }"
                />
                <small
                  v-if="passwordErrors.old_password"
                  class="field-error"
                >
                  {{ passwordErrors.old_password[0] }}
                </small>
              </div>

              <div class="profile-field">
                <label for="new_password">Password Baru</label>
                <input
                  id="new_password"
                  v-model="passwordForm.new_password"
                  type="password"
                  placeholder="Minimal 8 karakter"
                  :class="{ 'input-error': passwordErrors.new_password }"
                />
                <small
                  v-if="passwordErrors.new_password"
                  class="field-error"
                >
                  {{ passwordErrors.new_password[0] }}
                </small>
              </div>

              <div class="profile-field full-width">
                <label for="new_password_confirmation">Konfirmasi Password Baru</label>
                <input
                  id="new_password_confirmation"
                  v-model="passwordForm.new_password_confirmation"
                  type="password"
                  placeholder="Ulangi password baru"
                  :class="{ 'input-error': passwordErrors.new_password_confirmation }"
                />
                <small
                  v-if="passwordErrors.new_password_confirmation"
                  class="field-error"
                >
                  {{ passwordErrors.new_password_confirmation[0] }}
                </small>
              </div>

              <div class="profile-actions">
                <button class="save-btn" :disabled="saving" @click="handleSave">
                  {{ saving ? 'Menyimpan...' : 'Simpan' }}
                </button>
                <button class="cancel-btn" :disabled="saving" @click="cancelEdit">
                  Batal
                </button>
              </div>
            </div>
          </div>
        </section>
      </template>
    </section>

    <AppFooter />

    <div v-if="showModal" class="auth-modal-backdrop" @click.self="closeModal">
      <div class="auth-modal">
        <div class="modal-icon success">✓</div>
        <h2>Berhasil</h2>
        <p>{{ successMessage }}</p>
        <button @click="closeModal">Tutup</button>
      </div>
    </div>
  </main>
</template>
