<template>
  <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-700 leading-relaxed">
    <p v-if="isFirstTime">
      Pengajuan pertama wajib melampirkan <strong>Rancangan Penggunaan Dana</strong>.
    </p>
    <p v-else>
      Pengajuan selanjutnya wajib melampirkan <strong>Bukti Penggunaan Dana</strong> dari pencairan sebelumnya.
    </p>
  </div>
</template>

<script setup>
defineProps({
  isFirstTime: { type: Boolean, default: true },
})
</script>
