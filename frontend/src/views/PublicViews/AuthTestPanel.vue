<template>
  <div class="test-page">
    <div class="test-container">
      <h1>🔧 Authentication Test Panel</h1>
      
      <div class="test-section">
        <h2>1. LocalStorage Check</h2>
        <div class="test-result">
          <p><strong>Token:</strong></p>
          <pre>{{ tokenPreview }}</pre>
          <p class="status" :class="tokenStatus">{{ tokenMessage }}</p>
        </div>
        
        <div class="test-result">
          <p><strong>User Object:</strong></p>
          <pre>{{ userPreview }}</pre>
          <p class="status" :class="userStatus">{{ userMessage }}</p>
        </div>
      </div>

      <div class="test-section">
        <h2>2. API Connection Test</h2>
        <button @click="testApiConnection" :disabled="testing">
          {{ testing ? 'Testing...' : 'Test API Connection' }}
        </button>
        <div v-if="apiTestResult" class="test-result">
          <p><strong>Result:</strong></p>
          <pre>{{ apiTestResult }}</pre>
          <p class="status" :class="apiStatus">{{ apiMessage }}</p>
        </div>
      </div>

      <div class="test-section" v-if="isCompany">
        <h2>3. Company Internships Test</h2>
        <button @click="testCompanyInternships" :disabled="testing">
          {{ testing ? 'Testing...' : 'Test Company Internships Endpoint' }}
        </button>
        <div v-if="internshipsTestResult" class="test-result">
          <p><strong>Result:</strong></p>
          <pre>{{ internshipsTestResult }}</pre>
          <p class="status" :class="internshipsStatus">{{ internshipsMessage }}</p>
        </div>
      </div>

      <div class="test-section">
        <h2>4. Recommendations</h2>
        <ul class="recommendations">
          <li v-for="rec in recommendations" :key="rec" :class="rec.type">
            {{ rec.message }}
          </li>
        </ul>
      </div>

      <div class="test-section">
        <h2>Actions</h2>
        <button @click="clearAndRedirect" class="danger">
          Clear Auth & Go to Login
        </button>
        <button @click="copyDebugInfo">
          Copy Debug Info to Clipboard
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/api'
import { useRouter } from 'vue-router'

const router = useRouter()
const testing = ref(false)
const apiTestResult = ref(null)
const internshipsTestResult = ref(null)
const recommendations = ref([])

// Token checks
const token = ref(localStorage.getItem('token'))
const tokenPreview = computed(() => {
  if (!token.value) return 'No token found'
  return token.value.substring(0, 50) + '... (length: ' + token.value.length + ')'
})
const tokenStatus = computed(() => {
  return token.value ? 'success' : 'error'
})
const tokenMessage = computed(() => {
  return token.value ? '✓ Token exists' : '✗ Token missing'
})

// User checks
const userStr = localStorage.getItem('user')
const user = ref(userStr ? JSON.parse(userStr) : null)
const userPreview = computed(() => {
  if (!user.value) return 'No user found'
  return JSON.stringify(user.value, null, 2)
})
const userStatus = computed(() => {
  return user.value ? 'success' : 'error'
})
const userMessage = computed(() => {
  if (!user.value) return '✗ User object missing'
  if (!user.value.role_name) return '⚠ User missing role_name'
  if (user.value.role_name === 'company' && !user.value.company) {
    return '✗ Company user missing company object'
  }
  return '✓ User object valid'
})

const isCompany = computed(() => user.value?.role_name === 'company')

// API test
const apiStatus = ref('')
const apiMessage = ref('')

async function testApiConnection() {
  testing.value = true
  apiTestResult.value = null
  apiStatus.value = ''
  apiMessage.value = ''

  try {
    const response = await api.get('/user')
    apiTestResult.value = JSON.stringify(response.data, null, 2)
    apiStatus.value = 'success'
    apiMessage.value = '✓ API connection successful'
    
    addRecommendation('success', 'API authentication is working correctly')
  } catch (err) {
    apiTestResult.value = JSON.stringify({
      status: err.response?.status,
      message: err.response?.data?.message || err.message,
      data: err.response?.data
    }, null, 2)
    
    if (err.response?.status === 401) {
      apiStatus.value = 'error'
      apiMessage.value = '✗ 401 Unauthorized - Token invalid or expired'
      addRecommendation('error', 'Token is invalid. Try logging out and logging back in.')
    } else {
      apiStatus.value = 'error'
      apiMessage.value = `✗ API Error: ${err.response?.status || 'Network Error'}`
      addRecommendation('error', 'API connection failed. Check backend server.')
    }
  } finally {
    testing.value = false
  }
}

// Company internships test
const internshipsStatus = ref('')
const internshipsMessage = ref('')

async function testCompanyInternships() {
  if (!user.value?.company?.id) {
    internshipsTestResult.value = 'Cannot test: User missing company.id'
    internshipsStatus.value = 'error'
    internshipsMessage.value = '✗ Company ID not found'
    addRecommendation('error', 'User object is missing company.id. Check backend login response.')
    return
  }

  testing.value = true
  internshipsTestResult.value = null
  internshipsStatus.value = ''
  internshipsMessage.value = ''

  try {
    const response = await api.get(`/company-internships/${user.value.company.id}`)
    internshipsTestResult.value = JSON.stringify({
      total: response.data.internships?.length || 0,
      sample: response.data.internships?.[0] || 'No internships found'
    }, null, 2)
    internshipsStatus.value = 'success'
    internshipsMessage.value = `✓ Successfully loaded ${response.data.internships?.length || 0} internships`
    
    addRecommendation('success', 'Company dashboard should work correctly')
  } catch (err) {
    internshipsTestResult.value = JSON.stringify({
      status: err.response?.status,
      message: err.response?.data?.message || err.message,
      data: err.response?.data
    }, null, 2)
    
    if (err.response?.status === 401) {
      internshipsStatus.value = 'error'
      internshipsMessage.value = '✗ 401 Unauthorized'
      addRecommendation('error', 'This is the error causing your dashboard issue!')
    } else if (err.response?.status === 403) {
      internshipsStatus.value = 'error'
      internshipsMessage.value = '✗ 403 Forbidden'
      addRecommendation('error', 'User lacks permission. Check company_id in database.')
    } else {
      internshipsStatus.value = 'error'
      internshipsMessage.value = `✗ API Error: ${err.response?.status || 'Network Error'}`
    }
  } finally {
    testing.value = false
  }
}

function addRecommendation(type, message) {
  recommendations.value.push({ type, message })
}

function clearAndRedirect() {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  router.push('/login')
}

function copyDebugInfo() {
  const debugInfo = {
    timestamp: new Date().toISOString(),
    token: tokenPreview.value,
    user: user.value,
    apiTest: apiTestResult.value,
    internshipsTest: internshipsTestResult.value,
    recommendations: recommendations.value
  }
  
  navigator.clipboard.writeText(JSON.stringify(debugInfo, null, 2))
  alert('Debug info copied to clipboard!')
}

onMounted(() => {
  // Initial checks
  if (!token.value) {
    addRecommendation('error', 'No authentication token found. Please log in.')
  }
  
  if (!user.value) {
    addRecommendation('error', 'No user object found. Please log in.')
  }
  
  if (user.value?.role_name === 'company' && !user.value?.company) {
    addRecommendation('error', 'Company user is missing company object. Backend login response issue.')
  }
  
  if (user.value?.company && !user.value?.company.id) {
    addRecommendation('error', 'Company object exists but missing ID.')
  }
  
  if (token.value && user.value) {
    addRecommendation('info', 'Run the API tests above to diagnose the 401 issue.')
  }
})
</script>

<style scoped>
.test-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 40px 20px;
}

.test-container {
  max-width: 900px;
  margin: 0 auto;
  background: white;
  border-radius: 12px;
  padding: 30px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

h1 {
  text-align: center;
  color: #2c3e50;
  margin-bottom: 30px;
  font-size: 28px;
}

h2 {
  color: #34495e;
  font-size: 20px;
  margin-bottom: 15px;
  padding-bottom: 10px;
  border-bottom: 2px solid #e0e0e0;
}

.test-section {
  margin-bottom: 30px;
  padding: 20px;
  background: #f8f9fa;
  border-radius: 8px;
}

.test-result {
  margin-top: 15px;
  padding: 15px;
  background: white;
  border-radius: 6px;
  border: 1px solid #dee2e6;
}

.test-result pre {
  background: #f1f3f5;
  padding: 12px;
  border-radius: 4px;
  overflow-x: auto;
  font-size: 12px;
  margin: 10px 0;
  max-height: 300px;
  overflow-y: auto;
}

.status {
  padding: 8px 12px;
  border-radius: 4px;
  font-weight: 600;
  margin-top: 10px;
}

.status.success {
  background: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.status.error {
  background: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

.status.warning {
  background: #fff3cd;
  color: #856404;
  border: 1px solid #ffeaa7;
}

button {
  padding: 12px 24px;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  font-size: 14px;
  margin-right: 10px;
  margin-bottom: 10px;
  transition: background 0.3s;
}

button:hover:not(:disabled) {
  background: #5568d3;
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

button.danger {
  background: #e74c3c;
}

button.danger:hover:not(:disabled) {
  background: #c0392b;
}

.recommendations {
  list-style: none;
  padding: 0;
}

.recommendations li {
  padding: 12px;
  margin-bottom: 10px;
  border-radius: 6px;
  border-left: 4px solid;
}

.recommendations li.success {
  background: #d4edda;
  border-color: #28a745;
  color: #155724;
}

.recommendations li.error {
  background: #f8d7da;
  border-color: #dc3545;
  color: #721c24;
}

.recommendations li.warning {
  background: #fff3cd;
  border-color: #ffc107;
  color: #856404;
}

.recommendations li.info {
  background: #d1ecf1;
  border-color: #17a2b8;
  color: #0c5460;
}
</style>
