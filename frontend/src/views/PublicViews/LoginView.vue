<template>
  <div class="login-page">
    <PageAlert
      v-if="alert.show"
      :type="alert.type"
      :message="alert.message"
      @close="alert.show = false"
      dismissible
    />

    <div class="login-container">
      <div class="login-card">
        <h1>Prihlásenie</h1>

        <Spinner v-if="loading" overlay />

        <form @submit.prevent="handleLogin">
          <div class="form-group">
            <label for="email">Email</label>
            <input 
              id="email" 
              v-model="form.email" 
              type="email" 
              placeholder="Email alebo študentský email"
              :disabled="loading"
            />
            <p v-if="errors.email" class="error">{{ errors.email[0] }}</p>
          </div>

          <div class="form-group">
            <label for="password">Heslo</label>
            <input 
              id="password" 
              v-model="form.password" 
              type="password" 
              placeholder="Heslo"
              :disabled="loading"
            />
            <p v-if="errors.password" class="error">{{ errors.password[0] }}</p>
          </div>

          <button type="submit" :disabled="loading">
            {{ loading ? 'Prihlasovanie...' : 'Prihlásiť sa' }}
          </button>
        </form>

        <div class="links">
          <router-link to="/forgot-password" class="forgot-link">
            Zabudli ste heslo?
          </router-link>
          
          <p class="register-link">
            Ešte nemáš účet?
            <router-link to="/register">Registrácia</router-link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import api from '@/api'
import PageAlert from '@/components/PageAlert.vue'
import Spinner from '@/components/Spinner.vue'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()

const form = reactive({
  email: '',
  password: '',
})
const errors = reactive({})
const alert = reactive({ show: false, type: 'error', message: '' })
const loading = ref(false)

function showAlert(message, type = 'error') {
  alert.message = message
  alert.type = type
  alert.show = true
}

function handleLogin() {
  // Clear previous errors
  Object.keys(errors).forEach(k => delete errors[k])
  alert.show = false
  loading.value = true

  api.post('/login', {
    email: form.email.trim(),
    password: form.password
  })
    .then(res => {
      loading.value = false
      
      // Store authentication data
      localStorage.setItem('token', res.data.access_token)
      localStorage.setItem('user', JSON.stringify(res.data.user))

      // Check if user must change password
      if (res.data.user.must_change_password) {
        showAlert('Prihlásenie úspešné. Prosím zmeňte si heslo.', 'success')
        setTimeout(() => {
          router.push('/change-password')
        }, 1500)
        return
      }

      showAlert(res.data.message || 'Prihlásenie úspešné.', 'success')

      // Redirect based on role
      setTimeout(() => {
        const user = res.data.user
        switch(user.role_name) {
          case 'student':
            router.push('/student-dashboard')
            break
          case 'company':
            router.push('/company-dashboard')
            break
          case 'guarantor':
            router.push('/guarantor-dashboard')
            break
          default:
            router.push('/home')
        }
      }, 1000)
    })
    .catch(err => {
      loading.value = false
      
      // Handle validation errors
      if (err.response?.status === 422 && err.response?.data?.errors) {
        Object.assign(errors, err.response.data.errors)
        showAlert('Prosím opravte chyby vo formulári.', 'error')
        return
      }

      // Handle specific error messages
      if (err.response?.status === 403) {
        showAlert('Váš účet nie je aktivovaný. Skontrolujte si email.', 'error')
        return
      }

      if (err.response?.status === 401) {
        showAlert('Nesprávny email alebo heslo.', 'error')
        return
      }

      // Generic error
      showAlert(err.response?.data?.message || 'Prihlásenie zlyhalo. Skúste to znova.', 'error')
    })
}

// Check for email change success/error on mount
onMounted(() => {
  // Force logout if logout parameter is present
  if (route.query.logout === 'true') {
    localStorage.clear()
    sessionStorage.clear()
    localStorage.removeItem('token')
    localStorage.removeItem('user')
  }

  // Check if email was successfully changed
  if (route.query.email_changed === 'true') {
    showAlert('Email bol úspešne zmenený. Prosím prihláste sa s novým emailom.', 'success')
    // Clean URL
    router.replace({ query: {} })
  }

  // Check for email change errors
  if (route.query.email_change_error) {
    let errorMessage = ''

    switch (route.query.email_change_error) {
      case 'expired':
        errorMessage = 'Odkaz na zmenu emailu vypršal. Požiadajte o novú zmenu emailu v profile.'
        break
      case 'invalid':
        errorMessage = 'Neplatný odkaz na zmenu emailu.'
        break
      case 'user_not_found':
        errorMessage = 'Používateľ nebol nájdený.'
        break
      case 'failed':
        errorMessage = 'Zmena emailu zlyhala. Skúste to znova neskôr.'
        break
      default:
        errorMessage = 'Neplatný alebo expirovaný odkaz.'
    }

    showAlert(errorMessage, 'error')
    // Clean URL
    router.replace({ query: {} })
  }
})
</script>

<style scoped>
html,
body,
.login-page {
  height: 100%;
  margin: 0;
  background: linear-gradient(135deg, #42b883 0%, #2c3e50 100%);
  font-family: 'Inter', sans-serif;
}

.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: calc(100vh - 116px);
  padding: 20px;
}

.login-card {
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
  margin-bottom: 25px;
  color: #2c3e50;
  font-size: 22px;
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
  font-size: 14px;
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

input:focus {
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
  background: #95d5b2;
  cursor: not-allowed;
}

.error {
  color: #e74c3c;
  font-size: 12px;
  margin-top: 4px;
}

.links {
  margin-top: 20px;
}

.forgot-link {
  display: block;
  text-align: center;
  color: #42b883;
  text-decoration: none;
  font-size: 13px;
  font-weight: 500;
  margin-bottom: 12px;
}

.forgot-link:hover {
  text-decoration: underline;
}

.register-link {
  text-align: center;
  margin-top: 16px;
  font-size: 13px;
  color: #666;
}

.register-link a {
  color: #42b883;
  text-decoration: none;
  font-weight: 600;
}

.register-link a:hover {
  text-decoration: underline;
}

@media (max-width: 480px) {
  .login-card {
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