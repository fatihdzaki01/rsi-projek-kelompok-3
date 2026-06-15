<script setup>
import { X } from 'lucide-vue-next'

defineProps({
  // Pesan error — sesuaikan dari parent berdasarkan response backend
  message: {
    type: String,
    default: 'Email atau kata sandi yang kamu masukkan salah.',
  },
  // Label tombol primer (default "Coba Lagi", bisa diganti misal "Tutup")
  retryLabel: {
    type: String,
    default: 'Coba Lagi',
  },
})

defineEmits(['retry', 'forgot-password'])
</script>

<template>
  <Transition
    enter-active-class="transition duration-200 ease-out"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition duration-150 ease-in"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/20 backdrop-blur-sm">
      <div
        class="w-full max-w-xs rounded-2xl bg-white p-8 text-center shadow-lg"
        role="alertdialog"
        aria-modal="true"
        aria-labelledby="login-failed-title"
      >
        <!-- Icon -->
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-[#FEE2E2]">
          <X :size="24" class="text-[#EF4444]" />
        </div>

        <!-- Title -->
        <h2 id="login-failed-title" class="mb-1 text-xl font-bold text-[#1a2744]">
          Login gagal
        </h2>

        <!-- Subtitle (dinamis) -->
        <p class="mb-6 text-sm text-[#6B7280]">
          {{ message }}
        </p>

        <!-- Primary button -->
        <button
          type="button"
          class="h-11 w-full rounded-lg bg-[#1a2744] font-medium text-white transition-colors hover:bg-[#2a3754]"
          @click="$emit('retry')"
        >
          {{ retryLabel }}
        </button>

        <!-- Secondary link -->
        <button
          type="button"
          class="mt-2 cursor-pointer text-sm text-[#6B7280] underline"
          @click="$emit('forgot-password')"
        >
          Lupa kata sandi?
        </button>
      </div>
    </div>
  </Transition>
</template>