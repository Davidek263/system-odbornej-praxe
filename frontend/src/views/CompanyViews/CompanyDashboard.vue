<template>
  <div class="dashboard-page">
    <PageAlert
      v-if="alert.show"
      :type="alert.type"
      :message="alert.message"
      :duration="alert.duration"
      @close="alert.show = false"
      dismissible
    />

    <div class="dashboard-container">
      <div class="panel-card">
        <header class="panel-header">
          <h1>Správa odborných praxí</h1>

          <div class="actions">
            <input
              v-model="filters.search"
              @input="applyFilters"
              placeholder="Vyhľadávanie..."
              :disabled="loading"
              class="search-input"
            />
            <button @click="fetchInternships" :disabled="loading" class="refresh-btn">
              {{ loading ? 'Načítavam...' : 'Aktualizovať' }}
            </button>
            <button @click="toggleAdvancedFilters" class="filter-btn" :class="{ active: showAdvancedFilters }">
              Filtre {{ showAdvancedFilters ? '▲' : '▼' }}
            </button>
          </div>
        </header>

        <!-- Advanced Filters -->
        <section v-if="showAdvancedFilters" class="advanced-filters">
          <div class="filter-group">
            <label>Stav:</label>
            <select v-model="filters.status" @change="applyFilters">
              <option value="">Všetky stavy</option>
              <option value="Vytvorená">Vytvorená</option>
              <option value="Potvrdená">Potvrdená</option>
              <option value="Schválená">Schválená</option>
              <option value="Obhájená">Obhájená</option>
              <option value="Neobhájená">Neobhájená</option>
              <option value="Zamietnutá">Zamietnutá</option>
            </select>
          </div>

          <div class="filter-group">
            <label>Akademický rok:</label>
            <select v-model="filters.academicYear" @change="applyFilters">
              <option value="">Všetky roky</option>
              <option v-for="year in academicYears" :key="year" :value="year">{{ year }}</option>
            </select>
          </div>

          <div class="filter-group">
            <label>Semester:</label>
            <select v-model="filters.semester" @change="applyFilters">
              <option value="">Všetky semestre</option>
              <option value="1">Zimný semester</option>
              <option value="2">Letný semester</option>
            </select>
          </div>

          <div class="filter-group">
            <label>Študijný odbor:</label>
            <select v-model="filters.studyField" @change="applyFilters">
              <option value="">Všetky odbory</option>
              <option v-for="field in studyFields" :key="field" :value="field">{{ field }}</option>
            </select>
          </div>

          <button @click="clearFilters" class="clear-filters-btn">
            Vymazať filtre
          </button>
        </section>

        <!-- Statistics -->
        <section class="stats-bar" v-if="!loading">
          <div class="stat">
            <span class="stat-label">Čakajúce</span>
            <span class="stat-value pending">{{ stats.pending }}</span>
          </div>
          <div class="stat">
            <span class="stat-label">Potvrdené</span>
            <span class="stat-value approved">{{ stats.confirmed }}</span>
          </div>
          <div class="stat">
            <span class="stat-label">Schválené</span>
            <span class="stat-value approved">{{ stats.approved }}</span>
          </div>
          <div class="stat">
            <span class="stat-label">Zamietnuté</span>
            <span class="stat-value rejected">{{ stats.rejected }}</span>
          </div>
          <div class="stat">
            <span class="stat-label">Celkom</span>
            <span class="stat-value total">{{ filteredInternships.length }}</span>
          </div>
        </section>

        <!-- Table -->
        <section class="table-wrap">
          <table class="applications-table" v-if="!loading && internships.length">
            <thead>
              <tr>
                <th class="sortable" @click="toggleSort('student')">
                  <div class="th-content">
                    <span>Študent</span>
                    <span class="sort-indicator" v-if="sortColumn === 'student'">
                      {{ sortDirection === 'asc' ? '↑' : '↓' }}
                    </span>
                  </div>
                </th>
                <th class="sortable" @click="toggleSort('studyField')">
                  <div class="th-content">
                    <span>Študijný odbor</span>
                    <span class="sort-indicator" v-if="sortColumn === 'studyField'">
                      {{ sortDirection === 'asc' ? '↑' : '↓' }}
                    </span>
                  </div>
                </th>
                <th class="sortable" @click="toggleSort('academicYear')">
                  <div class="th-content">
                    <span>Akademický rok</span>
                    <span class="sort-indicator" v-if="sortColumn === 'academicYear'">
                      {{ sortDirection === 'asc' ? '↑' : '↓' }}
                    </span>
                  </div>
                </th>
                <th class="sortable" @click="toggleSort('dateStart')">
                  <div class="th-content">
                    <span>Termín</span>
                    <span class="sort-indicator" v-if="sortColumn === 'dateStart'">
                      {{ sortDirection === 'asc' ? '↑' : '↓' }}
                    </span>
                  </div>
                </th>
                <th class="sortable" @click="toggleSort('status')">
                  <div class="th-content">
                    <span>Stav praxe</span>
                    <span class="sort-indicator" v-if="sortColumn === 'status'">
                      {{ sortDirection === 'asc' ? '↑' : '↓' }}
                    </span>
                  </div>
                </th>
                <th>Stav výkazu</th>
                <th class="actions-col">Akcie</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="internship in paginated" :key="internship.id">
                <td>
                  <div class="name">{{ internship.student.first_name }} {{ internship.student.last_name }}</div>
                  <div class="muted">{{ internship.student.student_email || internship.student.email }}</div>
                </td>
                <td>
                  <div>{{ internship.student.study_field?.study_field_name || '—' }}</div>
                  <div class="muted">{{ internship.student.study_field?.abbreviation || '' }}</div>
                </td>
                <td>
                  <div>{{ internship.academic_year }}</div>
                  <div class="muted">{{ getSemesterText(internship.semester) }}</div>
                </td>
                <td>
                  <div>{{ formatDate(internship.date_start) }}</div>
                  <div class="muted">{{ formatDate(internship.date_end) }}</div>
                </td>
                <td>
                  <span :class="statusBadge(internship.current_status?.internship_status_name)">
                    {{ internship.current_status?.internship_status_name || 'Neznámy' }}
                  </span>
                </td>
                <td>
                  <div v-if="hasTimesheet(internship)" class="timesheet-status">
                    <span :class="timesheetBadge(internship)">
                      {{ getTimesheetStatus(internship) }}
                    </span>
                    <div v-if="!isInternshipConfirmed(internship)" class="warning-note">
                      ⚠️ Najprv potvrďte prax
                    </div>
                  </div>
                  <div v-else class="muted">Bez výkazu</div>
                </td>
                <td class="actions-col">
                  <div class="actions-wrapper">
                    <!-- Step 1: Internship Actions -->
                    <div class="action-group internship-actions">
                      <div class="action-label">Prax:</div>
                      <button class="ghost-small" @click="viewDetails(internship)">Detail</button>
                      
                      <button
                        v-if="canConfirm(internship)"
                        class="approve"
                        @click="confirmInternship(internship)"
                        :disabled="processing[internship.id]"
                        title="Potvrdiť prax"
                      >
                        ✓ Potvrdiť
                      </button>
                      <button
                        v-if="canReject(internship)"
                        class="reject"
                        @click="rejectInternship(internship)"
                        :disabled="processing[internship.id]"
                        title="Zamietnuť prax"
                      >
                        ✗ Zamietnuť
                      </button>

                      <span v-if="isInternshipConfirmed(internship) && !canConfirm(internship)" class="status-confirmed">
                        ✓ Potvrdené
                      </span>
                    </div>
                    
                    <!-- Step 2: Timesheet Actions -->
                    <div 
                      class="action-group timesheet-actions" 
                      :class="{ disabled: !isInternshipConfirmed(internship) }"
                    >
                      <div class="action-label">Výkaz:</div>
                      
                      <template v-if="isInternshipConfirmed(internship)">
                        <button
                          v-if="canApproveTimesheet(internship)"
                          class="approve-small"
                          @click="approveTimesheet(internship)"
                          :disabled="processing[internship.id]"
                          title="Schváliť výkaz"
                        >
                          ✓ Schváliť
                        </button>
                        <button
                          v-if="canRejectTimesheet(internship)"
                          class="reject-small"
                          @click="rejectTimesheet(internship)"
                          :disabled="processing[internship.id]"
                          title="Zamietnuť výkaz"
                        >
                          ✗ Zamietnuť
                        </button>
                        
                        <span v-if="!hasTimesheet(internship)" class="muted-small">
                          Žiadny výkaz
                        </span>
                        <span v-else-if="getTimesheetStatus(internship) === 'Potvrdený'" class="status-confirmed-small">
                          ✓ Schválený
                        </span>
                      </template>
                      
                      <template v-else>
                        <span class="disabled-note" title="Najprv potvrďte prax">
                          🔒 Uzamknuté
                        </span>
                      </template>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <div v-if="!loading && !internships.length" class="empty">
            <p>Žiadne odborné praxe nenájdené.</p>
            <p class="muted">Praxe sa zobrazia po tom, čo ich študenti vytvoria a priradia k vašej firme.</p>
          </div>

          <div v-if="!loading && internships.length && !filteredInternships.length" class="empty">
            <p>Žiadne výsledky pre zadané filtre.</p>
            <button @click="clearFilters" class="clear-btn">Vymazať filtre</button>
          </div>

          <div v-if="loading" class="loading">
            <div class="spinner"></div>
            <p>Načítavam odborné praxe...</p>
          </div>
        </section>

        <!-- Pagination -->
        <footer class="panel-footer" v-if="totalPages > 1">
          <div class="pagination">
            <button @click="changePage(1)" :disabled="currentPage === 1">Prvá</button>
            <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1">Predchádzajúca</button>
            <span class="page-info">
              Strana {{ currentPage }} / {{ totalPages }} 
              <span class="muted">({{ filteredInternships.length }} záznamov)</span>
            </span>
            <button @click="changePage(currentPage + 1)" :disabled="currentPage === totalPages">Ďalšia</button>
            <button @click="changePage(totalPages)" :disabled="currentPage === totalPages">Posledná</button>
          </div>
          <div class="page-size-selector">
            <label>Počet na stránku:</label>
            <select v-model="pageSize" @change="currentPage = 1">
              <option :value="5">5</option>
              <option :value="10">10</option>
              <option :value="20">20</option>
              <option :value="50">50</option>
            </select>
          </div>
        </footer>
      </div>
    </div>

    <!-- Detail Modal -->
    <div v-if="selected" class="modal-backdrop" @click.self="closeModal">
      <div class="modal-card">
        <header class="modal-header">
          <h2>Detail odbornej praxe</h2>
          <button class="close" @click="closeModal">&times;</button>
        </header>

        <div class="modal-body">
          <div class="detail-section">
            <h3>Študent</h3>
            <p><strong>Meno:</strong> {{ selected.student.first_name }} {{ selected.student.last_name }}</p>
            <p><strong>Email:</strong> {{ selected.student.student_email || selected.student.email }}</p>
            <p v-if="selected.student.phone_number"><strong>Telefón:</strong> {{ selected.student.phone_number }}</p>
            <p><strong>Študijný odbor:</strong> {{ selected.student.study_field?.study_field_name || '—' }}</p>
            <p v-if="selected.student.address">
              <strong>Adresa:</strong> 
              {{ selected.student.address.street }} {{ selected.student.address.street_number }}, 
              {{ selected.student.address.postal_code }} {{ selected.student.address.city }}
            </p>
          </div>

          <div class="detail-section">
            <h3>Informácie o praxi</h3>
            <p><strong>Akademický rok:</strong> {{ selected.academic_year }}</p>
            <p><strong>Semester:</strong> {{ getSemesterText(selected.semester) }}</p>
            <p><strong>Začiatok:</strong> {{ formatDate(selected.date_start) }}</p>
            <p><strong>Koniec:</strong> {{ formatDate(selected.date_end) }}</p>
            <p><strong>Trvanie:</strong> {{ calculateDuration(selected.date_start, selected.date_end) }}</p>
          </div>

          <div class="detail-section">
            <h3>Stav praxe</h3>
            <p>
              <strong>Aktuálny stav:</strong> 
              <span :class="statusBadge(selected.current_status?.internship_status_name)">
                {{ selected.current_status?.internship_status_name || 'Neznámy' }}
              </span>
            </p>
            
            <div v-if="selected.status_history && selected.status_history.length" class="status-history">
              <strong>História zmien stavu:</strong>
              <ul>
                <li v-for="change in selected.status_history" :key="change.id">
                  <span class="history-date">{{ formatDateTime(change.status_changed_at) }}</span>
                  <span :class="statusBadge(change.status.internship_status_name)">
                    {{ change.status.internship_status_name }}
                  </span>
                  <span v-if="change.notes" class="history-notes">{{ change.notes }}</span>
                </li>
              </ul>
            </div>
          </div>

          <div class="detail-section" v-if="selected.documents && selected.documents.length">
            <h3>Dokumenty</h3>
            <ul class="documents-list">
              <li v-for="doc in selected.documents" :key="doc.id">
                <a :href="doc.file_path" target="_blank" rel="noopener" class="document-link">
                  {{ doc.document_name }}
                </a>
                <span class="document-type">({{ doc.document_type?.document_type_name }})</span>
                <span v-if="doc.is_verified" class="verified-badge">Overené</span>
              </li>
            </ul>
          </div>
          <div class="detail-section" v-else>
            <h3>Dokumenty</h3>
            <p class="muted">Žiadne dokumenty neboli nahrané.</p>
          </div>
        </div>

        <footer class="modal-footer">
          <button class="ghost" @click="closeModal">Zavrieť</button>
          <button 
            v-if="canConfirm(selected)" 
            class="approve" 
            @click="confirmInternship(selected)" 
            :disabled="processing[selected.id]"
          >
            Potvrdiť prax
          </button>
          <button 
            v-if="canReject(selected)" 
            class="reject" 
            @click="rejectInternship(selected)" 
            :disabled="processing[selected.id]"
          >
            Zamietnuť prax
          </button>
        </footer>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue'
import api from '@/api'
import PageAlert from '@/components/PageAlert.vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const alert = reactive({ show: false, type: 'error', message: '', duration: 5000 })

function showAlert(message, type = 'error') {
  alert.message = message
  alert.type = type
  alert.show = true
}

// State
const internships = ref([])
const loading = ref(false)
const processing = reactive({})
const selected = ref(null)
const showAdvancedFilters = ref(false)

// Filters & Sorting
const filters = reactive({
  search: '',
  status: '',
  academicYear: '',
  semester: '',
  studyField: '',
})

const sortColumn = ref('')
const sortDirection = ref('asc')
const pageSize = ref(10)
const currentPage = ref(1)

// Helper functions
function formatDate(d) {
  if (!d) return '—'
  const dt = new Date(d)
  if (isNaN(dt)) return d
  return dt.toLocaleDateString('sk-SK', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

function formatDateTime(d) {
  if (!d) return '—'
  const dt = new Date(d)
  if (isNaN(dt)) return d
  return dt.toLocaleString('sk-SK', { 
    day: '2-digit', 
    month: '2-digit', 
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function getSemesterText(semester) {
  return semester === 1 ? 'Zimný semester' : 'Letný semester'
}

function calculateDuration(start, end) {
  if (!start || !end) return '—'
  const startDate = new Date(start)
  const endDate = new Date(end)
  const days = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24))
  const weeks = Math.floor(days / 7)
  return `${days} dní (${weeks} týždňov)`
}

function statusBadge(statusName) {
  const badges = {
    'Vytvorená': 'badge badge-pending',
    'Potvrdená': 'badge badge-confirmed',
    'Schválená': 'badge badge-approved',
    'Obhájená': 'badge badge-success',
    'Neobhájená': 'badge badge-failed',
    'Zamietnutá': 'badge badge-rejected',
  }
  return badges[statusName] || 'badge'
}

// Check if internship is confirmed
function isInternshipConfirmed(internship) {
  const status = internship.current_status?.internship_status_name
  return status && status !== 'Vytvorená' && status !== 'Zamietnutá'
}

function canConfirm(internship) {
  return internship.current_status?.internship_status_name === 'Vytvorená'
}

function canReject(internship) {
  return internship.current_status?.internship_status_name === 'Vytvorená'
}

// Timesheet helpers
function hasTimesheet(internship) {
  if (!internship.documents || !Array.isArray(internship.documents)) return false
  return internship.documents.some(doc => 
    doc.document_type?.document_type_name === 'Výkaz hodín'
  )
}

function getTimesheet(internship) {
  if (!internship.documents || !Array.isArray(internship.documents)) return null
  return internship.documents.find(doc => 
    doc.document_type?.document_type_name === 'Výkaz hodín'
  )
}

function getTimesheetStatus(internship) {
  const timesheet = getTimesheet(internship)
  if (!timesheet) return 'Bez výkazu'
  
  // Check timesheet_status_history for latest status (ordered by desc in backend)
  if (timesheet.timesheet_status_history && timesheet.timesheet_status_history.length > 0) {
    const latestStatus = timesheet.timesheet_status_history[0]
    return latestStatus.status?.timesheet_status_name || 'Nahraný'
  }
  
  // Fallback to is_verified flag
  return timesheet.is_verified ? 'Potvrdený' : 'Nahraný'
}

function timesheetBadge(internship) {
  const status = getTimesheetStatus(internship)
  const badgeMap = {
    'Bez výkazu': 'badge muted',
    'Nahraný': 'badge pending',
    'Potvrdený': 'badge approved',
    'Zamietnutý': 'badge rejected'
  }
  return badgeMap[status] || 'badge'
}

function canApproveTimesheet(internship) {
  if (!isInternshipConfirmed(internship)) return false
  
  const timesheet = getTimesheet(internship)
  if (!timesheet) return false
  
  const status = getTimesheetStatus(internship)
  return status === 'Nahraný' || status === 'Zamietnutý'
}

function canRejectTimesheet(internship) {
  if (!isInternshipConfirmed(internship)) return false
  
  const timesheet = getTimesheet(internship)
  if (!timesheet) return false
  
  const status = getTimesheetStatus(internship)
  return status === 'Nahraný' || status === 'Potvrdený'
}

function toggleAdvancedFilters() {
  showAdvancedFilters.value = !showAdvancedFilters.value
}

function toggleSort(column) {
  if (sortColumn.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortColumn.value = column
    sortDirection.value = 'asc'
  }
}

function clearFilters() {
  filters.search = ''
  filters.status = ''
  filters.academicYear = ''
  filters.semester = ''
  filters.studyField = ''
  currentPage.value = 1
}

// Computed
const academicYears = computed(() => {
  const years = new Set()
  internships.value.forEach(i => {
    if (i.academic_year) years.add(i.academic_year)
  })
  return Array.from(years).sort().reverse()
})

const studyFields = computed(() => {
  const fields = new Set()
  internships.value.forEach(i => {
    const fieldName = i.student?.study_field?.study_field_name
    if (fieldName) fields.add(fieldName)
  })
  return Array.from(fields).sort()
})

const filteredInternships = computed(() => {
  let result = internships.value

  if (filters.search) {
    const searchLower = filters.search.toLowerCase()
    result = result.filter(i => {
      const studentName = `${i.student.first_name} ${i.student.last_name}`.toLowerCase()
      const studentEmail = (i.student.student_email || i.student.email || '').toLowerCase()
      const studyField = (i.student.study_field?.study_field_name || '').toLowerCase()
      const academicYear = (i.academic_year || '').toLowerCase()
      const status = (i.current_status?.internship_status_name || '').toLowerCase()
      
      return studentName.includes(searchLower) ||
             studentEmail.includes(searchLower) ||
             studyField.includes(searchLower) ||
             academicYear.includes(searchLower) ||
             status.includes(searchLower)
    })
  }

  if (filters.status) {
    result = result.filter(i => i.current_status?.internship_status_name === filters.status)
  }

  if (filters.academicYear) {
    result = result.filter(i => i.academic_year === filters.academicYear)
  }

  if (filters.semester) {
    result = result.filter(i => i.semester === parseInt(filters.semester))
  }

  if (filters.studyField) {
    result = result.filter(i => i.student.study_field?.study_field_name === filters.studyField)
  }

  // Sorting
  if (sortColumn.value) {
    result = [...result].sort((a, b) => {
      let aVal, bVal

      switch (sortColumn.value) {
        case 'student':
          aVal = `${a.student.first_name} ${a.student.last_name}`.toLowerCase()
          bVal = `${b.student.first_name} ${b.student.last_name}`.toLowerCase()
          break
        case 'studyField':
          aVal = (a.student.study_field?.study_field_name || '').toLowerCase()
          bVal = (b.student.study_field?.study_field_name || '').toLowerCase()
          break
        case 'academicYear':
          aVal = a.academic_year
          bVal = b.academic_year
          break
        case 'dateStart':
          aVal = new Date(a.date_start)
          bVal = new Date(b.date_start)
          break
        case 'status':
          aVal = (a.current_status?.internship_status_name || '').toLowerCase()
          bVal = (b.current_status?.internship_status_name || '').toLowerCase()
          break
        default:
          return 0
      }

      if (aVal < bVal) return sortDirection.value === 'asc' ? -1 : 1
      if (aVal > bVal) return sortDirection.value === 'asc' ? 1 : -1
      return 0
    })
  }

  return result
})

const stats = computed(() => {
  const pending = filteredInternships.value.filter(i => i.current_status?.internship_status_name === 'Vytvorená').length
  const confirmed = filteredInternships.value.filter(i => i.current_status?.internship_status_name === 'Potvrdená').length
  const approved = filteredInternships.value.filter(i => i.current_status?.internship_status_name === 'Schválená').length
  const rejected = filteredInternships.value.filter(i => i.current_status?.internship_status_name === 'Zamietnutá').length

  return { pending, confirmed, approved, rejected }
})

const totalPages = computed(() => Math.max(1, Math.ceil(filteredInternships.value.length / pageSize.value)))

const paginated = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value
  return filteredInternships.value.slice(start, start + pageSize.value)
})

// API calls
async function fetchInternships() {
  loading.value = true
  try {
    const userStr = localStorage.getItem('user')
    const user = userStr ? JSON.parse(userStr) : null
    
    if (!user) {
      showAlert('Neautorizovaný prístup.', 'error')
      setTimeout(() => router.push('/login'), 1500)
      return
    }
    
    if (!user.company) {
      showAlert('Používateľ nie je priradený k žiadnej firme.', 'error')
      return
    }
    
    const response = await api.get(`/company-internships/${user.company.id}`)
    internships.value = response.data.internships || []
    currentPage.value = 1
    
  } catch (err) {
    showAlert(err.response?.data?.message || 'Nepodarilo sa načítať praxe.', 'error')
    
    if (err.response?.status === 401) {
      setTimeout(() => {
        localStorage.removeItem('token')
        localStorage.removeItem('user')
        router.push('/login')
      }, 2000)
    }
  } finally {
    loading.value = false
  }
}

function applyFilters() {
  currentPage.value = 1
}

function viewDetails(internship) {
  selected.value = internship
}

function closeModal() {
  selected.value = null
}

// Actions
async function confirmInternship(internship) {
  if (!confirm(`Potvrdiť odbornú prax pre študenta ${internship.student.first_name} ${internship.student.last_name}?`)) {
    return
  }

  processing[internship.id] = true
  
  try {
    await api.post(`/internships/${internship.id}/confirm`, {
      notes: 'Potvrdené firmou'
    })

    showAlert('Prax bola úspešne potvrdená.', 'success')
    await fetchInternships()
    
    if (selected.value && selected.value.id === internship.id) {
      closeModal()
    }
  } catch (err) {
    showAlert(err.response?.data?.message || 'Nepodarilo sa potvrdiť prax.', 'error')
  } finally {
    processing[internship.id] = false
  }
}

async function rejectInternship(internship) {
  const reason = prompt('Zadajte dôvod zamietnutia (voliteľné):')
  
  if (reason === null) return
  
  if (!confirm(`Zamietnuť odbornú prax pre študenta ${internship.student.first_name} ${internship.student.last_name}?`)) {
    return
  }

  processing[internship.id] = true
  
  try {
    await api.post(`/internships/${internship.id}/reject`, {
      notes: reason || 'Zamietnuté firmou'
    })

    showAlert('Prax bola úspešne zamietnutá.', 'success')
    await fetchInternships()
    
    if (selected.value && selected.value.id === internship.id) {
      closeModal()
    }
  } catch (err) {
    showAlert(err.response?.data?.message || 'Nepodarilo sa zamietnuť prax.', 'error')
  } finally {
    processing[internship.id] = false
  }
}

// Timesheet approval/rejection using DocumentController endpoints
async function approveTimesheet(internship) {
  const timesheet = getTimesheet(internship)
  if (!timesheet) {
    showAlert('Výkaz nebol nájdený.', 'error')
    return
  }
  
  if (!isInternshipConfirmed(internship)) {
    showAlert('Najprv musíte potvrdiť odbornú prax pred schválením výkazu.', 'error')
    return
  }
  
  if (!confirm(`Schváliť výkaz hodín pre študenta ${internship.student.first_name} ${internship.student.last_name}?`)) {
    return
  }

  processing[internship.id] = true
  
  try {
    await api.post(`/documents/${timesheet.id}/approve-timesheet`, {
      notes: 'Schválené firmou'
    })

    showAlert('Výkaz hodín bol úspešne schválený.', 'success')
    await fetchInternships()
    
    if (selected.value && selected.value.id === internship.id) {
      closeModal()
    }
  } catch (err) {
    showAlert(err.response?.data?.message || 'Nepodarilo sa schváliť výkaz hodín.', 'error')
  } finally {
    processing[internship.id] = false
  }
}

async function rejectTimesheet(internship) {
  const timesheet = getTimesheet(internship)
  if (!timesheet) {
    showAlert('Výkaz nebol nájdený.', 'error')
    return
  }
  
  if (!isInternshipConfirmed(internship)) {
    showAlert('Najprv musíte potvrdiť odbornú prax pred zamietnutím výkazu.', 'error')
    return
  }
  
  const reason = prompt('Zadajte dôvod zamietnutia výkazu (voliteľné):')
  
  if (reason === null) return
  
  if (!confirm(`Zamietnuť výkaz hodín pre študenta ${internship.student.first_name} ${internship.student.last_name}?`)) {
    return
  }

  processing[internship.id] = true
  
  try {
    await api.post(`/documents/${timesheet.id}/reject-timesheet`, {
      notes: reason || 'Zamietnuté firmou'
    })

    showAlert('Výkaz hodín bol zamietnutý.', 'success')
    await fetchInternships()
    
    if (selected.value && selected.value.id === internship.id) {
      closeModal()
    }
  } catch (err) {
    showAlert(err.response?.data?.message || 'Nepodarilo sa zamietnuť výkaz hodín.', 'error')
  } finally {
    processing[internship.id] = false
  }
}

// Pagination
function changePage(n) {
  if (n < 1 || n > totalPages.value) return
  currentPage.value = n
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// Mount
onMounted(() => {
  fetchInternships()
})
</script>

<style scoped>
* {
  box-sizing: border-box;
}

.dashboard-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #76cbec 0%, #607d9b 100%);
  padding: 24px;
  font-family: 'Inter', 'Segoe UI', sans-serif;
}

@media (max-width: 768px) {
  .dashboard-page {
    padding: 12px;
  }
}

.dashboard-container {
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
  letter-spacing: -0.5px;
}

.actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  align-items: center;
}

.search-input {
  padding: 10px 14px;
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 8px;
  font-size: 14px;
  min-width: 280px;
  background: rgba(255, 255, 255, 0.95);
  transition: all 0.2s;
}

.search-input:focus {
  outline: none;
  background: #fff;
  border-color: #42b883;
  box-shadow: 0 0 0 3px rgba(66, 184, 131, 0.2);
}

.refresh-btn,
.filter-btn {
  padding: 10px 18px;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.refresh-btn {
  background: #42b883;
  color: white;
}

.refresh-btn:hover:not(:disabled) {
  background: #369f73;
  transform: translateY(-1px);
}

.filter-btn {
  background: rgba(255, 255, 255, 0.2);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.filter-btn.active,
.filter-btn:hover {
  background: rgba(255, 255, 255, 0.3);
}

.refresh-btn:disabled,
.filter-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Advanced Filters */
.advanced-filters {
  background: #f8f9fa;
  padding: 20px;
  border-bottom: 1px solid #e5e7eb;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  animation: slideDown 0.3s ease;
}

@keyframes slideDown {
  from {
    opacity: 0;
    max-height: 0;
  }
  to {
    opacity: 1;
    max-height: 500px;
  }
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.filter-group label {
  font-size: 13px;
  font-weight: 600;
  color: #4b5563;
}

.filter-group select {
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
  background: white;
  cursor: pointer;
  transition: border 0.2s;
}

.filter-group select:focus {
  outline: none;
  border-color: #42b883;
  box-shadow: 0 0 0 2px rgba(66, 184, 131, 0.2);
}

.clear-filters-btn {
  grid-column: 1 / -1;
  padding: 10px 16px;
  background: #e74c3c;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
  max-width: 200px;
}

.clear-filters-btn:hover {
  background: #c0392b;
}

/* Statistics Bar */
.stats-bar {
  display: flex;
  justify-content: space-around;
  padding: 20px;
  background: #fafbfc;
  border-bottom: 1px solid #e5e7eb;
}

.stat {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
}

.stat-label {
  font-size: 13px;
  color: #6b7280;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stat-value {
  font-size: 28px;
  font-weight: 700;
}

.stat-value.pending {
  color: #f59e0b;
}

.stat-value.approved {
  color: #10b981;
}

.stat-value.rejected {
  color: #ef4444;
}

.stat-value.total {
  color: #3b82f6;
}

/* Table */
.table-wrap {
  overflow-x: auto;
  padding: 20px;
}

.applications-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.applications-table thead {
  background: #f9fafb;
  border-bottom: 2px solid #e5e7eb;
}

.applications-table th {
  padding: 14px 12px;
  text-align: left;
  font-weight: 700;
  color: #374151;
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.applications-table th.sortable {
  cursor: pointer;
  user-select: none;
  transition: background 0.2s;
}

.applications-table th.sortable:hover {
  background: #f3f4f6;
}

.th-content {
  display: flex;
  align-items: center;
  gap: 6px;
}

.sort-indicator {
  font-size: 12px;
  color: #42b883;
}

.applications-table tbody tr {
  border-bottom: 1px solid #e5e7eb;
  transition: background 0.15s;
}

.applications-table tbody tr:hover {
  background: #f9fafb;
}

.applications-table td {
  padding: 16px 12px;
  color: #1f2937;
}

.name {
  font-weight: 600;
  color: #1f2937;
}

.muted {
  font-size: 12px;
  color: #9ca3af;
  margin-top: 2px;
}

.muted-small {
  font-size: 11px;
  color: #9ca3af;
  font-style: italic;
}

.timesheet-status {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.warning-note {
  font-size: 11px;
  color: #f59e0b;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 4px;
}

/* Badge styles */
.badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.badge.badge-pending {
  background: #fef3c7;
  color: #92400e;
}

.badge.badge-confirmed {
  background: #dbeafe;
  color: #1e40af;
}

.badge.badge-approved {
  background: #d1fae5;
  color: #065f46;
}

.badge.badge-success {
  background: #d1fae5;
  color: #065f46;
}

.badge.badge-failed {
  background: #fee2e2;
  color: #991b1b;
}

.badge.badge-rejected {
  background: #fee2e2;
  color: #991b1b;
}

.badge.muted {
  background: #f3f4f6;
  color: #6b7280;
}

.badge.pending {
  background: #fef3c7;
  color: #92400e;
}

.badge.approved {
  background: #d1fae5;
  color: #065f46;
}

.badge.rejected {
  background: #fee2e2;
  color: #991b1b;
}

/* Actions column */
.actions-col {
  min-width: 280px;
}

.actions-wrapper {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.action-group {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
  padding: 8px;
  background: #f9fafb;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
}

.action-group.internship-actions {
  border-left: 3px solid #3b82f6;
}

.action-group.timesheet-actions {
  border-left: 3px solid #8b5cf6;
}

.action-group.disabled {
  opacity: 0.5;
  background: #f3f4f6;
}

.action-label {
  font-size: 11px;
  font-weight: 700;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  min-width: 60px;
}

.status-confirmed {
  font-size: 11px;
  color: #059669;
  font-weight: 600;
  padding: 4px 8px;
  background: #d1fae5;
  border-radius: 4px;
}

.status-confirmed-small {
  font-size: 10px;
  color: #059669;
  font-weight: 600;
  padding: 3px 6px;
  background: #d1fae5;
  border-radius: 4px;
}

.disabled-note {
  font-size: 11px;
  color: #6b7280;
  font-weight: 600;
  padding: 4px 8px;
  background: #f3f4f6;
  border-radius: 4px;
  cursor: not-allowed;
}

/* Button styles */
button {
  padding: 8px 14px;
  border: none;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}

button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

button.ghost,
button.ghost-small {
  background: transparent;
  color: #3b82f6;
  border: 1px solid #3b82f6;
}

button.ghost:hover:not(:disabled),
button.ghost-small:hover:not(:disabled) {
  background: #eff6ff;
}

button.ghost-small {
  padding: 6px 10px;
  font-size: 12px;
}

button.approve {
  background: #10b981;
  color: white;
}

button.approve:hover:not(:disabled) {
  background: #059669;
  transform: translateY(-1px);
}

button.reject {
  background: #ef4444;
  color: white;
}

button.reject:hover:not(:disabled) {
  background: #dc2626;
  transform: translateY(-1px);
}

button.approve-small {
  background: #10b981;
  color: white;
  padding: 6px 10px;
  font-size: 12px;
}

button.approve-small:hover:not(:disabled) {
  background: #059669;
}

button.reject-small {
  background: #ef4444;
  color: white;
  padding: 6px 10px;
  font-size: 12px;
}

button.reject-small:hover:not(:disabled) {
  background: #dc2626;
}

/* Empty state */
.empty {
  text-align: center;
  padding: 60px 20px;
  color: #6b7280;
}

.empty p {
  font-size: 16px;
  margin: 8px 0;
}

.empty .muted {
  font-size: 14px;
}

.clear-btn {
  margin-top: 16px;
  padding: 10px 20px;
  background: #42b883;
  color: white;
}

.clear-btn:hover {
  background: #369f73;
}

/* Loading */
.loading {
  text-align: center;
  padding: 60px 20px;
}

.spinner {
  width: 48px;
  height: 48px;
  border: 4px solid #e5e7eb;
  border-top-color: #42b883;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 16px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.loading p {
  color: #6b7280;
  font-size: 14px;
}

/* Pagination */
.panel-footer {
  border-top: 1px solid #e5e7eb;
  padding: 16px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #fafbfc;
}

.pagination {
  display: flex;
  gap: 8px;
  align-items: center;
}

.pagination button {
  padding: 8px 14px;
  background: #fff;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 600;
  color: #374151;
  cursor: pointer;
  transition: background 0.2s;
}

.pagination button:hover:not(:disabled) {
  background: #1a252f;
  color: white;
}

.pagination button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  font-size: 14px;
  color: #4b5563;
  font-weight: 600;
  padding: 0 12px;
}

.page-size-selector {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
}

.page-size-selector label {
  color: #6b7280;
  font-weight: 600;
}

.page-size-selector select {
  padding: 6px 10px;
  border-radius: 6px;
  border: 1px solid #d1d5db;
  font-size: 14px;
  cursor: pointer;
}

/* Modal styles */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 24px;
  z-index: 1000;
  overflow-y: auto;
}

.modal-card {
  width: 100%;
  max-width: 800px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
  overflow: hidden;
  animation: modalFadeIn 0.3s ease;
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
  background: #f9fafb;
}

.modal-header h2 { 
  margin: 0; 
  font-size: 20px;
  color: #1f2937;
}

.modal-body {
  padding: 24px;
  font-size: 14px;
  color: #374151;
  max-height: 60vh;
  overflow-y: auto;
}

.detail-section {
  margin-bottom: 24px;
  padding-bottom: 20px;
  border-bottom: 1px solid #e5e7eb;
}

.detail-section:last-child {
  border-bottom: none;
  margin-bottom: 0;
  padding-bottom: 0;
}

.detail-section h3 {
  font-size: 16px;
  margin: 0 0 12px 0;
  color: #1f2937;
  font-weight: 700;
}

.detail-section p {
  margin: 8px 0;
  line-height: 1.6;
}

.status-history {
  margin-top: 12px;
}

.status-history ul {
  list-style: none;
  padding: 0;
  margin: 8px 0 0 0;
}

.status-history li {
  padding: 8px 12px;
  background: #f9fafb;
  border-radius: 6px;
  margin-bottom: 8px;
  font-size: 13px;
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.history-date {
  color: #6b7280;
  font-weight: 600;
}

.history-notes {
  display: block;
  width: 100%;
  margin-top: 4px;
  color: #4b5563;
  font-style: italic;
}

.documents-list {
  list-style: none;
  padding: 0;
  margin: 8px 0 0 0;
}

.documents-list li {
  padding: 10px 12px;
  background: #f9fafb;
  border-radius: 6px;
  margin-bottom: 8px;
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.document-link {
  color: #2563eb;
  text-decoration: none;
  font-weight: 600;
  flex: 1;
}

.document-link:hover {
  text-decoration: underline;
}

.document-type {
  color: #6b7280;
  font-size: 12px;
}

.verified-badge {
  background: #d1fae5;
  color: #065f46;
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 700;
}

.modal-footer {
  border-top: 1px solid #e5e7eb;
  gap: 10px;
  padding: 16px 24px;
  display: flex;
  justify-content: flex-end;
  background: #f9fafb;
}

button.close {
  background: transparent;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #6b7280;
  line-height: 1;
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  transition: background 0.2s;
}

button.close:hover {
  background: #e5e7eb;
}

/* Tablet Responsiveness (768px - 1024px) */
@media (max-width: 1024px) and (min-width: 769px) {
  .dashboard-container {
    max-width: 100%;
    padding: 0 16px;
  }

  .panel-header h1 {
    font-size: 24px;
  }

  .search-input {
    min-width: 220px;
  }

  .applications-table {
    font-size: 13px;
  }

  .applications-table th,
  .applications-table td {
    padding: 12px 10px;
  }

  .actions-col {
    min-width: 260px;
  }

  .stat-value {
    font-size: 24px;
  }
}

/* Mobile Responsiveness (max-width: 768px) */
@media (max-width: 768px) {
  .dashboard-page {
    padding: 8px;
  }

  .dashboard-container {
    max-width: 100%;
  }

  .panel-card {
    border-radius: 12px;
  }

  .panel-header {
    flex-direction: column;
    align-items: stretch;
    padding: 16px;
    gap: 12px;
  }

  .panel-header h1 {
    font-size: 20px;
    text-align: center;
  }

  .actions {
    flex-direction: column;
    width: 100%;
    gap: 8px;
  }

  .search-input {
    width: 100%;
    min-width: auto;
    font-size: 16px; /* Prevents zoom on iOS */
    padding: 12px 14px;
  }

  .refresh-btn,
  .filter-btn {
    width: 100%;
    padding: 12px 18px;
    font-size: 15px;
  }

  .advanced-filters {
    grid-template-columns: 1fr;
    padding: 16px;
    gap: 12px;
  }

  .filter-group select,
  .filter-group input {
    font-size: 16px; /* Prevents zoom on iOS */
    padding: 10px 12px;
  }

  .clear-filters-btn {
    max-width: none;
    width: 100%;
  }

  .stats-bar {
    flex-wrap: wrap;
    padding: 12px;
    gap: 8px;
    justify-content: center;
  }

  .stat {
    flex: 1;
    min-width: calc(50% - 4px);
    padding: 12px 8px;
  }

  .stat-label {
    font-size: 11px;
  }

  .stat-value {
    font-size: 24px;
  }

  /* Table optimizations for mobile */
  .table-wrap {
    padding: 8px;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch; /* Smooth scrolling on iOS */
  }

  .applications-table {
    font-size: 11px;
    min-width: 800px; /* Ensures table doesn't collapse */
  }

  .applications-table th,
  .applications-table td {
    padding: 8px 6px;
  }

  .applications-table th {
    font-size: 10px;
    position: sticky;
    top: 0;
    background: #f9fafb;
    z-index: 1;
  }

  .name {
    font-size: 12px;
  }

  .muted {
    font-size: 10px;
  }

  /* Actions column for mobile */
  .actions-col {
    min-width: auto;
    width: 240px;
  }

  .actions-wrapper {
    width: 100%;
    gap: 8px;
  }

  .action-group {
    flex-direction: column;
    align-items: stretch;
    padding: 6px 8px;
    gap: 6px;
  }

  .action-label {
    width: 100%;
    margin-bottom: 2px;
    font-size: 10px;
  }

  .action-group button {
    width: 100%;
    justify-content: center;
    padding: 8px 10px;
    font-size: 12px;
  }

  button.ghost-small {
    padding: 7px 8px;
    font-size: 11px;
  }

  button.approve-small,
  button.reject-small {
    padding: 7px 8px;
    font-size: 11px;
  }

  .status-confirmed {
    font-size: 10px;
    padding: 4px 6px;
    text-align: center;
  }

  .status-confirmed-small {
    font-size: 9px;
    padding: 3px 5px;
  }

  .disabled-note {
    font-size: 10px;
    padding: 4px 6px;
    text-align: center;
  }

  .badge {
    font-size: 9px;
    padding: 3px 6px;
  }

  .warning-note {
    font-size: 10px;
  }

  /* Empty state */
  .empty {
    padding: 40px 16px;
  }

  .empty p {
    font-size: 14px;
  }

  /* Loading */
  .loading {
    padding: 40px 16px;
  }

  .spinner {
    width: 40px;
    height: 40px;
    border-width: 3px;
  }

  /* Modal optimizations for mobile */
  .modal-backdrop {
    padding: 0;
    align-items: flex-start;
  }

  .modal-card {
    max-width: 100%;
    min-height: 100vh;
    border-radius: 0;
    margin: 0;
  }

  .modal-header {
    padding: 14px 16px;
    position: sticky;
    top: 0;
    z-index: 10;
    background: #f9fafb;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .modal-header h2 {
    font-size: 18px;
  }

  button.close {
    width: 36px;
    height: 36px;
    font-size: 28px;
  }

  .modal-body {
    padding: 16px;
    max-height: none;
    font-size: 13px;
  }

  .detail-section {
    margin-bottom: 20px;
    padding-bottom: 16px;
  }

  .detail-section h3 {
    font-size: 15px;
    margin-bottom: 10px;
  }

  .detail-section p {
    font-size: 13px;
    line-height: 1.5;
  }

  .detail-section strong {
    display: inline-block;
    min-width: 90px;
  }

  .status-history li {
    font-size: 12px;
    padding: 8px 10px;
  }

  .history-date {
    font-size: 11px;
  }

  .history-notes {
    font-size: 12px;
  }

  .documents-list li {
    flex-direction: column;
    align-items: flex-start;
    padding: 10px;
  }

  .document-link {
    font-size: 13px;
    width: 100%;
  }

  .document-type {
    font-size: 11px;
  }

  .verified-badge {
    font-size: 10px;
    padding: 3px 8px;
  }

  .modal-footer {
    padding: 12px 16px;
    position: sticky;
    bottom: 0;
    z-index: 10;
    background: #f9fafb;
    flex-direction: column;
    gap: 8px;
    box-shadow: 0 -2px 4px rgba(0, 0, 0, 0.1);
  }

  .modal-footer button {
    width: 100%;
    padding: 14px 16px;
    font-size: 15px;
    font-weight: 700;
  }

  /* Pagination for mobile */
  .panel-footer {
    flex-direction: column;
    gap: 12px;
    padding: 12px 16px;
  }

  .pagination {
    flex-wrap: wrap;
    justify-content: center;
    gap: 6px;
    width: 100%;
  }

  .pagination button {
    padding: 10px 12px;
    font-size: 12px;
    flex: 0 0 auto;
  }

  .pagination button:first-child,
  .pagination button:last-child {
    display: none; /* Hide "First" and "Last" buttons on mobile */
  }

  .page-info {
    font-size: 13px;
    width: 100%;
    text-align: center;
    order: -1; /* Move to top */
    margin-bottom: 8px;
  }

  .page-info .muted {
    display: block;
    margin-top: 4px;
  }

  .page-size-selector {
    width: 100%;
    justify-content: center;
    font-size: 13px;
  }

  .page-size-selector select {
    font-size: 14px;
    padding: 8px 12px;
  }
}

/* Small mobile devices (max-width: 480px) */
@media (max-width: 480px) {
  .panel-header h1 {
    font-size: 18px;
  }

  .stat {
    min-width: calc(50% - 4px);
  }

  .stat-label {
    font-size: 10px;
  }

  .stat-value {
    font-size: 20px;
  }

  .applications-table {
    min-width: 700px;
    font-size: 10px;
  }

  .applications-table th,
  .applications-table td {
    padding: 6px 4px;
  }

  .actions-col {
    width: 200px;
  }

  .action-group {
    padding: 5px 6px;
  }

  .action-group button {
    font-size: 11px;
    padding: 7px 8px;
  }

  .badge {
    font-size: 8px;
    padding: 2px 5px;
  }

  .modal-body {
    padding: 12px;
  }

  .detail-section h3 {
    font-size: 14px;
  }

  .detail-section p {
    font-size: 12px;
  }

  .detail-section strong {
    min-width: 80px;
    font-size: 11px;
  }
}

/* Landscape mobile optimization */
@media (max-width: 768px) and (orientation: landscape) {
  .modal-card {
    min-height: auto;
  }

  .modal-body {
    max-height: calc(100vh - 140px);
    overflow-y: auto;
  }

  .modal-header,
  .modal-footer {
    position: relative;
  }
}

/* Touch improvements for all mobile devices */
@media (hover: none) and (pointer: coarse) {
  /* Increase tap targets */
  button {
    min-height: 44px;
    padding: 12px 16px;
  }

  .action-group button {
    min-height: 40px;
  }

  /* Remove hover effects */
  .applications-table tbody tr:hover {
    background: transparent;
  }

  .applications-table th.sortable:hover {
    background: #f9fafb;
  }

  button:hover:not(:disabled) {
    transform: none;
  }

  /* Better touch scrolling */
  .table-wrap,
  .modal-body {
    -webkit-overflow-scrolling: touch;
    scroll-behavior: smooth;
  }
}

/* Print styles */
@media print {
  .panel-header,
  .advanced-filters,
  .stats-bar,
  .actions-col,
  .panel-footer,
  .modal-backdrop {
    display: none !important;
  }

  .panel-card {
    box-shadow: none;
    border: 1px solid #e5e7eb;
  }

  .applications-table {
    font-size: 10px;
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