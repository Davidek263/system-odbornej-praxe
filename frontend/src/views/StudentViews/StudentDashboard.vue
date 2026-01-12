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
          <h1>Moje odborné praxe</h1>

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

            <!-- Create New Internship Button -->
            <button
              @click="showCreateModal = true"
              class="create-btn"
            >
              + Vytvoriť novú prax
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
            <label>Semester:</label>
            <select v-model="filters.semester" @change="applyFilters">
              <option value="">Všetky semestre</option>
              <option value="1">Zimný semester</option>
              <option value="2">Letný semester</option>
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
            <span class="stat-label">Obhájené</span>
            <span class="stat-value success">{{ stats.defended }}</span>
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
                <th class="sortable" @click="toggleSort('company')">
                  <div class="th-content">
                    <span>Firma</span>
                    <span class="sort-indicator" v-if="sortColumn === 'company'">
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
                  <div class="name">{{ internship.company?.company_name || '"”' }}</div>
                  <div class="muted">{{ internship.company?.contact_person_email || '' }}</div>
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
                  <div v-if="hasTimesheet(internship)">
                    <span :class="timesheetBadge(internship)">
                      {{ getTimesheetStatus(internship) }}
                    </span>
                  </div>
                  <div v-else class="muted">Bez výkazu</div>
                </td>
                <td class="actions-col">
                  <button class="ghost" @click="viewDetails(internship)">Detail</button>
                  <button class="edit-btn" @click="editInternship(internship)">Upraviť</button>
                  <button class="upload-btn" @click="uploadDocument(internship)">Dokumenty</button>
                </td>
              </tr>
            </tbody>
          </table>

          <div v-if="!loading && !internships.length" class="empty">
            <p>Zatiaľ nemáte žiadne odborné praxe.</p>
            <p class="muted">Vytvorte si novú prax kliknutím na tlačidlo "+ Vytvoriť novú prax"</p>
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
          <button class="close-btn" @click="closeModal">✕</button>
        </header>

        <div class="modal-body">
          <div class="detail-section">
            <h3>Firma</h3>
            <p><strong>Názov:</strong> {{ selected.company?.company_name || '"”' }}</p>
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

            <div v-if="!selected.documents || selected.documents.length === 0" class="empty-documents">
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
                    <span class="upload-date">{{ formatDate(doc.uploaded_at) }}</span>
                    <span class="file-size-badge">{{ formatFileSize(doc.file_size) }}</span>
                    <span v-if="doc.is_verified" class="verified-badge">✓ Overené</span>
                    <span v-if="isTimesheet(doc)" :class="timesheetBadgeForDoc(doc)">
                      {{ getTimesheetStatusForDoc(doc) }}
                    </span>
                  </div>
                </div>
                <div class="document-actions-detail">
                  <button @click="downloadDocumentFile(doc)" class="download-btn-detail" title="Stiahnuť">
                    ⬇ Stiahnuť
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <footer class="modal-footer">
          <button class="ghost" @click="closeModal">Zavrieť</button>
          <button v-if="selected?.internship_type === 'prax'" class="dohoda-btn" @click="downloadDohoda(selected)" title="Generovať dohodu">
            Generovať dohodu
          </button>
          <button class="upload-btn" @click="uploadDocument(selected)">
            Nahrať dokument
          </button>
        </footer>
      </div>
    </div>

    <!-- Create Internship Modal -->
    <div v-if="showCreateModal" class="modal-overlay" @click.self="closeCreateModal">
      <div class="modal-content create-modal">
        <header class="modal-header">
          <h2>Vytvorenie novej odbornej praxe</h2>
          <button class="close-btn" @click="closeCreateModal">✕</button>
        </header>

        <Spinner v-if="createLoading" overlay />

        <div class="modal-body">
          <form @submit.prevent="submitCreateForm">
            <!-- Company Autocomplete -->
            <div class="form-group">
              <label for="company">Firma *</label>
              <div class="autocomplete-wrapper">
                <input
                  id="company"
                  v-model="createForm.companySearch"
                  @input="filterCompanies"
                  @focus="showCompanyDropdown = true"
                  type="text"
                  placeholder="Začnite písať názov firmy..."
                  autocomplete="off"
                  :required="!createForm.selectedCompany"
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
                      <span v-if="company.contact_person_email">- {{ company.contact_person_email }}</span>
                    </div>
                  </div>
                </div>
                <div v-if="showCompanyDropdown && createForm.companySearch && !filteredCompanies.length" class="dropdown">
                  <div class="dropdown-empty">Žiadne firmy nenájdené</div>
                </div>
              </div>
            </div>

            <!-- Academic Year -->
            <div class="form-group">
              <label for="academic_year">Akademický rok *</label>
              <div class="autocomplete-wrapper">
                <input
                  id="academic_year"
                  v-model="createForm.academic_year"
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
            </div>

            <!-- Semester -->
            <div class="form-group">
              <label for="semester">Semester *</label>
              <div class="autocomplete-wrapper">
                <input
                  id="semester"
                  v-model="createForm.semesterDisplay"
                  @focus="showSemesterDropdown = true"
                  type="text"
                  placeholder="Vyberte semester..."
                  autocomplete="off"
                  readonly
                  required
                  :class="{ 'readonly-input': true }"
                />
                <div v-if="showSemesterDropdown" class="dropdown">
                  <div
                    @click="selectSemester('1')"
                    class="dropdown-item"
                  >
                    Zimný semester
                  </div>
                  <div
                    @click="selectSemester('2')"
                    class="dropdown-item"
                  >
                    Letný semester
                  </div>
                </div>
              </div>
            </div>

            <!-- Internship Type -->
            <div class="form-group">
              <label for="internshipType">Typ *</label>
              <div class="autocomplete-wrapper">
                <input
                  id="internshipType"
                  v-model="createForm.internshipTypeDisplay"
                  @focus="showInternshipTypeDropdown = true"
                  type="text"
                  placeholder="Vyberte typ..."
                  autocomplete="off"
                  readonly
                  required
                  :class="{ 'readonly-input': true }"
                />
                <div v-if="showInternshipTypeDropdown" class="dropdown">
                  <div
                    @click="selectInternshipType('prax')"
                    class="dropdown-item"
                  >
                    Prax
                  </div>
                  <div
                    @click="selectInternshipType('brigada')"
                    class="dropdown-item"
                  >
                    Brigáda
                  </div>
                </div>
              </div>
            </div>

            <!-- Date Start -->
            <div class="form-group">
              <label for="date_start">Dátum začiatku *</label>
              <input id="date_start" v-model="createForm.date_start" type="date" required />
            </div>

            <!-- Date End -->
            <div class="form-group">
              <label for="date_end">Dátum konca *</label>
              <input id="date_end" v-model="createForm.date_end" type="date" required />
            </div>
          </form>
        </div>

        <footer class="modal-footer">
          <button class="ghost" @click="closeCreateModal">Zrušiť</button>
          <button class="submit-btn" @click="submitCreateForm" :disabled="createSubmitting">
            {{ createSubmitting ? 'Vytvára sa...' : 'Vytvoriť prax' }}
          </button>
        </footer>
      </div>
    </div>

    <!-- Edit Internship Modal -->
    <div v-if="showEditModal" class="modal-overlay" @click.self="closeEditModal">
      <div class="modal-content edit-modal">
        <header class="modal-header">
          <h2>Úpráva odbornej praxe</h2>
          <button class="close-btn" @click="closeEditModal">✕</button>
        </header>

        <Spinner v-if="editLoading" overlay />

        <div class="modal-body">
          <form @submit.prevent="submitEditForm">
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
                      <span v-if="company.contact_person_email"> "¢ {{ company.contact_person_email }}</span>
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
          <button class="submit-btn" @click="submitEditForm" :disabled="editSubmitting">
            {{ editSubmitting ? 'Ukladá sa...' : 'Uložiť zmeny' }}
          </button>
        </footer>
      </div>
    </div>

    <!-- Document Management Modal -->
    <div v-if="showDocumentModal" class="modal-overlay" @click.self="closeDocumentModal">
      <div class="modal-content document-modal">
        <header class="modal-header">
          <h2>Správa dokumentov - {{ documentModalInternship?.company?.company_name }}</h2>
          <button class="close-btn" @click="closeDocumentModal">✕</button>
        </header>

        <Spinner v-if="documentLoading" overlay />

        <div class="modal-body">
          <!-- Upload Section -->
          <div class="upload-section">
            <h3>Nahrať nový dokument</h3>
            <form @submit.prevent="submitUploadDocument" class="upload-form">
              <div class="form-row">
                <div class="form-group">
                  <label for="document_type">Typ dokumentu *</label>
                  <select id="document_type" v-model="uploadForm.document_type_id" required>
                    <option value="">Vyberte typ dokumentu...</option>
                    <option v-for="type in documentTypes" :key="type.id" :value="type.id">
                      {{ type.document_type_name }}
                    </option>
                  </select>
                </div>
                <div class="form-group">
                  <label for="document_name">Názov dokumentu</label>
                  <input
                    id="document_name"
                    v-model="uploadForm.document_name"
                    type="text"
                    placeholder="Nepovinné - použije sa názov typu"
                  />
                </div>
              </div>
              <div class="form-group">
                <label for="description">Popis</label>
                <textarea
                  id="description"
                  v-model="uploadForm.description"
                  rows="2"
                  placeholder="Voliteľný popis dokumentu..."
                ></textarea>
              </div>
              <div class="form-group file-input-group">
                <label for="file">Súbor * (PDF, DOC, DOCX, JPG, PNG - max 10MB)</label>
                <input
                  id="file"
                  type="file"
                  @change="handleFileSelect"
                  accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                  required
                />
                <div v-if="uploadForm.file" class="file-info">
                  <span class="file-name">{{ uploadForm.file.name }}</span>
                  <span class="file-size">({{ formatFileSize(uploadForm.file.size) }})</span>
                </div>
              </div>
              <button type="submit" class="submit-btn" :disabled="uploadSubmitting">
                {{ uploadSubmitting ? 'Nahrávam...' : 'Nahrať dokument' }}
              </button>
            </form>
          </div>

          <!-- Documents List -->
          <div class="documents-section">
            <h3>Nahraté dokumenty ({{ documents.length }})</h3>

            <div v-if="documents.length === 0" class="empty-documents">
              <p>Zatiaľ neboli nahrané žiadne dokumenty.</p>
            </div>

            <div v-else class="documents-grid">
              <div v-for="doc in documents" :key="doc.id" class="document-card">
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
                    <span class="upload-date">{{ formatDate(doc.uploaded_at) }}</span>
                    <span class="file-size-badge">{{ formatFileSize(doc.file_size) }}</span>
                    <span v-if="doc.is_verified" class="verified-badge">✓ Overené</span>
                    <span v-if="isTimesheet(doc)" :class="timesheetBadgeForDoc(doc)">
                      {{ getTimesheetStatusForDoc(doc) }}
                    </span>
                  </div>
                </div>
                <div class="document-actions">
                  <button @click="downloadDocumentFile(doc)" class="download-btn" title="Stiahnuť">
                    ⬇ Stiahnuť
                  </button>
                  <button
                    v-if="canDeleteDocument(doc)"
                    @click="confirmDeleteDocument(doc)"
                    class="delete-btn"
                    title="Vymazať"
                  >
                    🗑️ Vymazať
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <footer class="modal-footer">
          <button class="ghost" @click="closeDocumentModal">Zavrieť</button>
        </footer>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteConfirm" class="modal-backdrop delete-confirm-backdrop" @click.self="cancelDelete">
      <div class="modal-card confirm-modal">
        <header class="modal-header">
          <h2>Potvrdenie vymazania</h2>
          <button class="close-btn" @click="cancelDelete">✕</button>
        </header>
        <div class="modal-body">
          <p>Naozaj chcete vymazať dokument <strong>{{ documentToDelete?.document_name }}</strong>?</p>
          <p class="warning-text">Táto akcia sa nedá vrátiť späť.</p>
        </div>
        <footer class="modal-footer">
          <button class="ghost" @click="cancelDelete">Zrušiť</button>
          <button class="delete-confirm-btn" @click="executeDelete" :disabled="deleteSubmitting">
            {{ deleteSubmitting ? 'Mažem...' : 'Vymazať' }}
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
import Spinner from '@/components/Spinner.vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// ============================================================
// ALERT SYSTEM
// ============================================================
const alert = reactive({ show: false, type: 'error', message: '', duration: 5000 })

function showAlert(message, type = 'error') {
  alert.message = message
  alert.type = type
  alert.show = true
}

// ============================================================
// STATE MANAGEMENT
// ============================================================
const internships = ref([])
const loading = ref(false)
const selected = ref(null)
const showAdvancedFilters = ref(false)

// Create Modal State
const showCreateModal = ref(false)
const createLoading = ref(false)
const createSubmitting = ref(false)
const companies = ref([])
const showCompanyDropdown = ref(false)
const showYearDropdown = ref(false)
const showSemesterDropdown = ref(false)
const showInternshipTypeDropdown = ref(false)

const createForm = reactive({
  companySearch: '',
  selectedCompany: null,
  company_id: null,
  academic_year: '',
  semester: '',
  semesterDisplay: '',
  internship_type: '',
  internshipTypeDisplay: '',
  date_start: '',
  date_end: ''
})

// Edit Modal State
const showEditModal = ref(false)
const editLoading = ref(false)
const editSubmitting = ref(false)
const showEditCompanyDropdown = ref(false)
const showEditYearDropdown = ref(false)
const showEditSemesterDropdown = ref(false)
const showEditInternshipTypeDropdown = ref(false)
const editingInternship = ref(null)

const editForm = reactive({
  companySearch: '',
  selectedCompany: null,
  company_id: null,
  academic_year: '',
  semester: '',
  semesterDisplay: '',
  internship_type: '',
  internshipTypeDisplay: '',
  date_start: '',
  date_end: ''
})

// Document Management State
const showDocumentModal = ref(false)
const documentModalInternship = ref(null)
const documentLoading = ref(false)
const documents = ref([])
const documentTypes = ref([])
const uploadSubmitting = ref(false)
const showDeleteConfirm = ref(false)
const documentToDelete = ref(null)
const deleteSubmitting = ref(false)

const uploadForm = reactive({
  document_type_id: '',
  document_name: '',
  description: '',
  file: null
})

// ============================================================
// FILTERS & SORTING
// ============================================================
const filters = reactive({
  search: '',
  status: '',
  academicYear: '',
  semester: '',
})

const sortColumn = ref('')
const sortDirection = ref('asc')
const pageSize = ref(10)
const currentPage = ref(1)

// ============================================================
// HELPER FUNCTIONS
// ============================================================
function formatDate(d) {
  if (!d) return '"”'
  const dt = new Date(d)
  if (isNaN(dt)) return d
  return dt.toLocaleDateString('sk-SK', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

function formatDateTime(d) {
  if (!d) return '"”'
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
  if (!start || !end) return '"”'
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

function isTimesheet(doc) {
  return doc.document_type?.document_type_name === 'Výkaz hodín'
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

function getTimesheetStatusForDoc(doc) {
  if (!isTimesheet(doc)) return ''
  
  if (doc.timesheet_status_history && doc.timesheet_status_history.length > 0) {
    const latestStatus = doc.timesheet_status_history[0]
    return latestStatus.status?.timesheet_status_name || 'Nahraný'
  }
  
  return doc.is_verified ? 'Potvrdený' : 'Nahraný'
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

function timesheetBadgeForDoc(doc) {
  const status = getTimesheetStatusForDoc(doc)
  const badgeMap = {
    'Nahraný': 'badge pending',
    'Potvrdený': 'badge approved',
    'Zamietnutý': 'badge rejected'
  }
  return badgeMap[status] || 'badge'
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
  currentPage.value = 1
}

// Computed
// Create Modal Computed
const academicYearsOptions = computed(() => {
  const currentYear = new Date().getFullYear()
  const years = []
  
  // Min: 2020/2021, Max: current/current+1
  const minYear = 2020
  const maxYear = currentYear
  
  for (let year = minYear; year <= maxYear; year++) {
    years.push(`${year}/${year + 1}`)
  }
  
  return years.reverse() // Newest first
})

const currentAcademicYear = computed(() => {
  const now = new Date()
  const currentYear = now.getFullYear()
  const currentMonth = now.getMonth() + 1
  
  if (currentMonth >= 9) {
    return `${currentYear}/${currentYear + 1}`
  } else {
    return `${currentYear - 1}/${currentYear}`
  }
})

const filteredCompanies = computed(() => {
  if (!createForm.companySearch) return companies.value
  
  const search = createForm.companySearch.toLowerCase()
  return companies.value.filter(company => 
    company.company_name.toLowerCase().includes(search) ||
    company.address?.city?.toLowerCase().includes(search) ||
    company.contact_person_name?.toLowerCase().includes(search) ||
    company.contact_person_email?.toLowerCase().includes(search)
  )
})

const filteredYears = computed(() => {
  if (!createForm.academic_year) return academicYearsOptions.value
  
  const search = createForm.academic_year.toLowerCase()
  return academicYearsOptions.value.filter(year => 
    year.toLowerCase().includes(search)
  )
})

// Edit Modal Computed
const filteredEditCompanies = computed(() => {
  if (!editForm.companySearch) return companies.value
  
  const search = editForm.companySearch.toLowerCase()
  return companies.value.filter(company => 
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

const filteredInternships = computed(() => {
  let result = internships.value

  if (filters.search) {
    const searchLower = filters.search.toLowerCase()
    result = result.filter(i => {
      const companyName = (i.company?.company_name || '').toLowerCase()
      const academicYear = (i.academic_year || '').toLowerCase()
      const status = (i.current_status?.internship_status_name || '').toLowerCase()
      
      return companyName.includes(searchLower) ||
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

  // Sorting
  if (sortColumn.value) {
    result = [...result].sort((a, b) => {
      let aVal, bVal

      switch (sortColumn.value) {
        case 'company':
          aVal = (a.company?.company_name || '').toLowerCase()
          bVal = (b.company?.company_name || '').toLowerCase()
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
          // Sort by timesheet status
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
  const pending = filteredInternships.value.filter(i => i.current_status?.internship_status_name === 'Vytvorená').length
  const confirmed = filteredInternships.value.filter(i => i.current_status?.internship_status_name === 'Potvrdená').length
  const approved = filteredInternships.value.filter(i => i.current_status?.internship_status_name === 'Schválená').length
  const defended = filteredInternships.value.filter(i => i.current_status?.internship_status_name === 'Obhájená').length
  const rejected = filteredInternships.value.filter(i => i.current_status?.internship_status_name === 'Zamietnutá').length

  return { pending, confirmed, approved, defended, rejected }
})

const totalPages = computed(() => Math.max(1, Math.ceil(filteredInternships.value.length / pageSize.value)))

const paginated = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value
  return filteredInternships.value.slice(start, start + pageSize.value)
})

// Create Modal Functions
async function fetchCompanies() {
  createLoading.value = true
  try {
    const response = await api.get('/student/companies')
    companies.value = response.data.companies || []
  } catch (err) {
    showAlert('Nepodarilo sa načítať zoznam firiem.', 'error')
    console.error('Error fetching companies:', err)
  } finally {
    createLoading.value = false
  }
}

function filterCompanies() {
  showCompanyDropdown.value = true
  if (createForm.selectedCompany && createForm.companySearch !== createForm.selectedCompany.company_name) {
    createForm.selectedCompany = null
    createForm.company_id = null
  }
}

function selectCompany(company) {
  createForm.selectedCompany = company
  createForm.companySearch = company.company_name
  createForm.company_id = company.id
  showCompanyDropdown.value = false
}

function clearCompany() {
  createForm.selectedCompany = null
  createForm.companySearch = ''
  createForm.company_id = null
  showCompanyDropdown.value = false
}

function filterYears() {
  showYearDropdown.value = true
}

function selectYear(year) {
  createForm.academic_year = year
  showYearDropdown.value = false
}

function selectSemester(semester) {
  createForm.semester = semester
  createForm.semesterDisplay = semester === '1' ? 'Zimný semester' : 'Letný semester'
  showSemesterDropdown.value = false
}

function selectInternshipType(type) {
  createForm.internship_type = type
  createForm.internshipTypeDisplay = type === 'prax' ? 'Prax' : 'Brigáda'
  showInternshipTypeDropdown.value = false
}

function closeCreateModal() {
  showCreateModal.value = false
  resetCreateForm()
  showCompanyDropdown.value = false
  showYearDropdown.value = false
  showSemesterDropdown.value = false
  showInternshipTypeDropdown.value = false
}

function resetCreateForm() {
  createForm.companySearch = ''
  createForm.selectedCompany = null
  createForm.company_id = null
  createForm.academic_year = currentAcademicYear.value
  createForm.semester = ''
  createForm.semesterDisplay = ''
  createForm.internship_type = ''
  createForm.internshipTypeDisplay = ''
  createForm.date_start = ''
  createForm.date_end = ''
}

async function submitCreateForm() {
  // Validate company
  if (!createForm.selectedCompany) {
    showAlert('Musíte vybrať firmu zo zoznamu.', 'error')
    return
  }

  // Validate academic year format
  if (!createForm.academic_year.match(/^\d{4}\/\d{4}$/)) {
    showAlert('Akademický rok musí byť vo formáte YYYY/YYYY (napr. 2024/2025).', 'error')
    return
  }

  // Validate dates
  if (new Date(createForm.date_start) >= new Date(createForm.date_end)) {
    showAlert('Dátum konca musí byť po dátume začiatku.', 'error')
    return
  }

  createSubmitting.value = true

  try {
    const payload = {
      company_id: createForm.company_id,
      academic_year: createForm.academic_year,
      semester: parseInt(createForm.semester),
      internship_type: createForm.internship_type,
      date_start: createForm.date_start,
      date_end: createForm.date_end
    }

    await api.post('/internships', payload)
    
    showAlert('Prax bola úspešne vytvorená!', 'success')
    
    closeCreateModal()
    
    // Refresh internships list
    await fetchInternships()

  } catch (err) {
    showAlert(err.response?.data?.message || 'Nepodarilo sa vytvoriť prax.', 'error')
    console.error('Error creating internship:', err)
  } finally {
    createSubmitting.value = false
  }
}

function handleClickOutside(event) {
  const target = event.target
  const clickedWrapper = target.closest('.autocomplete-wrapper')

  // If clicking outside all autocomplete wrappers, close all dropdowns
  if (!clickedWrapper) {
    showCompanyDropdown.value = false
    showYearDropdown.value = false
    showSemesterDropdown.value = false
    showEditCompanyDropdown.value = false
    showEditYearDropdown.value = false
    showEditSemesterDropdown.value = false
    return
  }

  // If clicking inside a specific wrapper, close other dropdowns
  const createCompanyInput = document.getElementById('company')
  const createYearInput = document.getElementById('academic_year')
  const createSemesterInput = document.getElementById('semester')
  const editCompanyInput = document.getElementById('edit_company')
  const editYearInput = document.getElementById('edit_academic_year')
  const editSemesterInput = document.getElementById('edit_semester')

  if (clickedWrapper.contains(createCompanyInput)) {
    showYearDropdown.value = false
    showSemesterDropdown.value = false
    showEditCompanyDropdown.value = false
    showEditYearDropdown.value = false
    showEditSemesterDropdown.value = false
  } else if (clickedWrapper.contains(createYearInput)) {
    showCompanyDropdown.value = false
    showSemesterDropdown.value = false
    showEditCompanyDropdown.value = false
    showEditYearDropdown.value = false
    showEditSemesterDropdown.value = false
  } else if (clickedWrapper.contains(createSemesterInput)) {
    showCompanyDropdown.value = false
    showYearDropdown.value = false
    showEditCompanyDropdown.value = false
    showEditYearDropdown.value = false
    showEditSemesterDropdown.value = false
  } else if (clickedWrapper.contains(editCompanyInput)) {
    showCompanyDropdown.value = false
    showYearDropdown.value = false
    showSemesterDropdown.value = false
    showEditYearDropdown.value = false
    showEditSemesterDropdown.value = false
  } else if (clickedWrapper.contains(editYearInput)) {
    showCompanyDropdown.value = false
    showYearDropdown.value = false
    showSemesterDropdown.value = false
    showEditCompanyDropdown.value = false
    showEditSemesterDropdown.value = false
  } else if (clickedWrapper.contains(editSemesterInput)) {
    showCompanyDropdown.value = false
    showYearDropdown.value = false
    showSemesterDropdown.value = false
    showEditCompanyDropdown.value = false
    showEditYearDropdown.value = false
  }
}

// Edit Modal Functions
function editInternship(internship) {
  editingInternship.value = internship

  // Populate form with existing data
  const company = internship.company
  editForm.companySearch = company?.company_name || ''
  editForm.selectedCompany = company
  editForm.company_id = company?.id || null
  editForm.academic_year = internship.academic_year || ''
  editForm.semester = String(internship.semester) || ''
  editForm.semesterDisplay = internship.semester === 1 ? 'Zimný semester' : internship.semester === 2 ? 'Letný semester' : ''
  editForm.internship_type = internship.internship_type || 'prax'
  editForm.internshipTypeDisplay = internship.internship_type === 'prax' ? 'Prax' : 'Brigáda'
  editForm.date_start = internship.date_start || ''
  editForm.date_end = internship.date_end || ''

  showEditModal.value = true
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
  showEditModal.value = false
  editingInternship.value = null
  resetEditForm()
  showEditCompanyDropdown.value = false
  showEditYearDropdown.value = false
  showEditSemesterDropdown.value = false
  showEditInternshipTypeDropdown.value = false
}

function resetEditForm() {
  editForm.companySearch = ''
  editForm.selectedCompany = null
  editForm.company_id = null
  editForm.academic_year = ''
  editForm.semester = ''
  editForm.semesterDisplay = ''
  editForm.internship_type = ''
  editForm.internshipTypeDisplay = ''
  editForm.date_start = ''
  editForm.date_end = ''
}

async function submitEditForm() {
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

  editSubmitting.value = true

  try {
    const payload = {
      company_id: editForm.company_id,
      academic_year: editForm.academic_year,
      semester: parseInt(editForm.semester),
      internship_type: editForm.internship_type,
      date_start: editForm.date_start,
      date_end: editForm.date_end
    }

    await api.put(`/internships/${editingInternship.value.id}`, payload)
    
    showAlert('Prax bola úspešne upravená!', 'success')
    
    closeEditModal()
    
    // Refresh internships list
    await fetchInternships()

  } catch (err) {
    showAlert(err.response?.data?.message || 'Nepodarilo sa upraviť prax.', 'error')
    console.error('Error editing internship:', err)
  } finally {
    editSubmitting.value = false
  }
}

// ============================================================
// API CALLS
// ============================================================
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
    
    // Backend gets student from auth()->user(), so we can pass user.id or nothing
    const response = await api.get(`/student-internships/${user.id}`)
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

// ============================================================
// MODAL FUNCTIONS
// ============================================================
function viewDetails(internship) {
  selected.value = internship
}

function closeModal() {
  selected.value = null
}

// ============================================================
// ACTION FUNCTIONS
// ============================================================
async function manageDocuments() {
  // Open document modal with list of all internships
  showAlert('Vyberte prax zo zoznamu pre správu dokumentov kliknutím na tlačidlo "Nahrať".', 'info')
}

async function uploadDocument(internship) {
  documentModalInternship.value = internship
  showDocumentModal.value = true
  await fetchDocuments(internship.id)
  await fetchDocumentTypes()
}

async function fetchDocuments(internshipId) {
  documentLoading.value = true
  try {
    const response = await api.get(`/student/internships/${internshipId}/documents`)
    documents.value = response.data.documents || []
  } catch (err) {
    showAlert('Nepodarilo sa načítať dokumenty.', 'error')
    console.error('Error fetching documents:', err)
  } finally {
    documentLoading.value = false
  }
}

async function fetchDocumentTypes() {
  try {
    const response = await api.get('/student/document-types')
    documentTypes.value = response.data.documentTypes || []
  } catch (err) {
    showAlert('Nepodarilo sa načítať typy dokumentov.', 'error')
    console.error('Error fetching document types:', err)
  }
}

function handleFileSelect(event) {
  const file = event.target.files[0]
  if (file) {
    // Check file size (10MB max)
    if (file.size > 10 * 1024 * 1024) {
      showAlert('Súbor je príliš veľký. Maximálna veľkosť je 10MB.', 'error')
      event.target.value = ''
      return
    }
    uploadForm.file = file
  }
}

function formatFileSize(bytes) {
  if (!bytes) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}

async function submitUploadDocument() {
  if (!uploadForm.file) {
    showAlert('Musíte vybrať súbor.', 'error')
    return
  }

  if (!uploadForm.document_type_id) {
    showAlert('Musíte vybrať typ dokumentu.', 'error')
    return
  }

  uploadSubmitting.value = true

  try {
    const formData = new FormData()
    formData.append('file', uploadForm.file)
    formData.append('document_type_id', uploadForm.document_type_id)
    if (uploadForm.document_name) {
      formData.append('document_name', uploadForm.document_name)
    }
    if (uploadForm.description) {
      formData.append('description', uploadForm.description)
    }

    await api.post(`/student/internships/${documentModalInternship.value.id}/documents`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    showAlert('Dokument bol úspešne nahraný!', 'success')

    // Reset form
    resetUploadForm()

    // Refresh documents list
    await fetchDocuments(documentModalInternship.value.id)

    // Refresh internships to update document counts
    await fetchInternships()

  } catch (err) {
    showAlert(err.response?.data?.message || 'Nepodarilo sa nahrať dokument.', 'error')
    console.error('Error uploading document:', err)
  } finally {
    uploadSubmitting.value = false
  }
}

function resetUploadForm() {
  uploadForm.document_type_id = ''
  uploadForm.document_name = ''
  uploadForm.description = ''
  uploadForm.file = null

  // Reset file input
  const fileInput = document.getElementById('file')
  if (fileInput) {
    fileInput.value = ''
  }
}

async function downloadDocumentFile(doc) {
  try {
    const response = await api.get(`/student/documents/${doc.id}/download`, {
      responseType: 'blob'
    })

    // Create download link
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', doc.file_name)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)

    showAlert('Dokument bol stiahnutý.', 'success')
  } catch (err) {
    showAlert('Nepodarilo sa stiahnuť dokument.', 'error')
    console.error('Error downloading document:', err)
  }
}

function canDeleteDocument(doc) {
  // Allow deletion of all documents
  return true
}

function confirmDeleteDocument(doc) {
  documentToDelete.value = doc
  showDeleteConfirm.value = true
}

function cancelDelete() {
  documentToDelete.value = null
  showDeleteConfirm.value = false
}

async function executeDelete() {
  if (!documentToDelete.value) return

  deleteSubmitting.value = true

  try {
    const response = await api.delete(`/student/documents/${documentToDelete.value.id}`)

    console.log('Delete response:', response)

    // Close confirmation modal first
    cancelDelete()

    // Show success message
    showAlert('Dokument bol vymazaný.', 'success')

    // Refresh documents list
    await fetchDocuments(documentModalInternship.value.id)

    // Refresh internships to update document counts
    await fetchInternships()

  } catch (err) {
    console.error('Error deleting document:', err)
    console.error('Error response:', err.response)
    showAlert(err.response?.data?.message || 'Nepodarilo sa vymazať dokument.', 'error')
  } finally {
    deleteSubmitting.value = false
  }
}

function closeDocumentModal() {
  showDocumentModal.value = false
  documentModalInternship.value = null
  documents.value = []
  resetUploadForm()
}

// Download Dohoda PDF
async function downloadDohoda(internship) {
  try {
    const response = await api.get(`/internships/${internship.id}/generate-dohoda`, {
      responseType: 'blob'
    })
    
    // Create download link
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    
    // Generate filename
    const filename = `Dohoda_${internship.company?.company_name || 'Prax'}_${internship.academic_year}.pdf`
      .replace(/[^A-Za-z0-9_\-\.]/g, '_')
    
    link.setAttribute('download', filename)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
    
    showAlert('Dohoda bola úspešne stiahnutá.', 'success')
  } catch (err) {
    showAlert(err.response?.data?.message || 'Nepodarilo sa stiahnuť dohodu.', 'error')
    console.error('Error downloading Dohoda:', err)
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
  fetchCompanies()
  
  // Set current academic year as default
  createForm.academic_year = currentAcademicYear.value
  
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
  background: linear-gradient(135deg, #38a169 0%, #2d5f4e 100%);
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
  color: #ffffff;
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
.documents-btn,
.create-btn {
  padding: 10px 18px;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.refresh-btn {
  background: #38a169;
  color: white;
}

.refresh-btn:hover:not(:disabled) {
  background: #2f855a;
  transform: translateY(-1px);
}

.refresh-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.documents-btn {
  background: #4299e1;
  color: white;
}

.documents-btn:hover {
  background: #3182ce;
  transform: translateY(-1px);
}

.create-btn {
  background: #f59e0b;
  color: white;
}

.create-btn:hover {
  background: #d97706;
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
.filter-group input {
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
  border-color: #38a169;
  box-shadow: 0 0 0 2px rgba(56, 161, 105, 0.2);
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
  flex-wrap: wrap;
  gap: 12px;
}

.stat {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  min-width: 100px;
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

.stat-value.success {
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
  color: #38a169;
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

.badge.badge-info {
  background: #dbeafe;
  color: #1e40af;
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
  min-width: 200px;
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
  color: #6b7280;
  border: 1px solid #d1d5db;
  margin-right: 6px;
}

button.ghost:hover:not(:disabled) {
  background: #f3f4f6;
  color: #374151;
  border-color: #9ca3af;
}

button.edit-btn {
  background: #10b981;
  color: white;
  margin-right: 6px;
}

button.edit-btn:hover:not(:disabled) {
  background: #13ac37;
  transform: translateY(-1px);
}

button.upload-btn {
  background: #8b5cf6;
  color: white;
}

button.upload-btn:hover:not(:disabled) {
  background: #7c3aed;
  transform: translateY(-1px);
}

button.dohoda-btn {
  background: #3b82f6;
  color: white;
}

button.dohoda-btn:hover:not(:disabled) {
  background: #2563eb;
  transform: translateY(-1px);
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
  background: #38a169;
  color: white;
}

.clear-btn:hover {
  background: #2f855a;
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
  border-top-color: #38a169;
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

/* Mobile Responsiveness */
@media (max-width: 768px) {
  .panel-header {
    flex-direction: column;
    align-items: stretch;
    padding: 16px;
  }

  .panel-header h1 {
    font-size: 22px;
  }

  .actions {
    flex-direction: column;
    width: 100%;
  }

  .search-input,
  .actions button,
  .create-btn,
  .documents-btn {
    width: 100%;
    min-width: auto;
  }

  .advanced-filters {
    grid-template-columns: 1fr;
    padding: 16px;
  }

  .stats-bar {
    padding: 16px;
    gap: 12px;
  }

  .stat {
    flex: 1;
    min-width: 45%;
  }

  .table-wrap {
    padding: 12px;
  }

  .applications-table {
    font-size: 12px;
  }

  .applications-table th,
  .applications-table td {
    padding: 10px 8px;
  }

  .modal-backdrop {
    padding: 0;
    align-items: flex-start;
  }

  .modal-card {
    max-width: 100%;
    min-height: 100vh;
    border-radius: 0;
  }

  .modal-header {
    padding: 16px;
    position: sticky;
    top: 0;
    z-index: 10;
  }

  .modal-body {
    padding: 16px;
    max-height: none;
  }

  .modal-footer {
    padding: 12px 16px;
    position: sticky;
    bottom: 0;
    z-index: 10;
    flex-direction: column;
  }

  .modal-footer button {
    width: 100%;
  }

  .panel-footer {
    flex-direction: column;
    gap: 12px;
    padding: 12px 16px;
  }

  .pagination {
    flex-wrap: wrap;
    justify-content: center;
  }

  .page-info {
    width: 100%;
    text-align: center;
  }

  .page-size-selector {
    width: 100%;
    justify-content: center;
  }
}

/* Create Modal Styles */
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

.modal-content.create-modal {
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

.form-group {
  margin-bottom: 20px;
  position: relative;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #374151;
  font-size: 14px;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 12px 14px;
  border-radius: 8px;
  border: 1px solid #d1d5db;
  font-size: 14px;
  transition: all 0.2s;
  background: white;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #38a169;
  box-shadow: 0 0 0 3px rgba(56, 161, 105, 0.1);
}

.autocomplete-wrapper {
  position: relative;
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

.modal-content .submit-btn {
  background: #38a169;
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
  background: #2f855a;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(56, 161, 105, 0.3);
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

/* Mobile Create Modal */
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

  .form-group {
    margin-bottom: 18px;
  }

  .form-group input,
  .form-group select {
    font-size: 16px; /* Prevent iOS zoom */
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

  .form-group {
    margin-bottom: 16px;
  }

  .form-group label {
    font-size: 13px;
  }
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

/* Document Management Modal Styles */
.modal-content.document-modal {
  max-width: 900px;
}

.upload-section {
  background: #f9fafb;
  padding: 24px;
  border-radius: 12px;
  margin-bottom: 32px;
  border: 2px dashed #d1d5db;
}

.upload-section h3 {
  margin: 0 0 16px 0;
  font-size: 18px;
  font-weight: 700;
  color: #1f2937;
}

.upload-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.form-group textarea {
  width: 100%;
  padding: 12px 14px;
  border-radius: 8px;
  border: 1px solid #d1d5db;
  font-size: 14px;
  font-family: inherit;
  resize: vertical;
  transition: all 0.2s;
}

.form-group textarea:focus {
  outline: none;
  border-color: #38a169;
  box-shadow: 0 0 0 3px rgba(56, 161, 105, 0.1);
}

.file-input-group input[type="file"] {
  padding: 10px;
  border: 2px solid #d1d5db;
  border-radius: 8px;
  background: white;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.2s;
}

.file-input-group input[type="file"]:hover {
  border-color: #38a169;
}

.file-info {
  margin-top: 8px;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
}

.file-name {
  color: #1f2937;
  font-weight: 600;
}

.file-size {
  color: #6b7280;
}

.documents-section {
  margin-top: 24px;
}

.documents-section h3 {
  margin: 0 0 16px 0;
  font-size: 18px;
  font-weight: 700;
  color: #1f2937;
}

.empty-documents {
  text-align: center;
  padding: 40px 20px;
  color: #9ca3af;
  background: #f9fafb;
  border-radius: 8px;
}

.documents-grid {
  display: grid;
  gap: 16px;
}

.document-card {
  display: flex;
  gap: 16px;
  padding: 16px;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  transition: all 0.2s;
}

.document-card:hover {
  border-color: #38a169;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
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

.document-actions {
  display: flex;
  flex-direction: column;
  gap: 8px;
  flex-shrink: 0;
}

.document-actions button {
  padding: 8px 12px;
  font-size: 12px;
  white-space: nowrap;
}

.download-btn {
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}

.download-btn:hover {
  background: #2563eb;
  transform: translateY(-1px);
}

.delete-btn {
  background: #ef4444;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}

.delete-btn:hover {
  background: #dc2626;
  transform: translateY(-1px);
}

/* Delete Confirmation Modal */
.modal-card.confirm-modal {
  max-width: 500px;
}

.warning-text {
  color: #ef4444;
  font-weight: 600;
  margin-top: 8px;
  font-size: 14px;
}

/* Detail View Document Cards */
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
  border-color: #38a169;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
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
  transition: all 0.2s;
}

.download-btn-detail:hover {
  background: #2563eb;
  transform: translateY(-1px);
}

.delete-confirm-btn {
  background: #ef4444;
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.delete-confirm-btn:hover:not(:disabled) {
  background: #dc2626;
  transform: translateY(-1px);
}

.delete-confirm-btn:disabled {
  background: #9ca3af;
  cursor: not-allowed;
}

/* Delete Confirmation Modal - Higher z-index to appear on top */
.modal-backdrop.delete-confirm-backdrop {
  z-index: 3000;
}

/* Responsive Document Modal */
@media (max-width: 768px) {
  .modal-content.document-modal {
    max-width: 100%;
    max-height: 100vh;
    border-radius: 0;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .document-card {
    flex-direction: column;
  }

  .document-actions {
    flex-direction: row;
    width: 100%;
  }

  .document-actions button {
    flex: 1;
  }
}
</style>