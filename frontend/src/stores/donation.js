import { ref } from 'vue'
import { defineStore } from 'pinia'
import api from '@/api/axios'

export const useDonationStore = defineStore('donation', () => {
  const donations = ref([])
  const pagination = ref({ page: 1, per_page: 15, total: 0 })
  const loading = ref(false)
  const error = ref(null)

  async function fetchHistory({ page = 1, search = '', status = '' } = {}) {
    loading.value = true
    error.value = null
    try {
      const params = { page, per_page: 15 }
      if (search) params.search = search
      if (status && status !== 'semua') params.status = status

      const res = await api.get('/users/me/donations', { params })
      donations.value = res.data.data.data
      pagination.value = res.data.data.pagination
    } catch (e) {
      error.value = e.response?.data?.message ?? 'Gagal memuat riwayat donasi'
      donations.value = []
    } finally {
      loading.value = false
    }
  }

  return { donations, pagination, loading, error, fetchHistory }
})
