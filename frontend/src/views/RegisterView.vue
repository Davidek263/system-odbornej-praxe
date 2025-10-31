<template>
  <div class="register-page">
    <PageAlert 
      v-if="alert.show" 
      :type="alert.type" 
      :message="alert.message" 
      @close="alert.show = false" 
      dismissible 
    />
    <div class="register-container">
      <div class="register-card">
        <h1>{{ isCompany ? 'Registrácia Firmy' : 'Registrácia Študenta' }}</h1>
        
        <Spinner v-if="loading" overlay />

        <div class="toggle-buttons">
          <button :class="{ active: !isCompany }" @click="isCompany = false">Študent</button>
          <button :class="{ active: isCompany }" @click="isCompany = true">Firma</button>
        </div>

        <form @submit.prevent="handleRegister" class="register-form">
          <!-- PERSON FORM -->
          <template v-if="!isCompany">
            <div class="form-group">
              <label for="first_name">Meno</label>
              <input
                id="first_name"
                v-model="form.first_name"
                type="text"
                placeholder="Vlož svoje meno"
              />
              <p v-if="errors.first_name" class="error">{{ errors.first_name }}</p>
            </div>

            <div class="form-group">
              <label for="last_name">Priezvisko</label>
              <input
                id="last_name"
                v-model="form.last_name"
                type="text"
                placeholder="Vlož svoje priezvisko"
              />
              <p v-if="errors.last_name" class="error">{{ errors.last_name }}</p>
            </div>

            <div class="form-group">
              <label for="email">Email</label>
              <input id="email" v-model="form.email" type="email" placeholder="Vlož svoj email" />
              <p v-if="errors.email" class="error">{{ errors.email }}</p>
            </div>
          </template>

          <!-- COMPANY FORM -->
          <template v-else>
            <div class="form-group">
              <label for="companyName">Názov firmy</label>
              <input
                id="companyName"
                v-model="form.companyName"
                type="text"
                placeholder="Vlož názov firmy"
              />
              <p v-if="errors.companyName" class="error">{{ errors.companyName }}</p>
            </div>

            <div class="form-group">
              <label for="email">Firemný email</label>
              <input
                id="email"
                v-model="form.email"
                type="email"
                placeholder="Vlož firemný email pre kontaktnú osobu"
              />
              <p v-if="errors.email" class="error">{{ errors.email }}</p>
            </div>

            <div class="form-group">
              <label for="address">Adresa</label>
              <input
                id="address"
                v-model="form.address"
                type="text"
                placeholder="Vlož adresu firmy"
              />
              <p v-if="errors.address" class="error">{{ errors.address }}</p>
            </div>

            <div class="form-group">
              <label for="phone">Telefón</label>
              <input
                id="phone"
                v-model="form.phone"
                type="text"
                placeholder="Vlož telefónne číslo"
              />
              <p v-if="errors.phone" class="error">{{ errors.phone }}</p>
            </div>

            <div class="form-group">
              <label for="password">Heslo</label>
              <input
                id="password"
                v-model="form.password"
                type="password"
                placeholder="Vlož heslo"
              />
              <p v-if="errors.password" class="error">{{ errors.password }}</p>
            </div>
          </template>

          <button type="submit">Registrácia</button>
        </form>

        <p class="login-link">
          Už máš účet?
          <router-link to="/login">Prihlásenie</router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import api from '../api.js'
import PageAlert from '@/components/PageAlert.vue'
import Spinner from '@/components/Spinner.vue'

const isCompany = ref(false)
const loading = ref(false)

const alert = reactive({
  show: false,
  type: 'error',
  message: ''
})

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

function showAlert(message, type = 'error') {
  alert.message = message
  alert.type = type
  alert.show = true
}

function handleRegister() {
  // Reset errors
  Object.keys(errors).forEach((k) => (errors[k] = ''))
  alert.show = false
  
  let isValid = true
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

  if (!isCompany.value) {
    // Validation for person
    if (!form.first_name.trim()) {
      errors.first_name = 'Meno je povinné.'
      isValid = false
    }
    if (!form.last_name.trim()) {
      errors.last_name = 'Priezvisko je povinné.'
      isValid = false
    }
    if (!form.email.trim()) {
      errors.email = 'Email je povinný.'
      isValid = false
    } else if (!emailPattern.test(form.email)) {
      errors.email = 'Neplatný formát emailu.'
      isValid = false
    }
  } else {
    // Validation for company
    if (!form.companyName.trim()) {
      errors.companyName = 'Názov firmy je povinný.'
      isValid = false
    }
    if (!form.email.trim()) {
      errors.email = 'Firemný email je povinný.'
      isValid = false
    } else if (!emailPattern.test(form.email)) {
      errors.email = 'Neplatný formát emailu.'
      isValid = false
    }
    if (!form.address.trim()) {
      errors.address = 'Adresa je povinná.'
      isValid = false
    }
    if (!form.phone.trim()) {
      errors.phone = 'Telefónne číslo je povinné.'
      isValid = false
    }
    if (form.password.length < 6) {
      errors.password = 'Heslo musí mať aspoň 6 znakov.'
      isValid = false
    }
  }

  if (!isValid) {
    showAlert('validation.form', 'validation')
    return
  }

  const endpoint = isCompany.value ? '/register-company' : '/register-person'

  const payload = isCompany.value
    ? {
        companyName: form.companyName,
        email: form.email,
        address: form.address,
        phone: form.phone,
        password: form.password,
        password_confirmation: form.password,
      }
    : {
        first_name: form.first_name,
        last_name: form.last_name,
        email: form.email,
      }

  loading.value = true

  api
    .post(endpoint, payload)
    .then((res) => {
      const token = res.data.token
      localStorage.setItem('token', token)
      
      const successMessage = isCompany.value 
        ? 'success.register.company' 
        : 'success.register.student'
      
      showAlert(successMessage, 'success')
      
      Object.keys(form).forEach((k) => (form[k] = ''))
      
      // Optional: Redirect after success
      // setTimeout(() => {
      //   router.push('/dashboard')
      // }, 2000)
    })
    .catch((err) => {
      if (err.response) {
        const status = err.response.status
        
        // Handle validation errors from backend
        if (status === 422 && err.response.data.errors) {
          Object.assign(errors, err.response.data.errors)
          showAlert('validation.form', 'validation')
        } else if (status === 409) {
          showAlert('duplicate.email', 'error')
        } else if (status === 429) {
          showAlert('ratelimit.error', 'ratelimit')
        } else if (status >= 500) {
          showAlert('server.error', 'server')
        } else if (err.response.data?.message) {
          // Custom message from server
          showAlert(err.response.data.message, 'error')
        } else {
          showAlert('error.register', 'error')
        }
      } else if (err.code === 'ERR_NETWORK' || err.message.includes('Network Error')) {
        showAlert('network.error', 'network')
      } else if (err.code === 'ECONNABORTED' || err.message.includes('timeout')) {
        showAlert('timeout.error', 'timeout')
      } else {
        console.error('Registration error:', err)
        showAlert('error.register', 'error')
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
.register-page {
  height: 100%;
  margin: 0;
  background: linear-gradient(135deg, #42b883 0%, #2c3e50 100%);
  font-family: 'Inter', sans-serif;
}

.register-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: calc(100vh - 76px);
}

.register-card {
  width: 100%;
  max-width: 420px;
  background: #fff;
  padding: 36px 28px;
  border-radius: 16px;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
  animation: fadeIn 0.6s ease;
}

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

h1 {
  text-align: center;
  margin-bottom: 20px;
  color: #2c3e50;
  font-size: 22px;
}

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

.error {
  color: #e74c3c;
  font-size: 12px;
  margin-top: 4px;
}

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