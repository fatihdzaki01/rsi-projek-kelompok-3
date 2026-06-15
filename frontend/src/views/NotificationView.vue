<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/services/api'
import NavBar from '@/components/NavBar.vue'
import AppFooter from '@/components/AppFooter.vue'

const notifications = ref([])
const pagination = ref(null)
const loading = ref(true)
const errorMessage = ref('')

const tipeFilter = ref('')
const unreadOnly = ref(false)
const currentPage = ref(1)
const unreadCount = ref(0)

const tipeOptions = [
  { label: 'Semua', value: '' },
  { label: 'Info', value: 'info' },
  { label: 'Warning', value: 'warning' },
  { label: 'Error', value: 'error' },
  { label: 'Success', value: 'success' },
]

const fetchNotifications = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const params = { page: currentPage.value }

    if (tipeFilter.value) {
      params.tipe = tipeFilter.value
    }

    if (unreadOnly.value) {
      params.unread_only = true
    }

    const response = await api.get('/notifications', { params })

    notifications.value = response.data.data.notifications.data
    pagination.value = response.data.data.notifications
    unreadCount.value = response.data.data.unread_count
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Gagal memuat notifikasi.'
  } finally {
    loading.value = false
  }
}

const applyFilters = () => {
  currentPage.value = 1
  fetchNotifications()
}

const markAsRead = async (id) => {
  try {
    await api.patch(`/notifications/${id}/read`)
    fetchNotifications()
  } catch {
    // silently fail
  }
}

const markAllRead = async () => {
  try {
    await api.patch('/notifications/read-all')
    fetchNotifications()
  } catch {
    // silently fail
  }
}

const nextPage = () => {
  if (pagination.value?.next_page_url) {
    currentPage.value += 1
    fetchNotifications()
  }
}

const prevPage = () => {
  if (pagination.value?.prev_page_url) {
    currentPage.value -= 1
    fetchNotifications()
  }
}

const tipeClass = (tipe) => {
  const map = { info: '', warning: 'status', error: 'inactive', success: 'active' }
  return map[tipe] || ''
}

onMounted(fetchNotifications)
</script>

<template>
  <main class="dashboard-page">
    <NavBar />

    <section class="container">
      <div class="page-title">
        <div>
          <h1>Notifikasi</h1>
          <p>Notifikasi dan pemberitahuan sistem.</p>
        </div>

        <RouterLink to="/dashboard" class="back-link">Kembali Dashboard</RouterLink>
      </div>

      <div class="filter-bar secondary-filters">
        <div class="filter-group">
          <label>Tipe</label>
          <select v-model="tipeFilter" class="filter-select" @change="applyFilters">
            <option v-for="o in tipeOptions" :key="o.value" :value="o.value">{{ o.label }}</option>
          </select>
        </div>

        <div class="filter-group">
          <label class="checkbox-filter-label">
            <input v-model="unreadOnly" type="checkbox" @change="applyFilters" />
            Belum dibaca saja
          </label>
        </div>

        <div class="filter-actions" style="padding-bottom: 6px;">
          <button v-if="unreadCount > 0" class="apply-btn" @click="markAllRead">
            Tandai Semua Dibaca ({{ unreadCount }})
          </button>
        </div>
      </div>

      <section v-if="loading" class="card">Memuat notifikasi...</section>

      <section v-else-if="errorMessage" class="card error">{{ errorMessage }}</section>

      <section v-else class="card">
        <div class="card-header">
          <div>
            <h2>Daftar Notifikasi</h2>
            <p>{{ unreadOnly ? 'Notifikasi belum dibaca' : 'Semua notifikasi' }}</p>
          </div>
          <span class="badge">{{ pagination?.total || 0 }} notifikasi</span>
        </div>

        <table>
          <thead>
            <tr>
              <th>Status</th>
              <th>Tipe</th>
              <th>Pesan</th>
              <th>Tanggal</th>
              <th>Aksi</th>
            </tr>
          </thead>

          <tbody>
            <tr v-if="notifications.length === 0">
              <td colspan="5">Tidak ada notifikasi.</td>
            </tr>

            <tr v-for="n in notifications" :key="n.id_notif" :class="{ 'unread-row': !n.is_read }">
              <td>
                <span class="status-dot" :class="n.is_read ? 'read' : 'unread'"></span>
              </td>
              <td>
                <span class="status-badge" :class="tipeClass(n.tipe)">{{ n.tipe }}</span>
              </td>
              <td>{{ n.pesan }}</td>
              <td>{{ n.created_at ? new Date(n.created_at).toLocaleDateString('id-ID') : '-' }}</td>
              <td class="action-cell">
                <button v-if="!n.is_read" class="mini-btn" @click="markAsRead(n.id_notif)">Tandai Dibaca</button>
                <span v-else class="read-text">✓ Dibaca</span>
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

    <AppFooter />
  </main>
</template>

<style scoped>
.secondary-filters { display: flex; flex-wrap: wrap; gap: 16px 24px; margin-bottom: 22px; align-items: flex-end; }
.filter-group { display: flex; flex-direction: column; gap: 6px; }
.filter-group label { font-size: 12px; font-weight: 700; color: #6f655b; text-transform: uppercase; letter-spacing: 0.3px; }
.filter-select { height: 36px; padding: 0 10px; border: 1px solid #dccdbb; border-radius: 8px; background: #fffaf2; color: #07313a; font-size: 13px; font-family: inherit; min-width: 150px; }
.filter-actions { display: flex; gap: 8px; }
.apply-btn { height: 36px; padding: 0 18px; border: 0; border-radius: 8px; background: #07313a; color: white; font-weight: 700; font-size: 13px; cursor: pointer; }
.checkbox-filter-label { display: flex !important; align-items: center; gap: 6px; font-size: 13px !important; text-transform: none !important; cursor: pointer; padding-top: 4px; }
.checkbox-filter-label input { width: 16px; height: 16px; }
.status-dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; }
.status-dot.unread { background: #a85f20; }
.status-dot.read { background: #dccdbb; }
.unread-row { background: #fdf8f0; }
.read-text { color: #6f655b; font-size: 12px; font-weight: 600; }
</style>
