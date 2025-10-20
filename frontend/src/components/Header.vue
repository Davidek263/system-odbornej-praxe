<template>
  <header class="app-header">
    <div class="header-container">
      <!--  Logo + Title -->
      <div class="logo-section">
        
        <h1 class="title">Evidencia odbornej praxe</h1>
      </div>

      <!--  Hamburger button (mobile only) -->
      <button class="menu-btn" @click="toggleMenu" aria-label="Toggle menu">
        <span class="menu-icon" :class="{ open: menuOpen }"></span>
      </button>

      <!--  Navigation -->
      <nav :class="['nav-links', { open: menuOpen }]">
        <RouterLink to="/login" class="nav-item" active-class="active-link">
          Prihlásenie
        </RouterLink>
        <RouterLink to="/register" class="nav-item" active-class="active-link">
          Registrácia
        </RouterLink>
      </nav>
    </div>
  </header>
</template>

<script setup>
import { ref } from 'vue'

const menuOpen = ref(false)
function toggleMenu() {
  menuOpen.value = !menuOpen.value
}
</script>

<style scoped>
/* ============================= */
/*  HLAVNÉ NASTAVENIA */
/* ============================= */

.app-header {
  position: sticky;
  top: 0;
  z-index: 50;
  background-color: #ffffff;
  border-bottom: 1px solid #dcdcdc;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  font-family: 'Inter', sans-serif;
}

.header-container {
  max-width: 1000px;
  margin: 0 auto;
  padding: 12px 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

/* ============================= */
/*  LOGO A NÁZOV */
/* ============================= */

.logo-section {
  display: flex;
  align-items: center;
  gap: 12px;
}

.logo {
  width: 36px;
  height: 36px;
}

.title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #2c3e50;
}

/* ============================= */
/*  NAVIGÁCIA (DESKTOP) */
/* ============================= */

.nav-links {
  display: flex;
  align-items: center;
  gap: 28px;
}

.nav-item {
  font-size: 0.95rem;
  text-decoration: none;
  color: #555;
  transition: color 0.2s ease, border-color 0.2s ease;
  border-bottom: 2px solid transparent;
  padding-bottom: 2px;
}

.nav-item:hover {
  color: #1976d2;
}

.active-link {
  color: #1976d2;
  border-bottom: 2px solid #1976d2;
}

/* ============================= */
/*  HAMBURGER BUTTON */
/* ============================= */

.menu-btn {
  display: none;
  background: none;
  border: none;
  cursor: pointer;
  padding: 6px;
}

.menu-icon {
  position: relative;
  width: 22px;
  height: 2px;
  background-color: #333;
  display: block;
  transition: background 0.2s ease;
}

.menu-icon::before,
.menu-icon::after {
  content: '';
  position: absolute;
  left: 0;
  width: 22px;
  height: 2px;
  background-color: #333;
  transition: transform 0.25s ease, top 0.25s ease;
}

.menu-icon::before {
  top: -7px;
}

.menu-icon::after {
  top: 7px;
}

.menu-icon.open {
  background-color: transparent;
}

.menu-icon.open::before {
  transform: rotate(45deg);
  top: 0;
}

.menu-icon.open::after {
  transform: rotate(-45deg);
  top: 0;
}

/* ============================= */
/*  MOBILE STYLING */
/* ============================= */

@media (max-width: 768px) {
  .header-container {
    flex-wrap: wrap;
  }

  .menu-btn {
    display: block;
  }

  .nav-links {
    display: none;
    flex-direction: column;
    width: 100%;
    margin-top: 10px;
    border-top: 1px solid #eee;
    padding-top: 10px;
    gap: 10px;
  }

  .nav-links.open {
    display: flex;
  }

  .nav-item {
    width: 100%;
    text-align: center;
    padding: 8px 0;
    border-bottom: none;
  }

  .nav-item:hover {
    color: #1976d2;
    background-color: #f2f6ff;
    border-radius: 4px;
  }
}
</style>
