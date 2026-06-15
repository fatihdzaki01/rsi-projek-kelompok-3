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

const searchQuery = ref('')
const statusFilter = ref('')
const dateFrom = ref('')
const dateTo = ref('')
const currentPage = ref(1)

const fetchHistories = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const params = { page: currentPage.value }

    if (searchQuery.value.trim()) {
      params.search = searchQuery.value.trim()
    }

    if (statusFilter.value) {
      params.status = statusFilter.value
    }

    if (dateFrom.value) {
      params.date_from = dateFrom.value
    }

    if (dateTo.value) {
      params.date_to = dateTo.value
    }

    const response = await api.get('/superadmin/community-registrations/history', { params })

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

const resetFilters = () => {
  searchQuery.value = ''
  statusFilter.value = ''
  dateFrom.value = ''
  dateTo.value = ''
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
          <h1>Riwayat Review Pendaftaran</h1>
          <p>Riwayat keputusan review pendaftaran komunitas.</p>
        </div>

        <RouterLink to="/dashboard/community-registrations" class="back-link">Kembali Review</RouterLink>
      </div>

      <div class="filter-bar">
        <div class="search-box">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari lembaga..."
          />
        </div>
      </div>

      <div class="filter-bar secondary-filters">
        <div class="filter-group">
          <label>Status</label>
          <select v-model="statusFilter" class="filter-select">
            <option value="">Semua</option>
            <option value="aktif">Disetujui</option>
            <option value="ditolak">Ditolak</option>
          </select>
        </div>

        <div class="filter-group">
          <label>Tanggal Review</label>
          <div class="date-range">
            <input v-model="dateFrom" type="date" />
            <span class="date-sep">—</span>
            <input v-model="dateTo" type="date" />
          </div>
        </div>

        <div class="filter-actions">
          <button class="apply-btn" @click="applyFilters">Terapkan</button>
          <button class="reset-btn" @click="resetFilters">Reset</button>
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
              {{ searchQuery ? `Hasil pencarian "${searchQuery}" — ` : '' }}
              {{ statusFilter === 'aktif' ? 'Disetujui' : statusFilter === 'ditolak' ? 'Ditolak' : 'Semua riwayat' }}
            </p>
          </div>

          <span class="badge">{{ pagination?.total || 0 }} data</span>
        </div>

        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Nama Lembaga</th>
              <th>Jenis</th>
              <th>Email</th>
              <th>Status</th>
              <th>Reviewer</th>
              <th>Alasan Penolakan</th>
              <th>Tanggal Review</th>
            </tr>
          </thead>

          <tbody>
            <tr v-if="histories.length === 0">
              <td colspan="8">Tidak ada riwayat review.</td>
            </tr>

            <tr v-for="item in histories" :key="item.id_komunitas">
              <td>{{ item.id_komunitas }}</td>
              <td>{{ item.nama_lembaga }}</td>
              <td>{{ item.jenis_lembaga }}</td>
              <td>{{ item.email }}</td>
              <td>
                <span class="status-badge" :class="item.status === 'aktif' ? 'active' : 'inactive'">
                  {{ item.status === 'aktif' ? 'Disetujui' : 'Ditolak' }}
                </span>
              </td>
              <td>{{ item.reviewer || '-' }}</td>
              <td>{{ item.alasan_penolakan || '-' }}</td>
              <td>{{ item.tanggal_review ? new Date(item.tanggal_review).toLocaleDateString('id-ID') : '-' }}</td>
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
  display: flex;
  flex-wrap: wrap;
  gap: 16px 24px;
  margin-top: 16px;
  align-items: flex-end;
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

.date-range {
  display: flex;
  align-items: center;
  gap: 8px;
}

.date-range input[type="date"] {
  height: 36px;
  padding: 0 10px;
  border: 1px solid #dccdbb;
  border-radius: 8px;
  background: #fffaf2;
  color: #07313a;
  font-size: 13px;
  font-family: inherit;
}

.date-sep {
  color: #6f655b;
  font-weight: 700;
}

.filter-actions {
  display: flex;
  gap: 8px;
  align-items: flex-end;
  padding-bottom: 2px;
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

.reset-btn {
  height: 36px;
  padding: 0 18px;
  border: 1px solid #dccdbb;
  border-radius: 8px;
  background: #fffaf2;
  color: #6f655b;
  font-weight: 700;
  font-size: 13px;
  cursor: pointer;
}
</style>
