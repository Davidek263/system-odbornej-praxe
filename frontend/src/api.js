import axios from 'axios'

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    Accept: 'application/json',
  },
})

api.interceptors.response.use(
  response => response,
  error => {
    const backendMessage =
      error.response?.data?.message ||
      (typeof error.response?.data === 'string' ? error.response.data : null) ||
      'Neznáma chyba. Skús znova.'

    error.message = backendMessage
    return Promise.reject(error)
  }
)

export default api
