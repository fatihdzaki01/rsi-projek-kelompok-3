<template>
  <div class="donor-table-card">
    <div class="table-top">
      <h3 class="table-title">Monitoring Donasi</h3>
    </div>

    <table class="donor-table">
      <thead>
        <tr>
          <th>NAMA DONATUR</th>
          <th>JUMLAH DONASI</th>
          <th>TANGGAL</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="!donors || donors.length === 0">
          <td colspan="3" class="text-center py-4 text-gray-400">Belum ada donasi untuk campaign ini.</td>
        </tr>
        <tr v-for="(donor, i) in donors" :key="i">
          <td class="donor-name">{{ donor.name }}</td>
          <td class="donor-amount" :class="{ large: donor.amount >= 5000000 }">
            {{ formatRp(donor.amount) }}
          </td>
          <td class="donor-date">{{ formatDate(donor.date) }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
defineProps({ donors: Array })

const formatRp = (n) => 'Rp ' + Number(n).toLocaleString('id-ID')

const formatDate = (d) => {
  if (!d) return '-'
  return new Date(d).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}
</script>

<style scoped>
.donor-table-card {
  background: white;
  border-radius: 14px;
  padding: 22px 24px;
  box-shadow: 0 1px 8px rgba(0,0,0,0.06);
}

.table-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 18px;
}

.table-title {
  font-size: 15px;
  font-weight: 700;
  color: #1a1a2e;
}

.donor-table {
  width: 100%;
  border-collapse: collapse;
}

.donor-table th {
  text-align: left;
  font-size: 11px;
  font-weight: 700;
  color: #aaa;
  letter-spacing: 0.5px;
  padding: 8px 12px;
  border-bottom: 1px solid #f0ebe4;
}

.donor-table td {
  padding: 14px 12px;
  font-size: 13px;
  color: #333;
  border-bottom: 1px solid #f8f5f2;
}

.donor-table tr:last-child td { border-bottom: none; }

.donor-name { font-weight: 500; color: #1a1a2e; }

.donor-amount {
  font-weight: 600;
  color: #333;
}

.donor-amount.large { color: #c0392b; }

.donor-date { color: #888; }
.text-center { text-align: center; }
.py-4 { padding-top: 16px; padding-bottom: 16px; }
</style>
