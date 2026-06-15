<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/services/api'
import NavBar from '@/components/NavBar.vue'
import AppFooter from '@/components/AppFooter.vue'

const donations = ref([])
const pagination = ref(null)
const loading = ref(true)
const errorMessage = ref('')

const currentPage = ref(1)

const selectedDonation = ref(null)
const showSuccessModal = ref(false)
const successLoading = ref(false)
const successError = ref('')

const showFailModal = ref(false)
const failLoading = ref(false)
const failError = ref('')

const fetchDonations = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const params = { page: currentPage.value }
    const response = await api.get('/superadmin/donations/pending', { params })

    donations.value = response.data.data.data
    pagination.value = response.data.data
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Gagal memuat daftar donasi.'
  } finally {
    loading.value = false
  }
}

const nextPage = () => {
  if (pagination.value?.next_page_url) {
    currentPage.value += 1
    fetchDonations()
  }
}

const prevPage = () => {
  if (pagination.value?.prev_page_url) {
    currentPage.value -= 1
    fetchDonations()
  }
}

const markSuccess = (donation) => {
  selectedDonation.value = donation
  successError.value = ''
  showSuccessModal.value = true
}

const closeSuccess = () => {
  showSuccessModal.value = false
  selectedDonation.value = null
  successError.value = ''
}

const confirmSuccess = async () => {
  successLoading.value = true
  successError.value = ''

  try {
    await api.patch(`/superadmin/donations/${selectedDonation.value.id_donasi}/status`, {
      status_pembayaran: 'berhasil',
    })
    showSuccessModal.value = false
    selectedDonation.value = null
    fetchDonations()
  } catch (error) {
    successError.value = error.response?.data?.message || 'Gagal memperbarui status.'
  } finally {
    successLoading.value = false
  }
}

const markFail = (donation) => {
  selectedDonation.value = donation
  failError.value = ''
  showFailModal.value = true
}

const closeFail = () => {
  showFailModal.value = false
  selectedDonation.value = null
  failError.value = ''
}

const confirmFail = async () => {
  failLoading.value = true
  failError.value = ''

  try {
    await api.patch(`/superadmin/donations/${selectedDonation.value.id_donasi}/status`, {
      status_pembayaran: 'gagal',
    })
    showFailModal.value = false
    selectedDonation.value = null
    fetchDonations()
  } catch (error) {
    failError.value = error.response?.data?.message || 'Gagal memperbarui status.'
  } finally {
    failLoading.value = false
  }
}

onMounted(fetchDonations)
</script>

<template>
  <main class="dashboard-page">
    <NavBar />

    <section class="container">
      <div class="page-title">
        <div>
          <h1>Verifikasi Pembayaran</h1>
          <p>Verifikasi status pembayaran donasi yang pending.</p>
        </div>

        <RouterLink to="/dashboard" class="back-link">Kembali Dashboard</RouterLink>
      </div>

      <section v-if="loading" class="card">Memuat daftar donasi...</section>

      <section v-else-if="errorMessage" class="card error">{{ errorMessage }}</section>

      <section v-else class="card">
        <div class="card-header">
          <div>
            <h2>Donasi Pending</h2>
            <p>Donasi yang menunggu verifikasi pembayaran.</p>
          </div>
          <span class="badge">{{ pagination?.total || 0 }} data</span>
        </div>

        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Donatur</th>
              <th>Email</th>
              <th>Campaign</th>
              <th>Nominal</th>
              <th>Metode</th>
              <th>Tanggal</th>
              <th>Aksi</th>
            </tr>
          </thead>

          <tbody>
            <tr v-if="donations.length === 0">
              <td colspan="8">Tidak ada donasi pending.</td>
            </tr>

            <tr v-for="d in donations" :key="d.id_donasi">
              <td>{{ d.id_donasi }}</td>
              <td>{{ d.nama_lengkap }}</td>
              <td>{{ d.email }}</td>
              <td>{{ d.judul_campaign }}</td>
              <td>Rp{{ Number(d.nominal).toLocaleString('id-ID') }}</td>
              <td>{{ d.metode_pembayaran }}</td>
              <td>{{ d.created_at ? new Date(d.created_at).toLocaleDateString('id-ID') : '-' }}</td>
              <td class="action-cell">
                <button class="mini-btn success" @click="markSuccess(d)">Berhasil</button>
                <button class="mini-btn danger" @click="markFail(d)">Gagal</button>
              </td>
            </tr>
          </tbody>
        </table>

        <section v-if="pagination && pagination.last_page > 1" class="pagination-box">
          <button :disabled="!pagination.prev_page_url" @click="prevPage">Sebelumnya</button>
          <span>Halaman {{ pagination.current_page }} dari {{ pagination.last_page }}</span>
          <button :disabled="!pagination.next_page_url" @click="nextPage">Selanjutnya</button>
        </section>
      </section>
    </section>

    <div v-if="showSuccessModal" class="modal-backdrop" @click.self="closeSuccess">
      <div class="auth-modal" style="width: 320px;">
        <div class="modal-icon success">✓</div>
        <h2>Verifikasi Berhasil</h2>
        <p>Konfirmasi pembayaran berhasil untuk donasi <strong>Rp{{ Number(selectedDonation?.nominal).toLocaleString('id-ID') }}</strong>?</p>
        <p v-if="successError" class="field-error">{{ successError }}</p>
        <button class="save-btn" :disabled="successLoading" @click="confirmSuccess">{{ successLoading ? 'Memproses...' : 'Ya, Berhasil' }}</button>
        <button class="cancel-btn" :disabled="successLoading" @click="closeSuccess">Batal</button>
      </div>
    </div>

    <div v-if="showFailModal" class="modal-backdrop" @click.self="closeFail">
      <div class="auth-modal" style="width: 320px;">
        <div class="modal-icon error">!</div>
        <h2>Verifikasi Gagal</h2>
        <p>Tandai donasi <strong>Rp{{ Number(selectedDonation?.nominal).toLocaleString('id-ID') }}</strong> sebagai gagal?</p>
        <p v-if="failError" class="field-error">{{ failError }}</p>
        <button class="reject-btn" :disabled="failLoading" @click="confirmFail">{{ failLoading ? 'Memproses...' : 'Ya, Gagal' }}</button>
        <button class="cancel-btn" :disabled="failLoading" @click="closeFail">Batal</button>
      </div>
    </div>

    <AppFooter />
  </main>
</template>

<style scoped>
.pagination-box { display: flex; justify-content: center; align-items: center; gap: 14px; margin-top: 24px; }
.pagination-box button { padding: 9px 16px; border: 0; border-radius: 999px; background: #07313a; color: white; font-weight: 800; cursor: pointer; }
.pagination-box button:disabled { opacity: 0.4; cursor: not-allowed; }
.pagination-box span { color: #6f655b; font-weight: 700; }
</style>
