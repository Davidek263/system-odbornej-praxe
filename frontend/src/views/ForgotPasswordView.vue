<template>
  <div class="forgot-password-page">
    <PageAlert 
      v-if="alert.show" 
      :type="alert.type" 
      :message="alert.message" 
      @close="alert.show = false" 
      dismissible 
    />
    <div class="forgot-password-container">
      <div class="forgot-password-card">
        <h1>Zabudnuté heslo</h1>
        <p class="subtitle">Zadaj svoj email a pošleme ti odkaz na obnovenie hesla.</p>
        
        <Spinner v-if="loading" overlay />
        
        <form @submit.prevent="handleSubmit" class="forgot-password-form">
          <div class="form-group">
            <label for="email">Email</label>
            <input 
              id="email" 
              v-model="email" 
              type="email" 
              placeholder="Vlož svoj email" 
              :disabled="loading"
            />
            <p v-if="errors.email" class="error">{{ errors.email }}</p>
          </div>

          <button type="submit" :disabled="loading">Odoslať odkaz</button>
        </form>

        <div class="back-link">
          <router-link to="/login">← Späť na prihlásenie</router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import axios from 'axios'
import PageAlert from '@/components/PageAlert.vue'
import Spinner from '@/components/Spinner.vue'

const email = ref('')
const loading = ref(false)

const errors = reactive({
  email: ''
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

function handleSubmit() {
  // Reset
  errors.email = ''
  alert.show = false

  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  
  // Validation
  if (!email.value.trim()) {
    errors.email = 'Email je povinný.'
    showAlert('validation.email.required', 'validation')
    return
  }
  
  if (!emailPattern.test(email.value)) {
    errors.email = 'Zadaj platný email.'
    showAlert('validation.email.invalid', 'validation')
    return
  }

  loading.value = true

  api.post('/forgot-password', {
    email: email.value
  })
    .then(res => {
      showAlert('success.password.reset.sent', 'success')
      email.value = ''
    })
    .catch(err => {
      if (err.response) {
        const status = err.response.status
        
        if (status === 404) {
          showAlert('notfound.user', 'notfound')
        } else if (status === 429) {
          showAlert('ratelimit.error', 'ratelimit')
        } else if (status >= 500) {
          showAlert('server.error', 'server')
        } else if (err.response.data?.message) {
          showAlert(err.response.data.message, 'error')
        } else {
          showAlert('error.password.reset', 'error')
        }
      } else if (err.code === 'ERR_NETWORK' || err.message.includes('Network Error')) {
        showAlert('network.error', 'network')
      } else if (err.code === 'ECONNABORTED' || err.message.includes('timeout')) {
        showAlert('timeout.error', 'timeout')
      } else {
        console.error('Forgot password error:', err)
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
.forgot-password-page {
  height: 100%;
  margin: 0;
  background: linear-gradient(135deg, #42b883 0%, #2c3e50 100%);
  font-family: 'Inter', sans-serif;
}

.forgot-password-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: calc(100vh - 116px);
  padding: 20px;
}

.forgot-password-card {
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
  margin-bottom: 20px;
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
  .forgot-password-card {
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