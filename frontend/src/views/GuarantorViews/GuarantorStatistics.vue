<template>
  <div class="statistics-page">
    <PageAlert
      v-if="alert.show"
      :type="alert.type"
      :message="alert.message"
      :duration="alert.duration"
      @close="alert.show = false"
    />

    <div class="statistics-container">
      <div class="panel-card">
        <header class="panel-header">
          <h1>📊 Štatistiky a reporty</h1>
          <div class="actions">
            <button class="refresh-btn" @click="fetchStatistics" :disabled="loading">
              {{ loading ? 'Načítavam...' : '🔄 Obnoviť' }}
            </button>
          </div>
        </header>

        <div class="panel-body" v-if="!loading">
          <!-- Summary Cards -->
          <div class="summary-cards">
            <div class="stat-card">
              <div class="stat-icon">📋</div>
              <div class="stat-content">
                <h3>Celkový počet praxí</h3>
                <p class="stat-number">{{ stats.total }}</p>
              </div>
            </div>

            <div class="stat-card">
              <div class="stat-icon">✅</div>
              <div class="stat-content">
                <h3>Schválené</h3>
                <p class="stat-number">{{ stats.approved }}</p>
              </div>
            </div>

            <div class="stat-card">
              <div class="stat-icon">🎓</div>
              <div class="stat-content">
                <h3>Obhájené</h3>
                <p class="stat-number">{{ stats.defended }}</p>
              </div>
            </div>

            <div class="stat-card">
              <div class="stat-icon">⏳</div>
              <div class="stat-content">
                <h3>Čakajúce</h3>
                <p class="stat-number">{{ stats.created + stats.confirmed }}</p>
              </div>
            </div>
          </div>

          <!-- Charts Row -->
          <div class="charts-row">
            <!-- Status Distribution Pie Chart -->
            <div class="chart-card">
              <h3>Rozdelenie podľa stavu</h3>
              <canvas ref="statusChart"></canvas>
            </div>

            <!-- Academic Year Bar Chart -->
            <div class="chart-card">
              <h3>Rozdelenie podľa akademického roku</h3>
              <canvas ref="yearChart"></canvas>
            </div>
          </div>

          <!-- Second Charts Row -->
          <div class="charts-row">
            <!-- Semester Distribution Chart -->
            <div class="chart-card">
              <h3>Rozdelenie podľa semestra</h3>
              <canvas ref="semesterChart"></canvas>
            </div>

            <!-- Study Field Chart -->
            <div class="chart-card">
              <h3>Rozdelenie podľa študijného odboru</h3>
              <canvas ref="studyFieldChart"></canvas>
            </div>
          </div>

          <!-- Third Charts Row -->
          <div class="charts-row">
            <!-- Internship Type Chart -->
            <div class="chart-card">
              <h3>Typ praxe</h3>
              <canvas ref="internshipTypeChart"></canvas>
            </div>

            <!-- Company Chart -->
            <div class="chart-card">
              <h3>Top 10 firiem podľa počtu praxí</h3>
              <canvas ref="companyChart"></canvas>
            </div>
          </div>

          <!-- CSV Export Section -->
          <div class="export-section">
            <div class="export-header">
              <h2>📥 Export údajov do CSV</h2>
              <p>Vyberte stĺpce a nastavte filtry pre export údajov do CSV súboru.</p>
            </div>

            <button class="export-btn" @click="openExportModal" :disabled="exporting">
              {{ exporting ? 'Exportujem...' : '📄 Vytvoriť CSV Export' }}
            </button>
          </div>
        </div>

        <div class="panel-body" v-else>
          <div class="loading-spinner">
            <div class="spinner"></div>
            <p>Načítavam štatistiky...</p>
          </div>
        </div>
      </div>
    </div>

    <!-- CSV Export Modal -->
    <div v-if="exportModal" class="modal-backdrop" @click.self="closeExportModal">
      <div class="modal-card modal-medium">
        <header class="modal-header">
          <h2>CSV report – výber stĺpcov a filtrov</h2>
          <button class="close-btn" @click="closeExportModal">✕</button>
        </header>

        <div class="modal-body">
          <div class="detail-section" style="border-bottom:none; padding-bottom:0; margin-bottom:0;">
            <p style="font-size: 14px; color: #666; margin-bottom: 16px;">Zakliknite stĺpce, ktoré chcete exportovať. Pre každý stĺpec môžete pridať filter.</p>

            <!-- Študijný odbor -->
            <div class="export-column-item">
              <label class="check-item">
                <input type="checkbox" v-model="exportOptions.columns.studyField" />
                Študijný odbor
              </label>
              <div v-if="exportOptions.columns.studyField" class="filter-input-wrapper">
                <div class="autocomplete-wrapper">
                  <input
                    type="text"
                    v-model="exportOptions.filters.studyField"
                    @focus="showExportStudyFieldDropdown = true"
                    placeholder="Filtrovať podľa odboru..."
                    class="filter-input"
                    autocomplete="off"
                  />
                  <div v-if="showExportStudyFieldDropdown && filteredExportStudyFields.length" class="dropdown">
                    <div
                      v-for="field in filteredExportStudyFields"
                      :key="field"
                      @click="exportOptions.filters.studyField = field; showExportStudyFieldDropdown = false"
                      class="dropdown-item"
                    >
                      {{ field }}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Akademický rok -->
            <div class="export-column-item">
              <label class="check-item">
                <input type="checkbox" v-model="exportOptions.columns.academicYear" />
                Akademický rok
              </label>
              <div v-if="exportOptions.columns.academicYear" class="filter-input-wrapper">
                <div class="autocomplete-wrapper">
                  <input
                    type="text"
                    v-model="exportOptions.filters.academicYear"
                    @focus="showExportAcademicYearDropdown = true"
                    placeholder="Filtrovať podľa roka..."
                    class="filter-input"
                    autocomplete="off"
                  />
                  <div v-if="showExportAcademicYearDropdown && filteredExportAcademicYears.length" class="dropdown">
                    <div
                      v-for="year in filteredExportAcademicYears"
                      :key="year"
                      @click="exportOptions.filters.academicYear = year; showExportAcademicYearDropdown = false"
                      class="dropdown-item"
                    >
                      {{ year }}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Meno -->
            <div class="export-column-item">
              <label class="check-item">
                <input type="checkbox" v-model="exportOptions.columns.firstName" />
                Meno
              </label>
              <div v-if="exportOptions.columns.firstName" class="filter-input-wrapper">
                <input
                  type="text"
                  v-model="exportOptions.filters.firstName"
                  placeholder="Filtrovať podľa mena..."
                  class="filter-input"
                />
              </div>
            </div>

            <!-- Priezvisko -->
            <div class="export-column-item">
              <label class="check-item">
                <input type="checkbox" v-model="exportOptions.columns.lastName" />
                Priezvisko
              </label>
              <div v-if="exportOptions.columns.lastName" class="filter-input-wrapper">
                <input
                  type="text"
                  v-model="exportOptions.filters.lastName"
                  placeholder="Filtrovať podľa priezviska..."
                  class="filter-input"
                />
              </div>
            </div>

            <!-- Email -->
            <div class="export-column-item">
              <label class="check-item">
                <input type="checkbox" v-model="exportOptions.columns.email" />
                Email
              </label>
              <div v-if="exportOptions.columns.email" class="filter-input-wrapper">
                <input
                  type="text"
                  v-model="exportOptions.filters.email"
                  placeholder="Filtrovať podľa emailu..."
                  class="filter-input"
                />
              </div>
            </div>

            <!-- Študentský email -->
            <div class="export-column-item">
              <label class="check-item">
                <input type="checkbox" v-model="exportOptions.columns.studentEmail" />
                Študentský email
              </label>
              <div v-if="exportOptions.columns.studentEmail" class="filter-input-wrapper">
                <input
                  type="text"
                  v-model="exportOptions.filters.studentEmail"
                  placeholder="Filtrovať podľa študentského emailu..."
                  class="filter-input"
                />
              </div>
            </div>

            <!-- Alternatívny email -->
            <div class="export-column-item">
              <label class="check-item">
                <input type="checkbox" v-model="exportOptions.columns.alternativeEmail" />
                Alternatívny email
              </label>
              <div v-if="exportOptions.columns.alternativeEmail" class="filter-input-wrapper">
                <input
                  type="text"
                  v-model="exportOptions.filters.alternativeEmail"
                  placeholder="Filtrovať podľa alternatívneho emailu..."
                  class="filter-input"
                />
              </div>
            </div>

            <!-- Firma -->
            <div class="export-column-item">
              <label class="check-item">
                <input type="checkbox" v-model="exportOptions.columns.company" />
                Firma
              </label>
              <div v-if="exportOptions.columns.company" class="filter-input-wrapper">
                <div class="autocomplete-wrapper">
                  <input
                    type="text"
                    v-model="exportOptions.filters.company"
                    @focus="showExportCompanyDropdown = true"
                    placeholder="Filtrovať podľa firmy..."
                    class="filter-input"
                    autocomplete="off"
                  />
                  <div v-if="showExportCompanyDropdown && filteredExportCompanies.length" class="dropdown">
                    <div
                      v-for="company in filteredExportCompanies"
                      :key="company"
                      @click="exportOptions.filters.company = company; showExportCompanyDropdown = false"
                      class="dropdown-item"
                    >
                      {{ company }}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Dátum začiatku -->
            <div class="export-column-item">
              <label class="check-item">
                <input type="checkbox" v-model="exportOptions.columns.dateStart" />
                Dátum začiatku
              </label>
              <div v-if="exportOptions.columns.dateStart" class="filter-input-wrapper">
                <input
                  type="date"
                  v-model="exportOptions.filters.dateStart"
                  class="filter-input"
                />
              </div>
            </div>

            <!-- Dátum konca -->
            <div class="export-column-item">
              <label class="check-item">
                <input type="checkbox" v-model="exportOptions.columns.dateEnd" />
                Dátum konca
              </label>
              <div v-if="exportOptions.columns.dateEnd" class="filter-input-wrapper">
                <input
                  type="date"
                  v-model="exportOptions.filters.dateEnd"
                  class="filter-input"
                />
              </div>
            </div>

            <!-- Stav praxe -->
            <div class="export-column-item">
              <label class="check-item">
                <input type="checkbox" v-model="exportOptions.columns.status" />
                Stav praxe
              </label>
              <div v-if="exportOptions.columns.status" class="filter-input-wrapper">
                <div class="autocomplete-wrapper">
                  <input
                    type="text"
                    v-model="exportOptions.filters.status"
                    @focus="showExportStatusDropdown = true"
                    placeholder="Filtrovať podľa stavu..."
                    class="filter-input"
                    autocomplete="off"
                  />
                  <div v-if="showExportStatusDropdown && filteredExportStatuses.length" class="dropdown">
                    <div
                      v-for="status in filteredExportStatuses"
                      :key="status"
                      @click="exportOptions.filters.status = status; showExportStatusDropdown = false"
                      class="dropdown-item"
                    >
                      {{ status }}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="info-box" style="margin-top:16px;">
              <strong>ℹ️ Tip:</strong>
              Filtry sa aplikujú len na údaje v exporte.
            </div>
          </div>
        </div>

        <footer class="modal-footer">
          <button class="ghost" @click="closeExportModal">Zrušiť</button>
          <button class="approve" @click="confirmExport" :disabled="exporting">
            {{ exporting ? 'Exportujem...' : 'Generovať CSV' }}
          </button>
        </footer>
      </div>
    </div>
  </div>
</template>

<script setup>
// ============================================================
// IMPORTS & SETUP
// ============================================================
import { reactive, ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { Chart, registerables } from 'chart.js'
import api from '@/api'
import PageAlert from '@/components/PageAlert.vue'
import { useRouter } from 'vue-router'

Chart.register(...registerables)

const router = useRouter()

// ============================================================
// ALERT SYSTEM
// ============================================================
const alert = reactive({ show: false, type: 'error', message: '', duration: 5000 })

function showAlert(message, type = 'error', duration = 5000) {
  alert.message = message
  alert.type = type
  alert.duration = duration
  alert.show = true
}

// ============================================================
// STATE MANAGEMENT
// ============================================================
const loading = ref(false)
const exporting = ref(false)
const internships = ref([])

// Chart refs
const statusChart = ref(null)
const yearChart = ref(null)
const semesterChart = ref(null)
const studyFieldChart = ref(null)
const internshipTypeChart = ref(null)
const companyChart = ref(null)

// Chart instances
let statusChartInstance = null
let yearChartInstance = null
let semesterChartInstance = null
let studyFieldChartInstance = null
let internshipTypeChartInstance = null
let companyChartInstance = null

// ============================================================
// COMPUTED PROPERTIES
// ============================================================
const stats = computed(() => {
  const created = internships.value.filter(i => i.current_status?.internship_status_name === 'Vytvorená').length
  const confirmed = internships.value.filter(i => i.current_status?.internship_status_name === 'Potvrdená').length
  const approved = internships.value.filter(i => i.current_status?.internship_status_name === 'Schválená').length
  const defended = internships.value.filter(i => i.current_status?.internship_status_name === 'Obhájená').length
  const failed = internships.value.filter(i => i.current_status?.internship_status_name === 'Neobhájená').length
  const rejected = internships.value.filter(i => i.current_status?.internship_status_name === 'Zamietnutá').length

  return {
    total: internships.value.length,
    created,
    confirmed,
    approved,
    defended,
    failed,
    rejected
  }
})

// ============================================================
// EXPORT STATE
// ============================================================
const exportModal = ref(false)

const exportOptions = reactive({
  columns: {
    studyField: true,
    academicYear: true,
    firstName: true,
    lastName: true,
    email: true,
    studentEmail: true,
    alternativeEmail: true,
    company: true,
    dateStart: true,
    dateEnd: true,
    status: true,
  },
  filters: {
    studyField: '',
    academicYear: '',
    firstName: '',
    lastName: '',
    email: '',
    studentEmail: '',
    alternativeEmail: '',
    company: '',
    dateStart: '',
    dateEnd: '',
    status: '',
  }
})

// Export modal dropdown states
const showExportStudyFieldDropdown = ref(false)
const showExportAcademicYearDropdown = ref(false)
const showExportCompanyDropdown = ref(false)
const showExportStatusDropdown = ref(false)

const statuses = [
  'Vytvorená',
  'Potvrdená',
  'Schválená',
  'Obhájená',
  'Neobhájená',
  'Zamietnutá'
]

const academicYears = computed(() => {
  const years = new Set()
  internships.value.forEach(i => {
    if (i.academic_year) years.add(i.academic_year)
  })
  return Array.from(years).sort().reverse()
})

const companies = computed(() => {
  const companyNames = new Set()
  internships.value.forEach(i => {
    const name = i.company?.company_name
    if (name) companyNames.add(name)
  })
  return Array.from(companyNames).sort()
})

const studyFields = computed(() => {
  const fields = new Set()
  internships.value.forEach(i => {
    const fieldName = i.student?.study_field?.study_field_name
    if (fieldName) fields.add(fieldName)
  })
  return Array.from(fields).sort()
})

// ============================================================
// EXPORT FILTERS (COMPUTED)
// ============================================================
const filteredExportStudyFields = computed(() => {
  if (!exportOptions.filters.studyField) return studyFields.value
  const search = exportOptions.filters.studyField.toLowerCase()
  return studyFields.value.filter(field => field.toLowerCase().includes(search))
})

const filteredExportAcademicYears = computed(() => {
  if (!exportOptions.filters.academicYear) return academicYears.value
  const search = exportOptions.filters.academicYear.toLowerCase()
  return academicYears.value.filter(year => year.toLowerCase().includes(search))
})

const filteredExportCompanies = computed(() => {
  if (!exportOptions.filters.company) return companies.value
  const search = exportOptions.filters.company.toLowerCase()
  return companies.value.filter(company => company.toLowerCase().includes(search))
})

const filteredExportStatuses = computed(() => {
  if (!exportOptions.filters.status) return statuses
  const search = exportOptions.filters.status.toLowerCase()
  return statuses.filter(status => status.toLowerCase().includes(search))
})

// ============================================================
// API CALLS
// ============================================================
async function fetchStatistics() {
  loading.value = true
  try {
    const response = await api.get('/guarantor/internships')
    internships.value = response.data.internships || []
  } catch (err) {
    showAlert(err.response?.data?.message || 'Nepodarilo sa načítať štatistiky.', 'error')

    if (err.response?.status === 401) {
      setTimeout(() => {
        localStorage.removeItem('token')
        localStorage.removeItem('user')
        router.push('/login')
      }, 2000)
    }
  } finally {
    loading.value = false
    // Wait for DOM to update after loading is set to false
    await nextTick()
    // Give canvas elements a moment to be fully rendered
    setTimeout(() => {
      renderCharts()
    }, 100)
  }
}

// ============================================================
// CHART RENDERING
// ============================================================
function renderCharts() {
  renderStatusChart()
  renderYearChart()
  renderSemesterChart()
  renderStudyFieldChart()
  renderInternshipTypeChart()
  renderCompanyChart()
}

function renderStatusChart() {
  if (statusChartInstance) {
    statusChartInstance.destroy()
  }

  if (!statusChart.value) return

  const ctx = statusChart.value.getContext('2d')
  statusChartInstance = new Chart(ctx, {
    type: 'pie',
    data: {
      labels: ['Vytvorená', 'Potvrdená', 'Schválená', 'Obhájená', 'Neobhájená', 'Zamietnutá'],
      datasets: [{
        data: [
          stats.value.created,
          stats.value.confirmed,
          stats.value.approved,
          stats.value.defended,
          stats.value.failed,
          stats.value.rejected
        ],
        backgroundColor: [
          '#3b82f6',
          '#f59e0b',
          '#10b981',
          '#06b6d4',
          '#ef4444',
          '#6b7280'
        ]
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom'
        }
      }
    }
  })
}

function renderYearChart() {
  if (yearChartInstance) {
    yearChartInstance.destroy()
  }

  if (!yearChart.value) return

  const yearCounts = {}
  internships.value.forEach(i => {
    const year = i.academic_year || 'Nezadaný'
    yearCounts[year] = (yearCounts[year] || 0) + 1
  })

  const sortedYears = Object.keys(yearCounts).sort()
  const ctx = yearChart.value.getContext('2d')

  yearChartInstance = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: sortedYears,
      datasets: [{
        label: 'Počet praxí',
        data: sortedYears.map(year => yearCounts[year]),
        backgroundColor: '#3b82f6'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 1
          }
        }
      },
      plugins: {
        legend: {
          display: false
        }
      }
    }
  })
}

function renderSemesterChart() {
  if (semesterChartInstance) {
    semesterChartInstance.destroy()
  }

  if (!semesterChart.value) return

  // Count internships by semester
  const semesterCounts = {
    'Zimný semester': 0,
    'Letný semester': 0,
    'Nezadané': 0
  }

  internships.value.forEach(i => {
    if (!i.semester) {
      semesterCounts['Nezadané']++
    } else if (i.semester === 1) {
      semesterCounts['Zimný semester']++
    } else if (i.semester === 2) {
      semesterCounts['Letný semester']++
    } else {
      semesterCounts['Nezadané']++
    }
  })

  const ctx = semesterChart.value.getContext('2d')

  semesterChartInstance = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: Object.keys(semesterCounts),
      datasets: [{
        data: Object.values(semesterCounts),
        backgroundColor: [
          '#3b82f6',  // blue for winter
          '#f59e0b',  // orange for summer
          '#6b7280'   // gray for unspecified
        ]
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom'
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              const label = context.label || ''
              const value = context.parsed || 0
              const total = internships.value.length
              const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0
              return `${label}: ${value} (${percentage}%)`
            }
          }
        }
      }
    }
  })
}

function renderInternshipTypeChart() {
  if (internshipTypeChartInstance) {
    internshipTypeChartInstance.destroy()
  }

  if (!internshipTypeChart.value) return

  // Count internships by type
  const typeCounts = {
    'Prax': 0,
    'Brigáda': 0,
    'Nezadané': 0
  }

  internships.value.forEach(i => {
    if (!i.internship_type) {
      typeCounts['Nezadané']++
    } else if (i.internship_type === 'prax') {
      typeCounts['Prax']++
    } else if (i.internship_type === 'brigada') {
      typeCounts['Brigáda']++
    } else {
      typeCounts['Nezadané']++
    }
  })

  const ctx = internshipTypeChart.value.getContext('2d')

  internshipTypeChartInstance = new Chart(ctx, {
    type: 'pie',
    data: {
      labels: Object.keys(typeCounts),
      datasets: [{
        data: Object.values(typeCounts),
        backgroundColor: [
          '#10b981',  // green for prax (professional internship)
          '#8b5cf6',  // purple for brigada (work/job)
          '#6b7280'   // gray for unspecified
        ]
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom'
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              const label = context.label || ''
              const value = context.parsed || 0
              const total = internships.value.length
              const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0
              return `${label}: ${value} (${percentage}%)`
            }
          }
        }
      }
    }
  })
}

function renderStudyFieldChart() {
  if (studyFieldChartInstance) {
    studyFieldChartInstance.destroy()
  }

  if (!studyFieldChart.value) return

  const fieldCounts = {}
  internships.value.forEach(i => {
    const field = i.student?.study_field?.study_field_name || 'Nezadaný'
    fieldCounts[field] = (fieldCounts[field] || 0) + 1
  })

  const sortedFields = Object.keys(fieldCounts).sort((a, b) => fieldCounts[b] - fieldCounts[a])
  const ctx = studyFieldChart.value.getContext('2d')

  studyFieldChartInstance = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: sortedFields,
      datasets: [{
        data: sortedFields.map(field => fieldCounts[field]),
        backgroundColor: [
          '#3b82f6',
          '#10b981',
          '#f59e0b',
          '#ef4444',
          '#8b5cf6',
          '#ec4899',
          '#06b6d4',
          '#84cc16',
          '#f97316',
          '#6366f1'
        ]
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom'
        }
      }
    }
  })
}

function renderCompanyChart() {
  if (companyChartInstance) {
    companyChartInstance.destroy()
  }

  if (!companyChart.value) return

  const companyCounts = {}
  internships.value.forEach(i => {
    const company = i.company?.company_name || 'Nezadaná'
    companyCounts[company] = (companyCounts[company] || 0) + 1
  })

  const sortedCompanies = Object.keys(companyCounts)
    .sort((a, b) => companyCounts[b] - companyCounts[a])
    .slice(0, 10)

  const ctx = companyChart.value.getContext('2d')

  companyChartInstance = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: sortedCompanies,
      datasets: [{
        label: 'Počet praxí',
        data: sortedCompanies.map(company => companyCounts[company]),
        backgroundColor: '#10b981'
      }]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        x: {
          beginAtZero: true,
          ticks: {
            stepSize: 1
          }
        }
      },
      plugins: {
        legend: {
          display: false
        }
      }
    }
  })
}

// ============================================================
// EXPORT FUNCTIONS
// ============================================================
function openExportModal() {
  exportModal.value = true
}

function closeExportModal() {
  exportModal.value = false
}

function buildExportPayload() {
  const columns = Object.entries(exportOptions.columns)
    .filter(([, v]) => v)
    .map(([k]) => k)

  // Only include filters that have values
  const filters = Object.entries(exportOptions.filters)
    .filter(([, v]) => v && v.trim() !== '')
    .reduce((acc, [k, v]) => {
      acc[k] = v
      return acc
    }, {})

  return { columns, filters }
}

async function confirmExport() {
  const { columns, filters } = buildExportPayload()
  if (!columns.length) {
    showAlert('Musíte vybrať aspoň jeden stĺpec.', 'error')
    return
  }

  exportModal.value = false
  await downloadCsv({ columns, filters })
}

function getFilenameFromHeaders(headers) {
  const disposition =
    headers?.['content-disposition'] ||
    headers?.['Content-Disposition'] ||
    headers?.get?.('content-disposition') ||
    headers?.get?.('Content-Disposition')

  if (!disposition) return null

  const utf8Match = disposition.match(/filename\*\s*=\s*UTF-8''([^;]+)/i)
  if (utf8Match && utf8Match[1]) return decodeURIComponent(utf8Match[1].replace(/"/g, ''))

  const normalMatch = disposition.match(/filename\s*=\s*"?([^"]+)"?/i)
  if (normalMatch && normalMatch[1]) return normalMatch[1]

  return null
}

function buildFallbackCsvName() {
  const pad = (n) => String(n).padStart(2, '0')
  const d = new Date()
  const stamp =
    `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}_${pad(d.getHours())}-${pad(d.getMinutes())}`
  return `report_praxe_${stamp}.csv`
}

async function downloadCsv(customOptions = null) {
  exporting.value = true
  try {
    const payload = {
      ...(customOptions ? {
        columns: customOptions.columns,
        filters: customOptions.filters
      } : {})
    }

    const response = await api.post('/guarantor/internships/export', payload, {
      responseType: 'blob',
    })

    // Get filename from header, or use fallback
    let filename = getFilenameFromHeaders(response.headers) || buildFallbackCsvName()

    // Ensure CSV extension
    filename = filename.replace(/\.xlsx$/i, '.csv')
    if (!/\.csv$/i.test(filename)) filename = `${filename}.csv`

    // Add BOM for proper diacritics in Excel
    const bom = '\uFEFF'
    const blob = new Blob([bom, response.data], { type: 'text/csv;charset=utf-8;' })

    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = filename
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)

    showAlert('CSV report bol úspešne stiahnutý.', 'success', 3000)
  } catch (err) {
    console.error(err)
    showAlert(err.response?.data?.message || 'Nepodarilo sa stiahnuť CSV report.', 'error')

    if (err.response?.status === 401) {
      setTimeout(() => {
        localStorage.removeItem('token')
        localStorage.removeItem('user')
        router.push('/login')
      }, 2000)
    }
  } finally {
    exporting.value = false
  }
}

// ============================================================
// EVENT HANDLERS
// ============================================================
function handleClickOutside(event) {
  const target = event.target
  const clickedWrapper = target.closest('.autocomplete-wrapper')

  if (!clickedWrapper) {
    showExportStudyFieldDropdown.value = false
    showExportAcademicYearDropdown.value = false
    showExportCompanyDropdown.value = false
    showExportStatusDropdown.value = false
  }
}

// ============================================================
// LIFECYCLE HOOKS
// ============================================================
onMounted(() => {
  fetchStatistics()
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  // Destroy chart instances
  if (statusChartInstance) statusChartInstance.destroy()
  if (yearChartInstance) yearChartInstance.destroy()
  if (semesterChartInstance) semesterChartInstance.destroy()
  if (studyFieldChartInstance) studyFieldChartInstance.destroy()
  if (internshipTypeChartInstance) internshipTypeChartInstance.destroy()
  if (companyChartInstance) companyChartInstance.destroy()

  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
* {
  box-sizing: border-box;
}

.statistics-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #ffb74d 0%, #ff8a65 100%);
  padding: 24px;
  font-family: 'Inter', 'Segoe UI', sans-serif;
}

.statistics-container {
  max-width: 1400px;
  margin: 0 auto;
}

.panel-card {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
  overflow: hidden;
  animation: fadeIn 0.6s ease;
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

.panel-header {
  background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
  color: #fff;
  padding: 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
}

.panel-header h1 {
  margin: 0;
  font-size: 26px;
  font-weight: 700;
}

.actions {
  display: flex;
  gap: 10px;
}

.refresh-btn {
  padding: 10px 18px;
  border: none;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.2);
  color: #fff;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s;
}

.refresh-btn:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.3);
}

.refresh-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.panel-body {
  padding: 24px;
}

.loading-spinner {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid #e5e7eb;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.loading-spinner p {
  margin-top: 16px;
  color: #6b7280;
  font-size: 16px;
}

/* Summary Cards */
.summary-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: #fff;
  padding: 24px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
  transition: transform 0.2s;
}

.stat-card:hover {
  transform: translateY(-4px);
}

.stat-icon {
  font-size: 40px;
}

.stat-content h3 {
  margin: 0 0 8px 0;
  font-size: 14px;
  font-weight: 600;
  opacity: 0.9;
}

.stat-number {
  margin: 0;
  font-size: 32px;
  font-weight: 700;
}

/* Charts */
.charts-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 24px;
  margin-bottom: 24px;
}

.chart-card {
  background: #f9fafb;
  padding: 24px;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
  min-height: 400px;
  display: flex;
  flex-direction: column;
}

.chart-card h3 {
  margin: 0 0 16px 0;
  font-size: 18px;
  font-weight: 700;
  color: #1f2937;
}

.chart-card canvas {
  max-height: 350px;
  flex: 1;
}

/* Export Section */
.export-section {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  padding: 32px;
  border-radius: 12px;
  text-align: center;
  margin-top: 24px;
}

.export-header h2 {
  margin: 0 0 8px 0;
  color: #fff;
  font-size: 24px;
  font-weight: 700;
}

.export-header p {
  margin: 0 0 20px 0;
  color: rgba(255, 255, 255, 0.9);
  font-size: 16px;
}

.export-btn {
  padding: 14px 32px;
  border: none;
  border-radius: 8px;
  background: #fff;
  color: #059669;
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}

.export-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.export-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Modal Styles */
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  padding: 20px;
}

.modal-card {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
  overflow: hidden;
  animation: modalFadeIn 0.3s ease;
}

.modal-card.modal-medium {
  max-width: 800px;
  max-height: 90vh;
  overflow-y: auto;
}

@keyframes modalFadeIn {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid #e5e7eb;
  background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
}

.modal-header h2 {
  margin: 0;
  font-size: 20px;
  color: #ffffff;
  font-weight: 700;
}

.close-btn {
  background: none;
  border: none;
  font-size: 24px;
  color: #fff;
  cursor: pointer;
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: background 0.2s;
}

.close-btn:hover {
  background: rgba(255, 255, 255, 0.1);
}

.modal-body {
  padding: 24px;
  max-height: calc(90vh - 140px);
  overflow-y: auto;
}

.detail-section {
  margin-bottom: 20px;
}

/* Export Column Item */
.export-column-item {
  margin-bottom: 12px;
  padding: 12px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background: #fafafa;
  transition: all 0.2s;
}

.export-column-item:hover {
  background: #f3f4f6;
  border-color: #d1d5db;
}

.check-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 0;
  cursor: pointer;
  user-select: none;
  font-size: 15px;
  font-weight: 600;
  color: #374151;
}

.check-item input[type="checkbox"] {
  width: 18px;
  height: 18px;
  cursor: pointer;
}

.filter-input-wrapper {
  margin-top: 10px;
  padding-left: 28px;
  animation: slideDown 0.2s ease;
}

@keyframes slideDown {
  from {
    opacity: 0;
    max-height: 0;
  }
  to {
    opacity: 1;
    max-height: 100px;
  }
}

.filter-input {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
  transition: all 0.2s;
}

.filter-input:focus {
  outline: none;
  border-color: #42b883;
  box-shadow: 0 0 0 3px rgba(66, 184, 131, 0.1);
}

.autocomplete-wrapper {
  position: relative;
}

.dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: #fff;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  margin-top: 4px;
  max-height: 200px;
  overflow-y: auto;
  z-index: 100;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.dropdown-item {
  padding: 10px 12px;
  cursor: pointer;
  transition: background 0.15s;
  font-size: 14px;
}

.dropdown-item:hover {
  background: #f3f4f6;
}

.info-box {
  background: #eff6ff;
  border-left: 4px solid #3b82f6;
  padding: 12px 16px;
  border-radius: 6px;
  font-size: 14px;
  color: #1e40af;
}

.modal-footer {
  padding: 16px 24px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  background: #f9fafb;
}

.ghost,
.approve {
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 15px;
}

.ghost {
  background: #e5e7eb;
  color: #374151;
}

.ghost:hover {
  background: #d1d5db;
}

.approve {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: #fff;
}

.approve:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.approve:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .statistics-page {
    padding: 12px;
  }

  .panel-header h1 {
    font-size: 20px;
  }

  .summary-cards {
    grid-template-columns: 1fr;
  }

  .charts-row {
    grid-template-columns: 1fr;
  }

  .stat-card {
    padding: 16px;
  }
}
</style>
