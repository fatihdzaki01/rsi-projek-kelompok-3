<template>
  <div class="register-page">
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

    <main class="register-main">
      <section class="register-card">
        <div class="register-content">
          <h1>Mari Mulai Kebaikan.</h1>

          <p class="subtitle">
            Bergabung menjadi donatur dan <br />
            abadikan jejak kemanusiaan Anda.
          </p>

          <form class="register-form" @submit.prevent="handleSubmit">
            <div class="form-group">
              <label>USERNAME</label>

              <div :class="['input-wrapper', errors.username ? 'input-error' : '']">
                <input
                  v-model="form.username"
                  type="text"
                  placeholder="pilih nama unik"
                  @input="clearFieldError('username')"
                />
                <span class="input-icon">♙</span>
              </div>

              <small v-if="errors.username" class="error-text">
                {{ errors.username }}
              </small>
            </div>

            <div class="form-group">
              <label>ALAMAT EMAIL</label>

              <div :class="['input-wrapper', errors.email ? 'input-error' : '']">
                <input
                  v-model="form.email"
                  type="email"
                  placeholder="nama@email.com"
                  @input="clearFieldError('email')"
                />
                <span class="input-icon">@</span>
              </div>

              <small v-if="errors.email" class="error-text">
                {{ errors.email }}
              </small>
            </div>

            <div class="form-group">
              <label>KATA SANDI</label>

              <div :class="['input-wrapper', errors.password ? 'input-error' : '']">
                <input
                  v-model="form.password"
                  type="password"
                  placeholder="••••••••"
                  @input="clearFieldError('password')"
                />
                <span class="input-icon">▣</span>
              </div>

              <small v-if="errors.password" class="error-text">
                {{ errors.password }}
              </small>
            </div>

            <button type="submit" class="submit-button" :disabled="loading">
              {{ loading ? "Memproses..." : "Daftar" }}
              <span v-if="!loading" class="arrow">→</span>
            </button>

            <p v-if="globalError" class="global-error">
              *{{ globalError }}
            </p>

            <p v-if="successMessage" class="global-success">
              {{ successMessage }}
            </p>
          </form>

          <p class="login-text">
            Sudah memiliki akun?
            <a href="/login">Masuk ke Dashboard</a>
          </p>
        </div>
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
import { registerUser } from "../../services/api";

const router = useRouter();

const form = reactive({
  username: "",
  email: "",
  password: "",
});

const errors = reactive({
  username: "",
  email: "",
  password: "",
});

const loading = ref(false);
const globalError = ref("");
const successMessage = ref("");

function resetErrors() {
  errors.username = "";
  errors.email = "";
  errors.password = "";
  globalError.value = "";
  successMessage.value = "";
}

function clearFieldError(field) {
  errors[field] = "";
  globalError.value = "";
  successMessage.value = "";
}

function validateForm() {
  resetErrors();

  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  const passwordPattern = /^(?=.*[A-Za-z])(?=.*\d).{8,}$/;

  if (!form.username.trim()) {
    errors.username = "Username wajib diisi";
  }

  if (!form.email.trim()) {
    errors.email = "Alamat email wajib diisi";
  } else if (!emailPattern.test(form.email)) {
    errors.email = "Format email tidak valid";
  }

  if (!form.password.trim()) {
    errors.password = "Password wajib diisi";
  } else if (!passwordPattern.test(form.password)) {
    errors.password =
      "Password harus memuat 8 karakter, kombinasi huruf dan angka";
  }

  return !errors.username && !errors.email && !errors.password;
}

async function handleSubmit() {
  if (!validateForm()) return;

  try {
    loading.value = true;
    globalError.value = "";
    successMessage.value = "";

    await registerUser({
      username: form.username,
      email: form.email,
      password: form.password,
    });

    localStorage.setItem("verification_email", form.email);

    router.push({
      path: "/email-verification",
      query: {
        email: form.email,
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

  if (error.status === 409) {
    globalError.value = "Email sudah terdaftar";
    return;
  }

  if (error.status === 400 || error.status === 422) {
    if (error.errors) {
      if (error.errors.username) {
        errors.username = Array.isArray(error.errors.username)
          ? error.errors.username[0]
          : error.errors.username;
      }

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

    if (!errors.username && !errors.email && !errors.password) {
      globalError.value =
        error.message || "Format email atau password tidak memenuhi ketentuan";
    }

    return;
  }

  if (error.status === 500) {
    globalError.value = "Server sedang bermasalah. Silakan coba lagi nanti";
    return;
  }

  globalError.value = error.message || "Registrasi gagal. Silakan coba lagi";
}
</script>

<style scoped>
* {
  box-sizing: border-box;
}

.register-page {
  min-height: 100vh;
  background: #eadfce;
  color: #12364a;
  font-family: Inter, Arial, sans-serif;
}

a {
  text-decoration: none;
}

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

.register-main {
  min-height: calc(100vh - 170px);
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 80px 20px 100px;
}

.register-card {
  width: 550px;
  min-height: 720px;
  border-radius: 34px;
  background: linear-gradient(145deg, #f6eadb 0%, #f4e6d6 70%, #ffe9d6 100%);
  box-shadow: 0 25px 60px rgba(144, 113, 83, 0.18);
  display: flex;
  justify-content: center;
}

.register-content {
  width: 430px;
  padding: 62px 0 45px;
}

.register-content h1 {
  margin: 0 0 18px;
  color: #063f5a;
  font-size: 36px;
  font-weight: 500;
  letter-spacing: -1.5px;
}

.subtitle {
  margin: 0 0 42px;
  color: #6f747c;
  font-size: 17px;
  line-height: 1.55;
}

.register-form {
  display: flex;
  flex-direction: column;
  gap: 30px;
}

.form-group label {
  display: block;
  margin: 0 0 10px 4px;
  color: #444a51;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 1px;
}

.input-wrapper {
  width: 100%;
  height: 70px;
  padding: 0 18px 0 26px;
  border-radius: 24px;
  background: #efe1d1;
  display: flex;
  align-items: center;
  border: 1px solid transparent;
}

.input-wrapper input {
  width: 100%;
  border: none;
  outline: none;
  background: transparent;
  font-size: 16px;
  color: #34404b;
}

.input-wrapper input::placeholder {
  color: #7c858d;
}

.input-icon {
  color: #6f7880;
  font-size: 19px;
}

.input-error {
  border-color: #ff3b3b;
}

.error-text {
  display: block;
  margin: 8px 0 0 26px;
  color: #ff2d2d;
  font-size: 12px;
  line-height: 1.4;
}

.submit-button {
  margin-top: 22px;
  width: 100%;
  height: 74px;
  border: none;
  border-radius: 22px;
  background: #003e5b;
  color: white;
  font-size: 18px;
  font-weight: 800;
  cursor: pointer;
  box-shadow: 0 10px 18px rgba(0, 45, 68, 0.25);
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
  margin-left: 10px;
  font-size: 28px;
  vertical-align: -2px;
}

.global-error {
  margin: 26px 0 0;
  text-align: center;
  color: #ff2d2d;
  font-size: 16px;
  font-weight: 400;
}

.global-success {
  margin: 26px 0 0;
  text-align: center;
  color: #176b4d;
  font-size: 15px;
  font-weight: 500;
}

.login-text {
  margin-top: 26px;
  text-align: center;
  color: #4d5358;
  font-size: 14px;
}

.login-text a {
  color: #126a65;
  font-weight: 800;
}

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

  .register-card {
    width: 100%;
    min-height: auto;
  }

  .register-content {
    width: 100%;
    padding: 45px 28px;
  }

  .register-content h1 {
    font-size: 30px;
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
