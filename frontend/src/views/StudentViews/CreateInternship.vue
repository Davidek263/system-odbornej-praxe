<template>
  <div class="create-internship-page">
    <PageAlert
      v-if="alert.show"
      :type="alert.type"
      :message="alert.message"
      @close="alert.show = false"
      dismissible
    />

    <div class="form-card">
      <h2>Vytvorenie novej odbornej praxe</h2>

      <Spinner v-if="loading" overlay />

      <form @submit.prevent="submitForm">
        <!-- Company Autocomplete -->
        <div class="form-group">
          <label for="company">Firma *</label>
          <div class="autocomplete-wrapper">
            <input
              id="company"
              v-model="companySearch"
              @input="filterCompanies"
              @focus="showCompanyDropdown = true"
              type="text"
              placeholder="Začnite písať názov firmy..."
              autocomplete="off"
              :required="!selectedCompany"
            />
            <div v-if="showCompanyDropdown && filteredCompanies.length" class="dropdown">
              <div
                v-for="company in filteredCompanies"
                :key="company.id"
                @click="selectCompany(company)"
                class="dropdown-item"
              >
                <div class="company-name">{{ company.company_name }}</div>
                <div class="company-info">
                  {{ company.address?.city || '' }}
                  <span v-if="company.contact_person_email"> • {{ company.contact_person_email }}</span>
                </div>
              </div>
            </div>
            <div v-if="showCompanyDropdown && companySearch && !filteredCompanies.length" class="dropdown">
              <div class="dropdown-empty">Žiadne firmy nenájdené</div>
            </div>
            <div v-if="selectedCompany" class="selected-badge">
              <span>✓ {{ selectedCompany.company_name }}</span>
              <button type="button" @click="clearCompany" class="clear-btn">✕</button>
            </div>
          </div>
        </div>

        <!-- Academic Year (no badge, just fills the field) -->
        <div class="form-group">
          <label for="academic_year">Akademický rok *</label>
          <input
            id="academic_year"
            v-model="form.academic_year"
            @input="filterYears"
            @focus="showYearDropdown = true"
            type="text"
            placeholder="Zadajte akademický rok (napr. 2024/2025)..."
            autocomplete="off"
            required
          />
          <div v-if="showYearDropdown && filteredYears.length" class="dropdown">
            <div
              v-for="year in filteredYears"
              :key="year"
              @click="selectYear(year)"
              class="dropdown-item"
            >
              {{ year }}
            </div>
          </div>
        </div>

        <!-- Semester -->
        <div class="form-group">
          <label for="semester">Semester *</label>
          <select id="semester" v-model="form.semester" required>
            <option value="">Vyberte semester</option>
            <option value="1">Zimný semester</option>
            <option value="2">Letný semester</option>
          </select>
        </div>

        <!-- Date Start -->
        <div class="form-group">
          <label for="date_start">Dátum začiatku *</label>
          <input id="date_start" v-model="form.date_start" type="date" required />
        </div>

        <!-- Date End -->
        <div class="form-group">
          <label for="date_end">Dátum konca *</label>
          <input id="date_end" v-model="form.date_end" type="date" required />
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
          <router-link to="/student-dashboard" class="cancel-btn">
            ← Späť na dashboard
          </router-link>
          <button type="submit" class="submit-btn" :disabled="submitting">
            {{ submitting ? 'Vytvára sa...' : 'Vytvoriť prax' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api'
import PageAlert from '@/components/PageAlert.vue'
import Spinner from '@/components/Spinner.vue'

const router = useRouter()

// State
const companies = ref([])
const loading = ref(false)
const submitting = ref(false)
const alert = reactive({ show: false, type: 'error', message: '' })

// Company autocomplete
const companySearch = ref('')
const selectedCompany = ref(null)
const showCompanyDropdown = ref(false)

// Year autocomplete
const showYearDropdown = ref(false)

// Form data
const form = reactive({
  company_id: null,
  academic_year: '',
  semester: '',
  date_start: '',
  date_end: ''
})

// Generate academic years (current year - 1 to current year + 5)
const academicYears = computed(() => {
  const currentYear = new Date().getFullYear()
  const years = []
  
  for (let i = -1; i <= 5; i++) {
    const year1 = currentYear + i
    const year2 = year1 + 1
    years.push(`${year1}/${year2}`)
  }
  
  return years
})

// Set current academic year as default
const currentAcademicYear = computed(() => {
  const now = new Date()
  const currentYear = now.getFullYear()
  const currentMonth = now.getMonth() + 1 // 1-12
  
  // Academic year typically starts in September (month 9)
  if (currentMonth >= 9) {
    return `${currentYear}/${currentYear + 1}`
  } else {
    return `${currentYear - 1}/${currentYear}`
  }
})

// Filter companies based on search
const filteredCompanies = computed(() => {
  if (!companySearch.value) return companies.value
  
  const search = companySearch.value.toLowerCase()
  return companies.value.filter(company => 
    company.company_name.toLowerCase().includes(search) ||
    company.address?.city?.toLowerCase().includes(search) ||
    company.contact_person_name?.toLowerCase().includes(search) ||
    company.contact_person_email?.toLowerCase().includes(search)
  )
})

// Filter years based on search
const filteredYears = computed(() => {
  if (!form.academic_year) return academicYears.value
  
  const search = form.academic_year.toLowerCase()
  return academicYears.value.filter(year => 
    year.toLowerCase().includes(search)
  )
})

// Functions
function showAlert(message, type = 'error') {
  alert.message = message
  alert.type = type
  alert.show = true
}

async function fetchCompanies() {
  loading.value = true
  try {
    const response = await api.get('/guarantor/companies')
    companies.value = response.data.companies || []
  } catch (err) {
    showAlert('Nepodarilo sa načítať zoznam firiem.', 'error')
    console.error('Error fetching companies:', err)
  } finally {
    loading.value = false
  }
}

function filterCompanies() {
  showCompanyDropdown.value = true
  // Clear selection if user types after selecting
  if (selectedCompany.value && companySearch.value !== selectedCompany.value.company_name) {
    selectedCompany.value = null
    form.company_id = null
  }
}

function selectCompany(company) {
  selectedCompany.value = company
  companySearch.value = company.company_name
  form.company_id = company.id
  showCompanyDropdown.value = false
}

function clearCompany() {
  selectedCompany.value = null
  companySearch.value = ''
  form.company_id = null
  showCompanyDropdown.value = false
}

function filterYears() {
  showYearDropdown.value = true
}

function selectYear(year) {
  form.academic_year = year
  showYearDropdown.value = false
}

// Close dropdowns when clicking outside
function handleClickOutside(event) {
  const target = event.target
  
  // Close company dropdown
  if (!target.closest('.autocomplete-wrapper')) {
    showCompanyDropdown.value = false
    showYearDropdown.value = false
  }
}

async function submitForm() {
  // Validate company selection
  if (!selectedCompany.value) {
    showAlert('Musíte vybrať firmu zo zoznamu.', 'error')
    return
  }

  // Validate academic year format
  if (!form.academic_year.match(/^\d{4}\/\d{4}$/)) {
    showAlert('Akademický rok musí byť vo formáte YYYY/YYYY (napr. 2024/2025).', 'error')
    return
  }

  // Validate dates
  if (new Date(form.date_start) >= new Date(form.date_end)) {
    showAlert('Dátum konca musí byť po dátume začiatku.', 'error')
    return
  }

  submitting.value = true

  try {
    const payload = {
      company_id: form.company_id,
      academic_year: form.academic_year,
      semester: parseInt(form.semester),
      date_start: form.date_start,
      date_end: form.date_end
    }

    // TODO: Replace with actual API endpoint
    await api.post('/internships', payload)
    
    showAlert('Prax bola úspešne vytvorená!', 'success')
    
    setTimeout(() => {
      router.push('/student-dashboard')
    }, 1500)

  } catch (err) {
    showAlert(err.response?.data?.message || 'Nepodarilo sa vytvoriť prax.', 'error')
    console.error('Error creating internship:', err)
  } finally {
    submitting.value = false
  }
}

// Lifecycle
onMounted(() => {
  fetchCompanies()
  
  // Set current academic year as default
  form.academic_year = currentAcademicYear.value
  
  // Add click outside listener
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
* {
  box-sizing: border-box;
}

.create-internship-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #38a169 0%, #2d5f4e 100%);
  padding: 40px 20px;
  font-family: 'Inter', 'Segoe UI', sans-serif;
  display: flex;
  justify-content: center;
  align-items: center;
}

.form-card {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
  padding: 40px;
  width: 100%;
  max-width: 600px;
  animation: fadeIn 0.6s ease;
  position: relative;
}

h2 {
  text-align: center;
  margin-bottom: 32px;
  color: #2c3e50;
  font-size: 26px;
  font-weight: 700;
}

.form-group {
  display: flex;
  flex-direction: column;
  margin-bottom: 20px;
  position: relative;
}

label {
  font-weight: 600;
  margin-bottom: 8px;
  color: #374151;
  font-size: 14px;
}

input,
select {
  padding: 12px 14px;
  border-radius: 8px;
  border: 1px solid #d1d5db;
  font-size: 14px;
  transition: all 0.2s;
  background: white;
  width: 100%;
}

input:focus,
select:focus {
  outline: none;
  border-color: #38a169;
  box-shadow: 0 0 0 3px rgba(56, 161, 105, 0.1);
}

input:disabled,
select:disabled {
  background: #f3f4f6;
  cursor: not-allowed;
}

/* Autocomplete Styles */
.autocomplete-wrapper {
  position: relative;
}

.dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  margin-top: 4px;
  max-height: 300px;
  overflow-y: auto;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  z-index: 1000;
  animation: slideDown 0.2s ease;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.dropdown-item {
  padding: 12px 14px;
  cursor: pointer;
  transition: background 0.15s;
  border-bottom: 1px solid #f3f4f6;
}

.dropdown-item:last-child {
  border-bottom: none;
}

.dropdown-item:hover {
  background: #f9fafb;
}

.dropdown-item:active {
  background: #f3f4f6;
}

.company-name {
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 2px;
}

.company-info {
  font-size: 12px;
  color: #6b7280;
}

.dropdown-empty {
  padding: 16px 14px;
  text-align: center;
  color: #9ca3af;
  font-size: 14px;
}

.selected-badge {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 8px;
  padding: 8px 12px;
  background: #d1fae5;
  border: 1px solid #10b981;
  border-radius: 6px;
  font-size: 14px;
  color: #065f46;
  font-weight: 600;
}

.clear-btn {
  background: transparent;
  border: none;
  color: #065f46;
  cursor: pointer;
  font-size: 18px;
  padding: 0 4px;
  line-height: 1;
  transition: color 0.2s;
}

.clear-btn:hover {
  color: #047857;
}

/* Form Actions */
.form-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 32px;
  gap: 16px;
}

.cancel-btn {
  color: #6b7280;
  text-decoration: none;
  font-weight: 600;
  font-size: 14px;
  transition: color 0.2s;
}

.cancel-btn:hover {
  color: #374151;
}

.submit-btn {
  background: #38a169;
  color: #fff;
  border: none;
  padding: 12px 24px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s;
}

.submit-btn:hover:not(:disabled) {
  background: #2f855a;
  transform: translateY(-1px);
}

.submit-btn:disabled {
  background: #9ca3af;
  cursor: not-allowed;
  transform: none;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
  .create-internship-page {
    padding: 20px 12px;
  }

  .form-card {
    padding: 24px 20px;
  }

  h2 {
    font-size: 22px;
    margin-bottom: 24px;
  }

  .form-group {
    margin-bottom: 18px;
  }

  input,
  select {
    font-size: 16px; /* Prevent iOS zoom */
  }

  .form-actions {
    flex-direction: column;
    gap: 12px;
  }

  .cancel-btn,
  .submit-btn {
    width: 100%;
    text-align: center;
    padding: 12px;
  }

  .dropdown {
    max-height: 200px;
  }
}

@media (max-width: 480px) {
  .form-card {
    padding: 20px 16px;
  }

  h2 {
    font-size: 20px;
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>