import { defineStore } from 'pinia'
import api from '@/api/axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('token'),
    user: JSON.parse(localStorage.getItem('user') || 'null'),
    pendingEmail: localStorage.getItem('verification_email') || null,
  }),

  getters: {
    isLoggedIn: (state) => !!state.token,
    userRole: (state) => state.user?.role || null,
    isAdmin: (state) => state.user?.role === 'SUPERADMIN',
    isCommunity: (state) => state.user?.role === 'KOMUNITAS',
    isDonor: (state) => state.user?.role === 'DONATUR',
  },

  actions: {
    async login(email, password) {
      const response = await api.post('/auth/login', {
        email,
        password,
      })

      this.token = response.data.data.token
      this.user = response.data.data.user

      localStorage.setItem('token', this.token)
      localStorage.setItem('user', JSON.stringify(this.user))

      return response.data
    },

    async fetchMe() {
      try {
        const role = this.user?.role
        let response
        if (role === 'KOMUNITAS') {
          response = await api.get('/communities/profile')
        } else if (role === 'SUPERADMIN') {
          response = await api.get('/superadmin/profile')
        } else {
          response = await api.get('/users/me')
        }
        this.user = response.data.data || response.data.user || response.data
        localStorage.setItem('user', JSON.stringify(this.user))
        return this.user
      } catch (error) {
        this.logout()
        throw error
      }
    },

    async logout() {
      try {
        if (this.token) {
          await api.post('/auth/logout').catch(() => {})
        }
      } finally {
        this.token = null
        this.user = null
        localStorage.removeItem('token')
        localStorage.removeItem('user')
      }
    },
  },
})
