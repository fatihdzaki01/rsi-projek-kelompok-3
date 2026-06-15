<script setup>
import { Check } from 'lucide-vue-next'

defineEmits(['back', 'view-history'])
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
        role="dialog"
        aria-modal="true"
        aria-labelledby="login-success-title"
      >
        <!-- Icon -->
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-[#F5E6D3]">
          <Check :size="24" class="text-[#8B4513]" />
        </div>

        <!-- Title -->
        <h2 id="login-success-title" class="mb-1 text-xl font-bold text-[#1a2744]">
          Login berhasil
        </h2>

        <!-- Subtitle -->
        <p class="mb-6 text-sm text-[#6B7280]">
          Selamat datang kembali!
        </p>

        <!-- Primary button -->
        <button
          type="button"
          class="h-11 w-full rounded-lg bg-[#1a2744] font-medium text-white transition-colors hover:bg-[#2a3754]"
          @click="$emit('back')"
        >
          Kembali
        </button>

        <!-- Secondary link -->
        <button
          type="button"
          class="mt-2 cursor-pointer text-sm text-[#6B7280] underline"
          @click="$emit('view-history')"
        >
          Lihat Detail Riwayat
        </button>
      </div>
    </div>
  </Transition>
</template>