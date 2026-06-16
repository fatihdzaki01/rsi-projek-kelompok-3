<template>
  <div class="table-card">
    <div class="table-header">
      <div>
        <h3 class="table-title">Riwayat Pencairan Dana</h3>
        <p class="table-sub">Daftar transaksi pencairan dana yang telah diajukan</p>
      </div>
      <button class="btn-semua">Lihat Semua →</button>
    </div>

    <table class="trx-table">
      <thead>
        <tr>
          <th>ID TRANSAKSI</th>
          <th>TANGGAL</th>
          <th>KATEGORI</th>
          <th>JUMLAH</th>
          <th>STATUS</th>
          <th>AKSI</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="t in transactions" :key="t.id">
          <td class="td-id">{{ t.id }}</td>
          <td class="td-date">{{ t.date }}</td>
          <td><span class="cat-badge">{{ t.category }}</span></td>
          <td class="td-amount">{{ fmt(t.amount) }}</td>
          <td>
            <span class="status-badge" :class="statusClass(t.status)">
              {{ t.status }}
            </span>
          </td>
          <td>
            <button class="btn-detail" title="Detail">📄</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
defineProps({ transactions: Array })

const fmt = (n) => 'Rp ' + n.toLocaleString('id-ID')

const statusClass = (s) => ({
  BERHASIL: 'success',
  DIPROSES: 'pending',
  GAGAL   : 'failed',
}[s] ?? 'pending')
</script>

<style scoped>
.table-card {
  background: white;
  border-radius: 14px;
  padding: 22px 24px;
  box-shadow: 0 1px 8px rgba(0,0,0,0.06);
}

.table-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 20px;
}

.table-title { font-size: 15px; font-weight: 700; color: #1a1a2e; }
.table-sub   { font-size: 12px; color: #888; margin-top: 4px; }

.btn-semua {
  background: none; border: none;
  color: #4a3728; font-size: 13px;
  font-weight: 600; cursor: pointer;
}

.trx-table { width: 100%; border-collapse: collapse; }

.trx-table th {
  text-align: left;
  font-size: 10px; font-weight: 700;
  color: #aaa; letter-spacing: 0.5px;
  padding: 8px 12px;
  border-bottom: 1px solid #f0ebe4;
}

.trx-table td {
  padding: 14px 12px;
  font-size: 13px; color: #333;
  border-bottom: 1px solid #f8f5f2;
}

.trx-table tr:last-child td { border-bottom: none; }

.td-id     { font-weight: 700; color: #1a1a2e; }
+.td-date   { color: #666; }
.td-amount { font-weight: 600; }

.cat-badge {
  background: #f0ebe4; color: #4a3728;
  border-radius: 6px; padding: 4px 10px;
  font-size: 12px; font-weight: 500;
}

.status-badge {
  padding: 4px 12px; border-radius: 6px;
  font-size: 12px; font-weight: 600;
}

.status-badge.success { background: #e0f5ec; color: #2e7d5e; }
.status-badge.pending { background: #fff3e0; color: #d97706; }
.status-badge.failed  { background: #fdecea; color: #c0392b; }

.btn-detail {
  background: none; border: none;
  cursor: pointer; font-size: 16px;
  padding: 4px 8px; border-radius: 6px;
  transition: background 0.2s;
}
.btn-detail:hover { background: #f0ebe4; }
</style>
