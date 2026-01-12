<template>
  <div class="tokens-page">
    <PageAlert
      v-if="alert.show"
      :type="alert.type"
      :message="alert.message"
      :duration="alert.duration"
      @close="alert.show = false"
      dismissible
    />

    <div class="tokens-container">
      <div class="panel-card">
        <header class="panel-header">
          <div>
            <h1>🔐 Tokeny pre externý systém</h1>
            <p class="subtitle">API tokeny pre integráciu s externým obhajobovým systémom</p>
          </div>
          <button class="create-btn" @click="openCreateModal">
            <span class="icon">+</span>
            Vytvoriť token
          </button>
        </header>

        <div class="info-banner">
          <div class="info-icon">ℹ️</div>
          <div class="info-content">
            <strong>Token sa zobrazí iba raz pri vytvorení.</strong> Uložte si ho na bezpečnom mieste.
          </div>
        </div>

        <!-- Tokens Table -->
        <div class="table-section" v-if="!loading && tokens.length">
          <table class="tokens-table">
            <thead>
              <tr>
                <th>Stav</th>
                <th>Oprávnenia</th>
                <th>Vytvorené</th>
                <th>Platnosť do</th>
                <th>Posledné použitie</th>
                <th class="actions-col">Akcie</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="token in tokens" :key="token.id">
                <td>
                  <span :class="getStatusBadge(token)">
                    {{ getStatusText(token) }}
                  </span>
                </td>
                <td>
                  <div class="abilities">
                    <span v-for="ability in token.abilities" :key="ability" class="ability-badge">
                      {{ formatAbility(ability) }}
                    </span>
                  </div>
                </td>
                <td>
                  <div class="date">{{ formatDate(token.created_at) }}</div>
                  <div class="time">{{ formatTime(token.created_at) }}</div>
                </td>
                <td>
                  <div v-if="token.expires_at" class="date">
                    {{ formatDate(token.expires_at) }}
                  </div>
                  <div v-else class="muted">Bez expirácie</div>
                </td>
                <td>
                  <div v-if="token.last_used_at" class="date">
                    {{ formatDate(token.last_used_at) }}
                  </div>
                  <div v-else class="muted">Nepoužitý</div>
                </td>
                <td class="actions-col">
                  <button
                    v-if="token.is_active"
                    class="revoke-btn"
                    @click="revokeToken(token)"
                    :disabled="processing[token.id]"
                  >
                    {{ processing[token.id] ? 'Ruším...' : 'Zrušiť' }}
                  </button>
                  <button
                    v-else
                    class="delete-btn"
                    @click="deleteToken(token)"
                    :disabled="processing[token.id]"
                  >
                    Vymazať
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="!loading && !tokens.length" class="empty-state">
          <div class="empty-icon">🔑</div>
          <h3>Zatiaľ žiadne tokeny</h3>
          <p>Vytvorte prvý token pre externý obhajobový systém.</p>
          <button class="create-btn" @click="openCreateModal">
            Vytvoriť token
          </button>
        </div>

        <div v-if="loading" class="loading-state">
          <div class="spinner"></div>
          <p>Načítavam tokeny...</p>
        </div>
      </div>

      <!-- API Documentation Card -->
      <div class="panel-card api-docs">
        <header class="docs-header">
          <h2>📖 API Dokumentácia</h2>
        </header>
        <div class="docs-content">
          <div class="endpoint-section">
            <h3>API Endpoint</h3>
            
            <div class="endpoint-box">
              <span class="method">POST</span>
              <code class="url">{{ apiBaseUrl }}/external/mark-defended/{internship_id}</code>
            </div>

            <p class="endpoint-description">
              Zmení stav praxe zo <strong>"Schválená"</strong> na <strong>"Obhájená"</strong>.
            </p>

            <h4>Autentifikácia:</h4>
            <pre class="code-block">Authorization: Bearer YOUR_API_TOKEN</pre>

            <h4>Request body (voliteľné):</h4>
            <pre class="code-block">{
  "notes": "Poznámka k obhajobe (max 500 znakov)"
}</pre>

            <h4>Príklady použitia:</h4>
            
            <pre class="code-block"># Bez poznámky
curl -X POST {{ apiBaseUrl }}/external/mark-defended/123 \
  -H "Authorization: Bearer YOUR_TOKEN"

# S poznámkou
curl -X POST {{ apiBaseUrl }}/external/mark-defended/123 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"notes": "Obhájené s hodnotením A"}'</pre>

            <h4>Odpoveď:</h4>
            <pre class="code-block">{
  "success": true,
  "data": {
    "internship_id": 123,
    "old_status": "Schválená",
    "new_status": "Obhájená",
    "changed_at": "2024-06-15T14:30:00Z"
  }
}</pre>

            <h4>Podmienky:</h4>
            <ul class="requirements-list">
              <li>Prax musí byť v stave <strong>"Schválená"</strong></li>
              <li>Token musí mať oprávnenie <code>internship:defend</code></li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Token Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-card">
        <header class="modal-header">
          <h2>Vytvoriť nový token</h2>
          <button class="close-btn" @click="closeModal">✕</button>
        </header>

        <div class="modal-body">
          <!-- Token Created Success -->
          <div v-if="createdToken" class="success-section">
            <div class="success-icon">✅</div>
            <h3>Token bol úspešne vytvorený!</h3>
            
            <div class="warning-box">
              <strong>⚠️ Dôležité:</strong>
              <p>Token sa zobrazí iba raz. Uložte si ho teraz!</p>
            </div>

            <div class="token-display">
              <label>Váš API token:</label>
              <div class="token-box">
                <code class="token-value">{{ createdToken }}</code>
                <button 
                  class="copy-btn" 
                  @click="copyToken"
                  :class="{ copied: tokenCopied }"
                >
                  {{ tokenCopied ? '✓ Skopírované' : '📋 Kopírovať' }}
                </button>
              </div>
            </div>

            <div class="token-info">
              <div class="info-row">
                <span class="label">Oprávnenia:</span>
                <span class="value">
                  <span v-for="ability in form.abilities" :key="ability" class="ability-badge">
                    {{ formatAbility(ability) }}
                  </span>
                </span>
              </div>
              <div class="info-row" v-if="form.expires_days">
                <span class="label">Platnosť:</span>
                <span class="value">{{ form.expires_days }} dní (do {{ getExpiryDate() }})</span>
              </div>
              <div class="info-row" v-else>
                <span class="label">Platnosť:</span>
                <span class="value">Bez expirácie</span>
              </div>
            </div>
          </div>

          <!-- Token Creation Form -->
          <form v-else @submit.prevent="createToken" class="token-form">
            <div class="form-group">
              <label>Oprávnenia *</label>
              <div class="checkbox-group">
                <label class="checkbox-label">
                  <input
                    type="checkbox"
                    value="internship:defend"
                    v-model="form.abilities"
                  />
                  <span class="checkbox-text">
                    <strong>internship:defend</strong>
                    <small>Označiť prax ako obhájenú</small>
                  </span>
                </label>
              </div>
              <p class="help-text">Vyberte aspoň jedno oprávnenie.</p>
            </div>

            <div class="form-group">
              <label>Platnosť tokenu</label>
              <select v-model="form.expires_days" class="form-select">
                <option :value="7">7 dní</option>
                <option :value="30">30 dní</option>
                <option :value="90">90 dní</option>
                <option :value="180">180 dní</option>
                <option :value="365">365 dní (1 rok)</option>
                <option :value="null">Bez expirácie</option>
              </select>
              <p class="help-text">Token prestane fungovať po uplynutí tejto doby.</p>
            </div>

            <div class="security-notice">
              <div class="notice-icon">🔒</div>
              <div class="notice-content">
                <strong>Bezpečnosť tokenu:</strong>
                <ul>
                  <li>Nikdy token nezdieľajte verejne</li>
                  <li>Pri kompromitácii okamžite zrušte token</li>
                </ul>
              </div>
            </div>
          </form>
        </div>

        <footer class="modal-footer">
          <button 
            v-if="createdToken"
            class="primary-btn"
            @click="closeModal"
          >
            Rozumiem, uložil som token
          </button>
          <template v-else>
            <button class="secondary-btn" @click="closeModal">
              Zrušiť
            </button>
            <button
              class="primary-btn"
              @click="createToken"
              :disabled="!form.abilities.length || processing.create"
            >
              {{ processing.create ? 'Vytváram...' : 'Vytvoriť token' }}
            </button>
          </template>
        </footer>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import api from '@/api'
import PageAlert from '@/components/PageAlert.vue'

const alert = reactive({ show: false, type: 'error', message: '', duration: 5000 })

function showAlert(message, type = 'error', duration = 5000) {
  alert.message = message
  alert.type = type
  alert.duration = duration
  alert.show = true
}

const tokens = ref([])
const loading = ref(false)
const showModal = ref(false)
const createdToken = ref(null)
const tokenCopied = ref(false)
const processing = reactive({ create: false })

const form = reactive({
  abilities: ['internship:defend'],
  expires_days: 90,
})

const apiBaseUrl = computed(() => {
  return import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api'
})

function formatDate(dateString) {
  if (!dateString) return '—'
  const date = new Date(dateString)
  return date.toLocaleDateString('sk-SK', { 
    day: '2-digit', 
    month: '2-digit', 
    year: 'numeric' 
  })
}

function formatTime(dateString) {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleTimeString('sk-SK', { 
    hour: '2-digit', 
    minute: '2-digit' 
  })
}

function formatAbility(ability) {
  const abilities = {
    'internship:defend': 'Obhájenie praxe'
  }
  return abilities[ability] || ability
}

function getStatusText(token) {
  if (!token.is_active) return 'Expirovaný'
  if (!token.last_used_at) return 'Nevyužitý'
  return 'Aktívny'
}

function getStatusBadge(token) {
  if (!token.is_active) return 'status-badge expired'
  if (!token.last_used_at) return 'status-badge unused'
  return 'status-badge active'
}

function getExpiryDate() {
  if (!form.expires_days) return ''
  const date = new Date()
  date.setDate(date.getDate() + form.expires_days)
  return formatDate(date.toISOString())
}

async function fetchTokens() {
  loading.value = true
  try {
    const response = await api.get('/guarantor/external-system-tokens')
    tokens.value = response.data.tokens || []
  } catch (error) {
    console.error('Failed to fetch tokens:', error)
    showAlert('Nepodarilo sa načítať tokeny.', 'error')
  } finally {
    loading.value = false
  }
}

function openCreateModal() {
  showModal.value = true
  createdToken.value = null
  tokenCopied.value = false
  form.abilities = ['internship:defend']
  form.expires_days = 90
}

function closeModal() {
  showModal.value = false
  setTimeout(() => {
    createdToken.value = null
    tokenCopied.value = false
  }, 300)
}

async function createToken() {
  if (!form.abilities.length) {
    showAlert('Musíte vybrať aspoň jedno oprávnenie.', 'error')
    return
  }

  processing.create = true
  try {
    const response = await api.post('/guarantor/external-system-tokens', {
      abilities: form.abilities,
      expires_days: form.expires_days
    })
    
    createdToken.value = response.data.token
    await fetchTokens()
    showAlert('Token bol úspešne vytvorený.', 'success')
  } catch (error) {
    console.error('Failed to create token:', error)
    showAlert(
      error.response?.data?.message || 'Nepodarilo sa vytvoriť token.',
      'error'
    )
    closeModal()
  } finally {
    processing.create = false
  }
}

async function copyToken() {
  try {
    await navigator.clipboard.writeText(createdToken.value)
    tokenCopied.value = true
    setTimeout(() => {
      tokenCopied.value = false
    }, 2000)
  } catch (error) {
    console.error('Failed to copy token:', error)
    showAlert('Nepodarilo sa skopírovať token.', 'error', 3000)
  }
}

async function revokeToken(token) {
  if (!confirm(`Naozaj chcete zrušiť tento token?\n\nExterný systém už nebude môcť tento token používať.`)) {
    return
  }

  processing[token.id] = true
  try {
    await api.delete(`/guarantor/external-system-tokens/${token.id}`)
    showAlert('Token bol úspešne zrušený.', 'success')
    await fetchTokens()
  } catch (error) {
    console.error('Failed to revoke token:', error)
    showAlert(
      error.response?.data?.message || 'Nepodarilo sa zrušiť token.',
      'error'
    )
  } finally {
    delete processing[token.id]
  }
}

async function deleteToken(token) {
  await revokeToken(token)
}

onMounted(() => {
  fetchTokens()
})
</script>

<style scoped>
* {
  box-sizing: border-box;
}

.tokens-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #ffb74d 0%, #ff8a65 100%);
  padding: 24px;
  font-family: 'Inter', 'Segoe UI', sans-serif;
}

.tokens-container {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 24px;
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
  padding: 32px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 20px;
}

.panel-header h1 {
  margin: 0 0 8px 0;
  font-size: 28px;
  font-weight: 700;
}

.subtitle {
  margin: 0;
  font-size: 14px;
  opacity: 0.9;
  font-weight: 400;
}

.create-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  background: #42b883;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.create-btn:hover {
  background: #369f73;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(66, 184, 131, 0.3);
}

.icon {
  font-size: 20px;
  font-weight: 700;
}

.info-banner {
  display: flex;
  gap: 16px;
  padding: 20px 32px;
  background: #eff6ff;
  border-bottom: 1px solid #bfdbfe;
}

.info-icon {
  font-size: 24px;
  flex-shrink: 0;
}

.info-content {
  font-size: 14px;
  color: #1e40af;
  line-height: 1.6;
}

.info-content strong {
  display: block;
  margin-bottom: 4px;
}

.table-section {
  padding: 24px 32px;
  overflow-x: auto;
}

.tokens-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.tokens-table thead {
  background: #f9fafb;
  border-bottom: 2px solid #e5e7eb;
}

.tokens-table th {
  padding: 14px 12px;
  text-align: left;
  font-weight: 700;
  color: #374151;
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.tokens-table tbody tr {
  border-bottom: 1px solid #e5e7eb;
  transition: background 0.15s;
}

.tokens-table tbody tr:hover {
  background: #f9fafb;
}

.tokens-table td {
  padding: 16px 12px;
  color: #1f2937;
}

.status-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.status-badge.active {
  background: #d1fae5;
  color: #065f46;
}

.status-badge.unused {
  background: #fef3c7;
  color: #92400e;
}

.status-badge.expired {
  background: #fee2e2;
  color: #991b1b;
}

.abilities {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.ability-badge {
  display: inline-block;
  padding: 4px 10px;
  background: #dbeafe;
  color: #1e40af;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
}

.date {
  font-weight: 600;
  color: #1f2937;
}

.time {
  font-size: 12px;
  color: #6b7280;
  margin-top: 2px;
}

.muted {
  color: #9ca3af;
  font-size: 13px;
}

.actions-col {
  text-align: right;
}

.revoke-btn,
.delete-btn {
  padding: 8px 16px;
  border: none;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.revoke-btn {
  background: #ef4444;
  color: white;
}

.revoke-btn:hover:not(:disabled) {
  background: #dc2626;
  transform: translateY(-1px);
}

.delete-btn {
  background: #9ca3af;
  color: white;
}

.delete-btn:hover:not(:disabled) {
  background: #6b7280;
}

.revoke-btn:disabled,
.delete-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.empty-state {
  text-align: center;
  padding: 80px 32px;
}

.empty-icon {
  font-size: 64px;
  margin-bottom: 20px;
}

.empty-state h3 {
  margin: 0 0 12px 0;
  font-size: 22px;
  color: #1f2937;
}

.empty-state p {
  margin: 0 0 24px 0;
  color: #6b7280;
  font-size: 15px;
}

.loading-state {
  text-align: center;
  padding: 80px 32px;
}

.spinner {
  width: 48px;
  height: 48px;
  border: 4px solid #e5e7eb;
  border-top-color: #42b883;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 20px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.loading-state p {
  color: #6b7280;
  font-size: 15px;
}

/* API Documentation */
.api-docs .docs-header {
  padding: 24px 32px;
  background: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
}

.docs-header h2 {
  margin: 0;
  font-size: 20px;
  color: #1f2937;
  font-weight: 700;
}

.docs-content {
  padding: 32px;
}

.endpoint-section h3 {
  margin: 0 0 16px 0;
  font-size: 18px;
  color: #1f2937;
  font-weight: 700;
}

.endpoint-description {
  margin: 0 0 24px 0;
  font-size: 15px;
  color: #4b5563;
  line-height: 1.6;
}

.endpoint-section h4 {
  margin: 32px 0 12px 0;
  font-size: 15px;
  color: #374151;
  font-weight: 700;
}

.endpoint-box {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px;
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  margin-bottom: 24px;
}

.method {
  padding: 6px 12px;
  background: #10b981;
  color: white;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 700;
  flex-shrink: 0;
}

.url {
  font-family: 'Monaco', 'Courier New', monospace;
  font-size: 13px;
  color: #374151;
  word-break: break-all;
}

.code-block {
  background: #1f2937;
  color: #e5e7eb;
  padding: 16px;
  border-radius: 8px;
  font-family: 'Monaco', 'Courier New', monospace;
  font-size: 13px;
  line-height: 1.6;
  overflow-x: auto;
  margin: 8px 0 16px 0;
}

.requirements-list {
  margin: 8px 0 16px 20px;
  padding: 0;
  line-height: 1.8;
  color: #374151;
}

.requirements-list li {
  margin: 8px 0;
}

.requirements-list strong {
  color: #1f2937;
}

.requirements-list code {
  background: #f3f4f6;
  padding: 2px 6px;
  border-radius: 4px;
  font-family: 'Monaco', 'Courier New', monospace;
  font-size: 12px;
  color: #374151;
}

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 24px;
  z-index: 1000;
  overflow-y: auto;
  animation: fadeIn 0.3s ease;
}

.modal-card {
  width: 100%;
  max-width: 600px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  overflow: hidden;
  animation: slideUp 0.3s ease;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(40px);
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
  padding: 24px 32px;
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
  background: transparent;
  border: none;
  font-size: 28px;
  color: rgba(255, 255, 255, 0.9);
  cursor: pointer;
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

.close-btn:hover {
  background: rgba(255, 255, 255, 0.15);
  color: #ffffff;
  transform: rotate(90deg);
}

.modal-body {
  padding: 32px;
  max-height: 70vh;
  overflow-y: auto;
}

.success-section {
  text-align: center;
}

.success-icon {
  font-size: 64px;
  margin-bottom: 16px;
}

.success-section h3 {
  margin: 0 0 24px 0;
  font-size: 22px;
  color: #1f2937;
  font-weight: 700;
}

.warning-box {
  background: #fef3c7;
  border: 2px solid #f59e0b;
  border-radius: 8px;
  padding: 16px;
  margin-bottom: 24px;
  text-align: left;
}

.warning-box strong {
  display: block;
  margin-bottom: 8px;
  color: #92400e;
  font-size: 15px;
}

.warning-box p {
  margin: 0;
  color: #78350f;
  font-size: 14px;
  line-height: 1.5;
}

.token-display {
  margin-bottom: 24px;
  text-align: left;
}

.token-display label {
  display: block;
  margin-bottom: 8px;
  font-size: 14px;
  font-weight: 600;
  color: #374151;
}

.token-box {
  display: flex;
  gap: 12px;
  align-items: center;
  padding: 16px;
  background: #f9fafb;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
}

.token-value {
  flex: 1;
  font-family: 'Monaco', 'Courier New', monospace;
  font-size: 12px;
  color: #1f2937;
  word-break: break-all;
  line-height: 1.6;
}

.copy-btn {
  padding: 8px 16px;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
  flex-shrink: 0;
}

.copy-btn:hover {
  background: #2563eb;
}

.copy-btn.copied {
  background: #10b981;
}

.token-info {
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 16px;
  text-align: left;
}

.info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid #e5e7eb;
}

.info-row:last-child {
  border-bottom: none;
}

.info-row .label {
  font-size: 14px;
  font-weight: 600;
  color: #6b7280;
}

.info-row .value {
  font-size: 14px;
  color: #1f2937;
  text-align: right;
}

.token-form {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group label {
  font-size: 14px;
  font-weight: 600;
  color: #374151;
}

.checkbox-group {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.checkbox-label {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 12px;
  background: #f9fafb;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
}

.checkbox-label:hover {
  background: #f3f4f6;
  border-color: #42b883;
}

.checkbox-label input[type="checkbox"] {
  margin-top: 2px;
  cursor: pointer;
  width: 18px;
  height: 18px;
  flex-shrink: 0;
}

.checkbox-text {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.checkbox-text strong {
  color: #1f2937;
  font-size: 14px;
}

.checkbox-text small {
  color: #6b7280;
  font-size: 12px;
}

.form-select {
  padding: 12px 14px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 14px;
  color: #1f2937;
  background: white;
  cursor: pointer;
  transition: border 0.2s;
}

.form-select:focus {
  outline: none;
  border-color: #42b883;
  box-shadow: 0 0 0 3px rgba(66, 184, 131, 0.1);
}

.help-text {
  margin: 0;
  font-size: 13px;
  color: #6b7280;
  line-height: 1.5;
}

.security-notice {
  display: flex;
  gap: 16px;
  padding: 16px;
  background: #fef3c7;
  border: 1px solid #fbbf24;
  border-radius: 8px;
}

.notice-icon {
  font-size: 24px;
  flex-shrink: 0;
}

.notice-content {
  font-size: 13px;
  color: #78350f;
  line-height: 1.6;
}

.notice-content strong {
  display: block;
  margin-bottom: 8px;
  color: #92400e;
}

.notice-content ul {
  margin: 8px 0 0 20px;
  padding: 0;
}

.notice-content li {
  margin: 4px 0;
}

.modal-footer {
  border-top: 1px solid #e5e7eb;
  padding: 20px 32px;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  background: #f9fafb;
}

.primary-btn,
.secondary-btn {
  padding: 12px 24px;
  border: none;
  border-radius: 8px;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.primary-btn {
  background: #42b883;
  color: white;
}

.primary-btn:hover:not(:disabled) {
  background: #369f73;
  transform: translateY(-1px);
}

.primary-btn:disabled {
  background: #9ca3af;
  cursor: not-allowed;
}

.secondary-btn {
  background: transparent;
  color: #6b7280;
  border: 1px solid #d1d5db;
}

.secondary-btn:hover {
  background: #f3f4f6;
  color: #374151;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
  .tokens-page {
    padding: 12px;
  }

  .panel-header {
    flex-direction: column;
    align-items: stretch;
    padding: 20px;
  }

  .panel-header h1 {
    font-size: 22px;
  }

  .create-btn {
    width: 100%;
    justify-content: center;
  }

  .info-banner {
    flex-direction: column;
    padding: 16px 20px;
  }

  .table-section {
    padding: 16px;
    overflow-x: auto;
  }

  .tokens-table {
    min-width: 800px;
    font-size: 13px;
  }

  .tokens-table th,
  .tokens-table td {
    padding: 12px 8px;
  }

  .docs-content {
    padding: 20px;
  }

  .code-block {
    font-size: 11px;
    padding: 12px;
  }

  .endpoint-description {
    font-size: 14px;
  }

  .modal-overlay {
    padding: 0;
    align-items: flex-start;
  }

  .modal-card {
    max-width: 100%;
    min-height: 100vh;
    border-radius: 0;
  }

  .modal-header,
  .modal-body,
  .modal-footer {
    padding: 20px;
  }

  .modal-footer {
    flex-direction: column-reverse;
  }

  .primary-btn,
  .secondary-btn {
    width: 100%;
  }

  .token-box {
    flex-direction: column;
    align-items: stretch;
  }

  .copy-btn {
    width: 100%;
  }

  .info-row {
    flex-direction: column;
    align-items: flex-start;
    gap: 4px;
  }

  .info-row .value {
    text-align: left;
  }
}
</style>