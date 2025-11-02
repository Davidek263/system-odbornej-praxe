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
          <button 
            :class="{ active: !isCompany }" 
            @click="switchToStudent"
            :disabled="loading"
            type="button"
          >
            Študent
          </button>
          <button 
            :class="{ active: isCompany }" 
            @click="switchToCompany"
            :disabled="loading"
            type="button"
          >
            Firma
          </button>
        </div>

        <form @submit.prevent="handleRegister">
          <!-- STUDENT FORM -->
          <template v-if="!isCompany">
            <div class="form-row">
              <div class="form-group">
                <label for="first_name">Meno <span class="required">*</span></label>
                <input 
                  id="first_name" 
                  v-model="studentForm.first_name" 
                  type="text" 
                  placeholder="Meno"
                  :disabled="loading"
                />
                <p v-if="errors.first_name" class="error">{{ errors.first_name[0] }}</p>
              </div>

              <div class="form-group">
                <label for="last_name">Priezvisko <span class="required">*</span></label>
                <input 
                  id="last_name" 
                  v-model="studentForm.last_name" 
                  type="text" 
                  placeholder="Priezvisko"
                  :disabled="loading"
                />
                <p v-if="errors.last_name" class="error">{{ errors.last_name[0] }}</p>
              </div>
            </div>

            <div class="form-group">
              <label for="student_email">Študentský email <span class="required">*</span></label>
              <input 
                id="student_email" 
                v-model="studentForm.student_email" 
                type="email" 
                placeholder="meno.priezvisko@student.ukf.sk"
                :disabled="loading"
                @blur="validateStudentEmail"
              />
              <p class="hint">Formát: meno.priezvisko@student.ukf.sk (bez diakritiky)</p>
              <p v-if="errors.student_email" class="error">{{ errors.student_email[0] }}</p>
            </div>

            <div class="form-group">
              <label for="alternative_email">Alternatívny email</label>
              <input 
                id="alternative_email" 
                v-model="studentForm.alternative_email" 
                type="email" 
                placeholder="osobny.email@gmail.com (voliteľné)"
                :disabled="loading"
              />
              <p v-if="errors.alternative_email" class="error">{{ errors.alternative_email[0] }}</p>
            </div>

            <div class="form-group">
              <label for="phone_number">Telefónne číslo</label>
              <input 
                id="phone_number" 
                v-model="studentForm.phone_number" 
                type="text" 
                placeholder="+421 XXX XXX XXX"
                :disabled="loading"
              />
              <p v-if="errors.phone_number" class="error">{{ errors.phone_number[0] }}</p>
            </div>

            <div class="form-group">
              <label for="study_field_id">Študijný odbor <span class="required">*</span></label>
              <select 
                id="study_field_id" 
                v-model="studentForm.study_field_id"
                :disabled="loading || studyFieldsLoading"
              >
                <option value="" disabled>Vyberte študijný odbor</option>
                <option 
                  v-for="field in studyFields" 
                  :key="field.id" 
                  :value="field.id"
                >
                  {{ field.study_field_name }} ({{ field.abbreviation }})
                </option>
              </select>
              <p v-if="errors.study_field_id" class="error">{{ errors.study_field_id[0] }}</p>
            </div>

            <div class="section-title">Adresa <span class="required">*</span></div>

            <div class="form-row">
              <div class="form-group flex-2">
                <label for="street">Ulica <span class="required">*</span></label>
                <input 
                  id="street" 
                  v-model="studentForm.street" 
                  type="text" 
                  placeholder="Ulica"
                  :disabled="loading"
                />
                <p v-if="errors.street" class="error">{{ errors.street[0] }}</p>
              </div>

              <div class="form-group flex-1">
                <label for="street_number">Číslo <span class="required">*</span></label>
                <input 
                  id="street_number" 
                  v-model="studentForm.street_number" 
                  type="text" 
                  placeholder="123"
                  :disabled="loading"
                />
                <p v-if="errors.street_number" class="error">{{ errors.street_number[0] }}</p>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="city">Mesto <span class="required">*</span></label>
                <input 
                  id="city" 
                  v-model="studentForm.city" 
                  type="text" 
                  placeholder="Mesto"
                  :disabled="loading"
                />
                <p v-if="errors.city" class="error">{{ errors.city[0] }}</p>
              </div>

              <div class="form-group">
                <label for="postal_code">PSČ <span class="required">*</span></label>
                <input 
                  id="postal_code" 
                  v-model="studentForm.postal_code" 
                  type="text" 
                  placeholder="94901"
                  :disabled="loading"
                />
                <p v-if="errors.postal_code" class="error">{{ errors.postal_code[0] }}</p>
              </div>
            </div>

            <div class="form-group">
              <label for="country">Krajina</label>
              <input 
                id="country" 
                v-model="studentForm.country" 
                type="text" 
                placeholder="Slovakia"
                :disabled="loading"
              />
              <p v-if="errors.country" class="error">{{ errors.country[0] }}</p>
            </div>
          </template>

          <!-- COMPANY FORM -->
          <template v-else>
            <div class="form-group">
              <label for="company_name">Názov firmy <span class="required">*</span></label>
              <input 
                id="company_name" 
                v-model="companyForm.company_name" 
                type="text" 
                placeholder="Názov firmy"
                :disabled="loading"
              />
              <p v-if="errors.company_name" class="error">{{ errors.company_name[0] }}</p>
            </div>

            <div class="section-title">Kontaktná osoba <span class="required">*</span></div>

            <div class="form-group">
              <label for="contact_person_name">Meno a priezvisko <span class="required">*</span></label>
              <input 
                id="contact_person_name" 
                v-model="companyForm.contact_person_name" 
                type="text" 
                placeholder="Meno a priezvisko"
                :disabled="loading"
              />
              <p v-if="errors.contact_person_name" class="error">{{ errors.contact_person_name[0] }}</p>
            </div>

            <div class="form-group">
              <label for="contact_person_email">Email <span class="required">*</span></label>
              <input 
                id="contact_person_email" 
                v-model="companyForm.contact_person_email" 
                type="email" 
                placeholder="kontakt@firma.sk"
                :disabled="loading"
              />
              <p v-if="errors.contact_person_email" class="error">{{ errors.contact_person_email[0] }}</p>
            </div>

            <div class="form-group">
              <label for="contact_person_phone">Telefón <span class="required">*</span></label>
              <input 
                id="contact_person_phone" 
                v-model="companyForm.contact_person_phone" 
                type="text" 
                placeholder="+421 XXX XXX XXX"
                :disabled="loading"
              />
              <p v-if="errors.contact_person_phone" class="error">{{ errors.contact_person_phone[0] }}</p>
            </div>

            <div class="section-title">Adresa firmy <span class="required">*</span></div>

            <div class="form-row">
              <div class="form-group flex-2">
                <label for="company_street">Ulica <span class="required">*</span></label>
                <input 
                  id="company_street" 
                  v-model="companyForm.street" 
                  type="text" 
                  placeholder="Ulica"
                  :disabled="loading"
                />
                <p v-if="errors.street" class="error">{{ errors.street[0] }}</p>
              </div>

              <div class="form-group flex-1">
                <label for="company_street_number">Číslo <span class="required">*</span></label>
                <input 
                  id="company_street_number" 
                  v-model="companyForm.street_number" 
                  type="text" 
                  placeholder="123"
                  :disabled="loading"
                />
                <p v-if="errors.street_number" class="error">{{ errors.street_number[0] }}</p>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="company_city">Mesto <span class="required">*</span></label>
                <input 
                  id="company_city" 
                  v-model="companyForm.city" 
                  type="text" 
                  placeholder="Mesto"
                  :disabled="loading"
                />
                <p v-if="errors.city" class="error">{{ errors.city[0] }}</p>
              </div>

              <div class="form-group">
                <label for="company_postal_code">PSČ <span class="required">*</span></label>
                <input 
                  id="company_postal_code" 
                  v-model="companyForm.postal_code" 
                  type="text" 
                  placeholder="82109"
                  :disabled="loading"
                />
                <p v-if="errors.postal_code" class="error">{{ errors.postal_code[0] }}</p>
              </div>
            </div>

            <div class="form-group">
              <label for="company_country">Krajina</label>
              <input 
                id="company_country" 
                v-model="companyForm.country" 
                type="text" 
                placeholder="Slovakia"
                :disabled="loading"
              />
              <p v-if="errors.country" class="error">{{ errors.country[0] }}</p>
            </div>

            <div class="section-title">Heslo <span class="required">*</span></div>

            <div class="form-group">
              <label for="password">Heslo <span class="required">*</span></label>
              <input 
                id="password" 
                v-model="companyForm.password" 
                type="password" 
                placeholder="Minimálne 8 znakov"
                :disabled="loading"
              />
              <p v-if="errors.password" class="error">{{ errors.password[0] }}</p>
            </div>

            <div class="form-group">
              <label for="password_confirmation">Potvrdenie hesla <span class="required">*</span></label>
              <input 
                id="password_confirmation" 
                v-model="companyForm.password_confirmation" 
                type="password" 
                placeholder="Zopakujte heslo"
                :disabled="loading"
              />
              <p v-if="errors.password_confirmation" class="error">{{ errors.password_confirmation[0] }}</p>
            </div>
          </template>

          <button type="submit" :disabled="loading">
            {{ loading ? 'Registrácia prebieha...' : 'Registrácia' }}
          </button>
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
import { reactive, ref, onMounted } from 'vue'
import api from '@/api'
import PageAlert from '@/components/PageAlert.vue'
import Spinner from '@/components/Spinner.vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const isCompany = ref(false)
const loading = ref(false)
const studyFieldsLoading = ref(false)
const studyFields = ref([])

const studentForm = reactive({
  first_name: '',
  last_name: '',
  student_email: '',
  alternative_email: '',
  phone_number: '',
  study_field_id: '',
  street: '',
  street_number: '',
  city: '',
  postal_code: '',
  country: 'Slovakia',
})

const companyForm = reactive({
  company_name: '',
  contact_person_name: '',
  contact_person_email: '',
  contact_person_phone: '',
  street: '',
  street_number: '',
  city: '',
  postal_code: '',
  country: 'Slovakia',
  password: '',
  password_confirmation: '',
})

const errors = reactive({})
const alert = reactive({ show: false, type: 'error', message: '' })

function showAlert(message, type = 'error') {
  alert.message = message
  alert.type = type
  alert.show = true
}

function switchToStudent() {
  if (loading.value) return
  isCompany.value = false
  Object.keys(errors).forEach(k => delete errors[k])
  alert.show = false
}

function switchToCompany() {
  if (loading.value) return
  isCompany.value = true
  Object.keys(errors).forEach(k => delete errors[k])
  alert.show = false
}

function validateStudentEmail() {
  const email = studentForm.student_email.trim()
  const pattern = /^[a-z]+\.[a-z]+@student\.ukf\.sk$/
  
  if (email && !pattern.test(email)) {
    errors.student_email = ['Email musí byť vo formáte: meno.priezvisko@student.ukf.sk (bez diakritiky, malé písmená)']
  } else {
    delete errors.student_email
  }
}

async function fetchStudyFields() {
  studyFieldsLoading.value = true
  try {
    const response = await api.get('/study-fields')
    studyFields.value = response.data.study_fields
  } catch (err) {
    console.error('Failed to fetch study fields:', err)
    showAlert('Nepodarilo sa načítať študijné odbory.', 'error')
  } finally {
    studyFieldsLoading.value = false
  }
}

function handleRegister() {
  // Clear previous errors
  Object.keys(errors).forEach(k => delete errors[k])
  alert.show = false
  loading.value = true

  const endpoint = isCompany.value ? '/register-company' : '/register-student'
  const payload = isCompany.value ? { ...companyForm } : { ...studentForm }

  // Trim string fields
  Object.keys(payload).forEach(key => {
    if (typeof payload[key] === 'string') {
      payload[key] = payload[key].trim()
    }
  })

  // Convert study_field_id to number for student
  if (!isCompany.value && payload.study_field_id) {
    payload.study_field_id = parseInt(payload.study_field_id)
  }

  api.post(endpoint, payload)
    .then(res => {
      loading.value = false
      showAlert(
        res.data.message || 'Registrácia úspešná! Skontrolujte si email pre aktiváciu účtu.', 
        'success'
      )

      // Redirect to login after 4 seconds
      setTimeout(() => {
        router.push('/login')
      }, 4000)
    })
    .catch(err => {
      loading.value = false
      
      // Handle validation errors
      if (err.response?.status === 422 && err.response?.data?.errors) {
        Object.assign(errors, err.response.data.errors)
        showAlert('Prosím opravte chyby vo formulári.', 'error')
        
        // Scroll to first error
        setTimeout(() => {
          const firstError = document.querySelector('.error')
          if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' })
          }
        }, 100)
        return
      }

      // Generic error
      showAlert(
        err.response?.data?.message || 'Registrácia zlyhala. Skúste to znova.', 
        'error'
      )
    })
}

onMounted(() => {
  fetchStudyFields()
})
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
  padding: 30px 20px;
}

.register-card {
  width: 100%;
  max-width: 520px;
  background: #fff;
  padding: 36px 28px;
  border-radius: 16px;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
  animation: fadeIn 0.6s ease;
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
  margin-bottom: 24px;
}

.toggle-buttons button {
  padding: 8px 24px;
  border: 1px solid #42b883;
  background: white;
  color: #42b883;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
  font-size: 14px;
}

.toggle-buttons button.active {
  background: #42b883;
  color: white;
}

.toggle-buttons button:hover:not(:disabled) {
  background: #369f73;
  color: white;
}

.toggle-buttons button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.section-title {
  font-weight: 700;
  color: #2c3e50;
  font-size: 15px;
  margin: 20px 0 12px 0;
  padding-bottom: 6px;
  border-bottom: 2px solid #e8e8e8;
}

.form-row {
  display: flex;
  gap: 12px;
}

.form-row .form-group {
  flex: 1;
}

.form-row .flex-1 {
  flex: 1;
}

.form-row .flex-2 {
  flex: 2;
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
  font-size: 13px;
}

.required {
  color: #e74c3c;
}

input,
select {
  padding: 10px 12px;
  border: 1px solid #ccc;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.2s ease;
  font-family: inherit;
}

input:disabled,
select:disabled {
  background-color: #f5f5f5;
  cursor: not-allowed;
}

input:focus,
select:focus {
  outline: none;
  border-color: #42b883;
  box-shadow: 0 0 0 2px rgba(66, 184, 131, 0.25);
}

select {
  cursor: pointer;
}

.hint {
  font-size: 11px;
  color: #666;
  margin-top: 4px;
  font-style: italic;
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
  margin-top: 16px;
  transition: background 0.3s ease;
}

button[type='submit']:hover:not(:disabled) {
  background: #369f73;
}

button[type='submit']:disabled {
  background: #95d5b2;
  cursor: not-allowed;
}

.error {
  color: #e74c3c;
  font-size: 12px;
  margin-top: 4px;
  font-weight: 500;
}

.login-link {
  text-align: center;
  margin-top: 16px;
  font-size: 13px;
  color: #666;
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
  .register-card {
    padding: 24px 20px;
    max-width: 90%;
  }

  h1 {
    font-size: 20px;
  }

  .form-row {
    flex-direction: column;
    gap: 0;
  }

  input,
  select {
    font-size: 13px;
  }

  button[type='submit'] {
    font-size: 14px;
    padding: 10px;
  }

  .toggle-buttons button {
    padding: 7px 20px;
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