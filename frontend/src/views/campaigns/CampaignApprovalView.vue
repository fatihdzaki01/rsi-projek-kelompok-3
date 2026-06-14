<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/services/api'
import AppFooter from '@/components/shared/AppFooter.vue'

const campaigns = ref([])
const pagination = ref(null)
const loading = ref(true)
const errorMessage = ref('')

const selectedStatus = ref('menunggu_review')
const currentPage = ref(1)

const statuses = [
  { label: 'Pending', value: 'menunggu_review' },
  { label: 'Approved', value: 'aktif' },
  { label: 'Rejected', value: 'ditolak' },
  { label: 'Semua', value: 'semua' },
]

const fetchCampaigns = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const params = {
      per_page: 9,
      page: currentPage.value,
    }

    if (selectedStatus.value !== 'semua') {
      params.status = selectedStatus.value
    } else {
      params.status = ''
    }

    const response = await api.get('/superadmin/campaigns/review', { params })

    campaigns.value = response.data.data.data
    pagination.value = response.data.data
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || 'Gagal memuat daftar campaign approval.'
  } finally {
    loading.value = false
  }
}

const changeStatus = (status) => {
  selectedStatus.value = status
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

onMounted(fetchCampaigns)
</script>

<template>
  <main class="dashboard-page">
    <header class="navbar">
      <div class="brand">BERBAGIVE</div>

      <nav>
        <RouterLink to="/dashboard">Dashboard</RouterLink>
        <RouterLink to="/dashboard/campaign-approvals" class="active">
          Approval
        </RouterLink>
      </nav>
    </header>

    <section class="container">
      <div class="page-title">
        <div>
          <h1>Campaign Approval</h1>
          <p>Review dan kelola permintaan campaign dari komunitas.</p>
        </div>

        <RouterLink to="/dashboard" class="back-link">Kembali Dashboard</RouterLink>
      </div>

      <section class="approval-tabs">
        <button
          v-for="item in statuses"
          :key="item.value"
          :class="{ active: selectedStatus === item.value }"
          @click="changeStatus(item.value)"
        >
          {{ item.label }}
        </button>
      </section>

      <section v-if="loading" class="card">Memuat campaign approval...</section>

      <section v-else-if="errorMessage" class="card error">
        {{ errorMessage }}
      </section>

      <template v-else>
        <section class="campaign-grid">
          <article
            v-for="campaign in campaigns"
            :key="campaign.id_campaign"
            class="campaign-card"
          >
            <div class="campaign-image">
              <span>{{ campaign.nama_kategori }}</span>
            </div>

            <div class="campaign-body">
              <span class="status">{{ campaign.status }}</span>

              <h2>{{ campaign.judul }}</h2>
              <p>{{ campaign.nama_lembaga }}</p>

              <div class="campaign-meta">
                <span>ID {{ campaign.id_campaign }}</span>
                <span>Rp{{ Number(campaign.target_dana).toLocaleString('id-ID') }}</span>
              </div>

              <RouterLink
                class="detail-link full"
                :to="`/dashboard/campaigns/${campaign.id_campaign}`"
              >
                Lihat Detail
              </RouterLink>
            </div>
          </article>
        </section>

        <section v-if="campaigns.length === 0" class="card empty-state">
          Tidak ada campaign pada status ini.
        </section>

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
      </template>
    </section>
     <AppFooter />
  </main>
</template>