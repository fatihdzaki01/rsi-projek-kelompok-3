import { defineStore } from 'pinia'
import api from '@/api/axios'

export const useCampaignStore = defineStore('campaign', {
  state: () => ({
    items: [],
    detail: null,
    categories: [],
    pagination: { current_page: 1, last_page: 1, total: 0, per_page: 15 },
    filters: { keyword: '', kategori: null, wilayah: null, status: 'aktif' },
    loading: false,
    error: null,
  }),

  actions: {
    async fetchList(params = {}) {
      this.loading = true
      this.error = null
      try {
        const response = await api.get('/campaigns/search', {
          params: { ...this.filters, ...params },
        })
        const data = response.data.data || response.data
        this.items = data.items || data.data || data
        if (data.meta) this.pagination = data.meta
      } catch (e) {
        this.error = e.response?.data?.message || e.message
      } finally {
        this.loading = false
      }
    },

    async fetchDetail(id) {
      this.loading = true
      this.error = null
      try {
        const response = await api.get(`/campaigns/${id}/public`)
        this.detail = response.data.data || response.data
      } catch (e) {
        this.error = e.response?.data?.message || e.message
      } finally {
        this.loading = false
      }
    },

    async fetchCategories() {
      try {
        const response = await api.get('/campaign-categories')
        this.categories = response.data.data || response.data
      } catch (e) {
        this.error = e.response?.data?.message || e.message
      }
    },

    setFilter(key, value) {
      this.filters[key] = value
    },

    resetFilters() {
      this.filters = { keyword: '', kategori: null, wilayah: null, status: 'aktif' }
    },
  },
})
