<script setup>
import { onMounted, ref } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import api from '@/services/api'
import AppFooter from '@/components/AppFooter.vue'

const route = useRoute()

const data = ref(null)
const loading = ref(true)
const errorMessage = ref('')

const fetchInternalMonitoring = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await api.get(`/campaigns/${route.params.id}/internal`)
    data.value = response.data.data
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || 'Gagal memuat monitoring internal.'
  } finally {
    loading.value = false
  }
}

onMounted(fetchInternalMonitoring)
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
          <h1>Monitoring Internal Campaign</h1>
          <p>Menampilkan ringkasan dana, donasi, pencairan, dan dokumen campaign.</p>
        </div>

        <RouterLink
          v-if="data"
          class="back-link"
          :to="`/dashboard/campaigns/${data.campaign.id_campaign}`"
        >
          Kembali Detail
        </RouterLink>
      </div>

      <section v-if="loading" class="card">Memuat monitoring internal...</section>

      <section v-else-if="errorMessage" class="card error">
        {{ errorMessage }}
      </section>

      <template v-else>
        <section class="card">
          <div class="card-header">
            <div>
              <h2>{{ data.campaign.judul }}</h2>
              <p>{{ data.campaign.nama_lembaga }} • {{ data.campaign.nama_kategori }}</p>
            </div>
            <span class="status">{{ data.campaign.status }}</span>
          </div>
        </section>

        <section class="stats-grid">
          <div class="stat-card">
            <p>Total Dana Masuk</p>
            <h2>Rp{{ Number(data.summary.total_dana_masuk).toLocaleString('id-ID') }}</h2>
            <span>Donasi berhasil</span>
          </div>

          <div class="stat-card">
            <p>Saldo Tersisa</p>
            <h2>Rp{{ Number(data.summary.saldo_tersisa).toLocaleString('id-ID') }}</h2>
            <span>Saldo campaign</span>
          </div>

          <div class="stat-card highlight">
            <p>Total Dicairkan</p>
            <h2>Rp{{ Number(data.summary.total_dana_dicairkan).toLocaleString('id-ID') }}</h2>
            <span>Riwayat pencairan</span>
          </div>
        </section>

        <section class="content-grid">
          <div class="card">
            <h2>Donasi Terbaru</h2>

            <table>
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Donatur</th>
                  <th>Nominal</th>
                  <th>Metode</th>
                  <th>Status</th>
                </tr>
              </thead>

              <tbody>
                <tr v-if="data.donations.length === 0">
                  <td colspan="5">Belum ada donasi.</td>
                </tr>

                <tr v-for="donation in data.donations" :key="donation.id_donasi">
                  <td>{{ donation.id_donasi }}</td>
                  <td>{{ donation.is_anonim ? 'Anonim' : donation.nama_lengkap }}</td>
                  <td>Rp{{ Number(donation.nominal).toLocaleString('id-ID') }}</td>
                  <td>{{ donation.metode_pembayaran }}</td>
                  <td><span class="status">{{ donation.status_pembayaran }}</span></td>
                </tr>
              </tbody>
            </table>
          </div>

          <aside class="card profile-card">
            <h2>Dokumen dan Dana</h2>

            <p>
              <strong>Target Dana</strong>
              Rp{{ Number(data.campaign.target_dana).toLocaleString('id-ID') }}
            </p>

            <p>
              <strong>Potongan Platform</strong>
              Rp{{ Number(data.summary.potongan_platform).toLocaleString('id-ID') }}
            </p>

            <p>
              <strong>Dokumen RAB</strong>
              <a :href="data.summary.dokumen_rab" target="_blank">Lihat Dokumen</a>
            </p>
          </aside>
        </section>

        <section class="card">
          <h2>Riwayat Pencairan</h2>

          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Urutan</th>
                <th>Nominal Diajukan</th>
                <th>Nominal Disetujui</th>
                <th>Status</th>
              </tr>
            </thead>

            <tbody>
              <tr v-if="data.withdrawals.length === 0">
                <td colspan="5">Belum ada pencairan.</td>
              </tr>

              <tr v-for="item in data.withdrawals" :key="item.id_pencairan">
                <td>{{ item.id_pencairan }}</td>
                <td>{{ item.urutan_ke }}</td>
                <td>Rp{{ Number(item.nominal_diajukan).toLocaleString('id-ID') }}</td>
                <td>
                  Rp{{ Number(item.nominal_disetujui || 0).toLocaleString('id-ID') }}
                </td>
                <td><span class="status">{{ item.status }}</span></td>
              </tr>
            </tbody>
          </table>
        </section>
      </template>
    </section>
    <AppFooter />
  </main>
</template>