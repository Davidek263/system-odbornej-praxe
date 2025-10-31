<template>
  <div class="reset-password-page">
    <PageAlert 
      v-if="alert.show" 
      :type="alert.type" 
      :message="alert.message" 
      @close="alert.show = false" 
      dismissible 
    />
    <div class="reset-password-container">
      <div class="reset-password-card">
        <h1>Nové heslo</h1>
        <p class="subtitle">Zadaj svoje nové heslo.</p>
        
        <Spinner v-if="loading" overlay />
        
        <form @submit.prevent="handleSubmit" class="reset-password-form">
          <div class="form-group">
            <label for="password">Nové heslo</label>
            <input 
              id="password" 
              v-model="form.password" 
              type="password" 
              placeholder="Vlož nové heslo" 
              :disabled="loading"
            />
            <p v-if="errors.password" class="error">{{ errors.password }}</p>
          </div>

          <div class="form-group">
            <label for="password_confirmation">Potvrdenie hesla</label>
            <input 
              id="password_confirmation" 
              v-model="form.password_confirmation" 
              type="password" 
              placeholder="Zopakuj nové heslo" 
              :disabled="loading"
            />
            <p v-if="errors.password_confirmation" class="error">{{ errors.password_confirmation }}</p>
          </div>

          <button type="submit" :disabled="loading">Zmeniť heslo</button>
        </form>

        <div class="back-link">
          <router-link to="/login">← Späť na prihlásenie</router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import PageAlert from '@/components/PageAlert.vue'
import Spinner from '@/components/Spinner.vue'

const route = useRoute()
const router = useRouter()

const loading = ref(false)

const form = reactive({
  password: '',
  password_confirmation: '',
  token: '',
  email: ''
})

const errors = reactive({
  password: '',
  password_confirmation: ''
})

const alert = reactive({
  show: false,
  type: 'error',
  message: ''
})

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
})

function showAlert(message, type = 'error') {
  alert.message = message
  alert.type = type
  alert.show = true
}

onMounted(() => {
  // Get token and email from URL query parameters
  form.token = route.query.token || ''
  form.email = route.query.email || ''
  
  if (!form.token || !form.email) {
    showAlert('error.password.reset.invalid', 'error')
  }
})

function handleSubmit() {
  // Reset
  errors.password = ''
  errors.password_confirmation = ''
  alert.show = false

  let isValid = true

  // Validation
  if (!form.password.trim()) {
    errors.password = 'Heslo je povinné.'
    isValid = false
  } else if (form.password.length < 6) {
    errors.password = 'Heslo musí mať aspoň 6 znakov.'
    isValid = false
  }

  if (!form.password_confirmation.trim()) {
    errors.password_confirmation = 'Potvrdenie hesla je povinné.'
    isValid = false
  } else if (form.password !== form.password_confirmation) {
    errors.password_confirmation = 'Heslá sa nezhodujú.'
    isValid = false
  }

  if (!isValid) {
    showAlert('validation.form', 'validation')
    return
  }

  loading.value = true

  api.post('/reset-password', {
    email: form.email,
    token: form.token,
    password: form.password,
    password_confirmation: form.password_confirmation
  })
    .then(res => {
      showAlert('success.password.reset', 'success')
      
      // Redirect to login after 2 seconds
      setTimeout(() => {
        router.push('/login')
      }, 2000)
    })
    .catch(err => {
      if (err.response) {
        const status = err.response.status
        
        if (status === 400 || status === 422) {
          if (err.response.data?.errors) {
            Object.assign(errors, err.response.data.errors)
            showAlert('validation.form', 'validation')
          } else if (err.response.data?.message) {
            showAlert(err.response.data.message, 'error')
          } else {
            showAlert('error.password.reset.invalid', 'error')
          }
        } else if (status === 429) {
          showAlert('ratelimit.error', 'ratelimit')
        } else if (status >= 500) {
          showAlert('server.error', 'server')
        } else {
          showAlert('error.password.reset', 'error')
        }
      } else if (err.code === 'ERR_NETWORK' || err.message.includes('Network Error')) {
        showAlert('network.error', 'network')
      } else if (err.code === 'ECONNABORTED' || err.message.includes('timeout')) {
        showAlert('timeout.error', 'timeout')
      } else {
        console.error('Reset password error:', err)
        showAlert('error.password.reset', 'error')
      }
    })
    .finally(() => {
      loading.value = false
    })
}
</script>

<style scoped>
html,
body,
.reset-password-page {
  height: 100%;
  margin: 0;
  background: linear-gradient(135deg, #42b883 0%, #2c3e50 100%);
  font-family: 'Inter', sans-serif;
}

.reset-password-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: calc(100vh - 116px);
  padding: 20px;
}

.reset-password-card {
  width: 100%;
  max-width: 420px;
  background: #fff;
  padding: 36px 28px;
  border-radius: 16px;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
  animation: fadeIn 0.6s ease;
}

h1 {
  text-align: center;
  margin-bottom: 12px;
  color: #2c3e50;
  font-size: 22px;
}

.subtitle {
  text-align: center;
  color: #666;
  font-size: 14px;
  margin-bottom: 25px;
  line-height: 1.5;
}

.form-group {
  margin-bottom: 16px;
  display: flex;
  flex-direction: column;
}

label {
  margin-bottom: 5px;
  font-weight: 600;
  color: #333;
}

input {
  padding: 10px 12px;
  border: 1px solid #ccc;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.2s ease;
}

input:disabled {
  background-color: #f5f5f5;
  cursor: not-allowed;
}

input:focus:not(:disabled) {
  outline: none;
  border-color: #42b883;
  box-shadow: 0 0 0 2px rgba(66, 184, 131, 0.25);
}

button {
  width: 100%;
  background: #42b883;
  color: white;
  border: none;
  padding: 12px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 15px;
  font-weight: 600;
  margin-top: 6px;
  transition: background 0.3s ease;
}

button:hover:not(:disabled) {
  background: #369f73;
}

button:disabled {
  background: #9ca3af;
  cursor: not-allowed;
}

.error {
  color: #e74c3c;
  font-size: 12px;
  margin-top: 4px;
}

.back-link {
  text-align: center;
  margin-top: 18px;
  font-size: 13px;
}

.back-link a {
  color: #42b883;
  text-decoration: none;
  font-weight: 600;
}

.back-link a:hover {
  text-decoration: underline;
}

@media (max-width: 480px) {
  .reset-password-card {
    max-width: 90%;
    padding: 26px 20px;
  }

  h1 {
    font-size: 20px;
  }

  button {
    font-size: 14px;
    padding: 10px;
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>