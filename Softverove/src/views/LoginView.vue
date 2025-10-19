<template>
  <div class="login-container">
    <div class="login-card">
      <h1>Welcome Back</h1>

      <form @submit.prevent="handleLogin" class="login-form">
        <div class="form-group">
          <label for="email">Email</label>
          <input id="email" v-model="form.email" type="email" placeholder="Enter your email" />
          <p v-if="errors.email" class="error">{{ errors.email }}</p>
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            placeholder="Enter your password"
          />
          <p v-if="errors.password" class="error">{{ errors.password }}</p>
        </div>

        <button type="submit">Log In</button>
      </form>

      <div class="bottom-links">
        <router-link to="/forgot-password" class="forgot">Forgot password?</router-link>
        <router-link to="/register" class="register">Register</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive } from 'vue'

const form = reactive({
  email: '',
  password: '',
})

const errors = reactive({
  email: '',
  password: '',
})

function handleLogin() {
  errors.email = ''
  errors.password = ''

  let isValid = true
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

  if (!form.email.trim()) {
    errors.email = 'Email is required.'
    isValid = false
  } else if (!emailPattern.test(form.email)) {
    errors.email = 'Please enter a valid email.'
    isValid = false
  }

  if (!form.password.trim()) {
    errors.password = 'Password is required.'
    isValid = false
  }

  if (!isValid) return

  console.log('Logging in user:', form)
  alert(`Welcome back, ${form.email}!`)
  form.email = ''
  form.password = ''
}
</script>

<style scoped>
html,
body,
#app,
.login-container {
  height: 100%;
  margin: 0;
  padding: 0;
}

.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background: linear-gradient(135deg, #42b883 0%, #2c3e50 100%);
  font-family: 'Inter', sans-serif;
  overflow: hidden;
}

.login-card {
  width: 100%;
  max-width: 360px;
  background: #fff;
  padding: 32px 26px;
  border-radius: 14px;
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
  padding: 11px;
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

.bottom-links {
  display: flex;
  justify-content: space-between;
  margin-top: 16px;
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
