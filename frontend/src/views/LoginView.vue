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
            <input id="email" v-model="form.email" type="email" placeholder="Email" />
            <p v-if="errors.email" class="error">{{ errors.email[0] }}</p>
          </div>

          <div class="form-group">
            <label for="password">Heslo</label>
            <input id="password" v-model="form.password" type="password" placeholder="Heslo" />
            <p v-if="errors.password" class="error">{{ errors.password[0] }}</p>
          </div>

          <button type="submit">Prihlásiť sa</button>
        </form>

        <p class="register-link">
          Ešte nemáš účet?
          <router-link to="/register">Registrácia</router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import api from '@/api'
import PageAlert from '@/components/PageAlert.vue'
import Spinner from '@/components/Spinner.vue'
import { useRouter } from 'vue-router'

const router = useRouter()

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
  Object.keys(errors).forEach(k => delete errors[k])
  alert.show = false
  loading.value = true

  api.post('/login-person', {
    email: form.email,
    password: form.password
  })
    .then(res => {
      loading.value = false
      localStorage.setItem('token', res.data.access_token)
      localStorage.setItem('user', JSON.stringify(res.data.user))

      showAlert(res.data.message, 'success')

      const user = JSON.parse(localStorage.getItem('user'))
      switch(user.role_name) {
        case "student":
          router.push('/home')
          break;
        case "guarantor":
          router.push('/home')
          break;
        default:
          router.push('/home')
      }
    })
    .catch(err => {
      loading.value = false
      showAlert(err.response?.data?.message || 'Login failed.', 'error')
    })
}
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
}

input {
  padding: 10px 12px;
  border: 1px solid #ccc;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.2s ease;
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

button:hover {
  background: #369f73;
}

.error {
  color: #e74c3c;
  font-size: 12px;
  margin-top: 4px;
}

.register-link {
  text-align: center;
  margin-top: 16px;
  font-size: 13px;
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