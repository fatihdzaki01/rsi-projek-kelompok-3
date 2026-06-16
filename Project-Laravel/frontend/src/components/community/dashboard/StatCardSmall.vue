<template>
  <div class="small-card">
    <p class="small-value">{{ value }}</p>
    <p class="small-label">{{ label }}</p>
  </div>
</template>

<script setup>
defineProps({ label: String, value: [String, Number] })
</script>

<style scoped>
.small-card {
  background: white;
  border-radius: 12px;
  padding: 16px;
  text-align: center;
  box-shadow: 0 1px 6px rgba(0,0,0,0.05);
}

.small-value {
  font-size: 24px;
  font-weight: 800;
  color: #1a1a2e;
  margin-bottom: 4px;
}

.small-label {
  font-size: 10px;
  font-weight: 700;
  color: #888;
  letter-spacing: 0.5px;
}
</style>