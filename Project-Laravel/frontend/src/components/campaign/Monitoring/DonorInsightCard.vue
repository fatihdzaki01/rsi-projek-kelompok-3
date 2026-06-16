<template>
  <div class="insight-card">
    <div class="insight-icon">📍</div>
    <div>
      <h4 class="insight-title">Donor Insight</h4>
      <p class="insight-text">{{ insight }}</p>
    </div>
  </div>
</template>

<script setup>
defineProps({ insight: String })
</script>

<style scoped>
.insight-card {
  background: #1e4a45;
  border-radius: 14px;
  padding: 18px;
  display: flex;
  gap: 12px;
  align-items: flex-start;
  box-shadow: 0 1px 8px rgba(0,0,0,0.1);
}

.insight-icon {
  font-size: 20px;
  flex-shrink: 0;
}

.insight-title {
  font-size: 14px;
  font-weight: 700;
  color: white;
  margin-bottom: 6px;
}

.insight-text {
  font-size: 12px;
  color: #a8d5cf;
  line-height: 1.6;
}
</style>
