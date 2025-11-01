<template>
  <div class="dashboard-page">
    <div class="dashboard-container">
      <div class="panel-card">
        <header class="panel-header">
          <h1>Schvaľovanie odbornej praxe</h1>

          <div class="actions">
            <input
              v-model="filters.search"
              @input="applyFilters"
              placeholder="Hľadaj študenta, fakultu alebo firmu..."
              aria-label="Search student, faculty or company"
            />
            <select v-model="filters.status" @change="applyFilters" aria-label="Filter status">
              <option value="">Všetky</option>
              <option value="pending">Čaká</option>
              <option value="approved">Schválené</option>
              <option value="rejected">Zamietnuté</option>
            </select>
            <button @click="fetchApplications" :disabled="loading">Aktualizovať</button>
          </div>
        </header>

        <section class="table-wrap">
          <table class="applications-table" v-if="!loading && applications.length">
            <thead>
              <tr>
                <th>Študent</th>
                <th>Fakulta / Program</th>
                <th>Firma</th>
                <th>Termín</th>
                <th>Stav</th>
                <th class="actions-col">Akcie</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="app in paginated" :key="app.id">
                <td>
                  <div class="name">{{ app.student_name }}</div>
                  <div class="muted">{{ app.student_email }}</div>
                </td>
                <td>
                  <div>{{ app.school }}</div>
                  <div class="muted">{{ app.course }}</div>
                </td>
                <td>
                  <div>{{ app.company || '—' }}</div>
                </td>
                <td>{{ formatDate(app.start_date) }} → {{ formatDate(app.end_date) }}</td>
                <td>
                  <span :class="statusBadge(app.status)">{{ prettyStatus(app.status) }}</span>
                </td>
                <td class="actions-col">
                  <button class="ghost" @click="viewDetails(app)">Detail</button>
                  <button
                    class="approve"
                    @click="confirmApprove(app)"
                    :disabled="app.status !== 'pending' || processing[app.id]"
                  >
                    ✓
                  </button>
                  <button
                    class="reject"
                    @click="confirmReject(app)"
                    :disabled="app.status !== 'pending' || processing[app.id]"
                  >
                    ✕
                  </button>
                </td>
              </tr>
            </tbody>
          </table>

          <div v-if="!loading && !applications.length" class="empty">
            Žiadne žiadosti nenájdené.
          </div>

          <div v-if="loading" class="loading">
            Načítavam žiadosti...
          </div>
        </section>

        <footer class="panel-footer" v-if="totalPages > 1">
          <div class="pagination">
            <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1">Predchádzajúca</button>
            <span>Strana {{ currentPage }} z {{ totalPages }}</span>
            <button @click="changePage(currentPage + 1)" :disabled="currentPage === totalPages">Ďalšia</button>
          </div>
        </footer>
      </div>
    </div>

    <!-- Detail modal -->
    <div v-if="selected" class="modal-backdrop" @click.self="selected = null">
      <div class="modal-card">
        <header class="modal-header">
          <h2>Detail žiadosti</h2>
          <button class="close" @click="selected = null">✕</button>
        </header>

        <div class="modal-body">
          <p><strong>Študent:</strong> {{ selected.student_name }} ({{ selected.student_email }})</p>
          <p><strong>Fakulta / Program:</strong> {{ selected.school }} — {{ selected.course }}</p>
          <p><strong>Firma:</strong> {{ selected.company || '—' }}</p>
          <p><strong>Termín praxe:</strong> {{ formatDate(selected.start_date) }} — {{ formatDate(selected.end_date) }}</p>
          <p><strong>Popis práce:</strong></p>
          <div class="description">{{ selected.description }}</div>

          <p><strong>Dokumenty:</strong></p>
          <ul>
            <li v-for="(doc, i) in selected.documents || []" :key="i">
              <a :href="doc.url" target="_blank" rel="noopener">{{ doc.name }}</a>
            </li>
            <li v-if="!(selected.documents && selected.documents.length)">Žiadne dokumenty.</li>
          </ul>
        </div>

        <footer class="modal-footer">
          <button class="ghost" @click="selected = null">Zavrieť</button>
          <button class="approve" @click="confirmApprove(selected)" :disabled="processing[selected.id]">Schváliť</button>
          <button class="reject" @click="confirmReject(selected)" :disabled="processing[selected.id]">Zamietnuť</button>
        </footer>
      </div>
    </div>
  </div>
</template>

<script setup>
/*
  InternshipApproval.vue
  - Rozhranie pre firmu: zobrazenie a schvaľovanie žiadostí o prax
  - Vyhľadávanie podľa študenta, fakulty aj firmy
*/

import { reactive, ref, computed, onMounted } from 'vue'
import axios from 'axios'

// --- Stav ---
const applications = ref([])
const loading = ref(false)
const processing = reactive({})
const selected = ref(null)

// --- Filtre & stránkovanie ---
const filters = reactive({
  search: '',
  status: '',
})
const pageSize = 8
const currentPage = ref(1)
const error = ref(null)

// --- Axios ---
const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  timeout: 10000,
})

const token = localStorage.getItem('token')
if (token) api.defaults.headers.common['Authorization'] = `Bearer ${token}`

// --- Pomocné funkcie ---
function formatDate(d) {
  if (!d) return '-'
  const dt = new Date(d)
  if (isNaN(dt)) return d
  return dt.toLocaleDateString('sk-SK', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

function prettyStatus(s) {
  if (s === 'pending') return 'Čaká'
  if (s === 'approved') return 'Schválené'
  if (s === 'rejected') return 'Zamietnuté'
  return s
}

function statusBadge(s) {
  return {
    badge: true,
    'badge-pending': s === 'pending',
    'badge-approved': s === 'approved',
    'badge-rejected': s === 'rejected',
  }
}

// --- API volania ---
async function fetchApplications() {
  loading.value = true
  error.value = null
  try {
    const params = {}
    if (filters.search) params.search = filters.search
    if (filters.status) params.status = filters.status

    const res = await api.get('/internships', { params })
    applications.value = Array.isArray(res.data) ? res.data : res.data.data || []
    currentPage.value = 1
  } catch (err) {
    console.error(err)
    error.value = 'Chyba pri načítaní žiadostí.'
  } finally {
    loading.value = false
  }
}

function applyFilters() {
  fetchApplications()
}

function viewDetails(app) {
  selected.value = app
}

// --- Akcie ---
async function confirmApprove(app) {
  if (!confirm(`Schváliť prax pre ${app.student_name}?`)) return
  await performAction(app.id, 'approve')
}

async function confirmReject(app) {
  if (!confirm(`Zamietnuť prax pre ${app.student_name}?`)) return
  const reason = prompt('Zadaj dôvod zamietnutia (voliteľné):', '')
  await performAction(app.id, 'reject', { reason })
}

async function performAction(id, action, payload = {}) {
  processing[id] = true
  try {
    const endpoint =
      action === 'approve'
        ? `/internships/${id}/approve`
        : `/internships/${id}/reject`

    await api.post(endpoint, payload)

    const idx = applications.value.findIndex(a => a.id === id)
    if (idx !== -1) {
      applications.value[idx].status = action === 'approve' ? 'approved' : 'rejected'
    }
    if (selected.value && selected.value.id === id) {
      selected.value.status = action === 'approve' ? 'approved' : 'rejected'
    }
  } catch (err) {
    console.error(err)
    alert('Akcia zlyhala. Skontroluj konzolu.')
  } finally {
    processing[id] = false
  }
}

// --- Pagination ---
const totalPages = computed(() => Math.max(1, Math.ceil(applications.value.length / pageSize)))

const paginated = computed(() => {
  const start = (currentPage.value - 1) * pageSize
  return applications.value.slice(start, start + pageSize)
})

function changePage(n) {
  if (n < 1 || n > totalPages.value) return
  currentPage.value = n
}

// --- Mount ---
onMounted(() => {
  fetchApplications()
})
</script>

<style scoped>
/* (Štýly nezmenené, ako v pôvodnej verzii) */

/* Global page background + font */
html, body, .dashboard-page {
  height: 100%;
  margin: 0;
  background: linear-gradient(135deg, #76cbec 0%, #607d9b 100%);
  font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
  color: #2c3e50;
}

/* Container */
.dashboard-container {
  display: flex;
  justify-content: center;
  align-items: flex-start;
  min-height: calc(100vh - 80px);
  padding: 28px;
}

/* Card */
.panel-card {
  width: 100%;
  max-width: 1000px;
  background: #fff;
  padding: 22px;
  border-radius: 14px;
  box-shadow: 0 10px 30px rgba(12, 38, 52, 0.12);
  animation: fadeIn 0.45s ease;
}

/* Header */
.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  margin-bottom: 14px;
}

.panel-header h1 {
  font-size: 20px;
  margin: 0;
}

/* Actions */
.actions {
  display: flex;
  gap: 10px;
  align-items: center;
}

.actions input {
  padding: 10px 12px;
  border-radius: 10px;
  border: 1px solid #e6e9ee;
  min-width: 260px;
}

.actions select {
  padding: 10px 12px;
  border-radius: 10px;
  border: 1px solid #e6e9ee;
}

.actions button {
  padding: 10px 14px;
  border-radius: 10px;
  border: none;
  background: #42b883;
  color: #fff;
  font-weight: 600;
  cursor: pointer;
}

.actions button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Table */
.table-wrap {
  margin-top: 8px;
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
  border-bottom: 1px solid #eef2f6;
}

.applications-table tbody td {
  padding: 12px 10px;
  border-bottom: 1px solid #f4f7fa;
  vertical-align: middle;
}

.name { font-weight: 700; }
.muted { color: #7b8794; font-size: 13px; margin-top: 4px; }

/* Badges */
.badge {
  display: inline-block;
  padding: 6px 10px;
  border-radius: 999px;
  font-weight: 700;
  font-size: 12px;
}

.badge-pending { background: #fff4e6; color: #b76f00; border: 1px solid #ffe6b8; }
.badge-approved { background: #e9f9f1; color: #0f7a4d; border: 1px solid #bfeed2; }
.badge-rejected { background: #ffecec; color: #a42b2b; border: 1px solid #ffcdcd; }

/* Actions column */
.actions-col {
  display: flex;
  gap: 8px;
  justify-content: flex-end;
  min-width: 160px;
}

button.ghost {
  background: transparent;
  border: 1px solid #e6eef2;
  padding: 8px 10px;
  border-radius: 8px;
  cursor: pointer;
}

button.approve {
  background: #42b883;
  color: #fff;
  border: none;
  padding: 8px 10px;
  border-radius: 8px;
  font-weight: 700;
  cursor: pointer;
}

button.reject {
  background: #ff6b6b;
  color: #fff;
  border: none;
  padding: 8px 10px;
  border-radius: 8px;
  font-weight: 700;
  cursor: pointer;
}

/* Empty & loading */
.empty, .loading {
  padding: 36px;
  text-align: center;
  color: #6b7280;
}

/* Pagination */
.panel-footer {
  margin-top: 14px;
  display: flex;
  justify-content: center;
  align-items: center;
}

.pagination {
  display: flex;
  gap: 12px;
  align-items: center;
}

.pagination button {
  padding: 8px 12px;
  border-radius: 8px;
  border: none;
  background: #2c3e50;
  color: #fff;
  cursor: pointer;
}

/* Modal */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(12, 20, 30, 0.45);
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 24px;
}

.modal-card {
  width: 100%;
  max-width: 760px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 20px 50px rgba(6, 30, 45, 0.3);
  overflow: hidden;
}

.modal-header, .modal-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 14px 18px;
  border-bottom: 1px solid #f1f5f9;
}

.modal-header h2 { margin: 0; font-size: 18px; }

.modal-body {
  padding: 18px;
  font-size: 14px;
  color: #253244;
}

.modal-body .description {
  background: #fbfbfd;
  padding: 12px;
  border-radius: 8px;
  border: 1px solid #eef2f7;
  margin-bottom: 12px;
}

.modal-footer {
  border-top: 1px solid #f1f5f9;
  gap: 10px;
  padding: 12px 18px;
}

button.close {
  background: transparent;
  border: none;
  font-size: 18px;
  cursor: pointer;
}

@media (max-width: 720px) {
  .actions input { min-width: 140px; }
  .actions { flex-wrap: wrap; gap: 8px; }
  .actions-col { min-width: auto; }
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(8px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
