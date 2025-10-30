<template>
  <div class="login-page">
    <PageAlert v-if="loginError" type="error" :message="loginError" @update:show="loginError = ''" dismissible />
    <div class="login-container">
      <div class="login-card">
        <h1>Prihlásenie</h1>
        <Spinner v-if="loading" overlay />
        <form @submit.prevent="handleLogin" class="login-form">
          <div class="form-group">
            <label for="email">Email</label>
            <input id="email" v-model="form.email" type="email" placeholder="Vlož svoj email" />
            <p v-if="errors.email" class="error">{{ errors.email }}</p>
          </div>

          <div class="form-group">
            <label for="password">Heslo</label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              placeholder="Vlož svoje heslo"
            />
            <p v-if="errors.password" class="error">{{ errors.password }}</p>
          </div>

          <button type="submit">Prihlásiť</button>
        </form>

        <div class="bottom-links">
          <router-link to="/forgot-password" class="forgot">Zabudol si heslo?</router-link>
          <!-- <router-link to="/register" class="register">Register</router-link> --> 
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import axios from 'axios'
import PageAlert from '@/components/PageAlert.vue'

const form = reactive({
  email: '',
  password: '',
})

const errors = reactive({
  email: '',
  password: '',
})

const loading = ref(false)
const loginError = ref('')

// Axios instance
const api = axios.create({
  baseURL: 'http://localhost:8000/api', // your Laravel backend
})

function handleLogin() {
  errors.email = ''
  errors.password = ''
  loginError.value = ''

  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  let isValid = true

  if (!form.email.trim()) {
    errors.email = 'Email is required.'
    isValid = false
  } else if (!emailPattern.test(form.email)) {
    errors.email = 'Please enter a valid email address.'
    isValid = false
  }

  if (!form.password.trim()) {
    errors.password = 'Password is required.'
    isValid = false
  }

  if (!isValid) return

  loading.value = true

  // Call Laravel API
  api.post('/login-person', {
    email: form.email,
    password: form.password
  })
    .then(res => {
      const token = res.data.token
      localStorage.setItem('token', token) // save token
      // Success - redirect or show success message
      form.email = ''
      form.password = ''
      // You might want to redirect here instead:
      // router.push('/dashboard')
    })
    .catch(err => {
      if (err.response && err.response.data.message) {
        loginError.value = err.response.data.message
      } else {
        console.error(err)
        loginError.value = 'Login failed. Please try again.'
      }
    })
    .finally(() => {
      loading.value = false
    })
}
</script>

<style scoped>
/*  Full page background */
html,
body,
.login-page {
  height: 100%;
  margin: 0;
  background: linear-gradient(135deg, #42b883 0%, #2c3e50 100%);
  font-family: 'Inter', sans-serif;
}

/*  Center container */
.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: calc(100vh - 116px); /* keeps centered under navbar */
  padding: 20px;
}

/*  Card */
.login-card {
  width: 100%;
  max-width: 420px;
  background: #fff;
  padding: 36px 28px;
  border-radius: 16px;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
  animation: fadeIn 0.6s ease;
}

/*  Title */
h1 {
  text-align: center;
  margin-bottom: 25px;
  color: #2c3e50;
  font-size: 22px;
}

/*  Form fields */
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

/*  Button */
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

/*  Error messages */
.error {
  color: #e74c3c;
  font-size: 12px;
  margin-top: 4px;
}

/*  Bottom links */
.bottom-links {
  display: flex;
  justify-content: space-between;
  margin-top: 18px;
  font-size: 13px;
}

.bottom-links a {
  color: #42b883;
  text-decoration: none;
  font-weight: 600;
}

.bottom-links a:hover {
  text-decoration: underline;
}

/*  Mobile responsive */
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

/*  Animation */
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