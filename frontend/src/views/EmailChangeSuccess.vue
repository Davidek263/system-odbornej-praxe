<template>
  <div class="email-change-success-page">
    <PageAlert
      v-if="alert.show"
      :type="alert.type"
      :message="alert.message"
      @close="alert.show = false"
      dismissible
    />

    <div class="success-container">
      <div class="success-card">
        <div class="success-icon" v-if="!error">✓</div>
        <div class="error-icon" v-else>✕</div>

        <h1 v-if="!error">Email úspešne zmenený</h1>
        <h1 v-else>Chyba pri zmene emailu</h1>

        <p class="success-message" v-if="!error">
          Váš email bol úspešne aktualizovaný. Od tejto chvíle používajte nový email pri prihlasovaní.
        </p>

        <p class="error-message" v-else>
          {{ errorMessage }}
        </p>

        <Spinner v-if="loading" overlay />

        <div class="actions">
          <router-link to="/profile" class="btn btn-primary" v-if="!error">
            Prejsť na profil
          </router-link>
          <router-link to="/login" class="btn btn-secondary">
            Prihlásiť sa
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import PageAlert from '@/components/PageAlert.vue'
import Spinner from '@/components/Spinner.vue'

const route = useRoute()
const loading = ref(false)
const error = ref(false)
const errorMessage = ref('')

const alert = reactive({
  show: false,
  type: 'success',
  message: ''
})

onMounted(() => {
  // Check if there's an error from backend redirect
  if (route.query.error) {
    error.value = true

    switch (route.query.error) {
      case 'expired':
        errorMessage.value = 'Odkaz na zmenu emailu vypršal. Požiadajte o novú zmenu emailu v profile.'
        break
      case 'invalid':
        errorMessage.value = 'Neplatný odkaz na zmenu emailu.'
        break
      case 'user_not_found':
        errorMessage.value = 'Používateľ nebol nájdený.'
        break
      case 'failed':
        errorMessage.value = 'Zmena emailu zlyhala. Skúste to znova neskôr.'
        break
      default:
        errorMessage.value = 'Neplatný alebo expirovaný odkaz.'
    }

    alert.message = errorMessage.value
    alert.type = 'error'
    alert.show = true
  } else {
    alert.message = 'Email bol úspešne zmenený!'
    alert.type = 'success'
    alert.show = true
  }
})
</script>

<style scoped>
.email-change-success-page {
  min-height: calc(100vh - 116px);
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #42b883 0%, #2c3e50 100%);
  font-family: 'Inter', sans-serif;
  padding: 20px;
}

.success-container {
  width: 100%;
  max-width: 480px;
}

.success-card {
  background: white;
  padding: 40px 30px;
  border-radius: 16px;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
  text-align: center;
  animation: fadeIn 0.6s ease;
}

.success-icon {
  width: 80px;
  height: 80px;
  background: #42b883;
  color: white;
  font-size: 48px;
  line-height: 80px;
  border-radius: 50%;
  margin: 0 auto 20px;
}

.error-icon {
  width: 80px;
  height: 80px;
  background: #e74c3c;
  color: white;
  font-size: 48px;
  line-height: 80px;
  border-radius: 50%;
  margin: 0 auto 20px;
}

h1 {
  color: #2c3e50;
  font-size: 24px;
  margin-bottom: 15px;
  font-weight: 600;
}

.success-message, .error-message {
  color: #666;
  font-size: 15px;
  line-height: 1.6;
  margin-bottom: 30px;
}

.error-message {
  color: #e74c3c;
}

.actions {
  display: flex;
  gap: 12px;
  flex-direction: column;
}

.btn {
  padding: 12px 24px;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 600;
  font-size: 15px;
  transition: all 0.2s ease;
  display: inline-block;
  cursor: pointer;
}

.btn-primary {
  background: #42b883;
  color: white;
}

.btn-primary:hover {
  background: #369f73;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(66, 184, 131, 0.3);
}

.btn-secondary {
  background: #f9fafb;
  color: #2c3e50;
  border: 1px solid #d1d5db;
}

.btn-secondary:hover {
  background: #f3f4f6;
  border-color: #9ca3af;
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

@media (max-width: 640px) {
  .email-change-success-page {
    padding: 15px;
  }

  .success-card {
    padding: 30px 20px;
  }

  h1 {
    font-size: 20px;
  }

  .success-icon, .error-icon {
    width: 60px;
    height: 60px;
    font-size: 36px;
    line-height: 60px;
  }
}
</style>
