<template>
    <div class="profile-page" :style="{ background: backgroundGradient }">
        <PageAlert
            v-if="alert.show"
            :type="alert.type"
            :message="alert.message"
            @close="alert.show = false"
            dismissible
        />

        <Spinner v-if="loading" overlay />

        <div v-if="userExists" class="profile-card">
            <div class="profile-header">
                <div class="avatar">
                    {{ initials }}
                </div>
                <div class="header-text">
                    <h1>Môj profil</h1>
                    <p class="name">{{ user.fullName }}</p>
                    <p class="email">{{ user.email }}</p>
                    <span class="role-pill">
                        {{ user.role || 'Používateľ' }}
                    </span>
                </div>
            </div>

            <div class="divider"></div>

            <!-- Profile Information -->
            <div class="profile-body">
                <!-- Student Information -->
                <template v-if="user.roleName?.toLowerCase() === 'student'">
                    <div class="section-header">
                        <h2>Základné informácie</h2>
                    </div>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Meno</span>
                            <span class="info-value">{{ user.fullName }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email</span>
                            <span class="info-value">{{ user.email }}</span>
                        </div>
                        <div class="info-item" v-if="user.studentEmail">
                            <span class="info-label">Študentský email</span>
                            <span class="info-value">{{ user.studentEmail }}</span>
                        </div>
                        <div class="info-item" v-if="user.alternativeEmail">
                            <span class="info-label">Alternatívny email</span>
                            <span class="info-value">{{ user.alternativeEmail }}</span>
                        </div>
                        <div class="info-item" v-if="user.phone">
                            <span class="info-label">Telefónne číslo</span>
                            <span class="info-value">{{ user.phone }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Rola</span>
                            <span class="info-value">{{ user.role }}</span>
                        </div>
                        <div class="info-item" v-if="user.studyProgram && user.studyProgram !== '—'">
                            <span class="info-label">Študijný program</span>
                            <span class="info-value">{{ user.studyProgram }}</span>
                        </div>
                    </div>
                </template>

                <!-- Company Information -->
                <template v-else-if="user.roleName?.toLowerCase() === 'company'">
                    <div class="section-header">
                        <h2>Informácie o účte</h2>
                    </div>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Meno</span>
                            <span class="info-value">{{ user.fullName }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email</span>
                            <span class="info-value">{{ user.email }}</span>
                        </div>
                        <div class="info-item" v-if="user.phone">
                            <span class="info-label">Telefónne číslo</span>
                            <span class="info-value">{{ user.phone }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Rola</span>
                            <span class="info-value">{{ user.role }}</span>
                        </div>
                    </div>

                    <div class="divider"></div>

                    <div class="section-header">
                        <h2>Informácie o spoločnosti</h2>
                    </div>
                    <div class="info-grid">
                        <div class="info-item" v-if="user.companyName">
                            <span class="info-label">Názov spoločnosti</span>
                            <span class="info-value">{{ user.companyName }}</span>
                        </div>
                        <div class="info-item" v-if="user.companyAddress">
                            <span class="info-label">Adresa</span>
                            <span class="info-value">{{ user.companyAddress }}</span>
                        </div>
                        <div class="info-item" v-if="user.companyCity">
                            <span class="info-label">Mesto</span>
                            <span class="info-value">{{ user.companyCity }}</span>
                        </div>
                        <div class="info-item" v-if="user.contactPerson">
                            <span class="info-label">Kontaktná osoba</span>
                            <span class="info-value">{{ user.contactPerson }}</span>
                        </div>
                        <div class="info-item" v-if="user.contactEmail">
                            <span class="info-label">Kontaktný email</span>
                            <span class="info-value">{{ user.contactEmail }}</span>
                        </div>
                        <div class="info-item" v-if="user.contactPhone">
                            <span class="info-label">Kontaktný telefón</span>
                            <span class="info-value">{{ user.contactPhone }}</span>
                        </div>
                    </div>
                </template>

                <!-- Guarantor Information (Basic) -->
                <template v-else>
                    <div class="section-header">
                        <h2>Základné informácie</h2>
                    </div>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Meno</span>
                            <span class="info-value">{{ user.fullName }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email</span>
                            <span class="info-value">{{ user.email }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Rola</span>
                            <span class="info-value">{{ user.role }}</span>
                        </div>
                    </div>
                </template>
            </div>

            <div class="divider"></div>

            <!-- Email Management Section (Students) -->
            <div v-if="isStudent" class="profile-body">
                <div class="section-header">
                    <h2>Správa emailov</h2>
                </div>

                <!-- Student Email -->
                <div class="email-section">
                    <div class="section-label">Študentský email (UKF)</div>
                    <div v-if="!editStudentEmailMode" class="email-display">
                        <span class="email-value">{{ user.email }}</span>
                        <button type="button" class="btn-edit-inline" @click="enterStudentEmailEditMode">
                            Upraviť
                        </button>
                    </div>
                    <form v-else @submit.prevent="updateStudentEmail">
                        <div class="form-group">
                            <input
                                v-model="studentEmailForm.student_email"
                                type="email"
                                placeholder="meno.priezvisko@student.ukf.sk"
                                :disabled="loading"
                            />
                            <p v-if="errors.student_email" class="error">{{ errors.student_email[0] }}</p>
                            <p class="hint">Formát: meno.priezvisko@student.ukf.sk</p>
                        </div>
                        <div class="form-actions-inline">
                            <button type="button" class="btn btn-secondary" @click="cancelStudentEmailEdit" :disabled="loading">
                                Zrušiť
                            </button>
                            <button type="submit" class="btn btn-primary" :disabled="loading">
                                {{ loading ? 'Ukladám...' : 'Uložiť' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Alternative Email -->
                <div class="email-section">
                    <div class="section-label">Alternatívny email</div>
                    <div v-if="!editAlternativeEmailMode" class="email-display">
                        <span class="email-value">{{ user.alternativeEmail || 'Nie je nastavený' }}</span>
                        <button type="button" class="btn-edit-inline" @click="enterAlternativeEmailEditMode">
                            Upraviť
                        </button>
                    </div>
                    <form v-else @submit.prevent="updateAlternativeEmail">
                        <div class="form-group">
                            <input
                                v-model="alternativeEmailForm.alternative_email"
                                type="email"
                                placeholder="vas.email@example.com"
                                :disabled="loading"
                            />
                            <p v-if="errors.alternative_email" class="error">{{ errors.alternative_email[0] }}</p>
                            <p class="hint">Voliteľný email pre dodatočnú komunikáciu</p>
                        </div>
                        <div class="form-actions-inline">
                            <button type="button" class="btn btn-secondary" @click="cancelAlternativeEmailEdit" :disabled="loading">
                                Zrušiť
                            </button>
                            <button type="submit" class="btn btn-primary" :disabled="loading">
                                {{ loading ? 'Ukladám...' : 'Uložiť' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Email Management Section (Companies) -->
            <div v-if="isCompany" class="profile-body">
                <div class="section-header">
                    <h2>Správa emailu</h2>
                </div>

                <div class="email-section">
                    <div class="section-label">Firemný email</div>
                    <div v-if="!editCompanyEmailMode" class="email-display">
                        <span class="email-value">{{ user.email }}</span>
                        <button type="button" class="btn-edit-inline" @click="enterCompanyEmailEditMode">
                            Upraviť
                        </button>
                    </div>
                    <form v-else @submit.prevent="updateCompanyEmail">
                        <div class="form-group">
                            <input
                                v-model="companyEmailForm.email"
                                type="email"
                                placeholder="kontakt@firma.sk"
                                :disabled="loading"
                            />
                            <p v-if="errors.email" class="error">{{ errors.email[0] }}</p>
                            <p class="hint">Tento email sa používa na prihlásenie a komunikáciu</p>
                        </div>
                        <div class="form-actions-inline">
                            <button type="button" class="btn btn-secondary" @click="cancelCompanyEmailEdit" :disabled="loading">
                                Zrušiť
                            </button>
                            <button type="submit" class="btn btn-primary" :disabled="loading">
                                {{ loading ? 'Ukladám...' : 'Uložiť' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="divider"></div>

            <!-- Password Change Section -->
            <div class="profile-body">
                <div class="section-header">
                    <h2>Zmena hesla</h2>
                </div>

                <div v-if="!changePasswordMode">
                    <button type="button" class="btn btn-primary" @click="enterPasswordChangeMode">
                        Zmeniť heslo
                    </button>
                </div>

                <form v-else @submit.prevent="changePassword">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="current_password">Súčasné heslo</label>
                            <input
                                id="current_password"
                                v-model="passwordForm.current_password"
                                type="password"
                                placeholder="Súčasné heslo"
                                :disabled="loading"
                            />
                            <p v-if="errors.current_password" class="error">{{ errors.current_password[0] }}</p>
                        </div>

                        <div class="form-group">
                            <label for="password">Nové heslo</label>
                            <input
                                id="password"
                                v-model="passwordForm.password"
                                type="password"
                                placeholder="Nové heslo (min. 8 znakov)"
                                :disabled="loading"
                            />
                            <p v-if="errors.password" class="error">{{ errors.password[0] }}</p>
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Potvrdiť nové heslo</label>
                            <input
                                id="password_confirmation"
                                v-model="passwordForm.password_confirmation"
                                type="password"
                                placeholder="Potvrdiť nové heslo"
                                :disabled="loading"
                            />
                            <p v-if="errors.password_confirmation" class="error">{{ errors.password_confirmation[0] }}</p>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" @click="cancelPasswordChange" :disabled="loading">
                            Zrušiť
                        </button>
                        <button type="submit" class="btn btn-primary" :disabled="loading">
                            {{ loading ? 'Mením heslo...' : 'Zmeniť heslo' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div v-else class="profile-card empty">
            <h1>Môj profil</h1>
            <p>Nenašiel som údaje o používateľovi. Skús sa prosím prihlásiť znova.</p>
        </div>
    </div>
</template>

<script setup>
// ============================================================
// IMPORTS & SETUP
// ============================================================
import { computed, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api'
import PageAlert from '@/components/PageAlert.vue'
import Spinner from '@/components/Spinner.vue'

const router = useRouter()

// ============================================================
// STATE MANAGEMENT
// ============================================================
const loading = ref(false)
const changePasswordMode = ref(false)
const alert = reactive({ show: false, type: 'error', message: '' })
const errors = reactive({})

// Email editing states
const editStudentEmailMode = ref(false)
const editAlternativeEmailMode = ref(false)
const editCompanyEmailMode = ref(false)

// Email forms
const studentEmailForm = reactive({ student_email: '' })
const alternativeEmailForm = reactive({ alternative_email: '' })
const companyEmailForm = reactive({ email: '' })

const storedUser = localStorage.getItem('user')
let rawUser = storedUser ? JSON.parse(storedUser) : null

if (!rawUser) {
    router.push({ name: 'login' })
}

const userExists = computed(() => !!rawUser)

// Role detection
const isStudent = computed(() => {
    return rawUser?.role_name === 'student' || rawUser?.role === 'student'
})

const isCompany = computed(() => {
    return rawUser?.role_name === 'company' || rawUser?.role === 'company'
})

// Password form
const passwordForm = reactive({
    current_password: '',
    password: '',
    password_confirmation: ''
})

// ============================================================
// HELPER FUNCTIONS
// ============================================================
const formatStudyProgram = () => {
    if (!rawUser) return '—'

    // ak backend pošle už priamo string
    if (typeof rawUser.study_program === 'string') {
        return rawUser.study_program
    }

    // ak je to objekt study_field
    const sf = rawUser.study_field
    if (sf && typeof sf === 'object') {
        const name = sf.study_field_name ?? sf.name ?? ''
        const abbr = sf.abbreviation ? ` (${sf.abbreviation})` : ''
        const result = `${name}${abbr}`.trim()
        return result || '—'
    }

    // fallback
    if (rawUser.program) {
        return rawUser.program
    }

    return '—'
}

// ============================================================
// COMPUTED PROPERTIES
// ============================================================
const userExists = computed(() => !!rawUser)

const user = computed(() => {
    if (!rawUser) {
        return {
            fullName: '',
            email: '',
            phone: '',
            alternativeEmail: '',
            role: '',
            roleName: '',
            studyProgram: '',
            studentEmail: '',
            // Company fields
            companyName: '',
            companyAddress: '',
            companyCity: '',
            contactPerson: '',
            contactEmail: '',
            contactPhone: ''
        }
    }

    const fullName =
        rawUser.name ||
        `${rawUser.first_name ?? ''} ${rawUser.last_name ?? ''}`.trim() ||
        'Neznámy používateľ'

    return {
        fullName,
        email: rawUser.email ?? '—',
        phone: rawUser.phone ?? rawUser.phone_number ?? '',
        alternativeEmail: rawUser.alternative_email ?? rawUser.alternativeEmail ?? '',
        role: rawUser.role_name ?? rawUser.role ?? '—',
        roleName: rawUser.role_name ?? rawUser.role ?? '',
        studyProgram: formatStudyProgram(),
        studentEmail: rawUser.student_email ?? '',
        // Company fields (from nested company object)
        companyName: rawUser.company?.company_name ?? '',
        companyAddress: rawUser.company?.address?.street ?? '',
        companyCity: rawUser.company?.address?.city ?? '',
        contactPerson: rawUser.company?.contact_person_name ?? '',
        contactEmail: rawUser.company?.contact_person_email ?? '',
        contactPhone: rawUser.company?.contact_person_phone ?? ''
    }
})

// Background gradient based on role
const backgroundGradient = computed(() => {
    const role = user.value.roleName?.toLowerCase()
    switch(role) {
        case 'student':
            return 'linear-gradient(135deg, #42b883 0%, #2c3e50 100%)'
        case 'company':
            return 'linear-gradient(135deg, #76cbec 0%, #607d9b 100%)'
        case 'guarantor':
            return 'linear-gradient(135deg, #ffb74d 0%, #ff8a65 100%)'
        default:
            return 'linear-gradient(135deg, #42b883 0%, #2c3e50 100%)'
    }
})

// iniciály do kruhového avatara
const initials = computed(() => {
    const name = user.value.fullName || ''
    if (!name) return '?'
    const parts = name.trim().split(' ').filter(Boolean)
    if (!parts.length) return '?'
    const first = parts[0][0] ?? ''
    const last = parts.length > 1 ? parts[parts.length - 1][0] ?? '' : ''
    return (first + last).toUpperCase()
})

// ============================================================
// UI FUNCTIONS
// ============================================================
function showAlert(message, type = 'error') {
    alert.message = message
    alert.type = type
    alert.show = true
}

function clearErrors() {
    Object.keys(errors).forEach(k => delete errors[k])
}

// Password change functions
function enterPasswordChangeMode() {
    changePasswordMode.value = true
    passwordForm.current_password = ''
    passwordForm.password = ''
    passwordForm.password_confirmation = ''
    clearErrors()
}

function cancelPasswordChange() {
    changePasswordMode.value = false
    clearErrors()
}

function changePassword() {
    clearErrors()
    alert.show = false
    loading.value = true

    // Use POST method as defined in backend routes
    api.post('/change-password', passwordForm)
        .then(() => {
            loading.value = false
            changePasswordMode.value = false

            // Clear password form
            passwordForm.current_password = ''
            passwordForm.password = ''
            passwordForm.password_confirmation = ''

            showAlert('success.password.changed', 'success')
        })
        .catch(err => {
            loading.value = false

            // Handle validation errors
            if (err.response?.status === 422 && err.response?.data?.errors) {
                Object.assign(errors, err.response.data.errors)
                showAlert('validation.form', 'validation')
                return
            }

            // Handle specific error messages (backend returns 400 for wrong password)
            if (err.response?.status === 400 || err.response?.status === 401) {
                showAlert('Súčasné heslo je nesprávne.', 'error')
                return
            }

            // Generic error
            showAlert(err.response?.data?.message || 'Zmena hesla zlyhala. Skúste to znova.', 'error')
        })
}

// Student Email functions
function enterStudentEmailEditMode() {
    editStudentEmailMode.value = true
    studentEmailForm.student_email = rawUser.student_email || ''
    clearErrors()
}

function cancelStudentEmailEdit() {
    editStudentEmailMode.value = false
    clearErrors()
}

function updateStudentEmail() {
    clearErrors()
    alert.show = false
    loading.value = true

    // Changed to use new endpoint with new_email parameter
    api.post('/request-student-email-change', {
        new_email: studentEmailForm.student_email
    })
        .then(response => {
            loading.value = false
            editStudentEmailMode.value = false

            // Do NOT update localStorage - email changes only after verification

            showAlert('Verifikačný email bol odoslaný na váš aktuálny email. Potvrďte zmenu kliknutím na odkaz v emaile.', 'info')
        })
        .catch(err => {
            loading.value = false
            handleEmailUpdateError(err)
        })
}

// Alternative Email functions
function enterAlternativeEmailEditMode() {
    editAlternativeEmailMode.value = true
    alternativeEmailForm.alternative_email = rawUser.alternative_email || ''
    clearErrors()
}

function cancelAlternativeEmailEdit() {
    editAlternativeEmailMode.value = false
    clearErrors()
}

function updateAlternativeEmail() {
    clearErrors()
    alert.show = false
    loading.value = true

    api.post('/update-alternative-email', alternativeEmailForm)
        .then(response => {
            loading.value = false
            editAlternativeEmailMode.value = false

            // Update localStorage
            if (rawUser) {
                rawUser.alternative_email = alternativeEmailForm.alternative_email
                localStorage.setItem('user', JSON.stringify(rawUser))
            }

            showAlert('success.email.changed', 'success')
        })
        .catch(err => {
            loading.value = false
            handleEmailUpdateError(err)
        })
}

// Company Email functions
function enterCompanyEmailEditMode() {
    editCompanyEmailMode.value = true
    companyEmailForm.email = rawUser.email || ''
    clearErrors()
}

function cancelCompanyEmailEdit() {
    editCompanyEmailMode.value = false
    clearErrors()
}

function updateCompanyEmail() {
    clearErrors()
    alert.show = false
    loading.value = true

    // Changed to use new endpoint with new_email parameter
    api.post('/request-company-email-change', {
        new_email: companyEmailForm.email
    })
        .then(response => {
            loading.value = false
            editCompanyEmailMode.value = false

            // Do NOT update localStorage - email changes only after verification

            showAlert('Verifikačný email bol odoslaný na váš aktuálny email. Potvrďte zmenu kliknutím na odkaz v emaile.', 'info')
        })
        .catch(err => {
            loading.value = false
            handleEmailUpdateError(err)
        })
}

// Shared error handler for email updates
function handleEmailUpdateError(err) {
    if (err.response?.status === 422 && err.response?.data?.errors) {
        Object.assign(errors, err.response.data.errors)
        showAlert('validation.form', 'validation')
        return
    }

    if (err.response?.status === 403) {
        showAlert('Nemáte oprávnenie vykonať túto akciu.', 'error')
        return
    }

    showAlert(err.response?.data?.message || 'Aktualizácia emailu zlyhala. Skúste to znova.', 'error')
}
</script>

<style scoped>
.profile-page {
    min-height: calc(100vh - 116px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: linear-gradient(135deg, #42b883 0%, #2c3e50 100%);
    font-family: 'Inter', sans-serif;
}

.profile-card {
    width: 100%;
    max-width: 800px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    padding: 36px 28px;
    box-sizing: border-box;
    animation: fadeIn 0.6s ease;
}

.profile-card.empty {
    text-align: center;
    max-width: 420px;
}

.profile-header {
    display: flex;
    gap: 1.5rem;
    align-items: center;
    margin-bottom: 20px;
}

.avatar {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: #42b883;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.6rem;
    flex-shrink: 0;
}

.header-text {
    flex: 1;
}

.header-text h1 {
    font-size: 16px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #666;
    margin: 0 0 8px 0;
}

.name {
    font-size: 24px;
    font-weight: 600;
    color: #2c3e50;
    margin: 0 0 4px 0;
}

.email {
    font-size: 14px;
    color: #666;
    margin: 0 0 8px 0;
}

.role-pill {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 500;
    background: #e8f5f1;
    color: #42b883;
}

.divider {
    height: 1px;
    background: #e5e7eb;
    margin: 24px 0;
}

.profile-body {
    margin-bottom: 16px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.section-header h2 {
    font-size: 18px;
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
}

.btn-edit {
    padding: 8px 16px;
    background: #f9fafb;
    color: #2c3e50;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-edit:hover {
    background: #f3f4f6;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px 24px;
}

.info-item {
    display: flex;
    flex-direction: column;
}

.info-label {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #9ca3af;
    margin-bottom: 4px;
    font-weight: 600;
}

.info-value {
    font-size: 14px;
    color: #2c3e50;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

label {
    margin-bottom: 5px;
    font-weight: 600;
    color: #333;
    font-size: 14px;
}

input {
    padding: 10px 12px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.2s ease;
}

input:disabled {
    background-color: #f5f5f5;
    cursor: not-allowed;
}

input:focus {
    outline: none;
    border-color: #42b883;
    box-shadow: 0 0 0 2px rgba(66, 184, 131, 0.25);
}

.error {
    color: #e74c3c;
    font-size: 12px;
    margin-top: 4px;
}

.form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 20px;
}

.btn {
    padding: 10px 20px;
    border-radius: 8px;
    border: none;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-primary {
    background: #42b883;
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background: #369f73;
}

.btn-primary:disabled {
    background: #95d5b2;
    cursor: not-allowed;
}

.btn-secondary {
    background: #f9fafb;
    color: #2c3e50;
    border: 1px solid #d1d5db;
}

.btn-secondary:hover:not(:disabled) {
    background: #f3f4f6;
}

.btn-secondary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
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

/* Email Management Section */
.email-section {
    margin-bottom: 24px;
    padding: 16px;
    background: #f9fafb;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
}

.section-label {
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6b7280;
    margin-bottom: 8px;
    font-weight: 600;
}

.email-display {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
}

.email-value {
    font-size: 15px;
    color: #2c3e50;
    font-weight: 500;
}

.btn-edit-inline {
    padding: 6px 14px;
    background: white;
    color: #42b883;
    border: 1px solid #42b883;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.btn-edit-inline:hover {
    background: #42b883;
    color: white;
}

.form-actions-inline {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 12px;
}

.hint {
    font-size: 12px;
    color: #6b7280;
    margin-top: 4px;
    font-style: italic;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .profile-card {
        padding: 26px 20px;
        border-radius: 14px;
    }

    .profile-header {
        flex-direction: column;
        text-align: center;
    }

    .info-grid {
        grid-template-columns: 1fr;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }

    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .form-actions {
        flex-direction: column-reverse;
    }

    .btn {
        width: 100%;
    }

    .profile-page {
        padding: 15px 10px;
    }

    .email-display {
        flex-direction: column;
        align-items: stretch;
    }

    .btn-edit-inline {
        width: 100%;
    }

    .form-actions-inline {
        flex-direction: column-reverse;
    }

    .form-actions-inline .btn {
        width: 100%;
    }
}

@media (max-width: 480px) {
    .name {
        font-size: 20px;
    }

    .header-text h1 {
        font-size: 14px;
    }

    .avatar {
        width: 60px;
        height: 60px;
        font-size: 1.4rem;
    }
}
</style>
