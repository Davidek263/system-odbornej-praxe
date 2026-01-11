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

        <p class="subtitle" v-if="isActivationMode">
          Zadaj svoje pôvodné (dočasné) heslo a nastav nové heslo.
        </p>
        <p class="subtitle" v-else>
          Zadaj svoje nové heslo.
        </p>
        
        <Spinner v-if="loading" overlay />
        
        <form @submit.prevent="handleSubmit" class="reset-password-form">

          <!-- Temporary password (ONLY for activation mode) -->
          <div class="form-group" v-if="isActivationMode">
            <label for="temporary_password">Dočasné heslo</label>
            <input 
              id="temporary_password" 
              v-model="form.temporary_password" 
              type="password" 
              placeholder="Zadaj dočasné heslo"
              :disabled="loading"
            />
            <p v-if="errors.temporary_password" class="error">{{ errors.temporary_password }}</p>
          </div>

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

const isActivationMode = ref(false) // <–– determines which mode we are in

const form = reactive({
  temporary_password: '',
  password: '',
  password_confirmation: '',
  token: '',
  email: ''
})

const errors = reactive({
  temporary_password: '',
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
  form.token = route.query.token || ''
  form.email = route.query.email || ''

  // ✔ Check if activation mode
  isActivationMode.value = route.query.activated === '1'

  if (!form.email) {
    showAlert('Nesprávny alebo chýbajúci email.', 'error')
    return
  }

  // ✔ For reset password: token MUST exist
  if (!isActivationMode.value && !form.token) {
    showAlert('Nesprávny alebo chýbajúci odkaz na obnovenie hesla.', 'error')
    return
  }
})

function handleSubmit() {
  errors.temporary_password = ''
  errors.password = ''
  errors.password_confirmation = ''
  alert.show = false

  let isValid = true

  if (isActivationMode.value) {
    if (!form.temporary_password.trim()) {
      errors.temporary_password = 'Dočasné heslo je povinné.'
      isValid = false
    }
  }

  if (!form.password.trim()) {
    errors.password = 'Heslo je povinné.'
    isValid = false
  } else if (form.password.length < 8) {
    errors.password = 'Heslo musí mať aspoň 8 znakov.'
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
    showAlert('Skontrolujte správnosť údajov.', 'validation')
    return
  }

  loading.value = true

  // ✔ Select correct endpoint
  const endpoint = isActivationMode.value ? '/set-initial-password' : '/reset-password'

  // ✔ Build payload
  const payload = {
    email: form.email,
    password: form.password,
    password_confirmation: form.password_confirmation,
  }

  if (!isActivationMode.value) {
    payload.token = form.token // reset password mode
  } else {
    payload.temporary_password = form.temporary_password // activation mode
  }

  api.post(endpoint, payload)
    .then(() => {
      showAlert('Heslo bolo úspešne zmenené.', 'success')
      setTimeout(() => router.push('/login'), 2000)
    })
    .catch(err => {
      if (err.response?.data?.message) {
        showAlert(err.response.data.message, 'error')
      } else {
        showAlert('Chyba pri zmene hesla.', 'error')
      }
    })
    .finally(() => {
      loading.value = false
    })
}
</script>

<style scoped>
/* unchanged CSS */
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
