<template>
  <div class="card">
    <div class="card-top">
      <p class="card-label">{{ label }}</p>
      <span class="card-icon">{{ icon }}</span>
    </div>
    <h2 class="card-value" :style="{ color: valueColor || '#1a1a2e' }">
      {{ value }}
    </h2>
  </div>
</template>

<script setup>
defineProps({
  label     : String,
  value     : String,
  icon      : String,
  valueColor: { type: String, default: '' },
})
</script>

<style scoped>
.card {
  background: white;
  border-radius: 14px;
  padding: 20px 22px;
  box-shadow: 0 1px 8px rgba(0,0,0,0.06);
}

.card-top {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 12px;
}

.card-label {
  font-size: 11px;
  font-weight: 700;
  color: #888;
  letter-spacing: 0.5px;
}

.card-icon {
  font-size: 20px;
  background: #f5f0eb;
  width: 38px;
  height: 38px;
  border-radius: 9px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.card-value {
  font-size: 28px;
  font-weight: 800;
}
</style>
