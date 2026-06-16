<template>
  <div class="card" :class="variant">
    <div class="card-top">
      <span class="card-icon">{{ icon }}</span>
      <span v-if="badge" class="card-badge">{{ badge }}</span>
    </div>
    <p class="card-label">{{ label }}</p>
    <h2 class="card-value" :style="{ color: valueColor || '' }">{{ value }}</h2>

    <template v-if="showProgress">
      <div class="progress-meta">
        <span>{{ targetLabel }}</span>
        <span>{{ progressLabel }}</span>
      </div>
      <div class="bar-bg">
        <div class="bar-fill" :style="{ width: progress + '%' }" />
      </div>
    </template>

    <p v-if="subText" class="card-sub">{{ subText }}</p>
  </div>
</template>

<script setup>
defineProps({
  icon         : String,
  label        : String,
  value        : String,
  badge        : String,
  showProgress : { type: Boolean, default: false },
  progress     : { type: Number, default: 0 },
  targetLabel  : String,
  progressLabel: String,
  subText      : String,
  valueColor   : String,
  variant      : { type: String, default: '' },
})
</script>

<style scoped>
.card {
  background: white;
  border-radius: 14px;
  padding: 18px 20px;
  box-shadow: 0 1px 8px rgba(0,0,0,0.06);
}

.card.dark {
  background: #1a3a35;
  color: white;
}

.card-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.card-icon {
  font-size: 20px;
  background: #f5f0eb;
  width: 38px; height: 38px;
  border-radius: 9px;
  display: flex; align-items: center; justify-content: center;
}

.dark .card-icon { background: rgba(255,255,255,0.1); }

.card-badge {
  font-size: 10px;
  font-weight: 600;
  background: #e0f5ec;
  color: #2e7d5e;
  padding: 3px 8px;
  border-radius: 999px;
}

.card-label {
  font-size: 11px;
  font-weight: 700;
  color: #888;
  letter-spacing: 0.5px;
  margin-bottom: 6px;
}

.dark .card-label { color: #a8d5cf; }

.card-value {
  font-size: 26px;
  font-weight: 800;
  color: #1a1a2e;
  margin-bottom: 8px;
}

.dark .card-value { color: white; }

.progress-meta {
  display: flex;
  justify-content: space-between;
  font-size: 11px;
  color: #888;
  margin-bottom: 6px;
}

.bar-bg {
  background: #e0e0e0;
  border-radius: 999px;
  height: 7px;
}

.bar-fill {
  background: #2c7a6b;
  height: 100%;
  border-radius: 999px;
  transition: width 0.4s ease;
}

.card-sub {
  font-size: 12px;
  color: #888;
  margin-top: 8px;
}

.dark .card-sub { color: #a8d5cf; }
</style>
