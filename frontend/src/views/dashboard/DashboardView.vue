<script setup>
import { onMounted, ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import api from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import AppFooter from '@/components/shared/AppFooter.vue'

const router = useRouter()
const auth = useAuthStore()

const profile = ref(null)
const campaigns = ref([])
const stats = ref({
  total_users: 0,
  total_campaign: 0,
  total_donasi: 0,
})

const loading = ref(true)
const errorMessage = ref('')

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
    errorMessage.value =
      error.response?.data?.message || 'Gagal memuat dashboard.'
  } finally {
    loading.value = false
  }
}

const handleLogout = () => {
  auth.logout()
  router.push('/login')
}

onMounted(fetchDashboard)
</script>

<template>
  <main class="dashboard-page">
    <header class="navbar">
      <div class="brand">BERBAGIVE</div>

      <nav>
        <RouterLink to="/dashboard" class="active">Dashboard</RouterLink>
        <RouterLink to="/dashboard/campaign-approvals">Approval</RouterLink>
        <RouterLink to="/dashboard/disbursements">Pencairan</RouterLink>
        <a>Laporan</a>
        <a>Admin Panel</a>
      </nav>

      <button class="logout-btn" @click="handleLogout">Logout</button>
    </header>

    <section class="container">
      <div class="page-title">
        <div>
          <h1>Ringkasan Platform</h1>
          <p>Pantau aktivitas terbaru dan performa platform secara real-time.</p>
        </div>

        <div v-if="profile" class="user-box">
          <span>{{ profile.nama_lengkap }}</span>
          <small>{{ profile.role }}</small>
        </div>
      </div>

      <section v-if="loading" class="card">Memuat data...</section>

      <section v-else-if="errorMessage" class="card error">
        {{ errorMessage }}
      </section>

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
                    <RouterLink
                      class="detail-link"
                      :to="`/dashboard/campaigns/${campaign.id_campaign}`"
                    >
                      Detail
                    </RouterLink>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <aside class="card profile-card">
            <h2>Profil Superadmin</h2>

            <img
              v-if="profile?.foto_profil_url"
              :src="profile.foto_profil_url"
              alt="Foto profil"
            />

            <p><strong>Nama</strong>{{ profile?.nama_lengkap }}</p>
            <p><strong>Email</strong>{{ profile?.email }}</p>
            <p><strong>Status</strong>{{ profile?.is_active ? 'Aktif' : 'Nonaktif' }}</p>
          </aside>
        </section>
      </template>
    </section>
   <AppFooter />
  </main>
</template>