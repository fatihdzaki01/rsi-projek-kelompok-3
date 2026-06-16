<template>
  <div class="auth-page">
    <AuthNavbar />

    <main class="auth-main">
      <section class="auth-card">
        <div class="auth-side-panel"></div>

        <div class="auth-center">
          <div class="auth-content">
            <p class="auth-mini-brand">BERBAGIVE</p>

            <h1>Perbarui password</h1>
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
                    @input="clearFieldError('email')"
                  />
                </div>

                <small v-if="errors.email" class="auth-error-text">
                  {{ errors.email }}
                </small>
              </div>

              <div class="auth-form-group">
                <label>New Password</label>

                <div :class="['auth-input-wrapper', errors.password_baru ? 'auth-input-error' : '']">
                  <span class="auth-input-icon">▣</span>
                  <input
                    v-model="form.password_baru"
                    :type="showNewPassword ? 'text' : 'password'"
                    placeholder="••••••••"
                    @input="clearFieldError('password_baru')"
                  />
                  <button
                    type="button"
                    class="auth-eye-button"
                    @click="showNewPassword = !showNewPassword"
                  >
                    {{ showNewPassword ? "🙈" : "👁" }}
                  </button>
                </div>

                <small v-if="errors.password_baru" class="auth-error-text">
                  {{ errors.password_baru }}
                </small>
              </div>

              <div class="auth-form-group">
                <label>Verify new password</label>

                <div :class="['auth-input-wrapper', errors.konfirmasi_password ? 'auth-input-error' : '']">
                  <span class="auth-input-icon">▣</span>
                  <input
                    v-model="form.konfirmasi_password"
                    :type="showConfirmPassword ? 'text' : 'password'"
                    placeholder="••••••••"
                    @input="clearFieldError('konfirmasi_password')"
                  />
                  <button
                    type="button"
                    class="auth-eye-button"
                    @click="showConfirmPassword = !showConfirmPassword"
                  >
                    {{ showConfirmPassword ? "🙈" : "👁" }}
                  </button>
                </div>

                <small v-if="errors.konfirmasi_password" class="auth-error-text">
                  {{ errors.konfirmasi_password }}
                </small>
              </div>

              <button type="submit" class="auth-submit-button" :disabled="loading">
                {{ loading ? "Menyimpan..." : "Save" }}
                <span v-if="!loading" class="auth-arrow">→</span>
              </button>

              <p v-if="globalError" class="auth-global-error">
                *{{ globalError }}
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
import { reactive, ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import api from "@/api/axios";
import AuthNavbar from "../../components/auth/AuthNavbar.vue";
import AuthFooter from "../../components/auth/AuthFooter.vue";

const route = useRoute();
const router = useRouter();

const form = reactive({
  email: "",
  token: "",
  password_baru: "",
  konfirmasi_password: "",
});

const errors = reactive({
  email: "",
  password_baru: "",
  konfirmasi_password: "",
});

const loading = ref(false);
const globalError = ref("");
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

onMounted(() => {
  form.email = route.query.email || "";
  form.token = route.query.token || "";
});

function clearFieldError(field) {
  errors[field] = "";
  globalError.value = "";
}

function resetErrors() {
  errors.email = "";
  errors.password_baru = "";
  errors.konfirmasi_password = "";
  globalError.value = "";
}

function validateForm() {
  resetErrors();

  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  const passwordPattern = /^(?=.*[A-Za-z])(?=.*\d).{8,}$/;

  if (!form.email.trim()) {
    errors.email = "Email wajib diisi";
  } else if (!emailPattern.test(form.email)) {
    errors.email = "Format email tidak valid";
  }

  if (!form.password_baru.trim()) {
    errors.password_baru = "Password baru wajib diisi";
  } else if (!passwordPattern.test(form.password_baru)) {
    errors.password_baru =
      "Password baru minimal 8 karakter dan terdiri dari huruf serta angka";
  }

  if (!form.konfirmasi_password.trim()) {
    errors.konfirmasi_password = "Konfirmasi password wajib diisi";
  } else if (form.konfirmasi_password !== form.password_baru) {
    errors.konfirmasi_password = "Konfirmasi password tidak cocok";
  }

  return (
    !errors.email &&
    !errors.password_baru &&
    !errors.konfirmasi_password
  );
}

async function handleSubmit() {
  if (!validateForm()) return;

  try {
    loading.value = true;

    await api.post('/auth/reset-password', {
      email: form.email,
      token: form.token,
      password_baru: form.password_baru,
      konfirmasi_password: form.konfirmasi_password,
    });

    router.push({
      path: "/password-result",
      query: {
        status: "success",
      },
    });
  } catch (error) {
    handleApiError(error);
  } finally {
    loading.value = false;
  }
}

function handleApiError(error) {
  resetErrors();
  const status = error.response?.status;
  const errData = error.response?.data?.errors || {};

  if (status === 400 || status === 422) {
    if (errData.email) {
      errors.email = Array.isArray(errData.email)
        ? errData.email[0]
        : errData.email;
    }

    if (errData.password_baru) {
      errors.password_baru = Array.isArray(errData.password_baru)
        ? errData.password_baru[0]
        : errData.password_baru;
    }

    if (errData.konfirmasi_password) {
      errors.konfirmasi_password = Array.isArray(errData.konfirmasi_password)
        ? errData.konfirmasi_password[0]
        : errData.konfirmasi_password;
    }

    if (!errors.email && !errors.password_baru && !errors.konfirmasi_password) {
      router.push({
        path: "/password-result",
        query: {
          status: "failed",
          reason: "invalid",
        },
      });
    }

    return;
  }

  if (status === 410) {
    router.push({
      path: "/password-result",
      query: {
        status: "failed",
        reason: "expired",
      },
    });
    return;
  }

  router.push({
    path: "/password-result",
    query: {
      status: "failed",
      reason: "server",
    },
  });
}
</script>
