import { defineStore } from 'pinia'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('token'),
    user: JSON.parse(localStorage.getItem('user') || 'null'),
  }),

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

    logout() {
      this.token = null
      this.user = null

      localStorage.removeItem('token')
      localStorage.removeItem('user')
    },
  },
})