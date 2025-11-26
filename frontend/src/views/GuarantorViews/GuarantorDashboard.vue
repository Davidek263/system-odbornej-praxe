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
          <h1>Správa odborných praxí - Garant</h1>

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
            <label>Firma:</label>
            <select v-model="filters.company" @change="applyFilters">
              <option value="">Všetky firmy</option>
              <option v-for="company in companies" :key="company" :value="company">{{ company }}</option>
            </select>
          </div>

          <div class="filter-group">
            <label>Študijný odbor:</label>
            <select v-model="filters.studyField" @change="applyFilters">
              <option value="">Všetky odbory</option>
              <option v-for="field in studyFields" :key="field" :value="field">{{ field }}</option>
            </select>
          </div>

          <div class="filter-group">
            <label>Študent:</label>
            <input 
              type="text" 
              v-model="filters.student" 
              @input="applyFilters"
              placeholder="Meno študenta..."
            />
          </div>

          <button @click="clearFilters" class="clear-filters-btn">
            Vymazať filtre
          </button>
        </section>

        <!-- Statistics -->
        <section class="stats-bar" v-if="!loading">
          <div class="stat">
            <span class="stat-label">Vytvorené</span>
            <span class="stat-value pending">{{ stats.created }}</span>
          </div>
          <div class="stat">
            <span class="stat-label">Potvrdené</span>
            <span class="stat-value confirmed">{{ stats.confirmed }}</span>
          </div>
          <div class="stat">
            <span class="stat-label">Schválené</span>
            <span class="stat-value approved">{{ stats.approved }}</span>
          </div>
          <div class="stat">
            <span class="stat-label">Obhájené</span>
            <span class="stat-value success">{{ stats.defended }}</span>
          </div>
          <div class="stat">
            <span class="stat-label">Neobhájené</span>
            <span class="stat-value failed">{{ stats.failed }}</span>
          </div>
          <div class="stat">
            <span class="stat-label">Zamietnuté</span>
            <span class="stat-value denied">{{ stats.rejected }}</span>
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
                <th class="sortable" @click="toggleSort('company')">
                  <div class="th-content">
                    <span>Firma</span>
                    <span class="sort-indicator" v-if="sortColumn === 'company'">
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
                    <span>Termín praxe</span>
                    <span class="sort-indicator" v-if="sortColumn === 'dateStart'">
                      {{ sortDirection === 'asc' ? '↑' : '↓' }}
                    </span>
                  </div>
                </th>
                <th class="sortable" @click="toggleSort('status')">
                  <div class="th-content">
                    <span>Stav</span>
                    <span class="sort-indicator" v-if="sortColumn === 'status'">
                      {{ sortDirection === 'asc' ? '↑' : '↓' }}
                    </span>
                  </div>
                </th>
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
                  <div>{{ internship.company?.company_name || '—' }}</div>
                  <div class="muted">{{ internship.company?.city || '' }}</div>
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
                <td class="actions-col">
                  <button class="ghost" @click="viewDetails(internship)">Detail</button>
                  <button class="edit" @click="editInternship(internship)">Upraviť</button>
                  <button 
                    class="status" 
                    @click="changeStatus(internship)"
                    :disabled="processing[internship.id]"
                  >
                    Zmeniť stav
                  </button>
                </td>
              </tr>
            </tbody>
          </table>

          <div v-if="!loading && !internships.length" class="empty">
            <p>Žiadne odborné praxe nenájdené.</p>
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
              <option :value="100">100</option>
            </select>
          </div>
        </footer>
      </div>
    </div>

    <!-- Detail Modal -->
    <div v-if="selected" class="modal-backdrop" @click.self="closeDetailModal">
      <div class="modal-card">
        <header class="modal-header">
          <h2>Detail odbornej praxe</h2>
          <button class="close" @click="closeDetailModal">&times;</button>
        </header>

        <div class="modal-body">
          <div class="detail-section">
            <h3>Študent</h3>
            <p><strong>Meno:</strong> {{ selected.student.first_name }} {{ selected.student.last_name }}</p>
            <p><strong>Email:</strong> {{ selected.student.student_email || selected.student.email }}</p>
            <p v-if="selected.student.phone_number"><strong>Telefón:</strong> {{ selected.student.phone_number }}</p>
            <p><strong>Študijný odbor:</strong> {{ selected.student.study_field?.study_field_name || '—' }}</p>
          </div>

          <div class="detail-section">
            <h3>Firma</h3>
            <p><strong>Názov:</strong> {{ selected.company?.company_name || '—' }}</p>
            <p v-if="selected.company?.city"><strong>Mesto:</strong> {{ selected.company.city }}</p>
            <p v-if="selected.company?.email"><strong>Email:</strong> {{ selected.company.email }}</p>
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
        </div>

        <footer class="modal-footer">
          <button class="ghost" @click="closeDetailModal">Zavrieť</button>
          <button class="edit" @click="editInternship(selected)">Upraviť</button>
          <button class="status" @click="changeStatus(selected)">Zmeniť stav</button>
        </footer>
      </div>
    </div>

    <!-- Edit Modal -->
    <div v-if="editMode" class="modal-backdrop" @click.self="closeEditModal">
      <div class="modal-card">
        <header class="modal-header">
          <h2>Upraviť odbornú prax</h2>
          <button class="close" @click="closeEditModal">&times;</button>
        </header>

        <div class="modal-body">
          <form @submit.prevent="saveInternship" class="edit-form">
            <div class="form-group">
              <label>Študent *</label>
              <select v-model="editForm.student_id" required>
                <option value="">Vyberte študenta</option>
                <option v-for="student in allStudents" :key="student.id" :value="student.id">
                  {{ student.first_name }} {{ student.last_name }} 
                  ({{ student.student_email || student.email }})
                  <template v-if="student.study_field">
                    - {{ student.study_field.abbreviation }}
                  </template>
                </option>
              </select>
            </div>

            <div class="form-group">
              <label>Firma *</label>
              <select v-model="editForm.company_id" required>
                <option value="">Vyberte firmu</option>
                <option v-for="company in allCompanies" :key="company.id" :value="company.id">
                  {{ company.company_name }}
                  <template v-if="company.address">
                    ({{ company.address.city }})
                  </template>
                </option>
              </select>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Akademický rok *</label>
                <input type="text" v-model="editForm.academic_year" required placeholder="2024/2025" />
              </div>

              <div class="form-group">
                <label>Semester *</label>
                <select v-model="editForm.semester" required>
                  <option value="">Vyberte semester</option>
                  <option :value="1">Zimný semester</option>
                  <option :value="2">Letný semester</option>
                </select>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Dátum začiatku *</label>
                <input type="date" v-model="editForm.date_start" required />
              </div>

              <div class="form-group">
                <label>Dátum konca *</label>
                <input type="date" v-model="editForm.date_end" required />
              </div>
            </div>
          </form>
        </div>

        <footer class="modal-footer">
          <button class="ghost" @click="closeEditModal">Zrušiť</button>
          <button 
            class="approve" 
            @click="saveInternship" 
            :disabled="processing.edit"
          >
            {{ processing.edit ? 'Ukladám...' : 'Uložiť zmeny' }}
          </button>
        </footer>
      </div>
    </div>

    <!-- Change Status Modal -->
    <div v-if="statusMode" class="modal-backdrop" @click.self="closeStatusModal">
      <div class="modal-card modal-small">
        <header class="modal-header">
          <h2>Zmeniť stav praxe</h2>
          <button class="close" @click="closeStatusModal">&times;</button>
        </header>

        <div class="modal-body">
          <form @submit.prevent="saveStatus" class="edit-form">
            <div class="form-group">
              <label>Aktuálny stav</label>
              <p class="current-status">
                <span :class="statusBadge(statusForm.currentStatus)">
                  {{ statusForm.currentStatus }}
                </span>
              </p>
            </div>

            <div class="form-group">
              <label>Nový stav *</label>
              <select v-model="statusForm.new_status" required>
                <option value="">Vyberte nový stav</option>
                <option 
                  v-for="status in statuses" 
                >
                  {{ status }}
                </option>
              </select>
            </div>

            <div class="form-group">
              <label>Poznámka</label>
              <textarea 
                v-model="statusForm.notes" 
                rows="3"
                placeholder="Voliteľná poznámka ku zmene stavu..."
              ></textarea>
            </div>

            <div class="info-box">
              <strong>ℹ️ Upozornenie:</strong> 
              Po zmene stavu bude automaticky odoslaná emailová notifikácia študentovi aj firme.
            </div>
          </form>
        </div>

        <footer class="modal-footer">
          <button class="ghost" @click="closeStatusModal">Zrušiť</button>
          <button 
            class="approve" 
            @click="saveStatus" 
            :disabled="processing.status || !statusForm.new_status"
          >
            {{ processing.status ? 'Ukladám...' : 'Zmeniť stav' }}
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

function showAlert(message, type = 'error', duration = 5000) {
  alert.message = message
  alert.type = type
  alert.duration = duration
  alert.show = true
}

// State
const internships = ref([])
const allStudents = ref([])
const allCompanies = ref([])
const loading = ref(false)
const processing = reactive({ edit: false, status: false })
const selected = ref(null)
const editMode = ref(false)
const statusMode = ref(false)
const showAdvancedFilters = ref(false)

// Forms
const editForm = reactive({
  id: null,
  student_id: '',
  company_id: '',
  academic_year: '',
  semester: '',
  date_start: '',
  date_end: ''
})

const statusForm = reactive({
  internship_id: null,
  currentStatus: '',
  new_status: '',
  notes: ''
})

// Filters & Sorting
const filters = reactive({
  search: '',
  status: '',
  academicYear: '',
  company: '',
  studyField: '',
  student: ''
})

const sortColumn = ref('')
const sortDirection = ref('asc')
const pageSize = ref(10)
const currentPage = ref(1)

const statuses = [
  'Vytvorená',
  'Potvrdená',
  'Schválená',
  'Obhájená',
  'Neobhájená',
  'Zamietnutá'
]

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

function toggleAdvancedFilters() {
  showAdvancedFilters.value = !showAdvancedFilters.value
}

function clearFilters() {
  Object.keys(filters).forEach(key => {
    filters[key] = ''
  })
  currentPage.value = 1
}

function toggleSort(column) {
  if (sortColumn.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortColumn.value = column
    sortDirection.value = 'asc'
  }
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

const filteredInternships = computed(() => {
  let result = internships.value

  // Fulltext search
  if (filters.search) {
    const searchLower = filters.search.toLowerCase()
    result = result.filter(i => {
      const studentName = `${i.student.first_name} ${i.student.last_name}`.toLowerCase()
      const studentEmail = (i.student.student_email || i.student.email || '').toLowerCase()
      const companyName = (i.company?.company_name || '').toLowerCase()
      const studyField = (i.student.study_field?.study_field_name || '').toLowerCase()
      const academicYear = (i.academic_year || '').toLowerCase()
      const status = (i.current_status?.internship_status_name || '').toLowerCase()
      
      return studentName.includes(searchLower) ||
             studentEmail.includes(searchLower) ||
             companyName.includes(searchLower) ||
             studyField.includes(searchLower) ||
             academicYear.includes(searchLower) ||
             status.includes(searchLower)
    })
  }

  // Apply filters
  if (filters.status) {
    result = result.filter(i => i.current_status?.internship_status_name === filters.status)
  }

  if (filters.academicYear) {
    result = result.filter(i => i.academic_year === filters.academicYear)
  }

  if (filters.company) {
    result = result.filter(i => i.company?.company_name === filters.company)
  }

  if (filters.studyField) {
    result = result.filter(i => i.student.study_field?.study_field_name === filters.studyField)
  }

  if (filters.student) {
    const studentLower = filters.student.toLowerCase()
    result = result.filter(i => {
      const name = `${i.student.first_name} ${i.student.last_name}`.toLowerCase()
      return name.includes(studentLower)
    })
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
        case 'company':
          aVal = (a.company?.company_name || '').toLowerCase()
          bVal = (b.company?.company_name || '').toLowerCase()
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
  const created = filteredInternships.value.filter(i => i.current_status?.internship_status_name === 'Vytvorená').length
  const confirmed = filteredInternships.value.filter(i => i.current_status?.internship_status_name === 'Potvrdená').length
  const approved = filteredInternships.value.filter(i => i.current_status?.internship_status_name === 'Schválená').length
  const defended = filteredInternships.value.filter(i => i.current_status?.internship_status_name === 'Obhájená').length
  const failed = filteredInternships.value.filter(i => i.current_status?.internship_status_name === 'Neobhájená').length
  const rejected = filteredInternships.value.filter(i => i.current_status?.internship_status_name === 'Zamietnutá').length

  return { created, confirmed, approved, defended, failed, rejected }
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
    const response = await api.get('/guarantor/internships')
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

async function fetchStudents() {
  try {
    const response = await api.get('/guarantor/students')
    allStudents.value = response.data.students || []
  } catch (err) {
    console.error('Failed to fetch students:', err)
    showAlert('Nepodarilo sa načítať študentov.', 'error', 3000)
  }
}

async function fetchCompanies() {
  try {
    const response = await api.get('/guarantor/companies')
    allCompanies.value = response.data.companies || []
  } catch (err) {
    console.error('Failed to fetch companies:', err)
    showAlert('Nepodarilo sa načítať firmy.', 'error', 3000)
  }
}

function applyFilters() {
  currentPage.value = 1
}

function viewDetails(internship) {
  selected.value = internship
}

function closeDetailModal() {
  selected.value = null
}

// Edit internship
function editInternship(internship) {
  editForm.id = internship.id
  editForm.student_id = internship.student.id || internship.users_id
  editForm.company_id = internship.company?.id || ''
  editForm.academic_year = internship.academic_year || ''
  editForm.semester = internship.semester || ''
  
  // Správne formátovanie dátumov pre HTML input type="date"
  editForm.date_start = internship.date_start ? new Date(internship.date_start).toISOString().split('T')[0] : ''
  editForm.date_end = internship.date_end ? new Date(internship.date_end).toISOString().split('T')[0] : ''
  
  editMode.value = true
  selected.value = null
}

function closeEditModal() {
  editMode.value = false
  Object.keys(editForm).forEach(key => {
    editForm[key] = key === 'id' ? null : ''
  })
}

async function saveInternship() {
  if (!editForm.student_id || !editForm.company_id || !editForm.academic_year || 
      !editForm.semester || !editForm.date_start || !editForm.date_end) {
    showAlert('Prosím vyplňte všetky povinné polia.', 'error')
    return
  }

  processing.edit = true
  
  try {
    await api.put(`/guarantor/internships/${editForm.id}`, {
      users_id: editForm.student_id,
      company_id: editForm.company_id,
      academic_year: editForm.academic_year,
      semester: parseInt(editForm.semester),
      date_start: editForm.date_start,
      date_end: editForm.date_end
    })

    showAlert('Prax bola úspešne aktualizovaná.', 'success')
    await fetchInternships()
    closeEditModal()
  } catch (err) {
    showAlert(err.response?.data?.message || 'Nepodarilo sa uložiť zmeny.', 'error')
  } finally {
    processing.edit = false
  }
}

// Change status
function changeStatus(internship) {
  statusForm.internship_id = internship.id
  statusForm.currentStatus = internship.current_status?.internship_status_name || ''
  statusForm.new_status = ''
  statusForm.notes = ''
  
  statusMode.value = true
  selected.value = null
}

function closeStatusModal() {
  statusMode.value = false
  statusForm.internship_id = null
  statusForm.currentStatus = ''
  statusForm.new_status = ''
  statusForm.notes = ''
}

async function saveStatus() {
  if (!statusForm.new_status) {
    showAlert('Prosím vyberte nový stav.', 'error')
    return
  }

  if (!confirm(`Naozaj chcete zmeniť stav z "${statusForm.currentStatus}" na "${statusForm.new_status}"?\n\nŠtudent aj firma dostanú emailovú notifikáciu.`)) {
    return
  }

  processing.status = true
  
  try {
    await api.post(`/guarantor/internships/${statusForm.internship_id}/change-status`, {
      status: statusForm.new_status,
      notes: statusForm.notes || `Zmena stavu garantom: ${statusForm.currentStatus} → ${statusForm.new_status}`
    })

    showAlert('Stav praxe bol úspešne zmenený. Notifikácie boli odoslané.', 'success')
    await fetchInternships()
    closeStatusModal()
  } catch (err) {
    showAlert(err.response?.data?.message || 'Nepodarilo sa zmeniť stav.', 'error')
  } finally {
    processing.status = false
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
  fetchStudents()
  fetchCompanies()
})
</script>

<style scoped>
html, body, .dashboard-page {
  height: 100%;
  margin: 0;
  background: linear-gradient(135deg, #ffb74d 0%, #ff8a65 100%);
  font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
  color: #2c3e50;
}

.dashboard-container {
  display: flex;
  justify-content: center;
  align-items: flex-start;
  min-height: calc(100vh - 80px);
  padding: 28px 20px;
}

.panel-card {
  width: 100%;
  max-width: 1400px;
  background: #fff;
  padding: 24px;
  border-radius: 14px;
  box-shadow: 0 10px 30px rgba(12, 38, 52, 0.12);
  animation: fadeIn 0.45s ease;
}

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.panel-header h1 {
  font-size: 22px;
  margin: 0;
  color: #2c3e50;
}

.actions {
  display: flex;
  gap: 10px;
  align-items: center;
  flex-wrap: wrap;
}

.search-input {
  padding: 10px 16px;
  border-radius: 8px;
  border: 2px solid #e6e9ee;
  font-size: 14px;
  min-width: 300px;
  transition: all 0.2s;
}

.search-input:focus {
  outline: none;
  border-color: #42b883;
  box-shadow: 0 0 0 3px rgba(66, 184, 131, 0.1);
}

.refresh-btn,
.filter-btn {
  padding: 10px 16px;
  border-radius: 8px;
  border: none;
  font-weight: 600;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.3s;
}

.refresh-btn {
  background: #42b883;
  color: #fff;
}

.refresh-btn:hover:not(:disabled) {
  background: #369f73;
  transform: translateY(-1px);
}

.filter-btn {
  background: #607d9b;
  color: #fff;
}

.filter-btn.active {
  background: #4a6280;
}

.filter-btn:hover {
  background: #4a6280;
  transform: translateY(-1px);
}

.advanced-filters {
  background: #f8f9fa;
  border-radius: 10px;
  padding: 20px;
  margin-bottom: 20px;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 15px;
  animation: slideDown 0.3s ease;
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

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.filter-group label {
  font-size: 12px;
  font-weight: 600;
  color: #4b5563;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.filter-group select,
.filter-group input[type="text"],
.filter-group input[type="date"] {
  padding: 8px 12px;
  border-radius: 6px;
  border: 1px solid #d1d5db;
  font-size: 14px;
  transition: border-color 0.2s;
}

.filter-group select:focus,
.filter-group input:focus {
  outline: none;
  border-color: #42b883;
}

.clear-filters-btn {
  padding: 8px 16px;
  background: #ef4444;
  color: white;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
  align-self: end;
}

.clear-filters-btn:hover {
  background: #dc2626;
}

.stats-bar {
  display: flex;
  gap: 16px;
  margin-bottom: 20px;
  padding: 16px;
  background: #f8f9fa;
  border-radius: 10px;
  flex-wrap: wrap;
}

.stat {
  display: flex;
  flex-direction: column;
  align-items: center;
  flex: 1;
  min-width: 100px;
}

.stat-label {
  font-size: 12px;
  color: #6b7280;
  font-weight: 600;
  text-transform: uppercase;
  margin-bottom: 6px;
}

.stat-value {
  font-size: 24px;
  font-weight: 700;
}

.stat-value.pending {
  color: #a17516;
}

.stat-value.confirmed {
  color: #2563eb; 
}

.stat-value.approved {
  color: #047857;
}

.stat-value.success {
  color: #16a34a; 
}

.stat-value.failed {
  color: #dc2626; 
}

.stat-value.denied {
  color: #dc2626;
}

.stat-value.total {
  color: #3b82f6;
}

.table-wrap {
  margin-top: 8px;
  overflow-x: auto;
}

.applications-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.applications-table thead th {
  text-align: left;
  padding: 12px 10px;
  color: #4b5563;
  font-weight: 700;
  border-bottom: 2px solid #eef2f6;
  background: #f9fafb;
  white-space: nowrap;
}

.applications-table thead th.sortable {
  cursor: pointer;
  user-select: none;
  transition: background 0.2s;
}

.applications-table thead th.sortable:hover {
  background: #f3f4f6;
}

.th-content {
  display: flex;
  align-items: center;
  gap: 8px;
  justify-content: space-between;
}

.sort-indicator {
  font-size: 16px;
  color: #42b883;
  font-weight: bold;
}

.applications-table tbody td {
  padding: 14px 10px;
  border-bottom: 1px solid #f4f7fa;
  vertical-align: middle;
}

.applications-table tbody tr:hover {
  background: #f9fafb;
}

.name { 
  font-weight: 700;
  color: #1f2937;
}

.muted { 
  color: #6b7280; 
  font-size: 13px; 
  margin-top: 4px; 
}

.badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 999px;
  font-weight: 700;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.badge-pending { 
  background: #fef3c7; 
  color: #78350f; 
  border: 1px solid #fde047; 
}

.badge-confirmed { 
  background: #dbeafe; 
  color: #1e3a8a; 
  border: 1px solid #93c5fd; 
}

.badge-approved { 
  background: #ccfbf1; 
  color: #134e4a; 
  border: 1px solid #5eead4; 
}

.badge-success { 
  background: #dcfce7; 
  color: #14532d; 
  border: 1px solid #86efac; 
}

.badge-failed { 
  background: #fee2e2; 
  color: #7f1d1d; 
  border: 1px solid #fca5a5; 
}

.badge-rejected { 
  background: #fecaca; 
  color: #7f1d1d; 
  border: 1px solid #ef4444; 
}

.actions-col {
  display: flex;
  gap: 8px;
  justify-content: flex-end;
  min-width: 280px;
  flex-wrap: wrap;
}

button.ghost {
  background: transparent;
  border: 1px solid #d1d5db;
  padding: 8px 12px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 13px;
  color: #4b5563;
  font-weight: 600;
  transition: all 0.2s;
}

button.ghost:hover {
  background: #f3f4f6;
  border-color: #9ca3af;
}

button.edit {
  background: #3b82f6;
  color: #fff;
  border: none;
  padding: 8px 12px;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
  font-size: 13px;
}

button.edit:hover:not(:disabled) {
  background: #2563eb;
}

button.status {
  background: #8b5cf6;
  color: #fff;
  border: none;
  padding: 8px 12px;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
  font-size: 13px;
}

button.status:hover:not(:disabled) {
  background: #7c3aed;
}

button.approve {
  background: #10b981;
  color: #fff;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
  font-size: 14px;
}

button.approve:hover:not(:disabled) {
  background: #059669;
}

button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.empty {
  padding: 60px 20px;
  text-align: center;
  color: #6b7280;
}

.empty p {
  margin: 8px 0;
}

.clear-btn {
  margin-top: 16px;
  padding: 10px 20px;
  background: #42b883;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
}

.clear-btn:hover {
  background: #369f73;
}

.loading {
  padding: 60px 20px;
  text-align: center;
  color: #6b7280;
}

.spinner {
  width: 40px;
  height: 40px;
  margin: 0 auto 16px;
  border: 4px solid #e5e7eb;
  border-top-color: #42b883;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.panel-footer {
  margin-top: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
}

.pagination {
  display: flex;
  gap: 8px;
  align-items: center;
}

.pagination button {
  padding: 8px 14px;
  border-radius: 6px;
  border: none;
  background: #2c3e50;
  color: #fff;
  cursor: pointer;
  font-size: 13px;
  font-weight: 600;
  transition: background 0.2s;
}

.pagination button:hover:not(:disabled) {
  background: #1a252f;
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

.modal-card.modal-small {
  max-width: 600px;
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

/* Edit form styles */
.edit-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group label {
  font-size: 13px;
  font-weight: 600;
  color: #374151;
}

.form-group input,
.form-group select,
.form-group textarea {
  padding: 10px 14px;
  border-radius: 6px;
  border: 1px solid #d1d5db;
  font-size: 14px;
  transition: border-color 0.2s;
  font-family: inherit;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #42b883;
  box-shadow: 0 0 0 3px rgba(66, 184, 131, 0.1);
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.current-status {
  margin: 4px 0;
}

.info-box {
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  border-radius: 8px;
  padding: 12px 16px;
  font-size: 13px;
  color: #1e40af;
  line-height: 1.5;
}

@media (max-width: 768px) {
  .panel-header {
    flex-direction: column;
    align-items: stretch;
  }

  .actions {
    flex-direction: column;
    width: 100%;
  }

  .search-input,
  .actions button {
    width: 100%;
    min-width: auto;
  }

  .advanced-filters {
    grid-template-columns: 1fr;
  }

  .stats-bar {
    flex-wrap: wrap;
  }

  .actions-col {
    min-width: auto;
    flex-wrap: wrap;
  }

  .modal-card {
    max-width: 95%;
  }

  .panel-footer {
    flex-direction: column;
  }

  .pagination {
    flex-wrap: wrap;
    justify-content: center;
  }

  .form-row {
    grid-template-columns: 1fr;
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