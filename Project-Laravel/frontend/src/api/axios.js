import axios from 'axios'
import router from '@/router'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
})

api.interceptors.request.use((config) => {
  const token = import.meta.env.VITE_DEV_TOKEN || localStorage.getItem('token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

api.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error.response?.status

    if (status === 401) {
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      router.push('/login')
    } else if (status === 403) {
      const requestUrl = error.config?.url || ''
      const isAuthRequest = requestUrl.includes('/auth/') || requestUrl.includes('auth/login')
      if (!isAuthRequest) {
        router.push('/forbidden')
      }
    }

    return Promise.reject(error)
  },
)

export default api
