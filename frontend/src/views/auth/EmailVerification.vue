<template>
  <div class="verification-page">
    <header class="navbar">
      <div class="brand">BERBAGIVE</div>

      <nav class="nav-menu">
        <a href="/" class="nav-link active">Beranda</a>
        <a href="/campaign" class="nav-link">Campaign</a>
        <a href="/komunitas" class="nav-link">Komunitas</a>
      </nav>

      <div class="nav-right">
        <div class="search-box">
          <span class="search-icon">⌕</span>
          <input type="text" placeholder="Search" />
        </div>

        <a href="/login" class="small-button">Masuk</a>
        <a href="/register" class="small-button">Daftar</a>
      </div>
    </header>

    <main class="verification-main">
      <section class="verification-card">
        <div class="icon-circle">
          <span>✉✓</span>
        </div>

        <h1>Verifikasi Email</h1>

        <p class="description">
          Kami telah mengirimkan link verifikasi ke email kamu
          <br />
          <span>{{ userEmail }}</span>.
        </p>

        <p class="instruction">
          Silakan cek inbox atau folder spam untuk melanjutkan
          <br />
          pendaftaran kamu di Berbagive.
        </p>

        <div class="timer-box">
          <div class="timer-label">
            <span>◷</span>
            <p>LINK VERIFIKASI AKAN DIKIRIM DALAM</p>
          </div>

          <h2>{{ formattedTime }}</h2>

          <div class="progress-track">
            <div class="progress-fill" :style="{ width: progressWidth + '%' }"></div>
          </div>
        </div>

        <p class="resend-text">Belum menerima email?</p>

        <button
          class="resend-button"
          :disabled="loading || countdown > 0"
          @click="handleResend"
        >
          {{ buttonText }}
        </button>

        <p v-if="successMessage" class="success-message">
          {{ successMessage }}
        </p>

        <p v-if="errorMessage" class="error-message">
          *{{ errorMessage }}
        </p>
      </section>
    </main>

    <footer class="footer">
      <div class="footer-left">
        <h3>Berbagive</h3>
        <p>© 2024 Berbagive. Part of The Human Archive project.</p>
      </div>

      <div class="footer-links">
        <a href="/privacy">Kebijakan Privasi</a>
        <a href="/terms">Syarat & Ketentuan</a>
        <a href="/contact">Hubungi Kami</a>
        <a href="/faq">FAQ</a>
      </div>

      <button class="share-button">⌯</button>
    </footer>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from "vue";
import { useRoute } from "vue-router";
import api from "@/api/axios";

const route = useRoute();

const userEmail = ref(route.query.email || localStorage.getItem("verification_email") || "user@email.com");

const countdown = ref(30);
const totalTime = 30;
const loading = ref(false);
const successMessage = ref("");
const errorMessage = ref("");

let timer = null;

const formattedTime = computed(() => {
  const seconds = String(countdown.value).padStart(2, "0");
  return `00:${seconds}`;
});

const progressWidth = computed(() => {
  return (countdown.value / totalTime) * 100;
});

const buttonText = computed(() => {
  if (loading.value) return "Mengirim...";
  if (countdown.value > 0) return "Kirim Ulang Email";
  return "Kirim Ulang Email";
});

function startCountdown() {
  clearInterval(timer);
  countdown.value = 30;

  timer = setInterval(() => {
    if (countdown.value > 0) {
      countdown.value -= 1;
    } else {
      clearInterval(timer);
    }
  }, 1000);
}

async function handleResend() {
  successMessage.value = "";
  errorMessage.value = "";

  if (!userEmail.value || userEmail.value === "user@email.com") {
    errorMessage.value = "Email tidak ditemukan. Silakan registrasi ulang.";
    return;
  }

  try {
    loading.value = true;

    const response = await api.post('/auth/resend-verification', {
      email: userEmail.value,
    });

    successMessage.value =
      response.data?.message || "Email verifikasi baru telah dikirim.";

    startCountdown();
  } catch (error) {
    const status = error.response?.status;
    const message = error.response?.data?.message || error.message || "";

    if (status === 400 || status === 409) {
      errorMessage.value = message || "Akun sudah terverifikasi.";
      return;
    }

    if (status === 429) {
      errorMessage.value =
        "Batas pengiriman ulang email verifikasi telah tercapai.";
      return;
    }

    errorMessage.value =
      message || "Gagal mengirim ulang email verifikasi.";
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  if (route.query.email) {
    localStorage.setItem("verification_email", route.query.email);
  }

  startCountdown();
});

onUnmounted(() => {
  clearInterval(timer);
});
</script>

<style scoped>
* {
  box-sizing: border-box;
}

.verification-page {
  min-height: 100vh;
  background: #eadfce;
  color: #12364a;
  font-family: Inter, Arial, sans-serif;
}

a {
  text-decoration: none;
}

/* NAVBAR */

.navbar {
  width: 100%;
  height: 72px;
  padding: 18px 42px;
  display: flex;
  align-items: center;
  gap: 28px;
  background: #eadfce;
}

.brand {
  font-size: 25px;
  letter-spacing: 4px;
  font-weight: 800;
  color: #b08160;
  margin-right: 10px;
}

.nav-menu {
  display: flex;
  align-items: center;
  gap: 22px;
}

.nav-link {
  min-width: 145px;
  padding: 12px 32px;
  border-radius: 10px;
  text-align: center;
  font-size: 14px;
  font-weight: 700;
  color: white;
  background: #b07f5e;
}

.nav-link.active {
  min-width: 320px;
}

.nav-right {
  margin-left: auto;
  display: flex;
  align-items: center;
  gap: 16px;
}

.search-box {
  width: 380px;
  height: 46px;
  padding: 0 16px;
  border-radius: 24px;
  background: #d6c6b5;
  display: flex;
  align-items: center;
  gap: 10px;
  color: #1f1f1f;
}

.search-icon {
  font-size: 22px;
}

.search-box input {
  width: 100%;
  border: none;
  outline: none;
  background: transparent;
  font-size: 16px;
  color: #2b2b2b;
}

.search-box input::placeholder {
  color: #2d2d2d;
}

.small-button {
  min-width: 90px;
  height: 38px;
  border-radius: 5px;
  background: #b07f5e;
  color: #0e0e0e;
  font-size: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* MAIN */

.verification-main {
  min-height: calc(100vh - 190px);
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 90px 20px 120px;
}

.verification-card {
  width: 555px;
  min-height: 700px;
  padding: 54px 56px 55px;
  border-radius: 32px;
  background: #ffffff;
  box-shadow: 0 24px 60px rgba(115, 95, 73, 0.14);
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.icon-circle {
  width: 78px;
  height: 78px;
  border-radius: 50%;
  background: #fff1df;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 32px;
}

.icon-circle span {
  color: #003e5b;
  font-size: 28px;
  font-weight: 900;
  letter-spacing: -6px;
  transform: translateX(-3px);
}

.verification-card h1 {
  margin: 0 0 20px;
  color: #063f5a;
  font-size: 31px;
  font-weight: 900;
  letter-spacing: -1px;
}

.description {
  margin: 0 0 24px;
  color: #584f4a;
  font-size: 17px;
  line-height: 1.55;
}

.description span {
  color: #1d6d8a;
}

.instruction {
  margin: 0 0 42px;
  color: #584f4a;
  font-size: 17px;
  line-height: 1.55;
}

.timer-box {
  width: 100%;
  min-height: 144px;
  padding: 26px 26px 20px;
  border-radius: 7px;
  background: #fff1df;
  margin-bottom: 42px;
}

.timer-label {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 9px;
  margin-bottom: 12px;
  color: #003e5b;
}

.timer-label span {
  font-size: 18px;
}

.timer-label p {
  margin: 0;
  font-size: 14px;
  font-weight: 800;
  letter-spacing: 0.3px;
}

.timer-box h2 {
  margin: 0 0 18px;
  color: #003e5b;
  font-size: 40px;
  font-weight: 900;
  letter-spacing: 3px;
}

.progress-track {
  width: 100%;
  height: 4px;
  border-radius: 10px;
  background: #eadfce;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  border-radius: 10px;
  background: #003e5b;
  transition: width 0.3s ease;
}

.resend-text {
  margin: 0 0 16px;
  color: #584f4a;
  font-size: 14px;
}

.resend-button {
  width: 100%;
  height: 56px;
  border: none;
  border-radius: 30px;
  background: #003e5b;
  color: #ffffff;
  font-size: 16px;
  font-weight: 800;
  cursor: pointer;
  box-shadow: 0 10px 16px rgba(0, 45, 68, 0.18);
  transition: 0.2s ease;
}

.resend-button:hover:not(:disabled) {
  background: #00344d;
  transform: translateY(-1px);
}

.resend-button:disabled {
  cursor: not-allowed;
  opacity: 0.85;
}

.success-message {
  margin: 16px 0 0;
  color: #176b4d;
  font-size: 14px;
}

.error-message {
  margin: 16px 0 0;
  color: #ff2d2d;
  font-size: 14px;
}

/* FOOTER */

.footer {
  min-height: 118px;
  padding: 28px 48px;
  background: #f2f2f2;
  display: flex;
  align-items: center;
  gap: 30px;
}

.footer-left h3 {
  margin: 0 0 18px;
  font-size: 20px;
  font-style: italic;
  color: #003e5b;
}

.footer-left p {
  margin: 0;
  color: #6f6f6f;
  font-size: 14px;
}

.footer-links {
  margin-left: auto;
  display: flex;
  gap: 36px;
}

.footer-links a {
  color: #6a6a6a;
  font-size: 14px;
}

.share-button {
  margin-left: 70px;
  width: 42px;
  height: 42px;
  border: none;
  border-radius: 50%;
  background: #eadfce;
  font-size: 20px;
  cursor: pointer;
}

@media (max-width: 1100px) {
  .navbar {
    height: auto;
    flex-wrap: wrap;
  }

  .nav-right {
    width: 100%;
    margin-left: 0;
  }

  .search-box {
    flex: 1;
  }

  .nav-link.active {
    min-width: 160px;
  }
}

@media (max-width: 650px) {
  .navbar {
    padding: 18px 20px;
  }

  .nav-menu,
  .nav-right {
    flex-wrap: wrap;
  }

  .nav-link,
  .nav-link.active,
  .search-box,
  .small-button {
    width: 100%;
    min-width: unset;
  }

  .verification-card {
    width: 100%;
    min-height: auto;
    padding: 42px 26px;
  }

  .verification-card h1 {
    font-size: 27px;
  }

  .description,
  .instruction {
    font-size: 15px;
  }

  .timer-box h2 {
    font-size: 34px;
  }

  .timer-label p {
    font-size: 12px;
  }

  .footer {
    flex-direction: column;
    align-items: flex-start;
  }

  .footer-links {
    margin-left: 0;
    flex-wrap: wrap;
    gap: 18px;
  }

  .share-button {
    margin-left: 0;
  }
}
</style>
