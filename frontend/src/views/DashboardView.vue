<template>
  <div class="dashboard">
    <h1>Welcome to your Dashboard 🎉</h1>
    <p>This page is protected by token authentication.</p>

    <button @click="logout">Logout</button>
  </div>
</template>

<script setup>
import api from '../api.js'
import { useRouter } from 'vue-router'

const router = useRouter()

async function logout() {
  try {
    await api.post('/logout')
  } catch (err) {
    console.error(err)
  }

  localStorage.removeItem('token')
  router.push('/login')
}
</script>

<style scoped>
.dashboard {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #42b883, #2c3e50);
  color: white;
  font-family: 'Inter', sans-serif;
}

button {
  margin-top: 20px;
  padding: 10px 20px;
  background: white;
  color: #2c3e50;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
}
</style>
