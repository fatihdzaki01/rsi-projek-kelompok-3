<template>
  <div class="auth-page">
    <AuthNavbar />

    <main class="auth-main">
      <section class="auth-card">
        <div class="auth-side-panel"></div>

        <div class="auth-center">
          <div class="auth-content">
            <p class="auth-mini-brand">BERBAGIVE</p>

            <h1>Lupa password</h1>
            <p class="auth-subtitle">Silahkan masukan email anda</p>

            <form class="auth-form" @submit.prevent="handleSubmit">
              <div class="auth-form-group">
                <label>Email</label>

                <div :class="['auth-input-wrapper', errors.email ? 'auth-input-error' : '']">
                  <span class="auth-input-icon">♙</span>
                  <input
                    v-model="form.email"
                    type="email"
                    placeholder="nama@email.com"
                    @input="clearError"
                  />
                </div>

                <small v-if="errors.email" class="auth-error-text">
                  {{ errors.email }}
                </small>
              </div>

              <button type="submit" class="auth-submit-button" :disabled="loading">
                {{ loading ? "Mengirim..." : "Send" }}
                <span v-if="!loading" class="auth-arrow">→</span>
              </button>

              <p v-if="globalError" class="auth-global-error">
                *{{ globalError }}
              </p>

              <p v-if="successMessage" class="auth-global-success">
                {{ successMessage }}
              </p>
            </form>

            <p class="auth-register-text">
              Belum punya akun Berbagive?
              <a href="/register">Daftar sekarang</a>
            </p>
          </div>
        </div>

        <div class="auth-side-panel"></div>
      </section>
    </main>

    <AuthFooter />
  </div>
</template>

<script setup>
import { reactive, ref } from "vue";
import { useRouter } from "vue-router";
import api from "@/api/axios";
import AuthNavbar from "../../components/auth/AuthNavbar.vue";
import AuthFooter from "../../components/auth/AuthFooter.vue";

const router = useRouter();

const form = reactive({
  email: "",
});

const errors = reactive({
  email: "",
});

const loading = ref(false);
const globalError = ref("");
const successMessage = ref("");

function clearError() {
  errors.email = "";
  globalError.value = "";
  successMessage.value = "";
}

function validateForm() {
  errors.email = "";
  globalError.value = "";
  successMessage.value = "";

  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  if (!form.email.trim()) {
    errors.email = "Email wajib diisi";
  } else if (!emailPattern.test(form.email)) {
    errors.email = "Format email tidak valid";
  }

  return !errors.email;
}

async function handleSubmit() {
  if (!validateForm()) return;

  try {
    loading.value = true;

    const response = await api.post('/auth/forgot-password', {
      email: form.email,
    });

    successMessage.value =
      response.data?.message || "Link reset password dikirim jika email terdaftar";

    setTimeout(() => {
      router.push({
        path: "/reset-password",
        query: {
          email: form.email,
        },
      });
    }, 800);
  } catch (error) {
    const status = error.response?.status;
    const errData = error.response?.data?.errors || {};
    const message = error.response?.data?.message || error.message || "";

    if (status === 400 || status === 422) {
      if (errData.email) {
        errors.email = Array.isArray(errData.email)
          ? errData.email[0]
          : errData.email;
      } else {
        globalError.value = message || "Format email tidak valid";
      }
      return;
    }

    globalError.value =
      message || "Gagal mengirim link reset password. Silakan coba lagi";
  } finally {
    loading.value = false;
  }
}
</script>
