<template>
  <div class="forgot-container">
    <div class="forgot-card">
      <h1>Forgot Password</h1>
      <p class="info-text">
        Enter your email address and we’ll send you a link to reset your password.
      </p>

      <form @submit.prevent="handleReset" class="forgot-form">
        <div class="form-group">
          <label for="email">Email</label>
          <input id="email" v-model="email" type="email" placeholder="Enter your email" />
          <p v-if="error" class="error">{{ error }}</p>
        </div>

        <button type="submit">Send Reset Link</button>
      </form>

      <p class="login-link">
        <router-link to="/login">Back to Login</router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const email = ref('')
const error = ref('')
const router = useRouter()

function handleReset() {
  error.value = ''

  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!email.value.trim()) {
    error.value = 'Email is required.'
    return
  } else if (!emailPattern.test(email.value)) {
    error.value = 'Please enter a valid email address.'
    return
  }

  // ✅ Simulate success
  alert(`A password reset link has been sent to ${email.value}.`)

  // 🕒 Redirect back to login after short delay
  setTimeout(() => {
    router.push('/login')
  }, 500)

  email.value = ''
}
</script>

<style scoped>
html,
body,
#app,
.forgot-container {
  height: 100%;
  margin: 0;
  padding: 0;
}

.forgot-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background: linear-gradient(135deg, #42b883 0%, #2c3e50 100%);
  font-family: 'Inter', sans-serif;
}

.forgot-card {
  width: 100%;
  max-width: 380px;
  background: #fff;
  padding: 36px 28px;
  border-radius: 14px;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
  animation: fadeIn 0.6s ease;
}

h1 {
  text-align: center;
  color: #2c3e50;
  margin-bottom: 12px;
}

.info-text {
  text-align: center;
  font-size: 14px;
  color: #555;
  margin-bottom: 20px;
}

.form-group {
  margin-bottom: 18px;
  display: flex;
  flex-direction: column;
}

label {
  margin-bottom: 6px;
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

.error {
  color: #e74c3c;
  font-size: 13px;
  margin-top: 3px;
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
