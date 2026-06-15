<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/services/api'
import NavBar from '@/components/NavBar.vue'
import AppFooter from '@/components/AppFooter.vue'

const donors = ref([])
const pagination = ref(null)
const loading = ref(true)
const errorMessage = ref('')

const searchQuery = ref('')
const statusFilter = ref('')
const nominalMin = ref('')
const nominalMax = ref('')
const dateFrom = ref('')
const dateTo = ref('')
const sortBy = ref('created_at')
const sortOrder = ref('desc')
const currentPage = ref(1)

const sortOptions = [
  { label: 'Tanggal Daftar', value: 'created_at' },
  { label: 'Nama', value: 'nama_lengkap' },
  { label: 'Total Donasi', value: 'total_transaksi_donasi' },
  { label: 'Nominal', value: 'total_nominal_donasi' },
]

const selectedDonor = ref(null)
const showDetailModal = ref(false)
const detailLoading = ref(false)
const detailError = ref('')

const toggleTarget = ref(null)
const showConfirmModal = ref(false)
const toggling = ref(false)
const toggleError = ref('')

const fetchDonors = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const params = { page: currentPage.value }

    if (searchQuery.value.trim()) {
      params.search = searchQuery.value.trim()
    }

    if (statusFilter.value) {
      params.is_active = statusFilter.value
    }

    if (nominalMin.value) {
      params.nominal_min = nominalMin.value
    }

    if (nominalMax.value) {
      params.nominal_max = nominalMax.value
    }

    if (dateFrom.value) {
      params.created_from = dateFrom.value
    }

    if (dateTo.value) {
      params.created_to = dateTo.value
    }

    params.sort_by = sortBy.value
    params.sort_order = sortOrder.value

    const response = await api.get('/superadmin/donors', { params })

    donors.value = response.data.data.data
    pagination.value = response.data.data
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || 'Gagal memuat data donatur.'
  } finally {
    loading.value = false
  }
}

const filterStatus = (status) => {
  statusFilter.value = status
  currentPage.value = 1
  fetchDonors()
}

const applyFilters = () => {
  currentPage.value = 1
  fetchDonors()
}

const resetFilters = () => {
  searchQuery.value = ''
  statusFilter.value = ''
  nominalMin.value = ''
  nominalMax.value = ''
  dateFrom.value = ''
  dateTo.value = ''
  sortBy.value = 'created_at'
  sortOrder.value = 'desc'
  currentPage.value = 1
  fetchDonors()
}

const nextPage = () => {
  if (pagination.value?.next_page_url) {
    currentPage.value += 1
    fetchDonors()
  }
}

const prevPage = () => {
  if (pagination.value?.prev_page_url) {
    currentPage.value -= 1
    fetchDonors()
  }
}

const viewDetail = async (id) => {
  showDetailModal.value = true
  detailLoading.value = true
  detailError.value = ''
  selectedDonor.value = null

  try {
    const response = await api.get(`/superadmin/donors/${id}`)
    selectedDonor.value = response.data.data
  } catch (error) {
    detailError.value =
      error.response?.data?.message || 'Gagal memuat detail donatur.'
  } finally {
    detailLoading.value = false
  }
}

const closeDetail = () => {
  showDetailModal.value = false
  selectedDonor.value = null
  detailError.value = ''
}

const showToggleConfirm = (donor) => {
  toggleTarget.value = donor
  toggleError.value = ''
  showConfirmModal.value = true
}

const confirmToggle = async () => {
  toggling.value = true
  toggleError.value = ''

  try {
    await api.patch(
      `/superadmin/donors/${toggleTarget.value.id_user}/status`,
      { is_active: !toggleTarget.value.is_active }
    )
    showConfirmModal.value = false
    toggleTarget.value = null
    fetchDonors()
  } catch (error) {
    toggleError.value =
      error.response?.data?.message || 'Gagal mengubah status donatur.'
  } finally {
    toggling.value = false
  }
}

const closeConfirm = () => {
  showConfirmModal.value = false
  toggleTarget.value = null
  toggleError.value = ''
}

onMounted(fetchDonors)
</script>

<template>
  <main class="dashboard-page">
    <NavBar />

    <section class="container">
      <div class="page-title">
        <div>
          <h1>Kelola Donatur</h1>
          <p>Kelola dan pantau aktivitas seluruh donatur.</p>
        </div>

        <RouterLink to="/dashboard" class="back-link">Kembali Dashboard</RouterLink>
      </div>

      <div class="filter-bar">
        <div class="search-box">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari donatur..."
          />
        </div>

        <div class="approval-tabs">
          <button
            :class="{ active: statusFilter === '' }"
            @click="filterStatus('')"
          >
            Semua
          </button>
          <button
            :class="{ active: statusFilter === 'true' }"
            @click="filterStatus('true')"
          >
            Aktif
          </button>
          <button
            :class="{ active: statusFilter === 'false' }"
            @click="filterStatus('false')"
          >
            Nonaktif
          </button>
        </div>
      </div>

      <div class="filter-bar secondary-filters">
        <div class="filter-group">
          <label>Nominal Donasi</label>
          <div class="nominal-range">
            <span class="nominal-prefix">Rp</span>
            <input
              v-model="nominalMin"
              type="number"
              placeholder="Min"
              min="0"
            />
            <span class="date-sep">—</span>
            <span class="nominal-prefix">Rp</span>
            <input
              v-model="nominalMax"
              type="number"
              placeholder="Maks"
              min="0"
            />
          </div>
        </div>

        <div class="filter-group">
          <label>Periode Bergabung</label>
          <div class="date-range">
            <input
              v-model="dateFrom"
              type="date"
            />
            <span class="date-sep">—</span>
            <input
              v-model="dateTo"
              type="date"
            />
          </div>
        </div>

        <div class="filter-group sort-group">
          <label>Urutkan</label>
          <div class="sort-controls">
            <select v-model="sortBy">
              <option
                v-for="opt in sortOptions"
                :key="opt.value"
                :value="opt.value"
              >
                {{ opt.label }}
              </option>
            </select>
            <button class="sort-order-btn" @click="sortOrder = sortOrder === 'asc' ? 'desc' : 'asc'">
              {{ sortOrder === 'asc' ? '↑' : '↓' }}
            </button>
          </div>
        </div>

        <div class="filter-actions">
          <button class="apply-btn" @click="applyFilters">Terapkan</button>
          <button class="reset-btn" @click="resetFilters">Reset</button>
        </div>
      </div>

      <section v-if="loading" class="card">Memuat data donatur...</section>

      <section v-else-if="errorMessage" class="card error">
        {{ errorMessage }}
      </section>

      <section v-else class="card">
        <div class="card-header">
          <div>
            <h2>Daftar Donatur</h2>
            <p>
              {{ searchQuery ? `Hasil pencarian "${searchQuery}"` : 'Seluruh donatur terdaftar.' }}
            </p>
          </div>

          <span class="badge">{{ pagination?.total || 0 }} data</span>
        </div>

        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Nama</th>
              <th>Email</th>
              <th>Username</th>
              <th>Total Donasi</th>
              <th>Nominal</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>

          <tbody>
            <tr v-if="donors.length === 0">
              <td colspan="8">Tidak ada donatur ditemukan.</td>
            </tr>

            <tr v-for="donor in donors" :key="donor.id_user">
              <td>{{ donor.id_user }}</td>
              <td>{{ donor.nama_lengkap }}</td>
              <td>{{ donor.email }}</td>
              <td>{{ donor.username }}</td>
              <td>{{ donor.total_transaksi_donasi }}</td>
              <td>Rp{{ Number(donor.total_nominal_donasi).toLocaleString('id-ID') }}</td>
              <td>
                <span class="status-badge" :class="donor.is_active ? 'active' : 'inactive'">
                  {{ donor.is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
              </td>
              <td class="action-cell">
                <button class="mini-btn" @click="viewDetail(donor.id_user)">Detail</button>
                <button
                  class="mini-btn"
                  :class="donor.is_active ? 'danger' : 'success'"
                  @click="showToggleConfirm(donor)"
                >
                  {{ donor.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                </button>
              </td>
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

    <div v-if="showDetailModal" class="modal-backdrop" @click.self="closeDetail">
      <div class="donor-modal">
        <div class="modal-header">
          <h2>Detail Donatur</h2>
          <button class="modal-close" @click="closeDetail">&times;</button>
        </div>

        <div v-if="detailLoading" class="card">Memuat detail...</div>

        <div v-else-if="detailError" class="card error">{{ detailError }}</div>

        <template v-else-if="selectedDonor">
          <div class="detail-grid">
            <div class="detail-field">
              <strong>Nama Lengkap</strong>
              <span>{{ selectedDonor.nama_lengkap }}</span>
            </div>

            <div class="detail-field">
              <strong>Email</strong>
              <span>{{ selectedDonor.email }}</span>
            </div>

            <div class="detail-field">
              <strong>Username</strong>
              <span>{{ selectedDonor.username }}</span>
            </div>

            <div class="detail-field">
              <strong>No. Telepon</strong>
              <span>{{ selectedDonor.nomor_telepon || '-' }}</span>
            </div>

            <div class="detail-field">
              <strong>Status</strong>
              <span>
                <span class="status-badge" :class="selectedDonor.is_active ? 'active' : 'inactive'">
                  {{ selectedDonor.is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
              </span>
            </div>

            <div class="detail-field">
              <strong>Tanggal Registrasi</strong>
              <span>{{ selectedDonor.created_at ? new Date(selectedDonor.created_at).toLocaleDateString('id-ID') : '-' }}</span>
        </div>

        <div class="filter-actions">
          <button class="apply-btn" @click="applyFilters">Terapkan</button>
          <button class="reset-btn" @click="resetFilters">Reset</button>
        </div>
      </div>

          <hr class="profile-divider" />

          <div class="detail-section">
            <h3>Riwayat Donasi</h3>

            <table v-if="selectedDonor.donation_history?.length" class="history-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Campaign</th>
                  <th>Nominal</th>
                  <th>Status</th>
                  <th>Tanggal</th>
                </tr>
              </thead>

              <tbody>
                <tr v-for="donation in selectedDonor.donation_history" :key="donation.id_donasi">
                  <td>{{ donation.id_donasi }}</td>
                  <td>{{ donation.judul_campaign }}</td>
                  <td>Rp{{ Number(donation.nominal).toLocaleString('id-ID') }}</td>
                  <td>
                    <span class="status">{{ donation.status }}</span>
                  </td>
                  <td>{{ new Date(donation.created_at).toLocaleDateString('id-ID') }}</td>
                </tr>
              </tbody>
            </table>

            <p v-else class="empty-text">Tidak ada riwayat donasi.</p>
          </div>
        </template>
      </div>
    </div>

    <div v-if="showConfirmModal" class="modal-backdrop" @click.self="closeConfirm">
      <div class="auth-modal">
        <div class="modal-icon" :class="toggleTarget?.is_active ? 'error' : 'success'">
          {{ toggleTarget?.is_active ? '!' : '✓' }}
        </div>

        <h2>Konfirmasi</h2>

        <p>
          Apakah anda yakin ingin
          {{ toggleTarget?.is_active ? 'menonaktifkan' : 'mengaktifkan' }}
          donatur <strong>{{ toggleTarget?.nama_lengkap }}</strong>?
        </p>

        <p v-if="toggleError" class="field-error">{{ toggleError }}</p>

        <button
          class="save-btn"
          :class="toggleTarget?.is_active ? 'danger' : ''"
          :disabled="toggling"
          @click="confirmToggle"
        >
          {{ toggling ? 'Menyimpan...' : 'Ya' }}
        </button>

        <button class="cancel-btn" :disabled="toggling" @click="closeConfirm">
          Batal
        </button>
      </div>
    </div>

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

.nominal-range {
  display: flex;
  align-items: center;
  gap: 6px;
}

.nominal-range input[type="number"] {
  width: 120px;
  height: 36px;
  padding: 0 10px;
  border: 1px solid #dccdbb;
  border-radius: 8px;
  background: #fffaf2;
  color: #07313a;
  font-size: 13px;
  font-family: inherit;
  -moz-appearance: textfield;
}

.nominal-range input[type="number"]::-webkit-outer-spin-button,
.nominal-range input[type="number"]::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

.nominal-prefix {
  color: #6f655b;
  font-weight: 700;
  font-size: 13px;
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

.sort-controls {
  display: flex;
  gap: 6px;
}

.sort-controls select {
  height: 36px;
  padding: 0 10px;
  border: 1px solid #dccdbb;
  border-radius: 8px;
  background: #fffaf2;
  color: #07313a;
  font-size: 13px;
  font-family: inherit;
}

.sort-order-btn {
  width: 36px;
  height: 36px;
  border: 1px solid #dccdbb;
  border-radius: 8px;
  background: #fffaf2;
  color: #a85f20;
  font-size: 16px;
  font-weight: 800;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
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
