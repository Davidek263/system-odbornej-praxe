<template>
  <header class="app-header">
    <div class="header-container">
      <!--  Logo + Title -->
      <div class="logo-section">
        <RouterLink to="/home" class="logo-link">
          <img src="/Praximoron_Logo.png" alt="Praximoron Logo" class="logo" />
        </RouterLink>
      </div>

      <!--  Hamburger button (mobile only) -->
      <button class="menu-btn" @click="toggleMenu" aria-label="Toggle menu">
        <span class="menu-icon" :class="{ open: menuOpen }"></span>
      </button>

      <!--  Navigation -->
      <nav :class="['nav-links', { open: menuOpen }]">
        <!-- Anonymous user navigation -->
        <template v-if="!userRole">
          <RouterLink to="/login" class="nav-item" active-class="active-link">
            Prihlásenie
          </RouterLink>
          <RouterLink to="/register" class="nav-item" active-class="active-link">
            Registrácia
          </RouterLink>
        </template>

        <!-- Student navigation -->
        <template v-else-if="userRole === 'student'">
          <RouterLink to="/student-dashboard" class="nav-item" active-class="active-link">
            Praxe
          </RouterLink>
          <RouterLink to="/profil" class="nav-item" active-class="active-link">
            Profil
          </RouterLink>
          <button @click="handleLogout" class="nav-item logout-btn">
            Odhlásiť
          </button>
        </template>

        <!-- Company navigation -->
        <template v-else-if="userRole === 'company'">
          <RouterLink to="/company-dashboard" class="nav-item" active-class="active-link">
            Praxe
          </RouterLink>
          <RouterLink to="/profil" class="nav-item" active-class="active-link">
            Profil
          </RouterLink>
          <button @click="handleLogout" class="nav-item logout-btn">
            Odhlásiť
          </button>
        </template>

        <!-- Guarantor navigation -->
        <template v-else-if="userRole === 'guarantor'">
          <RouterLink to="/guarantor-dashboard" class="nav-item" active-class="active-link">
            Praxe
          </RouterLink>
          <RouterLink to="/profil" class="nav-item" active-class="active-link">
            Profil
          </RouterLink>
          <button @click="handleLogout" class="nav-item logout-btn">
            Odhlásiť
          </button>
        </template>

        <!-- Fallback for any other authenticated user -->
        <template v-else>
          <RouterLink to="/profil" class="nav-item" active-class="active-link">
            Profil
          </RouterLink>
          <button @click="handleLogout" class="nav-item logout-btn">
            Odhlásiť
          </button>
        </template>
      </nav>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const menuOpen = ref(false)
const userRole = ref(null)

function toggleMenu() {
  menuOpen.value = !menuOpen.value
}

function loadUserRole() {
  const user = localStorage.getItem('user')
  if (user) {
    try {
      const parsedUser = JSON.parse(user)
      userRole.value = parsedUser.role_name || null
    } catch (e) {
      console.error('Failed to parse user data:', e)
      userRole.value = null
    }
  } else {
    userRole.value = null
  }
}

function handleLogout() {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  userRole.value = null
  router.push('/login')
}

onMounted(() => {
  loadUserRole()
  
  // Listen for storage changes (e.g., login in another tab)
  window.addEventListener('storage', loadUserRole)
})

// Re-check user role when navigating
router.afterEach(() => {
  loadUserRole()
})
</script>

<style scoped>
/* ============================= */
/*  HLAVNÉ NASTAVENIA */
/* ============================= */

.app-header {
  position: sticky;
  top: 0;
  z-index: 1000;
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

.logo-link {
  display: flex;
  align-items: center;
}

.logo {
  width: 300px;
  height: auto;
  object-fit: contain;
  transition: transform 0.2s ease;
}

.logo:hover {
  transform: scale(1.05);
}

.title-link {
  text-decoration: none;
  border-bottom: none !important;
  padding-bottom: 0 !important;
}

.title-link:hover {
  color: inherit;
}

.title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #2c3e50;
  margin: 0;
  transition: color 0.2s ease;
}

.title-link:hover .title {
  color: #1976d2;
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
  background: none;
  border: none;
  cursor: pointer;
  font-family: 'Inter', sans-serif;
}

.nav-item:hover {
  color: #1976d2;
}

.active-link {
  color: #1976d2;
  border-bottom: 2px solid #1976d2;
}

.logout-btn {
  border-bottom: none !important;
  padding: 6px 14px;
  background-color: #f5f5f5;
  border-radius: 6px;
  font-weight: 500;
  transition: all 0.2s ease;
}

.logout-btn:hover {
  background-color: #e74c3c;
  color: white;
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
    padding: 10px 16px;
  }

  .logo {
    width: 200px;
    height: auto;
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

  .logout-btn {
    padding: 10px 0;
    background-color: transparent;
  }

  .logout-btn:hover {
    background-color: #ffe5e5;
    color: #e74c3c;
  }
}

@media (max-width: 480px) {
  .header-container {
    padding: 8px 12px;
  }

  .logo {
    width: 160px;
    height: auto;
  }
}

@media (max-width: 360px) {
  .logo {
    width: 140px;
    height: auto;
  }
}
</style>