<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center px-4"
        @click.self="$emit('cancel')"
      >
        <!-- Overlay -->
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" />

        <!-- Modal Card -->
        <div class="relative bg-white rounded-2xl shadow-xl p-6 w-full max-w-xs z-10">
          <!-- Icon -->
          <div class="flex justify-center mb-4">
            <div class="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center">
              <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6"/>
              </svg>
            </div>
          </div>

          <h3 class="text-base font-bold text-gray-900 text-center mb-2">
            Berhenti mengikuti komunitas ini?
          </h3>
          <p class="text-sm text-gray-500 text-center leading-relaxed mb-6">
            Kamu tidak akan menerima notifikasi dari komunitas ini lagi.
          </p>

          <div class="flex gap-3">
            <button
              @click="$emit('cancel')"
              class="flex-1 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors"
            >
              Batal
            </button>
            <button
              @click="$emit('confirm')"
              class="flex-1 py-2.5 rounded-xl bg-red-600 text-sm font-medium text-white hover:bg-red-700 transition-colors"
            >
              Ya, Unfollow
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
defineProps({
  show: {
    type: Boolean,
    default: false
  }
})

defineEmits(['confirm', 'cancel'])
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.2s ease;
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
.modal-enter-active .relative,
.modal-leave-active .relative {
  transition: transform 0.2s ease;
}
.modal-enter-from .relative {
  transform: scale(0.95);
}
</style>
