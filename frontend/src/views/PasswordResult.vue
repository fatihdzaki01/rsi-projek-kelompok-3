<template>
  <div class="auth-page">
    <AuthNavbar />

    <main class="result-main">
      <section class="result-container">
        <div class="result-card">
          <div :class="['result-icon-circle', isSuccess ? 'result-success-icon' : 'result-error-icon']">
            <span>{{ isSuccess ? "✓" : "×" }}</span>
          </div>

          <h1>{{ title }}</h1>

          <p class="result-description">
            {{ description }}
          </p>

          <button class="result-primary-button" @click="handlePrimaryAction">
            {{ primaryButtonText }}
          </button>

          <button class="result-secondary-button" @click="handleSecondaryAction">
            {{ secondaryText }}
          </button>
        </div>
      </section>
    </main>

    <AuthFooter />
  </div>
</template>

<script setup>
import { computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import AuthNavbar from "../components/AuthNavbar.vue";
import AuthFooter from "../components/AuthFooter.vue";

const route = useRoute();
const router = useRouter();

const status = computed(() => route.query.status || "failed");
const reason = computed(() => route.query.reason || "");

const isSuccess = computed(() => status.value === "success");

const title = computed(() => {
  if (isSuccess.value) return "Update Password Berhasil";
  return "Update Password Gagal";
});

const description = computed(() => {
  if (isSuccess.value) {
    return "Selamat datang kembali!";
  }

  if (reason.value === "expired") {
    return "Link reset password telah kedaluwarsa";
  }

  if (reason.value === "invalid") {
    return "Link reset password tidak valid atau sudah digunakan";
  }

  if (reason.value === "server") {
    return "Server sedang bermasalah. Silakan coba lagi nanti";
  }

  return "Password gagal diperbarui. Silakan coba lagi.";
});

const primaryButtonText = computed(() => {
  if (isSuccess.value) return "Kembali";
  return "Coba Lagi";
});

const secondaryText = computed(() => {
  if (isSuccess.value) return "Lihat Detail Riwayat";
  return "Kembali ke lupa password";
});

function handlePrimaryAction() {
  if (isSuccess.value) {
    router.push("/login");
    return;
  }

  router.push("/reset-password");
}

function handleSecondaryAction() {
  if (isSuccess.value) {
    router.push("/login");
    return;
  }

  router.push("/forgot-password");
}
</script>