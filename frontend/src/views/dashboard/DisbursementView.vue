<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/api/axios'
import AppFooter from '@/components/shared/AppFooter.vue'

const activeTab = ref('review')
const items = ref([])
const pagination = ref(null)
const loading = ref(true)
const errorMessage = ref('')
const currentPage = ref(1)
const successMessage = ref('')

const showRejectModal = ref(false)
const rejectId = ref(null)
const rejectReason = ref('')
const rejectLoading = ref(false)
const approveLoading = ref({})

const fetchDisbursements = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const endpoint =
      activeTab.value === 'review'
        ? '/superadmin/disbursements'
        : '/superadmin/disbursements/history'

    const response = await api.get(endpoint, {
      params: {
        per_page: 8,
        page: currentPage.value,
      },
    })

    items.value = response.data.data.data
    pagination.value = response.data.data
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || 'Gagal memuat data pencairan.'
  } finally {
    loading.value = false
  }
}

const changeTab = (tab) => {
  activeTab.value = tab
  currentPage.value = 1
  fetchDisbursements()
}

const nextPage = () => {
  if (pagination.value?.next_page_url) {
    currentPage.value += 1
    fetchDisbursements()
  }
}

const prevPage = () => {
  if (pagination.value?.prev_page_url) {
    currentPage.value -= 1
    fetchDisbursements()
  }
}

const openReject = (id) => {
  rejectId.value = id
  rejectReason.value = ''
  showRejectModal.value = true
}

const handleApprove = async (id) => {
  approveLoading.value = { ...approveLoading.value, [id]: true }
  successMessage.value = ''
  try {
    await api.post(`/superadmin/disbursements/${id}/approve`)
    successMessage.value = 'Pencairan disetujui.'
    fetchDisbursements()
  } catch (e) {
    errorMessage.value = e.response?.data?.message || 'Gagal menyetujui.'
  } finally {
    approveLoading.value = { ...approveLoading.value, [id]: false }
  }
}

const handleReject = async () => {
  if (!rejectReason.value.trim()) return
  rejectLoading.value = true
  try {
    await api.post(`/superadmin/disbursements/${rejectId.value}/reject`, {
      alasan_penolakan: rejectReason.value,
    })
    successMessage.value = 'Pencairan ditolak.'
    showRejectModal.value = false
    fetchDisbursements()
  } catch (e) {
    errorMessage.value = e.response?.data?.message || 'Gagal menolak.'
  } finally {
    rejectLoading.value = false
  }
}

onMounted(fetchDisbursements)
</script>

<template>
  <main class="dashboard-page">
    <header class="navbar">
      <div class="brand">BERBAGIVE</div>

      <nav>
        <RouterLink to="/dashboard">Dashboard</RouterLink>
        <RouterLink to="/campaigns/approval">Approval</RouterLink>
        <RouterLink to="/disbursements" class="active">
          Pencairan
        </RouterLink>
      </nav>
    </header>

    <section class="container">
      <div class="page-title">
        <div>
          <h1>Pencairan Dana Superadmin</h1>
          <p>Review pengajuan pencairan dan riwayat pencairan dana.</p>
        </div>

        <RouterLink to="/dashboard" class="back-link">Kembali Dashboard</RouterLink>
      </div>

      <section v-if="successMessage" class="card success">
        {{ successMessage }}
      </section>

      <section class="approval-tabs">
        <button :class="{ active: activeTab === 'review' }" @click="changeTab('review')">
          Menunggu Review
        </button>

        <button :class="{ active: activeTab === 'history' }" @click="changeTab('history')">
          Riwayat Pencairan
        </button>
      </section>

      <section v-if="loading" class="card">
        Memuat data pencairan...
      </section>

      <section v-else-if="errorMessage" class="card error">
        {{ errorMessage }}
      </section>

      <section v-else class="card">
        <div class="card-header">
          <div>
            <h2>
              {{ activeTab === 'review' ? 'Pengajuan Pencairan' : 'Riwayat Pencairan' }}
            </h2>
            <p>
              {{ activeTab === 'review'
                ? 'Daftar pengajuan dana yang perlu direview superadmin.'
                : 'Daftar riwayat keputusan pencairan dana.'
              }}
            </p>
          </div>

          <span class="badge">{{ pagination?.total || 0 }} data</span>
        </div>

        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Campaign</th>
              <th>Komunitas</th>
              <th>Nominal</th>
              <th>Status</th>
              <th>Tanggal</th>
              <th>Aksi</th>
            </tr>
          </thead>

          <tbody>
            <tr v-if="items.length === 0">
              <td colspan="7">Belum ada data pencairan.</td>
            </tr>

            <tr v-for="item in items" :key="item.id_pencairan">
              <td>{{ item.id_pencairan }}</td>

              <td>
                {{ item.judul_campaign }}
              </td>

              <td>
                {{ item.nama_lembaga }}
              </td>

              <td>
                Rp{{ Number(item.nominal_diajukan || item.nominal_disetujui || 0).toLocaleString('id-ID') }}
              </td>

              <td>
                <span class="status">{{ item.status }}</span>
              </td>

              <td>
                {{ item.tanggal_pengajuan || item.tanggal_keputusan || '-' }}
              </td>

              <td>
                <template v-if="activeTab === 'review' && item.status === 'menunggu_review'">
                  <button
                    class="mini-btn approve"
                    :disabled="approveLoading[item.id_pencairan]"
                    @click="handleApprove(item.id_pencairan)"
                  >
                    {{ approveLoading[item.id_pencairan] ? '...' : 'Setujui' }}
                  </button>
                  <button
                    class="mini-btn reject"
                    @click="openReject(item.id_pencairan)"
                  >
                    Tolak
                  </button>
                </template>
                <span v-else class="text-xs text-gray-400">-</span>
              </td>
            </tr>
          </tbody>
        </table>

        <section v-if="pagination" class="pagination-box">
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

    <div v-if="showRejectModal" class="modal-overlay" @click.self="showRejectModal = false">
      <div class="modal-box">
        <h3 class="text-sm font-bold text-[#2C2C2C] mb-3">Tolak Pencairan</h3>
        <textarea
          v-model="rejectReason"
          rows="3"
          placeholder="Alasan penolakan..."
          class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm resize-none focus:outline-none focus:ring-2 focus:ring-red-400"
        ></textarea>
        <div class="flex justify-end gap-2 mt-3">
          <button class="px-4 py-1.5 text-sm text-gray-600 hover:text-gray-800" @click="showRejectModal = false">Batal</button>
          <button
            class="px-4 py-1.5 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50"
            :disabled="!rejectReason.trim() || rejectLoading"
            @click="handleReject"
          >
            {{ rejectLoading ? '...' : 'Tolak' }}
          </button>
        </div>
      </div>
    </div>
  </main>
</template>

<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
}
.modal-box {
  background: #fff;
  padding: 24px;
  border-radius: 12px;
  width: 90%;
  max-width: 420px;
}
.mini-btn {
  padding: 4px 10px;
  font-size: 12px;
  border-radius: 6px;
  font-weight: 500;
  margin-right: 4px;
  cursor: pointer;
  border: none;
  color: #fff;
}
.mini-btn.approve {
  background: #16a34a;
}
.mini-btn.approve:hover {
  background: #15803d;
}
.mini-btn.reject {
  background: #dc2626;
}
.mini-btn.reject:hover {
  background: #b91c1c;
}
.mini-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.card.success {
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  color: #166534;
  padding: 12px 16px;
  border-radius: 8px;
  margin-bottom: 16px;
  font-size: 14px;
}
</style>
