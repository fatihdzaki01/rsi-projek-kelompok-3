<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AppFooter from '@/components/AppFooter.vue'

const router = useRouter()
const auth = useAuthStore()

const email = ref('darmanamandala3267@icloud.com')
const password = ref('admin123')
const loading = ref(false)

const showModal = ref(false)
const modalType = ref('success')
const modalTitle = ref('')
const modalMessage = ref('')

const openModal = (type, title, message) => {
  modalType.value = type
  modalTitle.value = title
  modalMessage.value = message
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

const handleLogin = async () => {
  loading.value = true

  try {
    await auth.login(email.value, password.value)

    openModal('success', 'Login berhasil', 'Selamat datang kembali.')

    setTimeout(() => {
      router.push('/dashboard')
    }, 800)
  } catch (error) {
    openModal(
      'error',
      'Login gagal',
      error.response?.data?.message || 'Email atau password salah.'
    )
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <main class="auth-page">
    <header class="auth-navbar">
  <div class="auth-brand">BERBAGIVE</div>

  <nav>
    <span>Beranda</span>
    <span>Campaign</span>
    <span>Komunitas</span>
    <span>Donasi Saya</span>
  </nav>

  <div class="auth-search">
    <span>⌕</span>
    <input type="text" placeholder="Search" />
  </div>

  <div class="auth-icons">
    <span class="user-icon">●</span>
    <span>✉</span>
    <span>↩</span>
  </div>
</header>

    <section class="auth-content">
      <div class="auth-card">
        <div class="auth-visual"></div>

        <div class="auth-form">
          <p class="auth-small-brand">BERBAGIVE</p>
          <h1>Masuk</h1>
          <p class="auth-subtitle">Selamat datang kembali. Silakan akses akun Anda.</p>

          <form @submit.prevent="handleLogin">
            <label>Email</label>
            <input
              v-model="email"
              type="email"
              placeholder="Masukkan email"
              required
            />

            <label>Password</label>
            <input
              v-model="password"
              type="password"
              placeholder="Masukkan password"
              required
            />

            <div class="auth-options">
              <label class="remember">
                <input type="checkbox" />
                Ingat saya
              </label>

              <a href="#">Lupa Password?</a>
            </div>

            <button type="submit" :disabled="loading">
              {{ loading ? 'Memproses...' : 'Masuk' }}
            </button>

            <p class="auth-register">
              Belum punya akun Berbagive?
              <a href="#">Daftar sekarang</a>
            </p>
          </form>
        </div>
      </div>
    </section>

    <AppFooter />

    <div v-if="showModal" class="auth-modal-backdrop">
      <div class="auth-modal">
        <div :class="['modal-icon', modalType]">
          {{ modalType === 'success' ? '✓' : '×' }}
        </div>

        <h2>{{ modalTitle }}</h2>
        <p>{{ modalMessage }}</p>

        <button @click="closeModal">
          {{ modalType === 'success' ? 'Lihat Dashboard' : 'Coba Lagi' }}
        </button>
      </div>
    </div>
  </main>
</template>