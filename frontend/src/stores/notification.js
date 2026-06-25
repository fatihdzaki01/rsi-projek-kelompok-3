import { defineStore } from 'pinia'
import api from '@/api/axios'

export const useNotificationStore = defineStore('notification', {
  state: () => ({
    items: [],
    pagination: { current_page: 1, per_page: 10, total: 0, last_page: 1 },
    unreadCount: 0,
    loading: false,
    error: null,
    filter: 'all',
    mutedTypes: JSON.parse(localStorage.getItem('mutedNotificationTypes') || '[]'),
  }),

  getters: {
    unread: (state) => state.items.filter((n) => !n.is_read),
    filtered: (state) => {
      let list = state.items
      if (state.filter === 'unread') list = list.filter((n) => !n.is_read)
      if (state.filter === 'read') list = list.filter((n) => n.is_read)
      list = list.filter((n) => !state.mutedTypes.includes(n.type))
      return list
    },
    isMuted: (state) => (type) => state.mutedTypes.includes(type),
  },

  actions: {
    async fetchAll({ page = 1, per_page = 10 } = {}) {
      this.loading = true
      this.error = null
      try {
        const response = await api.get('/notifications', { params: { page, per_page } })
        const data = response.data.data || response.data
        const items = Array.isArray(data) ? data : data.items || []

        this.items = items.map((n) => ({
          id: n.id_notif,
          title: n.judul,
          message: n.pesan,
          type: n.tipe,
          is_read: n.is_read,
          created_at: n.created_at,
          related_campaign_id: n.related_campaign_id,
          related_donasi_id: n.related_donasi_id,
          related_update_id: n.related_update_id,
        }))

        this.pagination = data.pagination || { current_page: page, per_page, total: items.length, last_page: 1 }
        this.unreadCount = data.unread_count ?? this.unread.length
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

    toggleMute(type) {
      const idx = this.mutedTypes.indexOf(type)
      if (idx >= 0) {
        this.mutedTypes.splice(idx, 1)
      } else {
        this.mutedTypes.push(type)
      }
      localStorage.setItem('mutedNotificationTypes', JSON.stringify(this.mutedTypes))
    },
  },
})
