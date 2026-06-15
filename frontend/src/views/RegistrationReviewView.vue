<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/services/api'
import NavBar from '@/components/NavBar.vue'
import AppFooter from '@/components/AppFooter.vue'

const registrations = ref([])
const pagination = ref(null)
const loading = ref(true)
const errorMessage = ref('')

const currentPage = ref(1)

const selectedRegistration = ref(null)
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

const fetchRegistrations = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const params = { page: currentPage.value }
    const response = await api.get('/superadmin/community-registrations', { params })

    registrations.value = response.data.data.data
    pagination.value = response.data.data
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || 'Gagal memuat daftar pendaftaran.'
  } finally {
    loading.value = false
  }
}

const nextPage = () => {
  if (pagination.value?.next_page_url) {
    currentPage.value += 1
    fetchRegistrations()
  }
}

const prevPage = () => {
  if (pagination.value?.prev_page_url) {
    currentPage.value -= 1
    fetchRegistrations()
  }
}

const viewDetail = async (id) => {
  showDetailModal.value = true
  detailLoading.value = true
  detailError.value = ''
  selectedRegistration.value = null

  try {
    const response = await api.get(`/superadmin/community-registrations/${id}`)
    selectedRegistration.value = response.data.data
  } catch (error) {
    detailError.value =
      error.response?.data?.message || 'Gagal memuat detail pendaftaran.'
  } finally {
    detailLoading.value = false
  }
}

const closeDetail = () => {
  showDetailModal.value = false
  selectedRegistration.value = null
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
    const id = selectedRegistration.value.registration.id_komunitas
    await api.post(`/superadmin/community-registrations/${id}/approve`)
    showApproveModal.value = false
    showDetailModal.value = false
    selectedRegistration.value = null
    fetchRegistrations()
  } catch (error) {
    approveError.value =
      error.response?.data?.message || 'Gagal menyetujui pendaftaran.'
  } finally {
    approving.value = false
  }
}

const showReject = () => {
  rejectTarget.value = selectedRegistration.value
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
    const id = rejectTarget.value.registration.id_komunitas
    await api.post(
      `/superadmin/community-registrations/${id}/reject`,
      { alasan_penolakan: rejectReason.value }
    )
    showRejectModal.value = false
    showDetailModal.value = false
    rejectTarget.value = null
    selectedRegistration.value = null
    fetchRegistrations()
  } catch (error) {
    rejectError.value =
      error.response?.data?.message || 'Gagal menolak pendaftaran.'
  } finally {
    rejecting.value = false
  }
}

onMounted(fetchRegistrations)
</script>

<template>
  <main class="dashboard-page">
    <NavBar />

    <section class="container">
      <div class="page-title">
        <div>
          <h1>Review Pendaftaran Komunitas</h1>
          <p>Review dan setujui/tolak pendaftaran komunitas baru.</p>
        </div>

        <RouterLink to="/dashboard/community-registrations/history" class="back-link">Riwayat Review</RouterLink>
      </div>

      <section v-if="loading" class="card">Memuat daftar pendaftaran...</section>

      <section v-else-if="errorMessage" class="card error">
        {{ errorMessage }}
      </section>

      <section v-else class="card">
        <div class="card-header">
          <div>
            <h2>Pendaftaran Baru</h2>
            <p>Daftar komunitas yang menunggu review.</p>
          </div>

          <span class="badge">{{ pagination?.total || 0 }} data</span>
        </div>

        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Nama Lembaga</th>
              <th>Jenis</th>
              <th>Pengurus</th>
              <th>Email</th>
              <th>Kontak</th>
              <th>Wilayah</th>
              <th>Tanggal Daftar</th>
              <th>Aksi</th>
            </tr>
          </thead>

          <tbody>
            <tr v-if="registrations.length === 0">
              <td colspan="9">Tidak ada pendaftaran menunggu review.</td>
            </tr>

            <tr v-for="reg in registrations" :key="reg.id_komunitas">
              <td>{{ reg.id_komunitas }}</td>
              <td>{{ reg.nama_lembaga }}</td>
              <td>{{ reg.jenis_lembaga }}</td>
              <td>{{ reg.nama_pengurus }}</td>
              <td>{{ reg.email }}</td>
              <td>{{ reg.nomor_kontak }}</td>
              <td>{{ reg.nama_wilayah }}</td>
              <td>{{ new Date(reg.created_at).toLocaleDateString('id-ID') }}</td>
              <td class="action-cell">
                <button class="mini-btn" @click="viewDetail(reg.id_komunitas)">Detail</button>
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
          <h2>Detail Pendaftaran</h2>
          <button class="modal-close" @click="closeDetail">&times;</button>
        </div>

        <div v-if="detailLoading" class="card">Memuat detail...</div>

        <div v-else-if="detailError" class="card error">{{ detailError }}</div>

        <template v-else-if="selectedRegistration">
          <div class="detail-grid">
            <div class="detail-field">
              <strong>Nama Lembaga</strong>
              <span>{{ selectedRegistration.registration.nama_lembaga }}</span>
            </div>

            <div class="detail-field">
              <strong>Jenis Lembaga</strong>
              <span>{{ selectedRegistration.registration.jenis_lembaga }}</span>
            </div>

            <div class="detail-field">
              <strong>Nama Pengurus</strong>
              <span>{{ selectedRegistration.registration.nama_lengkap }}</span>
            </div>

            <div class="detail-field">
              <strong>Username</strong>
              <span>{{ selectedRegistration.registration.username }}</span>
            </div>

            <div class="detail-field">
              <strong>Email</strong>
              <span>{{ selectedRegistration.registration.email }}</span>
            </div>

            <div class="detail-field">
              <strong>Nomor Kontak</strong>
              <span>{{ selectedRegistration.registration.nomor_kontak || '-' }}</span>
            </div>

            <div class="detail-field">
              <strong>Wilayah</strong>
              <span>{{ selectedRegistration.registration.nama_wilayah || '-' }}</span>
            </div>

            <div class="detail-field">
              <strong>Tanggal Daftar</strong>
              <span>{{ selectedRegistration.registration.created_at ? new Date(selectedRegistration.registration.created_at).toLocaleDateString('id-ID') : '-' }}</span>
            </div>
          </div>

          <div v-if="selectedRegistration.registration.deskripsi" class="description-box">
            <strong>Deskripsi</strong>
            <p>{{ selectedRegistration.registration.deskripsi }}</p>
          </div>

          <hr class="profile-divider" />

          <div class="detail-section">
            <h3>Dokumen</h3>

            <table v-if="selectedRegistration.documents?.length" class="history-table">
              <thead>
                <tr>
                  <th>Nama Dokumen</th>
                  <th>URL</th>
                </tr>
              </thead>

              <tbody>
                <tr v-for="doc in selectedRegistration.documents" :key="doc.id_jenis_dok">
                  <td>{{ doc.nama_dokumen }}</td>
                  <td>
                    <a v-if="doc.url_dokumen" :href="doc.url_dokumen" target="_blank" class="doc-link">Lihat</a>
                    <span v-else>-</span>
                  </td>
                </tr>
              </tbody>
            </table>

            <p v-else class="empty-text">Tidak ada dokumen.</p>
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

        <h2>Setujui Pendaftaran</h2>

        <p>Apakah anda yakin ingin menyetujui pendaftaran <strong>{{ selectedRegistration?.registration.nama_lembaga }}</strong>?</p>

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

        <h2>Tolak Pendaftaran</h2>

        <p>Berikan alasan penolakan untuk <strong>{{ rejectTarget?.registration.nama_lembaga }}</strong>:</p>

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

.description-box {
  margin-top: 16px;
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

.doc-link {
  color: #a85f20;
  font-weight: 800;
  font-size: 13px;
}
</style>
