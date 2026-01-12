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
                <th class="sortable" @click="toggleSort('internshipType')">
                  <div class="th-content">
                    <span>Typ</span>
                    <span class="sort-indicator" v-if="sortColumn === 'internshipType'">
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
                <th class="sortable" @click="toggleSort('timesheet')">
                  <div class="th-content">
                    <span>Stav výkazu</span>
                    <span class="sort-indicator" v-if="sortColumn === 'timesheet'">
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
                  <span :class="internship.internship_type === 'prax' ? 'badge badge-success' : 'badge badge-info'">
                    {{ internship.internship_type === 'prax' ? 'Prax' : 'Brigáda' }}
                  </span>
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
                  <div v-if="hasTimesheet(internship)" class="timesheet-info">
                    <span :class="timesheetBadge(internship)">
                      {{ getTimesheetStatus(internship) }}
                    </span>
                  </div>
                  <div v-else class="muted">Bez výkazu</div>
                </td>
                <td class="actions-col">
                  <button class="ghost" @click="viewDetails(internship)">Detail</button>
                  <button class="edit" @click="editInternship(internship)">Upraviť</button>
                  <button class="status" @click="changeStatus(internship)" :disabled="processing.status">Zmeniť stav</button>
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
          <button class="close-btn" @click="closeDetailModal">✕</button>
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
            <p v-if="selected.company?.contact_person_name">
              <strong>Kontaktná osoba:</strong> {{ selected.company.contact_person_name }}
            </p>
            <p v-if="selected.company?.contact_person_email">
              <strong>Email:</strong> {{ selected.company.contact_person_email }}
            </p>
            <p v-if="selected.company?.contact_person_phone">
              <strong>Telefón:</strong> {{ selected.company.contact_person_phone }}
            </p>
            <p v-if="selected.company?.address">
              <strong>Adresa:</strong>
              {{ selected.company.address.street }} {{ selected.company.address.street_number }},
              {{ selected.company.address.postal_code }} {{ selected.company.address.city }}
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

          <div class="detail-section">
            <h3>Dokumenty ({{ selected.documents?.length || 0 }})</h3>

            <div v-if="!selected.documents?.length" class="empty-documents">
              <p>Zatiaľ neboli nahrané žiadne dokumenty.</p>
            </div>

            <div v-else class="documents-grid-detail">
              <div v-for="doc in selected.documents" :key="doc.id" class="document-card-detail">
                <div class="document-icon">
                  <span v-if="doc.file_mime_type?.includes('pdf')">📄</span>
                  <span v-else-if="doc.file_mime_type?.includes('image')">🖼️</span>
                  <span v-else>📎</span>
                </div>
                <div class="document-info">
                  <h4>{{ doc.document_name }}</h4>
                  <p class="document-type-name">{{ doc.document_type?.document_type_name }}</p>
                  <p v-if="doc.description" class="document-description">{{ doc.description }}</p>
                  <div class="document-meta">
                    <span class="upload-date">Nahrané: {{ formatDate(doc.uploaded_at) }}</span>
                    <span class="file-size-badge">{{ formatFileSize(doc.file_size) }}</span>
                    <span v-if="doc.is_verified" class="verified-badge">✓ Overené</span>
                  </div>
                </div>
                <div class="document-actions-detail">
                  <a :href="doc.file_path" target="_blank" class="download-btn-detail" title="Stiahnuť">
                    ⬇ Stiahnuť
                  </a>
                </div>
              </div>
            </div>
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
    <div v-if="editMode" class="modal-overlay" @click.self="closeEditModal">
      <div class="modal-content edit-modal">
        <header class="modal-header">
          <h2>Upraviť odbornú prax</h2>
          <button class="close-btn" @click="closeEditModal">✕</button>
        </header>

        <div class="modal-body">
          <form @submit.prevent="saveInternship" class="edit-form">
            <!-- Student Autocomplete -->
            <div class="form-group">
              <label for="edit_student">Študent *</label>
              <div class="autocomplete-wrapper">
                <input
                  id="edit_student"
                  v-model="editForm.studentSearch"
                  @input="filterEditStudents"
                  @focus="showEditStudentDropdown = true"
                  type="text"
                  placeholder="Začnite písať meno študenta..."
                  autocomplete="off"
                  :required="!editForm.selectedStudent"
                />
                <div v-if="showEditStudentDropdown && filteredEditStudents.length" class="dropdown">
                  <div
                    v-for="student in filteredEditStudents"
                    :key="student.id"
                    @click="selectEditStudent(student)"
                    class="dropdown-item"
                  >
                    <div class="student-name">{{ student.first_name }} {{ student.last_name }}</div>
                    <div class="student-info">
                      {{ student.student_email || student.email }}
                      <span v-if="student.study_field"> • {{ student.study_field.abbreviation }}</span>
                    </div>
                  </div>
                </div>
                <div v-if="showEditStudentDropdown && editForm.studentSearch && !filteredEditStudents.length" class="dropdown">
                  <div class="dropdown-empty">Žiadni študenti nenájdení</div>
                </div>
              </div>
            </div>

            <!-- Company Autocomplete -->
            <div class="form-group">
              <label for="edit_company">Firma *</label>
              <div class="autocomplete-wrapper">
                <input
                  id="edit_company"
                  v-model="editForm.companySearch"
                  @input="filterEditCompanies"
                  @focus="showEditCompanyDropdown = true"
                  type="text"
                  placeholder="Začnite písať názov firmy..."
                  autocomplete="off"
                  :required="!editForm.selectedCompany"
                />
                <div v-if="showEditCompanyDropdown && filteredEditCompanies.length" class="dropdown">
                  <div
                    v-for="company in filteredEditCompanies"
                    :key="company.id"
                    @click="selectEditCompany(company)"
                    class="dropdown-item"
                  >
                    <div class="company-name">{{ company.company_name }}</div>
                    <div class="company-info">
                      {{ company.address?.city || '' }}
                      <span v-if="company.contact_person_email"> • {{ company.contact_person_email }}</span>
                    </div>
                  </div>
                </div>
                <div v-if="showEditCompanyDropdown && editForm.companySearch && !filteredEditCompanies.length" class="dropdown">
                  <div class="dropdown-empty">Žiadne firmy nenájdené</div>
                </div>
              </div>
            </div>

            <!-- Academic Year -->
            <div class="form-group">
              <label for="edit_academic_year">Akademický rok *</label>
              <div class="autocomplete-wrapper">
                <input
                  id="edit_academic_year"
                  v-model="editForm.academic_year"
                  @input="filterEditYears"
                  @focus="showEditYearDropdown = true"
                  type="text"
                  placeholder="Zadajte akademický rok (napr. 2024/2025)..."
                  autocomplete="off"
                  required
                />
                <div v-if="showEditYearDropdown && filteredEditYears.length" class="dropdown">
                  <div
                    v-for="year in filteredEditYears"
                    :key="year"
                    @click="selectEditYear(year)"
                    class="dropdown-item"
                  >
                    {{ year }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Semester -->
            <div class="form-group">
              <label for="edit_semester">Semester *</label>
              <div class="autocomplete-wrapper">
                <input
                  id="edit_semester"
                  v-model="editForm.semesterDisplay"
                  @focus="showEditSemesterDropdown = true"
                  type="text"
                  placeholder="Vyberte semester..."
                  autocomplete="off"
                  readonly
                  required
                  :class="{ 'readonly-input': true }"
                />
                <div v-if="showEditSemesterDropdown" class="dropdown">
                  <div
                    @click="selectEditSemester('1')"
                    class="dropdown-item"
                  >
                    Zimný semester
                  </div>
                  <div
                    @click="selectEditSemester('2')"
                    class="dropdown-item"
                  >
                    Letný semester
                  </div>
                </div>
              </div>
            </div>

            <!-- Internship Type -->
            <div class="form-group">
              <label for="edit_internshipType">Typ *</label>
              <div class="autocomplete-wrapper">
                <input
                  id="edit_internshipType"
                  v-model="editForm.internshipTypeDisplay"
                  @focus="showEditInternshipTypeDropdown = true"
                  type="text"
                  placeholder="Vyberte typ..."
                  autocomplete="off"
                  readonly
                  required
                  :class="{ 'readonly-input': true }"
                />
                <div v-if="showEditInternshipTypeDropdown" class="dropdown">
                  <div
                    @click="selectEditInternshipType('prax')"
                    class="dropdown-item"
                  >
                    Prax
                  </div>
                  <div
                    @click="selectEditInternshipType('brigada')"
                    class="dropdown-item"
                  >
                    Brigáda
                  </div>
                </div>
              </div>
            </div>

            <!-- Date Start -->
            <div class="form-group">
              <label for="edit_date_start">Dátum začiatku *</label>
              <input id="edit_date_start" v-model="editForm.date_start" type="date" required />
            </div>

            <!-- Date End -->
            <div class="form-group">
              <label for="edit_date_end">Dátum konca *</label>
              <input id="edit_date_end" v-model="editForm.date_end" type="date" required />
            </div>
          </form>
        </div>

        <footer class="modal-footer">
          <button class="ghost" @click="closeEditModal">Zrušiť</button>
          <button
            class="submit-btn"
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
          <button class="close-btn" @click="closeStatusModal">✕</button>
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
                  :key="status"
                  :value="status"
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
// ============================================================
// IMPORTS & SETUP
// ============================================================
import { reactive, ref, computed, onMounted, onBeforeUnmount } from 'vue'
import api from '@/api'
import PageAlert from '@/components/PageAlert.vue'
import { useRouter } from 'vue-router'

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
const internships = ref([])
const allStudents = ref([])
const allCompanies = ref([])
const loading = ref(false)
const processing = reactive({ edit: false, status: false })
const selected = ref(null)
const editMode = ref(false)
const statusMode = ref(false)
const showAdvancedFilters = ref(false)

// Dropdown visibility states
const showEditStudentDropdown = ref(false)
const showEditCompanyDropdown = ref(false)
const showEditYearDropdown = ref(false)
const showEditSemesterDropdown = ref(false)
const showEditInternshipTypeDropdown = ref(false)

// ============================================================
// FORM STATE
// ============================================================
const editForm = reactive({
  id: null,
  studentSearch: '',
  selectedStudent: null,
  student_id: '',
  companySearch: '',
  selectedCompany: null,
  company_id: '',
  academic_year: '',
  semester: '',
  semesterDisplay: '',
  internship_type: '',
  internshipTypeDisplay: '',
  date_start: '',
  date_end: ''
})

const statusForm = reactive({
  internship_id: null,
  currentStatus: '',
  new_status: '',
  notes: ''
})

// ============================================================
// FILTERS & SORTING
// ============================================================
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

// Available internship statuses
const statuses = [
  'Vytvorená',
  'Potvrdená',
  'Schválená',
  'Obhájená',
  'Neobhájená',
  'Zamietnutá'
]

// ============================================================
// HELPER FUNCTIONS
// ============================================================
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

function formatFileSize(bytes) {
  if (!bytes) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
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
  
  if (timesheet.timesheet_status_history && timesheet.timesheet_status_history.length > 0) {
    const latestStatus = timesheet.timesheet_status_history[0]
    return latestStatus.status?.timesheet_status_name || 'Nahraný'
  }
  
  return timesheet.is_verified ? 'Potvrdený' : 'Nahraný'
}

function timesheetBadge(internship) {
  const status = getTimesheetStatus(internship)
  const badges = {
    'Bez výkazu': 'badge badge-muted',
    'Nahraný': 'badge badge-info',
    'Potvrdený': 'badge badge-success',
    'Zamietnutý': 'badge badge-rejected',
  }
  return badges[status] || 'badge'
}

// ============================================================
// UI FUNCTIONS
// ============================================================
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

// ============================================================
// COMPUTED PROPERTIES
// ============================================================
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

// Edit Modal Computed
const academicYearsOptions = computed(() => {
  const currentYear = new Date().getFullYear()
  const years = []

  const minYear = 2020
  const maxYear = currentYear

  for (let year = minYear; year <= maxYear; year++) {
    years.push(`${year}/${year + 1}`)
  }

  return years.reverse()
})

const filteredEditStudents = computed(() => {
  if (!editForm.studentSearch) return allStudents.value

  const search = editForm.studentSearch.toLowerCase()
  return allStudents.value.filter(student => {
    const fullName = `${student.first_name} ${student.last_name}`.toLowerCase()
    const email = (student.student_email || student.email || '').toLowerCase()
    const studyField = student.study_field?.abbreviation?.toLowerCase() || ''

    return fullName.includes(search) ||
           email.includes(search) ||
           studyField.includes(search)
  })
})

const filteredEditCompanies = computed(() => {
  if (!editForm.companySearch) return allCompanies.value

  const search = editForm.companySearch.toLowerCase()
  return allCompanies.value.filter(company =>
    company.company_name.toLowerCase().includes(search) ||
    company.address?.city?.toLowerCase().includes(search) ||
    company.contact_person_name?.toLowerCase().includes(search) ||
    company.contact_person_email?.toLowerCase().includes(search)
  )
})

const filteredEditYears = computed(() => {
  if (!editForm.academic_year) return academicYearsOptions.value

  const search = editForm.academic_year.toLowerCase()
  return academicYearsOptions.value.filter(year =>
    year.toLowerCase().includes(search)
  )
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
        case 'internshipType':
          aVal = (a.internship_type || 'prax').toLowerCase()
          bVal = (b.internship_type || 'prax').toLowerCase()
          break
        case 'dateStart':
          aVal = new Date(a.date_start)
          bVal = new Date(b.date_start)
          break
        case 'status':
          aVal = (a.current_status?.internship_status_name || '').toLowerCase()
          bVal = (b.current_status?.internship_status_name || '').toLowerCase()
          break
        case 'timesheet':
          aVal = getTimesheetStatus(a).toLowerCase()
          bVal = getTimesheetStatus(b).toLowerCase()
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

// ============================================================
// API CALLS
// ============================================================
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

// ============================================================
// MODAL FUNCTIONS - DETAIL VIEW
// ============================================================
function viewDetails(internship) {
  selected.value = internship
}

function closeDetailModal() {
  selected.value = null
}

// ============================================================
// MODAL FUNCTIONS - EDIT
// ============================================================
function editInternship(internship) {
  editForm.id = internship.id

  // Populate student
  const student = internship.student
  editForm.studentSearch = student ? `${student.first_name} ${student.last_name}` : ''
  editForm.selectedStudent = student
  editForm.student_id = student?.id || internship.users_id || ''

  // Populate company
  const company = internship.company
  editForm.companySearch = company?.company_name || ''
  editForm.selectedCompany = company
  editForm.company_id = company?.id || ''

  editForm.academic_year = internship.academic_year || ''
  editForm.semester = String(internship.semester) || ''
  editForm.semesterDisplay = internship.semester === 1 ? 'Zimný semester' : internship.semester === 2 ? 'Letný semester' : ''
  editForm.internship_type = internship.internship_type || 'prax'
  editForm.internshipTypeDisplay = internship.internship_type === 'prax' ? 'Prax' : 'Brigáda'

  // Správne formátovanie dátumov pre HTML input type="date"
  editForm.date_start = internship.date_start ? new Date(internship.date_start).toISOString().split('T')[0] : ''
  editForm.date_end = internship.date_end ? new Date(internship.date_end).toISOString().split('T')[0] : ''

  editMode.value = true
  selected.value = null
}

// ============================================================
// DROPDOWN HANDLERS
// ============================================================
function filterEditStudents() {
  showEditStudentDropdown.value = true
  if (editForm.selectedStudent && editForm.studentSearch !== `${editForm.selectedStudent.first_name} ${editForm.selectedStudent.last_name}`) {
    editForm.selectedStudent = null
    editForm.student_id = ''
  }
}

function selectEditStudent(student) {
  editForm.selectedStudent = student
  editForm.studentSearch = `${student.first_name} ${student.last_name}`
  editForm.student_id = student.id
  showEditStudentDropdown.value = false
}

function filterEditCompanies() {
  showEditCompanyDropdown.value = true
  if (editForm.selectedCompany && editForm.companySearch !== editForm.selectedCompany.company_name) {
    editForm.selectedCompany = null
    editForm.company_id = null
  }
}

function selectEditCompany(company) {
  editForm.selectedCompany = company
  editForm.companySearch = company.company_name
  editForm.company_id = company.id
  showEditCompanyDropdown.value = false
}

function filterEditYears() {
  showEditYearDropdown.value = true
}

function selectEditYear(year) {
  editForm.academic_year = year
  showEditYearDropdown.value = false
}

function selectEditSemester(semester) {
  editForm.semester = semester
  editForm.semesterDisplay = semester === '1' ? 'Zimný semester' : 'Letný semester'
  showEditSemesterDropdown.value = false
}

function selectEditInternshipType(type) {
  editForm.internship_type = type
  editForm.internshipTypeDisplay = type === 'prax' ? 'Prax' : 'Brigáda'
  showEditInternshipTypeDropdown.value = false
}

function closeEditModal() {
  editMode.value = false
  showEditStudentDropdown.value = false
  showEditCompanyDropdown.value = false
  showEditYearDropdown.value = false
  showEditSemesterDropdown.value = false
  showEditInternshipTypeDropdown.value = false

  // Reset form
  editForm.id = null
  editForm.studentSearch = ''
  editForm.selectedStudent = null
  editForm.student_id = ''
  editForm.companySearch = ''
  editForm.selectedCompany = null
  editForm.company_id = ''
  editForm.academic_year = ''
  editForm.semester = ''
  editForm.semesterDisplay = ''
  editForm.internship_type = ''
  editForm.internshipTypeDisplay = ''
  editForm.date_start = ''
  editForm.date_end = ''
}

async function saveInternship() {
  // Validate student
  if (!editForm.selectedStudent) {
    showAlert('Musíte vybrať študenta zo zoznamu.', 'error')
    return
  }

  // Validate company
  if (!editForm.selectedCompany) {
    showAlert('Musíte vybrať firmu zo zoznamu.', 'error')
    return
  }

  // Validate academic year format
  if (!editForm.academic_year.match(/^\d{4}\/\d{4}$/)) {
    showAlert('Akademický rok musí byť vo formáte YYYY/YYYY (napr. 2024/2025).', 'error')
    return
  }

  // Validate dates
  if (new Date(editForm.date_start) >= new Date(editForm.date_end)) {
    showAlert('Dátum konca musí byť po dátume začiatku.', 'error')
    return
  }

  processing.edit = true

  try {
    await api.put(`/guarantor/internships/${editForm.id}`, {
      users_id: editForm.student_id,
      company_id: editForm.company_id,
      academic_year: editForm.academic_year,
      semester: parseInt(editForm.semester),
      internship_type: editForm.internship_type,
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

function handleClickOutside(event) {
  const target = event.target
  const clickedWrapper = target.closest('.autocomplete-wrapper')

  // If clicking outside all autocomplete wrappers, close all dropdowns
  if (!clickedWrapper) {
    showEditStudentDropdown.value = false
    showEditCompanyDropdown.value = false
    showEditYearDropdown.value = false
    showEditSemesterDropdown.value = false
    showEditInternshipTypeDropdown.value = false
    return
  }

  // If clicking inside a specific wrapper, close other dropdowns
  const studentInput = document.getElementById('edit_student')
  const companyInput = document.getElementById('edit_company')
  const yearInput = document.getElementById('edit_academic_year')
  const semesterInput = document.getElementById('edit_semester')
  const internshipTypeInput = document.getElementById('edit_internshipType')

  if (clickedWrapper.contains(studentInput)) {
    showEditCompanyDropdown.value = false
    showEditYearDropdown.value = false
    showEditSemesterDropdown.value = false
    showEditInternshipTypeDropdown.value = false
  } else if (clickedWrapper.contains(companyInput)) {
    showEditStudentDropdown.value = false
    showEditYearDropdown.value = false
    showEditSemesterDropdown.value = false
    showEditInternshipTypeDropdown.value = false
  } else if (clickedWrapper.contains(yearInput)) {
    showEditStudentDropdown.value = false
    showEditCompanyDropdown.value = false
    showEditSemesterDropdown.value = false
    showEditInternshipTypeDropdown.value = false
  } else if (clickedWrapper.contains(semesterInput)) {
    showEditStudentDropdown.value = false
    showEditCompanyDropdown.value = false
    showEditYearDropdown.value = false
    showEditInternshipTypeDropdown.value = false
  } else if (clickedWrapper.contains(internshipTypeInput)) {
    showEditStudentDropdown.value = false
    showEditCompanyDropdown.value = false
    showEditYearDropdown.value = false
    showEditSemesterDropdown.value = false
  }
}

// ============================================================
// MODAL FUNCTIONS - STATUS CHANGE
// ============================================================
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

// ============================================================
// PAGINATION
// ============================================================
function changePage(n) {
  if (n < 1 || n > totalPages.value) return
  currentPage.value = n
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// ============================================================
// LIFECYCLE HOOKS
// ============================================================
onMounted(() => {
  fetchInternships()
  fetchStudents()
  fetchCompanies()

  // Add click outside listener for dropdowns
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

.dashboard-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #ffb74d 0%, #ff8a65 100%);
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

.filter-group select,
.filter-group input[type="text"],
.filter-group input[type="date"] {
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
  background: white;
  cursor: pointer;
  transition: border 0.2s;
}

.filter-group select:focus,
.filter-group input:focus {
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

.stat-value.confirmed {
  color: #3b82f6;
}

.stat-value.approved {
  color: #10b981;
}

.stat-value.success {
  color: #10b981;
}

.stat-value.failed {
  color: #ef4444;
}

.stat-value.denied {
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

.timesheet-info {
  display: flex;
  flex-direction: row;
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

.badge.badge-muted {
  background: #f3f4f6;
  color: #6b7280;
}

.badge.badge-info {
  background: #dbeafe;
  color: #1e40af;
}

/* Actions column */
.actions-col {
  display: flex;
  gap: 8px;
  align-items: center;
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

button.ghost {
  background: transparent;
  color: #3b82f6;
  border: 1px solid #3b82f6;
}

button.ghost:hover:not(:disabled) {
  background: #eff6ff;
}

button.edit {
  background: #10b981;
  color: white;
}

button.edit:hover:not(:disabled) {
  background: #059669;
  transform: translateY(-1px);
}

button.status {
  background: #8b5cf6;
  color: white;
}

button.status:hover:not(:disabled) {
  background: #7c3aed;
  transform: translateY(-1px);
}

button.approve {
  background: #10b981;
  color: white;
  padding: 10px 20px;
  font-size: 14px;
}

button.approve:hover:not(:disabled) {
  background: #059669;
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

.modal-card.modal-small {
  max-width: 600px;
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

/* Detail View Document Cards */
.empty-documents {
  text-align: center;
  padding: 40px 20px;
  color: #9ca3af;
  background: #f9fafb;
  border-radius: 8px;
}

.documents-grid-detail {
  display: grid;
  gap: 16px;
  margin-top: 12px;
}

.document-card-detail {
  display: flex;
  gap: 16px;
  padding: 16px;
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  transition: all 0.2s;
}

.document-card-detail:hover {
  border-color: #ff8a65;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.document-icon {
  flex-shrink: 0;
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f3f4f6;
  border-radius: 8px;
  font-size: 24px;
}

.document-info {
  flex: 1;
  min-width: 0;
}

.document-info h4 {
  margin: 0 0 4px 0;
  font-size: 15px;
  font-weight: 700;
  color: #1f2937;
  word-break: break-word;
}

.document-type-name {
  margin: 0 0 4px 0;
  font-size: 13px;
  color: #6b7280;
  font-weight: 600;
}

.document-description {
  margin: 4px 0;
  font-size: 13px;
  color: #4b5563;
  line-height: 1.4;
}

.document-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 8px;
  font-size: 12px;
}

.upload-date {
  color: #6b7280;
}

.file-size-badge {
  background: #f3f4f6;
  color: #6b7280;
  padding: 2px 8px;
  border-radius: 4px;
  font-weight: 600;
}

.document-actions-detail {
  display: flex;
  flex-direction: column;
  gap: 8px;
  flex-shrink: 0;
}

.download-btn-detail {
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 6px;
  padding: 8px 12px;
  font-size: 12px;
  white-space: nowrap;
  cursor: pointer;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.download-btn-detail:hover {
  background: #2563eb;
  transform: translateY(-1px);
}

.modal-footer {
  border-top: 1px solid #e5e7eb;
  gap: 10px;
  padding: 16px 24px;
  display: flex;
  justify-content: flex-end;
  background: #f9fafb;
}

button.close,
button.close-btn {
  background: transparent;
  border: none;
  font-size: 28px;
  cursor: pointer;
  color: rgba(255, 255, 255, 0.9);
  line-height: 1;
  padding: 0;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  transition: all 0.2s;
}

button.close:hover,
button.close-btn:hover {
  background: rgba(255, 255, 255, 0.15);
  color: #ffffff;
  transform: rotate(90deg);
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
    font-size: 16px;
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
    font-size: 16px;
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

  .table-wrap {
    padding: 8px;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }

  .applications-table {
    font-size: 11px;
    min-width: 800px;
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

  .actions-col {
    min-width: auto;
  }

  .actions-col button {
    font-size: 11px;
    padding: 6px 10px;
  }

  .badge {
    font-size: 9px;
    padding: 3px 6px;
  }

  .empty {
    padding: 40px 16px;
  }

  .empty p {
    font-size: 14px;
  }

  .loading {
    padding: 40px 16px;
  }

  .spinner {
    width: 40px;
    height: 40px;
    border-width: 3px;
  }

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
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .modal-header h2 {
    font-size: 18px;
  }

  button.close,
  button.close-btn {
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

  .modal-footer {
    padding: 12px 16px;
    position: sticky;
    bottom: 0;
    z-index: 10;
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
    display: none;
  }

  .page-info {
    font-size: 13px;
    width: 100%;
    text-align: center;
    order: -1;
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

  .form-row {
    grid-template-columns: 1fr;
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

  .actions-col button {
    font-size: 10px;
    padding: 5px 8px;
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
  button {
    min-height: 44px;
    padding: 12px 16px;
  }

  .applications-table tbody tr:hover {
    background: transparent;
  }

  .applications-table th.sortable:hover {
    background: #f9fafb;
  }

  button:hover:not(:disabled) {
    transform: none;
  }

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

/* Modal Overlay Styles (for Edit Modal) */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 20px;
  z-index: 2000;
  overflow-y: auto;
  animation: fadeIn 0.3s ease;
}

.modal-content {
  width: 100%;
  max-width: 600px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  overflow: hidden;
  animation: modalSlideIn 0.3s ease;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
}

.modal-content.edit-modal {
  max-width: 600px;
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: translateY(-30px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.modal-content .modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid #e5e7eb;
  background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
  flex-shrink: 0;
}

.modal-content .modal-header h2 {
  margin: 0;
  font-size: 20px;
  color: #ffffff;
  font-weight: 700;
}

.modal-content .close-btn {
  background: transparent;
  border: none;
  font-size: 28px;
  cursor: pointer;
  color: rgba(255, 255, 255, 0.9);
  line-height: 1;
  padding: 0;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  transition: all 0.2s;
}

.modal-content .close-btn:hover {
  background: rgba(255, 255, 255, 0.15);
  color: #ffffff;
  transform: rotate(90deg);
}

.modal-content .modal-body {
  padding: 24px;
  overflow-y: auto;
  flex: 1;
}

.modal-content .modal-footer {
  border-top: 1px solid #e5e7eb;
  padding: 16px 24px;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  background: #f9fafb;
  flex-shrink: 0;
}

.autocomplete-wrapper {
  position: relative;
  display: flex;
  flex-direction: column;
}

.autocomplete-wrapper input.readonly-input {
  cursor: pointer;
  background: white;
}

.autocomplete-wrapper input.readonly-input:focus {
  cursor: pointer;
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
  max-height: 250px;
  overflow-y: auto;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
  z-index: 2100;
  animation: slideDown 0.2s ease;
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

.student-name,
.company-name {
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 2px;
}

.student-info,
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

.modal-content .submit-btn {
  background: #10b981;
  color: #fff;
  border: none;
  padding: 12px 24px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s;
  min-width: 140px;
}

.modal-content .submit-btn:hover:not(:disabled) {
  background: #059669;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.modal-content .submit-btn:disabled {
  background: #9ca3af;
  cursor: not-allowed;
  transform: none;
}

.modal-content .ghost {
  background: transparent;
  color: #6b7280;
  border: 1px solid #d1d5db;
  padding: 12px 24px;
  min-width: 100px;
}

.modal-content .ghost:hover:not(:disabled) {
  background: #f3f4f6;
  color: #374151;
  border-color: #9ca3af;
}

/* Mobile Edit Modal */
@media (max-width: 768px) {
  .modal-overlay {
    padding: 0;
    align-items: stretch;
  }

  .modal-content {
    max-width: 100%;
    max-height: 100vh;
    border-radius: 0;
    min-height: 100vh;
  }

  .modal-content .modal-header {
    padding: 16px 20px;
    position: sticky;
    top: 0;
    z-index: 10;
  }

  .modal-content .modal-header h2 {
    font-size: 18px;
  }

  .modal-content .close-btn {
    width: 32px;
    height: 32px;
    font-size: 24px;
  }

  .modal-content .modal-body {
    padding: 20px 16px;
    max-height: none;
  }

  .form-group input,
  .form-group select {
    font-size: 16px;
    padding: 14px 12px;
  }

  .dropdown {
    max-height: 200px;
  }

  .modal-content .modal-footer {
    padding: 16px 20px;
    flex-direction: column-reverse;
    position: sticky;
    bottom: 0;
    z-index: 10;
    box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.05);
  }

  .modal-content .submit-btn,
  .modal-content .ghost {
    width: 100%;
    min-width: auto;
    padding: 14px;
  }
}

@media (max-width: 480px) {
  .modal-content .modal-header {
    padding: 14px 16px;
  }

  .modal-content .modal-header h2 {
    font-size: 17px;
  }

  .modal-content .modal-body {
    padding: 16px 12px;
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