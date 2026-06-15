<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/services/api'
import NavBar from '@/components/NavBar.vue'
import AppFooter from '@/components/AppFooter.vue'

const communities = ref([])
const pagination = ref(null)
const loading = ref(true)
const errorMessage = ref('')

const searchQuery = ref('')
const statusFilter = ref('')
const lembagaFilter = ref('')
const jenisLembagaList = ref([])
const currentPage = ref(1)

const selectedCommunity = ref(null)
const showDetailModal = ref(false)
const detailLoading = ref(false)
const detailError = ref('')

const toggleTarget = ref(null)
const showConfirmModal = ref(false)
const toggling = ref(false)
const toggleError = ref('')

const showAddModal = ref(false)
const adding = ref(false)
const addError = ref('')
const addForm = ref({
  nama_lembaga: '',
  id_jenis_lembaga: '',
  email: '',
  username: '',
  password: '',
  nomor_kontak: '',
  wilayah: '',
  nama_bank: '',
  nomor_rekening: '',
  deskripsi: '',
})

const fetchCommunities = async () => {
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

    if (lembagaFilter.value) {
      params.id_jenis_lembaga = lembagaFilter.value
    }

    const response = await api.get('/superadmin/communities', { params })

    communities.value = response.data.data.data
    pagination.value = response.data.data
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || 'Gagal memuat data komunitas.'
  } finally {
    loading.value = false
  }
}

const fetchJenisLembaga = async () => {
  try {
    const response = await api.get('/superadmin/community-types')
    jenisLembagaList.value = response.data.data
  } catch {
    // silently fail
  }
}

const applyFilters = () => {
  currentPage.value = 1
  fetchCommunities()
}

const resetFilters = () => {
  searchQuery.value = ''
  statusFilter.value = ''
  lembagaFilter.value = ''
  currentPage.value = 1
  fetchCommunities()
}

const nextPage = () => {
  if (pagination.value?.next_page_url) {
    currentPage.value += 1
    fetchCommunities()
  }
}

const prevPage = () => {
  if (pagination.value?.prev_page_url) {
    currentPage.value -= 1
    fetchCommunities()
  }
}

const viewDetail = async (id) => {
  showDetailModal.value = true
  detailLoading.value = true
  detailError.value = ''
  selectedCommunity.value = null

  try {
    const response = await api.get(`/superadmin/communities/${id}`)
    selectedCommunity.value = response.data.data
  } catch (error) {
    detailError.value =
      error.response?.data?.message || 'Gagal memuat detail komunitas.'
  } finally {
    detailLoading.value = false
  }
}

const closeDetail = () => {
  showDetailModal.value = false
  selectedCommunity.value = null
  detailError.value = ''
}

const showToggleConfirm = (community) => {
  toggleTarget.value = community
  toggleError.value = ''
  showConfirmModal.value = true
}

const confirmToggle = async () => {
  toggling.value = true
  toggleError.value = ''

  try {
    await api.patch(
      `/superadmin/communities/${toggleTarget.value.id_komunitas}/status`,
      { is_active: toggleTarget.value.status !== 'aktif' }
    )
    showConfirmModal.value = false
    toggleTarget.value = null
    fetchCommunities()
  } catch (error) {
    toggleError.value =
      error.response?.data?.message || 'Gagal mengubah status komunitas.'
  } finally {
    toggling.value = false
  }
}

const closeConfirm = () => {
  showConfirmModal.value = false
  toggleTarget.value = null
  toggleError.value = ''
}

const openAddModal = () => {
  addForm.value = {
    nama_lembaga: '',
    id_jenis_lembaga: '',
    email: '',
    username: '',
    password: '',
    nomor_kontak: '',
    wilayah: '',
    nama_bank: '',
    nomor_rekening: '',
    deskripsi: '',
  }
  addError.value = ''
  showAddModal.value = true
}

const closeAddModal = () => {
  showAddModal.value = false
  addError.value = ''
}

const submitAddCommunity = async () => {
  adding.value = true
  addError.value = ''

  try {
    await api.post('/superadmin/communities', addForm.value)
    showAddModal.value = false
    fetchCommunities()
  } catch (error) {
    addError.value =
      error.response?.data?.message || 'Gagal menambahkan komunitas.'
  } finally {
    adding.value = false
  }
}

onMounted(() => {
  fetchCommunities()
  fetchJenisLembaga()
})
</script>

<template>
  <main class="dashboard-page">
    <NavBar />

    <section class="container">
      <div class="page-title">
        <div>
          <h1>Kelola Komunitas</h1>
          <p>Kelola dan pantau seluruh komunitas yang terdaftar.</p>
        </div>

        <div class="page-actions">
          <button class="add-btn" @click="openAddModal">+ Tambah Komunitas</button>
          <RouterLink to="/dashboard" class="back-link">Kembali Dashboard</RouterLink>
        </div>
      </div>

      <div class="filter-bar">
        <div class="search-box">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari komunitas..."
          />
        </div>
      </div>

      <div class="filter-bar secondary-filters">
        <div class="filter-group">
          <label>Status</label>
          <select v-model="statusFilter" class="filter-select">
            <option value="">Semua</option>
            <option value="aktif">Aktif</option>
            <option value="nonaktif">Nonaktif</option>
          </select>
        </div>

        <div class="filter-group">
          <label>Jenis Lembaga</label>
          <select v-model="lembagaFilter" class="filter-select">
            <option value="">Semua</option>
            <option
              v-for="jl in jenisLembagaList"
              :key="jl.id_jenis"
              :value="jl.id_jenis"
            >
              {{ jl.nama_jenis }}
            </option>
          </select>
        </div>

        <div class="filter-actions">
          <button class="apply-btn" @click="applyFilters">Terapkan</button>
          <button class="reset-btn" @click="resetFilters">Reset</button>
        </div>
      </div>

      <section v-if="loading" class="card">Memuat data komunitas...</section>

      <section v-else-if="errorMessage" class="card error">
        {{ errorMessage }}
      </section>

      <section v-else class="card">
        <div class="card-header">
          <div>
            <h2>Daftar Komunitas</h2>
            <p>
              {{ searchQuery ? `Hasil pencarian "${searchQuery}"` : 'Seluruh komunitas terdaftar.' }}
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
              <th>Kontak</th>
              <th>Total Campaign</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>

          <tbody>
            <tr v-if="communities.length === 0">
              <td colspan="8">Tidak ada komunitas ditemukan.</td>
            </tr>

            <tr v-for="community in communities" :key="community.id_komunitas">
              <td>{{ community.id_komunitas }}</td>
              <td>{{ community.nama_lembaga }}</td>
              <td>{{ community.jenis_lembaga }}</td>
              <td>{{ community.email }}</td>
              <td>{{ community.nomor_kontak }}</td>
              <td>{{ community.total_campaign }}</td>
              <td>
                <span class="status-badge" :class="community.status === 'aktif' ? 'active' : 'inactive'">
                  {{ community.status }}
                </span>
              </td>
              <td class="action-cell">
                <button class="mini-btn" @click="viewDetail(community.id_komunitas)">Detail</button>
                <button
                  class="mini-btn"
                  :class="community.status === 'aktif' ? 'danger' : 'success'"
                  @click="showToggleConfirm(community)"
                >
                  {{ community.status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
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
          <h2>Detail Komunitas</h2>
          <button class="modal-close" @click="closeDetail">&times;</button>
        </div>

        <div v-if="detailLoading" class="card">Memuat detail...</div>

        <div v-else-if="detailError" class="card error">{{ detailError }}</div>

        <template v-else-if="selectedCommunity">
          <div class="detail-grid">
            <div class="detail-field">
              <strong>Nama Lembaga</strong>
              <span>{{ selectedCommunity.community.nama_lembaga }}</span>
            </div>

            <div class="detail-field">
              <strong>Jenis Lembaga</strong>
              <span>{{ selectedCommunity.community.jenis_lembaga }}</span>
            </div>

            <div class="detail-field">
              <strong>Email</strong>
              <span>{{ selectedCommunity.community.email }}</span>
            </div>

            <div class="detail-field">
              <strong>Username</strong>
              <span>{{ selectedCommunity.community.username }}</span>
            </div>

            <div class="detail-field">
              <strong>Nomor Kontak</strong>
              <span>{{ selectedCommunity.community.nomor_kontak || '-' }}</span>
            </div>

            <div class="detail-field">
              <strong>Wilayah</strong>
              <span>{{ selectedCommunity.community.nama_wilayah || '-' }}</span>
            </div>

            <div class="detail-field">
              <strong>Nama Bank</strong>
              <span>{{ selectedCommunity.community.nama_bank || '-' }}</span>
            </div>

            <div class="detail-field">
              <strong>Nomor Rekening</strong>
              <span>{{ selectedCommunity.community.nomor_rekening || '-' }}</span>
            </div>

            <div class="detail-field">
              <strong>Status</strong>
              <span>
                <span class="status-badge" :class="selectedCommunity.community.status === 'aktif' ? 'active' : 'inactive'">
                  {{ selectedCommunity.community.status }}
                </span>
              </span>
            </div>

            <div class="detail-field">
              <strong>Tanggal Registrasi</strong>
              <span>{{ selectedCommunity.community.created_at ? new Date(selectedCommunity.community.created_at).toLocaleDateString('id-ID') : '-' }}</span>
            </div>
          </div>

          <hr class="profile-divider" />

          <div class="detail-section">
            <h3>Dokumen Komunitas</h3>

            <table v-if="selectedCommunity.documents?.length" class="history-table">
              <thead>
                <tr>
                  <th>Nama Dokumen</th>
                  <th>URL</th>
                </tr>
              </thead>

              <tbody>
                <tr v-for="doc in selectedCommunity.documents" :key="doc.id_jenis_dok">
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

          <div class="detail-section">
            <h3>Riwayat Campaign</h3>

            <table v-if="selectedCommunity.campaigns?.length" class="history-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Judul</th>
                  <th>Target Dana</th>
                  <th>Status</th>
                  <th>Tanggal</th>
                </tr>
              </thead>

              <tbody>
                <tr v-for="campaign in selectedCommunity.campaigns" :key="campaign.id_campaign">
                  <td>{{ campaign.id_campaign }}</td>
                  <td>{{ campaign.judul }}</td>
                  <td>Rp{{ Number(campaign.target_dana).toLocaleString('id-ID') }}</td>
                  <td>
                    <span class="status">{{ campaign.status }}</span>
                  </td>
                  <td>{{ new Date(campaign.created_at).toLocaleDateString('id-ID') }}</td>
                </tr>
              </tbody>
            </table>

            <p v-else class="empty-text">Tidak ada riwayat campaign.</p>
          </div>
        </template>
      </div>
    </div>

    <div v-if="showConfirmModal" class="modal-backdrop" @click.self="closeConfirm">
      <div class="auth-modal">
        <div class="modal-icon" :class="toggleTarget?.status === 'aktif' ? 'error' : 'success'">
          {{ toggleTarget?.status === 'aktif' ? '!' : '✓' }}
        </div>

        <h2>Konfirmasi</h2>

        <p>
          Apakah anda yakin ingin
          {{ toggleTarget?.status === 'aktif' ? 'menonaktifkan' : 'mengaktifkan' }}
          komunitas <strong>{{ toggleTarget?.nama_lembaga }}</strong>?
        </p>

        <p v-if="toggleError" class="field-error">{{ toggleError }}</p>

        <button
          class="save-btn"
          :class="toggleTarget?.status === 'aktif' ? 'danger' : ''"
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

    <div v-if="showAddModal" class="modal-backdrop" @click.self="closeAddModal">
      <div class="donor-modal">
        <div class="modal-header">
          <h2>Tambah Komunitas</h2>
          <button class="modal-close" @click="closeAddModal">&times;</button>
        </div>

        <p v-if="addError" class="field-error general-error">{{ addError }}</p>

        <div class="add-form-grid">
          <div class="form-field">
            <label>Nama Lembaga <span class="required">*</span></label>
            <input v-model="addForm.nama_lembaga" type="text" placeholder="Nama lembaga" />
          </div>

          <div class="form-field">
            <label>Jenis Lembaga <span class="required">*</span></label>
            <select v-model="addForm.id_jenis_lembaga" class="filter-select">
              <option value="">Pilih jenis</option>
              <option
                v-for="jl in jenisLembagaList"
                :key="jl.id_jenis"
                :value="jl.id_jenis"
              >
                {{ jl.nama_jenis }}
              </option>
            </select>
          </div>

          <div class="form-field">
            <label>Email <span class="required">*</span></label>
            <input v-model="addForm.email" type="email" placeholder="Email" />
          </div>

          <div class="form-field">
            <label>Username <span class="required">*</span></label>
            <input v-model="addForm.username" type="text" placeholder="Username" />
          </div>

          <div class="form-field">
            <label>Password <span class="required">*</span></label>
            <input v-model="addForm.password" type="password" placeholder="Password" />
          </div>

          <div class="form-field">
            <label>Nomor Kontak</label>
            <input v-model="addForm.nomor_kontak" type="text" placeholder="Nomor kontak" />
          </div>

          <div class="form-field">
            <label>Wilayah</label>
            <input v-model="addForm.wilayah" type="text" placeholder="Wilayah" />
          </div>

          <div class="form-field">
            <label>Nama Bank</label>
            <input v-model="addForm.nama_bank" type="text" placeholder="Nama bank" />
          </div>

          <div class="form-field">
            <label>Nomor Rekening</label>
            <input v-model="addForm.nomor_rekening" type="text" placeholder="Nomor rekening" />
          </div>

          <div class="form-field full-width">
            <label>Deskripsi</label>
            <textarea v-model="addForm.deskripsi" class="form-textarea" placeholder="Deskripsi komunitas" rows="3"></textarea>
          </div>
        </div>

        <div class="modal-actions">
          <button class="save-btn" :disabled="adding" @click="submitAddCommunity">
            {{ adding ? 'Menyimpan...' : 'Simpan' }}
          </button>
          <button class="cancel-btn" :disabled="adding" @click="closeAddModal">
            Batal
          </button>
        </div>
      </div>
    </div>

    <AppFooter />
  </main>
</template>

<style scoped>
.page-actions {
  display: flex;
  gap: 10px;
  align-items: center;
}

.add-btn {
  height: 38px;
  padding: 0 18px;
  border: 0;
  border-radius: 999px;
  background: #276749;
  color: white;
  font-weight: 800;
  font-size: 13px;
  cursor: pointer;
}

.doc-link {
  color: #a85f20;
  font-weight: 800;
  font-size: 13px;
}

.detail-field {
  margin-bottom: 16px;
}

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

.add-form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px 20px;
  margin-top: 4px;
}

.form-field {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.form-field.full-width {
  grid-column: 1 / -1;
}

.form-field label {
  font-size: 12px;
  font-weight: 700;
  color: #6f655b;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.form-field .required {
  color: #b91c1c;
}

.form-field input,
.form-field select {
  height: 38px;
  padding: 0 12px;
  border: 1px solid #dccdbb;
  border-radius: 8px;
  background: #fff;
  color: #07313a;
  font-size: 14px;
  font-family: inherit;
}

.form-textarea {
  padding: 10px 12px;
  border: 1px solid #dccdbb;
  border-radius: 8px;
  background: #fff;
  color: #07313a;
  font-size: 14px;
  font-family: inherit;
  resize: vertical;
}

.modal-actions {
  display: flex;
  gap: 10px;
  margin-top: 20px;
}

.modal-actions .save-btn,
.modal-actions .cancel-btn {
  flex: 1;
  height: 40px;
  border: 0;
  border-radius: 10px;
  font-weight: 800;
  cursor: pointer;
}
</style>
