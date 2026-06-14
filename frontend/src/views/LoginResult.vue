<template>
  <div class="result-page">
    <main class="result-main">
      <section class="result-container">
        <div class="result-card">
          <div :class="['icon-circle', isSuccess ? 'success-icon' : 'error-icon']">
            <span>{{ isSuccess ? "✓" : "×" }}</span>
          </div>

          <h1>{{ title }}</h1>

          <p class="description">
            {{ description }}
          </p>

          <button class="primary-button" @click="handlePrimaryAction">
            {{ primaryButtonText }}
          </button>

          <button class="secondary-button" @click="handleSecondaryAction">
            {{ secondaryText }}
          </button>
        </div>
      </section>

      <p class="copyright">
        © 2024 BERBAGIVE. PART OF THE HUMAN ARCHIVE PROJECT.
        <br />
        TERLINDUNGI OLEH RECAPTCHA DAN PRIVASI GOOGLE.
      </p>
    </main>
  </div>
</template>

<script setup>
import { computed } from "vue";
import { useRoute, useRouter } from "vue-router";

const route = useRoute();
const router = useRouter();

const status = computed(() => route.query.status || "failed");
const reason = computed(() => route.query.reason || "");

const isSuccess = computed(() => status.value === "success");

const title = computed(() => {
  if (isSuccess.value) return "Login berhasil";
  return "Login gagal";
});

const description = computed(() => {
  if (isSuccess.value) {
    return "Selamat datang kembali!";
  }

  if (reason.value === "wrong_credentials") {
    return "Email atau kata sandi yang kamu masukkan salah.";
  }

  if (reason.value === "verification") {
    return "Akun dalam proses verifikasi";
  }

  if (reason.value === "inactive") {
    return "Akun sedang tidak aktif";
  }

  if (reason.value === "locked") {
    return "Akun dikunci sementara selama 15 menit";
  }

  if (reason.value === "server") {
    return "Server sedang bermasalah. Silakan coba lagi nanti.";
  }

  return "Login gagal. Silakan coba lagi.";
});

const primaryButtonText = computed(() => {
  if (isSuccess.value) return "Kembali";
  return "Coba Lagi";
});

const secondaryText = computed(() => {
  if (isSuccess.value) return "Lihat Detail Riwayat";
  return "Lupa kata sandi?";
});

function handlePrimaryAction() {
  if (isSuccess.value) {
    router.push("/dashboard");
    return;
  }

  router.push("/login");
}

function handleSecondaryAction() {
  if (isSuccess.value) {
    router.push("/dashboard");
    return;
  }

  router.push("/forgot-password");
}
</script>

<style scoped>
* {
  box-sizing: border-box;
}

.result-page {
  min-height: 100vh;
  background: #e7dccb;
  font-family: Inter, Arial, sans-serif;
  color: #1f2937;
}

.result-main {
  min-height: 100vh;
  padding: 85px 0 30px;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.result-container {
  width: 86%;
  min-height: 855px;
  border-radius: 32px;
  background: #fff2df;
  display: flex;
  align-items: center;
  justify-content: center;
}

.result-card {
  width: 460px;
  min-height: 385px;
  padding: 40px;
  border-radius: 30px;
  background: #ffffff;
  box-shadow: 0 18px 25px rgba(92, 74, 55, 0.16);
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.icon-circle {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: #eadfce;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 22px;
}

.icon-circle span {
  font-size: 34px;
  font-weight: 500;
  line-height: 1;
}

.success-icon span {
  color: #a7652c;
}

.error-icon span {
  color: #e33434;
}

.result-card h1 {
  margin: 0 0 14px;
  font-size: 30px;
  font-weight: 800;
  color: #1f2937;
  letter-spacing: -0.5px;
}

.description {
  margin: 0 0 46px;
  min-height: 24px;
  color: #6d7482;
  font-size: 18px;
  line-height: 1.5;
}

.primary-button {
  width: 380px;
  height: 61px;
  border: none;
  border-radius: 30px;
  background: #003e5b;
  color: #ffffff;
  font-size: 18px;
  font-weight: 800;
  cursor: pointer;
  box-shadow: 0 10px 16px rgba(0, 45, 68, 0.22);
  transition: 0.2s ease;
}

.primary-button:hover {
  background: #00344d;
  transform: translateY(-1px);
}

.secondary-button {
  margin-top: 22px;
  border: none;
  background: transparent;
  color: #6d7482;
  font-size: 14px;
  cursor: pointer;
}

.secondary-button:hover {
  color: #003e5b;
}

.copyright {
  margin: 22px 0 0;
  text-align: center;
  color: #9b9186;
  font-size: 11px;
  letter-spacing: 1.4px;
  line-height: 1.7;
}

@media (max-width: 768px) {
  .result-main {
    padding: 35px 14px 24px;
  }

  .result-container {
    width: 100%;
    min-height: 720px;
  }

  .result-card {
    width: 90%;
    padding: 35px 24px;
  }

  .primary-button {
    width: 100%;
  }

  .description {
    font-size: 16px;
  }
}
</style>
