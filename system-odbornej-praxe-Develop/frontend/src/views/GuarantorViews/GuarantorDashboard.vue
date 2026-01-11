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

            <!-- ✅ CSV export otvorí modal -->
            <button
              @click="openExportModal"
              :disabled="loading || exporting"
              class="export-btn"
              title="Stiahnuť CSV report podľa aktuálnych filtrov"
            >
              {{ exporting ? 'Exportujem...' : 'Stiahnuť CSV' }}
            </button>

            <button
              @click="toggleAdvancedFilters"
              class="filter-btn"
              :class="{ active: showAdvancedFilters }"
            >
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
                  <div v-else class="muted">—</div>
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
                <div
                  v-if="showEditStudentDropdown && editForm.studentSearch && !filteredEditStudents.length"
                  class="dropdown"
                >
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
                <div
                  v-if="showEditCompanyDropdown && editForm.companySearch && !filteredEditCompanies.length"
                  class="dropdown"
                >
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
                  <div @click="selectEditSemester('1')" class="dropdown-item">
                    Zimný semester
                  </div>
                  <div @click="selectEditSemester('2')" class="dropdown-item">
                    Letný semester
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
          <button class="submit-btn" @click="saveInternship" :disabled="processing.edit">
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
                <option v-for="status in statuses" :key="status" :value="status">
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
          <button class="approve" @click="saveStatus" :disabled="processing.status || !statusForm.new_status">
            {{ processing.status ? 'Ukladám...' : 'Zmeniť stav' }}
          </button>
        </footer>
      </div>
    </div>

    <!-- ✅ Export Modal -->
    <div v-if="exportModal" class="modal-backdrop" @click.self="closeExportModal">
      <div class="modal-card modal-small">
        <header class="modal-header">
          <h2>CSV report – výber obsahu</h2>
          <button class="close-btn" @click="closeExportModal">✕</button>
        </header>

        <div class="modal-body">
          <div class="detail-section" style="border-bottom:none; padding-bottom:0; margin-bottom:18px;">
            <h3>Čo má report obsahovať?</h3>

            <div class="checkbox-grid">
              <label class="check-item">
                <input type="checkbox" v-model="exportOptions.columns.studyField" />
                Študijný odbor
              </label>

              <label class="check-item">
                <input type="checkbox" v-model="exportOptions.columns.academicYear" />
                Akademický rok
              </label>

              <label class="check-item">
                <input type="checkbox" v-model="exportOptions.columns.student" />
                Študent
              </label>

              <label class="check-item">
                <input type="checkbox" v-model="exportOptions.columns.company" />
                Firma
              </label>

              <label class="check-item">
                <input type="checkbox" v-model="exportOptions.columns.dateRange" />
                Termín praxe
              </label>

              <label class="check-item">
                <input type="checkbox" v-model="exportOptions.columns.status" />
                Stav praxe
              </label>
            </div>
          </div>

          <div class="detail-section" style="border-bottom:none; padding-bottom:0; margin-bottom:0;">
            <h3>Štatistika v reporte</h3>

            <div class="checkbox-grid">
              <label class="check-item">
                <input type="checkbox" v-model="exportOptions.stats.created" />
                Vytvorené
              </label>

              <label class="check-item">
                <input type="checkbox" v-model="exportOptions.stats.confirmed" />
                Potvrdené
              </label>

              <label class="check-item">
                <input type="checkbox" v-model="exportOptions.stats.approved" />
                Schválené
              </label>

              <label class="check-item">
                <input type="checkbox" v-model="exportOptions.stats.defended" />
                Obhájené
              </label>

              <label class="check-item">
                <input type="checkbox" v-model="exportOptions.stats.failed" />
                Neobhájené
              </label>

              <label class="check-item">
                <input type="checkbox" v-model="exportOptions.stats.rejected" />
                Zamietnuté
              </label>

              <label class="check-item">
                <input type="checkbox" v-model="exportOptions.stats.total" />
                Celkom
              </label>
            </div>

            <div class="info-box" style="margin-top:14px;">
              <strong>ℹ️ Tip:</strong>
              CSV sa vygeneruje podľa aktuálne zadaných filtrov a podľa toho, čo si tu zaklikneš.
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
import { reactive, ref, computed, onMounted, onBeforeUnmount } from 'vue'
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
const exporting = ref(false)
const processing = reactive({ edit: false, status: false })
const selected = ref(null)
const editMode = ref(false)
const statusMode = ref(false)
const showAdvancedFilters = ref(false)

// Export modal state
const exportModal = ref(false)

const exportOptions = reactive({
  columns: {
    studyField: true,
    academicYear: true,
    student: true,
    company: true,
    dateRange: true,
    status: true,
  },
  stats: {
    created: true,
    confirmed: true,
    approved: true,
    defended: true,
    failed: true,
    rejected: true,
    total: true,
  }
})

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

  const stats = Object.entries(exportOptions.stats)
    .filter(([, v]) => v)
    .map(([k]) => k)

  return { columns, stats }
}

async function confirmExport() {
  const { columns, stats } = buildExportPayload()
  if (!columns.length && !stats.length) {
    showAlert('Musíte vybrať aspoň jednu položku (stĺpec alebo štatistiku).', 'error')
    return
  }

  exportModal.value = false
  await downloadCsv({ columns, stats })
}

// Edit Modal State
const showEditStudentDropdown = ref(false)
const showEditCompanyDropdown = ref(false)
const showEditYearDropdown = ref(false)
const showEditSemesterDropdown = ref(false)

// Forms
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

// Timesheet helpers
function hasTimesheet(internship) {
  if (!internship.documents || !Array.isArray(internship.documents)) return false
  return internship.documents.some(doc => doc.document_type?.document_type_name === 'Výkaz hodín')
}

function getTimesheet(internship) {
  if (!internship.documents || !Array.isArray(internship.documents)) return null
  return internship.documents.find(doc => doc.document_type?.document_type_name === 'Výkaz hodín')
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

function toggleAdvancedFilters() {
  showAdvancedFilters.value = !showAdvancedFilters.value
}

function clearFilters() {
  Object.keys(filters).forEach(key => { filters[key] = '' })
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

// helper na filename z Content-Disposition
function getFilenameFromHeaders(headers) {
  // axios headers sú obyčajný object s lower-case keys
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

// ✅ FIX: download CSV správne (prípona + BOM + content-type)
async function downloadCsv(customOptions = null) {
  exporting.value = true
  try {
    const params = {
      academic_year: filters.academicYear || undefined,
      status: filters.status || undefined,
      company: filters.company || undefined,
      study_field: filters.studyField || undefined,
      student: filters.student || undefined,
      search: filters.search || undefined,

      ...(customOptions ? {
        columns: customOptions.columns,
        stats: customOptions.stats
      } : {})
    }

    // ✅ backend má aj GET route, takže toto môže ostať GET
    const response = await api.get('/guarantor/internships/export', {
      responseType: 'blob',
      params,
    })

    const rawType = (response.headers?.['content-type'] || '').toLowerCase()
    const isCsv = rawType.includes('text/csv') || rawType.includes('application/csv') || rawType.includes('text/plain')

    // filename z headeru, inak fallback
    let filename = getFilenameFromHeaders(response.headers) || buildFallbackCsvName()

    // poistky: vždy chceme CSV
    filename = filename.replace(/\.xlsx$/i, '.csv')
    if (!/\.csv$/i.test(filename)) filename = `${filename}.csv`

    // ✅ BOM kvôli diakritike v Exceli
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

// Computed
const academicYears = computed(() => {
  const years = new Set()
  internships.value.forEach(i => { if (i.academic_year) years.add(i.academic_year) })
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
    return fullName.includes(search) || email.includes(search) || studyField.includes(search)
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
  return academicYearsOptions.value.filter(year => year.toLowerCase().includes(search))
})

const filteredInternships = computed(() => {
  let result = internships.value

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

  if (filters.status) result = result.filter(i => i.current_status?.internship_status_name === filters.status)
  if (filters.academicYear) result = result.filter(i => i.academic_year === filters.academicYear)
  if (filters.company) result = result.filter(i => i.company?.company_name === filters.company)
  if (filters.studyField) result = result.filter(i => i.student.study_field?.study_field_name === filters.studyField)

  if (filters.student) {
    const studentLower = filters.student.toLowerCase()
    result = result.filter(i => {
      const name = `${i.student.first_name} ${i.student.last_name}`.toLowerCase()
      return name.includes(studentLower)
    })
  }

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

  const student = internship.student
  editForm.studentSearch = student ? `${student.first_name} ${student.last_name}` : ''
  editForm.selectedStudent = student
  editForm.student_id = student?.id || internship.users_id || ''

  const company = internship.company
  editForm.companySearch = company?.company_name || ''
  editForm.selectedCompany = company
  editForm.company_id = company?.id || ''

  editForm.academic_year = internship.academic_year || ''
  editForm.semester = String(internship.semester) || ''
  editForm.semesterDisplay = internship.semester === 1 ? 'Zimný semester' : internship.semester === 2 ? 'Letný semester' : ''

  editForm.date_start = internship.date_start ? new Date(internship.date_start).toISOString().split('T')[0] : ''
  editForm.date_end = internship.date_end ? new Date(internship.date_end).toISOString().split('T')[0] : ''

  editMode.value = true
  selected.value = null
}

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

function closeEditModal() {
  editMode.value = false
  showEditStudentDropdown.value = false
  showEditCompanyDropdown.value = false
  showEditYearDropdown.value = false
  showEditSemesterDropdown.value = false

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
  editForm.date_start = ''
  editForm.date_end = ''
}

async function saveInternship() {
  if (!editForm.selectedStudent) {
    showAlert('Musíte vybrať študenta zo zoznamu.', 'error')
    return
  }

  if (!editForm.selectedCompany) {
    showAlert('Musíte vybrať firmu zo zoznamu.', 'error')
    return
  }

  if (!editForm.academic_year.match(/^\d{4}\/\d{4}$/)) {
    showAlert('Akademický rok musí byť vo formáte YYYY/YYYY (napr. 2024/2025).', 'error')
    return
  }

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

  if (!clickedWrapper) {
    showEditStudentDropdown.value = false
    showEditCompanyDropdown.value = false
    showEditYearDropdown.value = false
    showEditSemesterDropdown.value = false
    return
  }

  const studentInput = document.getElementById('edit_student')
  const companyInput = document.getElementById('edit_company')
  const yearInput = document.getElementById('edit_academic_year')
  const semesterInput = document.getElementById('edit_semester')

  if (clickedWrapper.contains(studentInput)) {
    showEditCompanyDropdown.value = false
    showEditYearDropdown.value = false
    showEditSemesterDropdown.value = false
  } else if (clickedWrapper.contains(companyInput)) {
    showEditStudentDropdown.value = false
    showEditYearDropdown.value = false
    showEditSemesterDropdown.value = false
  } else if (clickedWrapper.contains(yearInput)) {
    showEditStudentDropdown.value = false
    showEditCompanyDropdown.value = false
    showEditSemesterDropdown.value = false
  } else if (clickedWrapper.contains(semesterInput)) {
    showEditStudentDropdown.value = false
    showEditCompanyDropdown.value = false
    showEditYearDropdown.value = false
  }
}

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

function changePage(n) {
  if (n < 1 || n > totalPages.value) return
  currentPage.value = n
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

onMounted(() => {
  fetchInternships()
  fetchStudents()
  fetchCompanies()
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
/* ✅ Všetky tvoje pôvodné štýly ostali. */
/* ✅ Pridal som iba štýl pre export button + checkbox grid pre export modal. */

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
.filter-btn,
.export-btn {
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

/* ✅ export button */
.export-btn {
  background: #3b82f6;
  color: white;
}

.export-btn:hover:not(:disabled) {
  background: #2563eb;
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
.filter-btn:disabled,
.export-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* ✅ checkbox grid pre export modal */
.checkbox-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px 14px;
  margin-top: 10px;
}

.check-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  background: #f9fafb;
  cursor: pointer;
  font-weight: 600;
  color: #374151;
  transition: all 0.15s;
}

.check-item:hover {
  background: #f3f4f6;
  border-color: #d1d5db;
}

.check-item input[type="checkbox"] {
  width: 18px;
  height: 18px;
  cursor: pointer;
}

@media (max-width: 768px) {
  .checkbox-grid {
    grid-template-columns: 1fr;
  }
}

/* --- zvyšok štýlov je tvoj pôvodný (nezmenený) --- */

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

.stat-value.pending { color: #f59e0b; }
.stat-value.confirmed { color: #3b82f6; }
.stat-value.approved { color: #10b981; }
.stat-value.success { color: #10b981; }
.stat-value.failed { color: #ef4444; }
.stat-value.denied { color: #ef4444; }
.stat-value.total { color: #3b82f6; }

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

.badge.badge-pending { background: #fef3c7; color: #92400e; }
.badge.badge-confirmed { background: #dbeafe; color: #1e40af; }
.badge.badge-approved { background: #d1fae5; color: #065f46; }
.badge.badge-success { background: #d1fae5; color: #065f46; }
.badge.badge-failed { background: #fee2e2; color: #991b1b; }
.badge.badge-rejected { background: #fee2e2; color: #991b1b; }
.badge.badge-muted { background: #f3f4f6; color: #6b7280; }
.badge.badge-info { background: #dbeafe; color: #1e40af; }

/* Actions column */
.actions-col {
  display: flex;
  gap: 8px;
  align-items: center;
  flex-wrap: wrap;
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

button.ghost:hover:not(:disabled) { background: #eff6ff; }

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

button.approve:hover:not(:disabled) { background: #059669; }

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

.clear-btn:hover { background: #369f73; }

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

@keyframes spin { to { transform: rotate(360deg); } }

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

.pagination button:disabled { opacity: 0.5; cursor: not-allowed; }

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

.page-size-selector label { color: #6b7280; font-weight: 600; }

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

.modal-card.modal-small { max-width: 600px; }

@keyframes modalFadeIn {
  from { opacity: 0; transform: translateY(-20px); }
  to { opacity: 1; transform: translateY(0); }
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

.detail-section:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }

.detail-section h3 {
  font-size: 16px;
  margin: 0 0 12px 0;
  color: #1f2937;
  font-weight: 700;
}

.detail-section p { margin: 8px 0; line-height: 1.6; }

.status-history { margin-top: 12px; }
.status-history ul { list-style: none; padding: 0; margin: 8px 0 0 0; }

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

.history-date { color: #6b7280; font-weight: 600; }

.history-notes {
  display: block;
  width: 100%;
  margin-top: 4px;
  color: #4b5563;
  font-style: italic;
}

.documents-list { list-style: none; padding: 0; margin: 8px 0 0 0; }

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

.document-link:hover { text-decoration: underline; }

.document-type { color: #6b7280; font-size: 12px; }

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
.edit-form { display: flex; flex-direction: column; gap: 20px; }

.form-group { display: flex; flex-direction: column; gap: 8px; }

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

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

.current-status { margin: 4px 0; }

.info-box {
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  border-radius: 8px;
  padding: 12px 16px;
  font-size: 13px;
  color: #1e40af;
  line-height: 1.5;
}

/* Tablet + Mobile */
@media (max-width: 768px) {
  .dashboard-page { padding: 8px; }
  .panel-header { flex-direction: column; align-items: stretch; padding: 16px; gap: 12px; }
  .panel-header h1 { font-size: 20px; text-align: center; }
  .actions { flex-direction: column; width: 100%; gap: 8px; }
  .search-input { width: 100%; min-width: auto; font-size: 16px; padding: 12px 14px; }
  .refresh-btn, .filter-btn, .export-btn { width: 100%; padding: 12px 18px; font-size: 15px; }
  .advanced-filters { grid-template-columns: 1fr; padding: 16px; gap: 12px; }
  .clear-filters-btn { max-width: none; width: 100%; }
  .stats-bar { flex-wrap: wrap; padding: 12px; gap: 8px; justify-content: center; }
  .stat { flex: 1; min-width: calc(50% - 4px); padding: 12px 8px; }
  .stat-label { font-size: 11px; }
  .stat-value { font-size: 24px; }
  .table-wrap { padding: 8px; overflow-x: auto; -webkit-overflow-scrolling: touch; }
  .applications-table { font-size: 11px; min-width: 800px; }
  .applications-table th, .applications-table td { padding: 8px 6px; }
  .applications-table th { font-size: 10px; position: sticky; top: 0; background: #f9fafb; z-index: 1; }
  .actions-col button { font-size: 11px; padding: 6px 10px; }
  .badge { font-size: 9px; padding: 3px 6px; }
  .modal-backdrop { padding: 0; align-items: flex-start; }
  .modal-card { max-width: 100%; min-height: 100vh; border-radius: 0; margin: 0; }
  .modal-header { padding: 14px 16px; position: sticky; top: 0; z-index: 10; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); }
  .modal-footer { padding: 12px 16px; position: sticky; bottom: 0; z-index: 10; flex-direction: column; gap: 8px; box-shadow: 0 -2px 4px rgba(0, 0, 0, 0.1); }
  .modal-footer button { width: 100%; padding: 14px 16px; font-size: 15px; font-weight: 700; }
  .panel-footer { flex-direction: column; gap: 12px; padding: 12px 16px; }
  .pagination { flex-wrap: wrap; justify-content: center; gap: 6px; width: 100%; }
  .pagination button:first-child, .pagination button:last-child { display: none; }
  .page-info { font-size: 13px; width: 100%; text-align: center; order: -1; margin-bottom: 8px; }
  .page-info .muted { display: block; margin-top: 4px; }
  .page-size-selector { width: 100%; justify-content: center; font-size: 13px; }
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
