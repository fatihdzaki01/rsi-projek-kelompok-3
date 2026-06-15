<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/services/api'
import NavBar from '@/components/NavBar.vue'
import AppFooter from '@/components/AppFooter.vue'

const templates = ref([])
const loading = ref(true)
const errorMessage = ref('')

const showAddModal = ref(false)
const addForm = ref({ nama_dokumen: '', deskripsi: '', wajib_untuk_jenis_lembaga: '', is_opsional: false })
const adding = ref(false)
const addError = ref('')

const deleteTarget = ref(null)
const showDeleteModal = ref(false)
const deleting = ref(false)
const deleteError = ref('')

const fetchTemplates = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await api.get('/superadmin/document-templates')
    templates.value = response.data.data
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Gagal memuat template dokumen.'
  } finally {
    loading.value = false
  }
}

const openAddModal = () => {
  addForm.value = { nama_dokumen: '', deskripsi: '', wajib_untuk_jenis_lembaga: '', is_opsional: false }
  addError.value = ''
  showAddModal.value = true
}

const closeAddModal = () => {
  showAddModal.value = false
  addError.value = ''
}

const submitAdd = async () => {
  adding.value = true
  addError.value = ''

  try {
    await api.post('/superadmin/document-templates', addForm.value)
    showAddModal.value = false
    fetchTemplates()
  } catch (error) {
    addError.value = error.response?.data?.message || 'Gagal menambah template.'
  } finally {
    adding.value = false
  }
}

const showDelete = (template) => {
  deleteTarget.value = template
  deleteError.value = ''
  showDeleteModal.value = true
}

const closeDelete = () => {
  showDeleteModal.value = false
  deleteTarget.value = null
  deleteError.value = ''
}

const confirmDelete = async () => {
  deleting.value = true
  deleteError.value = ''

  try {
    await api.delete(`/superadmin/document-templates/${deleteTarget.value.id_jenis_dok}`)
    showDeleteModal.value = false
    deleteTarget.value = null
    fetchTemplates()
  } catch (error) {
    deleteError.value = error.response?.data?.message || 'Gagal menghapus template.'
  } finally {
    deleting.value = false
  }
}

onMounted(fetchTemplates)
</script>

<template>
  <main class="dashboard-page">
    <NavBar />

    <section class="container">
      <div class="page-title">
        <div>
          <h1>Template Dokumen</h1>
          <p>Kelola template dokumen untuk pendaftaran komunitas.</p>
        </div>

        <div class="page-actions">
          <button class="add-btn" @click="openAddModal">+ Upload Baru</button>
          <RouterLink to="/dashboard" class="back-link">Kembali Dashboard</RouterLink>
        </div>
      </div>

      <section v-if="loading" class="card">Memuat template dokumen...</section>

      <section v-else-if="errorMessage" class="card error">{{ errorMessage }}</section>

      <section v-else class="card">
        <div class="card-header">
          <div>
            <h2>Daftar Template</h2>
            <p>Seluruh template dokumen yang tersedia.</p>
          </div>
          <span class="badge">{{ templates.length }} data</span>
        </div>

        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Nama Dokumen</th>
              <th>Deskripsi</th>
              <th>Wajib Untuk</th>
              <th>Opsional</th>
              <th>Aksi</th>
            </tr>
          </thead>

          <tbody>
            <tr v-if="templates.length === 0">
              <td colspan="6">Tidak ada template dokumen.</td>
            </tr>

            <tr v-for="t in templates" :key="t.id_jenis_dok">
              <td>{{ t.id_jenis_dok }}</td>
              <td>{{ t.nama_dokumen }}</td>
              <td>{{ t.deskripsi || '-' }}</td>
              <td>{{ t.wajib_untuk_jenis_lembaga || '-' }}</td>
              <td>{{ t.is_opsional ? 'Ya' : 'Tidak' }}</td>
              <td class="action-cell">
                <button class="mini-btn danger" @click="showDelete(t)">Hapus</button>
              </td>
            </tr>
          </tbody>
        </table>
      </section>
    </section>

    <div v-if="showAddModal" class="modal-backdrop" @click.self="closeAddModal">
      <div class="donor-modal">
        <div class="modal-header">
          <h2>Upload Template Dokumen</h2>
          <button class="modal-close" @click="closeAddModal">&times;</button>
        </div>

        <p v-if="addError" class="field-error general-error">{{ addError }}</p>

        <div class="add-form-grid">
          <div class="form-field full-width">
            <label>Nama Dokumen <span class="required">*</span></label>
            <input v-model="addForm.nama_dokumen" type="text" placeholder="Nama dokumen" />
          </div>

          <div class="form-field full-width">
            <label>Deskripsi</label>
            <textarea v-model="addForm.deskripsi" class="form-textarea" placeholder="Deskripsi dokumen" rows="2"></textarea>
          </div>

          <div class="form-field">
            <label>Wajib Untuk Jenis Lembaga</label>
            <input v-model="addForm.wajib_untuk_jenis_lembaga" type="text" placeholder="Contoh: Yayasan" />
          </div>

          <div class="form-field">
            <label class="checkbox-label">
              <input v-model="addForm.is_opsional" type="checkbox" />
              Dokumen opsional
            </label>
          </div>
        </div>

        <div class="modal-actions">
          <button class="save-btn" :disabled="adding" @click="submitAdd">{{ adding ? 'Menyimpan...' : 'Simpan' }}</button>
          <button class="cancel-btn" :disabled="adding" @click="closeAddModal">Batal</button>
        </div>
      </div>
    </div>

    <div v-if="showDeleteModal" class="modal-backdrop" @click.self="closeDelete">
      <div class="auth-modal" style="width: 320px;">
        <div class="modal-icon error">!</div>
        <h2>Hapus Template</h2>
        <p>Hapus template <strong>{{ deleteTarget?.nama_dokumen }}</strong>?</p>
        <p v-if="deleteError" class="field-error">{{ deleteError }}</p>
        <button class="reject-btn" :disabled="deleting" @click="confirmDelete">{{ deleting ? 'Menghapus...' : 'Ya, Hapus' }}</button>
        <button class="cancel-btn" :disabled="deleting" @click="closeDelete">Batal</button>
      </div>
    </div>

    <AppFooter />
  </main>
</template>

<style scoped>
.page-actions { display: flex; gap: 10px; align-items: center; }
.add-btn { height: 38px; padding: 0 18px; border: 0; border-radius: 999px; background: #276749; color: white; font-weight: 800; font-size: 13px; cursor: pointer; }
.add-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px 20px; margin-top: 4px; }
.form-field { display: flex; flex-direction: column; gap: 4px; }
.form-field.full-width { grid-column: 1 / -1; }
.form-field label { font-size: 12px; font-weight: 700; color: #6f655b; text-transform: uppercase; letter-spacing: 0.3px; }
.form-field .required { color: #b91c1c; }
.form-field input, .form-field select { height: 38px; padding: 0 12px; border: 1px solid #dccdbb; border-radius: 8px; background: #fff; color: #07313a; font-size: 14px; font-family: inherit; }
.form-textarea { padding: 10px 12px; border: 1px solid #dccdbb; border-radius: 8px; background: #fff; color: #07313a; font-size: 14px; font-family: inherit; resize: vertical; }
.checkbox-label { display: flex !important; align-items: center; gap: 8px; font-size: 13px !important; text-transform: none !important; cursor: pointer; margin-top: 24px; }
.checkbox-label input { width: 16px; height: 16px; }
.modal-actions { display: flex; gap: 10px; margin-top: 20px; }
.modal-actions .save-btn, .modal-actions .cancel-btn { flex: 1; height: 40px; border: 0; border-radius: 10px; font-weight: 800; cursor: pointer; }
</style>
