<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/services/api'
import NavBar from '@/components/NavBar.vue'
import AppFooter from '@/components/AppFooter.vue'

const changes = ref([])
const pagination = ref(null)
const loading = ref(true)
const errorMessage = ref('')

const currentPage = ref(1)

const selectedChange = ref(null)
const showDetailModal = ref(false)
const detailLoading = ref(false)
const detailError = ref('')

const rejectTarget = ref(null)
const showRejectModal = ref(false)
const rejectReason = ref('')
const rejecting = ref(false)
const rejectError = ref('')

const approving = ref(false)
const approveError = ref('')
const showApproveModal = ref(false)

const stats = ref({ pending: 0, approved: 0, rejected: 0 })

const fetchStats = async () => {
  try {
    const response = await api.get('/superadmin/bank-account-changes/stats')
    stats.value = response.data.data
  } catch {
    // silently fail
  }
}

const fetchChanges = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const params = { page: currentPage.value }
    const response = await api.get('/superadmin/bank-account-changes', { params })

    changes.value = response.data.data.data
    pagination.value = response.data.data
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || 'Gagal memuat daftar perubahan rekening.'
  } finally {
    loading.value = false
  }
}

const nextPage = () => {
  if (pagination.value?.next_page_url) {
    currentPage.value += 1
    fetchChanges()
  }
}

const prevPage = () => {
  if (pagination.value?.prev_page_url) {
    currentPage.value -= 1
    fetchChanges()
  }
}

const viewDetail = async (id) => {
  showDetailModal.value = true
  detailLoading.value = true
  detailError.value = ''
  selectedChange.value = null

  try {
    const response = await api.get(`/superadmin/bank-account-changes/${id}`)
    selectedChange.value = response.data.data
  } catch (error) {
    detailError.value =
      error.response?.data?.message || 'Gagal memuat detail perubahan rekening.'
  } finally {
    detailLoading.value = false
  }
}

const closeDetail = () => {
  showDetailModal.value = false
  selectedChange.value = null
  detailError.value = ''
}

const showApprove = () => {
  showApproveModal.value = true
  approveError.value = ''
}

const closeApprove = () => {
  showApproveModal.value = false
  approveError.value = ''
}

const confirmApprove = async () => {
  approving.value = true
  approveError.value = ''

  try {
    const id = selectedChange.value.id_verif
    await api.post(`/superadmin/bank-account-changes/${id}/approve`)
    showApproveModal.value = false
    showDetailModal.value = false
    selectedChange.value = null
    fetchChanges()
  } catch (error) {
    approveError.value =
      error.response?.data?.message || 'Gagal menyetujui perubahan rekening.'
  } finally {
    approving.value = false
  }
}

const showReject = () => {
  rejectTarget.value = selectedChange.value
  rejectReason.value = ''
  rejectError.value = ''
  showRejectModal.value = true
}

const closeReject = () => {
  showRejectModal.value = false
  rejectTarget.value = null
  rejectReason.value = ''
  rejectError.value = ''
}

const confirmReject = async () => {
  rejecting.value = true
  rejectError.value = ''

  try {
    const id = rejectTarget.value.id_verif
    await api.post(
      `/superadmin/bank-account-changes/${id}/reject`,
      { alasan_penolakan: rejectReason.value }
    )
    showRejectModal.value = false
    showDetailModal.value = false
    rejectTarget.value = null
    selectedChange.value = null
    fetchChanges()
  } catch (error) {
    rejectError.value =
      error.response?.data?.message || 'Gagal menolak perubahan rekening.'
  } finally {
    rejecting.value = false
  }
}

onMounted(() => {
  fetchChanges()
  fetchStats()
})
</script>

<template>
  <main class="dashboard-page">
    <NavBar />

    <section class="container">
      <div class="page-title">
        <div>
          <h1>Review Perubahan Rekening</h1>
          <p>Review dan setujui/tolak perubahan rekening komunitas.</p>
        </div>

        <RouterLink to="/dashboard/bank-account-changes/history" class="back-link">Riwayat Review</RouterLink>
      </div>

      <section class="stats-grid">
        <div class="stat-card">
          <p>Menunggu Review</p>
          <h2>{{ stats.pending }}</h2>
          <span>Perlu ditindaklanjuti</span>
        </div>

        <div class="stat-card">
          <p>Disetujui</p>
          <h2>{{ stats.approved }}</h2>
          <span>Total persetujuan</span>
        </div>

        <div class="stat-card">
          <p>Ditolak</p>
          <h2>{{ stats.rejected }}</h2>
          <span>Total penolakan</span>
        </div>
      </section>

      <section v-if="loading" class="card">Memuat daftar perubahan rekening...</section>

      <section v-else-if="errorMessage" class="card error">
        {{ errorMessage }}
      </section>

      <section v-else class="card">
        <div class="card-header">
          <div>
            <h2>Perubahan Rekening Baru</h2>
            <p>Daftar perubahan rekening yang menunggu review.</p>
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
              <th>Tanggal</th>
              <th>Aksi</th>
            </tr>
          </thead>

          <tbody>
            <tr v-if="changes.length === 0">
              <td colspan="8">Tidak ada perubahan rekening menunggu review.</td>
            </tr>

            <tr v-for="item in changes" :key="item.id_verif">
              <td>{{ item.id_verif }}</td>
              <td>{{ item.nama_lembaga }}</td>
              <td>{{ item.nama_bank_lama || '-' }}</td>
              <td>{{ item.nomor_rekening_lama || '-' }}</td>
              <td>{{ item.nama_bank_baru || '-' }}</td>
              <td>{{ item.nomor_rekening_baru || '-' }}</td>
              <td>{{ item.created_at ? new Date(item.created_at).toLocaleDateString('id-ID') : '-' }}</td>
              <td class="action-cell">
                <button class="mini-btn" @click="viewDetail(item.id_verif)">Detail</button>
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
          <h2>Detail Perubahan Rekening</h2>
          <button class="modal-close" @click="closeDetail">&times;</button>
        </div>

        <div v-if="detailLoading" class="card">Memuat detail...</div>

        <div v-else-if="detailError" class="card error">{{ detailError }}</div>

        <template v-else-if="selectedChange">
          <div class="detail-grid">
            <div class="detail-field">
              <strong>Nama Lembaga</strong>
              <span>{{ selectedChange.nama_lembaga }}</span>
            </div>

            <div class="detail-field">
              <strong>Status</strong>
              <span>{{ selectedChange.status }}</span>
            </div>
          </div>

          <hr class="profile-divider" />

          <div class="detail-section">
            <h3>Rekening Lama</h3>
            <div class="detail-grid">
              <div class="detail-field">
                <strong>Nama Bank</strong>
                <span>{{ selectedChange.nama_bank_lama || '-' }}</span>
              </div>
              <div class="detail-field">
                <strong>Nomor Rekening</strong>
                <span>{{ selectedChange.nomor_rekening_lama || '-' }}</span>
              </div>
            </div>
          </div>

          <hr class="profile-divider" />

          <div class="detail-section">
            <h3>Rekening Baru</h3>
            <div class="detail-grid">
              <div class="detail-field">
                <strong>Nama Bank</strong>
                <span>{{ selectedChange.nama_bank_baru || '-' }}</span>
              </div>
              <div class="detail-field">
                <strong>Nomor Rekening</strong>
                <span>{{ selectedChange.nomor_rekening_baru || '-' }}</span>
              </div>
            </div>

            <div v-if="selectedChange.foto_buku_rekening_url" class="detail-field" style="margin-top: 12px;">
              <strong>Foto Buku Rekening</strong>
              <div>
                <a :href="selectedChange.foto_buku_rekening_url" target="_blank" class="doc-link">Lihat Dokumen</a>
              </div>
            </div>
          </div>

          <hr class="profile-divider" />

          <div class="detail-section">
            <h3>Tindakan Review</h3>
            <div class="detail-grid">
              <div class="detail-field">
                <strong>Alasan Penolakan</strong>
                <span>{{ selectedChange.alasan_penolakan || '-' }}</span>
              </div>
              <div class="detail-field">
                <strong>Tanggal Keputusan</strong>
                <span>{{ selectedChange.tanggal_keputusan ? new Date(selectedChange.tanggal_keputusan).toLocaleDateString('id-ID') : '-' }}</span>
              </div>
            </div>
          </div>

          <hr class="profile-divider" />

          <div class="action-row">
            <button class="approve-btn" @click="showApprove">Setujui</button>
            <button class="reject-btn" @click="showReject">Tolak</button>
          </div>
        </template>
      </div>
    </div>

    <div v-if="showApproveModal" class="modal-backdrop" @click.self="closeApprove">
      <div class="auth-modal" style="width: 300px;">
        <div class="modal-icon success">✓</div>

        <h2>Setujui Perubahan Rekening</h2>

        <p>Apakah anda yakin ingin menyetujui perubahan rekening <strong>{{ selectedChange?.nama_lembaga }}</strong>?</p>

        <p v-if="approveError" class="field-error">{{ approveError }}</p>

        <button class="save-btn" :disabled="approving" @click="confirmApprove">
          {{ approving ? 'Menyetujui...' : 'Ya, Setujui' }}
        </button>

        <button class="cancel-btn" :disabled="approving" @click="closeApprove">
          Batal
        </button>
      </div>
    </div>

    <div v-if="showRejectModal" class="modal-backdrop" @click.self="closeReject">
      <div class="auth-modal" style="width: 360px;">
        <div class="modal-icon error">!</div>

        <h2>Tolak Perubahan Rekening</h2>

        <p>Berikan alasan penolakan untuk <strong>{{ rejectTarget?.nama_lembaga }}</strong>:</p>

        <textarea
          v-model="rejectReason"
          class="reject-textarea"
          placeholder="Alasan penolakan..."
          rows="4"
        ></textarea>

        <p v-if="rejectError" class="field-error">{{ rejectError }}</p>

        <button class="reject-btn" :disabled="rejecting || !rejectReason.trim()" @click="confirmReject">
          {{ rejecting ? 'Menolak...' : 'Ya, Tolak' }}
        </button>

        <button class="cancel-btn" :disabled="rejecting" @click="closeReject">
          Batal
        </button>
      </div>
    </div>

    <AppFooter />
  </main>
</template>

<style scoped>
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

.reject-textarea {
  width: 100%;
  padding: 10px;
  border: 1px solid #dccdbb;
  border-radius: 10px;
  background: #f7efe3;
  color: #07313a;
  font-family: inherit;
  font-size: 14px;
  resize: vertical;
  margin-bottom: 12px;
  box-sizing: border-box;
}

.doc-link {
  color: #a85f20;
  font-weight: 800;
  font-size: 13px;
}

.detail-field {
  margin-bottom: 16px;
}

.detail-section h3 {
  margin: 0 0 12px;
  font-size: 18px;
}
</style>
