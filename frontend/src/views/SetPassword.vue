<template>
  <div class="forgot-page">
    <div class="forgot-container">
      <div class="forgot-card">
        <h1>Set New Password</h1>
        <p class="info-text">
          Please enter your new password below to activate your account.
        </p>

        <form @submit.prevent="handleSetPassword" class="forgot-form">
          <div class="form-group">
            <label for="password">New Password</label>
            <input
              id="password"
              v-model="password"
              type="password"
              placeholder="Enter your new password"
            />
          </div>

          <div class="form-group">
            <label for="confirmPassword">Confirm Password</label>
            <input
              id="confirmPassword"
              v-model="confirmPassword"
              type="password"
              placeholder="Confirm your new password"
            />
            <p v-if="error" class="error">{{ error }}</p>
          </div>

          <button type="submit" :disabled="loading">
            {{ loading ? 'Saving...' : 'Set Password' }}
          </button>
        </form>

        <p v-if="success" class="success">{{ success }}</p>

        <p class="login-link">
          <router-link to="/login">Back to Login</router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

const route = useRoute()
const router = useRouter()

const email = ref('')
const token = ref('')
const password = ref('')
const confirmPassword = ref('')
const error = ref('')
const success = ref('')
const loading = ref(false)

onMounted(() => {
  email.value = route.query.email || ''
  token.value = route.query.token || ''
})

async function handleSetPassword() {
  error.value = ''
  success.value = ''

  if (!password.value.trim() || !confirmPassword.value.trim()) {
    error.value = 'All fields are required.'
    return
  }
  if (password.value.length < 8) {
    error.value = 'Password must be at least 8 characters long.'
    return
  }
  if (password.value !== confirmPassword.value) {
    error.value = 'Passwords do not match.'
    return
  }

  try {
    loading.value = true
    await axios.post('http://localhost:8000/api/set-password', {
      email: email.value,
      token: token.value,
      password: password.value,
      password_confirmation: confirmPassword.value,
    })

    success.value = 'Password successfully set! Redirecting to login...'

    setTimeout(() => router.push('/login'), 2000)
  } catch (err) {
    error.value =
      err.response?.data?.message || 'Error setting password. Try again.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
html,
body,
.forgot-page {
  height: 100%;
  margin: 0;
  background: linear-gradient(135deg, #42b883 0%, #2c3e50 100%);
  font-family: 'Inter', sans-serif;
}

.forgot-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: calc(100vh - 116px);
  padding: 20px;
}

.forgot-card {
  width: 100%;
  max-width: 400px;
  background: #fff;
  padding: 36px 28px;
  border-radius: 14px;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
  animation: fadeIn 0.6s ease;
}

h1 {
  text-align: center;
  margin-bottom: 15px;
  color: #2c3e50;
  font-size: 22px;
}

.info-text {
  text-align: center;
  font-size: 14px;
  color: #555;
  margin-bottom: 22px;
  line-height: 1.5;
}

.form-group {
  margin-bottom: 18px;
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
  transition: background 0.3s ease;
}

button:hover {
  background: #369f73;
}

button:disabled {
  background: #9cd9bc;
  cursor: not-allowed;
}

.error {
  color: #e74c3c;
  font-size: 13px;
  margin-top: 3px;
}

.success {
  color: #2ecc71;
  font-size: 14px;
  margin-top: 10px;
  text-align: center;
}

.login-link {
  text-align: center;
  margin-top: 20px;
  font-size: 14px;
}

.login-link a {
  color: #42b883;
  text-decoration: none;
  font-weight: 600;
}

.login-link a:hover {
  text-decoration: underline;
}

@media (max-width: 480px) {
  .forgot-card {
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

  .info-text {
    font-size: 13px;
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
