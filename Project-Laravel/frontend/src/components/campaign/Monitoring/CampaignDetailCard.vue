<template>
  <div class="detail-card">
    <h3 class="detail-title">Detail Campaign</h3>

    <img :src="campaign.image" alt="Campaign" class="detail-image" />

    <p class="detail-about">{{ campaign.about }}</p>

    <div class="progress-section">
      <div class="progress-header">
        <span class="progress-label">PROGRESS DONASI</span>
        <span class="progress-pct">{{ campaign.progressPercent }}%</span>
      </div>
      <div class="progress-bg">
        <div
          class="progress-fill"
          :style="{ width: campaign.progressPercent + '%' }"
        />
      </div>
      <div class="progress-footer">
        <div>
          <p class="footer-label">COLLECTED</p>
          <p class="footer-value">Rp {{ formatShort(campaign.collected) }}</p>
        </div>
        <div class="text-right">
          <p class="footer-label">TARGET</p>
          <p class="footer-value">Rp {{ formatShort(campaign.target) }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({ campaign: Object })

const formatShort = (n) => {
  const num = Number(n) || 0
  if (num >= 1000000000) return (num / 1000000000).toFixed(1).replace('.0', '') + 'M'
  if (num >= 1000000)    return (num / 1000000).toFixed(1).replace('.0', '') + 'Jt'
  return num.toLocaleString('id-ID')
}
</script>

<style scoped>
.detail-card {
  background: white;
  border-radius: 14px;
  padding: 18px;
  box-shadow: 0 1px 8px rgba(0,0,0,0.06);
}

.detail-title {
  font-size: 15px;
  font-weight: 700;
  color: #1a1a2e;
  margin-bottom: 12px;
}

.detail-image {
  width: 100%;
  height: 140px;
  object-fit: cover;
  border-radius: 10px;
  margin-bottom: 12px;
}

.detail-about {
  font-size: 12px;
  color: #555;
  line-height: 1.6;
  margin-bottom: 16px;
}

.progress-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.progress-label {
  font-size: 11px;
  font-weight: 700;
  color: #888;
  letter-spacing: 0.5px;
}

.progress-pct {
  font-size: 14px;
  font-weight: 700;
  color: #1a1a2e;
}

.progress-bg {
  background: #e0e0e0;
  border-radius: 999px;
  height: 7px;
  margin-bottom: 10px;
}

.progress-fill {
  background: #e07b39;
  height: 100%;
  border-radius: 999px;
  transition: width 0.5s ease;
}

.progress-footer {
  display: flex;
  justify-content: space-between;
}

.footer-label {
  font-size: 10px;
  color: #aaa;
  font-weight: 600;
  letter-spacing: 0.5px;
}

.footer-value {
  font-size: 12px;
  font-weight: 700;
  color: #333;
}

.text-right { text-align: right; }
</style>
