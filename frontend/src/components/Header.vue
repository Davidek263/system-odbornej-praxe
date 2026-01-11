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
          <RouterLink to="/profile" class="nav-item" active-class="active-link">
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
          <RouterLink to="/profile" class="nav-item" active-class="active-link">
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
          <RouterLink to="/guarantor-dashboard/tokens" class="nav-item" active-class="active-link">
            Tokeny
          </RouterLink>
          <RouterLink to="/guarantor-dashboard/statistics" class="nav-item" active-class="active-link">
            Štatistiky
          </RouterLink>
          <RouterLink to="/profile" class="nav-item" active-class="active-link">
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
import { ref, onMounted } from 'vue'
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
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid rgba(0, 0, 0, 0.08);
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
  font-family: 'Inter', sans-serif;
}

.header-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 14px 32px;
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
  transition: opacity 0.2s ease;
}

.logo-link:hover {
  opacity: 0.85;
}

.logo {
  width: 280px;
  height: auto;
  object-fit: contain;
  transition: transform 0.3s ease;
}

.logo:hover {
  transform: scale(1.02);
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
  font-size: 1.35rem;
  font-weight: 700;
  color: #2c3e50;
  margin: 0;
  transition: color 0.3s ease;
}

.title-link:hover .title {
  color: #42b883;
}

/* ============================= */
/*  NAVIGÁCIA (DESKTOP) */
/* ============================= */

.nav-links {
  display: flex;
  align-items: center;
  gap: 8px;
}

.nav-item {
  font-size: 0.95rem;
  text-decoration: none;
  color: #555;
  transition: all 0.3s ease;
  border-bottom: 2px solid transparent;
  padding: 8px 16px;
  background: none;
  border: none;
  cursor: pointer;
  font-family: 'Inter', sans-serif;
  font-weight: 500;
  border-radius: 8px;
  position: relative;
}

.nav-item:hover {
  color: #42b883;
  background: rgba(66, 184, 131, 0.08);
}

.active-link {
  color: #42b883;
  background: rgba(66, 184, 131, 0.12);
}

.active-link::after {
  content: '';
  position: absolute;
  bottom: 6px;
  left: 50%;
  transform: translateX(-50%);
  width: 20px;
  height: 2px;
  background: #42b883;
  border-radius: 2px;
}

.logout-btn {
  padding: 8px 18px !important;
  background: linear-gradient(135deg, #f5f5f5 0%, #e8e8e8 100%);
  border-radius: 8px;
  font-weight: 600;
  transition: all 0.3s ease;
  color: #555;
}

.logout-btn:hover {
  background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
  color: white;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(231, 76, 60, 0.25);
}

/* ============================= */
/*  HAMBURGER BUTTON */
/* ============================= */

.menu-btn {
  display: none;
  background: none;
  border: none;
  cursor: pointer;
  padding: 8px;
  transition: transform 0.2s ease;
}

.menu-btn:hover {
  transform: scale(1.1);
}

.menu-icon {
  position: relative;
  width: 24px;
  height: 2px;
  background-color: #2c3e50;
  display: block;
  transition: background 0.3s ease;
  border-radius: 2px;
}

.menu-icon::before,
.menu-icon::after {
  content: '';
  position: absolute;
  left: 0;
  width: 24px;
  height: 2px;
  background-color: #2c3e50;
  transition: transform 0.3s ease, top 0.3s ease;
  border-radius: 2px;
}

.menu-icon::before {
  top: -8px;
}

.menu-icon::after {
  top: 8px;
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
    padding: 12px 20px;
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
    margin-top: 12px;
    border-top: 1px solid rgba(0, 0, 0, 0.08);
    padding-top: 12px;
    gap: 6px;
    background: rgba(248, 249, 250, 0.5);
    border-radius: 12px;
    padding: 12px;
  }

  .nav-links.open {
    display: flex;
    animation: slideDown 0.3s ease;
  }

  @keyframes slideDown {
    from {
      opacity: 0;
      transform: translateY(-10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .nav-item {
    width: 100%;
    text-align: center;
    padding: 12px 16px;
    border-bottom: none;
  }

  .nav-item:hover {
    color: #42b883;
    background: rgba(66, 184, 131, 0.12);
    border-radius: 8px;
  }

  .active-link::after {
    display: none;
  }

  .logout-btn {
    padding: 12px 16px !important;
    background: linear-gradient(135deg, #f5f5f5 0%, #e8e8e8 100%);
    margin-top: 4px;
  }

  .logout-btn:hover {
    background: linear-gradient(135deg, #ffe5e5 0%, #ffcccc 100%);
    color: #e74c3c;
    transform: none;
    box-shadow: none;
  }
}

@media (max-width: 480px) {
  .header-container {
    padding: 10px 16px;
  }

  .logo {
    width: 160px;
    height: auto;
  }

  .nav-item {
    font-size: 0.9rem;
  }
}

@media (max-width: 360px) {
  .header-container {
    padding: 8px 12px;
  }

  .logo {
    width: 140px;
    height: auto;
  }

  .menu-icon {
    width: 20px;
  }

  .menu-icon::before,
  .menu-icon::after {
    width: 20px;
  }
}
</style>