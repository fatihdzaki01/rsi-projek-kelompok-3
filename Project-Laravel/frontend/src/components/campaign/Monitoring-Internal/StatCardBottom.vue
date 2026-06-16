<template>
  <div class="card">
    <span class="card-icon">{{ icon }}</span>
    <div>
      <p class="card-label">{{ label }}</p>
      <h3 class="card-value">{{ value }}</h3>
      <p class="card-sub">{{ subText }}</p>
    </div>
  </div>
</template>

<script setup>
defineProps({ icon: String, label: String, value: String, subText: String })
</script>

<style scoped>
.card {
  background: white;
  border-radius: 14px;
  padding: 18px 22px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: 0 1px 8px rgba(0,0,0,0.06);
}

.card-icon {
  font-size: 24px;
  background: #f5f0eb;
  width: 48px; height: 48px;
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}

.card-label { font-size: 11px; font-weight: 700; color: #888; letter-spacing: 0.5px; }
.card-value { font-size: 26px; font-weight: 800; color: #1a1a2e; margin: 2px 0; }
.card-sub   { font-size: 12px; color: #888; }
</style>
