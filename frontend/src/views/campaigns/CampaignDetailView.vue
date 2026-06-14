<script setup>
import { onMounted, ref } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import api from '@/services/api'

const route = useRoute()

const campaign = ref(null)
const loading = ref(true)
const errorMessage = ref('')

const fetchCampaignDetail = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await api.get(`/superadmin/campaigns/${route.params.id}`)
    campaign.value = response.data.data
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || 'Gagal memuat detail campaign.'
  } finally {
    loading.value = false
  }
}

onMounted(fetchCampaignDetail)
</script>

<template>
  <main class="dashboard-page">
    <header class="navbar">
      <div class="brand">BERBAGIVE</div>

      <nav>
        <RouterLink to="/dashboard" class="active">Dashboard</RouterLink>
      </nav>
    </header>

    <section class="container">
      <div class="page-title">
        <div>
          <h1>Campaign Approval Detail</h1>
          <p>Review detail campaign sebelum disetujui atau ditolak.</p>
        </div>

        <RouterLink to="/dashboard" class="back-link">Kembali</RouterLink>
      </div>

      <section v-if="loading" class="card">Memuat detail campaign...</section>

      <section v-else-if="errorMessage" class="card error">
        {{ errorMessage }}
      </section>

      <template v-else>
        <section class="detail-grid">
          <div class="card">
            <div class="card-header">
              <div>
                <h2>{{ campaign.judul }}</h2>
                <p>{{ campaign.nama_lembaga }}</p>
              </div>
              <span class="status">{{ campaign.status }}</span>
            </div>

            <div class="info-grid">
              <div>
                <strong>Kategori</strong>
                <span>{{ campaign.nama_kategori }}</span>
              </div>

              <div>
                <strong>Wilayah</strong>
                <span>{{ campaign.nama_wilayah }}</span>
              </div>

              <div>
                <strong>Target Dana</strong>
                <span>Rp{{ Number(campaign.target_dana).toLocaleString('id-ID') }}</span>
              </div>

              <div>
                <strong>Dana Terkumpul</strong>
                <span>Rp{{ Number(campaign.dana_terkumpul).toLocaleString('id-ID') }}</span>
              </div>

              <div>
                <strong>Tipe Distribusi</strong>
                <span>{{ campaign.tipe_distribusi }}</span>
              </div>

              <div>
                <strong>Target Audiens</strong>
                <span>{{ campaign.target_audiens }}</span>
              </div>
            </div>

            <div class="description-box">
              <strong>Deskripsi</strong>
              <p>{{ campaign.deskripsi }}</p>
            </div>

            <div class="description-box">
              <strong>Dokumen RAB</strong>
              <p>
                <a :href="campaign.url_rab" target="_blank">
                  {{ campaign.url_rab }}
                </a>
              </p>
            </div>

            <div v-if="campaign.alasan_penolakan" class="description-box">
              <strong>Alasan Penolakan</strong>
              <p>{{ campaign.alasan_penolakan }}</p>
            </div>
          </div>

          <aside class="card action-card">
            <h2>Aksi Review</h2>
            <p>
              Tombol approve dan reject belum diaktifkan agar data tidak berubah saat testing.
            </p>

            <button class="approve-btn" disabled>Approve Request</button>
            <button class="reject-btn" disabled>Reject Campaign</button>

            <RouterLink
              class="monitor-link"
              :to="`/dashboard/campaigns/${campaign.id_campaign}/internal`"
            >
              Lihat Monitoring Internal
            </RouterLink>
          </aside>
        </section>
      </template>
    </section>
  </main>
</template>