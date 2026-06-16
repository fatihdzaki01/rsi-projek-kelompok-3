import { defineStore } from 'pinia'
import api from '@/api/axios'

export const useNotificationStore = defineStore('notification', {
  state: () => ({
    items: [],
    unreadCount: 0,
    loading: false,
    error: null,
    filter: 'all',
  }),

  getters: {
    unread: (state) => state.items.filter((n) => !n.is_read),
    filtered: (state) => {
      if (state.filter === 'unread') return state.items.filter((n) => !n.is_read)
      if (state.filter === 'read') return state.items.filter((n) => n.is_read)
      return state.items
    },
  },

  actions: {
    async fetchAll() {
      this.loading = true
      this.error = null
      try {
        const response = await api.get('/notifications')
        const data = response.data.data || response.data
        this.items = Array.isArray(data) ? data : data.items || []
        this.unreadCount = this.unread.length
      } catch (e) {
        this.error = e.response?.data?.message || e.message
      } finally {
        this.loading = false
      }
    },

    async markAsRead(notificationId) {
      try {
        await api.patch(`/notifications/${notificationId}/read`)
        const item = this.items.find((n) => n.id === notificationId)
        if (item) item.is_read = true
        this.unreadCount = this.unread.length
      } catch (e) {
        this.error = e.response?.data?.message || e.message
      }
    },

    async markAllAsRead() {
      try {
        await api.patch('/notifications/read-all')
        this.items.forEach((n) => (n.is_read = true))
        this.unreadCount = 0
      } catch (e) {
        this.error = e.response?.data?.message || e.message
      }
    },

    setFilter(filter) {
      this.filter = filter
    },
  },
})
