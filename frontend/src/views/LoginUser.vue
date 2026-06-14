<template>
  <div class="login-page">
    <header class="navbar">
      <div class="brand">BERBAGIVE</div>

      <nav class="nav-menu">
        <a href="/" class="nav-link">Beranda</a>
        <a href="/campaign" class="nav-link">Campaign</a>
        <a href="/komunitas" class="nav-link">Komunitas</a>
        <a href="/donasi-saya" class="nav-link">Donasi Saya</a>
      </nav>

      <div class="nav-right">
        <div class="search-box">
          <span class="search-icon">⌕</span>
          <input type="text" placeholder="Search" />
        </div>

        <a href="/profile" class="icon-link">👤</a>
        <a href="/messages" class="icon-link">✉</a>
        <a href="/logout" class="icon-link logout-icon">↩</a>
      </div>
    </header>

    <main class="login-main">
      <section class="login-card">
        <div class="side-panel"></div>

        <div class="login-center">
          <div class="login-content">
            <p class="mini-brand">BERBAGIVE</p>

            <h1>Masuk</h1>

            <p class="subtitle">
              Selamat datang kembali. Silakan akses akun Anda.
            </p>

            <form class="login-form" @submit.prevent="handleSubmit">
              <div class="form-group">
                <label>Email</label>

                <div :class="['input-wrapper', errors.email ? 'input-error' : '']">
                  <span class="input-icon">♙</span>
                  <input
                    v-model="form.email"
                    type="email"
                    placeholder="nama@email.com"
                    @input="clearError('email')"
                  />
                </div>

                <small v-if="errors.email" class="error-text">
                  {{ errors.email }}
                </small>
              </div>

              <div class="form-group">
                <div class="password-label-row">
                  <label>Password</label>
                  <a href="/forgot-password">Lupa Password?</a>
                </div>

                <div :class="['input-wrapper', errors.password ? 'input-error' : '']">
                  <span class="input-icon">▣</span>

                  <input
                    v-model="form.password"
                    :type="showPassword ? 'text' : 'password'"
                    placeholder="••••••••"
                    @input="clearError('password')"
                  />

                  <button
                    type="button"
                    class="eye-button"
                    @click="showPassword = !showPassword"
                  >
                    {{ showPassword ? "🙈" : "👁" }}
                  </button>
                </div>

                <small v-if="errors.password" class="error-text">
                  {{ errors.password }}
                </small>
              </div>

              <label class="remember-row">
                <input v-model="rememberMe" type="checkbox" />
                <span>Ingat saya untuk 30 hari</span>
              </label>

              <button type="submit" class="submit-button" :disabled="loading">
                {{ loading ? "Memproses..." : "Masuk" }}
                <span v-if="!loading" class="arrow">→</span>
              </button>

              <p v-if="globalError" class="global-error">
                *{{ globalError }}
              </p>

              <p v-if="successMessage" class="global-success">
                {{ successMessage }}
              </p>
            </form>

            <p class="register-text">
              Belum punya akun Berbagive?
              <a href="/register">Daftar sekarang</a>
            </p>
          </div>
        </div>

        <div class="side-panel"></div>
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
import { reactive, ref } from "vue";
import { useRouter } from "vue-router";
import { loginUser } from "../services/api";

const router = useRouter();

const form = reactive({
  email: "",
  password: "",
});

const errors = reactive({
  email: "",
  password: "",
});

const loading = ref(false);
const globalError = ref("");
const successMessage = ref("");
const showPassword = ref(false);
const rememberMe = ref(false);

function resetErrors() {
  errors.email = "";
  errors.password = "";
  globalError.value = "";
  successMessage.value = "";
}

function clearError(field) {
  errors[field] = "";
  globalError.value = "";
  successMessage.value = "";
}

function validateForm() {
  resetErrors();

  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  if (!form.email.trim()) {
    errors.email = "Email wajib diisi";
  } else if (!emailPattern.test(form.email)) {
    errors.email = "Format email tidak valid";
  }

  if (!form.password.trim()) {
    errors.password = "Password wajib diisi";
  }

  return !errors.email && !errors.password;
}

async function handleSubmit() {
  if (!validateForm()) return;

  try {
    loading.value = true;
    resetErrors();

    const response = await loginUser({
      email: form.email,
      password: form.password,
    });

    const token = response.data?.token;
    const user = response.data?.user;

    if (token) {
      localStorage.setItem("auth_token", token);
    }

    if (user) {
      localStorage.setItem("user", JSON.stringify(user));
    }

    if (rememberMe.value) {
      localStorage.setItem("remember_me", "true");
    } else {
      localStorage.removeItem("remember_me");
    }

    router.push({
      path: "/login-result",
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

  if (error.status === 401) {
    router.push({
      path: "/login-result",
      query: {
        status: "failed",
        reason: "wrong_credentials",
      },
    });
    return;
  }

  if (error.status === 403) {
    const message = (error.message || "").toLowerCase();

    if (
      message.includes("verifikasi") ||
      message.includes("review") ||
      message.includes("proses")
    ) {
      router.push({
        path: "/login-result",
        query: {
          status: "failed",
          reason: "verification",
        },
      });
      return;
    }

    if (
      message.includes("tidak aktif") ||
      message.includes("nonaktif") ||
      message.includes("inactive")
    ) {
      router.push({
        path: "/login-result",
        query: {
          status: "failed",
          reason: "inactive",
        },
      });
      return;
    }

    router.push({
      path: "/login-result",
      query: {
        status: "failed",
        reason: "verification",
      },
    });
    return;
  }

  if (error.status === 423) {
    router.push({
      path: "/login-result",
      query: {
        status: "failed",
        reason: "locked",
      },
    });
    return;
  }

  if (error.status === 400 || error.status === 422) {
    if (error.errors) {
      if (error.errors.email) {
        errors.email = Array.isArray(error.errors.email)
          ? error.errors.email[0]
          : error.errors.email;
      }

      if (error.errors.password) {
        errors.password = Array.isArray(error.errors.password)
          ? error.errors.password[0]
          : error.errors.password;
      }
    }

    if (!errors.email && !errors.password) {
      router.push({
        path: "/login-result",
        query: {
          status: "failed",
          reason: "wrong_credentials",
        },
      });
    }

    return;
  }

  router.push({
    path: "/login-result",
    query: {
      status: "failed",
      reason: "server",
    },
  });
}

function redirectByRole(role) {
  if (role === "SUPERADMIN" || role === "superadmin") {
    router.push("/dashboard-superadmin");
    return;
  }

  if (role === "KOMUNITAS" || role === "komunitas") {
    router.push("/dashboard-komunitas");
    return;
  }

  router.push("/dashboard");
}
</script>

<style scoped>
* {
  box-sizing: border-box;
}

.login-page {
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
  padding: 18px 34px;
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
  gap: 34px;
}

.nav-link {
  min-width: 115px;
  height: 42px;
  padding: 0 24px;
  border-radius: 9px;
  text-align: center;
  font-size: 14px;
  font-weight: 700;
  color: white;
  background: #b07f5e;
  display: flex;
  align-items: center;
  justify-content: center;
}

.nav-right {
  margin-left: auto;
  display: flex;
  align-items: center;
  gap: 18px;
}

.search-box {
  width: 365px;
  height: 46px;
  padding: 0 16px;
  border-radius: 24px;
  background: #d6c6b5;
  display: flex;
  align-items: center;
  gap: 10px;
}

.search-icon {
  color: #1f1f1f;
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

.icon-link {
  color: #b07f5e;
  font-size: 34px;
  line-height: 1;
}

.logout-icon {
  font-size: 42px;
  transform: translateY(-2px);
}

/* MAIN */

.login-main {
  min-height: calc(100vh - 170px);
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 85px 20px 100px;
}

.login-card {
  width: 1160px;
  min-height: 735px;
  border-radius: 28px;
  overflow: hidden;
  display: grid;
  grid-template-columns: 1fr 1.65fr 1fr;
  background: #fff2de;
  box-shadow: 0 30px 70px rgba(133, 104, 75, 0.15);
}

.side-panel {
  background: #fff1dc;
}

.login-center {
  background: #ffffff;
  display: flex;
  justify-content: center;
  align-items: center;
}

.login-content {
  width: 390px;
}

.mini-brand {
  margin: 0 0 34px;
  color: #063f5a;
  font-size: 19px;
  font-weight: 900;
  letter-spacing: 8px;
}

.login-content h1 {
  margin: 0 0 8px;
  color: #063f5a;
  font-size: 34px;
  font-weight: 900;
  letter-spacing: -1px;
}

.subtitle {
  margin: 0 0 52px;
  color: #4f5459;
  font-size: 16px;
  line-height: 1.5;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 26px;
}

.form-group label {
  display: block;
  margin: 0 0 10px 2px;
  color: #333b42;
  font-size: 14px;
  font-weight: 600;
}

.password-label-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.password-label-row a {
  margin-bottom: 10px;
  color: #126a65;
  font-size: 12px;
  font-weight: 800;
}

.input-wrapper {
  width: 100%;
  height: 58px;
  padding: 0 16px;
  border-radius: 10px;
  background: #eadfce;
  display: flex;
  align-items: center;
  gap: 13px;
  border: 1px solid transparent;
}

.input-wrapper input {
  width: 100%;
  border: none;
  outline: none;
  background: transparent;
  font-size: 15px;
  color: #34404b;
}

.input-wrapper input::placeholder {
  color: #7c858d;
}

.input-icon {
  color: #6f7880;
  font-size: 18px;
}

.eye-button {
  border: none;
  outline: none;
  background: transparent;
  color: #6f7880;
  font-size: 18px;
  cursor: pointer;
}

.input-error {
  border-color: #ff3b3b;
}

.error-text {
  display: block;
  margin: 8px 0 0 4px;
  color: #ff2d2d;
  font-size: 12px;
  line-height: 1.4;
}

.remember-row {
  display: flex;
  align-items: center;
  gap: 12px;
  color: #566068;
  font-size: 14px;
  cursor: pointer;
}

.remember-row input {
  width: 21px;
  height: 21px;
  appearance: none;
  border: 1px solid #bcc6cf;
  border-radius: 5px;
  background: #ffffff;
  cursor: pointer;
  position: relative;
}

.remember-row input:checked {
  background: #003e5b;
  border-color: #003e5b;
}

.remember-row input:checked::after {
  content: "✓";
  color: white;
  font-size: 14px;
  position: absolute;
  top: 0;
  left: 4px;
}

.submit-button {
  margin-top: 8px;
  width: 100%;
  height: 63px;
  border: none;
  border-radius: 10px;
  background: #003e5b;
  color: white;
  font-size: 17px;
  font-weight: 800;
  cursor: pointer;
  box-shadow: 0 12px 18px rgba(0, 45, 68, 0.22);
  transition: 0.2s ease;
}

.submit-button:hover {
  transform: translateY(-1px);
  background: #00344d;
}

.submit-button:disabled {
  cursor: not-allowed;
  opacity: 0.75;
}

.arrow {
  margin-left: 8px;
  font-size: 25px;
  vertical-align: -2px;
}

.global-error {
  margin: 0;
  text-align: center;
  color: #ff2d2d;
  font-size: 15px;
  font-weight: 400;
}

.global-success {
  margin: 0;
  text-align: center;
  color: #176b4d;
  font-size: 15px;
  font-weight: 500;
}

.register-text {
  margin-top: 65px;
  text-align: center;
  color: #596168;
  font-size: 14px;
}

.register-text a {
  color: #126a65;
  font-weight: 800;
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

/* RESPONSIVE */

@media (max-width: 1180px) {
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

  .login-card {
    width: 95%;
  }
}

@media (max-width: 800px) {
  .login-card {
    grid-template-columns: 1fr;
  }

  .side-panel {
    display: none;
  }

  .login-center {
    padding: 55px 28px;
  }

  .login-content {
    width: 100%;
  }

  .nav-menu,
  .nav-right {
    flex-wrap: wrap;
  }

  .nav-link,
  .search-box {
    width: 100%;
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
