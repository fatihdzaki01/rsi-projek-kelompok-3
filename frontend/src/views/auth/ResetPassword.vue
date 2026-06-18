<script setup>
import { reactive, ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { Lock, ArrowLeft, Eye, EyeOff } from "lucide-vue-next";
import api from "@/api/axios";

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
      query: { status: "success" },
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
        query: { status: "failed", reason: "invalid" },
      });
    }
    return;
  }

  if (status === 410) {
    router.push({
      path: "/password-result",
      query: { status: "failed", reason: "expired" },
    });
    return;
  }

  router.push({
    path: "/password-result",
    query: { status: "failed", reason: "server" },
  });
}
</script>

<template>
  <div class="min-h-screen w-full bg-gradient-to-br from-[#FDF5EE] via-[#E8DDD0] to-[#D4C4B0] flex items-center justify-center px-4">
    <div class="w-full max-w-md">
      <!-- Back link -->
      <button
        type="button"
        @click="router.push('/login')"
        class="flex items-center gap-1.5 text-sm text-[#8B4513] hover:text-[#6b3410] transition-colors mb-4"
      >
        <ArrowLeft class="h-4 w-4" />
        Kembali ke Login
      </button>

      <div class="bg-white rounded-2xl shadow-lg p-8 border border-stone-100">
        <!-- Lock icon header -->
        <div class="flex justify-center mb-4">
          <div class="h-14 w-14 rounded-full bg-[#FDF5EE] flex items-center justify-center">
            <Lock class="h-6 w-6 text-[#8B4513]" />
          </div>
        </div>

        <p class="text-xs font-semibold tracking-widest text-[#8B4513] mb-4 text-center">BERBAGIVE</p>

        <h1 class="text-2xl font-bold text-[#2C2C2C] mb-1 text-center">Atur Ulang Password</h1>
        <p class="text-sm text-[#6B7280] mb-6 text-center">Buat password baru untuk akun Anda.</p>

        <form @submit.prevent="handleSubmit" class="space-y-4" novalidate>
          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-medium text-[#374151] mb-1">Email</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-[#9CA3AF] pointer-events-none">
                <Lock class="h-4 w-4" />
              </span>
              <input
                id="email"
                v-model="form.email"
                type="email"
                placeholder="nama@email.com"
                autocomplete="email"
                readonly
                class="w-full h-11 pl-10 pr-3 bg-[#F5F0E8] border border-gray-200 rounded-lg text-sm text-gray-500 cursor-not-allowed outline-none"
              />
            </div>
            <p v-if="errors.email" class="mt-1 text-xs text-red-500">{{ errors.email }}</p>
          </div>

          <!-- New Password -->
          <div>
            <label for="password" class="block text-sm font-medium text-[#374151] mb-1">Password Baru</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-[#9CA3AF] pointer-events-none">
                <Lock class="h-4 w-4" />
              </span>
              <input
                id="password"
                v-model="form.password_baru"
                :type="showNewPassword ? 'text' : 'password'"
                placeholder="••••••••"
                autocomplete="new-password"
                class="w-full h-11 pl-10 pr-10 bg-[#F5F0E8] border border-gray-200 rounded-lg text-sm text-gray-700 placeholder-gray-400 outline-none focus:ring-2 focus:ring-[#8B4513]/30 transition-shadow"
                :class="{ 'ring-2 ring-red-400': errors.password_baru }"
                @input="clearFieldError('password_baru')"
              />
              <button
                type="button"
                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 transition-colors"
                @click="showNewPassword = !showNewPassword"
              >
                <EyeOff v-if="showNewPassword" class="h-4 w-4" />
                <Eye v-else class="h-4 w-4" />
              </button>
            </div>
            <p v-if="errors.password_baru" class="mt-1 text-xs text-red-500">{{ errors.password_baru }}</p>
          </div>

          <!-- Confirm Password -->
          <div>
            <label for="confirm-password" class="block text-sm font-medium text-[#374151] mb-1">Verifikasi Password Baru</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-[#9CA3AF] pointer-events-none">
                <Lock class="h-4 w-4" />
              </span>
              <input
                id="confirm-password"
                v-model="form.konfirmasi_password"
                :type="showConfirmPassword ? 'text' : 'password'"
                placeholder="••••••••"
                autocomplete="new-password"
                class="w-full h-11 pl-10 pr-10 bg-[#F5F0E8] border border-gray-200 rounded-lg text-sm text-gray-700 placeholder-gray-400 outline-none focus:ring-2 focus:ring-[#8B4513]/30 transition-shadow"
                :class="{ 'ring-2 ring-red-400': errors.konfirmasi_password }"
                @input="clearFieldError('konfirmasi_password')"
              />
              <button
                type="button"
                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 transition-colors"
                @click="showConfirmPassword = !showConfirmPassword"
              >
                <EyeOff v-if="showConfirmPassword" class="h-4 w-4" />
                <Eye v-else class="h-4 w-4" />
              </button>
            </div>
            <p v-if="errors.konfirmasi_password" class="mt-1 text-xs text-red-500">{{ errors.konfirmasi_password }}</p>
          </div>

          <!-- Global error -->
          <p v-if="globalError" class="text-xs text-red-500 text-center">{{ globalError }}</p>

          <!-- Submit -->
          <button
            type="submit"
            :disabled="loading"
            class="w-full h-11 bg-[#8B4513] hover:bg-[#6b3410] disabled:opacity-60 disabled:cursor-not-allowed text-white rounded-lg font-medium transition-colors flex items-center justify-center gap-1.5"
          >
            <template v-if="loading">Menyimpan...</template>
            <template v-else>Simpan Password Baru <span aria-hidden="true" class="text-lg leading-none">→</span></template>
          </button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-600">
          Belum punya akun Berbagive?
          <button type="button" @click="router.push('/register')" class="text-[#8B4513] font-semibold hover:underline ml-0.5">Daftar sekarang</button>
        </p>
      </div>
    </div>
  </div>
</template>
