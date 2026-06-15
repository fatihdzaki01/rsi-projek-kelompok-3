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

const reactivateTarget = ref(null)
const showReactivateModal = ref(false)
const reactivating = ref(false)
const reactivateError = ref('')

const closeTarget = ref(null)
const showCloseModal = ref(false)
const closing = ref(false)
const closeError = ref('')

const statuses = [
  { label: 'Semua', value: '' },
  { label: 'Nonaktif', value: 'nonaktif' },
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

    const response = await api.get('/superadmin/campaign-clarifications', { params })

    campaigns.value = response.data.data.data
    pagination.value = response.data.data
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || 'Gagal memuat daftar klarifikasi.'
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
    const response = await api.get(`/superadmin/campaign-clarifications/${id}`)
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

const showReactivate = () => {
  reactivateTarget.value = selectedCampaign.value
  reactivateError.value = ''
  showReactivateModal.value = true
}

const closeReactivate = () => {
  showReactivateModal.value = false
  reactivateTarget.value = null
  reactivateError.value = ''
}

const confirmReactivate = async () => {
  reactivating.value = true
  reactivateError.value = ''

  try {
    await api.post(`/superadmin/campaign-clarifications/${reactivateTarget.value.id_campaign}/reactivate`)
    showReactivateModal.value = false
    showDetailModal.value = false
    reactivateTarget.value = null
    selectedCampaign.value = null
    fetchCampaigns()
  } catch (error) {
    reactivateError.value =
      error.response?.data?.message || 'Gagal mengaktifkan campaign.'
  } finally {
    reactivating.value = false
  }
}

const showClose = () => {
  closeTarget.value = selectedCampaign.value
  closeError.value = ''
  showCloseModal.value = true
}

const closeClose = () => {
  showCloseModal.value = false
  closeTarget.value = null
  closeError.value = ''
}

const confirmClose = async () => {
  closing.value = true
  closeError.value = ''

  try {
    await api.post(`/superadmin/campaign-clarifications/${closeTarget.value.id_campaign}/close-permanently`)
    showCloseModal.value = false
    showDetailModal.value = false
    closeTarget.value = null
    selectedCampaign.value = null
    fetchCampaigns()
  } catch (error) {
    closeError.value =
      error.response?.data?.message || 'Gagal menutup campaign.'
  } finally {
    closing.value = false
  }
}

const canReactivate = (campaign) => campaign?.status === 'nonaktif'
const canClosePermanently = (campaign) => campaign?.status !== 'ditutup_permanen'

onMounted(fetchCampaigns)
</script>

<template>
  <main class="dashboard-page">
    <NavBar />

    <section class="container">
      <div class="page-title">
        <div>
          <h1>Klarifikasi Campaign</h1>
          <p>Kelola klarifikasi campaign nonaktif atau ditutup permanen.</p>
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

      <section v-if="loading" class="card">Memuat daftar klarifikasi...</section>

      <section v-else-if="errorMessage" class="card error">
        {{ errorMessage }}
      </section>

      <section v-else class="card">
        <div class="card-header">
          <div>
            <h2>Daftar Klarifikasi</h2>
            <p>Campaign nonaktif atau ditutup permanen.</p>
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
              <td colspan="7">Tidak ada klarifikasi ditemukan.</td>
            </tr>

            <tr v-for="c in campaigns" :key="c.id_campaign">
              <td>{{ c.id_campaign }}</td>
              <td>{{ c.judul }}</td>
              <td>{{ c.nama_lembaga }}</td>
              <td>{{ c.nama_kategori }}</td>
              <td>Rp{{ Number(c.target_dana).toLocaleString('id-ID') }}</td>
              <td>
                <span class="status-badge"
                  :class="c.status === 'nonaktif' ? 'inactive' : 'inactive'"
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
                  :class="selectedCampaign.status === 'nonaktif' ? 'inactive' : 'inactive'"
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
            <strong>Alasan</strong>
            <p>{{ selectedCampaign.alasan_penolakan }}</p>
          </div>

          <hr class="profile-divider" />

          <div class="action-row">
            <button
              v-if="canReactivate(selectedCampaign)"
              class="approve-btn"
              @click="showReactivate"
            >
              Aktifkan Kembali
            </button>
            <button
              v-if="canClosePermanently(selectedCampaign)"
              class="reject-btn"
              @click="showClose"
            >
              Tutup Permanen
            </button>
          </div>
        </template>
      </div>
    </div>

    <div v-if="showReactivateModal" class="modal-backdrop" @click.self="closeReactivate">
      <div class="auth-modal" style="width: 320px;">
        <div class="modal-icon success">✓</div>

        <h2>Aktifkan Kembali</h2>

        <p>Campaign <strong>{{ reactivateTarget?.judul }}</strong> akan diaktifkan kembali.</p>

        <p v-if="reactivateError" class="field-error">{{ reactivateError }}</p>

        <button class="save-btn" :disabled="reactivating" @click="confirmReactivate">
          {{ reactivating ? 'Memproses...' : 'Ya, Aktifkan' }}
        </button>

        <button class="cancel-btn" :disabled="reactivating" @click="closeReactivate">
          Batal
        </button>
      </div>
    </div>

    <div v-if="showCloseModal" class="modal-backdrop" @click.self="closeClose">
      <div class="auth-modal" style="width: 320px;">
        <div class="modal-icon error">!</div>

        <h2>Tutup Permanen</h2>

        <p>Apakah anda yakin ingin menutup permanen campaign <strong>{{ closeTarget?.judul }}</strong>? Tindakan ini tidak dapat dibatalkan.</p>

        <p v-if="closeError" class="field-error">{{ closeError }}</p>

        <button class="reject-btn" :disabled="closing" @click="confirmClose">
          {{ closing ? 'Memproses...' : 'Ya, Tutup Permanen' }}
        </button>

        <button class="cancel-btn" :disabled="closing" @click="closeClose">
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
