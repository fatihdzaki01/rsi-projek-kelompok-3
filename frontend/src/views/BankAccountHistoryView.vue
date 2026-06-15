<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/services/api'
import NavBar from '@/components/NavBar.vue'
import AppFooter from '@/components/AppFooter.vue'

const histories = ref([])
const pagination = ref(null)
const loading = ref(true)
const errorMessage = ref('')

const statusFilter = ref('')
const currentPage = ref(1)

const fetchHistories = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const params = { page: currentPage.value }

    if (statusFilter.value) {
      params.status = statusFilter.value
    }

    const response = await api.get('/superadmin/bank-account-changes/history', { params })

    histories.value = response.data.data.data
    pagination.value = response.data.data
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || 'Gagal memuat riwayat review.'
  } finally {
    loading.value = false
  }
}

const applyFilters = () => {
  currentPage.value = 1
  fetchHistories()
}

const nextPage = () => {
  if (pagination.value?.next_page_url) {
    currentPage.value += 1
    fetchHistories()
  }
}

const prevPage = () => {
  if (pagination.value?.prev_page_url) {
    currentPage.value -= 1
    fetchHistories()
  }
}

onMounted(fetchHistories)
</script>

<template>
  <main class="dashboard-page">
    <NavBar />

    <section class="container">
      <div class="page-title">
        <div>
          <h1>Riwayat Perubahan Rekening</h1>
          <p>Riwayat keputusan review perubahan rekening komunitas.</p>
        </div>

        <RouterLink to="/dashboard/bank-account-changes" class="back-link">Kembali Review</RouterLink>
      </div>

      <div class="filter-bar secondary-filters">
        <div class="filter-group">
          <label>Status</label>
          <div class="filter-row">
            <select v-model="statusFilter" class="filter-select">
              <option value="">Semua</option>
              <option value="disetujui">Disetujui</option>
              <option value="ditolak">Ditolak</option>
            </select>
            <button class="apply-btn" @click="applyFilters">Terapkan</button>
          </div>
        </div>
      </div>

      <section v-if="loading" class="card">Memuat riwayat review...</section>

      <section v-else-if="errorMessage" class="card error">
        {{ errorMessage }}
      </section>

      <section v-else class="card">
        <div class="card-header">
          <div>
            <h2>Riwayat Keputusan</h2>
            <p>
              {{ statusFilter === 'disetujui' ? 'Perubahan yang disetujui' : statusFilter === 'ditolak' ? 'Perubahan yang ditolak' : 'Seluruh riwayat review' }}
            </p>
          </div>

          <span class="badge">{{ pagination?.total || 0 }} data</span>
        </div>

        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Nama Lembaga</th>
              <th>Bank Lama</th>
              <th>Rekening Lama</th>
              <th>Bank Baru</th>
              <th>Rekening Baru</th>
              <th>Status</th>
              <th>Reviewer</th>
              <th>Alasan Penolakan</th>
              <th>Tanggal Keputusan</th>
            </tr>
          </thead>

          <tbody>
            <tr v-if="histories.length === 0">
              <td colspan="10">Tidak ada riwayat review.</td>
            </tr>

            <tr v-for="item in histories" :key="item.id_verif">
              <td>{{ item.id_verif }}</td>
              <td>{{ item.nama_lembaga }}</td>
              <td>{{ item.nama_bank_lama || '-' }}</td>
              <td>{{ item.nomor_rekening_lama || '-' }}</td>
              <td>{{ item.nama_bank_baru || '-' }}</td>
              <td>{{ item.nomor_rekening_baru || '-' }}</td>
              <td>
                <span class="status-badge" :class="item.status === 'disetujui' ? 'active' : 'inactive'">
                  {{ item.status === 'disetujui' ? 'Disetujui' : 'Ditolak' }}
                </span>
              </td>
              <td>{{ item.reviewer || '-' }}</td>
              <td>{{ item.alasan_penolakan || '-' }}</td>
              <td>{{ item.tanggal_keputusan ? new Date(item.tanggal_keputusan).toLocaleDateString('id-ID') : '-' }}</td>
            </tr>
          </tbody>
        </table>

        <section v-if="pagination && pagination.last_page > 1" class="pagination-box">
          <button :disabled="!pagination.prev_page_url" @click="prevPage">
            Sebelumnya
          </button>

          <span>
            Halaman {{ pagination.current_page }} dari {{ pagination.last_page }}
          </span>

          <button :disabled="!pagination.next_page_url" @click="nextPage">
            Selanjutnya
          </button>
        </section>
      </section>
    </section>

    <AppFooter />
  </main>
</template>

<style scoped>
.secondary-filters {
  margin-bottom: 22px;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.filter-group label {
  font-size: 12px;
  font-weight: 700;
  color: #6f655b;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.filter-row {
  display: flex;
  gap: 8px;
  align-items: center;
}

.filter-select {
  height: 36px;
  padding: 0 10px;
  border: 1px solid #dccdbb;
  border-radius: 8px;
  background: #fffaf2;
  color: #07313a;
  font-size: 13px;
  font-family: inherit;
  min-width: 150px;
}

.apply-btn {
  height: 36px;
  padding: 0 18px;
  border: 0;
  border-radius: 8px;
  background: #07313a;
  color: white;
  font-weight: 700;
  font-size: 13px;
  cursor: pointer;
}
</style>
