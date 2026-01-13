<template>
  <div class="pending-companies-page">
    <PageAlert
      v-if="alert.show"
      :type="alert.type"
      :message="alert.message"
      :duration="alert.duration"
      @close="alert.show = false"
      dismissible
    />

    <div class="panel-card">
      <header class="panel-header">
        <h1>Firmy čakajúce na schválenie</h1>
        <button @click="fetchPendingCompanies" :disabled="loading" class="refresh-btn">
          {{ loading ? 'Načítavam...' : 'Aktualizovať' }}
        </button>
      </header>

      <Spinner v-if="loading" overlay />

      <div v-if="!loading && companies.length === 0" class="no-data">
        <p>Žiadne firmy nečakajú na schválenie.</p>
      </div>

      <div v-else class="companies-grid">
        <div v-for="company in companies" :key="company.id" class="company-card">
          <div class="company-header">
            <h2>{{ company.company_name }}</h2>
            <span class="badge pending">Čaká na schválenie</span>
          </div>

          <div class="company-details">
            <div class="detail-section">
              <h3>Kontaktná osoba</h3>
              <p><strong>Meno:</strong> {{ company.contact_person_name }} {{ getContactLastName(company) }}</p>
              <p><strong>Email:</strong> {{ company.contact_person_email }}</p>
              <p><strong>Telefón:</strong> {{ company.contact_person_phone }}</p>
            </div>

            <div class="detail-section">
              <h3>Adresa firmy</h3>
              <p><strong>Ulica:</strong> {{ company.address.street }} {{ company.address.street_number }}</p>
              <p><strong>Mesto:</strong> {{ company.address.city }}</p>
              <p><strong>PSČ:</strong> {{ company.address.postal_code }}</p>
              <p><strong>Krajina:</strong> {{ company.address.country }}</p>
            </div>

            <div class="detail-section">
              <h3>Dátum registrácie</h3>
              <p>{{ formatDate(company.created_at) }}</p>
            </div>
          </div>

          <div class="company-actions">
            <button
              @click="approveCompany(company.id)"
              :disabled="actionInProgress === company.id"
              class="approve-btn"
            >
              <span v-if="actionInProgress === company.id">Schvaľujem...</span>
              <span v-else>✓ Schváliť</span>
            </button>
            <button
              @click="openRejectDialog(company)"
              :disabled="actionInProgress === company.id"
              class="reject-btn"
            >
              <span v-if="actionInProgress === company.id">Spracovávam...</span>
              <span v-else>✗ Zamietnuť</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Reject Dialog -->
    <div v-if="showRejectDialog" class="modal-overlay" @click="closeRejectDialog">
      <div class="modal-content" @click.stop>
        <h2>Zamietnuť firmu</h2>
        <p>Ste si istí, že chcete zamietnuť registráciu firmy <strong>{{ selectedCompany?.company_name }}</strong>?</p>

        <div class="form-group">
          <label for="reject-reason">Dôvod zamietnutia (voliteľné):</label>
          <textarea
            id="reject-reason"
            v-model="rejectReason"
            placeholder="Uveďte dôvod zamietnutia..."
            rows="4"
          ></textarea>
        </div>

        <div class="modal-actions">
          <button @click="closeRejectDialog" class="cancel-btn">Zrušiť</button>
          <button @click="rejectCompany" class="confirm-reject-btn">Potvrdiť zamietnutie</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/api'
import PageAlert from '@/components/PageAlert.vue'
import Spinner from '@/components/Spinner.vue'

const companies = ref([])
const loading = ref(false)
const actionInProgress = ref(null)
const alert = ref({ show: false, type: 'info', message: '', duration: 5000 })

const showRejectDialog = ref(false)
const selectedCompany = ref(null)
const rejectReason = ref('')

function showAlert(message, type = 'info', duration = 5000) {
  alert.value = { show: true, type, message, duration }
}

function getContactLastName(company) {
  const user = company.users?.find(u => u.email === company.contact_person_email)
  return user?.last_name || ''
}

function formatDate(dateString) {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('sk-SK', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

async function fetchPendingCompanies() {
  loading.value = true
  try {
    const response = await api.get('/guarantor/pending-companies')
    companies.value = response.data.companies
  } catch (err) {
    console.error('Failed to fetch pending companies:', err)
    showAlert('Nepodarilo sa načítať čakajúce firmy.', 'error')
  } finally {
    loading.value = false
  }
}

async function approveCompany(companyId) {
  actionInProgress.value = companyId
  try {
    await api.post(`/guarantor/companies/${companyId}/approve`)
    showAlert('Firma bola úspešne schválená!', 'success')
    // Remove from list
    companies.value = companies.value.filter(c => c.id !== companyId)
  } catch (err) {
    console.error('Failed to approve company:', err)
    showAlert(
      err.response?.data?.message || 'Nepodarilo sa schváliť firmu.',
      'error'
    )
  } finally {
    actionInProgress.value = null
  }
}

function openRejectDialog(company) {
  selectedCompany.value = company
  rejectReason.value = ''
  showRejectDialog.value = true
}

function closeRejectDialog() {
  showRejectDialog.value = false
  selectedCompany.value = null
  rejectReason.value = ''
}

async function rejectCompany() {
  if (!selectedCompany.value) return

  actionInProgress.value = selectedCompany.value.id
  try {
    await api.post(`/guarantor/companies/${selectedCompany.value.id}/reject`, {
      reason: rejectReason.value.trim() || null
    })
    showAlert('Firma bola zamietnutá a odstránená zo systému.', 'success')
    // Remove from list
    companies.value = companies.value.filter(c => c.id !== selectedCompany.value.id)
    closeRejectDialog()
  } catch (err) {
    console.error('Failed to reject company:', err)
    showAlert(
      err.response?.data?.message || 'Nepodarilo sa zamietnuť firmu.',
      'error'
    )
  } finally {
    actionInProgress.value = null
  }
}

onMounted(() => {
  fetchPendingCompanies()
})
</script>

<style scoped>
.pending-companies-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #ffb74d 0%, #ff8a65 100%);
  padding: 24px;
  font-family: 'Inter', 'Segoe UI', sans-serif;
}

@media (max-width: 768px) {
  .pending-companies-page {
    padding: 12px;
  }
}

.panel-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
  overflow: hidden;
  animation: fadeIn 0.6s ease;
  max-width: 1400px;
  margin: 0 auto;
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
  font-size: 26px;
  font-weight: 700;
  letter-spacing: -0.5px;
  color: #fff;
  margin: 0;
}

.refresh-btn {
  padding: 10px 20px;
  background: #42b883;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  transition: background 0.3s ease;
}

.refresh-btn:hover:not(:disabled) {
  background: #369f73;
}

.refresh-btn:disabled {
  background: #95d5b2;
  cursor: not-allowed;
}

.no-data {
  text-align: center;
  padding: 60px 20px;
  color: #6b7280;
}

.no-data p {
  font-size: 18px;
  margin: 0;
}

.companies-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(500px, 1fr));
  gap: 24px;
  padding: 24px;
}

.company-card {
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  padding: 20px;
  background: #fafafa;
  transition: box-shadow 0.3s ease;
}

.company-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.company-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
  padding-bottom: 12px;
  border-bottom: 1px solid #ddd;
}

.company-header h2 {
  font-size: 20px;
  color: #2c3e50;
  margin: 0;
}

.badge {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

.badge.pending {
  background: #fff3cd;
  color: #856404;
}

.company-details {
  margin-bottom: 20px;
}

.detail-section {
  margin-bottom: 16px;
}

.detail-section h3 {
  font-size: 14px;
  color: #42b883;
  margin: 0 0 8px 0;
  text-transform: uppercase;
  font-weight: 700;
}

.detail-section p {
  font-size: 14px;
  margin: 4px 0;
  color: #333;
}

.company-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
}

.approve-btn,
.reject-btn {
  padding: 10px 24px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 14px;
  transition: all 0.3s ease;
}

.approve-btn {
  background: #4CAF50;
  color: white;
}

.approve-btn:hover:not(:disabled) {
  background: #45a049;
  transform: translateY(-2px);
}

.reject-btn {
  background: #f44336;
  color: white;
}

.reject-btn:hover:not(:disabled) {
  background: #da190b;
  transform: translateY(-2px);
}

.approve-btn:disabled,
.reject-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 30px;
  border-radius: 12px;
  max-width: 500px;
  width: 90%;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

.modal-content h2 {
  margin-top: 0;
  color: #2c3e50;
  font-size: 22px;
}

.modal-content p {
  color: #555;
  line-height: 1.6;
}

.form-group {
  margin: 20px 0;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #333;
}

.form-group textarea {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-family: inherit;
  font-size: 14px;
  resize: vertical;
}

.form-group textarea:focus {
  outline: none;
  border-color: #42b883;
  box-shadow: 0 0 0 2px rgba(66, 184, 131, 0.2);
}

.modal-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 24px;
}

.cancel-btn,
.confirm-reject-btn {
  padding: 10px 24px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
}

.cancel-btn {
  background: #e0e0e0;
  color: #333;
}

.cancel-btn:hover {
  background: #d0d0d0;
}

.confirm-reject-btn {
  background: #f44336;
  color: white;
}

.confirm-reject-btn:hover {
  background: #da190b;
}

@media (max-width: 768px) {
  .companies-grid {
    grid-template-columns: 1fr;
  }

  .company-actions {
    flex-direction: column;
  }

  .approve-btn,
  .reject-btn {
    width: 100%;
  }
}
</style>
