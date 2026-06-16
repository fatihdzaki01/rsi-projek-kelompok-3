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

onMounted(fetchDisbursements)
</script>

<template>
  <main class="dashboard-page">
    <header class="navbar">
      <div class="brand">BERBAGIVE</div>

      <nav>
        <RouterLink to="/dashboard">Dashboard</RouterLink>
        <RouterLink to="/dashboard/campaign-approvals">Approval</RouterLink>
        <RouterLink to="/dashboard/disbursements" class="active">
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
                <button class="mini-btn" disabled>
                  Detail
                </button>
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
  </main>
</template>