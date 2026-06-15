<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/services/api'
import NavBar from '@/components/NavBar.vue'
import AppFooter from '@/components/AppFooter.vue'

const profile = ref(null)
const campaigns = ref([])
const stats = ref({ total_users: 0, total_campaign: 0, total_donasi: 0 })

const loading = ref(true)
const errorMessage = ref('')

const navCards = [
  { title: 'Donatur', desc: 'Kelola dan pantau donatur', to: '/dashboard/donors', icon: '👥' },
  { title: 'Komunitas', desc: 'Kelola dan review komunitas', to: '/dashboard/communities', icon: '🏘' },
  { title: 'Statistik', desc: 'Grafik dan analitik platform', to: '/dashboard/platform-stats', icon: '📊' },
  { title: 'Laporan Keuangan', desc: 'Export laporan keuangan', to: '/dashboard/financial-export', icon: '💰' },
  { title: 'Notifikasi', desc: 'Pemberitahuan sistem', to: '/dashboard/notifications', icon: '🔔' },
  { title: 'Verifikasi Pembayaran', desc: 'Verifikasi donasi pending', to: '/dashboard/payment-verification', icon: '✓' },
]

const fetchDashboard = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const [profileResponse, campaignResponse, dbResponse] = await Promise.all([
      api.get('/superadmin/profile'),
      api.get('/superadmin/campaigns/review?per_page=5'),
      api.get('/db-test'),
    ])

    profile.value = profileResponse.data.data
    campaigns.value = campaignResponse.data.data.data
    stats.value = dbResponse.data.data
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Gagal memuat dashboard.'
  } finally {
    loading.value = false
  }
}

onMounted(fetchDashboard)
</script>

<template>
  <main class="dashboard-page">
    <NavBar />

    <section class="container">
      <div class="page-title">
        <div>
          <h1>Ringkasan Platform</h1>
          <p>Pantau aktivitas terbaru dan performa platform secara real-time.</p>
        </div>

        <RouterLink v-if="profile" to="/dashboard/profile" class="user-box">
          <span>{{ profile.nama_lengkap }}</span>
          <small>{{ profile.role }}</small>
        </RouterLink>
      </div>

      <section v-if="loading" class="card">Memuat data...</section>

      <section v-else-if="errorMessage" class="card error">{{ errorMessage }}</section>

      <template v-else>
        <section class="stats-grid">
          <div class="stat-card">
            <p>Total User</p>
            <h2>{{ Number(stats.total_users).toLocaleString('id-ID') }}</h2>
            <span>Database aktif</span>
          </div>

          <div class="stat-card">
            <p>Total Campaign</p>
            <h2>{{ Number(stats.total_campaign).toLocaleString('id-ID') }}</h2>
            <span>Semua campaign</span>
          </div>

          <div class="stat-card highlight">
            <p>Total Transaksi Donasi</p>
            <h2>{{ Number(stats.total_donasi).toLocaleString('id-ID') }}</h2>
            <span>Record transaksi donasi</span>
          </div>
        </section>

        <section class="nav-grid">
          <RouterLink
            v-for="card in navCards"
            :key="card.to"
            :to="card.to"
            class="nav-card"
          >
            <span class="nav-icon">{{ card.icon }}</span>
            <div>
              <strong>{{ card.title }}</strong>
              <p>{{ card.desc }}</p>
            </div>
            <span class="nav-arrow">→</span>
          </RouterLink>
        </section>

        <section class="content-grid">
          <div class="card">
            <div class="card-header">
              <div>
                <h2>Perlu Tindakan Segera</h2>
                <p>Campaign yang menunggu review superadmin.</p>
              </div>
              <span class="badge">{{ campaigns.length }} data</span>
            </div>

            <table>
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Judul Campaign</th>
                  <th>Komunitas</th>
                  <th>Kategori</th>
                  <th>Target Dana</th>
                  <th>Status</th>
                  <th>Aksi</th>
                </tr>
              </thead>

              <tbody>
                <tr v-for="campaign in campaigns" :key="campaign.id_campaign">
                  <td>{{ campaign.id_campaign }}</td>
                  <td>{{ campaign.judul }}</td>
                  <td>{{ campaign.nama_lembaga }}</td>
                  <td>{{ campaign.nama_kategori }}</td>
                  <td>Rp{{ Number(campaign.target_dana).toLocaleString('id-ID') }}</td>
                  <td>
                    <span class="status">{{ campaign.status }}</span>
                  </td>
                  <td>
                    <RouterLink class="detail-link" :to="`/dashboard/campaigns/${campaign.id_campaign}`">Detail</RouterLink>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </template>
    </section>
   <AppFooter />
  </main>
</template>

<style scoped>
.nav-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 14px;
  margin-bottom: 24px;
}

.nav-card {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 16px 18px;
  background: #fffaf2;
  border-radius: 14px;
  box-shadow: 0 8px 20px rgba(80, 49, 28, 0.08);
  text-decoration: none;
  color: inherit;
  transition: transform 0.15s, box-shadow 0.15s;
  cursor: pointer;
}

.nav-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 28px rgba(80, 49, 28, 0.14);
}

.nav-icon {
  font-size: 24px;
  flex-shrink: 0;
}

.nav-card div {
  flex: 1;
  min-width: 0;
}

.nav-card strong {
  display: block;
  font-size: 14px;
  margin-bottom: 2px;
}

.nav-card p {
  margin: 0;
  font-size: 12px;
  color: #6f655b;
}

.nav-arrow {
  font-size: 18px;
  color: #a85f20;
  font-weight: 800;
  flex-shrink: 0;
}

@media (max-width: 900px) {
  .nav-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 550px) {
  .nav-grid {
    grid-template-columns: 1fr;
  }
}
</style>
