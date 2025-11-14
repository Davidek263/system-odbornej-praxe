<template>
  <div class="create-internship-page">
    <div class="form-card">
      <h2>Vytvorenie novej odbornej praxe</h2>

      <form @submit.prevent="submitForm">
        <div class="form-group">
          <label for="company">Názov firmy</label>
          <input
            id="company"
            v-model="form.company"
            type="text"
            placeholder="Zadajte názov firmy"
            required
          />
        </div>

        <div class="form-group">
          <label for="semester">Semester</label>
          <select id="semester" v-model="form.semester" required>
            <option value="">Vyberte semester</option>
            <option value="ZS">Zimný semester</option>
            <option value="LS">Letný semester</option>
          </select>
        </div>

        <div class="form-group">
          <label for="date">Dátum začiatku praxe</label>
          <input id="date" v-model="form.date" type="date" required />
        </div>

        <div class="form-group">
          <label for="year">Rok</label>
          <input
            id="year"
            v-model="form.year"
            type="number"
            min="2000"
            max="2100"
            placeholder="Zadajte rok"
            required
          />
        </div>

        <div class="form-actions">
          <router-link to="/student-dashboard" class="cancel-btn">← Späť</router-link>
          <button type="submit" class="submit-btn">Uložiť prax</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const form = reactive({
  company: '',
  semester: '',
  date: '',
  year: ''
})

function submitForm() {
  console.log('Nová prax:', form)
  // Tu môžeš volať API – napr. api.post('/internships', form)

  alert('Prax bola úspešne vytvorená!')
  router.push('/student-dashboard')
}
</script>

<style scoped>
.create-internship-page {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: calc(100vh - 80px);
  background: linear-gradient(135deg, #b9f6ca 0%, #69f0ae 100%);
  padding: 40px 20px;
}

.form-card {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  padding: 40px;
  width: 100%;
  max-width: 500px;
  animation: fadeIn 0.3s ease;
}

h2 {
  text-align: center;
  margin-bottom: 24px;
  color: #2c3e50;
}

.form-group {
  display: flex;
  flex-direction: column;
  margin-bottom: 16px;
}

label {
  font-weight: 600;
  margin-bottom: 6px;
  color: #374151;
}

input,
select {
  padding: 10px 14px;
  border-radius: 8px;
  border: 1px solid #d1d5db;
  font-size: 14px;
}

input:focus,
select:focus {
  outline: none;
  border-color: #42b883;
  box-shadow: 0 0 0 3px rgba(66, 184, 131, 0.1);
}

.form-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 24px;
}

.cancel-btn {
  color: #374151;
  text-decoration: none;
  font-weight: 600;
}

.submit-btn {
  background: #10b981;
  color: #fff;
  border: none;
  padding: 10px 16px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.submit-btn:hover {
  background: #059669;
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
