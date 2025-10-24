<template>
  <div class="register-page">
    <div class="register-container">
      <div class="register-card">
        <h1>{{ isCompany ? 'Register Company' : 'Register Account' }}</h1>

        <!-- Toggle switch -->
        <div class="toggle-buttons">
          <button :class="{ active: !isCompany }" @click="isCompany = false">Person</button>
          <button :class="{ active: isCompany }" @click="isCompany = true">Company</button>
        </div>

        <form @submit.prevent="handleRegister" class="register-form">
          <!-- PERSON FORM -->
          <template v-if="!isCompany">
            <div class="form-group">
              <label for="first_name">First Name</label>
              <input
                id="first_name"
                v-model="form.first_name"
                type="text"
                placeholder="Enter your first name"
              />
              <p v-if="errors.first_name" class="error">{{ errors.first_name }}</p>
            </div>

            <div class="form-group">
              <label for="last_name">Last Name</label>
              <input
                id="last_name"
                v-model="form.last_name"
                type="text"
                placeholder="Enter your last name"
              />
              <p v-if="errors.last_name" class="error">{{ errors.last_name }}</p>
            </div>

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
          </template>

          <!-- COMPANY FORM -->
          <template v-else>
            <div class="form-group">
              <label for="companyName">Company Name</label>
              <input
                id="companyName"
                v-model="form.companyName"
                type="text"
                placeholder="Enter your company name"
              />
              <p v-if="errors.companyName" class="error">{{ errors.companyName }}</p>
            </div>

            <div class="form-group">
              <label for="email">Company Email</label>
              <input
                id="email"
                v-model="form.email"
                type="email"
                placeholder="Enter company email"
              />
              <p v-if="errors.email" class="error">{{ errors.email }}</p>
            </div>

            <div class="form-group">
              <label for="address">Address</label>
              <input
                id="address"
                v-model="form.address"
                type="text"
                placeholder="Enter company address"
              />
              <p v-if="errors.address" class="error">{{ errors.address }}</p>
            </div>

            <div class="form-group">
              <label for="phone">Phone</label>
              <input
                id="phone"
                v-model="form.phone"
                type="text"
                placeholder="Enter company phone number"
              />
              <p v-if="errors.phone" class="error">{{ errors.phone }}</p>
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
          </template>

          <button type="submit">Register</button>
        </form>

        <p class="login-link">
          Already have an account?
          <router-link to="/login">Login</router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import api from '../api.js'

const isCompany = ref(false)

const form = reactive({
  first_name: '',
  last_name: '',
  email: '',
  password: '',
  companyName: '',
  address: '',
  phone: '',
})

const errors = reactive({})

function handleRegister() {
  // reset errors
  Object.keys(errors).forEach((k) => (errors[k] = ''))
  let isValid = true
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

  if (!isCompany.value) {
    // Validation for person
    if (!form.first_name.trim()) {
      errors.first_name = 'First name is required.'
      isValid = false
    }
    if (!form.last_name.trim()) {
      errors.last_name = 'Last name is required.'
      isValid = false
    }
    if (!form.email.trim()) {
      errors.email = 'Email is required.'
      isValid = false
    } else if (!emailPattern.test(form.email)) {
      errors.email = 'Invalid email format.'
      isValid = false
    }
    if (form.password.length < 6) {
      errors.password = 'Password must be at least 6 characters.'
      isValid = false
    }
  } else {
    // Validation for company
    if (!form.companyName.trim()) {
      errors.companyName = 'Company name is required.'
      isValid = false
    }
    if (!form.email.trim()) {
      errors.email = 'Company email is required.'
      isValid = false
    } else if (!emailPattern.test(form.email)) {
      errors.email = 'Invalid email format.'
      isValid = false
    }
    if (!form.address.trim()) {
      errors.address = 'Address is required.'
      isValid = false
    }
    if (!form.phone.trim()) {
      errors.phone = 'Phone number is required.'
      isValid = false
    }
    if (form.password.length < 6) {
      errors.password = 'Password must be at least 6 characters.'
      isValid = false
    }
  }

  if (!isValid) return

  // call Laravel API
  const endpoint = isCompany.value ? '/register-company' : '/register-person'

  api.post(endpoint, {
    first_name: form.first_name,
    last_name: form.last_name,
    email: form.email,
    password: form.password,
    password_confirmation: form.password,
    companyName: form.companyName,
    address: form.address,
    phone: form.phone,
  })
    .then(res => {
      const token = res.data.token
      localStorage.setItem('token', token)
      alert(
        isCompany.value
          ? `Company "${form.companyName}" registered successfully!`
          : `Welcome, ${form.first_name}! Your account has been created.`
      )
      // reset form
      Object.keys(form).forEach((k) => (form[k] = ''))
    })
    .catch(err => {
      if (err.response && err.response.data) {
        Object.assign(errors, err.response.data)
      } else {
        console.error(err)
        alert('Registration failed. Try again.')
      }
    })
}
</script>

<style scoped>
html,
body,
.register-page {
  height: 100%;
  margin: 0;
  background: linear-gradient(135deg, #42b883 0%, #2c3e50 100%);
  font-family: 'Inter', sans-serif;
}

/* Center card */
.register-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: calc(100vh - 76px);
}

/* Card */
.register-card {
  width: 100%;
  max-width: 420px;
  background: #fff;
  padding: 36px 28px;
  border-radius: 16px;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
  animation: fadeIn 0.6s ease;
}

/* Responsive */
@media (max-width: 480px) {
  .register-card {
    padding: 24px 20px;
    max-width: 90%;
  }
  h1 {
    font-size: 20px;
  }
  input {
    font-size: 13px;
  }
  button[type='submit'] {
    font-size: 14px;
    padding: 10px;
  }
}

/* Titles */
h1 {
  text-align: center;
  margin-bottom: 20px;
  color: #2c3e50;
  font-size: 22px;
}

/* Toggle buttons */
.toggle-buttons {
  display: flex;
  justify-content: center;
  gap: 10px;
  margin-bottom: 20px;
}

.toggle-buttons button {
  padding: 8px 20px;
  border: 1px solid #42b883;
  background: white;
  color: #42b883;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
}

.toggle-buttons button.active {
  background: #42b883;
  color: white;
}

.toggle-buttons button:hover {
  background: #369f73;
  color: white;
}

/* Form */
.form-group {
  margin-bottom: 14px;
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

/* Button */
button[type='submit'] {
  width: 100%;
  background: #42b883;
  color: white;
  border: none;
  padding: 11px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 15px;
  font-weight: 600;
  margin-top: 10px;
  transition: background 0.3s ease;
}

button[type='submit']:hover {
  background: #369f73;
}

/* Errors */
.error {
  color: #e74c3c;
  font-size: 12px;
  margin-top: 4px;
}

/* Login link */
.login-link {
  text-align: center;
  margin-top: 16px;
  font-size: 13px;
}

.login-link a {
  color: #42b883;
  text-decoration: none;
  font-weight: 600;
}

.login-link a:hover {
  text-decoration: underline;
}

/* Fade-in animation */
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
