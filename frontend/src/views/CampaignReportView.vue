<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/services/api'
import NavBar from '@/components/NavBar.vue'
import AppFooter from '@/components/AppFooter.vue'

const campaigns = ref([])
const pagination = ref(null)
const loading = ref(true)
const errorMessage = ref('')

const statusFilter = ref('')
const currentPage = ref(1)

const selectedCampaign = ref(null)
const showDetailModal = ref(false)
const detailLoading = ref(false)
const detailError = ref('')

const disableTarget = ref(null)
const showDisableModal = ref(false)
const disabling = ref(false)
const disableError = ref('')

const ignoreTarget = ref(null)
const showIgnoreModal = ref(false)
const ignoring = ref(false)
const ignoreError = ref('')

const statuses = [
  { label: 'Semua', value: '' },
  { label: 'Menunggu Review', value: 'menunggu_review' },
  { label: 'Nonaktif', value: 'nonaktif' },
  { label: 'Ditolak', value: 'ditolak' },
  { label: 'Ditutup Permanen', value: 'ditutup_permanen' },
]

const fetchCampaigns = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const params = { page: currentPage.value }

    if (statusFilter.value) {
      params.status = statusFilter.value
    }

    const response = await api.get('/superadmin/campaign-reports', { params })

    campaigns.value = response.data.data.data
    pagination.value = response.data.data
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || 'Gagal memuat daftar laporan.'
  } finally {
    loading.value = false
  }
}

const applyFilters = () => {
  currentPage.value = 1
  fetchCampaigns()
}

const nextPage = () => {
  if (pagination.value?.next_page_url) {
    currentPage.value += 1
    fetchCampaigns()
  }
}

const prevPage = () => {
  if (pagination.value?.prev_page_url) {
    currentPage.value -= 1
    fetchCampaigns()
  }
}

const viewDetail = async (id) => {
  showDetailModal.value = true
  detailLoading.value = true
  detailError.value = ''
  selectedCampaign.value = null

  try {
    const response = await api.get(`/superadmin/campaign-reports/${id}`)
    selectedCampaign.value = response.data.data
  } catch (error) {
    detailError.value =
      error.response?.data?.message || 'Gagal memuat detail campaign.'
  } finally {
    detailLoading.value = false
  }
}

const closeDetail = () => {
  showDetailModal.value = false
  selectedCampaign.value = null
  detailError.value = ''
}

const showIgnore = () => {
  ignoreTarget.value = selectedCampaign.value
  ignoreError.value = ''
  showIgnoreModal.value = true
}

const closeIgnore = () => {
  showIgnoreModal.value = false
  ignoreTarget.value = null
  ignoreError.value = ''
}

const confirmIgnore = async () => {
  ignoring.value = true
  ignoreError.value = ''

  try {
    await api.post(`/superadmin/campaign-reports/${ignoreTarget.value.id_campaign}/ignore`)
    showIgnoreModal.value = false
    showDetailModal.value = false
    ignoreTarget.value = null
    selectedCampaign.value = null
    fetchCampaigns()
  } catch (error) {
    ignoreError.value =
      error.response?.data?.message || 'Gagal mengabaikan laporan.'
  } finally {
    ignoring.value = false
  }
}

const showDisable = () => {
  disableTarget.value = selectedCampaign.value
  disableError.value = ''
  showDisableModal.value = true
}

const closeDisable = () => {
  showDisableModal.value = false
  disableTarget.value = null
  disableError.value = ''
}

const confirmDisable = async () => {
  disabling.value = true
  disableError.value = ''

  try {
    await api.post(`/superadmin/campaigns/${disableTarget.value.id_campaign}/disable`)
    showDisableModal.value = false
    showDetailModal.value = false
    disableTarget.value = null
    selectedCampaign.value = null
    fetchCampaigns()
  } catch (error) {
    disableError.value =
      error.response?.data?.message || 'Gagal menonaktifkan campaign.'
  } finally {
    disabling.value = false
  }
}

const canIgnore = (campaign) => campaign?.status === 'menunggu_review'
const canDisable = (campaign) => campaign?.status !== 'nonaktif'

onMounted(fetchCampaigns)
</script>

<template>
  <main class="dashboard-page">
    <NavBar />

    <section class="container">
      <div class="page-title">
        <div>
          <h1>Laporan Campaign</h1>
          <p>Kelola laporan campaign yang masuk.</p>
        </div>

        <RouterLink to="/dashboard" class="back-link">Kembali Dashboard</RouterLink>
      </div>

      <div class="filter-bar secondary-filters">
        <div class="filter-group">
          <label>Status</label>
          <div class="filter-row">
            <select v-model="statusFilter" class="filter-select">
              <option
                v-for="s in statuses"
                :key="s.value"
                :value="s.value"
              >
                {{ s.label }}
              </option>
            </select>
            <button class="apply-btn" @click="applyFilters">Terapkan</button>
          </div>
        </div>
      </div>

      <section v-if="loading" class="card">Memuat daftar laporan...</section>

      <section v-else-if="errorMessage" class="card error">
        {{ errorMessage }}
      </section>

      <section v-else class="card">
        <div class="card-header">
          <div>
            <h2>Daftar Laporan</h2>
            <p>Campaign yang memerlukan tindakan.</p>
          </div>

          <span class="badge">{{ pagination?.total || 0 }} data</span>
        </div>

        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Judul</th>
              <th>Lembaga</th>
              <th>Kategori</th>
              <th>Target Dana</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>

          <tbody>
            <tr v-if="campaigns.length === 0">
              <td colspan="7">Tidak ada laporan ditemukan.</td>
            </tr>

            <tr v-for="c in campaigns" :key="c.id_campaign">
              <td>{{ c.id_campaign }}</td>
              <td>{{ c.judul }}</td>
              <td>{{ c.nama_lembaga }}</td>
              <td>{{ c.nama_kategori }}</td>
              <td>Rp{{ Number(c.target_dana).toLocaleString('id-ID') }}</td>
              <td>
                <span class="status-badge"
                  :class="c.status === 'aktif' ? 'active' : 'inactive'"
                >
                  {{ c.status }}
                </span>
              </td>
              <td class="action-cell">
                <button class="mini-btn" @click="viewDetail(c.id_campaign)">Detail</button>
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
          <h2>Detail Campaign</h2>
          <button class="modal-close" @click="closeDetail">&times;</button>
        </div>

        <div v-if="detailLoading" class="card">Memuat detail...</div>

        <div v-else-if="detailError" class="card error">{{ detailError }}</div>

        <template v-else-if="selectedCampaign">
          <div class="detail-grid">
            <div class="detail-field">
              <strong>Judul</strong>
              <span>{{ selectedCampaign.judul }}</span>
            </div>

            <div class="detail-field">
              <strong>Lembaga</strong>
              <span>{{ selectedCampaign.nama_lembaga }}</span>
            </div>

            <div class="detail-field">
              <strong>Kategori</strong>
              <span>{{ selectedCampaign.nama_kategori }}</span>
            </div>

            <div class="detail-field">
              <strong>Wilayah</strong>
              <span>{{ selectedCampaign.nama_wilayah || '-' }}</span>
            </div>

            <div class="detail-field">
              <strong>Target Dana</strong>
              <span>Rp{{ Number(selectedCampaign.target_dana).toLocaleString('id-ID') }}</span>
            </div>

            <div class="detail-field">
              <strong>Dana Terkumpul</strong>
              <span>Rp{{ Number(selectedCampaign.dana_terkumpul).toLocaleString('id-ID') }}</span>
            </div>

            <div class="detail-field">
              <strong>Status</strong>
              <span>
                <span class="status-badge"
                  :class="selectedCampaign.status === 'aktif' || selectedCampaign.status === 'menunggu_review' ? 'active' : 'inactive'"
                >
                  {{ selectedCampaign.status }}
                </span>
              </span>
            </div>

            <div class="detail-field">
              <strong>Reviewer</strong>
              <span>{{ selectedCampaign.reviewer || '-' }}</span>
            </div>
          </div>

          <div v-if="selectedCampaign.deskripsi" class="description-box">
            <strong>Deskripsi</strong>
            <p>{{ selectedCampaign.deskripsi }}</p>
          </div>

          <div v-if="selectedCampaign.alasan_penolakan" class="description-box">
            <strong>Alasan Penolakan</strong>
            <p>{{ selectedCampaign.alasan_penolakan }}</p>
          </div>

          <hr class="profile-divider" />

          <div class="action-row">
            <button
              v-if="canIgnore(selectedCampaign)"
              class="approve-btn"
              @click="showIgnore"
            >
              Abaikan Laporan
            </button>
            <button
              v-if="canDisable(selectedCampaign)"
              class="reject-btn"
              @click="showDisable"
            >
              Nonaktifkan Campaign
            </button>
          </div>
        </template>
      </div>
    </div>

    <div v-if="showIgnoreModal" class="modal-backdrop" @click.self="closeIgnore">
      <div class="auth-modal" style="width: 300px;">
        <div class="modal-icon success">✓</div>

        <h2>Abaikan Laporan</h2>

        <p>Laporan untuk <strong>{{ ignoreTarget?.judul }}</strong> akan diabaikan dan campaign diaktifkan kembali.</p>

        <p v-if="ignoreError" class="field-error">{{ ignoreError }}</p>

        <button class="save-btn" :disabled="ignoring" @click="confirmIgnore">
          {{ ignoring ? 'Memproses...' : 'Ya, Abaikan' }}
        </button>

        <button class="cancel-btn" :disabled="ignoring" @click="closeIgnore">
          Batal
        </button>
      </div>
    </div>

    <div v-if="showDisableModal" class="modal-backdrop" @click.self="closeDisable">
      <div class="auth-modal" style="width: 300px;">
        <div class="modal-icon error">!</div>

        <h2>Nonaktifkan Campaign</h2>

        <p>Apakah anda yakin ingin menonaktifkan campaign <strong>{{ disableTarget?.judul }}</strong>?</p>

        <p v-if="disableError" class="field-error">{{ disableError }}</p>

        <button class="reject-btn" :disabled="disabling" @click="confirmDisable">
          {{ disabling ? 'Memproses...' : 'Ya, Nonaktifkan' }}
        </button>

        <button class="cancel-btn" :disabled="disabling" @click="closeDisable">
          Batal
        </button>
      </div>
    </div>

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
  min-width: 170px;
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

.action-row {
  display: flex;
  gap: 12px;
  margin-top: 12px;
}

.action-row button {
  flex: 1;
  height: 42px;
  border: 0;
  border-radius: 10px;
  color: white;
  font-weight: 800;
  cursor: pointer;
}

.description-box {
  margin-top: 14px;
  padding: 14px;
  border-radius: 12px;
  background: #f7efe3;
}

.description-box strong {
  display: block;
  margin-bottom: 6px;
  color: #07313a;
}

.description-box p {
  margin: 0;
  color: #6f655b;
}

.detail-field {
  margin-bottom: 16px;
}
</style>
